<?php
/**
 * MGK — Voucher CPT + validation.
 * ===============================
 * Admin-managed discount codes (`mgk_voucher`). One voucher per order (BR-11);
 * the cart/checkout replaces an existing code rather than stacking two vouchers.
 * Validation is server-authoritative and runs both at apply-time and again at
 * charge-time so a tampered client can never widen a discount.
 *
 * Meta:
 *   mgk_v_code         (string, uppercased, unique-ish)   — the code customers type
 *   mgk_v_type         ('pct' | 'fixed')
 *   mgk_v_value        (number) — percent (0-100) or fixed SGD amount
 *   mgk_v_is_stackable (0|1)    — may combine with auto loyalty discounts
 *   mgk_v_min_spend    (number) — minimum order subtotal to qualify
 *   mgk_v_max_uses     (int)    — 0 = unlimited
 *   mgk_v_used_count   (int)    — incremented on successful payment
 *   mgk_v_valid_from   (Y-m-d)  — optional
 *   mgk_v_valid_to     (Y-m-d)  — optional
 *   mgk_v_applies_to   ('all'|'trial'|'package'|'retail')
 *   mgk_v_exclude_sale (0|1)    — retail: skip already-discounted items
 *   mgk_v_active       (0|1)
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── CPT registration ───────────────────────────────────────────────────── */

add_action( 'init', function () {
	register_post_type( 'mgk_voucher', [
		'labels' => [
			'name'               => 'Vouchers',
			'singular_name'      => 'Voucher',
			'add_new'            => 'Add Voucher',
			'add_new_item'       => 'Add Voucher',
			'edit_item'          => 'Edit Voucher',
			'new_item'           => 'New Voucher',
			'view_item'          => 'View Voucher',
			'search_items'       => 'Search Vouchers',
			'not_found'          => 'No vouchers yet — create your first code',
			'not_found_in_trash' => 'No vouchers in trash',
			'menu_name'          => 'Vouchers',
		],
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'mgk-discounts',  // nested under the Discounts top menu
		'supports'      => [ 'title' ],
		'capability_type' => 'post',
		'map_meta_cap'  => true,
	] );
} );

/* ── Helpers ────────────────────────────────────────────────────────────── */

/** Normalise a code: trimmed, uppercased, no spaces. */
function mgk_voucher_norm_code( $code ) {
	if ( class_exists( '\\Margick\\Commerce\\Voucher\\Domain\\Voucher' ) ) {
		return \Margick\Commerce\Voucher\Domain\Voucher::normalizeCode( (string) $code );
	}
	return strtoupper( preg_replace( '/\s+/', '', (string) $code ) );
}

/** Stable customer identity passed to the module's per-customer restrictions. */
function mgk_voucher_customer_key( $ctx ) {
	$email = sanitize_email( (string) ( $ctx['parent_email'] ?? '' ) );
	$uid   = (int) ( $ctx['parent_user_id'] ?? 0 );
	if ( $email === '' && $uid > 0 ) {
		$user = get_userdata( $uid );
		$email = $user ? sanitize_email( (string) $user->user_email ) : '';
	}
	return $email !== '' ? $email : ( $uid > 0 ? 'user:' . $uid : null );
}

/** Edu adapter for the generic first-order predicate. */
function mgk_voucher_customer_is_first_order( $ctx ) {
	if ( array_key_exists( 'first_order', (array) $ctx ) ) return ! empty( $ctx['first_order'] );
	$uid = (int) ( $ctx['parent_user_id'] ?? 0 );
	if ( ! $uid && ! empty( $ctx['parent_email'] ) ) {
		$user = get_user_by( 'email', sanitize_email( (string) $ctx['parent_email'] ) );
		$uid = $user ? (int) $user->ID : 0;
		if ( ! $uid ) return true;
	}
	if ( ! $uid || ! function_exists( 'mgk_booking_table' ) ) return false;
	global $wpdb;
	$table = mgk_booking_table( 'bookings' );
	$exclude = (int) ( $ctx['booking_id'] ?? 0 );
	$sql = "SELECT COUNT(*) FROM {$table}
		WHERE parent_user_id = %d AND status NOT IN ('CANCELLED','EXPIRED')";
	$args = [ $uid ];
	if ( $exclude > 0 ) {
		$sql .= ' AND id <> %d';
		$args[] = $exclude;
	}
	return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) ) === 0;
}

