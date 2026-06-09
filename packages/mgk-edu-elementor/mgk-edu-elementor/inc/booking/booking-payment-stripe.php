<?php
/**
 * MGK Booking Engine — Phase 0.5 · Stripe Checkout + webhook (LOCKED DATA CORE).
 * =============================================================================
 * Thin HTTP client against Stripe's REST API (no SDK / Composer). Two modes:
 *
 *   LIVE  — when a secret key is configured: real Checkout Session via
 *           wp_remote_post, real HMAC-SHA256 webhook signature verification.
 *   MOCK  — when no key is set: fakes a checkout session + exposes a mock-confirm
 *           endpoint so the whole hold→pay→confirm flow is testable now. Paste a
 *           Stripe test key and it flips to LIVE with no code change.
 *
 * The golden rule (plan §3.2, §17): a booking becomes CONFIRMED only from a
 * verified webhook (or admin override) — NEVER from the browser redirect.
 *
 * Settings (wp options): mgk_stripe_secret, mgk_stripe_pk, mgk_stripe_webhook_secret.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Mode + settings ───────────────────────────────────────────────────── */

// Keys come from MGK Site Settings → Payments (theme_mod), with the legacy
// wp-option as a fallback so a `wp option update mgk_stripe_secret …` still works.
function mgk_stripe_secret_key() {
	$v = function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_secret', '' ) ) : '';
	return $v !== '' ? $v : trim( (string) get_option( 'mgk_stripe_secret', '' ) );
}
function mgk_stripe_webhook_secret() {
	$v = function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_webhook_secret', '' ) ) : '';
	return $v !== '' ? $v : trim( (string) get_option( 'mgk_stripe_webhook_secret', '' ) );
}
function mgk_stripe_is_live()      { return mgk_stripe_secret_key() !== ''; }

/* ── Create Checkout Session ───────────────────────────────────────────── */

/**
 * Create a Stripe Checkout Session for a HELD booking. Moves the booking to
 * PENDING_PAYMENT and records a PENDING payment row.
 *
 * @return array|WP_Error  ['checkout_url'=>..., 'payment_id'=>..., 'mode'=>live|mock]
 */
