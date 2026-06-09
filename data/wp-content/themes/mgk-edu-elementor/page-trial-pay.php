<?php
/**
 * S11 Trial Booking — Pay. Route /trial-pay/ (page slug "trial-pay").
 *
 * BUILDER MODE — if built in Elementor, render the_content() (Elementor data).
 * DEFAULT MODE — render the S11 pay composite from PHP partials.
 * Order/discount/payment/state logic stays locked in inc/mgk-pay.php.
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

echo do_shortcode( '[mgk_pay]' );

get_footer();
