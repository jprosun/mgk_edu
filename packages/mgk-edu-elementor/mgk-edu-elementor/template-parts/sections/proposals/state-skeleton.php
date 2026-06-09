<?php
/**
 * Proposal loading skeleton state slice.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$lines = isset( $atts['lines'] ) ? max( 1, min( 6, (int) $atts['lines'] ) ) : 4;
?>
<section class="mgk-proposal-state mgk-proposal-state--skeleton">
    <div class="mgk-proposal-state-shell">
        <?php if ( ! mgk_proposal_bool( $atts['hide_label'] ?? '' ) ) : ?>
            <div class="mgk-proposal-state__label"><?php echo esc_html( $atts['label'] ?? 'Loading skeleton' ); ?></div>
        <?php endif; ?>
        <div class="mgk-proposal-state__body" aria-hidden="true">
            <div class="mgk-proposal-state-skeleton">
                <?php if ( ! mgk_proposal_bool( $atts['hide_avatar'] ?? '' ) ) : ?>
                    <span class="mgk-proposal-state-skeleton__avatar"></span>
                <?php endif; ?>
                <?php if ( ! mgk_proposal_bool( $atts['hide_lines'] ?? '' ) ) : ?>
                    <div class="mgk-proposal-state-skeleton__lines">
                        <?php for ( $i = 0; $i < $lines; $i++ ) : ?>
                            <span></span>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
