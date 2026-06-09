<?php
/**
 * S11 — Pay composite page partial.
 *
 * Resolves the locked pay view and renders nav → progress(3) → hold banner →
 * 2-col grid (left: account auto-create + payment method; right: order summary +
 * terms + pay CTA), OR a safe state. All DATA from mgk_get_pay_view(); $args
 * carries SAFE copy overrides. Sections carry data-reveal so the JS can reveal
 * them sequentially after the parent lands here from S10.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$view = function_exists( 'mgk_get_pay_view' ) ? mgk_get_pay_view() : [ 'status' => 'not_found' ];

$part = function ( $slug, $extra = [] ) use ( $view, $a ) {
    return mgk_render_part( 'template-parts/sections/booking/' . $slug, array_merge( $view, (array) $extra ) );
};

// Nav (centre label = booking-with-tutor, same as S10).
$tutor_name = $view['tutor']['name'] ?? '';
$with = 'BOOKING TRIAL';
if ( preg_match( '/\b(Ms|Mr|Mrs|Dr)\.?\s+([A-Z][a-z]+)/', (string) $tutor_name, $m ) ) {
    $with = 'BOOKING TRIAL WITH ' . strtoupper( $m[1] . ' ' . $m[2] );
}
echo $part( 'nav', [ 'secure_label' => '🔒 ' . $with ] ); // phpcs:ignore

// ── Error / non-ok states ───────────────────────────────────
if ( ( $view['status'] ?? '' ) !== 'ok' ) {
    $ctx  = (array) ( $view['context'] ?? [] );
    $back = function_exists( 'mgk_get_back_to_proposals_url' )
        ? mgk_get_back_to_proposals_url( $ctx['lead_token'] ?? '' ) : home_url( '/tutor-proposals/' );
    $slot = function_exists( 'mgk_get_s10_slot_url' ) ? mgk_get_s10_slot_url( $ctx ) : home_url( '/book-slot/' );
    $states = [
        'not_found'   => [ 'We couldn’t find this booking session.', 'Back to matched tutors', $back ],
        'expired'     => [ 'This proposal has expired. Request fresh matches.', 'Request fresh matches', home_url( '/tutor-proposals/' ) ],
        'unavailable' => [ 'This tutor is no longer available. Please pick another slot or tutor.', 'Back to slot picker', $slot ],
    ];
    $s = $states[ $view['status'] ] ?? $states['not_found'];
    ?>
    <main class="mgk-bk-main mgk-bk-main--state">
        <div class="mgk-shell">
            <div class="mgk-bk-state" role="alert" data-event="pay_unavailable_error">
                <p class="mgk-bk-state-msg"><?php echo esc_html( $s[0] ); ?></p>
                <a class="mgk-bk-continue" href="<?php echo esc_url( $s[2] ); ?>"><?php echo esc_html( $s[1] ); ?></a>
            </div>
        </div>
    </main>
    <?php
    return;
}

// ── OK state ────────────────────────────────────────────────
echo $part( 'booking-progress', [ 'current' => 3 ] ); // phpcs:ignore
echo $part( 'slot-hold-banner', $a );                  // phpcs:ignore
?>
<main class="mgk-bk-main mgk-bk-main--pay" data-mgk-pay data-reveal-root>
    <div class="mgk-shell">
        <div class="mgk-bk-grid mgk-bk-pay-grid">
            <div class="mgk-bk-col mgk-bk-col--left">
                <?php
                echo $part( 'pay-account', $a ); // phpcs:ignore
                echo $part( 'pay-method', $a );  // phpcs:ignore
                ?>
            </div>
            <div class="mgk-bk-col mgk-bk-col--right">
                <?php
                echo $part( 'pay-summary', $a ); // phpcs:ignore
                echo $part( 'pay-terms', $a );   // phpcs:ignore
                echo $part( 'pay-cta', $a );     // phpcs:ignore
                ?>
            </div>
        </div>
    </div>
</main>
