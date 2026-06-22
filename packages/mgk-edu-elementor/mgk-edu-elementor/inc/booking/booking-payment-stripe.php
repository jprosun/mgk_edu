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
 * Settings: platform/direct keys, Connect client id, connected account id,
 * webhook secret. Stored in MGK Site Settings theme_mods, with legacy wp-option
 * fallbacks for direct-key installs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Mode + settings ───────────────────────────────────────────────────── */

// Keys come from MGK Site Settings → Payments (theme_mod), with the legacy
// wp-option as a fallback so a `wp option update mgk_stripe_secret …` still works.
function mgk_stripe_secret_key() {
	$v = function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_secret', '' ) ) : '';
	return $v !== '' ? $v : trim( (string) get_option( 'mgk_stripe_secret', '' ) );
}
function mgk_stripe_publishable_key() {
	return function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_publishable', '' ) ) : '';
}
function mgk_stripe_webhook_secret() {
	$v = function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_webhook_secret', '' ) ) : '';
	return $v !== '' ? $v : trim( (string) get_option( 'mgk_stripe_webhook_secret', '' ) );
}
function mgk_stripe_connect_client_id() {
	return function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_connect_client_id', '' ) ) : '';
}
function mgk_stripe_connected_account_id() {
	$acct = function_exists( 'mgk_site_setting' ) ? trim( (string) mgk_site_setting( 'stripe_connect_account_id', '' ) ) : '';
	return preg_match( '/^acct_[A-Za-z0-9_]+$/', $acct ) ? $acct : '';
}
function mgk_stripe_connect_ready() {
	return mgk_stripe_secret_key() !== '' && mgk_stripe_connect_client_id() !== '';
}
function mgk_stripe_is_live()      { return mgk_stripe_secret_key() !== ''; }
function mgk_stripe_checkout_mode() {
	if ( ! mgk_stripe_is_live() ) return 'mock';
	return mgk_stripe_connected_account_id() ? 'connect' : 'direct';
}

function mgk_stripe_connect_redirect_uri() {
	return admin_url( 'admin-post.php?action=mgk_stripe_connect_callback' );
}

function mgk_stripe_connect_settings_url( $args = [] ) {
	return add_query_arg( $args, admin_url( 'admin.php?page=mgk-site-settings' ) );
}

function mgk_stripe_connect_state_key( $state ) {
	return 'mgk_stripe_connect_' . md5( (string) $state );
}

function mgk_stripe_connect_set_account( $account_id, $livemode = false ) {
	if ( ! preg_match( '/^acct_[A-Za-z0-9_]+$/', (string) $account_id ) ) {
		return false;
	}
	set_theme_mod( 'mgk_stripe_connect_account_id', (string) $account_id );
	set_theme_mod( 'mgk_stripe_connect_livemode', $livemode ? '1' : '0' );
	set_theme_mod( 'mgk_stripe_connect_connected_at', gmdate( 'Y-m-d H:i:s' ) );
	return true;
}

function mgk_stripe_connect_clear_account( $only_account_id = '' ) {
	$current = mgk_stripe_connected_account_id();
	if ( $only_account_id && $current && $only_account_id !== $current ) {
		return false;
	}
	remove_theme_mod( 'mgk_stripe_connect_account_id' );
	remove_theme_mod( 'mgk_stripe_connect_livemode' );
	remove_theme_mod( 'mgk_stripe_connect_connected_at' );
	return true;
}

/* ── Stripe Connect admin OAuth ───────────────────────────────────────── */

add_action( 'admin_post_mgk_stripe_connect_start', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Permission denied.' );
	}
	check_admin_referer( 'mgk_stripe_connect_start' );

	if ( ! mgk_stripe_connect_ready() ) {
		wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'missing_config' ] ) );
		exit;
	}

	$state = wp_generate_password( 32, false, false );
	set_transient( mgk_stripe_connect_state_key( $state ), get_current_user_id(), 15 * MINUTE_IN_SECONDS );

	$params = [
		'response_type' => 'code',
		'client_id'     => mgk_stripe_connect_client_id(),
		'scope'         => 'read_write',
		'redirect_uri'  => mgk_stripe_connect_redirect_uri(),
		'state'         => $state,
	];
	$email = function_exists( 'mgk_site_setting' ) ? sanitize_email( (string) mgk_site_setting( 'email', '' ) ) : '';
	if ( $email ) $params['stripe_user[email]'] = $email;
	$params['stripe_user[country]'] = 'SG';
	$params['stripe_user[business_name]'] = get_bloginfo( 'name' );

	wp_redirect( esc_url_raw( add_query_arg( $params, 'https://connect.stripe.com/oauth/authorize' ) ) );
	exit;
} );

