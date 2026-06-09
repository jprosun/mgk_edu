<?php
/**
 * Reusable pricing card.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$plan = $args['plan'] ?? [];
if ( empty( $plan ) ) {
    return;
}

$featured = ! empty( $plan['featured'] );
?>
<article class="mgk-pricing-card<?php echo $featured ? ' has-badge' : ''; ?>" data-mgk-package-card tabindex="0">
    <?php if ( ! empty( $plan['badge'] ) ) : ?>
        <span class="mgk-pricing-badge"><?php echo esc_html( $plan['badge'] ); ?></span>
    <?php endif; ?>
    <p class="mgk-pricing-name"><?php echo esc_html( $plan['name'] ?? '' ); ?></p>
    <strong><?php echo esc_html( $plan['price'] ?? '' ); ?></strong>
    <span><?php echo esc_html( $plan['summary'] ?? '' ); ?></span>
    <ul>
        <?php foreach ( $plan['features'] ?? [] as $feature ) : ?>
            <li><?php echo esc_html( $feature ); ?></li>
        <?php endforeach; ?>
        <?php foreach ( $plan['limits'] ?? [] as $limit ) : ?>
            <li class="is-muted"><?php echo esc_html( $limit ); ?></li>
        <?php endforeach; ?>
    </ul>
    <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_get_trial_url( [ 'package' => $plan['name'] ?? '' ] ) ); ?>"><?php echo esc_html( $plan['cta'] ?? 'Choose Plan' ); ?></a>
</article>
