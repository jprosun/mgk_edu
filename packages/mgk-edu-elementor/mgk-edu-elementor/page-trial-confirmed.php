<?php
/**
 * S12 Trial Booking — Confirmation. Route /trial-confirmed/ (slug "trial-confirmed").
 *
 * BUILDER MODE — if built in Elementor, render the_content() (Elementor data).
 * DEFAULT MODE — render the S12 booking-success composite from PHP partials.
 * Confirmation/contact-unlock/calendar/invoice/refund logic stays locked in
 * inc/mgk-confirmation.php.
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

echo do_shortcode( '[mgk_booking_success]' );

get_footer();
