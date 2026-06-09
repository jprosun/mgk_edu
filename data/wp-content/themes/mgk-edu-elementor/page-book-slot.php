<?php
/**
 * S10 Trial Booking — Pick Slot. Route /book-slot/ (page slug "book-slot").
 *
 * BUILDER MODE — if built in Elementor, render the_content() (Elementor data).
 * DEFAULT MODE — render the S10 pick-slot composite from PHP partials.
 * Data/hold/route logic stays locked in inc/mgk-slots.php.
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

echo do_shortcode( '[mgk_pick_slot]' );

get_footer();