function mgk_stripe_create_checkout( $booking_id, $return_url = '' ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) {
		return new WP_Error( 'mgk_no_booking', 'Booking not found.', [ 'status' => 404 ] );
	}
	if ( $row['status'] !== 'HELD' && $row['status'] !== 'PENDING_PAYMENT' ) {
		return new WP_Error( 'mgk_bad_state', 'Booking is not awaiting payment.', [ 'status' => 409 ] );
	}
	if ( $row['payment_status'] === 'PAID' ) {
		return new WP_Error( 'mgk_already_paid', 'Booking already paid.', [ 'status' => 409 ] );
	}
	// Hold must still be valid.
	if ( $row['status'] === 'HELD' && ! empty( $row['hold_expires_at_utc'] )
		&& strtotime( $row['hold_expires_at_utc'] . ' UTC' ) <= time() ) {
		return new WP_Error( 'mgk_hold_expired', 'Your hold has expired. Please pick a slot again.', [ 'status' => 410 ] );
	}

	$amount   = (float) $row['price_amount'];
	$currency = strtolower( $row['currency'] ?: 'sgd' );
	$amount_minor = (int) round( $amount * 100 ); // Stripe uses smallest unit
	$return_url = $return_url ?: home_url( '/trial-confirmed/?booking=' . rawurlencode( $row['booking_code'] ) );

	$payments = mgk_booking_table( 'payments' );
	$now = mgk_booking_now_utc();

	if ( mgk_stripe_is_live() ) {
		$resp = mgk_stripe_api_post( 'checkout/sessions', [
			'mode'        => 'payment',
			'success_url' => add_query_arg( 'session_id', '{CHECKOUT_SESSION_ID}', $return_url ),
			'cancel_url'  => $return_url,
			'client_reference_id' => $row['booking_code'],
			'metadata[booking_id]' => $booking_id,
			'metadata[booking_code]' => $row['booking_code'],
			'line_items[0][quantity]' => 1,
			'line_items[0][price_data][currency]' => $currency,
			'line_items[0][price_data][unit_amount]' => $amount_minor,
			'line_items[0][price_data][product_data][name]' => 'Trial lesson — ' . $row['booking_code'],
		] );
		if ( is_wp_error( $resp ) ) return $resp;
		$session_id  = $resp['id'] ?? '';
		$checkout_url = $resp['url'] ?? '';
		$intent_id    = $resp['payment_intent'] ?? null;
		$mode = 'live';
	} else {
		// MOCK: synthesize a session id + a local "checkout" URL that simulates
		// paying and then posting a mock webhook.
		$session_id   = 'cs_mock_' . substr( md5( $booking_id . $now ), 0, 20 );
		$intent_id    = 'pi_mock_' . substr( md5( 'pi' . $booking_id . $now ), 0, 20 );
		$checkout_url = add_query_arg( [
			'mgk_mock_pay' => $session_id,
			'booking'      => $row['booking_code'],
		], home_url( '/trial-pay/' ) );
		$mode = 'mock';
	}

	// Record/refresh the payment row.
	$wpdb->insert( $payments, [
		'booking_id'                   => $booking_id,
		'provider'                     => 'STRIPE',
		'provider_checkout_session_id' => $session_id,
		'provider_payment_intent_id'   => $intent_id,
		'amount'                       => $amount,
		'currency'                     => strtoupper( $currency ),
		'status'                       => 'PENDING',
		'created_at_utc'               => $now,
		'updated_at_utc'               => $now,
	] );
	$payment_id = (int) $wpdb->insert_id;

	// HELD → PENDING_PAYMENT (locks stay ACTIVE).
	$wpdb->update( mgk_booking_table( 'bookings' ),
		[ 'status' => 'PENDING_PAYMENT', 'updated_at_utc' => $now ],
		[ 'id' => $booking_id ]
	);
	mgk_log_booking_event( $booking_id, 'PAYMENT_CREATED', [
		'old_status' => 'HELD', 'new_status' => 'PENDING_PAYMENT', 'provider' => 'STRIPE',
		'metadata' => [ 'session_id' => $session_id, 'mode' => $mode, 'amount' => $amount ],
	] );

	return [ 'checkout_url' => $checkout_url, 'payment_id' => $payment_id, 'session_id' => $session_id, 'mode' => $mode ];
}

/* ── Webhook handling (LIVE + MOCK) ────────────────────────────────────── */

/**
 * Process a Stripe-style event. Confirms the booking on success. Idempotent via
 * mgk_mark_webhook_processed(). Returns ['ok'=>bool,'message'=>str].
 *
 * @param array  $event   decoded event (type, id, data.object…)
 */
function mgk_stripe_handle_event( array $event ) {
	$event_id   = (string) ( $event['id'] ?? '' );
	$event_type = (string) ( $event['type'] ?? '' );
	$object     = (array) ( $event['data']['object'] ?? [] );

	if ( ! $event_id ) {
		return [ 'ok' => false, 'message' => 'missing event id' ];
	}

	// Idempotency claim — duplicate delivery is a safe no-op.
	if ( ! mgk_mark_webhook_processed( 'STRIPE', $event_id, $event_type ) ) {
		return [ 'ok' => true, 'message' => 'duplicate ignored' ];
	}

	// Resolve booking from metadata / client_reference_id / session id.
	$booking_id = (int) ( $object['metadata']['booking_id'] ?? 0 );
	$session_id = (string) ( $object['id'] ?? '' );
	if ( ! $booking_id && $session_id ) {
		$booking_id = mgk_stripe_booking_id_from_session( $session_id );
	}
	if ( ! $booking_id && ! empty( $object['client_reference_id'] ) ) {
		$b = mgk_get_booking_by_code( $object['client_reference_id'] );
		if ( $b ) $booking_id = (int) $b['id'];
	}
	if ( ! $booking_id ) {
		return [ 'ok' => true, 'message' => 'no matching booking' ];
	}

	switch ( $event_type ) {
		case 'checkout.session.completed':
		case 'payment_intent.succeeded':
			return mgk_stripe_confirm_paid( $booking_id, $object );

		case 'payment_intent.payment_failed':
			return mgk_stripe_mark_failed( $booking_id );

		case 'checkout.session.expired':
			return mgk_stripe_mark_checkout_expired( $booking_id );
	}
	return [ 'ok' => true, 'message' => 'unhandled type: ' . $event_type ];
}

