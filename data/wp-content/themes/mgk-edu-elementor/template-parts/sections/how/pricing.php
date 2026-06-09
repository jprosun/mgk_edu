<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$pricing = $args['how']['pricing'] ?? [];
$included = $args['how']['included'] ?? [];
?>
<section class="mgk-section mgk-how-pricing">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Transparent pricing, no hidden fees</h2>
            <p>What you see is what you pay</p>
        </div>
        <div class="mgk-how-split">
            <div class="mgk-price-panel">
                <h3>What you pay</h3>
                <div class="mgk-price-lines">
                <?php foreach ( $pricing as $row ) : ?>
                    <div>
                        <span><?php echo esc_html( $row['label'] ?? '' ); ?></span>
                        <strong><?php echo esc_html( $row['value'] ?? '' ); ?></strong>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <aside class="mgk-card mgk-included-card">
                <h3>What's included free</h3>
                <ul class="mgk-check-list">
                    <?php foreach ( $included as $item ) : ?>
                        <li><?php echo esc_html( $item ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
        <div class="mgk-how-center">
            <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_cta_url( 'pricing' ) ); ?>" data-event="cta_click" data-screen="how_it_works" data-cta="view_pricing_calculator">View Full Pricing Calculator →</a>
        </div>
    </div>
</section>
