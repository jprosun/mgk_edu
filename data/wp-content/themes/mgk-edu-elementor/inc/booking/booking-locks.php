<?php
/**
 * MGK Booking Engine — Phase 0.5 · Atomic hold + lock engine (LOCKED DATA CORE).
 * ==============================================================================
 * The heart of double-book prevention. A hold is created inside a single DB
 * transaction:
 *
 *   1. DELETE expired HOLD locks for this tutor (painpoint C lazy cleanup, in-txn).
 *   2. INSERT the HELD booking row.
 *   3. INSERT one ACTIVE lock row per 15-min block of the slot.
 *      └─ if ANY insert hits uniq_active_block → another hold/booking owns an
 *         overlapping block → ROLLBACK → 409 SLOT_ALREADY_TAKEN.
 *   4. Stamp the booking with hold_expires_at + COMMIT + audit event.
 *
 * Correctness comes from the UNIQUE INSERT in step 3, NOT from a pre-read — two
 * concurrent holds both reach step 3 and exactly one wins the insert race.
 *
 * Also owns: hold→booking lock promotion (HOLD→BOOKING on payment), release, and
 * the expire job (Step 5 calls mgk_engine_expire_holds()).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_HOLD_SECONDS' ) ) define( 'MGK_HOLD_SECONDS', 600 ); // 10-min trial hold

/**
 * Create an atomic hold. Returns the booking row id on success, or WP_Error
 * (status 409 conflict / 400 bad input / 422 unavailable).
 *
 * @param array $req tutor_id,start_at_utc,end_at_utc,student_name,subject,
 *                   lesson_type,price_amount,currency,lead_id,parent_user_id,
 *                   idempotency_key
 */
