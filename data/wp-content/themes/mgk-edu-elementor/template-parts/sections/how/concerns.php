<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['how']['concerns'] ?? [];
?>
<section class="mgk-section mgk-section-surface mgk-how-concerns">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Common concerns we address</h2>
            <p>Real questions from Singapore parents</p>
        </div>
        <div class="mgk-grid mgk-grid-2">
            <?php foreach ( $items as $item ) : ?>
                <article class="mgk-card mgk-concern-card">
                    <h3><?php echo esc_html( '"' . ( $item['q'] ?? '' ) . '"' ); ?></h3>
                    <span></span>
                    <span></span>
                    <p>Our answer: <?php echo esc_html( strtoupper( $item['a'] ?? '' ) ); ?> →</p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
