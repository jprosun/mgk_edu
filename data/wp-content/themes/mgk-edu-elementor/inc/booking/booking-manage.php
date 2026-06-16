<?php
/**
 * MGK Booking Engine — Manage: Cancel / Refund / Reschedule.
 * ==========================================================
 * Backend for the S12 "manage booking" actions (the UI + refund/reschedule
 * PREVIEW already existed in mgk-confirmation.php; this is the real engine).
 *
 *   CANCEL + REFUND (BR-07 / FR-PAY-10):
 *     - Unpaid hold → just release the slot.
 *     - Paid lesson/trial → tiered refund by notice: ≥48h = full, 24–48h = 50%,
 *       <24h = 0%. Real Stripe refund against the payment intent.
 *     - Paid package → pro-rata refund on UNUSED lessons (remaining/total).
 *     Sets status CANCELLED, payment REFUNDED/PARTIALLY_REFUNDED, releases locks,
 *     cancels any enrolment, fires `mgk_booking_cancelled`.
 *
 *   RESCHEDULE (BR-23 / FR-BOOK-09):
 *     - Confirmed lesson only (packages have no slot).
 *     - Max MGK_RESCHEDULE_LIMIT (2) times, must give ≥ MGK_RESCHEDULE_FREE_HOURS
 *       (24h) notice. New slot is atomically re-locked (UNIQUE block guard) so it
 *       can't collide with another booking.
 *
 * Money rules + thresholds are the LOCKED constants in mgk-confirmation.php.
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Helpers ────────────────────────────────────────────────────────────── */

/** Count how many events of a type a booking has (e.g. reschedules used). */
function mgk_booking_event_count( $booking_id, $event_type ) {
	global $wpdb;
	if ( ! function_exists( 'mgk_booking_table' ) ) return 0;
	$t = mgk_booking_table( 'events' );
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$t} WHERE booking_id = %d AND event_type = %s",
		(int) $booking_id, $event_type
	) );
}

/** Can the current user manage this booking? (its parent, or an admin.) */
function mgk_booking_user_can_manage( $row ) {
	if ( current_user_can( 'manage_options' ) ) return true;
	$uid = get_current_user_id();
	return $uid > 0 && (int) ( $row['parent_user_id'] ?? 0 ) === $uid;
}

/** Whole hours from now until a UTC datetime (negative if past). */
function mgk_hours_until_utc( $utc ) {
	if ( ! $utc ) return 0;
	return (int) floor( ( strtotime( $utc . ' UTC' ) - time() ) / 3600 );
}

/** BR-07 refund tier for a lesson, from hours of notice. */
function mgk_refund_tier_for_hours( $hours_out ) {
	if ( $hours_out >= MGK_REFUND_FULL_HOURS ) return [ 'tier' => 'full', 'pct' => 100 ];
	if ( $hours_out >= MGK_REFUND_HALF_HOURS ) return [ 'tier' => 'half', 'pct' => 50 ];
	return [ 'tier' => 'none', 'pct' => 0 ];
}

/** Lessons already consumed under an enrolment (ATTENDED/LATE count). */
function mgk_enrolment_lessons_used( $enrolment_id ) {
	if ( ! $enrolment_id || ! function_exists( 'mgk_lessons_for_enrolment' ) ) return 0;
	$used = 0;
	foreach ( (array) mgk_lessons_for_enrolment( $enrolment_id ) as $l ) {
		$att = is_array( $l ) ? ( $l['attendance'] ?? '' ) : get_post_meta( (int) $l, 'mgk_lesson_attendance', true );
		if ( in_array( strtoupper( (string) $att ), [ 'ATTENDED', 'LATE' ], true ) ) $used++;
	}
	return $used;
}

/** Find the enrolment created from a (package) booking. */
function mgk_enrolment_for_booking( $booking_id ) {
	$q = get_posts( [
		'post_type'   => 'mg_enrolment',
		'numberposts' => 1,
		'fields'      => 'ids',
		'meta_query'  => [ [ 'key' => 'mgk_enr_source_booking_id', 'value' => (int) $booking_id ] ],
	] );
	return $q ? (int) $q[0] : 0;
}

/* ── Refund computation (preview-safe, no side effects) ─────────────────── */
/**
 * What WOULD be refunded if this booking were cancelled now.
 * @return array{amount:float, tier:string, basis:string, hours_out:int}
 */