/** Map the legacy Edu admin choice into exact, industry-owned item_type values. */
function mgk_voucher_item_types( $scope ) {
	$map = [
		'all'     => [],
		'trial'   => [ 'trial' ],
		'package' => [ 'package_8', 'package_16' ],
		'retail'  => [ 'retail' ],
	];
	$types = $map[ (string) $scope ] ?? [ (string) $scope ];
	return apply_filters( 'mgk_voucher_scope_item_types', $types, $scope );
}

/** Find the published voucher post for a code (or null). */
function mgk_voucher_find( $code ) {
	$code = mgk_voucher_norm_code( $code );
	if ( $code === '' ) return null;
	$q = get_posts( [
		'post_type'   => 'mgk_voucher',
		'post_status' => 'publish',
		'meta_key'    => 'mgk_v_code',
		'meta_value'  => $code,
		'numberposts' => 1,
		'fields'      => 'ids',
	] );
	return $q ? (int) $q[0] : null;
}

/**
 * Validate a voucher code against an order context.
 *
 * @param string $code
 * @param array  $args  subtotal, base, item_type, context
 * @return array{valid:bool, type?:string, value?:float, is_stackable?:bool, message?:string, post_id?:int}
 */
function mgk_voucher_validate( $code, $args = [] ) {
	$code = mgk_voucher_norm_code( $code );
	$fail = function ( $msg ) { return [ 'valid' => false, 'message' => $msg ]; };

	if ( class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) ) {
		$currency = strtoupper( (string) ( $args['currency'] ?? ( function_exists( 'mgk_discount_rule' ) ? mgk_discount_rule( 'currency', 'SGD' ) : 'SGD' ) ) );
		$subtotal = (float) ( $args['subtotal'] ?? $args['base'] ?? 0 );
		$item     = (string) ( $args['item_type'] ?? '' );
		$ctx      = (array) ( $args['context'] ?? [] );
		$context  = new \Margick\Commerce\Voucher\Domain\VoucherContext(
			\Margick\Commerce\Domain\Money::ofMajor( $subtotal, $currency ),
			$item !== '' ? [ $item ] : [],
			mgk_voucher_customer_key( $ctx ),
			mgk_voucher_customer_is_first_order( $ctx )
		);
		$reference_id = (int) ( $ctx['booking_id'] ?? 0 );
		$decision = \Margick\Commerce\Wp\VoucherRepository::preview(
			$code,
			$context,
			$reference_id > 0 ? 'booking' : null,
			$reference_id > 0 ? (string) $reference_id : null
		);
		if ( ! $decision->valid || ! $decision->voucher || ! $decision->discount ) {
			return $fail( $decision->message ?: 'Voucher not applicable' );
		}
		$v = $decision->voucher;
		return [
			'valid'        => true,
			'type'         => $v->discountType === $v::TYPE_FIXED ? 'fixed' : 'pct',
			'value'        => $v->discountType === $v::TYPE_FIXED
				? $v->fixedAmountMinor / ( 10 ** \Margick\Commerce\Domain\Money::scaleFor( $currency ) )
				: $v->percentageBps / 100,
			'amount'       => $decision->discount->toMajor(),
			'is_stackable' => $v->stackable,
			'voucher_id'   => $v->id,
			'message'      => '',
		];
	}

	$pid = mgk_voucher_find( $code );
	if ( ! $pid ) return $fail( 'Invalid voucher code' );

	$m = function ( $k, $d = '' ) use ( $pid ) { return get_post_meta( $pid, $k, true ) ?: $d; };

	if ( (int) $m( 'mgk_v_active', 1 ) !== 1 ) return $fail( 'This voucher is no longer active' );

	$today = gmdate( 'Y-m-d' );
	if ( ( $from = $m( 'mgk_v_valid_from' ) ) && $today < $from ) return $fail( 'This voucher is not active yet' );
	if ( ( $to   = $m( 'mgk_v_valid_to' ) )   && $today > $to )   return $fail( 'This voucher has expired' );

	$max  = (int) $m( 'mgk_v_max_uses', 0 );
	$used = (int) $m( 'mgk_v_used_count', 0 );
	if ( $max > 0 && $used >= $max ) return $fail( 'This voucher has reached its usage limit' );

	$min = (float) $m( 'mgk_v_min_spend', 0 );
	$sub = (float) ( $args['subtotal'] ?? $args['base'] ?? 0 );
	if ( $min > 0 && $sub < $min ) {
		return $fail( sprintf( 'Spend at least $%s to use this voucher', number_format( $min, 2 ) ) );
	}

	$applies = $m( 'mgk_v_applies_to', 'all' );
	$item    = (string) ( $args['item_type'] ?? '' );
	if ( $applies === 'trial'   && $item !== 'trial' )                                  return $fail( 'This voucher applies to trial lessons only' );
	if ( $applies === 'package' && strpos( $item, 'package' ) !== 0 )                   return $fail( 'This voucher applies to packages only' );
	if ( $applies === 'retail'  && $item !== 'retail' )                                 return $fail( 'This voucher applies to store purchases only' );

	$type  = $m( 'mgk_v_type', 'pct' ) === 'fixed' ? 'fixed' : 'pct';
	$value = (float) $m( 'mgk_v_value', 0 );
	if ( $value <= 0 ) return $fail( 'Voucher misconfigured' );

	return [
		'valid'        => true,
		'type'         => $type,
		'value'        => $value,
		'is_stackable' => (int) $m( 'mgk_v_is_stackable', 0 ) === 1,
		'post_id'      => $pid,
		'message'      => '',
	];
}

