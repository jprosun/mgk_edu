<?php
/**
 * MGK Booking Engine — Phase 0.5 · Custom table schema (LOCKED DATA CORE).
 * =======================================================================
 * Four custom tables that are the SOURCE OF TRUTH for the real booking engine,
 * replacing the transient-based demo hold:
 *
 *   wp_mgk_bookings          — one row per trial/package/reschedule lesson
 *   wp_mgk_slot_block_locks  — 15-min block locks; UNIQUE key prevents double-book
 *   wp_mgk_payments          — Stripe checkout/intent linkage + idempotency
 *   wp_mgk_booking_events     — audit log + webhook idempotency
 *
 * Design decisions (Phase 0.5 errata, see plan §1, §6, §15-16, §22):
 *   - Painpoint A: slot_block_locks UNIQUE key is (tutor_post_id, block_start_at_utc)
 *     ONLY — NOT including lock_status. A lock row exists ONLY while it blocks the
 *     slot; on release/expiry we DELETE the row (not flip to RELEASED). History
 *     lives in mgk_booking_events, not here. This keeps the UNIQUE key clean and
 *     avoids "Duplicate entry" on the 2nd release of the same block.
 *   - Painpoint D: mgk_bookings is the single source of truth. mg_booking CPT is a
 *     one-way read-only mirror (see booking-mirror.php).
 *   - No tenant_id (one site = one agency tenant, plan §1.1).
 *   - All datetimes stored in UTC (DATETIME columns named *_utc).
 *
 * Versioned via the 'mgk_booking_schema_version' option so dbDelta runs again
 * when the schema changes. Runs on after_switch_theme + admin_init guard.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_BOOKING_SCHEMA_VERSION' ) ) define( 'MGK_BOOKING_SCHEMA_VERSION', '0.7.0' );

/** Table name helpers — single place that owns the real table names. */
function mgk_booking_table( $key ) {
	global $wpdb;
	$map = [
		'bookings' => $wpdb->prefix . 'mgk_bookings',
		'locks'    => $wpdb->prefix . 'mgk_slot_block_locks',
		'payments' => $wpdb->prefix . 'mgk_payments',
		'events'   => $wpdb->prefix . 'mgk_booking_events',
	];
	return $map[ $key ] ?? '';
}

/**
 * Create/upgrade the four booking tables via dbDelta.
 * Idempotent — safe to call repeatedly. Uses the DB's own charset/collate so the
 * tables match the existing schema (utf8mb3_general_ci on this install).
 */
