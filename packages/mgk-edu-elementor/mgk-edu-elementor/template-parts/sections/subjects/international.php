<?php
/**
 * S04 international curriculum.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$items = $args['catalog']['international'] ?? [];
?>
<section class="mgk-section mgk-section-surface mgk-subject-international">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'International Curriculum' ); ?>

        <?php if ( empty( $items ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title' => 'No international subjects available',
                'message' => 'International curriculum data has not been imported yet.',
            ] ); ?>
        <?php else : ?>
            <div class="mgk-international-grid">
                <?php foreach ( $items as $item ) : ?>
                    <?php get_template_part( 'template-parts/components/subject-card', null, $item ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
