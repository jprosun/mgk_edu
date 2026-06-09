<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx  = $args['context'] ?? [];
$progress = $ctx['progress'] ?? [];
$log = $ctx['latest_log'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-progress-logs">
    <div class="mgk-parent-dashboard__shell">
        <div class="mgk-parent-dashboard-progress-card">
            <div class="mgk-parent-dashboard-card-head">
                <h2><?php echo esc_html( $progress['title'] ?? 'Weekly progress' ); ?></h2>
                <button type="button" data-event="progress_range_change"><?php echo esc_html( $progress['range_label'] ?? 'LAST 8 WEEKS ▾' ); ?></button>
            </div>
            <div class="mgk-parent-dashboard-chart" aria-hidden="true"></div>
            <p class="mgk-parent-dashboard-chart-note"><?php echo esc_html( $progress['description'] ?? '' ); ?></p>
            <p class="mgk-parent-dashboard-legend"><?php echo esc_html( $progress['legend'] ?? '' ); ?></p>
        </div>
        <div class="mgk-parent-dashboard-log-card">
            <div class="mgk-parent-dashboard-card-head">
                <h2>Latest lesson log</h2>
            </div>
            <div class="mgk-parent-dashboard-log-preview">
                <div class="mgk-parent-dashboard-log-preview__top">
                    <div>
                        <strong><?php echo esc_html( $log['date_subject'] ?? '' ); ?></strong>
                        <b><?php echo esc_html( $log['topic'] ?? '' ); ?></b>
                    </div>
                    <span><?php echo esc_html( $log['status'] ?? '' ); ?></span>
                </div>
                <div class="mgk-parent-dashboard-log-preview__lines"><span></span><span></span></div>
                <div class="mgk-parent-dashboard-log-preview__homework">
                    <i aria-hidden="true"></i>
                    <em><?php echo esc_html( $log['summary'] ?? '' ); ?></em>
                </div>
            </div>
            <a class="mgk-parent-dashboard-btn mgk-parent-dashboard-btn--outline mgk-parent-dashboard-btn--full" href="<?php echo esc_url( $ctx['lesson_logs_url'] ?? '#' ); ?>" data-event="lesson_log_view_all_click"><?php echo esc_html( $atts['logs_button'] ?? 'View all lesson logs →' ); ?></a>
        </div>
    </div>
</section>
