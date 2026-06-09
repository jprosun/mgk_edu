<?php
/**
 * S12 — Next steps checklist + e-invoice row.
 *
 * Checklist items + done-state + the invoice id / readiness / download URL come
 * from the locked view ($args['next_steps'], $args['invoice']). Heading +
 * download label are SAFE copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a     = (array) ( $args ?? [] );
$steps = (array) ( $a['next_steps'] ?? [] );
$inv   = (array) ( $a['invoice'] ?? [] );

$heading        = $a['heading']        ?? 'Next steps';
$download_label = $a['download_label'] ?? 'DOWNLOAD PDF →';
?>
<section class="mgk-cf-card mgk-cf-next" data-event="confirm_next_view">
    <h2 class="mgk-cf-card-title"><?php echo esc_html( $heading ); ?></h2>

    <ul class="mgk-cf-next-list">
        <?php foreach ( $steps as $st ) :
            $done = ! empty( $st['done'] ); ?>
        <li class="mgk-cf-next-item<?php echo $done ? ' is-done' : ''; ?>">
            <span class="mgk-cf-next-box" aria-hidden="true"><?php echo $done ? '✓' : ''; ?></span>
            <span class="mgk-cf-next-label"><?php echo esc_html( $st['label'] ?? '' ); ?></span>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="mgk-cf-invoice<?php echo empty( $inv['ready'] ) ? ' is-pending' : ''; ?>">
        <?php if ( ! empty( $inv['ready'] ) ) : ?>
            <span class="mgk-cf-invoice-label">📄 <?php echo esc_html( $inv['label'] ?? '' ); ?></span>
            <a class="mgk-cf-invoice-link" href="<?php echo esc_url( $inv['url'] ?? '#' ); ?>"
               data-event="invoice_download_click"><?php echo esc_html( $download_label ); ?></a>
        <?php else : ?>
            <span class="mgk-cf-invoice-label">📄 Invoice is being generated. Check back shortly.</span>
        <?php endif; ?>
    </div>
</section>
