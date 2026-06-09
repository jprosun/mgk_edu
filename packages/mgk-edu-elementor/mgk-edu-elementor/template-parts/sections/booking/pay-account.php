<?php
/**
 * S11 — SEC 3 Account auto-create (email capture + OTP note).
 *
 * Copy is SAFE. The OTP / magic-link rule + the FR-BOOK-07 / BR-22 references
 * come from the locked core ($args['account']); no password is ever collected.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a   = (array) ( $args ?? [] );
$acc = (array) ( $a['account'] ?? [] );

$tag         = $a['section_tag'] ?? 'SEC 3 Account auto-create';
$heading     = $a['heading']     ?? 'Where should we send your booking?';
$subnote     = $a['subnote']     ?? 'WE’LL CREATE YOUR ACCOUNT AUTOMATICALLY — NO PASSWORD TO REMEMBER.';
$placeholder = $a['placeholder'] ?? 'you.parent@example.sg';
$otp_note    = $a['otp_note']    ?? ( $acc['otp_note'] ?? "We'll email a 6-digit OTP + magic link to verify. No password needed (FR-BOOK-07 / BR-22)." );
?>
<section class="mgk-bk-card mgk-pay-account" data-reveal data-event="pay_account_view">
    <span class="mgk-bk-sectag"><?php echo esc_html( $tag ); ?></span>
    <h2 class="mgk-pay-account-heading"><?php echo esc_html( $heading ); ?></h2>
    <p class="mgk-pay-account-sub"><?php echo esc_html( $subnote ); ?></p>

    <label class="mgk-pay-field">
        <span class="mgk-pay-field-icon" aria-hidden="true">✉</span>
        <input type="email" class="mgk-pay-input" name="mgk_pay_email"
               placeholder="<?php echo esc_attr( $placeholder ); ?>"
               autocomplete="email" inputmode="email"
               data-mgk-pay-email data-event="pay_email_input" />
    </label>

    <p class="mgk-pay-otp-note" data-event="pay_otp_note">
        <span class="mgk-pay-otp-key" aria-hidden="true">🔑</span>
        <?php echo esc_html( $otp_note ); ?>
    </p>
</section>
