<?php
/**
 * S04 browse by level.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$levels = $args['catalog']['levels'] ?? [];
?>
<section class="mgk-section mgk-subject-levels">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Browse by Level', '', 'From Preschool to International Baccalaureate' ); ?>

        <?php if ( empty( $levels ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title' => 'No level groups available',
                'message' => 'Subject level data has not been imported yet.',
            ] ); ?>
        <?php else : ?>
            <div class="mgk-level-stack">
                <?php foreach ( $levels as $group ) : ?>
                    <section class="mgk-level-group">
                        <header>
                            <h2><?php echo esc_html( $group['title'] ?? '' ); ?></h2>
                            <span><?php echo esc_html( $group['meta'] ?? '' ); ?></span>
                        </header>
                        <div class="mgk-subject-tile-grid">
                            <?php foreach ( $group['subjects'] ?? [] as $subject ) : ?>
                                <?php
                                get_template_part( 'template-parts/components/subject-card', null, array_merge( $subject, [
                                    'level' => $group['level'] ?? '',
                                ] ) );
                                ?>
                            <?php endforeach; ?>
                            <a class="mgk-subject-tile mgk-subject-view-all" href="<?php echo esc_url( mgk_cta_url( 'browse', [ 'level' => $group['level'] ?? '' ] ) ); ?>">
                                <span class="mgk-subject-tile-name">View all</span>
                                <span class="mgk-subject-tile-meta">Browse tutors</span>
                            </a>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
