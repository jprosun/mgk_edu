<?php
/**
 * Proposal selected-card state slice.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-proposal-state mgk-proposal-state--selected">
    <div class="mgk-proposal-state-shell">
        <?php if ( ! mgk_proposal_bool( $atts['hide_label'] ?? '' ) ) : ?>
            <div class="mgk-proposal-state__label"><?php echo esc_html( $atts['label'] ?? 'Selected (card highlighted)' ); ?></div>
        <?php endif; ?>
        <div class="mgk-proposal-state__body">
            <div class="mgk-proposal-state-selected-card">
                <div class="mgk-proposal-state-selected-card__top">
                    <?php if ( ! mgk_proposal_bool( $atts['hide_tutor'] ?? '' ) ) : ?>
                        <strong class="mgk-proposal-state-selected-card__name"><?php echo esc_html( $atts['tutor'] ?? 'Ms Lee' ); ?></strong>
                    <?php endif; ?>
                    <?php if ( ! mgk_proposal_bool( $atts['hide_status'] ?? '' ) ) : ?>
                        <span class="mgk-proposal-state-selected-card__status"><?php echo esc_html( $atts['status'] ?? 'Selected' ); ?></span>
                    <?php endif; ?>
                    <?php if ( ! mgk_proposal_bool( $atts['hide_dot'] ?? '' ) ) : ?>
                        <span class="mgk-proposal-state-selected-card__dot" aria-hidden="true"></span>
                    <?php endif; ?>
                </div>
                <?php if ( ! mgk_proposal_bool( $atts['hide_button'] ?? '' ) ) : ?>
                    <button type="button" class="mgk-proposal-state__button mgk-proposal-state-selected-card__button">
                        <?php echo esc_html( $atts['button'] ?? 'Continue to trial' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
