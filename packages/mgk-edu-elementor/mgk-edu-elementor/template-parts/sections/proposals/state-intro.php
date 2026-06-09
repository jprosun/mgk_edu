<?php
/**
 * Proposal state-slices intro.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-proposal-state-intro">
    <div class="mgk-proposal-state-shell">
        <?php if ( ! mgk_proposal_bool( $atts['hide_heading'] ?? '' ) ) : ?>
            <h1><?php echo esc_html( $atts['heading'] ?? 'State slices' ); ?></h1>
        <?php endif; ?>
        <?php if ( ! mgk_proposal_bool( $atts['hide_nav'] ?? '' ) ) : ?>
            <p><?php echo esc_html( $atts['nav'] ?? 'Expired · Re-match · Skeleton' ); ?></p>
        <?php endif; ?>
    </div>
</section>
