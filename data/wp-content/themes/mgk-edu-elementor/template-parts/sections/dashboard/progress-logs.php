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
            <?php $series = $progress['series'] ?? []; ?>
            <?php if ( ! empty( $series ) ) : ?>
                <div class="mgk-parent-dashboard-chart" role="img" aria-label="<?php echo esc_attr( $progress['description'] ?? 'Progress chart' ); ?>" style="display:flex;align-items:flex-end;gap:6px;height:130px;padding:10px 0;">
                    <?php
                    $bar_colors = [ 1 => '#d9534f', 2 => '#e0a23a', 3 => '#4a90d9', 4 => '#1a7f37' ];
                    foreach ( $series as $pt ) :
                        $score = (int) $pt['score'];
                        $h     = max( 12, (int) round( $score / 4 * 100 ) );
                        $col   = $bar_colors[ $score ] ?? '#cfd3d8';
                        $tip   = 'Lesson ' . $pt['n'] . ' · ' . $pt['label'] . ( ! empty( $pt['topic'] ) ? ' · ' . $pt['topic'] : '' );
                        ?>
                        <div title="<?php echo esc_attr( $tip ); ?>" style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;min-width:0;">
                            <span style="width:100%;max-width:34px;background:<?php echo esc_attr( $col ); ?>;height:<?php echo (int) $h; ?>%;min-height:10px;border-radius:5px 5px 0 0;"></span>
                            <small style="font-size:10px;color:#646970;margin-top:5px;">L<?php echo esc_html( $pt['n'] ); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="mgk-parent-dashboard-chart" aria-hidden="true"></div>
            <?php endif; ?>
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
