<?php
/**
 * S05 How It Works (CONTENT page).
 *
 * Dual render mode + section toggles — see docs/TEMPLATE-BUILD-PLAYBOOK.md §3.3.
 * Sections are shortcodes wrapping the existing template-parts (single source of
 * truth). Registered as Elementor widgets in inc/mgk-elementor.php.
 */

add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'mgk-how-page';
    return $classes;
} );

get_header();

$mgk_use_builder = mgk_is_built_with_elementor( get_queried_object_id() );

if ( $mgk_use_builder ) :

    while ( have_posts() ) {
        the_post();
        the_content();
    }

else :

    if ( mgk_page_enabled( 'show_hero' ) )         echo do_shortcode( '[mgk_how_hero]' );
    if ( mgk_page_enabled( 'show_process' ) )      echo do_shortcode( '[mgk_how_process]' );
    if ( mgk_page_enabled( 'show_video' ) )        echo do_shortcode( '[mgk_how_video]' );
    if ( mgk_page_enabled( 'show_difference' ) )   echo do_shortcode( '[mgk_how_difference]' );
    if ( mgk_page_enabled( 'show_guarantee' ) )    echo do_shortcode( '[mgk_how_guarantee]' );
    if ( mgk_page_enabled( 'show_pricing' ) )      echo do_shortcode( '[mgk_how_pricing]' );
    if ( mgk_page_enabled( 'show_verification' ) ) echo do_shortcode( '[mgk_how_verification]' );
    if ( mgk_page_enabled( 'show_comparison' ) )   echo do_shortcode( '[mgk_how_comparison]' );
    if ( mgk_page_enabled( 'show_concerns' ) )     echo do_shortcode( '[mgk_how_concerns]' );
    if ( mgk_page_enabled( 'show_faq' ) )          echo do_shortcode( '[mgk_how_faq]' );
    if ( mgk_page_enabled( 'show_cta' ) )          echo do_shortcode( '[mgk_how_cta]' );

endif;
?>

<a class="mgk-how-mobile-sticky" href="<?php echo esc_url( mgk_cta_url( 'find-tutor' ) ); ?>" data-event="cta_click" data-screen="how_it_works_mobile" data-cta="sticky_start_step_1_mobile"><?php echo esc_html( mgk_page_field( 'mobile_sticky_label', 'Start Step 1 Now ->' ) ); ?></a>

<?php
get_footer();
