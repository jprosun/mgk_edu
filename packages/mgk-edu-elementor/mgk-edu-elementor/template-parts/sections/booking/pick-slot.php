<?php
/**
 * S10 — Pick Slot composite page partial.
 *
 * Resolves the locked pick-slot view and renders nav → progress(2) → hold
 * banner → live calendar → available times → confirm row, OR a safe state.
 * All DATA from mgk_get_pick_slot_view(); $args carries SAFE copy overrides.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$view = function_exists( 'mgk_get_pick_slot_view' ) ? mgk_get_pick_slot_view() : [ 'status' => 'not_found' ];

$part = function ( $slug, $extra = [] ) use ( $view, $a ) {
    return mgk_render_part( 'template-parts/sections/booking/' . $slug, array_merge( $view, (array) $extra ) );
};

// Nav (centre label = booking-with-tutor).
$tutor_name = $view['tutor']['name'] ?? '';
$with = 'BOOKING TRIAL';
if ( preg_match( '/\b(Ms|Mr|Mrs|Dr)\.?\s+([A-Z][a-z]+)/', (string) $tutor_name, $m ) ) {
    $with = 'BOOKING TRIAL WITH ' . strtoupper( $m[1] . ' ' . $m[2] );
}
echo $part( 'nav', [ 'secure_label' => '🔒 ' . $with ] ); // phpcs:ignore

// ── Error / non-ok states ───────────────────────────────────
if ( ( $view['status'] ?? '' ) !== 'ok' ) {
    $ctx = (array) ( $view['context'] ?? [] );
    $back = function_exists( 'mgk_get_back_to_proposals_url' )
        ? mgk_get_back_to_proposals_url( $ctx['lead_token'] ?? '' ) : home_url( '/tutor-proposals/' );
    $states = [
        'not_found'   => [ 'We couldn’t find this booking session.', 'Back to matched tutors', $back ],
        'expired'     => [ 'This proposal has expired. Request fresh matches.', 'Request fresh matches', home_url( '/tutor-proposals/' ) ],
        'unavailable' => [ 'This tutor is no longer available. Please choose another tutor.', 'Back to proposals', $back ],
    ];
    $s = $states[ $view['status'] ] ?? $states['not_found'];
    ?>
    <main class="mgk-bk-main mgk-bk-main--state">
        <div class="mgk-shell">
            <div class="mgk-bk-state" role="alert" data-event="slot_unavailable_error">
                <p class="mgk-bk-state-msg"><?php echo esc_html( $s[0] ); ?></p>
                <a class="mgk-bk-continue" href="<?php echo esc_url( $s[2] ); ?>"><?php echo esc_html( $s[1] ); ?></a>
            </div>
        </div>
    </main>
    <?php
    return;
}

// ── OK state ────────────────────────────────────────────────
echo $part( 'booking-progress', [ 'current' => 2 ] ); // phpcs:ignore
echo $part( 'slot-hold-banner', $a );                  // phpcs:ignore
?>
<main class="mgk-bk-main mgk-bk-main--slot">
    <div class="mgk-shell">
        <?php
        echo $part( 'live-calendar', $a );    // phpcs:ignore
        echo $part( 'available-times', $a );  // phpcs:ignore
        echo $part( 'selected-slot-confirm', $a ); // phpcs:ignore
        ?>
    </div>
</main>
