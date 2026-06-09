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
?>
<section class="mgk-bk-card mgk-pay-summary" data-reveal data-event="pay_summary_view">
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
        <?php foreach ( $rows as $r ) :
            $cls = 'mgk-bk-bd-row';
            if ( ! empty( $r['accent'] ) ) $cls .= ' is-accent';
            if ( ! empty( $r['strong'] ) ) $cls .= ' is-strong'; ?>
        <div class="<?php echo esc_attr( $cls ); ?>">
            <span class="mgk-bk-bd-label"><?php echo esc_html( $r['label'] ?? '' ); ?></span>
            <span class="mgk-bk-bd-value"><?php echo esc_html( $r['value'] ?? '' ); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if ( ! empty( $bd['cap_note'] ) ) : ?>
        <p class="mgk-pay-cap-note" data-event="pay_discount_capped"><?php echo esc_html( $bd['cap_note'] ); ?></p>
        <?php endif; ?>

        <div class="mgk-bk-bd-row mgk-pay-subtotal">
            <span class="mgk-bk-bd-label"><?php echo esc_html( $sub_lbl ); ?></span>
            <span class="mgk-bk-bd-value"><?php echo esc_html( $bd['subtotal'] ?? '' ); ?></span>
        </div>
        <div class="mgk-bk-bd-row mgk-pay-total">
            <span class="mgk-bk-bd-label"><?php echo esc_html( $tot_lbl ); ?></span>
            <span class="mgk-bk-bd-value"><?php echo esc_html( $bd['total'] ?? '' ); ?></span>
        </div>
        <p class="mgk-bk-bd-gst mgk-pay-gst"><?php echo esc_html( $bd['gst_note'] ?? '' ); ?></p>
    </div>
</section>