add_action( 'admin_post_mgk_stripe_connect_callback', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Permission denied.' );
	}

	$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
	$expected_user = $state ? get_transient( mgk_stripe_connect_state_key( $state ) ) : false;
	if ( ! $state || ! $expected_user || (int) $expected_user !== get_current_user_id() ) {
		wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'bad_state' ] ) );
		exit;
	}
	delete_transient( mgk_stripe_connect_state_key( $state ) );

	if ( ! empty( $_GET['error'] ) ) {
		wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'denied' ] ) );
		exit;
	}

	$code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
	if ( ! $code ) {
		wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'missing_code' ] ) );
		exit;
	}

	$token = mgk_stripe_oauth_token( $code );
	if ( is_wp_error( $token ) ) {
		wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'token_error' ] ) );
		exit;
	}

	$account_id = (string) ( $token['stripe_user_id'] ?? '' );
	$livemode   = ! empty( $token['livemode'] );
	if ( ! mgk_stripe_connect_set_account( $account_id, $livemode ) ) {
		wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'bad_account' ] ) );
		exit;
	}

	wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'connected' ] ) );
	exit;
} );

add_action( 'admin_post_mgk_stripe_connect_disconnect', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Permission denied.' );
	}
	check_admin_referer( 'mgk_stripe_connect_disconnect' );
	mgk_stripe_connect_clear_account();
	wp_safe_redirect( mgk_stripe_connect_settings_url( [ 'stripe_connect' => 'disconnected' ] ) );
	exit;
} );

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
	$product_name = 'Trial lesson — ' . $row['booking_code'];
	if ( (string) ( $row['lesson_type'] ?? '' ) === 'PACKAGE_16' ) {
		$product_name = '16-lesson package — ' . $row['booking_code'];
	} elseif ( (string) ( $row['lesson_type'] ?? '' ) === 'PACKAGE_8' ) {
		$product_name = '8-lesson package — ' . $row['booking_code'];
	}

	$payments = mgk_booking_table( 'payments' );
	$now = mgk_booking_now_utc();

	if ( mgk_stripe_is_live() ) {
		$stripe_account = mgk_stripe_connected_account_id();
		// Stripe param shape comes from the shared module (Money → correct smallest unit).
		$money  = \Margick\Commerce\Domain\Money::ofMajor( (float) $amount, (string) $currency );
		$params = \Margick\Commerce\Payment\Stripe\StripeGateway::checkoutParams(
			$money,
			$product_name,
			$row['booking_code'],
			add_query_arg( 'session_id', '{CHECKOUT_SESSION_ID}', $return_url ),
			$return_url,
			[ 'booking_id' => $booking_id, 'booking_code' => $row['booking_code'] ]
		);
		$resp = mgk_stripe_api_post( 'checkout/sessions', $params, [
			'stripe_account' => $stripe_account,
			'idempotency_key' => 'mgk_checkout_' . $booking_id . '_' . md5( $row['booking_code'] ),
		] );
		if ( is_wp_error( $resp ) ) return $resp;
		$session_id  = $resp['id'] ?? '';
		$checkout_url = $resp['url'] ?? '';
		$intent_id    = $resp['payment_intent'] ?? null;
		$mode = $stripe_account ? 'connect' : 'direct';
	} else {
		// MOCK: shared deterministic synth (module) + local mock-pay URL (app routing stays here).
		$mock         = \Margick\Commerce\Payment\Stripe\StripeGateway::mockSession( (string) $booking_id, (string) $now );
		$session_id   = $mock['session_id'];
		$intent_id    = $mock['intent_id'];
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
		'metadata' => [ 'session_id' => $session_id, 'mode' => $mode, 'amount' => $amount, 'stripe_account' => mgk_stripe_connected_account_id() ],
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
	$event_account = (string) ( $event['account'] ?? '' );

	if ( ! $event_id ) {
		return [ 'ok' => false, 'message' => 'missing event id' ];
	}

	// Idempotency claim — duplicate delivery is a safe no-op.
	if ( ! mgk_mark_webhook_processed( 'STRIPE', $event_id, $event_type ) ) {
		return [ 'ok' => true, 'message' => 'duplicate ignored' ];
	}

	if ( $event_type === 'account.application.deauthorized' ) {
		mgk_stripe_connect_clear_account( $event_account );
		return [ 'ok' => true, 'message' => 'connected account deauthorized' ];
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
	mgk_stripe_bind_webhook_event_to_booking( $event_id, $booking_id );

	switch ( $event_type ) {
		case 'checkout.session.completed':
		case 'payment_intent.succeeded':
			return mgk_stripe_confirm_paid( $booking_id, $object, $event_id );

		case 'payment_intent.payment_failed':
			return mgk_stripe_mark_failed( $booking_id );

		case 'checkout.session.expired':
			return mgk_stripe_mark_checkout_expired( $booking_id );
	}
	return [ 'ok' => true, 'message' => 'unhandled type: ' . $event_type ];
}

/** Attach the booking id to the already-claimed raw webhook row once resolved. */
function mgk_stripe_bind_webhook_event_to_booking( $event_id, $booking_id ) {
	global $wpdb;
	$table = function_exists( 'mgk_booking_table' ) ? mgk_booking_table( 'events' ) : '';
	$event_id = sanitize_text_field( (string) $event_id );
	$booking_id = (int) $booking_id;
	if ( ! $table || ! $event_id || ! $booking_id ) return;
	$wpdb->update(
		$table,
		[ 'booking_id' => $booking_id ],
		[ 'provider' => 'STRIPE', 'provider_event_id' => $event_id ]
	);
}

/** Extract a Stripe PaymentIntent id from either a Checkout Session or PI event. */
function mgk_stripe_payment_intent_id_from_object( $object ) {
	$object = (array) $object;
	$id = (string) ( $object['id'] ?? '' );
	if ( strpos( $id, 'pi_' ) === 0 ) {
		return sanitize_text_field( $id );
	}
	$pi = $object['payment_intent'] ?? '';
	if ( is_array( $pi ) ) {
		$pi = (string) ( $pi['id'] ?? '' );
	}
	$pi = (string) $pi;
	return strpos( $pi, 'pi_' ) === 0 ? sanitize_text_field( $pi ) : '';
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
function mgk_stripe_confirm_paid( $booking_id, $object = [], $event_id = '' ) {
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
	$payment_update = [
		'status'                  => 'PAID',
		'paid_at_utc'             => $now,
		'updated_at_utc'          => $now,
		'latest_webhook_event_id' => sanitize_text_field( (string) ( $event_id ?: ( $object['id'] ?? '' ) ) ),
	];
	$payment_intent_id = mgk_stripe_payment_intent_id_from_object( $object );
	if ( $payment_intent_id !== '' ) {
		$payment_update['provider_payment_intent_id'] = $payment_intent_id;
	}
	$wpdb->update( $payments, $payment_update, [ 'booking_id' => $booking_id ] );

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
 * Refund (part of) a booking's payment. Live → Stripe Refunds API against the
 * stored payment_intent; mock → simulated. Updates the payments row status to
 * REFUNDED / PARTIALLY_REFUNDED. Idempotent per (booking, amount) via the
 * Idempotency-Key. $amount is in SGD; <= 0 is a no-op.
 *
 * @return array{ok:bool, refunded:float, mode:string, refund_id:string, full:bool}|WP_Error
 */
function mgk_stripe_refund_payment( $booking_id, $amount ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$amount     = round( (float) $amount, 2 );
	$payments   = mgk_booking_table( 'payments' );
	$pay = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$payments} WHERE booking_id = %d ORDER BY id DESC LIMIT 1", $booking_id
	), ARRAY_A );

	if ( $amount <= 0 ) {
		return [ 'ok' => true, 'refunded' => 0.0, 'mode' => 'none', 'refund_id' => '', 'full' => false ];
	}
	if ( ! $pay || strtoupper( (string) $pay['status'] ) !== 'PAID' ) {
		// Nothing actually captured (e.g. unpaid hold) → no money to return.
		return [ 'ok' => true, 'refunded' => 0.0, 'mode' => 'none', 'refund_id' => '', 'full' => false ];
	}

	$paid_amount = (float) $pay['amount'];
	$amount      = min( $amount, $paid_amount );           // never refund more than paid
	$minor       = (int) round( $amount * 100 );
	$is_full     = $minor >= (int) round( $paid_amount * 100 );
	$now         = mgk_booking_now_utc();

	$refund_id = '';
	if ( mgk_stripe_is_live() ) {
		$pi = (string) ( $pay['provider_payment_intent_id'] ?? '' );
		if ( $pi === '' ) {
			return new WP_Error( 'mgk_no_intent', 'No payment intent on file to refund.', [ 'status' => 409 ] );
		}
		$res = mgk_stripe_api_request( 'POST', 'refunds', [
			'payment_intent' => $pi,
			'amount'         => $minor,
			'metadata[booking_id]' => $booking_id,
		], [ 'idempotency_key' => 'mgk_refund_' . $booking_id . '_' . $minor ] );
		if ( is_wp_error( $res ) ) return $res;
		$refund_id = (string) ( $res['id'] ?? '' );
		$mode = 'live';
	} else {
		$refund_id = 'rf_mock_' . substr( md5( $booking_id . ':' . $minor . ':' . $now ), 0, 18 );
		$mode = 'mock';
	}

	$wpdb->update( $payments,
		[ 'status' => $is_full ? 'REFUNDED' : 'PARTIALLY_REFUNDED', 'updated_at_utc' => $now ],
		[ 'booking_id' => $booking_id ]
	);
	mgk_log_booking_event( $booking_id, 'PAYMENT_REFUNDED', [
		'provider' => 'STRIPE', 'actor_type' => 'SYSTEM',
		'metadata' => [ 'amount' => $amount, 'full' => $is_full, 'refund_id' => $refund_id, 'mode' => $mode ],
	] );

	return [ 'ok' => true, 'refunded' => $amount, 'mode' => $mode, 'refund_id' => $refund_id, 'full' => $is_full ];
}

