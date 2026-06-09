<?php
/**
 * S09 — Trial offer box (pale-red, price focus).
 *
 * DATA (price, old price, discount %, saving) comes from the locked offer
 * calculation ($args['offer']). Labels/note are SAFE copy. Elementor cannot
 * edit the figures or the discount/GST rules.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a     = (array) ( $args ?? [] );
$offer = (array) ( $a['offer'] ?? [] );

$trial    = (int) ( $offer['trial_price'] ?? 40 );
$old      = (int) ( $offer['old_price'] ?? 65 );
$pct      = (int) ( $offer['discount_percent'] ?? 40 );
$saving   = (int) ( $offer['saving'] ?? 25 );

$label = $a['label'] ?? 'TRIAL LESSON · FIRST LESSON';
$badge = $a['badge'] ?? ( $pct . '% OFF · SAVE $' . $saving );
$note  = $a['note']  ?? 'SGD · INCL. GST · ONE TRIAL PER TUTOR';
?>
<div class="mgk-bk-offer" data-event="booking_offer_view">
    <p class="mgk-bk-offer-label"><?php echo esc_html( $label ); ?></p>
    <p class="mgk-bk-offer-price">
        <span class="mgk-bk-offer-now">$<?php echo esc_html( $trial ); ?></span>
        <span class="mgk-bk-offer-was">$<?php echo esc_html( $old ); ?></span>
    </p>
    <span class="mgk-bk-offer-badge"><?php echo esc_html( $badge ); ?></span>
    <p class="mgk-bk-offer-note"><?php echo esc_html( $note ); ?></p>
</div>
