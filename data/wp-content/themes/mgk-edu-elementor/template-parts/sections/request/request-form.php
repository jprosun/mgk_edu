<?php
/**
 * S07 — Request Match · STATE 1 (request form) — COMPOSITE.
 *
 * Thin wrapper: renders the Intro band + the Form fields. Both are split into
 * standalone partials (request-intro.php, request-fields.php) so they can also
 * be dropped as separate Elementor widgets and reordered / hidden / restyled
 * independently. The <form> itself is NEVER split — it lives whole inside
 * request-fields.php so it submits in one POST.
 *
 * Presentation only. SAFE marketing copy is forwarded via $args; the locked
 * enums / validation / lead / SLA / mask logic stay in mgk-forms.php.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$args = (array) ( $args ?? [] );

if ( function_exists( 'mgk_request_render_part' ) ) {
    echo mgk_request_render_part( 'request-intro',  $args ); // phpcs:ignore WordPress.Security.EscapeOutput
    echo mgk_request_render_part( 'request-fields', $args ); // phpcs:ignore WordPress.Security.EscapeOutput
} else {
    // Fallback: include directly (e.g. if helper not yet loaded).
    include __DIR__ . '/request-intro.php';
    include __DIR__ . '/request-fields.php';
}
