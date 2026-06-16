<?php
/**
 * MGK Booking Engine — Phase 0.5 · REST API (LOCKED DATA CORE).
 * =============================================================
 * Namespace mgk/v1, paths under /booking and /tutors/{id}/availability so they
 * don't collide with the legacy demo routes in mgk-rest.php.
 *
 *   GET  /tutors/{id}/availability                       — live slots (Step 3)
 *   POST /booking/hold                                   — atomic hold (Step 4)
 *   POST /booking/{id}/create-stripe-checkout            — Stripe (Step 6)
 *   GET  /booking/{id}                                   — status (Step 4/6)
 *   POST /stripe/webhook                                 — confirm (Step 7)
 *   POST /booking/{id}/cancel                            — cancel  (Step 9)
 *
 * Frontend only ever talks to these endpoints — it never reads the DB directly.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function () {
	$ns = 'mgk/v1';

	// ── Availability (Step 3) ──
	register_rest_route( $ns, '/tutors/(?P<id>\d+)/availability', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'mgk_rest_get_availability',
		'permission_callback' => '__return_true',
		'args'                => [
			'from'     => [ 'sanitize_callback' => 'sanitize_text_field' ],
			'to'       => [ 'sanitize_callback' => 'sanitize_text_field' ],
			'duration' => [ 'sanitize_callback' => 'absint' ],
		],
	] );

	// ── Hold (Step 4) ──
	register_rest_route( $ns, '/booking/hold', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_create_hold',
		'permission_callback' => '__return_true',
	] );

	// ── Booking status (used by S11/S12 polling) ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'mgk_rest_get_booking',
		'permission_callback' => '__return_true',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );

	// ── Stripe checkout (Step 6) ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)/create-stripe-checkout', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_create_stripe_checkout',
		'permission_callback' => '__return_true',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );

	// ── Attach parent contact email to a held booking (S11 email capture) ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)/attach-contact', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_attach_contact',
		'permission_callback' => '__return_true',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );

	// ── Apply / replace / remove a voucher on a held booking (re-quote) ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)/apply-voucher', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_apply_voucher',
		'permission_callback' => '__return_true',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );

	// ── PayNow QR payload for a booking ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)/paynow-qr', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'mgk_rest_paynow_qr',
		'permission_callback' => '__return_true',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );

	// ── Release hold (user abandons checkout) ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)/release', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_release_hold',
		'permission_callback' => '__return_true',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );

	// ── Stripe webhook (Step 7) ──
	register_rest_route( $ns, '/stripe/webhook', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_stripe_webhook',
		'permission_callback' => '__return_true', // verified by signature inside
	] );

	// ── Cancel (Step 9) ──
	register_rest_route( $ns, '/booking/(?P<id>\d+)/cancel', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_cancel_booking',
		'permission_callback' => 'is_user_logged_in',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );
} );

/* ── Availability (Step 3 — fully implemented) ─────────────────────────── */

function mgk_rest_get_availability( WP_REST_Request $req ) {
	$tutor_id = (int) $req['id'];
	if ( $tutor_id <= 0 || get_post_type( $tutor_id ) !== 'mg_teacher' ) {
		return new WP_Error( 'mgk_no_tutor', 'Tutor not found.', [ 'status' => 404 ] );
	}

	$tz = mgk_booking_tz();
	$from = sanitize_text_field( (string) $req->get_param( 'from' ) );
	$to   = sanitize_text_field( (string) $req->get_param( 'to' ) );

	// Defaults: today → +14 days (local).
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
		$from = ( new DateTime( 'now', $tz ) )->format( 'Y-m-d' );
	}
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
		$to = ( new DateTime( $from, $tz ) )->modify( '+14 days' )->format( 'Y-m-d' );
	}

	$duration = (int) $req->get_param( 'duration' );
	$result = mgk_engine_available_slots( $tutor_id, $from, $to, $duration );

	return new WP_REST_Response( $result, 200 );
}

/* ── Booking status (shared) ───────────────────────────────────────────── */