function mgk_engine_refund_quote( $row ) {
	$price = (float) ( $row['price_amount'] ?? 0 );
	$paid  = ( ( $row['payment_status'] ?? '' ) === 'PAID' ) || ( ( $row['status'] ?? '' ) === 'CONFIRMED' );
	if ( ! $paid ) {
		return [ 'amount' => 0.0, 'tier' => 'none', 'basis' => 'unpaid', 'hours_out' => 0 ];
	}

	$plan = function_exists( 'mgk_package_plan_from_lesson_type' ) ? mgk_package_plan_from_lesson_type( (string) $row['lesson_type'] ) : '';
	if ( $plan ) {
		// Package: pro-rata on unused lessons.
		$enr   = mgk_enrolment_for_booking( (int) $row['id'] );
		$total = $enr ? (int) get_post_meta( $enr, 'mgk_enr_lessons_total', true ) : ( function_exists( 'mgk_package_plan_lessons' ) ? mgk_package_plan_lessons( $plan ) : 0 );
		$used  = $enr ? mgk_enrolment_lessons_used( $enr ) : 0;
		$remaining = max( 0, $total - $used );
		$amount = $total > 0 ? round( $price * $remaining / $total, 2 ) : $price;
		return [ 'amount' => $amount, 'tier' => 'prorata', 'basis' => sprintf( '%d of %d lessons unused', $remaining, $total ), 'hours_out' => 0 ];
	}

	// Lesson/trial: BR-07 tier by notice.
	$hours_out = mgk_hours_until_utc( (string) $row['start_at_utc'] );
	$t = mgk_refund_tier_for_hours( $hours_out );
	return [ 'amount' => round( $price * $t['pct'] / 100, 2 ), 'tier' => $t['tier'], 'basis' => $t['pct'] . '% (' . $t['tier'] . ')', 'hours_out' => $hours_out ];
}

/* ── Cancel + refund ────────────────────────────────────────────────────── */
function mgk_engine_cancel_booking( $booking_id, $args = [] ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return new WP_Error( 'mgk_no_booking', 'Booking not found.', [ 'status' => 404 ] );
	if ( in_array( $row['status'], [ 'CANCELLED', 'EXPIRED' ], true ) ) {
		return new WP_Error( 'mgk_already_cancelled', 'This booking is already cancelled.', [ 'status' => 409 ] );
	}

	// Unpaid hold → simple release (frees the slot), no refund.
	if ( in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT' ], true ) && $row['payment_status'] !== 'PAID' ) {
		$rel = mgk_engine_release_hold( $booking_id );
		if ( is_wp_error( $rel ) ) return $rel;
		return [ 'cancelled' => true, 'refund_amount' => 0.0, 'tier' => 'unpaid', 'mode' => 'none' ];
	}

	$quote  = mgk_engine_refund_quote( $row );
	$refund = [ 'refunded' => 0.0, 'mode' => 'none', 'full' => false ];
	if ( $quote['amount'] > 0 && function_exists( 'mgk_stripe_refund_payment' ) ) {
		$refund = mgk_stripe_refund_payment( $booking_id, $quote['amount'] );
		if ( is_wp_error( $refund ) ) return $refund;
	}

	$now = mgk_booking_now_utc();
	mgk_engine_release_locks( $booking_id );

	$pay_status = $row['payment_status'];
	if ( ! empty( $refund['refunded'] ) ) {
		$pay_status = ! empty( $refund['full'] ) ? 'REFUNDED' : 'PARTIALLY_REFUNDED';
	}
	$wpdb->update( mgk_booking_table( 'bookings' ),
		[ 'status' => 'CANCELLED', 'payment_status' => $pay_status, 'cancelled_at_utc' => $now, 'updated_at_utc' => $now ],
		[ 'id' => $booking_id ]
	);

	// Cancel a package enrolment too.
	$enr = mgk_enrolment_for_booking( $booking_id );
	if ( $enr ) update_post_meta( $enr, 'mgk_enr_status', 'CANCELLED' );

	mgk_log_booking_event( $booking_id, 'BOOKING_CANCELLED', [
		'old_status' => $row['status'], 'new_status' => 'CANCELLED',
		'actor_type' => current_user_can( 'manage_options' ) ? 'ADMIN' : 'PARENT',
		'metadata'   => [ 'reason' => sanitize_text_field( (string) ( $args['reason'] ?? '' ) ), 'refund' => $refund['refunded'] ?? 0, 'tier' => $quote['tier'] ],
	] );

	if ( function_exists( 'mgk_lead_transition' ) && ! empty( $row['lead_id'] ) && defined( 'MGK_LEAD_CANCELLED' ) ) {
		// best-effort lead state sync (ignore if not allowed)
		if ( function_exists( 'mgk_lead_can_transition' ) ) {
			$cur = get_post_meta( (int) $row['lead_id'], 'mgk_lead_state', true );
			if ( $cur && mgk_lead_can_transition( $cur, MGK_LEAD_CANCELLED ) ) mgk_lead_transition( (int) $row['lead_id'], MGK_LEAD_CANCELLED );
		}
	}

	do_action( 'mgk_booking_cancelled', $booking_id, $refund );

	return [
		'cancelled'     => true,
		'refund_amount' => (float) ( $refund['refunded'] ?? 0 ),
		'tier'          => $quote['tier'],
		'basis'         => $quote['basis'],
		'mode'          => $refund['mode'] ?? 'none',
	];
}