function mgk_engine_hold_slot( array $req ) {
	global $wpdb;

	$tutor_id  = (int) ( $req['tutor_id'] ?? 0 );
	$start_utc = mgk_engine_clean_dt( $req['start_at_utc'] ?? '' );
	$end_utc   = mgk_engine_clean_dt( $req['end_at_utc'] ?? '' );
	$idem      = isset( $req['idempotency_key'] ) ? sanitize_text_field( $req['idempotency_key'] ) : '';

	if ( $tutor_id <= 0 || get_post_type( $tutor_id ) !== 'mg_teacher' ) {
		return new WP_Error( 'mgk_bad_tutor', 'Tutor not found.', [ 'status' => 400 ] );
	}
	if ( ! $start_utc || ! $end_utc || $end_utc <= $start_utc ) {
		return new WP_Error( 'mgk_bad_slot', 'Invalid slot times.', [ 'status' => 400 ] );
	}

	// Idempotency: same key returns the existing booking instead of a new hold.
	if ( $idem ) {
		$existing = mgk_engine_find_by_idempotency( $idem );
		if ( $existing ) return (int) $existing['id'];
	}

	$blocks = mgk_expand_to_blocks( $start_utc, $end_utc );
	if ( empty( $blocks ) ) {
		return new WP_Error( 'mgk_bad_slot', 'Slot has no blocks.', [ 'status' => 400 ] );
	}

	$bookings = mgk_booking_table( 'bookings' );
	$locks    = mgk_booking_table( 'locks' );
	$now      = mgk_booking_now_utc();
	$expires  = gmdate( 'Y-m-d H:i:s', time() + MGK_HOLD_SECONDS );

	// Price is server-authoritative via the unified quote engine (mgk_quote): the
	// held price already includes the headline trial discount AND any loyalty /
	// voucher discount this parent is eligible for, so the displayed total can
	// never diverge from what Stripe charges. A client-supplied price is ignored
	// for trials (recomputed) — the only trusted input is the voucher code.
	$lesson_type = sanitize_text_field( $req['lesson_type'] ?? 'TRIAL' );
	$price       = isset( $req['price_amount'] ) ? (float) $req['price_amount'] : 0;
	$base_amount = 0.0;
	$discount_json = null;
	$voucher_code  = '';

	if ( function_exists( 'mgk_quote_trial_for_tutor' ) && $lesson_type === 'TRIAL' ) {
		$qctx = mgk_engine_quote_context( $req, $tutor_id );
		$q    = mgk_quote_trial_for_tutor( $tutor_id, $qctx );
		$price         = (float) $q['total'];
		$base_amount   = (float) $q['base'];
		$discount_json = wp_json_encode( $q['discounts_applied'] );
		foreach ( (array) $q['discounts_applied'] as $d ) {
			if ( strpos( (string) $d['key'], 'voucher:' ) === 0 ) $voucher_code = substr( $d['key'], 8 );
		}
	} elseif ( $price <= 0 ) {
		$price = mgk_engine_trial_price_for_tutor( $tutor_id );
	}

	// Defensive availability pre-check (cheap reject for clearly-gone slots).
	// The authoritative guard is the UNIQUE insert below.
	if ( ! mgk_engine_slot_is_bookable( $tutor_id, $start_utc, $end_utc ) ) {
		return new WP_Error( 'mgk_slot_taken', 'This slot is no longer available.', [ 'status' => 409, 'alternatives' => mgk_engine_alternatives( $tutor_id, $start_utc ) ] );
	}

	$wpdb->query( 'START TRANSACTION' );

	// 1. Lazy cleanup of expired HOLD locks for this tutor (painpoint C, in-txn).
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM {$locks} WHERE tutor_post_id = %d AND lock_type = 'HOLD' AND expires_at_utc IS NOT NULL AND expires_at_utc <= %s",
		$tutor_id, $now
	) );

	// 2. Insert HELD booking.
	$code = mgk_engine_generate_booking_code();
	$ins = $wpdb->insert( $bookings, [
		'booking_code'        => $code,
		'tutor_post_id'       => $tutor_id,
		'lead_id'             => isset( $req['lead_id'] ) ? (int) $req['lead_id'] : null,
		// Only attribute the booking to the session user when that user is a real
		// PARENT booking for themselves. An admin/staff/editor browsing the site
		// must NOT become the booking's parent — otherwise the confirm-time claim
		// (mgk_parent_claim_on_booking) sees a pre-set parent_user_id and skips, so
		// the real parent account + child are never created from the booking email.
		'parent_user_id'      => isset( $req['parent_user_id'] )
			? (int) $req['parent_user_id']
			: ( ( function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) ? ( get_current_user_id() ?: null ) : null ),
		'student_name'        => isset( $req['student_name'] ) ? sanitize_text_field( $req['student_name'] ) : null,
		'subject'             => isset( $req['subject'] ) ? sanitize_text_field( $req['subject'] ) : null,
		'lesson_type'         => sanitize_text_field( $req['lesson_type'] ?? 'TRIAL' ),
		'slot_key'            => mgk_slot_key( $tutor_id, $start_utc, $end_utc ),
		'start_at_utc'        => $start_utc,
		'end_at_utc'          => $end_utc,
		'timezone'            => MGK_BOOKING_TZ,
		'status'              => 'HELD',
		'payment_status'      => 'PENDING',
		'price_amount'        => $price,
			'base_amount'         => $base_amount,
			'discount_applied'    => $discount_json,
			'voucher_code'        => $voucher_code ?: null,
		'currency'            => sanitize_text_field( $req['currency'] ?? 'SGD' ),
		'idempotency_key'     => $idem ?: null,
		'hold_expires_at_utc' => $expires,
		'created_at_utc'      => $now,
		'updated_at_utc'      => $now,
	] );

	if ( ! $ins ) {
		$wpdb->query( 'ROLLBACK' );
		// Idempotency unique race: another request with the same key won.
		if ( $idem ) {
			$existing = mgk_engine_find_by_idempotency( $idem );
			if ( $existing ) return (int) $existing['id'];
		}
		return new WP_Error( 'mgk_hold_failed', 'Could not create hold.', [ 'status' => 500 ] );
	}
	$booking_id = (int) $wpdb->insert_id;

	// 3. Insert ACTIVE block locks. ANY duplicate → conflict → rollback.
	$prev_errors = $wpdb->show_errors;
	$wpdb->hide_errors();
	foreach ( $blocks as $blk ) {
		$ok = $wpdb->insert( $locks, [
			'tutor_post_id'      => $tutor_id,
			'booking_id'         => $booking_id,
			'block_start_at_utc' => $blk,
			'lock_type'          => 'HOLD',
			'expires_at_utc'     => $expires,
			'created_at_utc'     => $now,
		] );
		if ( ! $ok ) {
			$wpdb->show_errors = $prev_errors;
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error( 'mgk_slot_taken', 'This slot was just taken. Please choose another time.', [
				'status'       => 409,
				'alternatives' => mgk_engine_alternatives( $tutor_id, $start_utc ),
			] );
		}
	}
	$wpdb->show_errors = $prev_errors;

	$wpdb->query( 'COMMIT' );

	mgk_log_booking_event( $booking_id, 'SLOT_HELD', [
		'new_status' => 'HELD',
		'actor_type' => 'PARENT',
		'metadata'   => [ 'slot_key' => mgk_slot_key( $tutor_id, $start_utc, $end_utc ), 'expires_at_utc' => $expires ],
	] );

	// Reflect into the legacy lead state machine if a lead is attached.
	if ( ! empty( $req['lead_id'] ) && function_exists( 'mgk_lead_transition' ) && function_exists( 'mgk_lead_can_transition' ) ) {
		$lead_id = (int) $req['lead_id'];
		$cur = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: '';
		if ( $cur && mgk_lead_can_transition( $cur, MGK_LEAD_SLOT_HELD ) ) {
			mgk_lead_transition( $lead_id, MGK_LEAD_SLOT_HELD );
		}
	}

	return $booking_id;
}

