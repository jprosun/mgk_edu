<?php
/**
 * S10 — Live calendar: heading + week nav + 7-day week strip.
 *
 * Week days + counts come from the locked slot helper ($args['week_days']).
 * Heading / live-note / nav labels are SAFE copy. Availability is not editable
 * in Elementor.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a       = (array) ( $args ?? [] );
$days    = (array) ( $a['week_days'] ?? [] );
$context = (array) ( $a['context'] ?? [] );
$active  = (string) ( $a['active_iso'] ?? '' );
$offset  = (int) ( $a['week_offset'] ?? 0 );

$heading    = $a['heading']    ?? 'Pick a trial slot';
$live_note  = $a['live_note']  ?? '• LIVE AVAILABILITY · UPDATES IN REAL TIME';
$week_label = $a['week_label'] ?? ( $a['week_label'] ?? 'This week' );
$prev_label = $a['prev_label'] ?? '‹ Prev';
$next_label = $a['next_label'] ?? 'Next ›';

$base = get_permalink() ?: home_url( '/book-slot/' );
$mk_url = function ( $o ) use ( $context, $base ) {
    return add_query_arg( array_filter( [
        'lead'  => $context['lead_token'] ?? '',
        'tutor' => $context['tutor_slug'] ?? '',
        'week'  => $o ?: null,
    ] ), $base );
};
?>
<div class="mgk-bk-calendar" data-event="booking_pick_slot_view">
    <div class="mgk-bk-cal-head">
        <div class="mgk-bk-cal-title">
            <h1><?php echo esc_html( $heading ); ?></h1>
            <p class="mgk-bk-cal-live"><?php echo esc_html( $live_note ); ?></p>
        </div>
        <div class="mgk-bk-week-nav" role="group" aria-label="Week navigation">
            <a class="mgk-bk-week-btn" href="<?php echo esc_url( $mk_url( $offset - 1 ) ); ?>" data-event="slot_week_nav"><?php echo esc_html( $prev_label ); ?></a>
            <span class="mgk-bk-week-btn is-current"><?php echo esc_html( $week_label ); ?></span>
            <a class="mgk-bk-week-btn" href="<?php echo esc_url( $mk_url( $offset + 1 ) ); ?>" data-event="slot_week_nav"><?php echo esc_html( $next_label ); ?></a>
        </div>
    </div>

    <div class="mgk-bk-weekstrip" role="list" aria-label="Week availability">
        <?php foreach ( $days as $d ) :
            $is_active = $d['iso'] === $active;
            $cls = 'mgk-bk-day';
            if ( $d['full'] ) $cls .= ' is-full';
            else $cls .= ' is-open';
            if ( $is_active ) $cls .= ' is-active';
            $count_label = $d['full'] ? 'Full' : '• ' . $d['count'] . ' slot' . ( $d['count'] === 1 ? '' : 's' );
            // Day label shown in the "available times" heading (e.g. "Tue 2 Jun").
            $day_heading = ucwords( strtolower( gmdate( 'D j M', $d['date_ts'] ) ) );
            ?>
            <?php if ( $d['full'] ) : ?>
            <div class="<?php echo esc_attr( $cls ); ?>" role="listitem" aria-disabled="true">
                <span class="mgk-bk-day-label"><?php echo esc_html( $d['label'] ); ?></span>
                <span class="mgk-bk-day-count"><?php echo esc_html( $count_label ); ?></span>
            </div>
            <?php else : ?>
            <button type="button" class="<?php echo esc_attr( $cls ); ?>" role="listitem"
                    data-event="slot_day_select" data-day="<?php echo esc_attr( $d['iso'] ); ?>"
                    data-day-heading="<?php echo esc_attr( $day_heading ); ?>"
                    <?php echo $is_active ? 'aria-pressed="true"' : ''; ?>>
                <span class="mgk-bk-day-label"><?php echo esc_html( $d['label'] ); ?></span>
                <span class="mgk-bk-day-count"><?php echo esc_html( $count_label ); ?></span>
            </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