/** Project one legacy voucher CPT row into the module-owned custom table. */
function mgk_voucher_sync_post_to_module( $post_id ) {
	if ( ! class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) || get_post_type( $post_id ) !== 'mgk_voucher' ) return 0;
	$g = function ( $key, $default = '' ) use ( $post_id ) {
		$value = get_post_meta( $post_id, $key, true );
		return $value === '' ? $default : $value;
	};
	$code = mgk_voucher_norm_code( $g( 'mgk_v_code' ) );
	if ( $code === '' ) return 0;
	$duplicates = get_posts( [
		'post_type' => 'mgk_voucher', 'post_status' => 'any', 'numberposts' => 2,
		'fields' => 'ids', 'meta_key' => 'mgk_v_code', 'meta_value' => $code,
	] );
	foreach ( $duplicates as $duplicate_id ) {
		if ( (int) $duplicate_id !== (int) $post_id ) return 0;
	}

	$old_code = mgk_voucher_norm_code( get_post_meta( $post_id, 'mgk_v_synced_code', true ) );
	if ( $old_code !== '' && $old_code !== $code ) {
		\Margick\Commerce\Wp\VoucherRepository::setStatus( $old_code, 'archived' );
	}

	$currency = strtoupper( (string) ( function_exists( 'mgk_discount_rule' ) ? mgk_discount_rule( 'currency', 'SGD' ) : 'SGD' ) );
	$type     = $g( 'mgk_v_type', 'pct' ) === 'fixed' ? 'fixed' : 'percent';
	$value    = max( 0, (float) $g( 'mgk_v_value', 0 ) );
	$from     = (string) $g( 'mgk_v_valid_from' );
	$to       = (string) $g( 'mgk_v_valid_to' );
	$end      = $to !== '' ? gmdate( 'Y-m-d H:i:s', strtotime( $to . ' 00:00:00 UTC +1 day' ) ) : null;
	$id = \Margick\Commerce\Wp\VoucherRepository::upsert( [
		'code'                     => $code,
		'name'                     => get_the_title( $post_id ) ?: $code,
		'status'                   => get_post_status( $post_id ) === 'publish' && (int) $g( 'mgk_v_active', 1 ) === 1 ? 'active' : 'inactive',
		'discount_type'            => $type,
		'percentage_bps'           => $type === 'percent' ? (int) round( $value * 100 ) : 0,
		'fixed_amount_minor'       => $type === 'fixed' ? \Margick\Commerce\Domain\Money::ofMajor( $value, $currency )->minor() : 0,
		'currency'                 => $currency,
		'min_order_minor'          => \Margick\Commerce\Domain\Money::ofMajor( (float) $g( 'mgk_v_min_spend', 0 ), $currency )->minor(),
		'stackable'                => (int) $g( 'mgk_v_is_stackable', 0 ) === 1,
		'usage_limit'              => (int) $g( 'mgk_v_max_uses', 0 ) ?: null,
		'usage_limit_per_customer' => (int) $g( 'mgk_v_max_uses_per_customer', 0 ) ?: null,
		'customer_key'             => (string) $g( 'mgk_v_customer_key' ) ?: null,
		'first_order_only'         => (int) $g( 'mgk_v_first_order_only', 0 ) === 1,
		'applies_to'               => mgk_voucher_item_types( $g( 'mgk_v_applies_to', 'all' ) ),
		'starts_at_utc'            => $from !== '' ? $from . ' 00:00:00' : null,
		'ends_at_utc'              => $end,
		'metadata'                 => [ 'legacy_post_id' => (int) $post_id, 'exclude_sale' => (int) $g( 'mgk_v_exclude_sale', 0 ) === 1 ],
	] );
	if ( $id > 0 ) {
		update_post_meta( $post_id, 'mgk_v_synced_code', $code );
		\Margick\Commerce\Wp\VoucherRepository::importLegacyUsage( $code, (int) $g( 'mgk_v_used_count', 0 ) );
	}
	return $id;
}

