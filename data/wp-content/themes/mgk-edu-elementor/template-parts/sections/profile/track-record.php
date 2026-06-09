<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Track Record', '', 'Data from completed packages · Updated weekly' ); ?>
        <div class="mgk-profile-track">
            <?php foreach ( $tutor['track'] as $stat ) : ?>
                <div><strong><?php echo esc_html( $stat[0] ); ?></strong><span><?php echo esc_html( $stat[1] ); ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="mgk-card mgk-chart-card">
            <h3>Avg student improvement (last 12 months)</h3>
            <div class="mgk-placeholder">Line chart: grade improvement over time</div>
        </div>
    </div>
</section>