function mgk_booking_install_schema() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();
	$bookings = mgk_booking_table( 'bookings' );
	$locks    = mgk_booking_table( 'locks' );
	$payments = mgk_booking_table( 'payments' );
	$events   = mgk_booking_table( 'events' );

	// ── mgk_bookings — source of truth ─────────────────────────────────
	$sql_bookings = "CREATE TABLE {$bookings} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		booking_code VARCHAR(64) NOT NULL,
		tutor_post_id BIGINT UNSIGNED NOT NULL,
		lead_id BIGINT UNSIGNED NULL,
		parent_user_id BIGINT UNSIGNED NULL,
		child_id BIGINT UNSIGNED NULL,
		student_name VARCHAR(190) NULL,
		subject VARCHAR(190) NULL,
		lesson_type VARCHAR(40) NOT NULL DEFAULT 'TRIAL',
		slot_key VARCHAR(190) NULL,
		start_at_utc DATETIME NOT NULL,
		end_at_utc DATETIME NOT NULL,
		timezone VARCHAR(64) NOT NULL DEFAULT 'Asia/Singapore',
		status VARCHAR(40) NOT NULL DEFAULT 'HELD',
		payment_status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
		price_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
		base_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
		discount_applied LONGTEXT NULL,
		voucher_code VARCHAR(64) NULL,
		currency VARCHAR(10) NOT NULL DEFAULT 'SGD',
		idempotency_key VARCHAR(190) NULL,
		hold_expires_at_utc DATETIME NULL,
		confirmed_at_utc DATETIME NULL,
		cancelled_at_utc DATETIME NULL,
		created_at_utc DATETIME NOT NULL,
		updated_at_utc DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY uniq_booking_code (booking_code),
		UNIQUE KEY uniq_idempotency (idempotency_key),
		KEY idx_tutor_time (tutor_post_id, start_at_utc, end_at_utc),
		KEY idx_status (status),
		KEY idx_payment_status (payment_status),
		KEY idx_hold_expires (status, hold_expires_at_utc)
	) {$charset_collate};";

	// ── mgk_slot_block_locks — double-book prevention (painpoint A) ─────
	// UNIQUE is (tutor_post_id, block_start_at_utc) ONLY. Row exists only while
	// the block is held/booked. Release = DELETE row.
	$sql_locks = "CREATE TABLE {$locks} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		tutor_post_id BIGINT UNSIGNED NOT NULL,
		booking_id BIGINT UNSIGNED NOT NULL,
		block_start_at_utc DATETIME NOT NULL,
		lock_type VARCHAR(20) NOT NULL DEFAULT 'HOLD',
		expires_at_utc DATETIME NULL,
		created_at_utc DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY uniq_active_block (tutor_post_id, block_start_at_utc),
		KEY idx_booking (booking_id),
		KEY idx_expires (lock_type, expires_at_utc)
	) {$charset_collate};";

	// ── mgk_payments — Stripe linkage + idempotency ────────────────────
	$sql_payments = "CREATE TABLE {$payments} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		booking_id BIGINT UNSIGNED NOT NULL,
		provider VARCHAR(40) NOT NULL DEFAULT 'STRIPE',
		provider_checkout_session_id VARCHAR(190) NULL,
		provider_payment_intent_id VARCHAR(190) NULL,
		latest_webhook_event_id VARCHAR(190) NULL,
		amount DECIMAL(10,2) NOT NULL,
		currency VARCHAR(10) NOT NULL DEFAULT 'SGD',
		status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
		paid_at_utc DATETIME NULL,
		failed_at_utc DATETIME NULL,
		created_at_utc DATETIME NOT NULL,
		updated_at_utc DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY idx_booking (booking_id),
		UNIQUE KEY uniq_checkout_session (provider_checkout_session_id),
		UNIQUE KEY uniq_payment_intent (provider_payment_intent_id),
		KEY idx_status (status)
	) {$charset_collate};";

	// ── mgk_booking_events — audit log + webhook idempotency ───────────
	$sql_events = "CREATE TABLE {$events} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		booking_id BIGINT UNSIGNED NULL,
		actor_type VARCHAR(40) NOT NULL DEFAULT 'SYSTEM',
		actor_id BIGINT UNSIGNED NULL,
		event_type VARCHAR(80) NOT NULL,
		old_status VARCHAR(40) NULL,
		new_status VARCHAR(40) NULL,
		provider VARCHAR(40) NULL,
		provider_event_id VARCHAR(190) NULL,
		metadata_json LONGTEXT NULL,
		created_at_utc DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY idx_booking (booking_id),
		KEY idx_event_type (event_type),
		UNIQUE KEY uniq_provider_event (provider, provider_event_id)
	) {$charset_collate};";

	dbDelta( $sql_bookings );
	dbDelta( $sql_locks );
	dbDelta( $sql_payments );
	dbDelta( $sql_events );

	update_option( 'mgk_booking_schema_version', MGK_BOOKING_SCHEMA_VERSION );
}

/** Run on theme switch. */
add_action( 'after_switch_theme', 'mgk_booking_install_schema' );

/**
 * Lightweight guard: if the stored version differs from the constant, run the
 * installer. Cheap option read on each admin load; only runs dbDelta on change.
 */
add_action( 'admin_init', function () {
	if ( get_option( 'mgk_booking_schema_version' ) !== MGK_BOOKING_SCHEMA_VERSION ) {
		mgk_booking_install_schema();
	}
} );

/** WP-CLI / manual trigger: `wp eval 'mgk_booking_install_schema();'`. */
