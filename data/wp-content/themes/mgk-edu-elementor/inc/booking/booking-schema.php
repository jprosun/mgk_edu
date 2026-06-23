<?php
/**
 * MGK Booking Engine — schema (OWNERSHIP MOVED TO MODULE).
 * =======================================================
 * The four booking tables (mgk_bookings, mgk_slot_block_locks, mgk_payments,
 * mgk_booking_events) are NO LONGER defined here. Per SCHEMA-AND-MIGRATIONS.md
 * §4-Phase0 the definition was lifted into the reusable commerce module so every
 * scheduling-shaped industry shares ONE schema and the template owns 0 tables:
 *
 *     Margick\Commerce\Wp\BookingSchema   (mu-plugins/margick-commerce/src/Wp/)
 *
 * The module's SchemaMigrator (init:5, version-gated) now drives creation/upgrade
 * on every boot. This file keeps ONLY the backward-compatible PHP API that the
 * rest of inc/booking/ already calls — it delegates to the module (single source
 * of truth). Table names are UNCHANGED (§0 no-rename), so this is a pure ownership
 * move: zero data movement, dbDelta stays a no-op on existing installs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Margick\Commerce\Wp\BookingSchema;

if ( ! defined( 'MGK_BOOKING_SCHEMA_VERSION' ) ) {
	define(
		'MGK_BOOKING_SCHEMA_VERSION',
		class_exists( BookingSchema::class ) ? BookingSchema::SCHEMA_VERSION : '0.7.0'
	);
}

/**
 * Table name helpers — delegate to the module, the single owner of the real names
 * (LUẬT 3). Defensive local fallback keeps a standalone site alive if the commerce
 * module is somehow absent (it ships as a vendored mu-plugin, so normally present).
 *
 * @param 'bookings'|'locks'|'payments'|'events' $key
 */
function mgk_booking_table( $key ) {
	if ( class_exists( BookingSchema::class ) ) {
		return BookingSchema::table( $key );
	}
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
 * Install/upgrade the booking tables. Kept for manual triggers
 * (`wp eval 'mgk_booking_install_schema();'`) and any legacy caller. Delegates to
 * the module; the module's SchemaMigrator already runs this version-gated on boot.
 */
function mgk_booking_install_schema() {
	if ( ! class_exists( BookingSchema::class ) ) {
		error_log( '[mgk-booking] Margick\Commerce\Wp\BookingSchema missing — commerce module not loaded; booking tables not installed.' );
		return;
	}
	BookingSchema::install();
	update_option( BookingSchema::VERSION_OPTION, BookingSchema::SCHEMA_VERSION );
}
