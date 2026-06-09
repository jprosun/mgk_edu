<?php
/**
 * S02 Tutor Listing (DATA page).
 *
 * Two render modes (see docs/TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR.md §3.3):
 *   BUILDER MODE — if the owner built this page in Elementor, render the_content()
 *                  so Elementor can inject its layout (the "MGK · Teacher Listing"
 *                  widget). Required: Elementor refuses to open a template that
 *                  never calls the_content().
 *   DEFAULT MODE — otherwise render the canonical listing via mgk_render_tutor_listing(),
 *                  the SINGLE source shared by this template, the [mgk_tutor_listing]
 *                  shortcode, and the Elementor widget. Data + logic (query, tutor
 *                  cards, pagination) stay locked in PHP; only display labels are editable.
 */

get_header();

if ( mgk_is_built_with_elementor( get_queried_object_id() ) ) :

    while ( have_posts() ) {
        the_post();
        the_content();
    }

else :

    echo mgk_render_tutor_listing();

endif;

get_footer();
