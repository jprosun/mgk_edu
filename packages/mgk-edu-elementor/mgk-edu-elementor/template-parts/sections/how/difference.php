<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['how']['differences'] ?? [];
$icons = [ '⚡', '✓', '▥', '$', '↔', '☏' ];
?>
<section class="mgk-section mgk-how-difference">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>What makes Margick different</h2>
            <p>Built for Singapore parents who demand quality and speed</p>
        </div>
        <div class="mgk-grid mgk-grid-3 mgk-difference-grid">
            <?php foreach ( $items as $index => $item ) : ?>
                <article class="mgk-card mgk-difference-card">
                    <span><?php echo esc_html( $icons[ $index ] ?? ( $item['metric'] ?? '' ) ); ?></span>
                    <h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
                    <p><?php echo esc_html( $item['body'] ?? '' ); ?></p>
                    <strong><?php echo esc_html( strtoupper( $item['proof'] ?? '' ) ); ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
