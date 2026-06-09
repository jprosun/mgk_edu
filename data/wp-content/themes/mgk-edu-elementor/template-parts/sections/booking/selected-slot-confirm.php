<?php
/**
 * S10 — Selected slot summary + Confirm-&-pay CTA.
 *
 * Summary DATA (day, time, tutor, duration) + pay route come from the locked
 * view. No payment here. Labels are SAFE copy. CTA is disabled if no valid hold.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a        = (array) ( $args ?? [] );
$context  = (array) ( $a['context'] ?? [] );
$tutor    = (array) ( $a['tutor'] ?? [] );
$selected = (array) ( $a['selected'] ?? [] );
$hold     = (array) ( $a['hold'] ?? [] );
$day_lbl  = (string) ( $a['active_label'] ?? '' );
$dur      = (int) ( $a['duration_min'] ?? 90 );

$tutor_short = $tutor['short_name'] ?? ( $tutor['name'] ?? 'your tutor' );
// Compact "with Ms Lee" style from the full name.
if ( preg_match( '/\b(Ms|Mr|Mrs|Dr)\.?\s+([A-Z][a-z]+)/', (string) ( $tutor['name'] ?? '' ), $m ) ) {
    $tutor_short = $m[1] . ' ' . $m[2];
}

$slot_label = $selected['label'] ?? '';
$dur_h = rtrim( rtrim( number_format( $dur / 60, 1 ), '0' ), '.' );

$pay_url = function_exists( 'mgk_get_s11_pay_url' )
    ? mgk_get_s11_pay_url( $context, $selected['id'] ?? '', $hold['hold_token'] ?? '' )
    : home_url( '/trial-pay/' );

$label_eyebrow = $a['eyebrow'] ?? 'YOUR TRIAL SLOT';
$cta_label     = $a['cta_label'] ?? 'Confirm slot & pay →';
$location      = $a['location'] ?? '📍 ONLINE (ZOOM LINK SENT AFTER PAYMENT) · ' . $dur_h . 'H';

$main_line = trim( ( $day_lbl ? $day_lbl : '' ) . ( $slot_label ? ' · ' . $slot_label : '' ) . ' · with ' . $tutor_short );

$has_slot = ! empty( $slot_label );
?>
<div class="mgk-bk-confirm" data-mgk-confirm>
    <div class="mgk-bk-confirm-summary">
        <p class="mgk-bk-confirm-eyebrow"><?php echo esc_html( $label_eyebrow ); ?></p>
        <p class="mgk-bk-confirm-main" data-confirm-main><?php echo esc_html( $main_line ); ?></p>
        <p class="mgk-bk-confirm-sub"><?php echo esc_html( $location ); ?></p>
    </div>
    <a class="mgk-bk-confirm-cta<?php echo $has_slot ? '' : ' is-disabled'; ?>"
       href="<?php echo esc_url( $pay_url ); ?>" data-confirm-cta
       data-event="booking_confirm_slot" data-step="pick_slot" data-next_step="pay"
       data-pay-base="<?php echo esc_attr( home_url( '/trial-pay/' ) ); ?>"
       data-lead="<?php echo esc_attr( $context['lead_token'] ?? '' ); ?>"
       data-tutor="<?php echo esc_attr( $context['tutor_slug'] ?? '' ); ?>"
       <?php echo $has_slot ? '' : 'aria-disabled="true" tabindex="-1"'; ?>><?php echo esc_html( $cta_label ); ?></a>
</div>
