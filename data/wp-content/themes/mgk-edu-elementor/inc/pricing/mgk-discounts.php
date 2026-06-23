<?php
/**
 * MGK — Unified Discount / Pricing Engine (DATA CORE, LOCKED logic).
 * ==================================================================
 * THE single source of truth for every amount the customer sees AND pays.
 * Profile display, S09 offer, S11 checkout summary, the booking-engine hold
 * price, the Stripe charge and the e-invoice all call `mgk_quote()` — so the
 * displayed total can never diverge from the charged total.
 *
 * Two configurable layers, both editable by the agency in wp-admin (no code):
 *   1. DISCOUNT RULES — automatic business rules (BR-01 trial / BR-05 sibling /
 *      BR-06 returning / package tiers / stacking cap). Stored in the option
 *      `mgk_discount_rules`; the admin toggles them on/off and edits the %.
 *   2. VOUCHERS — the `mgk_voucher` CPT (see mgk-voucher-cpt.php): one code per
 *      order, min-spend, stackable flag, etc.
 *
 * Stacking (BR-05/06): automatic loyalty discounts (sibling/returning) share
 * the configured cap (default 25%) of the advertised price. A voucher only
 * shares that cap when its campaign explicitly opts in; its own maximum amount
 * is configured on the voucher. GST (BR-04) is inclusive.
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Margick\Commerce\Domain\Money;
use Margick\Commerce\Domain\Discount\DiscountEngine;
use Margick\Commerce\Domain\Discount\DiscountLine;
use Margick\Commerce\Domain\Discount\QuoteRequest;

if ( ! defined( 'MGK_GST_PCT' ) )            define( 'MGK_GST_PCT', 9 );          // BR-04
if ( ! defined( 'MGK_DISCOUNT_STACK_CAP' ) ) define( 'MGK_DISCOUNT_STACK_CAP', 25 ); // BR-05/06

/* ─────────────────────────────────────────────────────────────────────────
 * 1. RULES CONFIG  (option-backed, admin-editable)
 * ───────────────────────────────────────────────────────────────────────── */

/**
 * Factory defaults for the discount rules. The agency overrides these in
 * wp-admin → Discounts → Discount Rules; only the keys present in the saved
 * option override the defaults, so new rules added in future ship enabled.
 */
function mgk_discount_rules_defaults() {
	return [
		'trial_pct'        => 40,   // BR-01 — first trial lesson % off the hourly rate
		'trial_enabled'    => 1,
		'package_8_pct'    => 5,    // package tier savings
		'package_16_pct'   => 10,
		'package_enabled'  => 1,
		'sibling_pct'      => 3,    // BR-05 — same parent email already has a child
		'sibling_pkg_pct'  => 5,    // BR-05 — sibling rate on packages
		'sibling_enabled'  => 1,
		'returning_pct'    => 5,    // BR-06 — re-signs within the window below
		'returning_days'   => 30,
		'returning_enabled'=> 1,
		'stack_cap_pct'    => MGK_DISCOUNT_STACK_CAP, // total extra-discount ceiling
		'gst_pct'          => MGK_GST_PCT,
		'gst_inclusive'    => 1,
		'currency'         => 'SGD',   // site charge currency; Money infers decimals (VND/JPY = 0)
	];
}

/** Current discount rules = defaults merged with the agency's saved overrides. */
function mgk_discount_rules() {
	$saved = get_option( 'mgk_discount_rules', [] );
	if ( ! is_array( $saved ) ) $saved = [];
	$rules = array_merge( mgk_discount_rules_defaults(), $saved );
	return apply_filters( 'mgk_discount_rules', $rules );
}

/** One rule value (int by default). */
function mgk_discount_rule( $key, $fallback = 0 ) {
	$rules = mgk_discount_rules();
	return $rules[ $key ] ?? $fallback;
}

/* ─────────────────────────────────────────────────────────────────────────
 * 2. ELIGIBILITY  (which automatic loyalty discounts apply to THIS customer)
 * ───────────────────────────────────────────────────────────────────────── */

/** Read-only resolve of the parent user id from a quote context (never creates). */
function mgk_discount_resolve_user( $ctx ) {
	$uid = (int) ( $ctx['parent_user_id'] ?? 0 );
	if ( ! $uid && ! empty( $ctx['parent_email'] ) ) {
		$u = get_user_by( 'email', sanitize_email( $ctx['parent_email'] ) );
		$uid = $u ? (int) $u->ID : 0;
	}
	return $uid;
}

/**
 * Is this parent a "sibling" case (BR-05)? True when the resolved parent user
 * already has at least one OTHER child on the account — detected by the shared
 * parent email/account, never self-declared.
 *
 * @param array $ctx  quote context (parent_user_id | parent_email | child_id)
 */
