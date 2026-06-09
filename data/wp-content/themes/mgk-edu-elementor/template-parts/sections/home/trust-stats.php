<?php
/** S01 Trust stats. @var array $args — items[] (each: value,label). Empty -> site settings. */
if ( ! defined( 'ABSPATH' ) ) exit;
$stats = ( ! empty( $args['items'] ) && is_array( $args['items'] ) ) ? $args['items'] : mgk_site_home_stats();
?>
    <section class="mgk-section-sm mgk-section-soft">
        <div class="mgk-shell mgk-trust-grid">
            <?php foreach ( $stats as $stat ) : ?>
                <div class="mgk-stat"><strong><?php echo esc_html( $stat['value'] ); ?></strong><span><?php echo esc_html( $stat['label'] ); ?></span></div>
            <?php endforeach; ?>
        </div>
    </section>
