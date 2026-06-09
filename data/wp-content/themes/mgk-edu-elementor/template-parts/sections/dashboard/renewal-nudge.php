<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx  = $args['context'] ?? [];
$renewal = $ctx['renewal'] ?? [];
if ( empty( $renewal['show'] ) ) return;
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-renewal" data-event="renewal_nudge_view">
    <div class="mgk-parent-dashboard__shell">
        <div class="mgk-parent-dashboard-renewal__copy">
            <strong><span aria-hidden="true">&#9200;</span> <?php echo esc_html( $renewal['title'] ?? '' ); ?></strong>
            <p><?php echo esc_html( $renewal['subline'] ?? '' ); ?></p>
        </div>
        <div class="mgk-parent-dashboard-renewal__actions">
            <a class="mgk-parent-dashboard-btn mgk-parent-dashboard-btn--red" href="<?php echo esc_url( $renewal['renew_url'] ?? '#' ); ?>" data-event="renew_package_click"><?php echo esc_html( $atts['renew_label'] ?? 'Renew Package →' ); ?></a>
            <?php if ( ! mgk_parent_bool( $atts['hide_snooze'] ?? '' ) ) : ?>
                <button type="button" class="mgk-parent-dashboard-btn mgk-parent-dashboard-btn--outline" data-event="renewal_snooze_click"><?php echo esc_html( $atts['snooze_label'] ?? 'Snooze 7d ×' ); ?></button>
            <?php endif; ?>
        </div>
    </div>
</section>
