<?php
/**
 * S12 — Booking summary card (TUTOR / DATE-TIME / PAID · SUBJECT / FORMAT / METHOD).
 *
 * Every value comes from the locked booking + payment summary ($args['summary']);
 * Elementor cannot edit them. The field LABELS are SAFE copy (l_* atts).
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a = (array) ( $args ?? [] );
$s = (array) ( $a['summary'] ?? [] );

$l_tutor    = $a['l_tutor']    ?? 'TUTOR';
$l_subject  = $a['l_subject']  ?? 'SUBJECT/LEVEL';
$l_datetime = $a['l_datetime'] ?? 'DATE/TIME';
$l_format   = $a['l_format']   ?? 'FORMAT';
$l_paid     = $a['l_paid']     ?? 'PAID';
$l_method   = $a['l_method']   ?? 'METHOD';

$row = function ( $label, $value ) {
    return '<div class="mgk-cf-sum-row"><span class="mgk-cf-sum-label">' . esc_html( $label )
        . '</span><span class="mgk-cf-sum-value">' . esc_html( $value ) . '</span></div>';
};
?>
<section class="mgk-cf-card mgk-cf-summary" data-event="confirm_summary_view">
    <h2 class="mgk-cf-card-title"><?php echo esc_html( $s['title'] ?? 'Trial lesson · 1.5h' ); ?></h2>
    <div class="mgk-cf-sum-grid">
        <div class="mgk-cf-sum-col">
            <?php
            echo $row( $l_tutor, $s['tutor'] ?? '' );        // phpcs:ignore
            echo $row( $l_datetime, $s['datetime'] ?? '' );  // phpcs:ignore
            echo $row( $l_paid, $s['paid'] ?? '' );          // phpcs:ignore
            ?>
        </div>
        <div class="mgk-cf-sum-col">
            <?php
            echo $row( $l_subject, $s['subject_level'] ?? '' ); // phpcs:ignore
            echo $row( $l_format, $s['format'] ?? '' );         // phpcs:ignore
            echo $row( $l_method, $s['method'] ?? '' );         // phpcs:ignore
            ?>
        </div>
    </div>
</section>