function mgk_rest_get_booking( WP_REST_Request $req ) {
	$row = mgk_get_booking_row( (int) $req['id'] );
	if ( ! $row ) {
		return new WP_Error( 'mgk_no_booking', 'Booking not found.', [ 'status' => 404 ] );
	}
	return new WP_REST_Response( mgk_booking_public_view( $row ), 200 );
}

/** Public-safe projection of a booking row for the frontend. */
function mgk_booking_public_view( $row ) {
	$tz = mgk_booking_tz();
	$fmt = function ( $utc ) use ( $tz ) {
		if ( ! $utc ) return null;
		try {
			$d = new DateTime( $utc, new DateTimeZone( 'UTC' ) );
			$d->setTimezone( $tz );
			return $d->format( 'Y-m-d H:i' );
		} catch ( Exception $e ) { return null; }
	};
	$hold_remaining = 0;
	if ( ! empty( $row['hold_expires_at_utc'] ) && $row['status'] === 'HELD' ) {
		$hold_remaining = max( 0, strtotime( $row['hold_expires_at_utc'] . ' UTC' ) - time() );
	}
	return [
		'booking_id'         => (int) $row['id'],
		'booking_code'       => $row['booking_code'],
		'status'             => $row['status'],
		'payment_status'     => $row['payment_status'],
		'tutor_id'           => (int) $row['tutor_post_id'],
		'student_name'       => $row['student_name'],
		'subject'            => $row['subject'],
		'lesson_type'        => $row['lesson_type'],
		'start_display'      => $fmt( $row['start_at_utc'] ),
		'end_display'        => $fmt( $row['end_at_utc'] ),
		'start_at_utc'       => $row['start_at_utc'],
		'end_at_utc'         => $row['end_at_utc'],
		'price_amount'       => $row['price_amount'],
		'currency'           => $row['currency'],
		'hold_remaining_sec' => (int) $hold_remaining,
	];
}

/* ── Stubs filled by later steps (return 501 until implemented) ────────── */

/* ── Hold (Step 4 — fully implemented) ─────────────────────────────────── */

function mgk_rest_create_hold( WP_REST_Request $req ) {
	$body = $req->get_json_params();
	if ( ! is_array( $body ) ) $body = $req->get_params();

	$booking_id = mgk_engine_hold_slot( [
		'tutor_id'        => (int) ( $body['tutor_id'] ?? 0 ),
		'start_at_utc'    => (string) ( $body['start_at_utc'] ?? '' ),
		'end_at_utc'      => (string) ( $body['end_at_utc'] ?? '' ),
		'student_name'    => (string) ( $body['student_name'] ?? '' ),
		'subject'         => (string) ( $body['subject'] ?? '' ),
		'lesson_type'     => (string) ( $body['lesson_type'] ?? 'TRIAL' ),
		'price_amount'    => (string) ( $body['price_amount'] ?? '0' ),
		'currency'        => (string) ( $body['currency'] ?? 'SGD' ),
		'lead_id'         => (int) ( $body['lead_id'] ?? 0 ),
		'idempotency_key' => (string) ( $body['idempotency_key'] ?? '' ),
	] );

	if ( is_wp_error( $booking_id ) ) {
		$data = $booking_id->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		$payload = [
			'error'   => $booking_id->get_error_code(),
			'message' => $booking_id->get_error_message(),
		];
		if ( is_array( $data ) && isset( $data['alternatives'] ) ) {
			$payload['alternatives'] = $data['alternatives'];
		}
		return new WP_REST_Response( $payload, $status );
	}

	$row = mgk_get_booking_row( $booking_id );
	$remaining = ! empty( $row['hold_expires_at_utc'] )
		? max( 0, strtotime( $row['hold_expires_at_utc'] . ' UTC' ) - time() )
		: 0;

	return new WP_REST_Response( [
		'booking_id'          => (int) $booking_id,
		'booking_code'        => $row['booking_code'],
		'status'              => $row['status'],
		'hold_expires_at_utc' => $row['hold_expires_at_utc'],
		'countdown_seconds'   => (int) $remaining,
		'next'                => 'PAYMENT',
	], 201 );
}
/* ── Stripe checkout + webhook (Steps 6+7 — fully implemented) ─────────── */

