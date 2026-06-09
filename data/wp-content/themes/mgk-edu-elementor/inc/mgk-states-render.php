<?php
/**
 * MGK state-panel shortcodes (404 / empty / loading / permission / validation /
 * form-error / offline / server-error / session-expired).
 *
 * ARCHITECTURE — "shortcode wraps partial" (same as inc/mgk-content-sections.php):
 *   Each [mgk_state_*] is a THIN wrapper that renders the existing
 *   template-parts/states/<x>.php via get_template_part(), forwarding the editable
 *   copy (title / message / button URL) as $args. The HTML lives ONCE in the
 *   partial; the page template, the [mgk_state_*] shortcode, and the Elementor
 *   widget all render through it, so markup can never diverge.
 *
 *   States are pure CONTENT shells — only copy + style are editable. There is no
 *   data or logic to lock (they show no records), so the Elementor widgets expose
 *   the title/message text (Content tab) + full per-element Style.
 *
 * @see inc/mgk-elementor.php — the state widgets are registered there.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'mgk_render_part' ) ) {
    /**
     * Render a template-part to string (buffered). Mirrors the helper in
     * inc/mgk-content-sections.php; redefined defensively in case load order changes.
     */
    function mgk_render_part( $slug, array $args = [] ) {
        ob_start();
        get_template_part( $slug, null, $args );
        return (string) ob_get_clean();
    }
}

/**
 * Map of state shortcode tag => [ partial slug, recognized atts (att => default) ].
 * Defaults are null so an empty att lets the partial keep its own original copy
 * (byte-identical to a no-att render).
 */
function mgk_state_shortcodes() {
    return [
        'mgk_state_404'             => [ 'not-found-panel',   [ 'title' => null, 'message' => null ] ],
        'mgk_state_empty'           => [ 'empty-results',     [ 'title' => null, 'message' => null ] ],
        'mgk_state_loading'         => [ 'loading-skeleton',  [ 'count' => null ] ],
        'mgk_state_permission'      => [ 'permission-denied', [ 'title' => null, 'message' => null, 'back_url' => null ] ],
        'mgk_state_validation'      => [ 'validation-message',[ 'message' => null ] ],
        'mgk_state_form_error'      => [ 'form-error',        [ 'title' => null, 'message' => null ] ],
        'mgk_state_offline'         => [ 'offline',           [ 'title' => null, 'message' => null ] ],
        'mgk_state_server_error'    => [ 'server-error',      [ 'title' => null, 'message' => null ] ],
        'mgk_state_session_expired' => [ 'session-expired',   [ 'title' => null, 'message' => null, 'login_url' => null ] ],
    ];
}

/**
 * Register every [mgk_state_*] shortcode. Each forwards its (non-empty) atts to the
 * matching partial; empty atts are dropped so the partial's own defaults apply.
 */
foreach ( mgk_state_shortcodes() as $tag => $spec ) {
    list( $partial, $att_defaults ) = $spec;
    add_shortcode( $tag, function ( $atts ) use ( $partial, $att_defaults ) {
        $atts = shortcode_atts( $att_defaults, is_array( $atts ) ? $atts : [], 'state' );
        // Drop empty atts so the partial keeps its original copy (no-att == identical).
        $args = [];
        foreach ( $atts as $k => $v ) {
            if ( $v !== null && $v !== '' ) {
                $args[ $k ] = ( $k === 'count' ) ? (int) $v : $v;
            }
        }
        return mgk_render_part( 'template-parts/states/' . $partial, $args );
    } );
}