/* ── Reschedule (BR-23) ─────────────────────────────────────────────────── */
function mgk_engine_reschedule_booking( $booking_id, $new_start_utc, $new_end_utc ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return new WP_Error( 'mgk_no_booking', 'Booking not found.', [ 'status' => 404 ] );

	if ( function_exists( 'mgk_package_plan_from_lesson_type' ) && mgk_package_plan_from_lesson_type( (string) $row['lesson_type'] ) ) {
		return new WP_Error( 'mgk_not_reschedulable', 'Packages are scheduled per lesson, not rescheduled here.', [ 'status' => 409 ] );
	}
	if ( $row['status'] !== 'CONFIRMED' ) {
		return new WP_Error( 'mgk_not_confirmed', 'Only a confirmed lesson can be rescheduled.', [ 'status' => 409 ] );
	}

	$used = mgk_booking_event_count( $booking_id, 'BOOKING_RESCHEDULED' );
	if ( $used >= MGK_RESCHEDULE_LIMIT ) {
		return new WP_Error( 'mgk_reschedule_limit', sprintf( 'You can reschedule at most %d times.', MGK_RESCHEDULE_LIMIT ), [ 'status' => 409 ] );
	}
	if ( mgk_hours_until_utc( (string) $row['start_at_utc'] ) < MGK_RESCHEDULE_FREE_HOURS ) {
		return new WP_Error( 'mgk_reschedule_too_late', sprintf( 'Reschedule needs at least %dh notice.', MGK_RESCHEDULE_FREE_HOURS ), [ 'status' => 409 ] );
	}

	// Validate the new window.
	$new_start_utc = gmdate( 'Y-m-d H:i:s', strtotime( (string) $new_start_utc . ' UTC' ) );
	$new_end_utc   = gmdate( 'Y-m-d H:i:s', strtotime( (string) $new_end_utc . ' UTC' ) );
	if ( ! $new_start_utc || ! $new_end_utc || strtotime( $new_end_utc ) <= strtotime( $new_start_utc ) ) {
		return new WP_Error( 'mgk_bad_slot', 'Invalid new time.', [ 'status' => 400 ] );
	}
	if ( strtotime( $new_start_utc . ' UTC' ) <= time() ) {
		return new WP_Error( 'mgk_past_slot', 'Pick a future time.', [ 'status' => 400 ] );
	}

	$tutor_id = (int) $row['tutor_post_id'];
	$blocks   = mgk_expand_to_blocks( $new_start_utc, $new_end_utc );
	if ( empty( $blocks ) ) return new WP_Error( 'mgk_bad_slot', 'New slot has no blocks.', [ 'status' => 400 ] );

	$now   = mgk_booking_now_utc();
	$locks = mgk_booking_table( 'locks' );

	$wpdb->query( 'START TRANSACTION' );
	// Drop this booking's current locks, then claim the new blocks. Any collision
	// with another booking → ROLLBACK restores the old locks (atomic).
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$locks} WHERE booking_id = %d", $booking_id ) );

	$prev = $wpdb->show_errors; $wpdb->hide_errors();
	foreach ( $blocks as $blk ) {
		$ok = $wpdb->insert( $locks, [
			'tutor_post_id'      => $tutor_id,
			'booking_id'         => $booking_id,
			'block_start_at_utc' => $blk,
			'lock_type'          => 'BOOKING',
			'expires_at_utc'     => null,
			'created_at_utc'     => $now,
		] );
		if ( ! $ok ) {
			$wpdb->show_errors = $prev;
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error( 'mgk_slot_taken', 'That time was just taken — pick another.', [
				'status' => 409, 'alternatives' => function_exists( 'mgk_engine_alternatives' ) ? mgk_engine_alternatives( $tutor_id, $new_start_utc ) : [],
			] );
		}
	}
	$wpdb->show_errors = $prev;

	$old_start = $row['start_at_utc'];
	$wpdb->update( mgk_booking_table( 'bookings' ), [
		'start_at_utc' => $new_start_utc,
		'end_at_utc'   => $new_end_utc,
		'slot_key'     => mgk_slot_key( $tutor_id, $new_start_utc, $new_end_utc ),
		'updated_at_utc' => $now,
	], [ 'id' => $booking_id ] );

	$wpdb->query( 'COMMIT' );

	mgk_log_booking_event( $booking_id, 'BOOKING_RESCHEDULED', [
		'actor_type' => current_user_can( 'manage_options' ) ? 'ADMIN' : 'PARENT',
		'metadata'   => [ 'from' => $old_start, 'to' => $new_start_utc, 'reschedule_number' => $used + 1 ],
	] );
	do_action( 'mgk_booking_rescheduled', $booking_id, $old_start, $new_start_utc );

	return [
		'rescheduled'      => true,
		'start_at_utc'     => $new_start_utc,
		'end_at_utc'       => $new_end_utc,
		'reschedules_used' => $used + 1,
		'reschedules_left' => max( 0, MGK_RESCHEDULE_LIMIT - ( $used + 1 ) ),
	];
}

