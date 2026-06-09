<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$ctx = $args['context'] ?? [];
$kpis = $ctx['kpis'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-kpis">
    <div class="mgk-parent-dashboard__shell">
        <div class="mgk-parent-dashboard-kpis__grid">
            <?php foreach ( $kpis as $kpi ) : ?>
                <div class="mgk-parent-dashboard-kpi">
                    <strong><?php echo esc_html( $kpi['value'] ?? '' ); ?></strong>
                    <span><?php echo esc_html( $kpi['label'] ?? '' ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
