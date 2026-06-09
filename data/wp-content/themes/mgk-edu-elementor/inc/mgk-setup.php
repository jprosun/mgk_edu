<?php
/**
 * MGK theme setup.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );

    // Standard WP custom logo (Appearance → Customize → Site Identity). Lets the
    // owner change the logo through the native WP Customizer; we bridge this to
    // the MGK Site Settings `logo_image_id` so both stay in sync (see
    // mgk_site_logo_html() + mgk_sync_custom_logo() in mgk-site-settings.php).
    add_theme_support( 'custom-logo', [
        'height'      => 48,
        'width'       => 180,
        'flex-height' => true,
        'flex-width'  => true,
        'unlink-homepage-logo' => false,
    ] );

    register_nav_menus( [
        'mgk_primary' => __( 'MGK Primary Navigation', 'mgk-edu' ),
        'mgk_footer'  => __( 'MGK Footer Navigation', 'mgk-edu' ),
    ] );
} );

add_filter( 'show_admin_bar', function ( $show ) {
    return current_user_can( 'manage_options' ) ? $show : false;
} );

/**
 * Make the tutor profile (mg_teacher CPT single) editable in the Elementor editor.
 *
 * Elementor only opens its editor for post types listed in the `elementor_cpt_support`
 * option (default: post, page). Without mg_teacher in that list, opening a tutor in
 * Elementor silently redirects back to the post list — so the "MGK · Teacher Profile"
 * widget (S03) could render on the front-end but never be edited. We add mg_teacher
 * idempotently on admin_init (and after_switch_theme for fresh installs), preserving
 * any other CPTs the owner enabled. Display labels/Style of the profile chrome then
 * become editable; tutor DATA stays in the mg_teacher CPT / ACF fields (wp-admin).
 */
function mgk_ensure_elementor_cpt_support() {
    $support = get_option( 'elementor_cpt_support' );
    if ( ! is_array( $support ) ) {
        $support = [ 'post', 'page' ];
    }
    if ( ! in_array( 'mg_teacher', $support, true ) ) {
        $support[] = 'mg_teacher';
        update_option( 'elementor_cpt_support', $support );
    }
}
add_action( 'admin_init', 'mgk_ensure_elementor_cpt_support' );
add_action( 'after_switch_theme', 'mgk_ensure_elementor_cpt_support' );

/**
 * Canonical redirects for duplicate pages.
 *
 * Two pairs of pages render the same screen. We keep one canonical page (the
 * one the theme code links to everywhere) and 301 the duplicate to it, so users
 * never land on the dead twin and there's a single URL per screen:
 *   - S02 Tutor Listing: /tutors/ (legacy)        → /student/teachers/ (canonical)
 *   - S07 Request Match: /request-tutor/ (legacy)  → /request-match/ (canonical)
 *
 * Matched by slug (resolved to the published page) so renaming a slug just stops
 * the redirect rather than sending visitors to a 404. The duplicate pages are
 * kept in the DB (not deleted) — owners may have Elementor edits on them.
 */
function mgk_canonical_redirects() {
    if ( is_admin() ) {
        return;
    }

    // legacy slug => canonical slug
    $map = [
        'tutors'        => 'student/teachers',
        'request-tutor' => 'request-match',
    ];

    foreach ( $map as $legacy => $canonical ) {
        $legacy_page = get_page_by_path( $legacy );
        if ( ! $legacy_page || ! is_page( $legacy_page->ID ) ) {
            continue;
        }
        $target = get_page_by_path( $canonical );
        if ( ! $target ) {
            continue;
        }
        // Carry any query string (filters, lead tokens) over to the canonical URL.
        $url = get_permalink( $target->ID );
        if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
            $url = add_query_arg(
                array_map( 'sanitize_text_field', wp_unslash( $_GET ) ),
                $url
            );
        }
        wp_safe_redirect( $url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'mgk_canonical_redirects' );
