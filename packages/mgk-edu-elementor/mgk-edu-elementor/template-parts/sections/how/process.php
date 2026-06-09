<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$steps = $args['how']['steps'] ?? [];
?>
<section class="mgk-section mgk-how-process">
    <div class="mgk-shell">
        <div class="mgk-section-head mgk-how-section-head">
            <h2>From need to perfect tutor in 4 steps</h2>
            <p>Total time: ~24 hours from first inquiry to confirmed booking</p>
        </div>
        <div class="mgk-process-grid">
            <?php foreach ( $steps as $index => $step ) : ?>
                <article class="mgk-process-step">
                    <span class="mgk-process-num"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
                    <h3><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
                    <strong><?php echo esc_html( $step['time'] ?? '' ); ?></strong>
                    <ul>
                        <?php foreach ( $step['items'] ?? [] as $item ) : ?>
                            <li><?php echo esc_html( $item ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><?php echo esc_html( $step['note'] ?? '' ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="mgk-how-center">
            <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_cta_url( 'find-tutor' ) ); ?>" data-event="cta_click" data-screen="how_it_works" data-cta="start_step_1"><span class="mgk-step-cta-full">Start Step 1 Now →</span><span class="mgk-step-cta-mobile">Start Step 1 →</span></a>
        </div>
    </div>
</section>
