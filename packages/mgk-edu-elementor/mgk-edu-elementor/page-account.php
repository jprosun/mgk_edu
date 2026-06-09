<?php
/**
 * Parent account route: /parent/account/
 */

get_header();

if ( function_exists( 'mgk_is_built_with_elementor' ) && mgk_is_built_with_elementor( get_queried_object_id() ) ) {
    while ( have_posts() ) {
        the_post();
        the_content();
    }
    get_footer();
    return;
}

echo do_shortcode( '[mgk_parent_account]' );

get_footer();
