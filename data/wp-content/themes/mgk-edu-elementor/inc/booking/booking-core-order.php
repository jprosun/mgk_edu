<?php
/**
 * Dual-write: keep a generic core order (margick/commerce) in sync with an edu
 * booking across its lifecycle. ADDITIVE — mgk_bookings stays the booking source
 * of truth; this writes an industry-blind order BESIDE it (no field removed, no
 * existing row touched), so mgk_core_orders/order_items become the shared spine
 * for reporting + future non-booking sellables.
 *
 * Lifecycle → core order status (the core now reflects the WHOLE funnel, incl.
 * unpaid holds, per owner's request):
 *     mgk_booking_held        → PENDING   (created at hold time)
 *     mgk_booking_confirmed   → PAID      (created if a hold-order is missing)
 *     mgk_booking_hold_expired→ EXPIRED   (update only — never resurrect)
 *     mgk_booking_cancelled   → CANCELLED (update only)
 *
 * Direction (SCHEMA-AND-MIGRATIONS.md): EDU glue (industry → core). Edu knows
 * lesson_type/tutor/base/price; OrderRepository knows only the generic shape.
 * Idempotent via the deterministic order_code 'BKG-<booking_code>' (UNIQUE).
 * Priority 20: after parent-identity claim (10) so parent_user_id is on the row.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Margick\Commerce\Wp\OrderRepository;

add_action( 'mgk_booking_held',         function ( $id ) { mgk_sync_core_order_for_booking( $id, 'PENDING',   true ); },  20, 1 );
add_action( 'mgk_booking_confirmed',     function ( $id ) { mgk_sync_core_order_for_booking( $id, 'PAID',      true ); },  20, 1 );
add_action( 'mgk_booking_hold_expired',  function ( $id ) { mgk_sync_core_order_for_booking( $id, 'EXPIRED',   false ); }, 20, 1 );
add_action( 'mgk_booking_cancelled',     function ( $id ) { mgk_sync_core_order_for_booking( $id, 'CANCELLED', false ); }, 20, 1 );

/**
 * Find-or-create the core order for a booking and set its status. Idempotent.
 *
 * @param int    $booking_id
 * @param string $status            target core order status
 * @param bool   $create_if_missing create the order (+line item) when absent
 * @return int   order id (0 on no-op/failure)
 */
function mgk_sync_core_order_for_booking( $booking_id, $status, $create_if_missing = true ) {
	if ( ! class_exists( '\\Margick\\Commerce\\Wp\\OrderRepository' ) ) return 0; // module absent → no-op
	if ( ! function_exists( 'mgk_get_booking_row' ) ) return 0;

	$row = mgk_get_booking_row( (int) $booking_id );
	if ( ! $row ) return 0;

	$code = 'BKG-' . $row['booking_code'];
	$oid  = OrderRepository::findIdByCode( $code );

	// Already mirrored → just advance status (+ attach the payer if claimed late).
	if ( $oid ) {
		$prev = (string) ( OrderRepository::getOrder( $oid )['status'] ?? '' );
		OrderRepository::updateStatus( $oid, (string) $status );
		if ( ! empty( $row['parent_user_id'] ) ) {
			OrderRepository::assignCustomer( $oid, (int) $row['parent_user_id'] );
		}
		// Fulfill once, on the PENDING→PAID edge only (idempotent vs webhook retries).
		if ( $status === 'PAID' && $prev !== 'PAID' ) {
			mgk_edu_dispatch_fulfillment( $oid );
		}
		return $oid;
	}

	// Don't resurrect an order for a terminal event that never had one.
	if ( ! $create_if_missing ) return 0;

	$base     = (float) ( $row['base_amount'] ?: $row['price_amount'] );
	$price    = (float) $row['price_amount'];
	$discount = max( 0, round( $base - $price, 2 ) );

	$lesson_type = (string) ( $row['lesson_type'] ?: 'TRIAL' );
	$item_type   = 'edu_' . strtolower( $lesson_type );        // edu_trial | edu_package_8 …
	$tutor_name  = get_the_title( (int) $row['tutor_post_id'] ) ?: 'Tutor';
	$subject     = (string) ( $row['subject'] ?? '' );
	$label       = ucfirst( strtolower( str_replace( '_', ' ', $lesson_type ) ) );
	$name        = trim( $label . ( $subject ? " — {$subject}" : '' ) . " with {$tutor_name}" );

	$oid = OrderRepository::createOrder( [
		'order_code'       => $code,
		'customer_user_id' => $row['parent_user_id'] ? (int) $row['parent_user_id'] : null,
		'currency'         => $row['currency'] ?: 'SGD',
		'status'           => (string) $status,
		'metadata'         => [
			'source'       => 'edu_booking',
			'booking_id'   => (int) $row['id'],
			'booking_code' => $row['booking_code'],
		],
	] );
	if ( ! $oid ) return 0;

	OrderRepository::addItem( $oid, [
		'item_type'     => $item_type,
		'item_ref_id'   => (int) $row['tutor_post_id'],   // thing sold = lesson w/ this tutor (no hard FK)
		'name'          => $name,                          // LAW 2 snapshot
		'sku'           => $lesson_type,
		'sell_unit'     => 'piece',
		'unit_price'    => $base,                          // list price
		'qty'           => 1,
		'options'       => [
			'subject'      => $subject,
			'student_name' => (string) ( $row['student_name'] ?? '' ),
			'start_at_utc' => (string) ( $row['start_at_utc'] ?? '' ),
		],
		'line_discount' => $discount,
		'line_tax'      => 0,
		'line_total'    => $price,                         // what was actually charged
	] );
	OrderRepository::recalcTotals( $oid );

	do_action( 'mgk_core_order_written', $oid, (int) $row['id'] );

	// Created straight at PAID (e.g. confirm without a prior hold-order) → fulfill.
	if ( $status === 'PAID' ) {
		mgk_edu_dispatch_fulfillment( $oid );
	}
	return $oid;
}

/**
 * Run the commerce fulfillment seam for every line of a core order. Guarded so a
 * missing module or a throwing handler can NEVER break the booking confirm path.
 *
 * @param int $oid core order id
 */
function mgk_edu_dispatch_fulfillment( $oid ) {
	if ( ! class_exists( '\\Margick\\Commerce\\Dispatcher' ) ) return;
	$order = OrderRepository::getOrderWithItems( (int) $oid );
	if ( ! $order || empty( $order['items'] ) ) return;
	foreach ( $order['items'] as $it ) {
		try {
			\Margick\Commerce\Dispatcher::fulfill(
				(string) $it['item_type'],
				(int) ( $it['item_ref_id'] ?? 0 ),
				$order
			);
		} catch ( \Throwable $e ) {
			error_log( '[mgk-commerce] fulfill failed for order ' . (int) $oid . ': ' . $e->getMessage() );
		}
	}
}
