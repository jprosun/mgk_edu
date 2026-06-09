<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$comparison = $args['pricing']['comparison'] ?? [];
?>
<section class="mgk-section mgk-pricing-comparison-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Margick vs Traditional Agency · Real numbers', '', 'Sample: P5 Math · 8 lessons x 1.5h x Full-time tutor at $55/hr' ); ?>
        <div class="mgk-cost-comparison">
            <article class="mgk-card" data-mgk-comparison-card tabindex="0">
                <h3>Traditional Agency</h3>
                <?php foreach ( $comparison['traditional'] ?? [] as $row ) : ?>
                    <div><span><?php echo esc_html( $row['label'] ); ?></span><strong><?php echo esc_html( $row['value'] ); ?></strong></div>
                <?php endforeach; ?>
                <footer><span>Total</span><strong>$782.50</strong></footer>
            </article>
            <article class="mgk-card is-mgk is-selected" data-mgk-comparison-card tabindex="0">
                <h3>Margick Package 8</h3>
                <?php foreach ( $comparison['mgk'] ?? [] as $row ) : ?>
                    <div><span><?php echo esc_html( $row['label'] ); ?></span><strong><?php echo esc_html( $row['value'] ); ?></strong></div>
                <?php endforeach; ?>
                <footer><span>Total</span><strong>$594-634</strong></footer>
                <p>You save $148-$188, about 22%.</p>
            </article>
        </div>
    </div>
</section>
