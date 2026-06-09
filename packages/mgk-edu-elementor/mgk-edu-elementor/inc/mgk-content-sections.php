<?php
/**
 * MGK content-page sections as shortcodes (S04 Subjects, S05 How, S06 Pricing).
 *
 * ARCHITECTURE — "shortcode wraps partial":
 *   Each shortcode is a THIN WRAPPER that calls the existing template-part via
 *   get_template_part(). The HTML markup stays in ONE place (template-parts/),
 *   never duplicated. The page template and the UX Builder element both render
 *   through the same partial, so the markup can never diverge.
 *
 *   Shortcode  ──get_template_part()──▶  template-parts/sections/<page>/<x>.php
 *        ▲                                         ▲
 *        │ UX Builder element                      │ page-*.php (default mode)
 *
 * Data getters (mgk_get_subject_catalog / mgk_demo) are called inside each
 * shortcode so a section renders correctly standalone (in the builder canvas or
 * dragged onto any page). Calling a shortcode with no atts reproduces the exact
 * original page output.
 *
 * See: inc/mgk-ux-builder.php (element registration), docs/TEMPLATE-BUILD-PLAYBOOK.md
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render a template-part to string (buffered). Internal helper.
 *
 * @param string $slug Relative slug under template-parts/ (no .php).
 * @param array  $args Passed to the partial as $args.
 * @return string
 */
function mgk_render_part( $slug, array $args = [] ) {
    ob_start();
    get_template_part( $slug, null, $args );
    return (string) ob_get_clean();
}

/* ============================================================
   S04 — Subject Catalog
   ============================================================ */

function mgk_subjects_page_meta() {
    return [
        'eyebrow'            => mgk_page_field( 'hero_eyebrow', '30+ subjects - all levels' ),
        'title'             => mgk_page_field( 'hero_title', 'Find tutors by subject' ),
        'body'              => mgk_page_field( 'hero_body', 'Search quickly or browse by level, major exam, stream, and international curriculum.' ),
        'search_placeholder' => mgk_page_field( 'hero_search_placeholder', 'Search subject e.g. PSLE Math, H2 Chem...' ),
        'search_button'     => mgk_page_field( 'hero_search_button', 'Search' ),
    ];
}

add_shortcode( 'mgk_subjects_hero', function () {
    return mgk_render_part( 'template-parts/sections/subjects/hero', [
        'catalog' => mgk_get_subject_catalog(),
        'page'    => mgk_subjects_page_meta(),
    ] );
} );

