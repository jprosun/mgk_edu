<?php
/**
 * S04 trending subjects.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$trending = $args['catalog']['trending'] ?? [];
?>
<section class="mgk-section mgk-section-surface mgk-subject-trending">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Trending This Month', '', 'Most searched subjects in the last 30 days' ); ?>

        <?php if ( empty( $trending ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title' => 'No trending subjects yet',
                'message' => 'Trending search data will appear after more activity is imported.',
            ] ); ?>
        <?php else : ?>
            <div class="mgk-trending-grid">
                <?php foreach ( $trending as $item ) : ?>
                    <a class="mgk-trending-card" href="<?php echo esc_url( mgk_subject_url( $item['name'] ?? '' ) ); ?>">
                        <span>Up <?php echo esc_html( $item['rank'] ?? '' ); ?></span>
                        <strong><?php echo esc_html( $item['name'] ?? '' ); ?></strong>
                        <em><?php echo esc_html( $item['growth'] ?? '' ); ?></em>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
