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
	$return_url = isset( $body['return_url'] ) ? esc_url_raw( $body['return_url'] ) : '';

	$res = mgk_stripe_create_checkout( (int) $req['id'], $return_url );
	if ( is_wp_error( $res ) ) {
		$data = $res->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		return new WP_REST_Response( [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ], $status );
	}
	return new WP_REST_Response( $res, 200 );
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
if ( ! function_exists( 'mgk_rest_cancel_booking' ) ) {
	function mgk_rest_cancel_booking( WP_REST_Request $req ) {
		return new WP_Error( 'mgk_not_ready', 'Cancel not yet implemented.', [ 'status' => 501 ] );
	}
}