function mgk_rest_create_stripe_checkout( WP_REST_Request $req ) {
	$body = $req->get_json_params();
	if ( ! is_array( $body ) ) $body = $req->get_params();
	$booking_id = (int) $req['id'];
	$return_url = isset( $body['return_url'] ) ? esc_url_raw( $body['return_url'] ) : '';
	$email = isset( $body['email'] ) ? sanitize_email( (string) $body['email'] ) : '';
	if ( isset( $body['email'] ) && ( ! $email || ! is_email( $email ) ) ) {
		return new WP_REST_Response( [
			'error'   => 'email_required',
			'message' => 'A valid parent email is required before card payment.',
		], 422 );
	}
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) {
		return new WP_REST_Response( [ 'error' => 'mgk_no_booking', 'message' => 'Booking not found.' ], 404 );
	}
	if ( $row['payment_status'] === 'PAID' ) {
		return new WP_REST_Response( [ 'error' => 'mgk_already_paid', 'message' => 'Booking already paid.' ], 409 );
	}
	if ( $row['status'] !== 'HELD' && $row['status'] !== 'PENDING_PAYMENT' ) {
		return new WP_REST_Response( [ 'error' => 'mgk_bad_state', 'message' => 'Booking is not awaiting payment.' ], 409 );
	}
	if ( $row['status'] === 'HELD' && ! empty( $row['hold_expires_at_utc'] )
		&& strtotime( $row['hold_expires_at_utc'] . ' UTC' ) <= time() ) {
		return new WP_REST_Response( [ 'error' => 'mgk_hold_expired', 'message' => 'Your hold has expired. Please pick a slot again.' ], 410 );
	}

	// Attach the parent's email to the booking FIRST, so the webhook → account
	// claim has it (atomic with pay).
	if ( $email && function_exists( 'mgk_parent_attach_booking_email' ) ) {
		$attached = mgk_parent_attach_booking_email( $booking_id, $email );
		if ( is_wp_error( $attached ) ) {
			return new WP_REST_Response( [
				'error'   => $attached->get_error_code(),
				'message' => $attached->get_error_message(),
			], 422 );
		}
	}

	$row = mgk_get_booking_row( $booking_id );
	$contact_email = '';
	if ( ! empty( $row['lead_id'] ) && function_exists( 'mgk_lead_contact' ) ) {
		$contact = mgk_lead_contact( (int) $row['lead_id'] );
		$contact_email = sanitize_email( (string) ( $contact['email'] ?? '' ) );
	}
	if ( ! $contact_email || ! is_email( $contact_email ) ) {
		return new WP_REST_Response( [
			'error'   => 'email_required',
			'message' => 'Enter a valid email before card payment so we can send the booking and create the parent account.',
		], 422 );
	}

	$res = mgk_stripe_create_checkout( $booking_id, $return_url );
	if ( is_wp_error( $res ) ) {
		$data = $res->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		return new WP_REST_Response( [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ], $status );
	}
	return new WP_REST_Response( $res, 200 );
}

/**
 * S11 email capture — attach a parent email to a not-yet-finalised booking so the
 * confirm hook can create/link the wp_user (FR-BOOK-07/BR-22). Saved on blur (so
 * it persists even if the parent finishes via an admin force-confirm) and again
 * with create-stripe-checkout. Same booking_id-based access level as the sibling
 * endpoints; production hardening (a hold-token bound to the paying browser) is
 * tracked for the whole booking flow, not just here.
 */
