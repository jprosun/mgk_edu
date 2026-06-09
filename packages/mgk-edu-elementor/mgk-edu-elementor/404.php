<?php
/**
 * MGK 404 state.
 */

get_header();
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php get_template_part( 'template-parts/states/not-found-panel', null, [
            'title' => 'Page not found',
            'message' => 'The page you are looking for is not available yet.',
        ] ); ?>
    </div>
</section>
<?php
get_footer();
