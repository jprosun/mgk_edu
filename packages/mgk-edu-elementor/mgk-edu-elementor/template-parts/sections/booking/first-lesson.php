<?php
/**
 * S12 — Your first lesson card (Zoom/readiness items + calendar export).
 *
 * Lesson items + the .ics URL come from the locked view ($args['lesson'],
 * $args['calendar']). Button label is SAFE copy. We expose only the .ics
 * download (Google/Outlook deep-links removed) — the same .ics is also attached
 * to the confirmation email so the parent can add it from their inbox.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a     = (array) ( $args ?? [] );
$items = (array) ( $a['lesson'] ?? [] );
$cal   = (array) ( $a['calendar'] ?? [] );

$heading       = $a['heading']        ?? 'Your first lesson';
$ics_label     = $a['ics_label']      ?? '📅 Add to calendar (.ics)';
?>
<section class="mgk-cf-card mgk-cf-lesson" data-event="confirm_lesson_view">
    <h2 class="mgk-cf-card-title"><?php echo esc_html( $heading ); ?></h2>

    <ul class="mgk-cf-lesson-list">
        <?php foreach ( $items as $it ) : ?>
        <li class="mgk-cf-lesson-item">
            <span class="mgk-cf-lesson-ico" aria-hidden="true"><?php echo esc_html( $it['icon'] ?? '·' ); ?></span>
            <?php if ( ! empty( $it['url'] ) ) : ?>
                <span class="mgk-cf-lesson-text">Zoom link:
                    <a href="<?php echo esc_url( $it['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $it['url'] ); ?></a>
                    (also emailed)</span>
            <?php else : ?>
                <span class="mgk-cf-lesson-text"><?php echo esc_html( $it['text'] ?? '' ); ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="mgk-cf-cal-row">
        <a class="mgk-cf-cal-primary" href="<?php echo esc_url( $cal['ics'] ?? '#' ); ?>"
           data-event="calendar_add_click"><?php echo esc_html( $ics_label ); ?></a>
    </div>
</section>
