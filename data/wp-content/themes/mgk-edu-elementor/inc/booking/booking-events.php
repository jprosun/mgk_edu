<?php
/**
 * MGK Booking Engine — Phase 0.5 · Audit log + webhook idempotency (LOCKED).
 * ==========================================================================
 * Every important state change writes one row to wp_mgk_booking_events. This is
 * the debugging backbone for slot races and payment mismatches, and the
 * idempotency store for provider webhooks (UNIQUE provider+provider_event_id).
 *
 * Also holds tiny shared row-access helpers used across the booking engine so
 * the table names live in exactly one place (booking-schema.php).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Current UTC timestamp as MySQL DATETIME (single source for the engine). */
function mgk_booking_now_utc() {
	return gmdate( 'Y-m-d H:i:s' );
}

/**
 * Write an audit event. Never throws — logging must not break a transaction.
 *
 * @param int|null $booking_id
 * @param string   $event_type  e.g. SLOT_HELD, HOLD_EXPIRED, PAYMENT_SUCCEEDED…
 * @param array    $args        old_status,new_status,actor_type,actor_id,
 *                              provider,provider_event_id,metadata(array)
 * @return int|false  inserted row id, or false on failure / dup webhook.
 */
function mgk_log_booking_event( $booking_id, $event_type, $args = [] ) {
	global $wpdb;
	$table = mgk_booking_table( 'events' );
	if ( ! $table ) return false;

	$meta = $args['metadata'] ?? null;

	$ok = $wpdb->insert(
		$table,
		[
			'booking_id'        => $booking_id ? (int) $booking_id : null,
			'actor_type'        => sanitize_text_field( $args['actor_type'] ?? 'SYSTEM' ),
			'actor_id'          => isset( $args['actor_id'] ) ? (int) $args['actor_id'] : null,
			'event_type'        => sanitize_text_field( $event_type ),
			'old_status'        => isset( $args['old_status'] ) ? sanitize_text_field( $args['old_status'] ) : null,
			'new_status'        => isset( $args['new_status'] ) ? sanitize_text_field( $args['new_status'] ) : null,
			'provider'          => isset( $args['provider'] ) ? sanitize_text_field( $args['provider'] ) : null,
			'provider_event_id' => isset( $args['provider_event_id'] ) ? sanitize_text_field( $args['provider_event_id'] ) : null,
			'metadata_json'     => $meta !== null ? wp_json_encode( $meta ) : null,
			'created_at_utc'    => mgk_booking_now_utc(),
		]
	);

	return $ok ? (int) $wpdb->insert_id : false;
}

/**
 * Webhook idempotency check. Returns true if this provider event was already
 * processed (so the caller can return 200 and skip re-processing).
 *
 * The UNIQUE KEY uniq_provider_event(provider, provider_event_id) is the real
 * guard against a race between two concurrent deliveries — this read is the fast
 * path; mgk_mark_webhook_processed() is the authoritative claim.
 */
function mgk_webhook_already_processed( $provider, $event_id ) {
	global $wpdb;
	$table = mgk_booking_table( 'events' );
	if ( ! $table || ! $event_id ) return false;

	$found = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM {$table} WHERE provider = %s AND provider_event_id = %s LIMIT 1",
		$provider,
		$event_id
	) );
	return ! empty( $found );
}

/**
 * Atomically claim a webhook event. Returns true if THIS call won the claim
 * (caller should process), false if it was already claimed (caller skips).
 * Relies on the UNIQUE constraint: a duplicate insert simply fails.
 */
function mgk_mark_webhook_processed( $provider, $event_id, $event_type = '', $booking_id = null ) {
	global $wpdb;
	$table = mgk_booking_table( 'events' );
	if ( ! $table || ! $event_id ) return false;

	// Suppress the duplicate-key warning; we read the boolean result instead.
	$prev = $wpdb->show_errors;
	$wpdb->hide_errors();
	$ok = $wpdb->insert(
		$table,
		[
			'booking_id'        => $booking_id ? (int) $booking_id : null,
			'actor_type'        => 'WEBHOOK',
			'event_type'        => $event_type ? sanitize_text_field( $event_type ) : 'WEBHOOK_RECEIVED',
			'provider'          => sanitize_text_field( $provider ),
			'provider_event_id' => sanitize_text_field( $event_id ),
			'created_at_utc'    => mgk_booking_now_utc(),
		]
	);
	$wpdb->show_errors = $prev;

	return (bool) $ok; // false → duplicate (UNIQUE) → already claimed.
}

/* ── Shared row accessors (used across the engine) ─────────────────────── */

/** Fetch a booking row as an associative array, or null. */
function mgk_get_booking_row( $booking_id ) {
	global $wpdb;
	$table = mgk_booking_table( 'bookings' );
	if ( ! $table || ! $booking_id ) return null;
	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
		(int) $booking_id
	), ARRAY_A );
	return $row ?: null;
}

/** Fetch a booking row by its public booking_code, or null. */
function mgk_get_booking_by_code( $code ) {
	global $wpdb;
	$table = mgk_booking_table( 'bookings' );
	if ( ! $table || ! $code ) return null;
	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE booking_code = %s LIMIT 1",
		sanitize_text_field( $code )
	), ARRAY_A );
	return $row ?: null;
}

/** Recent audit events for a booking (newest first). */
function mgk_get_booking_events( $booking_id, $limit = 50 ) {
	global $wpdb;
	$table = mgk_booking_table( 'events' );
	if ( ! $table || ! $booking_id ) return [];
	return (array) $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE booking_id = %d ORDER BY id DESC LIMIT %d",
		(int) $booking_id,
		(int) $limit
	), ARRAY_A );
}