function mgk_discount_is_sibling( $ctx ) {
	$uid = mgk_discount_resolve_user( $ctx );
	if ( ! $uid || ! function_exists( 'mgk_parent_children' ) ) return false;

	$children = mgk_parent_children( $uid );
	$count    = is_array( $children ) ? count( $children ) : 0;
	// If the quote is FOR one of these children, "sibling" means there is ≥1 more.
	$for_child = (int) ( $ctx['child_id'] ?? 0 );
	if ( $for_child ) {
		return $count >= 2;
	}
	return $count >= 1; // adding another child to an account that already has one
}

/**
 * Returning-student case (BR-06): the parent is re-signing a PACKAGE within the
 * window. Per SRS this is specifically about packages ("tái ký gói học … không
 * quá 30 ngày") — a prior trial does NOT make someone "returning". We therefore
 * require a prior PACKAGE enrolment (PACKAGE_8 / PACKAGE_16) for this parent,
 * created within `returning_days`.
 */
function mgk_discount_is_returning( $ctx ) {
	$uid = mgk_discount_resolve_user( $ctx );
	if ( ! $uid || ! post_type_exists( 'mg_enrolment' ) ) return false;

	$days   = max( 1, (int) mgk_discount_rule( 'returning_days', 30 ) );
	$cutoff = gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS );
	$prior  = get_posts( [
		'post_type'   => 'mg_enrolment',
		'numberposts' => 1,
		'fields'      => 'ids',
		'date_query'  => [ [ 'after' => $cutoff, 'inclusive' => true ] ],
		'meta_query'  => [
			[ 'key' => 'mgk_enr_parent_user_id', 'value' => $uid ],
			[ 'key' => 'mgk_enr_plan_type', 'value' => [ 'PACKAGE_8', 'PACKAGE_16' ], 'compare' => 'IN' ],
		],
	] );
	return ! empty( $prior );
}

/* ─────────────────────────────────────────────────────────────────────────
 * 3. THE QUOTE  (single source of truth — display AND charge call this)
 * ───────────────────────────────────────────────────────────────────────── */

/**
 * Resolve the discountable base + headline (advertised) discount for an item.
 *
 * @param array $args  item_type (trial|package_8|package_16|single|retail),
 *                     rate_num | base_amount, units
 * @return array{base:float, headline_label:string, headline_pct:int, headline_amount:float, advertised:float, units:int}
 */
function mgk_quote_base( $args ) {
	$rules     = mgk_discount_rules();
	$item_type = (string) ( $args['item_type'] ?? 'trial' );
	$rate      = (float) ( $args['rate_num'] ?? 0 );
	if ( $rate <= 0 ) $rate = 65;

	switch ( $item_type ) {
		case 'trial':
			$pct  = ! empty( $rules['trial_enabled'] ) ? (int) $rules['trial_pct'] : 0;
			$raw  = $rate * ( 1 - $pct / 100 );
			$adv  = (float) ( round( $raw / 5 ) * 5 );          // nearest $5 (clean marketing figure)
			if ( $adv <= 0 || $adv >= $rate ) $adv = round( $raw, 2 );
			return [
				'base' => $rate, 'units' => 1,
				'headline_label' => sprintf( 'Trial discount (%d%%)', $pct ),
				'headline_pct'   => $pct,
				'headline_amount'=> max( 0, $rate - $adv ),
				'advertised'     => $adv,
			];

		case 'package_8':
		case 'package_16':
			$units = $item_type === 'package_16' ? 16 : 8;
			$pct   = ! empty( $rules['package_enabled'] )
				? (int) ( $item_type === 'package_16' ? $rules['package_16_pct'] : $rules['package_8_pct'] )
				: 0;
			$base  = $rate * $units;
			$adv   = round( $base * ( 1 - $pct / 100 ), 2 );
			return [
				'base' => $base, 'units' => $units,
				'headline_label' => sprintf( '%d-lesson package (%d%% off)', $units, $pct ),
				'headline_pct'   => $pct,
				'headline_amount'=> max( 0, $base - $adv ),
				'advertised'     => $adv,
			];

		default: // 'single' / 'retail' / explicit base_amount — no headline discount
			$base = (float) ( $args['base_amount'] ?? $rate );
			return [
				'base' => $base, 'units' => (int) ( $args['units'] ?? 1 ),
				'headline_label' => '', 'headline_pct' => 0, 'headline_amount' => 0,
				'advertised' => $base,
			];
	}
}

/**
 * Full quote: headline discount → eligible automatic discounts + optional
 * voucher under their respective cap policies → GST. Returns BOTH machine
 * numbers (for charge) and display rows. This is THE funnel pricing function.
 *
 * @param array $args item_type, rate_num|base_amount, units, context{parent_user_id,
 *              parent_email,child_id}, voucher_code, apply_loyalty(bool, default true)
 * @return array
 */
