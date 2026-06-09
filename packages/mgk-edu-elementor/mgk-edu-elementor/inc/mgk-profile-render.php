<?php
/**
 * S03 Tutor Profile — single-source renderer, split into editable SUB-SECTIONS.
 *
 * Each profile section has its own render function + [mgk_profile_*] shortcode +
 * Elementor widget, so owners can drag / reorder / hide / style each section
 * independently in the builder (the audit's requirement: many small shells, NOT
 * one big profile shortcode). The tutor DATA + LOGIC stay locked in PHP — every
 * section reads the SAME tutor object (memoized in mgk_profile_current_tutor())
 * so they always describe the same tutor. Owners edit tutor content in wp-admin
 * (mg_teacher CPT / ACF), and restyle the section chrome in Elementor.
 *
 * Sections:
 *   hero, demo_video, quick_info, about, qualifications, track_record,
 *   availability, packages, reviews, gallery, faq, similar, sticky_cta, booking
 *
 * [mgk_tutor_profile] (the original composite) is KEPT so existing pages/seeds
 * that used the single widget still render the full profile identically.
 *
 * @see inc/mgk-listing-render.php — the S02 equivalent (same split pattern).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Resolve the profile's tutor ONCE per request and memoize, so every section
 * widget describes the same tutor. A 'slug' att (passed by a section widget)
 * overrides the queried object — but the FIRST resolution wins for the request
 * (sections on one page all show one tutor).
 *
 * @param string $slug Optional tutor slug.
 * @return array|null
 */
function mgk_profile_current_tutor( $slug = '' ) {
    static $cache = null;
    if ( $cache !== null ) {
        return $cache ?: null;
    }
    $cache = function_exists( 'mgk_profile_tutor' ) ? ( mgk_profile_tutor( $slug ) ?: false ) : false;
    return $cache ?: null;
}

/**
 * Render one profile section partial for the current tutor.
 *
 * @param string $partial Slug under template-parts/sections/profile/ (no .php).
 * @param string $slug    Optional tutor slug.
 * @return string Empty string if no tutor resolves.
 */
function mgk_render_profile_part( $partial, $slug = '' ) {
    $tutor = mgk_profile_current_tutor( $slug );
    if ( ! $tutor ) {
        return '';
    }
    return mgk_render_part( 'template-parts/sections/profile/' . $partial, [ 'tutor' => $tutor ] );
}

/**
 * Map of profile sub-section shortcode tag => partial slug.
 * (booking + sticky_cta + hero carry the action buttons; the rest are display.)
 */
function mgk_profile_section_map() {
    return [
        'mgk_profile_hero'           => 'hero',
        'mgk_profile_demo_video'     => 'demo-video',
        'mgk_profile_quick_info'     => 'quick-info',
        'mgk_profile_about'          => 'about',
        'mgk_profile_qualifications' => 'qualifications',
        'mgk_profile_track_record'   => 'track-record',
        'mgk_profile_availability'   => 'availability',
        'mgk_profile_packages'       => 'packages',
        'mgk_profile_reviews'        => 'reviews',
        'mgk_profile_gallery'        => 'gallery',
        'mgk_profile_faq'            => 'faq',
        'mgk_profile_similar'        => 'similar',
        'mgk_profile_sticky_cta'     => 'sticky-cta',
        'mgk_profile_booking'        => 'booking-widget',
    ];
}

/** Register a [mgk_profile_*] shortcode per section. */
foreach ( mgk_profile_section_map() as $tag => $partial ) {
    add_shortcode( $tag, function ( $atts ) use ( $partial ) {
        $atts = is_array( $atts ) ? $atts : [];
        $slug = isset( $atts['slug'] ) ? (string) $atts['slug'] : '';
        return mgk_render_profile_part( $partial, $slug );
    } );
}

/* ============================================================
   COMPOSITE renderer — the full profile in the original layout.
   Used by single-mg_teacher.php DEFAULT MODE + [mgk_tutor_profile].
   ============================================================ */

/**
 * Render the whole tutor profile to a string (original 2-column layout).
 *
 * @param array $atts Optional. 'slug' to render a specific tutor.
 * @return string
 */
function mgk_render_tutor_profile( array $atts = [] ) {
    $slug  = isset( $atts['slug'] ) ? (string) $atts['slug'] : '';
    $tutor = mgk_profile_current_tutor( $slug );

    ob_start();

    if ( ! $tutor ) {
        ?>
        <section class="mgk-section">
            <div class="mgk-shell">
                <?php get_template_part( 'template-parts/states/not-found-panel', null, [
                    'title'   => 'Tutor profile not found',
                    'message' => 'This tutor may no longer be available. Browse current verified tutors instead.',
                ] ); ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    // Full-width: hero (contains the booking widget in the desktop 3-col grid) + demo video.
    echo mgk_render_profile_part( 'hero', $slug );
    echo mgk_render_profile_part( 'demo-video', $slug );
    ?>

    <?php // 2-column layout: main content left, sticky booking widget right (desktop only). ?>
    <div class="mgk-profile-body">
        <div class="mgk-profile-main">
            <?php
            echo mgk_render_profile_part( 'quick-info', $slug );
            echo mgk_render_profile_part( 'about', $slug );
            echo mgk_render_profile_part( 'qualifications', $slug );
            echo mgk_render_profile_part( 'track-record', $slug );
            echo mgk_render_profile_part( 'availability', $slug );
            echo mgk_render_profile_part( 'packages', $slug );
            echo mgk_render_profile_part( 'reviews', $slug );
            echo mgk_render_profile_part( 'gallery', $slug );
            echo mgk_render_profile_part( 'faq', $slug );
            ?>
        </div>

        <aside class="mgk-profile-sidebar" aria-label="Book this tutor">
            <?php echo mgk_render_profile_part( 'booking-widget', $slug ); ?>
        </aside>
    </div>

    <?php
    // Full-width: similar tutors + mobile sticky CTA.
    echo mgk_render_profile_part( 'similar', $slug );
    echo mgk_render_profile_part( 'sticky-cta', $slug );

    return (string) ob_get_clean();
}

/**
 * [mgk_tutor_profile] — renders the current (or a given) tutor's full profile.
 */
function mgk_shortcode_tutor_profile( $atts ) {
    $atts = is_array( $atts ) ? $atts : [];
    return mgk_render_tutor_profile( $atts );
}
add_shortcode( 'mgk_tutor_profile', 'mgk_shortcode_tutor_profile' );
