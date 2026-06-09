<?php
/**
 * S04 popular subject combinations.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$combinations = $args['catalog']['combinations'] ?? [];
?>
<section class="mgk-section mgk-subject-combos">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Popular Subject Combinations', '', 'Save by booking related subjects with the same tutor' ); ?>

        <?php if ( empty( $combinations ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title' => 'No combinations available',
                'message' => 'Combination packages have not been imported yet.',
            ] ); ?>
        <?php else : ?>
            <div class="mgk-grid mgk-grid-3 mgk-combo-grid">
                <?php foreach ( $combinations as $combo ) : ?>
                    <a class="mgk-combo-card" href="<?php echo esc_url( mgk_subject_url( $combo['subject'] ?? $combo['name'], $combo['query'] ?? [] ) ); ?>">
                        <h2><?php echo esc_html( $combo['name'] ?? '' ); ?></h2>
                        <p><?php echo esc_html( ( $combo['count'] ?? '' ) . ' · ' . ( $combo['saving'] ?? '' ) ); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