/** Keep the legacy CPT screen as a projection of the module redemption ledger. */
function mgk_voucher_refresh_legacy_count( $code ) {
	if ( ! class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) ) return;
	$pid = mgk_voucher_find( $code );
	if ( ! $pid ) return;
	$stats = \Margick\Commerce\Wp\VoucherRepository::usageStats( $code );
	update_post_meta( $pid, 'mgk_v_used_count', (int) $stats['consumed'] );
}

/** One-time import for vouchers created before the module-owned tables existed. */
add_action( 'init', function () {
	if ( get_option( 'mgk_voucher_module_sync_version' ) === '1.0.0' || ! post_type_exists( 'mgk_voucher' ) ) return;
	$ids = get_posts( [ 'post_type' => 'mgk_voucher', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ] );
	$ok = true;
	foreach ( $ids as $id ) {
		if ( ! mgk_voucher_sync_post_to_module( (int) $id ) ) $ok = false;
	}
	if ( $ok ) update_option( 'mgk_voucher_module_sync_version', '1.0.0' );
}, 30 );

/**
 * Atomically increment a voucher's used_count, but ONLY while it is still under
 * its max_uses limit. The conditional UPDATE makes the check-and-increment a
 * single DB operation, so concurrent confirmations can never push used_count past
 * max_uses (the race the old read-then-write had). max_uses = 0 means unlimited.
 *
 * @return bool true if the count was incremented.
 */
function mgk_voucher_mark_used( $code ) {
	global $wpdb;
	$pid = mgk_voucher_find( $code );
	if ( ! $pid ) return false;

	// Guarantee the counter row exists so the atomic UPDATE has a row to touch.
	if ( get_post_meta( $pid, 'mgk_v_used_count', true ) === '' ) {
		add_post_meta( $pid, 'mgk_v_used_count', 0, true );
	}

	$res = $wpdb->query( $wpdb->prepare(
		"UPDATE {$wpdb->postmeta} uc
		 LEFT JOIN {$wpdb->postmeta} mx
		   ON mx.post_id = uc.post_id AND mx.meta_key = 'mgk_v_max_uses'
		 SET uc.meta_value = CAST(uc.meta_value AS UNSIGNED) + 1
		 WHERE uc.post_id = %d AND uc.meta_key = 'mgk_v_used_count'
		   AND ( COALESCE(CAST(mx.meta_value AS UNSIGNED), 0) = 0
		         OR CAST(uc.meta_value AS UNSIGNED) < CAST(mx.meta_value AS UNSIGNED) )",
		$pid
	) );
	wp_cache_delete( $pid, 'post_meta' ); // raw SQL bypassed the object cache
	return $res > 0;
}

/**
 * On a confirmed (paid) booking, count the voucher usage EXACTLY once. Idempotency
 * is anchored in the booking-events log (VOUCHER_COUNTED), so it survives webhook
 * retries even after a transient would have expired.
 */