function mgk_quote( $args = [] ) {
	$rules     = mgk_discount_rules();
	$ctx       = (array) ( $args['context'] ?? [] );
	$money     = function ( $n ) { return '$' . number_format( (float) $n, 2 ); };
	$item_type = (string) ( $args['item_type'] ?? 'trial' );
	$currency  = (string) ( $rules['currency'] ?? 'SGD' );
	$cap_pct   = max( 0, (int) $rules['stack_cap_pct'] );
	$gst_pct   = (int) $rules['gst_pct'];
	$gst_incl  = ! empty( $rules['gst_inclusive'] );

	$b          = mgk_quote_base( $args );
	$base       = (float) $b['base'];
	$advertised = (float) $b['advertised'];
	$is_package = in_array( $item_type, [ 'package_8', 'package_16' ], true );

	// Headline (advertised) discount — edu item-type logic stays in mgk_quote_base().
	$headline = ( $b['headline_amount'] > 0 )
		? new DiscountLine( 'headline:' . $item_type, $b['headline_label'], (int) $b['headline_pct'], Money::ofMajor( (float) $b['headline_amount'], $currency ) )
		: null;

	// ── Eligibility (edu/WP) → candidate EXTRA discounts on the advertised price ──
	$apply_loyalty = $args['apply_loyalty'] ?? true;
	$loyalty = [];
	if ( $apply_loyalty ) {
		if ( ! empty( $rules['sibling_enabled'] ) && mgk_discount_is_sibling( $ctx ) ) {
			$p = $is_package ? (int) $rules['sibling_pkg_pct'] : (int) $rules['sibling_pct'];
			$loyalty[] = new DiscountLine( 'sibling', 'Sibling discount', $p, Money::ofMajor( $advertised * $p / 100, $currency ) );
		}
		if ( ! empty( $rules['returning_enabled'] ) && mgk_discount_is_returning( $ctx ) ) {
			$p = (int) $rules['returning_pct'];
			$loyalty[] = new DiscountLine( 'returning', 'Returning student', $p, Money::ofMajor( $advertised * $p / 100, $currency ) );
		}
	}

	// ── Voucher (edu/WP CPT) ──
	$code = strtoupper( trim( (string) ( $args['voucher_code'] ?? '' ) ) );
	$voucher = null; $voucher_stackable = false; $voucher_capped = false; $voucher_note = '';
	if ( $code !== '' && function_exists( 'mgk_voucher_validate' ) ) {
		$v = mgk_voucher_validate( $code, [ 'subtotal' => $advertised, 'base' => $advertised, 'currency' => $currency, 'item_type' => $item_type, 'context' => $ctx ] );
		if ( ! empty( $v['valid'] ) ) {
			$vpct = $v['type'] === 'pct' ? (int) round( (float) $v['value'] ) : 0;
			$vamt = isset( $v['amount'] )
				? (float) $v['amount']
				: ( $v['type'] === 'fixed' ? (float) $v['value'] : $advertised * ( (float) $v['value'] / 100 ) );
			$voucher = new DiscountLine( 'voucher:' . $code, 'Voucher ' . $code, $vpct, Money::ofMajor( $vamt, $currency ) );
			$voucher_stackable = ! empty( $v['is_stackable'] );
			$voucher_capped = ! empty( $v['respect_global_cap'] );
		} else {
			$voucher_note = (string) ( $v['message'] ?? 'Voucher not applicable' );
		}
	}

	// ── Generic engine = single source of math: conflict + stacking + cap + GST ──
	$res = ( new DiscountEngine() )->quote( new QuoteRequest(
		base:             Money::ofMajor( $base, $currency ),
		headline:         $headline,
		loyalty:          $loyalty,
		voucher:          $voucher,
		voucherStackable: $voucher_stackable,
		voucherCapped:    $voucher_capped,
		capPct:           $cap_pct,
		gstPct:           $gst_pct,
		gstInclusive:     $gst_incl,
		lineLabel:        ( $args['line_label'] ?? 'Lesson' )
	) );

	// Voucher messaging (BR-11) reconstructed from which side the engine applied.
	if ( $voucher && ! $voucher_stackable && $loyalty ) {
		$applied_keys = array_map( function ( $d ) { return $d->key; }, $res->applied );
		if ( in_array( 'voucher:' . $code, $applied_keys, true ) ) {
			$voucher_note = 'Voucher applied instead of your other discounts (better value)';
		} else {
			$loyalty_total = 0.0;
			foreach ( $loyalty as $l ) { $loyalty_total += $l->amount->toMajor(); }
			$voucher_note = sprintf( 'Voucher %s not applied — your current discounts save more (-%s)', $code, $money( $loyalty_total ) );
		}
	}

	// ── Map engine result → the EXACT mgk_quote() return contract ($ format + rows) ──
	$rows = [];
	$rows[] = [ 'label' => ( $args['line_label'] ?? 'Lesson' ), 'value' => $money( $base ), 'accent' => false, 'strong' => false ];
	foreach ( $res->applied as $d ) {
		$is_headline = strpos( $d->key, 'headline:' ) === 0;
		$rows[] = [
			'label'  => $is_headline ? $d->label : $d->labelWithPct(),
			'value'  => '-' . $money( $d->amount->toMajor() ),
			'accent' => true, 'strong' => false,
		];
	}

	$subtotal   = $res->subtotal->toMajor();
	$total      = $res->total->toMajor();
	$net_amount = $res->net->toMajor();
	$gst_amount = $res->gst->toMajor();
	$gst_note   = $gst_incl
		? sprintf( 'INCL. %d%% GST (BR-04) · %s', $gst_pct, $currency )
		: sprintf( '+ %d%% GST (BR-04) · %s', $gst_pct, $currency );
	$cap_note   = $res->capped ? sprintf( 'Stacked discounts capped at %d%% (BR-05/06)', $cap_pct ) : '';

	return [
		'rows'             => $rows,
		'base'             => round( $base, 2 ),
		'advertised'       => round( $advertised, 2 ),
		'headline'         => $b['headline_amount'] > 0 ? [ 'label' => $b['headline_label'], 'pct' => $b['headline_pct'], 'amount' => round( $b['headline_amount'], 2 ) ] : null,
		'discounts_applied'=> $res->appliedToArray(),
		'subtotal'         => round( $subtotal, 2 ),
		'total'            => round( $total, 2 ),
		'total_str'        => $money( $total ),
		'subtotal_str'     => $money( $subtotal ),
		'net_amount'       => $net_amount,
		'net_str'          => $money( $net_amount ),
		'gst_amount'       => $gst_amount,
		'gst_str'          => $money( $gst_amount ),
		'gst_inclusive'    => $gst_incl,
		'currency'         => $currency,
		'gst_note'         => $gst_note,
		'gst_pct'          => $gst_pct,
		'cap_note'         => $cap_note,
		'voucher_note'     => $voucher_note,
		'voucher_code'     => $code,
		'item_type'        => $item_type,
	];
}