/** Quick bookable check (not authoritative; UNIQUE insert is). */
function mgk_engine_slot_is_bookable( $tutor_id, $start_utc, $end_utc ) {
	$active = mgk_get_active_lock_block_set( $tutor_id );
	foreach ( mgk_expand_to_blocks( $start_utc, $end_utc ) as $blk ) {
		if ( isset( $active[ $blk ] ) ) return false;
	}
	$busy = mgk_get_busy_booking_intervals( $tutor_id, $start_utc, $end_utc );
	$utc = new DateTimeZone( 'UTC' );
	try {
		$s = new DateTime( $start_utc, $utc ); $e = new DateTime( $end_utc, $utc );
	} catch ( Exception $ex ) { return false; }
	foreach ( $busy as $b ) {
		if ( $s < $b['end'] && $e > $b['start'] ) return false;
	}
	return true;
}

/** Up to 3 alternative slots near a taken slot (same tutor, same week onward). */
function mgk_engine_alternatives( $tutor_id, $around_start_utc ) {
	$tz = mgk_booking_tz();
	try {
		$from = new DateTime( $around_start_utc, new DateTimeZone( 'UTC' ) );
		$from->setTimezone( $tz );
	} catch ( Exception $e ) {
		$from = new DateTime( 'now', $tz );
	}
	$to = ( clone $from )->modify( '+10 days' );
	$res = mgk_engine_available_slots( $tutor_id, $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );
	return array_slice( array_map( function ( $s ) {
		return [ 'slot_key' => $s['slot_key'], 'start_at_utc' => $s['start_at_utc'], 'display_start' => $s['display_start'], 'display_day' => $s['display_day'] ];
	}, $res['slots'] ), 0, 3 );
}

/**
 * Promote a booking's HOLD locks to permanent BOOKING locks (on payment).
 * If the hold's locks were already expired+cron-deleted (the "hold expired but
 * slot still free → confirm" case, §18), re-create them as BOOKING locks so the
 * confirmed slot is actually protected. Caller MUST have already verified no
 * other booking owns these blocks (mgk_stripe_slot_lost) — typically inside the
 * confirm transaction.
 */
function mgk_engine_promote_locks_to_booking( $booking_id ) {
	global $wpdb;
	$locks = mgk_booking_table( 'locks' );
	$booking_id = (int) $booking_id;

	$updated = $wpdb->query( $wpdb->prepare(
		"UPDATE {$locks} SET lock_type = 'BOOKING', expires_at_utc = NULL WHERE booking_id = %d",
		$booking_id
	) );
	if ( $updated > 0 ) return $updated;

	// No locks existed (expired + cleaned). Re-create BOOKING locks for the slot.
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return 0;
	$blocks = mgk_expand_to_blocks( $row['start_at_utc'], $row['end_at_utc'] );
	$now = mgk_booking_now_utc();
	$created = 0;
	foreach ( $blocks as $blk ) {
		$ok = $wpdb->insert( $locks, [
			'tutor_post_id'      => (int) $row['tutor_post_id'],
			'booking_id'         => $booking_id,
			'block_start_at_utc' => $blk,
			'lock_type'          => 'BOOKING',
			'expires_at_utc'     => null,
			'created_at_utc'     => $now,
		] );
		if ( $ok ) $created++;
	}
	return $created;
}

/** Release a booking's locks (DELETE — painpoint A) and mark it released. */
function mgk_engine_release_locks( $booking_id ) {
	global $wpdb;
	$locks = mgk_booking_table( 'locks' );
	return $wpdb->query( $wpdb->prepare(
		"DELETE FROM {$locks} WHERE booking_id = %d",
		(int) $booking_id
	) );
}

/**
 * User-initiated release of an UNPAID hold (parent abandons checkout). Frees the
 * slot immediately instead of waiting for the 10-min TTL. Refuses to touch a
 * paid/confirmed booking. Returns true on release, WP_Error otherwise.
 */
