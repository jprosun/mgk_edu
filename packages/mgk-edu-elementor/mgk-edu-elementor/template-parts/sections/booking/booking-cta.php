<?php
/**
 * S09 — Continue CTA + Save & resume row.
 *
 * Routes (S10 slot URL, save/resume URL) come from the locked context. No
 * payment here. Button label + notes are SAFE copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a       = (array) ( $args ?? [] );
$context = (array) ( $a['context'] ?? [] );
$tutor   = (array) ( $a['tutor'] ?? [] );

$slot_url   = function_exists( 'mgk_get_s10_slot_url' )    ? mgk_get_s10_slot_url( $context )    : home_url( '/book-slot/' );
$resume_url = function_exists( 'mgk_get_save_resume_url' ) ? mgk_get_save_resume_url( $context ) : home_url( '/parent/trial/' );

$cta_label    = $a['cta_label']    ?? 'Continue to pick a slot →';
$resume_label = $a['resume_label'] ?? '💾 SAVE & RESUME LATER (WE’LL EMAIL YOU A LINK)';
$nopay_label  = $a['nopay_label']  ?? 'NO PAYMENT YET · PAY AT STEP 3';

$resumed = isset( $_GET['mgk_resumed'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_resumed'] ) ) : '';
?>
<div class="mgk-bk-cta-wrap">
    <?php if ( $resumed ) : ?>
    <p class="mgk-bk-resume-ok" role="status">Saved — we’ll email you a link to resume this booking.</p>
    <?php endif; ?>

    <a class="mgk-bk-continue" href="<?php echo esc_url( $slot_url ); ?>"
       data-event="booking_step_continue" data-step="select_tutor" data-next_step="pick_slot"
       data-tutor="<?php echo esc_attr( $tutor['slug'] ?? '' ); ?>"><?php echo esc_html( $cta_label ); ?></a>

    <div class="mgk-bk-cta-foot">
        <a class="mgk-bk-resume" href="<?php echo esc_url( $resume_url ); ?>" data-event="save_resume_click"><?php echo esc_html( $resume_label ); ?></a>
        <span class="mgk-bk-nopay"><?php echo esc_html( $nopay_label ); ?></span>
    </div>
</div>
