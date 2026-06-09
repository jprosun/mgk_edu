<?php
/**
 * MGK Booking Engine — Phase 0.5 · One-way mirror mgk_bookings → mg_booking.
 * =========================================================================
 * Painpoint D resolution (the option chosen by the project owner):
 *   mgk_bookings is the SINGLE SOURCE OF TRUTH. The mg_booking CPT is a read-only
 *   VIEW so the engine's confirmed bookings show up in wp-admin and remain
 *   compatible with the existing WooCommerce bridge.
 *
 * Rules:
 *   - Mirror is ONE-WAY: engine → CPT. Never CPT → engine.
 *   - Only mirror at "display-worthy" milestones (CONFIRMED onward), never the
 *     transient HELD/PENDING states.
 *   - Idempotent: keyed by meta 'mgk_engine_booking_id' (1:1), so re-firing the
 *     hook updates the same CPT post instead of creating duplicates.
 *   - Reverse-write guard: edits to a mirrored mg_booking post in wp-admin do NOT
 *     propagate back to the engine (the engine simply never reads the CPT).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Mirror a confirmed booking into mg_booking. Hooked to mgk_booking_confirmed. */
add_action( 'mgk_booking_confirmed', 'mgk_mirror_booking_to_cpt', 10, 1 );

/** Also mirror on cancellation/completion so the view stays accurate. */
add_action( 'mgk_booking_status_changed', function ( $booking_id, $old, $new ) {
	if ( in_array( $new, [ 'CONFIRMED', 'RESCHEDULED', 'COMPLETED', 'CANCELLED', 'NO_SHOW', 'MANUAL_REVIEW' ], true ) ) {
		mgk_mirror_booking_to_cpt( $booking_id );
	}
}, 10, 3 );

function mgk_mirror_booking_to_cpt( $booking_id ) {
	if ( ! post_type_exists( 'mg_booking' ) ) return;
	$row = mgk_get_booking_row( (int) $booking_id );
	if ( ! $row ) return;

	// Only mirror display-worthy states.
	$mirrorable = [ 'CONFIRMED', 'RESCHEDULED', 'COMPLETED', 'CANCELLED', 'NO_SHOW', 'MANUAL_REVIEW' ];
	if ( ! in_array( $row['status'], $mirrorable, true ) ) return;

	$existing = mgk_find_cpt_for_booking( (int) $booking_id );

	$tutor_title = get_the_title( (int) $row['tutor_post_id'] );
	$title = sprintf( '%s — %s (%s)', $row['booking_code'], $tutor_title ?: ( 'Tutor #' . $row['tutor_post_id'] ), $row['status'] );

	$postarr = [
		'post_type'   => 'mg_booking',
		'post_status' => 'publish',
		'post_title'  => $title,
	];
	if ( $existing ) {
		$postarr['ID'] = $existing;
		$post_id = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $post_id ) || ! $post_id ) return;

	// Mirror fields (legacy slot meta kept so mgk_is_slot_booked() etc. still work).
	$meta = [
		'mgk_engine_booking_id' => (int) $booking_id,
		'mgk_booking_code'      => $row['booking_code'],
		'mgk_booking_status'    => $row['status'],
		'mgk_payment_status'    => $row['payment_status'],
		'mgk_tutor_id'          => (int) $row['tutor_post_id'],
		'mgk_student_name'      => $row['student_name'],
		'mgk_subject'           => $row['subject'],
		'mgk_lesson_type'       => $row['lesson_type'],
		'mgk_start_at_utc'      => $row['start_at_utc'],
		'mgk_end_at_utc'        => $row['end_at_utc'],
		'mgk_price_amount'      => $row['price_amount'],
		'mgk_currency'          => $row['currency'],
		'mgk_slot_id'           => $row['slot_key'], // legacy compatibility
	];
	foreach ( $meta as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}
}

/** Find the mg_booking CPT post mirroring a given engine booking id, or 0. */
function mgk_find_cpt_for_booking( $booking_id ) {
	$q = get_posts( [
		'post_type'      => 'mg_booking',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[ 'key' => 'mgk_engine_booking_id', 'value' => (int) $booking_id, 'compare' => '=' ],
		],
	] );
	return $q ? (int) $q[0] : 0;
}
