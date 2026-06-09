<?php
/**
 * S11 — Terms consent (required to enable the pay CTA).
 *
 * The links + the requirement (BR-07 refund ref) come from the locked core
 * ($args['terms']); only the lead-in copy is SAFE. JS gates the pay CTA on this.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a     = (array) ( $args ?? [] );
$links = (array) ( $a['terms'] ?? [] );
$lead  = $a['lead_text'] ?? 'I agree to the';

$render_link = function ( $l ) {
    if ( empty( $l['label'] ) ) return '';
    return '<a href="' . esc_url( $l['url'] ?? '#' ) . '" target="_blank" rel="noopener">' . esc_html( $l['label'] ) . '</a>';
};
$terms  = $render_link( $links['terms']  ?? [] );
$refund = $render_link( $links['refund'] ?? [] );
$pdpa   = $render_link( $links['pdpa']   ?? [] );
?>
<section class="mgk-bk-card mgk-pay-terms" data-reveal data-event="pay_terms_view">
    <label class="mgk-pay-terms-row">
        <input type="checkbox" class="mgk-pay-terms-check" data-mgk-pay-terms
               data-event="pay_terms_toggle" />
        <span class="mgk-pay-terms-text">
            <?php
            echo esc_html( $lead ) . ' '
                . $terms . ', ' . $refund . ' &amp; ' . $pdpa . '.'; // phpcs:ignore WordPress.Security.EscapeOutput
            ?>
        </span>
    </label>
</section>
