<?php
/**
 * S10 — Available times for the active day + legend.
 *
 * Times + status come from the locked slot helper ($args['times']). Clicking an
 * available slot holds it (JS → REST). Taken/booked are disabled. Heading +
 * legend labels are SAFE copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a        = (array) ( $args ?? [] );
$times    = (array) ( $a['times'] ?? [] );
$selected = (array) ( $a['selected'] ?? [] );
$day_lbl  = (string) ( $a['active_label'] ?? '' );

$heading = $a['heading'] ?? ( ( $day_lbl ? $day_lbl . ' ' : '' ) . '— available times' );
$lg_avail = $a['legend_available'] ?? 'AVAILABLE';
$lg_taken = $a['legend_taken']     ?? 'TAKEN (LIVE)';
$lg_hold  = $a['legend_hold']      ?? 'YOUR HOLD';

$sel_id = $selected['id'] ?? '';
?>
<div class="mgk-bk-times" data-mgk-slots>
    <h2 class="mgk-bk-times-head"><?php echo esc_html( $heading ); ?></h2>

    <div class="mgk-bk-times-row" role="group" aria-label="Available times">
        <?php foreach ( $times as $t ) :
            $status = $t['status'];
            $is_sel = $t['id'] === $sel_id && in_array( $status, [ 'available', 'held' ], true );
            $disabled = in_array( $status, [ 'taken', 'booked' ], true );
            $cls = 'mgk-bk-slot';
            if ( $is_sel ) $cls .= ' is-selected';
            if ( $disabled ) $cls .= ' is-disabled';
            $sub = $status === 'taken' ? 'just taken' : ( $status === 'booked' ? 'booked' : '' );
            ?>
        <button type="button" class="<?php echo esc_attr( $cls ); ?>"
                data-slot-id="<?php echo esc_attr( $t['id'] ); ?>"
                data-slot-label="<?php echo esc_attr( $t['label'] ); ?>"
                data-event="slot_time_select"
                <?php echo $disabled ? 'disabled aria-disabled="true"' : ''; ?>
                <?php echo $is_sel ? 'aria-pressed="true"' : ''; ?>>
            <span class="mgk-bk-slot-time"><?php echo esc_html( $t['label'] ); ?><?php echo $is_sel ? ' ✓' : ''; ?></span>
            <?php if ( $sub ) : ?><span class="mgk-bk-slot-sub"><?php echo esc_html( $sub ); ?></span><?php endif; ?>
        </button>
        <?php endforeach; ?>
    </div>

    <p class="mgk-bk-legend">
        <span class="mgk-bk-lg mgk-bk-lg--avail"><?php echo esc_html( $lg_avail ); ?></span>
        <span class="mgk-bk-lg mgk-bk-lg--taken"><?php echo esc_html( $lg_taken ); ?></span>
        <span class="mgk-bk-lg mgk-bk-lg--hold"><?php echo esc_html( $lg_hold ); ?></span>
    </p>
    <p class="mgk-bk-times-msg" data-slot-msg hidden></p>
</div>
