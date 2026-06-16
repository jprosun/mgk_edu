<?php
/**
 * S11 — SEC 4 Order summary (tutor + slot line, stacked discounts, total).
 *
 * Every amount + the discount stack + cap note + GST come from the locked
 * pricing core ($args['summary']). Elementor cannot edit them. Copy labels
 * (heading / subtotal / total) are SAFE.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a   = (array) ( $args ?? [] );
$bd  = (array) ( $a['summary'] ?? [] );
$rows = (array) ( $bd['rows'] ?? [] );

$tutor    = (array) ( $a['tutor'] ?? [] );
$selected = (array) ( $a['selected'] ?? [] );
$day_lbl  = (string) ( $a['active_label'] ?? '' );
$dur_h    = (string) ( $bd['duration_h'] ?? '1.5' );

$tutor_short = $tutor['name'] ?? 'your tutor';
$slot_label  = $selected['label'] ?? '';
$slot_line   = trim( ( $day_lbl ?: '' ) . ( $slot_label ? ' · ' . $slot_label : '' ) . ' · Online' );

$tag      = $a['section_tag']    ?? 'SEC 4 Price breakdown';
$heading  = $a['heading']        ?? 'Order summary';
$sub_lbl  = $a['subtotal_label'] ?? 'Subtotal';
$tot_lbl  = $a['total_label']    ?? 'Total';

$avatar = $tutor['avatar_url'] ?? '';
$booking_id   = (int) ( $a['booking_id'] ?? 0 );
$voucher_code = (string) ( $bd['voucher_code'] ?? '' );
?>
<section class="mgk-bk-card mgk-pay-summary" data-reveal data-event="pay_summary_view" data-booking-id="<?php echo esc_attr( $booking_id ); ?>">
    <span class="mgk-bk-sectag"><?php echo esc_html( $tag ); ?></span>
    <h2 class="mgk-pay-summary-heading"><?php echo esc_html( $heading ); ?></h2>

    <div class="mgk-pay-summary-tutor">
        <span class="mgk-pay-summary-avatar"<?php echo $avatar ? ' style="background-image:url(' . esc_url( $avatar ) . ')"' : ''; ?> aria-hidden="true"></span>
        <span class="mgk-pay-summary-tutor-info">
            <span class="mgk-pay-summary-tutor-name"><strong>Trial</strong> · <?php echo esc_html( $tutor_short ); ?></span>
            <span class="mgk-pay-summary-tutor-slot"><?php echo esc_html( $slot_line ?: ( $dur_h . 'h trial · Online' ) ); ?></span>
        </span>
    </div>

    <div class="mgk-bk-breakdown mgk-pay-breakdown">
        <div data-mgk-bd-rows>
        <?php foreach ( $rows as $r ) :
            $cls = 'mgk-bk-bd-row';
            if ( ! empty( $r['accent'] ) ) $cls .= ' is-accent';
            if ( ! empty( $r['strong'] ) ) $cls .= ' is-strong'; ?>
        <div class="<?php echo esc_attr( $cls ); ?>">
            <span class="mgk-bk-bd-label"><?php echo esc_html( $r['label'] ?? '' ); ?></span>
            <span class="mgk-bk-bd-value"><?php echo esc_html( $r['value'] ?? '' ); ?></span>
        </div>
        <?php endforeach; ?>
        </div>

        <?php if ( ! empty( $bd['cap_note'] ) ) : ?>
        <p class="mgk-pay-cap-note" data-event="pay_discount_capped" data-mgk-cap-note><?php echo esc_html( $bd['cap_note'] ); ?></p>
        <?php endif; ?>

        <?php if ( $booking_id ) : ?>
        <div class="mgk-pay-voucher" data-mgk-voucher>
            <label class="mgk-pay-voucher-label" for="mgk-voucher-code">Have a voucher?</label>
            <div class="mgk-pay-voucher-row">
                <input type="text" id="mgk-voucher-code" data-mgk-voucher-code
                       value="<?php echo esc_attr( $voucher_code ); ?>"
                       placeholder="Enter code" autocomplete="off" spellcheck="false"
                       style="text-transform:uppercase">
                <button type="button" class="mgk-pay-voucher-apply" data-mgk-voucher-apply>
                    <?php echo $voucher_code ? 'Remove' : 'Apply'; ?>
                </button>
            </div>
            <p class="mgk-pay-voucher-fb" data-mgk-voucher-fb<?php echo $voucher_code ? '' : ' hidden'; ?>>
                <?php if ( $voucher_code ) : ?>✓ Voucher <strong><?php echo esc_html( $voucher_code ); ?></strong> applied<?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="mgk-bk-bd-row mgk-pay-subtotal">
            <span class="mgk-bk-bd-label"><?php echo esc_html( $sub_lbl ); ?></span>
            <span class="mgk-bk-bd-value" data-mgk-subtotal><?php echo esc_html( $bd['subtotal'] ?? '' ); ?></span>
        </div>
        <div class="mgk-bk-bd-row mgk-pay-total">
            <span class="mgk-bk-bd-label"><?php echo esc_html( $tot_lbl ); ?></span>
            <span class="mgk-bk-bd-value" data-mgk-total><?php echo esc_html( $bd['total'] ?? '' ); ?></span>
        </div>
        <p class="mgk-bk-bd-gst mgk-pay-gst"><?php echo esc_html( $bd['gst_note'] ?? '' ); ?></p>
    </div>
</section>