function mgk_engine_release_hold( $booking_id ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) {
		return new WP_Error( 'mgk_no_booking', 'Booking not found.', [ 'status' => 404 ] );
	}
	if ( ! in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT' ], true ) ) {
		return new WP_Error( 'mgk_not_releasable', 'Only an unpaid hold can be released.', [ 'status' => 409 ] );
	}
	if ( $row['payment_status'] === 'PAID' ) {
		return new WP_Error( 'mgk_already_paid', 'Booking already paid.', [ 'status' => 409 ] );
	}

	mgk_engine_release_locks( $booking_id );
	$now = mgk_booking_now_utc();
	$wpdb->update( mgk_booking_table( 'bookings' ),
		[ 'status' => 'EXPIRED', 'payment_status' => 'EXPIRED', 'updated_at_utc' => $now ],
		[ 'id' => $booking_id ]
	);
	mgk_log_booking_event( $booking_id, 'HOLD_RELEASED', [
		'old_status' => $row['status'], 'new_status' => 'EXPIRED', 'actor_type' => 'PARENT',
	] );
	return true;
}

/* ── Helpers ───────────────────────────────────────────────────────────── */

/** Normalize an incoming datetime to 'Y-m-d H:i:s' UTC, or '' if invalid. */
function mgk_engine_clean_dt( $s ) {
	$s = trim( (string) $s );
	if ( ! $s ) return '';
	try {
		$dt = new DateTime( $s ); // honors trailing Z / offset
		$dt->setTimezone( new DateTimeZone( 'UTC' ) );
		return $dt->format( 'Y-m-d H:i:s' );
	} catch ( Exception $e ) {
		return '';
	}
}

/** Find a booking by idempotency key. */
function mgk_engine_find_by_idempotency( $key ) {
	global $wpdb;
	$bookings = mgk_booking_table( 'bookings' );
	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$bookings} WHERE idempotency_key = %s LIMIT 1",
		sanitize_text_field( $key )
	), ARRAY_A );
	return $row ?: null;
}

/**
 * Build the loyalty/voucher context for a quote from a hold request: the parent
 * (explicit user id, else the lead's email) and any voucher code. Read-only —
 * used only to decide which discounts this customer is eligible for.
 */
function mgk_engine_quote_context( $req, $tutor_id ) {
	$ctx = [
		'parent_user_id' => isset( $req['parent_user_id'] ) ? (int) $req['parent_user_id'] : 0,
		'parent_email'   => isset( $req['parent_email'] ) ? sanitize_email( $req['parent_email'] ) : '',
		'child_id'       => isset( $req['child_id'] ) ? (int) $req['child_id'] : 0,
		'voucher_code'   => isset( $req['voucher_code'] ) ? sanitize_text_field( $req['voucher_code'] ) : '',
	];
	if ( ! $ctx['parent_user_id'] && function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) {
		$ctx['parent_user_id'] = (int) get_current_user_id();
	}
	if ( $ctx['parent_email'] === '' && ! empty( $req['lead_id'] ) && function_exists( 'mgk_lead_contact' ) ) {
		$c = mgk_lead_contact( (int) $req['lead_id'] );
		if ( ! empty( $c['email'] ) ) $ctx['parent_email'] = sanitize_email( $c['email'] );
	}
	return $ctx;
}

/**
 * Server-authoritative trial price for a tutor (SGD). Reuses the canonical S09
 * offer calc (mgk_calculate_trial_offer) from the tutor's hourly rate meta, so
 * the held booking's price matches what S09/S11 quote and what Stripe charges.
 */
function mgk_engine_trial_price_for_tutor( $tutor_id ) {
	$rate = (int) get_post_meta( (int) $tutor_id, 'mgk_rate_num', true );
	if ( function_exists( 'mgk_calculate_trial_offer' ) ) {
		$offer = mgk_calculate_trial_offer( [ 'rate_num' => $rate ] );
		return (float) ( $offer['trial_price'] ?? 0 );
	}
	// Fallback: 40% off, nearest $5.
	if ( $rate <= 0 ) $rate = 65;
	return (float) ( round( ( $rate * 0.6 ) / 5 ) * 5 );
}

/** Generate a unique, human-readable booking code MGK-YYYYMMDD-XXXXXX. */
function mgk_engine_generate_booking_code() {
	$date = gmdate( 'Ymd' );
	// wp_generate_password gives entropy without Date/random restrictions.
	$rand = strtoupper( substr( wp_generate_password( 8, false, false ), 0, 6 ) );
	return 'MGK-' . $date . '-' . $rand;
}