function mgk_rest_attach_contact( WP_REST_Request $req ) {
	$booking_id = (int) $req['id'];
	$body = $req->get_json_params();
	if ( ! is_array( $body ) ) $body = $req->get_params();

	$email = isset( $body['email'] ) ? sanitize_email( (string) $body['email'] ) : '';
	$phone = isset( $body['phone'] ) ? sanitize_text_field( (string) $body['phone'] ) : '';
	$name  = isset( $body['name'] )  ? sanitize_text_field( (string) $body['name'] )  : '';

	if ( ! $email || ! is_email( $email ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_email', 'message' => 'A valid email is required.' ], 422 );
	}
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) {
		return new WP_REST_Response( [ 'error' => 'no_booking', 'message' => 'Booking not found.' ], 404 );
	}
	// Only an in-progress booking may have its contact set from the front end.
	if ( ! in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT', 'MANUAL_REVIEW' ], true ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_state', 'message' => 'This booking can no longer be edited.' ], 409 );
	}
	if ( ! function_exists( 'mgk_parent_attach_booking_email' ) ) {
		return new WP_REST_Response( [ 'error' => 'unavailable' ], 501 );
	}
	$res = mgk_parent_attach_booking_email( $booking_id, $email, $phone, $name );
	if ( is_wp_error( $res ) ) {
		return new WP_REST_Response( [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ], 422 );
	}
	return new WP_REST_Response( [ 'saved' => true, 'lead_id' => (int) $res ], 200 );
}

/**
 * Apply, replace or remove a voucher on a held booking, then RE-QUOTE the whole
 * order (headline + loyalty + voucher, capped) and persist the new price to the
 * booking so the Stripe charge equals what the parent sees. One voucher per order
 * (BR-11): a new code overwrites the previous one; an empty code removes it.
 */
function mgk_rest_apply_voucher( WP_REST_Request $req ) {
	$booking_id = (int) $req['id'];
	$body = $req->get_json_params();
	if ( ! is_array( $body ) ) $body = $req->get_params();
	$code = isset( $body['code'] ) ? sanitize_text_field( (string) $body['code'] ) : '';

	// Rate-limit voucher attempts per client IP — cheap brute-force / code-probing
	// guard (the endpoint is public so guests can check out). Window: 20 / minute.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-f:.]/i', '', (string) $_SERVER['REMOTE_ADDR'] ) : 'cli';
	$rl_key = 'mgk_voucher_rl_' . md5( $ip );
	$hits   = (int) get_transient( $rl_key );
	if ( $hits >= 20 ) {
		return new WP_REST_Response( [ 'error' => 'rate_limited', 'message' => 'Too many attempts — please wait a minute.' ], 429 );
	}
	set_transient( $rl_key, $hits + 1, MINUTE_IN_SECONDS );

	// If the client sent a REST nonce, it must be valid. (Absent is allowed: a
	// logged-out guest checkout may not carry one; the state + rate-limit guards
	// still apply. A PRESENT-but-wrong nonce signals a forged/cross-site request.)
	$nonce = $req->get_header( 'x_wp_nonce' );
	if ( $nonce && ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_nonce', 'message' => 'Security check failed — reload the page.' ], 403 );
	}

	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) {
		return new WP_REST_Response( [ 'error' => 'no_booking', 'message' => 'Booking not found.' ], 404 );
	}
	if ( ! in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT' ], true ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_state', 'message' => 'This booking can no longer be changed.' ], 409 );
	}
	if ( $row['status'] === 'HELD' && ! empty( $row['hold_expires_at_utc'] )
		&& strtotime( $row['hold_expires_at_utc'] . ' UTC' ) <= time() ) {
		return new WP_REST_Response( [
			'error'   => 'mgk_hold_expired',
			'message' => 'Your hold has expired. Please pick a slot again.',
		], 410 );
	}
	if ( ! function_exists( 'mgk_engine_quote_for_booking' ) && ! function_exists( 'mgk_quote_trial_for_tutor' ) ) {
		return new WP_REST_Response( [ 'error' => 'unavailable' ], 501 );
	}

	if ( function_exists( 'mgk_engine_quote_for_booking' ) ) {
		$q = mgk_engine_quote_for_booking( $row, $code );
	} else {
		// Legacy fallback: rebuild the loyalty context for trial bookings only.
		$ctx_args = [
			'parent_user_id' => (int) $row['parent_user_id'],
			'child_id'       => (int) ( $row['child_id'] ?? 0 ),
			'lead_id'        => (int) $row['lead_id'],
			'voucher_code'   => $code,
		];
		$ctx = function_exists( 'mgk_engine_quote_context' )
			? mgk_engine_quote_context( $ctx_args, (int) $row['tutor_post_id'] )
			: [ 'voucher_code' => $code ];
		$q = mgk_quote_trial_for_tutor( (int) $row['tutor_post_id'], $ctx );
	}

	// Detect whether the submitted code was actually applied.
	$applied_code = '';
	foreach ( (array) $q['discounts_applied'] as $d ) {
		if ( strpos( (string) $d['key'], 'voucher:' ) === 0 ) $applied_code = substr( $d['key'], 8 );
	}
	$voucher_ok    = ( $code === '' ) ? null : ( $applied_code !== '' );
	$voucher_error = ( $voucher_ok === false ) ? ( $q['voucher_note'] ?: 'Voucher not applicable' ) : '';

	// Persist the re-quoted price to the booking (the single source of charge).
	global $wpdb;
	$t = mgk_booking_table( 'bookings' );
	$wpdb->update( $t, [
		'price_amount'     => (float) $q['total'],
		'base_amount'      => (float) $q['base'],
		'discount_applied' => wp_json_encode( $q['discounts_applied'] ),
		'voucher_code'     => $applied_code ?: null,
		'updated_at_utc'   => mgk_booking_now_utc(),
	], [ 'id' => $booking_id ] );

	mgk_log_booking_event( $booking_id, 'VOUCHER_APPLIED', [
		'metadata' => [ 'code' => $code, 'applied' => $applied_code, 'total' => $q['total'] ],
	] );

	// Re-render the summary rows so the page can swap them in (display === charge).
	$summary = function_exists( 'mgk_get_pay_order_summary' )
		? mgk_get_pay_order_summary( null, [ 'booking_id' => $booking_id, 'tutor_slug' => '' ] )
		: null;

	return new WP_REST_Response( [
		'applied'       => $applied_code,
		'voucher_ok'    => $voucher_ok,
		'voucher_error' => $voucher_error,
		'total'         => (float) $q['total'],
		'total_str'     => $q['total_str'],
		'rows'          => $summary ? $summary['rows'] : $q['rows'],
		'subtotal'      => $summary ? $summary['subtotal'] : $q['subtotal_str'],
		'cap_note'      => $summary ? $summary['cap_note'] : $q['cap_note'],
		'gst_note'      => $summary ? $summary['gst_note'] : $q['gst_note'],
	], 200 );
}

