<?php
/**
 * S10 — Slot hold banner (pale-red, countdown timer).
 *
 * Timer DATA comes from the locked hold ($args['hold']); JS counts down from
 * remaining seconds. Copy is SAFE. Hold logic is not exposed to Elementor.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$hold = (array) ( $a['hold'] ?? [] );

// Active hold → its remaining; otherwise show the full hold window (the slot is
// pre-selected on load and the JS starts counting from here).
$remaining = ! empty( $hold['active'] ) && (int) ( $hold['remaining'] ?? 0 ) > 0
    ? (int) $hold['remaining']
    : (int) ( $a['hold_seconds'] ?? 600 );
$mm = floor( $remaining / 60 );
$ss = $remaining % 60;

$title = $a['title'] ?? '⏱ Slot held for you';
$note  = $a['note']  ?? 'COMPLETE PAYMENT BEFORE THE TIMER ENDS OR THE SLOT IS RELEASED BACK TO OTHERS.';
?>
<div class="mgk-bk-hold" data-mgk-hold data-remaining="<?php echo esc_attr( $remaining ); ?>"
     data-slot="<?php echo esc_attr( $hold['slot_id'] ?? '' ); ?>" data-event="slot_hold_started">
    <div class="mgk-shell mgk-bk-hold__inner">
        <div class="mgk-bk-hold__copy">
            <p class="mgk-bk-hold__title"><?php echo esc_html( $title ); ?></p>
            <p class="mgk-bk-hold__note"><?php echo esc_html( $note ); ?></p>
        </div>
        <div class="mgk-bk-hold__timer" data-hold-timer aria-live="off">
            <span data-hold-mm><?php echo esc_html( sprintf( '%02d', $mm ) ); ?></span>
            <span class="mgk-bk-hold__sep">:</span>
            <span data-hold-ss><?php echo esc_html( sprintf( '%02d', $ss ) ); ?></span>
        </div>
    </div>
    <p class="mgk-bk-hold__expired" data-hold-expired hidden>Your hold expired. Please pick another slot.</p>
</div>