/* ── REST: cancel + reschedule (parent-owns or admin) ───────────────────── */
add_action( 'rest_api_init', function () {
	register_rest_route( 'mgk/v1', '/booking/(?P<id>\d+)/reschedule', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_reschedule_booking',
		'permission_callback' => 'is_user_logged_in',
		'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
	] );
} );

/** Real cancel handler (replaces the former 501 stub registered in booking-rest.php). */
function mgk_rest_cancel_booking( WP_REST_Request $req ) {
	$row = mgk_get_booking_row( (int) $req['id'] );
	if ( ! $row ) return new WP_REST_Response( [ 'error' => 'no_booking', 'message' => 'Booking not found.' ], 404 );
	if ( ! mgk_booking_user_can_manage( $row ) ) {
		return new WP_REST_Response( [ 'error' => 'forbidden', 'message' => 'Not your booking.' ], 403 );
	}
	$body = $req->get_json_params(); if ( ! is_array( $body ) ) $body = $req->get_params();
	$res = mgk_engine_cancel_booking( (int) $req['id'], [ 'reason' => (string) ( $body['reason'] ?? '' ) ] );
	if ( is_wp_error( $res ) ) {
		$d = $res->get_error_data(); $s = is_array( $d ) && isset( $d['status'] ) ? (int) $d['status'] : 400;
		return new WP_REST_Response( [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ], $s );
	}
	return new WP_REST_Response( $res, 200 );
}

function mgk_rest_reschedule_booking( WP_REST_Request $req ) {
	$row = mgk_get_booking_row( (int) $req['id'] );
	if ( ! $row ) return new WP_REST_Response( [ 'error' => 'no_booking', 'message' => 'Booking not found.' ], 404 );
	if ( ! mgk_booking_user_can_manage( $row ) ) {
		return new WP_REST_Response( [ 'error' => 'forbidden', 'message' => 'Not your booking.' ], 403 );
	}
	$body = $req->get_json_params(); if ( ! is_array( $body ) ) $body = $req->get_params();
	$start = (string) ( $body['start_at_utc'] ?? '' );
	$end   = (string) ( $body['end_at_utc'] ?? '' );
	$res = mgk_engine_reschedule_booking( (int) $req['id'], $start, $end );
	if ( is_wp_error( $res ) ) {
		$d = $res->get_error_data(); $s = is_array( $d ) && isset( $d['status'] ) ? (int) $d['status'] : 400;
		$out = [ 'error' => $res->get_error_code(), 'message' => $res->get_error_message() ];
		if ( is_array( $d ) && ! empty( $d['alternatives'] ) ) $out['alternatives'] = $d['alternatives'];
		return new WP_REST_Response( $out, $s );
	}
	return new WP_REST_Response( $res, 200 );
}
