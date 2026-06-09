<?php
/** S01 Press logos. @var array $args — label */
if ( ! defined( 'ABSPATH' ) ) exit;
$label = $args['label'] ?? mgk_site_setting( 'press_label' );
?>
    <section class="mgk-section-sm mgk-section-surface mgk-home-press">
        <div class="mgk-shell">
            <p class="mgk-muted" style="text-align:center;"><?php echo esc_html( $label ); ?></p>
            <div class="mgk-press-grid">
                <?php foreach ( mgk_site_csv( 'press_names' ) as $press ) : ?>
                    <div class="mgk-placeholder mgk-press-logo"><?php echo esc_html( $press ); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