add_action( 'mgk_booking_confirmed', function ( $booking_id ) {
	$booking_id = (int) $booking_id;
	if ( ! function_exists( 'mgk_get_booking_row' ) ) return;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row || empty( $row['voucher_code'] ) ) return;

	if ( mgk_booking_has_event( $booking_id, 'VOUCHER_COUNTED' ) ) return; // already counted
	$counted = class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' )
		? \Margick\Commerce\Wp\VoucherRepository::consume( 'booking', (string) $booking_id )
		: false;
	if ( ! $counted && mgk_voucher_mark_used( $row['voucher_code'] ) ) {
		mgk_voucher_sync_post_to_module( (int) mgk_voucher_find( $row['voucher_code'] ) );
		$counted = true;
	}
	if ( $counted ) {
		mgk_voucher_refresh_legacy_count( $row['voucher_code'] );
		mgk_log_booking_event( $booking_id, 'VOUCHER_COUNTED', [
			'metadata' => [ 'code' => $row['voucher_code'] ],
		] );
	}
}, 20 );

/** Unpaid holds release quota; consumed reservations deliberately remain counted. */
$mgk_release_booking_voucher = function ( $booking_id ) {
	if ( class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) ) {
		\Margick\Commerce\Wp\VoucherRepository::release( 'booking', (string) (int) $booking_id );
	}
};
add_action( 'mgk_booking_hold_expired', $mgk_release_booking_voucher, 10, 1 );
add_action( 'mgk_booking_hold_released', $mgk_release_booking_voucher, 10, 1 );
add_action( 'mgk_booking_cancelled', $mgk_release_booking_voucher, 10, 1 );
add_action( 'mgk_booking_status_changed', function ( $booking_id, $old_status, $new_status ) use ( $mgk_release_booking_voucher ) {
	if ( in_array( $new_status, [ 'CANCELLED', 'EXPIRED' ], true ) ) $mgk_release_booking_voucher( $booking_id );
}, 10, 3 );

/**
 * Reserve the voucher actually selected by DiscountEngine for a held booking.
 * Passing a quote without an applied voucher removes any prior reservation.
 *
 * @return array{ok:bool,reason:string,message:string}|array
 */
function mgk_voucher_reserve_for_booking( $booking_id, $booking, $quote, $ctx = [] ) {
	if ( ! class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) ) return [ 'ok' => true, 'reason' => 'legacy', 'message' => '' ];
	$code = '';
	$amount = 0.0;
	foreach ( (array) ( $quote['discounts_applied'] ?? [] ) as $discount ) {
		$key = (string) ( $discount['key'] ?? '' );
		if ( strpos( $key, 'voucher:' ) === 0 ) {
			$code = substr( $key, 8 );
			$amount = (float) ( $discount['amount'] ?? 0 );
			break;
		}
	}
	if ( $code === '' || $amount <= 0 ) {
		\Margick\Commerce\Wp\VoucherRepository::release( 'booking', (string) (int) $booking_id );
		return [ 'ok' => true, 'reason' => 'removed', 'message' => '' ];
	}

	$item_type = (string) ( $quote['item_type'] ?? '' );
	if ( $item_type === '' ) {
		$lesson_type = (string) ( $booking['lesson_type'] ?? 'TRIAL' );
		$item_type = function_exists( 'mgk_package_plan_from_lesson_type' )
			? ( mgk_package_plan_from_lesson_type( $lesson_type ) ?: 'trial' )
			: ( $lesson_type === 'TRIAL' ? 'trial' : strtolower( $lesson_type ) );
	}
	$currency = strtoupper( (string) ( $quote['currency'] ?? $booking['currency'] ?? 'SGD' ) );
	$context = new \Margick\Commerce\Voucher\Domain\VoucherContext(
		\Margick\Commerce\Domain\Money::ofMajor( (float) ( $quote['advertised'] ?? $quote['base'] ?? 0 ), $currency ),
		[ $item_type ],
		mgk_voucher_customer_key( (array) $ctx ),
		mgk_voucher_customer_is_first_order( $ctx )
	);
	$ttl = 1800;
	if ( ! empty( $booking['hold_expires_at_utc'] ) ) {
		$ttl = max( 60, strtotime( $booking['hold_expires_at_utc'] . ' UTC' ) - time() );
	}
	return \Margick\Commerce\Wp\VoucherRepository::reserve(
		$code,
		$context,
		'booking',
		(string) (int) $booking_id,
		$ttl,
		'booking-voucher:' . (int) $booking_id . ':' . $code,
		[ 'booking_code' => (string) ( $booking['booking_code'] ?? '' ), 'quote_total' => (float) ( $quote['total'] ?? 0 ) ],
		\Margick\Commerce\Domain\Money::ofMajor( $amount, $currency )
	);
}