/**
 * Confirm a booking after verified payment. ATOMIC + lock-authoritative (§18).
 *
 * Money was received, so payment → PAID always. The booking's fate is decided in
 * a single transaction whose authoritative guard is the SAME mechanism as hold
 * time: we re-INSERT/own the slot's block locks. If any block is owned by a
 * DIFFERENT booking, the slot was lost → MANUAL_REVIEW (no double-book). This
 * closes the gap where two PENDING_PAYMENT bookings could both confirm.
 */
function mgk_stripe_confirm_paid( $booking_id, $object = [] ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return [ 'ok' => true, 'message' => 'booking gone' ];
	if ( $row['status'] === 'CONFIRMED' ) return [ 'ok' => true, 'message' => 'already confirmed' ];

	$now      = mgk_booking_now_utc();
	$payments = mgk_booking_table( 'payments' );
	$bookings = mgk_booking_table( 'bookings' );

	// Amount sanity (when the event carries an amount).
	$paid_minor = isset( $object['amount_total'] ) ? (int) $object['amount_total']
		: ( isset( $object['amount'] ) ? (int) $object['amount'] : null );
	$expected_minor = (int) round( (float) $row['price_amount'] * 100 );
	$amount_ok = ( $paid_minor === null ) || ( $paid_minor === $expected_minor );

	// Payment is PAID regardless (money received). Single statement, idempotent.
	$wpdb->update( $payments,
		[ 'status' => 'PAID', 'paid_at_utc' => $now, 'updated_at_utc' => $now,
		  'latest_webhook_event_id' => sanitize_text_field( (string) ( $object['id'] ?? '' ) ) ],
		[ 'booking_id' => $booking_id ]
	);

	// Decide booking fate atomically.
	$wpdb->query( 'START TRANSACTION' );

	// Re-read inside the txn to avoid a stale status race.
	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT status FROM {$bookings} WHERE id = %d FOR UPDATE", $booking_id ), ARRAY_A );
	if ( $cur && $cur['status'] === 'CONFIRMED' ) {
		$wpdb->query( 'COMMIT' );
		return [ 'ok' => true, 'message' => 'already confirmed (race)' ];
	}

	// Authoritative ownership check: does THIS booking still own all its blocks,
	// and is no block owned by another booking?
	$slot_lost = mgk_stripe_slot_lost( $booking_id, $row );

	if ( ! $amount_ok || $slot_lost ) {
		$reason = ! $amount_ok ? 'amount_mismatch' : 'slot_conflict';
		$wpdb->update( $bookings,
			[ 'status' => 'MANUAL_REVIEW', 'payment_status' => 'PAID', 'updated_at_utc' => $now ],
			[ 'id' => $booking_id ]
		);
		$wpdb->query( 'COMMIT' );
		mgk_log_booking_event( $booking_id, 'MANUAL_REVIEW_CREATED', [
			'old_status' => $row['status'], 'new_status' => 'MANUAL_REVIEW', 'provider' => 'STRIPE',
			'actor_type' => 'WEBHOOK', 'metadata' => [ 'reason' => $reason, 'paid_minor' => $paid_minor, 'expected_minor' => $expected_minor ],
		] );
		return [ 'ok' => true, 'message' => 'manual review: ' . $reason ];
	}

	// Confirm + promote HOLD locks → permanent BOOKING locks, all in-txn.
	$wpdb->update( $bookings,
		[ 'status' => 'CONFIRMED', 'payment_status' => 'PAID', 'confirmed_at_utc' => $now, 'updated_at_utc' => $now ],
		[ 'id' => $booking_id ]
	);
	mgk_engine_promote_locks_to_booking( $booking_id );

	$wpdb->query( 'COMMIT' );

	mgk_log_booking_event( $booking_id, 'PAYMENT_SUCCEEDED', [ 'provider' => 'STRIPE', 'actor_type' => 'WEBHOOK', 'new_status' => 'PAID' ] );
	mgk_log_booking_event( $booking_id, 'BOOKING_CONFIRMED', [ 'old_status' => $row['status'], 'new_status' => 'CONFIRMED', 'actor_type' => 'WEBHOOK' ] );

	do_action( 'mgk_booking_confirmed', $booking_id );

	return [ 'ok' => true, 'message' => 'confirmed' ];
}

