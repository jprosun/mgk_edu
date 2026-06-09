<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx  = $args['context'] ?? [];
$lessons = $ctx['upcoming_lessons'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-upcoming">
    <div class="mgk-parent-dashboard__shell">
        <div class="mgk-parent-dashboard-section-head">
            <h2><?php echo esc_html( $atts['heading'] ?? 'Upcoming lessons' ); ?></h2>
            <div>
                <button type="button"><?php echo esc_html( $atts['all_label'] ?? 'ALL CHILDREN' ); ?></button>
                <button type="button" data-event="calendar_sync_click"><?php echo esc_html( $atts['calendar_label'] ?? '+ CALENDAR SYNC' ); ?></button>
            </div>
        </div>
        <div class="mgk-parent-dashboard-upcoming__grid">
            <?php foreach ( $lessons as $lesson ) : ?>
                <article class="mgk-parent-dashboard-lesson-card">
                    <strong><?php echo esc_html( $lesson['time'] ?? '' ); ?></strong>
                    <p><?php echo esc_html( $lesson['meta'] ?? '' ); ?></p>
                    <div>
                        <a href="<?php echo esc_url( $lesson['reschedule_url'] ?? '#' ); ?>" data-event="reschedule_click"><?php echo esc_html( $atts['reschedule'] ?? 'Reschedule' ); ?></a>
                        <a href="<?php echo esc_url( $lesson['message_url'] ?? '#' ); ?>" data-event="message_tutor_click"><?php echo esc_html( $atts['message'] ?? 'Message' ); ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
            <a class="mgk-parent-dashboard-book-next" href="<?php echo esc_url( mgk_get_book_next_lesson_url( $ctx['active_child']['id'] ?? '' ) ); ?>"><?php echo esc_html( $atts['book_label'] ?? '+ BOOK NEXT LESSON' ); ?></a>
        </div>
    </div>
</section>
