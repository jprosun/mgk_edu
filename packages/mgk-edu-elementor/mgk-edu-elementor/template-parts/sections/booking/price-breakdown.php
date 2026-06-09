<?php
/**
 * S09 — Checkout price breakdown box.
 *
 * DATA (rows + amounts + GST note) comes from the locked pricing helper
 * ($args['breakdown']). Elementor cannot edit the amounts or rules.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$bd   = (array) ( $a['breakdown'] ?? [] );
$rows = (array) ( $bd['rows'] ?? [] );
$gst  = (string) ( $bd['gst_note'] ?? 'INCL. GST' );
?>
<div class="mgk-bk-breakdown">
    <?php foreach ( $rows as $r ) :
        $cls = 'mgk-bk-bd-row';
        if ( ! empty( $r['strong'] ) ) $cls .= ' is-due';
        if ( ! empty( $r['accent'] ) ) $cls .= ' is-accent'; ?>
    <div class="<?php echo esc_attr( $cls ); ?>">
        <span class="mgk-bk-bd-label"><?php echo esc_html( $r['label'] ?? '' ); ?></span>
        <span class="mgk-bk-bd-value"><?php echo esc_html( $r['value'] ?? '' ); ?></span>
    </div>
    <?php endforeach; ?>
    <p class="mgk-bk-bd-gst"><?php echo esc_html( $gst ); ?></p>
</div>
