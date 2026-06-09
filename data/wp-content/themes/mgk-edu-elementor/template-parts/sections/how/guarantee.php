<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['how']['guarantees'] ?? [];
$icons = [ '★', '🎓', '🛡', '100' ];
?>
<section class="mgk-section mgk-section-accent mgk-how-guarantee">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Our Quality Guarantee</h2>
            <p>We stand behind every tutor on our platform.</p>
        </div>
        <div class="mgk-grid mgk-grid-4">
            <?php foreach ( $items as $index => $item ) : ?>
                <article class="mgk-guarantee-card">
                    <span><?php echo esc_html( $icons[ $index ] ?? '✓' ); ?></span>
                    <h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
                    <p><?php echo esc_html( $item['body'] ?? '' ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