/**
 * True if this booking can no longer safely claim its slot — i.e. ANY of its
 * 15-min blocks is currently locked by a DIFFERENT booking, OR another booking
 * already CONFIRMED an overlapping interval. This is the same authority used at
 * hold time, so two racing PENDING_PAYMENT bookings cannot both confirm.
 */
function mgk_stripe_slot_lost( $booking_id, $row ) {
	global $wpdb;
	$locks    = mgk_booking_table( 'locks' );
	$bookings = mgk_booking_table( 'bookings' );
	$blocks   = mgk_expand_to_blocks( $row['start_at_utc'], $row['end_at_utc'] );
	if ( empty( $blocks ) ) return true;

	$now = mgk_booking_now_utc();
	$placeholders = implode( ',', array_fill( 0, count( $blocks ), '%s' ) );

	// Any active block of this slot owned by another booking?
	$params = array_merge(
		[ (int) $row['tutor_post_id'], (int) $booking_id ],
		$blocks,
		[ $now ]
	);
	$other_lock = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM {$locks}
		 WHERE tutor_post_id = %d AND booking_id <> %d
		   AND block_start_at_utc IN ({$placeholders})
		   AND ( lock_type = 'BOOKING' OR expires_at_utc IS NULL OR expires_at_utc > %s )
		 LIMIT 1",
		$params
	) );
	if ( $other_lock ) return true;

	// Belt-and-suspenders: another booking already CONFIRMED an overlap.
	$other_booking = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM {$bookings}
		 WHERE tutor_post_id = %d AND id <> %d
		   AND status IN ('CONFIRMED','RESCHEDULED','COMPLETED')
		   AND end_at_utc > %s AND start_at_utc < %s
		 LIMIT 1",
		(int) $row['tutor_post_id'], (int) $booking_id, $row['start_at_utc'], $row['end_at_utc']
	) );
	return ! empty( $other_booking );
}

function mgk_stripe_mark_failed( $booking_id ) {
	global $wpdb;
	$now = mgk_booking_now_utc();
	$wpdb->update( mgk_booking_table( 'payments' ),
		[ 'status' => 'FAILED', 'failed_at_utc' => $now, 'updated_at_utc' => $now ],
		[ 'booking_id' => $booking_id ]
	);
	$row = mgk_get_booking_row( $booking_id );
	// Keep PENDING_PAYMENT if hold still valid (retry allowed); else FAILED_PAYMENT.
	$hold_valid = $row && ! empty( $row['hold_expires_at_utc'] ) && strtotime( $row['hold_expires_at_utc'] . ' UTC' ) > time();
	$new = $hold_valid ? 'PENDING_PAYMENT' : 'FAILED_PAYMENT';
	$wpdb->update( mgk_booking_table( 'bookings' ),
		[ 'status' => $new, 'payment_status' => 'FAILED', 'updated_at_utc' => $now ],
		[ 'id' => $booking_id ]
	);
	mgk_log_booking_event( $booking_id, 'PAYMENT_FAILED', [ 'new_status' => $new, 'provider' => 'STRIPE', 'actor_type' => 'WEBHOOK' ] );
	return [ 'ok' => true, 'message' => 'payment failed → ' . $new ];
}

function mgk_stripe_mark_checkout_expired( $booking_id ) {
	global $wpdb;
	$now = mgk_booking_now_utc();
	$wpdb->update( mgk_booking_table( 'payments' ),
		[ 'status' => 'EXPIRED', 'updated_at_utc' => $now ],
		[ 'booking_id' => $booking_id ]
	);
	// Let the hold-expiry job handle releasing locks if the hold also lapsed.
	mgk_log_booking_event( $booking_id, 'PAYMENT_EXPIRED', [ 'provider' => 'STRIPE', 'actor_type' => 'WEBHOOK' ] );
	return [ 'ok' => true, 'message' => 'checkout expired' ];
}