add_shortcode( 'mgk_subjects_levels', function () {
    return mgk_render_part( 'template-parts/sections/subjects/level-groups', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_exams', function () {
    return mgk_render_part( 'template-parts/sections/subjects/exam-groups', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_combinations', function () {
    return mgk_render_part( 'template-parts/sections/subjects/combinations', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_trending', function () {
    return mgk_render_part( 'template-parts/sections/subjects/trending', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_streams', function () {
    return mgk_render_part( 'template-parts/sections/subjects/streams', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_international', function () {
    return mgk_render_part( 'template-parts/sections/subjects/international', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_featured', function () {
    return mgk_render_part( 'template-parts/sections/subjects/featured', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

add_shortcode( 'mgk_subjects_cta', function () {
    return mgk_render_part( 'template-parts/sections/subjects/cta', [ 'catalog' => mgk_get_subject_catalog() ] );
} );

/* ============================================================
   S05 — How It Works
   ============================================================ */

function mgk_how_data() {
    return mgk_demo( 'how_it_works', [] );
}

function mgk_how_page_meta() {
    return [
        'eyebrow' => mgk_page_field( 'hero_eyebrow', 'Simple - transparent - guaranteed' ),
        'title'   => mgk_page_field( 'hero_title', 'How Margick Works' ),
        'body'    => mgk_page_field( 'hero_body', '' ),
    ];
}

add_shortcode( 'mgk_how_hero', function () {
    return mgk_render_part( 'template-parts/sections/how/hero', [ 'how' => mgk_how_data(), 'page' => mgk_how_page_meta() ] );
} );

add_shortcode( 'mgk_how_process', function () {
    return mgk_render_part( 'template-parts/sections/how/process', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_video', function () {
    return mgk_render_part( 'template-parts/sections/how/video', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_difference', function () {
    return mgk_render_part( 'template-parts/sections/how/difference', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_guarantee', function () {
    return mgk_render_part( 'template-parts/sections/how/guarantee', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_pricing', function () {
    return mgk_render_part( 'template-parts/sections/how/pricing', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_verification', function () {
    return mgk_render_part( 'template-parts/sections/how/verification', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_comparison', function () {
    return mgk_render_part( 'template-parts/sections/how/comparison', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_concerns', function () {
    return mgk_render_part( 'template-parts/sections/how/concerns', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_faq', function () {
    return mgk_render_part( 'template-parts/sections/how/faq', [ 'how' => mgk_how_data() ] );
} );

add_shortcode( 'mgk_how_cta', function () {
    return mgk_render_part( 'template-parts/components/cta-band', [
        'title'     => mgk_page_field( 'cta_title', 'Ready to find your perfect tutor?' ),
        'body'      => mgk_page_field( 'cta_body', 'Join 12,400+ Singapore parents. Match in 6 hours with no upfront matching fee.' ),
        'primary'   => [ 'label' => mgk_page_field( 'cta_primary_label', 'Find Tutor Now ->' ), 'url' => mgk_cta_url( 'find-tutor' ) ],
        'secondary' => [ 'label' => mgk_page_field( 'cta_secondary_label', 'Browse All Tutors' ), 'url' => mgk_cta_url( 'browse' ) ],
        'note'      => mgk_page_field( 'cta_note', '4.8/5 from verified parents - MOE-registered partner agencies' ),
    ] );
} );

/* ============================================================
   S06 — Pricing
   ============================================================ */

function mgk_pricing_data() {
    return mgk_demo( 'pricing_page', [] );
}

function mgk_pricing_page_meta() {
    return [
        'eyebrow' => mgk_page_field( 'hero_eyebrow', 'Transparent - no hidden fees' ),
        'title'   => mgk_page_field( 'hero_title', 'Simple, honest pricing' ),
        'body'    => mgk_page_field( 'hero_body', 'Estimate lesson costs before you contact a tutor. See hourly ranges, package savings, and possible add-ons upfront.' ),
    ];
}

add_shortcode( 'mgk_pricing_hero', function () {
    return mgk_render_part( 'template-parts/sections/pricing/hero', [ 'pricing' => mgk_pricing_data(), 'page' => mgk_pricing_page_meta() ] );
} );

add_shortcode( 'mgk_pricing_calculator', function () {
    return mgk_render_part( 'template-parts/sections/pricing/calculator', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_rate_table', function () {
    return mgk_render_part( 'template-parts/sections/pricing/rate-table', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_subject_premium', function () {
    return mgk_render_part( 'template-parts/sections/pricing/subject-premium', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_packages', function () {
    return mgk_render_part( 'template-parts/sections/pricing/packages', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_included', function () {
    return mgk_render_part( 'template-parts/sections/pricing/included', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_not_included', function () {
    return mgk_render_part( 'template-parts/sections/pricing/not-included', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_comparison', function () {
    return mgk_render_part( 'template-parts/sections/pricing/comparison', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_faq', function () {
    return mgk_render_part( 'template-parts/sections/pricing/faq', [ 'pricing' => mgk_pricing_data() ] );
} );

add_shortcode( 'mgk_pricing_cta', function () {
    return mgk_render_part( 'template-parts/components/cta-band', [
        'title'     => mgk_page_field( 'cta_title', 'See real tutors and prices for your child' ),
        'body'      => mgk_page_field( 'cta_body', 'Match in 6 hours - Trial $40 - No upfront commitment' ),
        'primary'   => [ 'label' => mgk_page_field( 'cta_primary_label', 'Find Tutor Now ->' ), 'url' => mgk_cta_url( 'find-tutor' ) ],
        'secondary' => [ 'label' => mgk_page_field( 'cta_secondary_label', 'Browse Tutors' ), 'url' => mgk_cta_url( 'browse' ) ],
        'note'      => mgk_page_field( 'cta_note', '4.8/5 from 12,400+ verified parents' ),
    ] );
} );
