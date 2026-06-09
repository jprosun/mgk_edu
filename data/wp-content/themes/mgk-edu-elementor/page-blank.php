<?php
/**
 * Template name: Page - Full Width
 *
 * Full-width blank canvas (no curated sections). Renders the_content() as-is so a
 * page can hold raw MGK shortcodes ([mgk_hero], [mgk_steps]…) or an Elementor-built
 * layout. Used by the MGK Sandbox / test pages. Mirrors Flatsome's page-blank.php
 * but on the Hello Elementor child (single source of header/footer markup).
 *
 * @package MGK_Edu_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>

<div id="content" role="main" class="content-area mgk-page-blank">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php get_footer(); ?>