/** True if a booking already has at least one event of the given type. */
function mgk_booking_has_event( $booking_id, $event_type ) {
	global $wpdb;
	if ( ! function_exists( 'mgk_booking_table' ) ) return false;
	$t = mgk_booking_table( 'events' );
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$t} WHERE booking_id = %d AND event_type = %s",
		(int) $booking_id, $event_type
	) ) > 0;
}

/* ── Admin edit screen: friendly meta box ───────────────────────────────── */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'mgk_voucher_cfg', 'Voucher settings', 'mgk_voucher_metabox', 'mgk_voucher', 'normal', 'high' );
} );

function mgk_voucher_metabox( $post ) {
	wp_nonce_field( 'mgk_voucher_save', 'mgk_voucher_nonce' );
	$g = function ( $k, $d = '' ) use ( $post ) { $v = get_post_meta( $post->ID, $k, true ); return $v === '' ? $d : $v; };
	$code   = $g( 'mgk_v_code' );
	$type   = $g( 'mgk_v_type', 'pct' );
	$value  = $g( 'mgk_v_value' );
	$applies= $g( 'mgk_v_applies_to', 'all' );
	$usage  = class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' )
		? \Margick\Commerce\Wp\VoucherRepository::usageStats( (string) $code )
		: [ 'reserved' => 0, 'consumed' => (int) $g( 'mgk_v_used_count', 0 ) ];
	?>
	<style>
		.mgk-vform{display:grid;grid-template-columns:1fr 1fr;gap:16px 24px;max-width:760px;margin-top:8px}
		.mgk-vform .full{grid-column:1/-1}
		.mgk-vform label{display:block;font-weight:600;margin-bottom:4px}
		.mgk-vform input[type=text],.mgk-vform input[type=number],.mgk-vform input[type=date],.mgk-vform select{width:100%}
		.mgk-vform .hint{color:#646970;font-size:12px;margin-top:3px}
		.mgk-vrow-inline{display:flex;gap:8px;align-items:center}
		.mgk-vchk{display:flex;gap:8px;align-items:flex-start;background:#f6f7f7;border:1px solid #dcdcde;padding:10px;border-radius:4px}
	</style>
	<div class="mgk-vform">
		<div>
			<label for="mgk_v_code">Code</label>
			<input type="text" id="mgk_v_code" name="mgk_v_code" value="<?php echo esc_attr( $code ); ?>" placeholder="WELCOME10" style="text-transform:uppercase">
			<p class="hint">What the customer types at checkout. Auto-uppercased.</p>
		</div>
		<div>
			<label>Discount</label>
			<div class="mgk-vrow-inline">
				<select name="mgk_v_type" style="max-width:130px">
					<option value="pct"   <?php selected( $type, 'pct' ); ?>>Percent %</option>
					<option value="fixed" <?php selected( $type, 'fixed' ); ?>>Fixed $</option>
				</select>
				<input type="number" name="mgk_v_value" value="<?php echo esc_attr( $value ); ?>" step="0.01" min="0" placeholder="10">
			</div>
			<p class="hint">e.g. <strong>10</strong> = 10% off (Percent) or $10 off (Fixed).</p>
		</div>
		<div>
			<label for="mgk_v_min_spend">Minimum spend ($)</label>
			<input type="number" id="mgk_v_min_spend" name="mgk_v_min_spend" value="<?php echo esc_attr( $g( 'mgk_v_min_spend', 0 ) ); ?>" step="0.01" min="0">
			<p class="hint">0 = no minimum.</p>
		</div>
		<div>
			<label for="mgk_v_max_uses">Max uses</label>
			<input type="number" id="mgk_v_max_uses" name="mgk_v_max_uses" value="<?php echo esc_attr( $g( 'mgk_v_max_uses', 0 ) ); ?>" step="1" min="0">
			<p class="hint">0 = unlimited. Consumed: <strong><?php echo (int) $usage['consumed']; ?></strong>; currently reserved: <strong><?php echo (int) $usage['reserved']; ?></strong>.</p>
		</div>
		<div>
			<label for="mgk_v_max_uses_per_customer">Max uses per customer</label>
			<input type="number" id="mgk_v_max_uses_per_customer" name="mgk_v_max_uses_per_customer" value="<?php echo esc_attr( $g( 'mgk_v_max_uses_per_customer', 0 ) ); ?>" step="1" min="0">
			<p class="hint">0 = unlimited. Customer identity uses account email.</p>
		</div>
		<div>
			<label for="mgk_v_customer_key">Only this customer (optional)</label>
			<input type="text" id="mgk_v_customer_key" name="mgk_v_customer_key" value="<?php echo esc_attr( $g( 'mgk_v_customer_key' ) ); ?>" placeholder="parent@example.com">
			<p class="hint">Leave blank for a public code.</p>
		</div>
		<div>
			<label for="mgk_v_valid_from">Valid from</label>
			<input type="date" id="mgk_v_valid_from" name="mgk_v_valid_from" value="<?php echo esc_attr( $g( 'mgk_v_valid_from' ) ); ?>">
		</div>
		<div>
			<label for="mgk_v_valid_to">Valid until</label>
			<input type="date" id="mgk_v_valid_to" name="mgk_v_valid_to" value="<?php echo esc_attr( $g( 'mgk_v_valid_to' ) ); ?>">
		</div>
		<div>
			<label for="mgk_v_applies_to">Applies to</label>
			<select id="mgk_v_applies_to" name="mgk_v_applies_to">
				<option value="all"     <?php selected( $applies, 'all' ); ?>>Everything</option>
				<option value="trial"   <?php selected( $applies, 'trial' ); ?>>Trial lessons only</option>
				<option value="package" <?php selected( $applies, 'package' ); ?>>Packages only</option>
				<option value="retail"  <?php selected( $applies, 'retail' ); ?>>Store purchases only (retail)</option>
			</select>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_first_order_only" name="mgk_v_first_order_only" value="1" <?php checked( (int) $g( 'mgk_v_first_order_only', 0 ), 1 ); ?>>
			<label for="mgk_v_first_order_only" style="font-weight:600;margin:0">First order only</label>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_is_stackable" name="mgk_v_is_stackable" value="1" <?php checked( (int) $g( 'mgk_v_is_stackable', 0 ), 1 ); ?>>
			<label for="mgk_v_is_stackable" style="font-weight:600;margin:0">
				Allow stacking with automatic discounts (sibling / returning)
				<span class="hint" style="display:block;font-weight:400">Off (default) = this voucher is the only discount applied when used. Total still capped at <?php echo (int) mgk_discount_rule( 'stack_cap_pct', 25 ); ?>%.</span>
			</label>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_exclude_sale" name="mgk_v_exclude_sale" value="1" <?php checked( (int) $g( 'mgk_v_exclude_sale', 0 ), 1 ); ?>>
			<label for="mgk_v_exclude_sale" style="font-weight:600;margin:0">Exclude already-discounted items (retail)</label>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_active" name="mgk_v_active" value="1" <?php checked( (int) $g( 'mgk_v_active', 1 ), 1 ); ?>>
			<label for="mgk_v_active" style="font-weight:600;margin:0">Active</label>
		</div>
	</div>
	<?php
}

add_action( 'save_post_mgk_voucher', function ( $post_id ) {
	if ( ! isset( $_POST['mgk_voucher_nonce'] ) || ! wp_verify_nonce( $_POST['mgk_voucher_nonce'], 'mgk_voucher_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$text = [ 'mgk_v_valid_from', 'mgk_v_valid_to', 'mgk_v_applies_to', 'mgk_v_customer_key' ];
	foreach ( $text as $k ) {
		if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
	}
	update_post_meta( $post_id, 'mgk_v_code', mgk_voucher_norm_code( $_POST['mgk_v_code'] ?? '' ) );
	update_post_meta( $post_id, 'mgk_v_type', ( ( $_POST['mgk_v_type'] ?? 'pct' ) === 'fixed' ) ? 'fixed' : 'pct' );
	update_post_meta( $post_id, 'mgk_v_value', (float) ( $_POST['mgk_v_value'] ?? 0 ) );
	update_post_meta( $post_id, 'mgk_v_min_spend', (float) ( $_POST['mgk_v_min_spend'] ?? 0 ) );
	update_post_meta( $post_id, 'mgk_v_max_uses', (int) ( $_POST['mgk_v_max_uses'] ?? 0 ) );
	update_post_meta( $post_id, 'mgk_v_max_uses_per_customer', (int) ( $_POST['mgk_v_max_uses_per_customer'] ?? 0 ) );
	foreach ( [ 'mgk_v_is_stackable', 'mgk_v_exclude_sale', 'mgk_v_active', 'mgk_v_first_order_only' ] as $cb ) {
		update_post_meta( $post_id, $cb, isset( $_POST[ $cb ] ) ? 1 : 0 );
	}
	if ( get_post_meta( $post_id, 'mgk_v_used_count', true ) === '' ) {
		update_post_meta( $post_id, 'mgk_v_used_count', 0 );
	}
	if ( ! mgk_voucher_sync_post_to_module( $post_id ) ) {
		set_transient( 'mgk_voucher_sync_error_' . $post_id, 1, MINUTE_IN_SECONDS );
	}
} );

add_action( 'admin_notices', function () {
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	if ( ! $post_id || ! get_transient( 'mgk_voucher_sync_error_' . $post_id ) ) return;
	delete_transient( 'mgk_voucher_sync_error_' . $post_id );
	echo '<div class="notice notice-error"><p>Voucher was not activated. Use a unique code and verify the discount settings.</p></div>';
} );

add_action( 'trashed_post', function ( $post_id ) {
	if ( get_post_type( $post_id ) !== 'mgk_voucher' || ! class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) ) return;
	\Margick\Commerce\Wp\VoucherRepository::setStatus( (string) get_post_meta( $post_id, 'mgk_v_code', true ), 'archived' );
} );
add_action( 'before_delete_post', function ( $post_id ) {
	if ( get_post_type( $post_id ) !== 'mgk_voucher' || ! class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' ) ) return;
	\Margick\Commerce\Wp\VoucherRepository::setStatus( (string) get_post_meta( $post_id, 'mgk_v_code', true ), 'archived' );
} );
add_action( 'untrashed_post', 'mgk_voucher_sync_post_to_module' );

/* ── Admin list columns ─────────────────────────────────────────────────── */

add_filter( 'manage_mgk_voucher_posts_columns', function ( $cols ) {
	$new = [ 'cb' => $cols['cb'] ?? '', 'title' => 'Name' ];
	$new['mgk_code']    = 'Code';
	$new['mgk_disc']    = 'Discount';
	$new['mgk_applies'] = 'Applies to';
	$new['mgk_uses']    = 'Used';
	$new['mgk_status']  = 'Status';
	return $new;
} );

add_action( 'manage_mgk_voucher_posts_custom_column', function ( $col, $post_id ) {
	$g = function ( $k, $d = '' ) use ( $post_id ) { $v = get_post_meta( $post_id, $k, true ); return $v === '' ? $d : $v; };
	switch ( $col ) {
		case 'mgk_code':
			echo '<code style="font-size:13px">' . esc_html( $g( 'mgk_v_code', '—' ) ) . '</code>';
			break;
		case 'mgk_disc':
			$v = (float) $g( 'mgk_v_value', 0 );
			echo $g( 'mgk_v_type', 'pct' ) === 'fixed' ? '$' . esc_html( number_format( $v, 2 ) ) : esc_html( (int) $v ) . '%';
			break;
		case 'mgk_applies':
			echo esc_html( ucfirst( $g( 'mgk_v_applies_to', 'all' ) ) );
			break;
		case 'mgk_uses':
			$max = (int) $g( 'mgk_v_max_uses', 0 );
			$stats = class_exists( '\\Margick\\Commerce\\Wp\\VoucherRepository' )
				? \Margick\Commerce\Wp\VoucherRepository::usageStats( (string) $g( 'mgk_v_code' ) )
				: [ 'consumed' => (int) $g( 'mgk_v_used_count', 0 ), 'reserved' => 0 ];
			echo (int) $stats['consumed'] . ( $max ? ' / ' . $max : ' / ∞' );
			if ( ! empty( $stats['reserved'] ) ) echo ' <small>(' . (int) $stats['reserved'] . ' reserved)</small>';
			break;
		case 'mgk_status':
			$active = (int) $g( 'mgk_v_active', 1 ) === 1;
			$to     = $g( 'mgk_v_valid_to' );
			$expired= $to && gmdate( 'Y-m-d' ) > $to;
			if ( $expired )      echo '<span style="color:#b32d2e">Expired</span>';
			elseif ( $active )   echo '<span style="color:#1a7f37">● Active</span>';
			else                 echo '<span style="color:#646970">Inactive</span>';
			break;
	}
}, 10, 2 );
