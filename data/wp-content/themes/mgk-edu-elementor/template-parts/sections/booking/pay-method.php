<?php
/**
 * S11 — SEC 5 Payment method (PayNow QR / Card fallback) + status panels.
 *
 * Method list, reference, corporate UEN, card surcharge / 3DS rule, and the
 * post-submit status states all come from the locked core ($args). JS toggles
 * which panel is visible (method switch + processing → success/failed/mismatch).
 * Copy is SAFE; rules + references are LOCKED.
 *
 * State preview override: pass state="card|processing|success|failed|mismatch"
 * on the [mgk_pay_method] shortcode to render that panel statically (wireframe).
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$meth = (array) ( $a['methods'] ?? [] );
$ref  = (string) ( $a['reference'] ?? 'TRIAL-XXXX-0000' );
$payee = (array) ( $a['payee'] ?? [] );
$booking_id = (int) ( $a['booking_id'] ?? ( ( $a['context']['booking_id'] ?? 0 ) ) );
$sur   = (array) ( $a['surcharge'] ?? [] );
$states = (array) ( $a['states'] ?? [] );
$hold   = (array) ( $a['hold'] ?? [] );

$tag         = $a['section_tag']  ?? 'SEC 5 Payment method';
$paynow_help = $a['paynow_help']  ?? 'Scan with any SG banking app';
$waiting     = $a['waiting_note'] ?? '⏳ Waiting for payment… we’ll confirm automatically (webhook).';

// Preview override (wireframe states); default active method = first (PayNow).
$preview = sanitize_key( (string) ( $a['state'] ?? '' ) );
$active_method = in_array( $preview, [ '', 'paynow' ], true ) ? 'paynow' : ( $preview === 'card' ? 'card' : 'paynow' );
$active_state  = in_array( $preview, [ 'processing', 'success', 'failed', 'mismatch' ], true ) ? $preview : '';

// Surcharge / 3DS note for the card fallback.
$sur_pct  = (int) ( $sur['pct'] ?? 2 );
$sur_thr  = (int) ( $sur['threshold'] ?? 1000 );
$card_note = sprintf( '+%d%% card surcharge · 3DS challenge for orders > $%s', $sur_pct, number_format( $sur_thr ) );

// Hold remaining for the "failed" copy ({hold} placeholder).
$hold_remaining = ! empty( $hold['active'] ) && (int) ( $hold['remaining'] ?? 0 ) > 0
    ? (int) $hold['remaining'] : (int) ( $a['hold_seconds'] ?? 600 );
$hold_mmss = sprintf( '%02d:%02d', floor( $hold_remaining / 60 ), $hold_remaining % 60 );

$state_copy = function ( $key ) use ( $states, $hold_mmss ) {
    $s = (array) ( $states[ $key ] ?? [] );
    $title = (string) ( $s['title'] ?? '' );
    return str_replace( '{hold}', $hold_mmss, $title );
};
?>
<section class="mgk-bk-card mgk-pay-method" data-reveal data-mgk-pay-method
         data-ref="<?php echo esc_attr( $ref ); ?>" data-event="pay_method_view">
    <span class="mgk-bk-sectag"><?php echo esc_html( $tag ); ?></span>

    <div class="mgk-pay-methods" role="tablist" aria-label="Payment method">
        <?php foreach ( $meth as $mth ) :
            $key = (string) ( $mth['key'] ?? '' );
            $is  = $key === $active_method; ?>
        <button type="button" class="mgk-pay-method-btn<?php echo $is ? ' is-active' : ''; ?>"
                role="tab" aria-selected="<?php echo $is ? 'true' : 'false'; ?>"
                data-pay-method="<?php echo esc_attr( $key ); ?>"
                data-event="pay_method_select" data-method="<?php echo esc_attr( $key ); ?>">
            <span class="mgk-pay-method-radio" aria-hidden="true"></span>
            <span class="mgk-pay-method-label"><?php echo esc_html( $mth['label'] ?? '' ); ?></span>
            <?php if ( ! empty( $mth['tag'] ) ) : ?><span class="mgk-pay-method-tag"><?php echo esc_html( $mth['tag'] ); ?></span><?php endif; ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- PayNow QR panel -->
    <div class="mgk-pay-panel mgk-pay-panel--paynow<?php echo $active_method === 'paynow' && ! $active_state ? ' is-active' : ''; ?>"
         data-pay-panel="paynow" role="tabpanel">
        <p class="mgk-pay-panel-head"><?php echo esc_html( $paynow_help ); ?></p>
        <div class="mgk-pay-qr" data-mgk-pay-qr data-booking-id="<?php echo esc_attr( $booking_id ); ?>" aria-hidden="true"><span>PayNow QR</span></div>
        <p class="mgk-pay-payee">
            CORPORATE UEN: <?php echo esc_html( $payee['uen'] ?? '' ); ?> · <?php echo esc_html( $payee['payee'] ?? '' ); ?><br>
            REF: <?php echo esc_html( $ref ); ?> · QUOTE THIS REF WHEN PAYING
        </p>
        <p class="mgk-pay-waiting" data-event="pay_waiting"><?php echo esc_html( $waiting ); ?></p>
    </div>

    <!-- Card (Stripe) fallback panel — FR-PAY-05 -->
    <div class="mgk-pay-panel mgk-pay-panel--card<?php echo $active_method === 'card' && ! $active_state ? ' is-active' : ''; ?>"
         data-pay-panel="card" role="tabpanel">
        <span class="mgk-bk-sectag mgk-bk-sectag--inline">CARD (Stripe · FR-PAY-05)</span>
        <p class="mgk-pay-card-redirect">🔒 You'll enter your card details securely on the next step (Stripe Checkout). No card data is stored on this site — you only enter it once.</p>
        <p class="mgk-pay-card-note"><?php echo esc_html( $card_note ); ?></p>
    </div>

    <!-- Status panels (post-submit) — FR-PAY-03/08 -->
    <div class="mgk-pay-panel mgk-pay-status mgk-pay-status--processing<?php echo $active_state === 'processing' ? ' is-active' : ''; ?>"
         data-pay-panel="processing" role="status" aria-live="polite">
        <span class="mgk-bk-sectag mgk-bk-sectag--inline">PROCESSING / VERIFYING WEBHOOK</span>
        <div class="mgk-pay-status-icon" aria-hidden="true">⏳</div>
        <p class="mgk-pay-status-title"><?php echo esc_html( $state_copy( 'processing' ) ); ?></p>
        <p class="mgk-pay-status-note"><?php echo esc_html( $states['processing']['note'] ?? '' ); ?></p>
    </div>

    <div class="mgk-pay-panel mgk-pay-status mgk-pay-status--success<?php echo $active_state === 'success' ? ' is-active' : ''; ?>"
         data-pay-panel="success" role="status" aria-live="polite">
        <span class="mgk-bk-sectag mgk-bk-sectag--inline">SUCCESS → S12</span>
        <div class="mgk-pay-status-icon mgk-pay-status-icon--ok" aria-hidden="true">✓</div>
        <p class="mgk-pay-status-title"><?php echo esc_html( $state_copy( 'success' ) ); ?></p>
    </div>

    <div class="mgk-pay-panel mgk-pay-status mgk-pay-status--failed<?php echo $active_state === 'failed' ? ' is-active' : ''; ?>"
         data-pay-panel="failed" role="alert">
        <span class="mgk-bk-sectag mgk-bk-sectag--inline">FAILED</span>
        <p class="mgk-pay-status-msg" data-pay-failed-msg><?php echo esc_html( $state_copy( 'failed' ) ); ?></p>
        <button type="button" class="mgk-pay-retry" data-pay-retry data-event="pay_retry"><?php echo esc_html( $states['failed']['note'] ?? 'Try again' ); ?></button>
    </div>

    <div class="mgk-pay-panel mgk-pay-status mgk-pay-status--mismatch<?php echo $active_state === 'mismatch' ? ' is-active' : ''; ?>"
         data-pay-panel="mismatch" role="alert">
        <span class="mgk-bk-sectag mgk-bk-sectag--inline">REFERENCE MISMATCH (FR-PAY-08)</span>
        <p class="mgk-pay-status-msg"><?php echo esc_html( $state_copy( 'mismatch' ) ); ?></p>
    </div>
</section>