function mgk_rest_paynow_qr( WP_REST_Request $req ) {
	if ( ! function_exists( 'mgk_paynow_payload_for_booking' ) ) {
		return new WP_REST_Response( [ 'error' => 'paynow_unavailable' ], 501 );
	}
	$res = mgk_paynow_payload_for_booking( (int) $req['id'] );
	if ( is_wp_error( $res ) ) {
		return new WP_REST_Response( [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ], 422 );
	}
	return new WP_REST_Response( $res, 200 );
}

function mgk_rest_release_hold( WP_REST_Request $req ) {
	$res = mgk_engine_release_hold( (int) $req['id'] );
	if ( is_wp_error( $res ) ) {
		$data = $res->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		return new WP_REST_Response( [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ], $status );
	}
	return new WP_REST_Response( [ 'released' => true ], 200 );
}

function mgk_rest_stripe_webhook( WP_REST_Request $req ) {
	$payload = $req->get_body(); // raw JSON

	if ( mgk_stripe_is_live() ) {
		$sig    = $req->get_header( 'stripe_signature' );
		$secret = mgk_stripe_webhook_secret();
		if ( ! mgk_stripe_verify_signature( $payload, $sig, $secret ) ) {
			return new WP_REST_Response( [ 'error' => 'invalid_signature' ], 400 );
		}
	}
	// MOCK mode accepts the event unsigned (local testing only).

	$event = json_decode( $payload, true );
	if ( ! is_array( $event ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_payload' ], 400 );
	}

	$result = mgk_stripe_handle_event( $event );
	// Always 200 on a processed/duplicate event so Stripe stops retrying.
	return new WP_REST_Response( [ 'received' => true, 'message' => $result['message'] ?? '' ], 200 );
}
// mgk_rest_cancel_booking is implemented in inc/booking/booking-manage.php
// (real cancel + tiered Stripe refund). The /cancel route is registered above.
