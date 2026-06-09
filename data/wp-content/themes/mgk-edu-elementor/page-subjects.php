<?php
/**
 * S04 Subject Catalog (CONTENT page).
 *
 * Two render modes (see docs/TEMPLATE-BUILD-PLAYBOOK.md §3.3):
 *   BUILDER MODE  — if the page was built with Elementor (owner composed it in the
 *                   Elementor editor), render the_content() and let Elementor inject
 *                   its layout.
 *   DEFAULT MODE  — otherwise render the curated sections via shortcodes, gated by
 *                   the per-page section toggles. Output is identical to before.
 *
 * Sections are shortcodes that wrap the existing template-parts (single source of
 * truth for markup). Registered as Elementor widgets in inc/mgk-elementor.php.
 */

get_header();

$mgk_use_builder = mgk_is_built_with_elementor( get_queried_object_id() );

if ( $mgk_use_builder ) :

    while ( have_posts() ) {
        the_post();
        the_content();
    }

else :

    $catalog = mgk_get_subject_catalog();

    if ( empty( $catalog ) ) :
        ?>
        <section class="mgk-section">
            <div class="mgk-shell">
                <?php get_template_part( 'template-parts/states/empty-results', null, [
                    'title'   => 'No subjects available yet',
                    'message' => 'Subject catalog data has not been imported. Please check back after the next content sync.',
                ] ); ?>
            </div>
        </section>
        <?php
    else :
        if ( mgk_page_enabled( 'show_hero' ) )         echo do_shortcode( '[mgk_subjects_hero]' );
        if ( mgk_page_enabled( 'show_level_groups' ) ) echo do_shortcode( '[mgk_subjects_levels]' );
        if ( mgk_page_enabled( 'show_exam_groups' ) )  echo do_shortcode( '[mgk_subjects_exams]' );
        if ( mgk_page_enabled( 'show_combinations' ) ) echo do_shortcode( '[mgk_subjects_combinations]' );
        if ( mgk_page_enabled( 'show_trending' ) )     echo do_shortcode( '[mgk_subjects_trending]' );
        if ( mgk_page_enabled( 'show_streams' ) )      echo do_shortcode( '[mgk_subjects_streams]' );
        if ( mgk_page_enabled( 'show_international' ) ) echo do_shortcode( '[mgk_subjects_international]' );
        if ( mgk_page_enabled( 'show_featured' ) )     echo do_shortcode( '[mgk_subjects_featured]' );
        if ( mgk_page_enabled( 'show_cta' ) )          echo do_shortcode( '[mgk_subjects_cta]' );
    endif;

endif;

get_footer();
