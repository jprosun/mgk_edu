<?php
/**
 * S01 Public Home.
 *
 * Two render modes:
 *   1. BUILDER MODE — if the front page was built with Elementor (the owner
 *      composed it in the Elementor editor using the MGK section widgets, see
 *      inc/mgk-elementor.php), defer to Elementor's rendered layout.
 *   2. DEFAULT MODE — otherwise render the curated MGK home sections directly.
 *      This is the out-of-the-box home; no editing required.
 *
 * Both modes share ONE source of truth: the section render functions in
 * inc/mgk-sections.php (also registered as shortcodes / Elementor widgets).
 */

get_header();

/*
 * Enter BUILDER MODE only when the front page was built with Elementor — i.e.
 * the owner deliberately composed it in the editor. Elementor then hijacks
 * the_content() to inject its layout. Any other front page (empty, plain text,
 * or a leftover demo page) falls through to the curated DEFAULT MODE, so the
 * home never breaks.
 */
$mgk_use_builder = mgk_is_built_with_elementor( get_queried_object_id() );

if ( $mgk_use_builder ) :

    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;

else :

    echo mgk_section_hero();
    echo mgk_section_trust_stats();

    if ( mgk_site_enabled( 'show_live_feed' ) ) echo mgk_section_live_feed();
    if ( mgk_site_enabled( 'show_steps' ) )      echo mgk_section_steps();
    if ( mgk_site_enabled( 'show_subjects' ) )   echo mgk_section_subjects();
    if ( mgk_site_enabled( 'show_tutors' ) )     echo mgk_section_featured_tutors();
    if ( mgk_site_enabled( 'show_why' ) )        echo mgk_section_why();
    if ( mgk_site_enabled( 'show_spotlight' ) )  echo mgk_section_spotlight();
    if ( mgk_site_enabled( 'show_results' ) )    echo mgk_section_results();
    if ( mgk_site_enabled( 'show_reviews' ) )    echo mgk_section_reviews();
    if ( mgk_site_enabled( 'show_faq' ) )        echo mgk_section_faq();
    if ( mgk_site_enabled( 'show_pricing' ) )    echo mgk_section_pricing_teaser();
    if ( mgk_site_enabled( 'show_press' ) )      echo mgk_section_press();
    if ( mgk_site_enabled( 'show_final_cta' ) )  echo mgk_section_final_cta();
    if ( mgk_site_enabled( 'show_newsletter' ) ) echo mgk_section_newsletter();

endif;
?>

<a class="mgk-mobile-sticky" href="<?php echo esc_url( mgk_cta_url( 'find-tutor' ) ); ?>" data-mgk-mobile-sticky><?php echo esc_html( mgk_site_setting( 'mobile_sticky_label' ) ); ?></a>

<?php
get_footer();
