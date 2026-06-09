<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-proposal-rematch">
    <div class="mgk-proposal-shell mgk-proposal-rematch__inner">
        <div>
            <?php if ( ! mgk_proposal_bool( $atts['hide_heading'] ?? '' ) ) : ?>
                <h2><?php echo esc_html( $atts['heading'] ?? 'None quite right?' ); ?></h2>
            <?php endif; ?>
            <?php if ( ! mgk_proposal_bool( $atts['hide_body'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $atts['body'] ?? '' ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( ! mgk_proposal_bool( $atts['hide_button'] ?? '' ) ) : ?>
            <button type="button" class="mgk-proposal-rematch__button" data-event="rematch_request_click" data-mgk-event="rematch_request_click">
                <span aria-hidden="true">&#8635;</span> <?php echo esc_html( $atts['button'] ?? 'Request re-match (free)' ); ?>
            </button>
        <?php endif; ?>
    </div>
</section>
