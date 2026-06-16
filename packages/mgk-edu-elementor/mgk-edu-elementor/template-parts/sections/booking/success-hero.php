<?php
/**
 * S12 — Success hero (pale-red band, check icon, confirmation line).
 *
 * Confirmation number + email come from the locked view; the heading + the
 * "sent to" prefix are SAFE copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a = (array) ( $args ?? [] );

$heading = $a['heading'] ?? ( ! empty( $a['is_package'] ) ? 'Package purchased!' : 'Trial lesson booked!' );
$prefix  = $a['sent_prefix'] ?? 'CONFIRMATION';
$conf    = (string) ( $a['confirmation'] ?? 'MGK-TRL-0842' );
$email   = (string) ( $a['email'] ?? 'your.email@example.sg' );
?>
<section class="mgk-cf-hero" data-event="confirm_hero_view">
    <div class="mgk-shell mgk-cf-hero__inner">
        <div class="mgk-cf-hero-check" aria-hidden="true">✓</div>
        <h1 class="mgk-cf-hero-title"><?php echo esc_html( $heading ); ?></h1>
        <p class="mgk-cf-hero-conf">
            <?php echo esc_html( $prefix ); ?> #<?php echo esc_html( $conf ); ?>
            · SENT TO ✉ <?php echo esc_html( strtoupper( $email ) ); ?>
        </p>
    </div>
</section>
