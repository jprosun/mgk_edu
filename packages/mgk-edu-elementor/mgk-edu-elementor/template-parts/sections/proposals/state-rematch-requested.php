<?php
/**
 * Proposal re-match requested state slice.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-proposal-state mgk-proposal-state--rematch-requested">
    <div class="mgk-proposal-state-shell">
        <?php if ( ! mgk_proposal_bool( $atts['hide_label'] ?? '' ) ) : ?>
            <div class="mgk-proposal-state__label"><?php echo esc_html( $atts['label'] ?? 'Re-match requested' ); ?></div>
        <?php endif; ?>
        <div class="mgk-proposal-state__body">
            <?php if ( ! mgk_proposal_bool( $atts['hide_message'] ?? '' ) ) : ?>
                <p class="mgk-proposal-state__message"><?php echo esc_html( $atts['message'] ?? 'FINDING NEW MATCHES. YOU WILL GET A FRESH SET WITHIN 6H.' ); ?></p>
            <?php endif; ?>
            <?php if ( ! mgk_proposal_bool( $atts['hide_timer'] ?? '' ) ) : ?>
                <strong class="mgk-proposal-state__timer"><?php echo esc_html( $atts['timer'] ?? '05:58:00' ); ?></strong>
            <?php endif; ?>
        </div>
    </div>
</section>
