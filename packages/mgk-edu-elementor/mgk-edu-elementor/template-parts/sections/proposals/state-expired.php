<?php
/**
 * Proposal expired state slice.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-proposal-state mgk-proposal-state--expired">
    <div class="mgk-proposal-state-shell">
        <?php if ( ! mgk_proposal_bool( $atts['hide_label'] ?? '' ) ) : ?>
            <div class="mgk-proposal-state__label"><?php echo esc_html( $atts['label'] ?? 'Proposed - expired (BR-11)' ); ?></div>
        <?php endif; ?>
        <div class="mgk-proposal-state__body">
            <?php if ( ! mgk_proposal_bool( $atts['hide_icon'] ?? '' ) ) : ?>
                <div class="mgk-proposal-state__icon" aria-hidden="true"><?php echo esc_html( $atts['icon'] ?? '⌛' ); ?></div>
            <?php endif; ?>
            <?php if ( ! mgk_proposal_bool( $atts['hide_title'] ?? '' ) ) : ?>
                <strong class="mgk-proposal-state__title"><?php echo esc_html( $atts['title'] ?? 'These proposals expired' ); ?></strong>
            <?php endif; ?>
            <?php if ( ! mgk_proposal_bool( $atts['hide_message'] ?? '' ) ) : ?>
                <p class="mgk-proposal-state__message"><?php echo esc_html( $atts['message'] ?? '48H WINDOW CLOSED. TUTOR AVAILABILITY MAY HAVE CHANGED.' ); ?></p>
            <?php endif; ?>
            <?php if ( ! mgk_proposal_bool( $atts['hide_button'] ?? '' ) ) : ?>
                <button type="button" class="mgk-proposal-state__button" data-event="rematch_request_click" data-mgk-event="rematch_request_click">
                    <?php echo esc_html( $atts['button'] ?? 'Re-send proposals (free)' ); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>