/** Look up booking id from a checkout session id. */
function mgk_stripe_booking_id_from_session( $session_id ) {
	global $wpdb;
	$payments = mgk_booking_table( 'payments' );
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT booking_id FROM {$payments} WHERE provider_checkout_session_id = %s LIMIT 1",
		sanitize_text_field( $session_id )
	) );
}

/* ── Mock-mode pay trigger (local testing only) ────────────────────────── */

/**
 * In MOCK mode the checkout_url points back to /trial-pay/?mgk_mock_pay=<session>.
 * Landing there with that param simulates Stripe firing a successful webhook, so
 * the full hold→pay→confirm flow works without real Stripe keys. Disabled
 * entirely once a live secret key is configured.
 */
add_action( 'template_redirect', function () {
	if ( mgk_stripe_is_live() ) return; // mock trigger only without live keys
	if ( empty( $_GET['mgk_mock_pay'] ) ) return;

	$session_id = sanitize_text_field( wp_unslash( $_GET['mgk_mock_pay'] ) );
	$booking_id = mgk_stripe_booking_id_from_session( $session_id );
	if ( ! $booking_id ) return;

	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return;

	// Synthesize the same event a real Stripe webhook would deliver.
	mgk_stripe_handle_event( [
		'id'   => 'evt_mock_' . $session_id,
		'type' => 'checkout.session.completed',
		'data' => [ 'object' => [
			'id'           => $session_id,
			'amount_total' => (int) round( (float) $row['price_amount'] * 100 ),
			'metadata'     => [ 'booking_id' => (int) $booking_id, 'booking_code' => $row['booking_code'] ],
		] ],
	] );

	// Land on the confirmation page just like a real return_url would.
	$dest = home_url( '/trial-confirmed/?booking=' . rawurlencode( $row['booking_code'] ) );
	wp_safe_redirect( $dest );
	exit;
} );

/* ── Stripe REST helpers (LIVE) ────────────────────────────────────────── */

/** POST form-encoded params to Stripe; returns decoded array or WP_Error. */
function mgk_stripe_api_post( $path, array $params ) {
	$resp = wp_remote_post( 'https://api.stripe.com/v1/' . $path, [
		'headers' => [
			'Authorization' => 'Bearer ' . mgk_stripe_secret_key(),
			'Content-Type'  => 'application/x-www-form-urlencoded',
		],
		'body'    => $params,
		'timeout' => 20,
	] );
	if ( is_wp_error( $resp ) ) {
		return new WP_Error( 'mgk_stripe_http', 'Stripe request failed: ' . $resp->get_error_message(), [ 'status' => 502 ] );
	}
	$code = wp_remote_retrieve_response_code( $resp );
	$data = json_decode( wp_remote_retrieve_body( $resp ), true );
	if ( $code >= 400 ) {
		$msg = $data['error']['message'] ?? 'Stripe error';
		return new WP_Error( 'mgk_stripe_api', $msg, [ 'status' => 502 ] );
	}
	return is_array( $data ) ? $data : [];
}

/**
 * Verify a Stripe webhook signature (t=…,v1=… header) against the raw body.
 * Implements the standard HMAC-SHA256 scheme so we don't need the SDK.
 */
function mgk_stripe_verify_signature( $payload, $sig_header, $secret, $tolerance = 300 ) {
	if ( ! $secret || ! $sig_header ) return false;
	$parts = [];
	foreach ( explode( ',', $sig_header ) as $kv ) {
		$p = explode( '=', trim( $kv ), 2 );
		if ( count( $p ) === 2 ) $parts[ $p[0] ][] = $p[1];
	}
	$t  = $parts['t'][0] ?? '';
	$v1 = $parts['v1'] ?? [];
	if ( ! $t || empty( $v1 ) ) return false;
	$expected = hash_hmac( 'sha256', $t . '.' . $payload, $secret );
	$match = false;
	foreach ( $v1 as $candidate ) {
		if ( hash_equals( $expected, $candidate ) ) { $match = true; break; }
	}
	if ( ! $match ) return false;
	// Replay window.
	if ( abs( time() - (int) $t ) > $tolerance ) return false;
	return true;
}
