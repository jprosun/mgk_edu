<?php
/**
 * S03 Tutor Profile (DATA page).
 *
 * Two render modes (see docs/TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR.md §3.3):
 *   BUILDER MODE — if the owner built this page in Elementor, render the_content()
 *                  so Elementor can inject its layout (the "MGK · Teacher Profile"
 *                  widget). Elementor refuses to open a template that never calls
 *                  the_content().
 *   DEFAULT MODE — otherwise render the canonical profile via mgk_render_tutor_profile(),
 *                  the SINGLE source shared by this template, the [mgk_tutor_profile]
 *                  shortcode, and the Elementor widget. Tutor data + logic (the
 *                  $tutor object, section partials, booking form) stay locked in PHP;
 *                  only display chrome (headings, buttons) is restylable in the builder.
 */

get_header();

if ( mgk_is_built_with_elementor( get_queried_object_id() ) ) :

    while ( have_posts() ) {
        the_post();
        the_content();
    }

else :

    echo mgk_render_tutor_profile();

endif;

get_footer();