/**
 * Engine-derived package cards for a tutor's hourly rate, so the prices shown on
 * the profile come from the SAME discount rules the agency edits in wp-admin (and
 * match what a package would charge). Returns the standard 3-tier set in the
 * profile card shape [ name, price_str, description, featured ].
 */
function mgk_engine_packages_for_rate( $rate ) {
	$rate = (int) $rate; if ( $rate <= 0 ) $rate = 65;
	$trial = mgk_quote( [ 'item_type' => 'trial',      'rate_num' => $rate, 'apply_loyalty' => false ] );
	$p8    = mgk_quote( [ 'item_type' => 'package_8',  'rate_num' => $rate, 'apply_loyalty' => false ] );
	$p16   = mgk_quote( [ 'item_type' => 'package_16', 'rate_num' => $rate, 'apply_loyalty' => false ] );
	$rules = mgk_discount_rules();
	return [
		[ 'Trial lesson', $trial['total_str'], sprintf( 'First lesson %d%% off', (int) $rules['trial_pct'] ), true ],
		[ '8 lessons',    $p8['total_str'],    sprintf( '%d%% package saving', (int) $rules['package_8_pct'] ), false ],
		[ '16 lessons',   $p16['total_str'],   sprintf( '%d%% package saving', (int) $rules['package_16_pct'] ), false ],
	];
}

/** Detect the standard tier (trial|package_8|package_16) from a package name. */
function mgk_engine_detect_tier( $name ) {
	$n = strtolower( (string) $name );
	if ( strpos( $n, 'trial' ) !== false ) return 'trial';
	if ( strpos( $n, '16' )    !== false ) return 'package_16';
	if ( strpos( $n, '8' )     !== false ) return 'package_8';
	return '';
}

/**
 * Convenience: the charge-authoritative TRIAL quote for a tutor post, including
 * any loyalty discounts the parent is eligible for at hold time. Used by the
 * booking engine so the held price already reflects the real discounts.
 */
function mgk_quote_trial_for_tutor( $tutor_id, $ctx = [] ) {
	$rate = (int) get_post_meta( (int) $tutor_id, 'mgk_rate_num', true );
	return mgk_quote( [
		'item_type'  => 'trial',
		'rate_num'   => $rate,
		'line_label' => 'Trial lesson',
		'context'    => $ctx,
		'voucher_code' => $ctx['voucher_code'] ?? '',
	] );
}
