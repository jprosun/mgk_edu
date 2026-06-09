<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$stats = $args['pricing']['hero_stats'] ?? [];
$page = $args['page'] ?? [];
?>
<section class="mgk-section mgk-pricing-hero">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <p class="mgk-eyebrow"><?php echo esc_html( $page['eyebrow'] ?? 'Transparent - no hidden fees' ); ?></p>
            <h1><?php echo esc_html( $page['title'] ?? 'Simple, honest pricing' ); ?></h1>
            <p><?php echo esc_html( $page['body'] ?? 'Estimate lesson costs before you contact a tutor. See hourly ranges, package savings, and possible add-ons upfront.' ); ?></p>
        </div>
        <div class="mgk-pricing-stat-grid">
            <?php foreach ( $stats as $stat ) : ?>
                <div class="mgk-pricing-stat">
                    <strong><?php echo esc_html( $stat['value'] ?? '' ); ?></strong>
                    <span><?php echo esc_html( $stat['label'] ?? '' ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
