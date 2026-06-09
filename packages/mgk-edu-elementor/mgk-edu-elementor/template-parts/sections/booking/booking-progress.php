<?php
/**
 * S09 — 3-step booking progress (reusable S09/S10/S11).
 * Step state is LOCKED via $current_step; only the labels are SAFE copy.
 *
 * @var array $args  ['current'=>int, 'select'=>str, 'slot'=>str, 'pay'=>str]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a = (array) ( $args ?? [] );
$current = (int) ( $a['current'] ?? 1 );
$labels = array_filter( [
    'select' => $a['select'] ?? '',
    'slot'   => $a['slot']   ?? '',
    'pay'    => $a['pay']    ?? '',
] );

echo mgk_render_booking_progress( $current, $labels ); // phpcs:ignore WordPress.Security.EscapeOutput
