<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-proposal-compare-drawer js-mgk-proposal-drawer" data-event="proposal_compare_open" data-mgk-event="proposal_compare_open">
    <div class="mgk-proposal-compare-head">
        <?php if ( ! mgk_proposal_bool( $atts['hide_heading'] ?? '' ) ) : ?>
            <strong><span aria-hidden="true">&#9878;</span> <?php echo esc_html( $atts['heading'] ?? 'Compare' ); ?> (<span class="js-mgk-compare-count">0</span> selected)</strong>
        <?php endif; ?>
        <?php if ( ! mgk_proposal_bool( $atts['hide_button'] ?? '' ) ) : ?>
            <button type="button" class="js-mgk-compare-toggle"><?php echo esc_html( $atts['button'] ?? 'View comparison' ); ?> <span aria-hidden="true">&#9650;</span></button>
        <?php endif; ?>
    </div>
    <?php if ( ! mgk_proposal_bool( $atts['hide_table'] ?? '' ) ) : ?>
        <div class="mgk-proposal-compare-body js-mgk-compare-body">
            <table class="mgk-proposal-compare-table">
                <thead class="js-mgk-compare-head-row"></thead>
                <tbody class="js-mgk-compare-table-body"></tbody>
            </table>
        </div>
    <?php endif; ?>
    <div class="mgk-proposal-toast js-mgk-proposal-toast" role="status" aria-live="polite"></div>
</section>
