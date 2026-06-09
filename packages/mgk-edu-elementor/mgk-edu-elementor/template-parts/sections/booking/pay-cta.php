<?php
/**
 * S11 — Pay CTA + reassurance.
 *
 * The amount in the button + the mock-pay action route come from the locked
 * core ($args['summary'] + context). Labels are SAFE copy. JS enables the CTA
 * once terms are accepted, and runs the processing → success/failed states.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a       = (array) ( $args ?? [] );
$bd      = (array) ( $a['summary'] ?? [] );
$context = (array) ( $a['context'] ?? [] );

// Real engine booking id (carried from S10). Drives the live Stripe checkout.
$booking_id = (int) ( $a['booking_id'] ?? ( $context['booking_id'] ?? 0 ) );

$due = (string) ( $bd['due'] ?? '$0.00' );

$cta_label = $a['cta_label'] ?? 'Pay {amount} with PayNow →';
$cta_label = str_replace( '{amount}', $due, $cta_label );
$reassure  = $a['reassure'] ?? '🔒 YOU WON’T BE CHARGED UNTIL PAYMENT IS CONFIRMED';

// Mock-pay redirect to S12 (no real charge in this phase).
$pay_action = add_query_arg( array_filter( [
    'lead'       => $context['lead_token'] ?? '',
    'tutor'      => $context['tutor_slug'] ?? '',
    'slot'       => $context['slot_id'] ?? '',
    'mgk_action' => 'mock_pay',
] ), home_url( '/trial-pay/' ) );
?>
<section class="mgk-bk-card mgk-pay-cta-wrap" data-reveal data-event="pay_cta_view">
    <a class="mgk-pay-cta is-disabled" href="<?php echo esc_url( $pay_action ); ?>"
       data-mgk-pay-cta aria-disabled="true" tabindex="-1"
       data-amount="<?php echo esc_attr( $bd['due_num'] ?? 0 ); ?>"
       data-booking-id="<?php echo esc_attr( $booking_id ); ?>"
       data-event="pay_submit" data-step="pay" data-next_step="confirmed">
        <span data-pay-cta-label><?php echo esc_html( $cta_label ); ?></span>
    </a>
    <p class="mgk-pay-reassure"><?php echo esc_html( $reassure ); ?></p>
</section>
