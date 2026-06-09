<?php
/**
 * S06 Pricing (CONTENT page).
 *
 * Dual render mode + section toggles — see docs/TEMPLATE-BUILD-PLAYBOOK.md §3.3.
 * Sections are shortcodes wrapping the existing template-parts (single source of
 * truth). Registered as Elementor widgets in inc/mgk-elementor.php.
 */

get_header();

$mgk_use_builder = mgk_is_built_with_elementor( get_queried_object_id() );

if ( $mgk_use_builder ) :

    while ( have_posts() ) {
        the_post();
        the_content();
    }

else :

    if ( mgk_page_enabled( 'show_hero' ) )            echo do_shortcode( '[mgk_pricing_hero]' );
    if ( mgk_page_enabled( 'show_calculator' ) )      echo do_shortcode( '[mgk_pricing_calculator]' );
    if ( mgk_page_enabled( 'show_rate_table' ) )      echo do_shortcode( '[mgk_pricing_rate_table]' );
    if ( mgk_page_enabled( 'show_subject_premium' ) ) echo do_shortcode( '[mgk_pricing_subject_premium]' );
    if ( mgk_page_enabled( 'show_packages' ) )        echo do_shortcode( '[mgk_pricing_packages]' );
    if ( mgk_page_enabled( 'show_included' ) )        echo do_shortcode( '[mgk_pricing_included]' );
    if ( mgk_page_enabled( 'show_not_included' ) )    echo do_shortcode( '[mgk_pricing_not_included]' );
    if ( mgk_page_enabled( 'show_comparison' ) )      echo do_shortcode( '[mgk_pricing_comparison]' );
    if ( mgk_page_enabled( 'show_faq' ) )             echo do_shortcode( '[mgk_pricing_faq]' );
    if ( mgk_page_enabled( 'show_cta' ) )             echo do_shortcode( '[mgk_pricing_cta]' );

endif;

get_footer();
