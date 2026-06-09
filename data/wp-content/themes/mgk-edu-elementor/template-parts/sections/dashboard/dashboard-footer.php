<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-footer">
    <div class="mgk-parent-dashboard__shell">
        <strong><?php echo esc_html( $atts['logo'] ?? '[AGENCY LOGO]' ); ?></strong>
        <p><?php echo esc_html( $atts['line'] ?? '© 2026 · Powered by Margick · MOE Registered · PDPA compliant' ); ?></p>
    </div>
</section>
