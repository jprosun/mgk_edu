<?php
/**
 * S04 browse by exam.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$exams = $args['catalog']['exams'] ?? [];
?>
<section class="mgk-section mgk-section-surface mgk-subject-exams">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Browse by Major Exam' ); ?>

        <?php if ( empty( $exams ) ) : ?>
            <?php get_template_part( 'template-parts/states/empty-results', null, [
                'title' => 'No exam groups available',
                'message' => 'Exam data has not been imported yet.',
            ] ); ?>
        <?php else : ?>
            <div class="mgk-grid mgk-grid-4 mgk-exam-grid">
                <?php foreach ( $exams as $exam ) : ?>
                    <a class="mgk-exam-card" href="<?php echo esc_url( mgk_cta_url( 'browse', $exam['query'] ?? [] ) ); ?>">
                        <strong><?php echo esc_html( $exam['name'] ?? '' ); ?></strong>
                        <span><?php echo esc_html( $exam['description'] ?? '' ); ?></span>
                        <p><?php echo esc_html( $exam['subjects'] ?? '' ); ?></p>
                        <b><?php echo esc_html( $exam['count'] ?? '' ); ?> →</b>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
