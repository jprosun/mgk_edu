<?php
/**
 * MGK Booking Engine — Phase 0.5 · Hold expiry cron (cleanup + notify layer).
 * ===========================================================================
 * IMPORTANT (painpoint C): correctness does NOT depend on this cron. Expired
 * holds are already treated as gone by the lazy filter in booking-availability.php
 * and the in-transaction DELETE in booking-locks.php. If WP-Cron never fires, the
 * engine is still correct — slots free up on read/hold. This job only:
 *
 *   - flips HELD bookings whose hold lapsed → EXPIRED (durable record)
 *   - deletes their now-stale HOLD lock rows (housekeeping)
 *   - writes a HOLD_EXPIRED audit event (and is the hook point for notify later)
 *
 * Registers a 1-minute schedule (WP has no built-in sub-hourly interval).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Add a 60-second cron interval. */
add_filter( 'cron_schedules', function ( $schedules ) {
	if ( ! isset( $schedules['mgk_minute'] ) ) {
		$schedules['mgk_minute'] = [ 'interval' => 60, 'display' => 'Every Minute (MGK booking)' ];
	}
	return $schedules;
} );

/** Schedule on load if not already scheduled. */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'mgk_cron_expire_holds' ) ) {
		wp_schedule_event( time() + 60, 'mgk_minute', 'mgk_cron_expire_holds' );
	}
} );

add_action( 'mgk_cron_expire_holds', 'mgk_engine_expire_holds' );

/** Unschedule on theme switch-away. */
add_action( 'switch_theme', function () {
	$ts = wp_next_scheduled( 'mgk_cron_expire_holds' );
	if ( $ts ) wp_unschedule_event( $ts, 'mgk_cron_expire_holds' );
} );

/**
 * Expire lapsed holds. Idempotent — safe to run repeatedly / concurrently.
 * @return int number of bookings expired
 */
function mgk_engine_expire_holds() {
	global $wpdb;
	$bookings = mgk_booking_table( 'bookings' );
	$locks    = mgk_booking_table( 'locks' );
	if ( ! $bookings ) return 0;

	$now = mgk_booking_now_utc();

	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT id FROM {$bookings}
		 WHERE status = 'HELD' AND hold_expires_at_utc IS NOT NULL AND hold_expires_at_utc <= %s",
		$now
	) );

	$count = 0;
	foreach ( (array) $ids as $bid ) {
		$bid = (int) $bid;

		// Flip booking → EXPIRED (guard on status to stay idempotent under races).
		$updated = $wpdb->query( $wpdb->prepare(
			"UPDATE {$bookings} SET status = 'EXPIRED', payment_status = 'EXPIRED', updated_at_utc = %s
			 WHERE id = %d AND status = 'HELD'",
			$now, $bid
		) );
		if ( ! $updated ) continue; // someone else already handled it

		// Delete its HOLD locks (BOOKING locks, if any, are left intact).
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$locks} WHERE booking_id = %d AND lock_type = 'HOLD'",
			$bid
		) );

		mgk_log_booking_event( $bid, 'HOLD_EXPIRED', [
			'old_status' => 'HELD',
			'new_status' => 'EXPIRED',
			'actor_type' => 'SYSTEM',
		] );

		// Hook for notifications / lead state reversion (handled elsewhere).
		do_action( 'mgk_booking_hold_expired', $bid );
		$count++;
	}

	return $count;
}