/**
 * True if this booking can no longer safely claim its slot — i.e. ANY of its
 * 15-min blocks is currently locked by a DIFFERENT booking, OR another booking
 * already CONFIRMED an overlapping interval. This is the same authority used at
 * hold time, so two racing PENDING_PAYMENT bookings cannot both confirm.
 */
function mgk_stripe_slot_lost( $booking_id, $row ) {
	global $wpdb;
	if ( function_exists( 'mgk_package_plan_from_lesson_type' ) && mgk_package_plan_from_lesson_type( (string) ( $row['lesson_type'] ?? '' ) ) ) {
		return false;
	}

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
	$row = mgk_get_booking_row( $booking_id );
	if ( $row && function_exists( 'mgk_package_plan_from_lesson_type' ) && mgk_package_plan_from_lesson_type( (string) ( $row['lesson_type'] ?? '' ) ) ) {
		$wpdb->update( mgk_booking_table( 'bookings' ),
			[ 'status' => 'EXPIRED', 'payment_status' => 'EXPIRED', 'updated_at_utc' => $now ],
			[ 'id' => $booking_id ]
		);
	}
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

/** Exchange a Stripe Connect OAuth code for the connected account id. */
function mgk_stripe_oauth_token( $code ) {
	$resp = wp_remote_post( 'https://connect.stripe.com/oauth/token', [
		'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
		'body'    => [
			'client_secret' => mgk_stripe_secret_key(),
			'code'          => (string) $code,
			'grant_type'    => 'authorization_code',
		],
		'timeout' => 20,
	] );
	if ( is_wp_error( $resp ) ) {
		return new WP_Error( 'mgk_stripe_oauth_http', 'Stripe OAuth failed: ' . $resp->get_error_message(), [ 'status' => 502 ] );
	}
	$code = wp_remote_retrieve_response_code( $resp );
	$data = json_decode( wp_remote_retrieve_body( $resp ), true );
	if ( $code >= 400 ) {
		$msg = $data['error_description'] ?? ( $data['error'] ?? 'Stripe OAuth error' );
		return new WP_Error( 'mgk_stripe_oauth_api', $msg, [ 'status' => 502 ] );
	}
	return is_array( $data ) ? $data : [];
}

/**
 * Send a request to Stripe's REST API. GET params go in the query string; other
 * verbs send form-encoded params in the body. Returns decoded array or WP_Error.
 */
function mgk_stripe_api_request( $method, $path, array $params = [], array $opts = [] ) {
	$method  = strtoupper( $method );
	$headers = [ 'Authorization' => 'Bearer ' . mgk_stripe_secret_key() ];
	if ( ! empty( $opts['stripe_account'] ) ) {
		$headers['Stripe-Account'] = sanitize_text_field( (string) $opts['stripe_account'] );
	}
	if ( ! empty( $opts['idempotency_key'] ) ) {
		$headers['Idempotency-Key'] = sanitize_text_field( (string) $opts['idempotency_key'] );
	}

	$url  = 'https://api.stripe.com/v1/' . $path;
	$args = [ 'method' => $method, 'headers' => $headers, 'timeout' => 20 ];
	if ( $method === 'GET' ) {
		if ( $params ) $url = add_query_arg( array_map( 'rawurlencode', $params ), $url );
	} elseif ( $params ) {
		$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		$args['headers'] = $headers;
		$args['body']    = $params;
	}

	$resp = wp_remote_request( $url, $args );
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

/** POST form-encoded params to Stripe (back-compat wrapper). */
function mgk_stripe_api_post( $path, array $params, array $opts = [] ) {
	return mgk_stripe_api_request( 'POST', $path, $params, $opts );
}

/* ── Auto webhook provisioning (zero-config for the agency) ─────────────── */

/** The four events the booking engine acts on. */
function mgk_stripe_webhook_events() {
	return [
		'checkout.session.completed',
		'payment_intent.succeeded',
		'payment_intent.payment_failed',
		'checkout.session.expired',
	];
}

/**
 * Create (or re-create) the Stripe webhook endpoint for this site and store its
 * signing secret automatically, so the agency never has to touch the Stripe
 * dashboard's "Developers → Webhooks" screen. Called right after a secret key is
 * saved.
 *
 * Stripe only reveals the signing secret (whsec_…) at creation time, so to keep a
 * secret we KNOW, we delete any existing endpoint pointing at our URL and create a
 * fresh one. Idempotent enough for a settings-save: one endpoint per site URL.
 *
 * Returns ['ok'=>true,'secret_set'=>bool] or WP_Error (e.g. on localhost, where
 * Stripe refuses to register a non-public URL — handled gracefully by the caller).
 */
function mgk_stripe_ensure_webhook_endpoint() {
	if ( ! mgk_stripe_is_live() ) {
		return new WP_Error( 'mgk_no_key', 'No Stripe secret key is configured.' );
	}
	$url = rest_url( 'mgk/v1/stripe/webhook' );

	// Stripe rejects non-public URLs. Detect localhost / *.local / private hosts so
	// we give a clear "works once you're on a real domain" message instead of a
	// confusing Stripe API error.
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	if ( $host === 'localhost' || $host === '' || preg_match( '/(^127\.)|(^10\.)|(^192\.168\.)|(\.local$)|(\.test$)/i', $host ) ) {
		return new WP_Error( 'mgk_local_url', 'Auto-setup needs a public domain. The webhook will configure itself automatically once this site is live on its real domain — nothing to do until then.' );
	}

	// Remove stale endpoints that point at our URL (avoid duplicates / unknown secrets).
	$list = mgk_stripe_api_request( 'GET', 'webhook_endpoints', [ 'limit' => 100 ] );
	if ( ! is_wp_error( $list ) && ! empty( $list['data'] ) && is_array( $list['data'] ) ) {
		foreach ( $list['data'] as $ep ) {
			if ( isset( $ep['url'], $ep['id'] ) && $ep['url'] === $url ) {
				mgk_stripe_api_request( 'DELETE', 'webhook_endpoints/' . rawurlencode( $ep['id'] ) );
			}
		}
	}

	// Create the endpoint. enabled_events as an indexed param array.
	$params = [ 'url' => $url, 'description' => 'MGK booking engine (auto-configured)' ];
	foreach ( array_values( mgk_stripe_webhook_events() ) as $i => $evt ) {
		$params[ "enabled_events[$i]" ] = $evt;
	}
	$created = mgk_stripe_api_request( 'POST', 'webhook_endpoints', $params );
	if ( is_wp_error( $created ) ) {
		return $created;
	}

	$secret = (string) ( $created['secret'] ?? '' );
	if ( $secret !== '' ) {
		set_theme_mod( 'mgk_stripe_webhook_secret', sanitize_text_field( $secret ) );
	}
	if ( ! empty( $created['id'] ) ) {
		set_theme_mod( 'mgk_stripe_webhook_endpoint_id', sanitize_text_field( (string) $created['id'] ) );
	}
	return [ 'ok' => true, 'secret_set' => $secret !== '', 'endpoint_id' => $created['id'] ?? '' ];
}

/**
 * Verify a Stripe webhook signature (t=…,v1=… header) against the raw body.
 * Implements the standard HMAC-SHA256 scheme so we don't need the SDK.
 */
function mgk_stripe_verify_signature( $payload, $sig_header, $secret, $tolerance = 300 ) {
	// Delegate to the shared, unit-tested module (single source for this security-critical check).
	if ( class_exists( '\\Margick\\Commerce\\Payment\\Stripe\\WebhookSignature' ) ) {
		return \Margick\Commerce\Payment\Stripe\WebhookSignature::verify(
			(string) $payload, (string) $sig_header, (string) $secret, (int) $tolerance
		);
	}
	// Fallback (module not vendored): original inline implementation.
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
