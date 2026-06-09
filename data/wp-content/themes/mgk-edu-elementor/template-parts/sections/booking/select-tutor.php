<?php
/**
 * S09 — Select Tutor composite page partial.
 *
 * Resolves the locked booking view and renders nav → progress → 2-column
 * content → CTA, OR a safe state (not_found / expired / unavailable). All DATA
 * comes from mgk_get_select_tutor_view(); $args carries SAFE copy overrides.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$view = function_exists( 'mgk_get_select_tutor_view' ) ? mgk_get_select_tutor_view() : [ 'status' => 'not_found' ];

$part = function ( $slug, $extra = [] ) use ( $view, $a ) {
    $data = array_merge( $view, (array) $extra );
    return mgk_render_part( 'template-parts/sections/booking/' . $slug, $data );
};

echo $part( 'nav', $a ); // phpcs:ignore

// ── Error / non-ok states ───────────────────────────────────
if ( ( $view['status'] ?? '' ) !== 'ok' ) {
    $ctx = (array) ( $view['context'] ?? [] );
    $back_proposals = function_exists( 'mgk_get_back_to_proposals_url' )
        ? mgk_get_back_to_proposals_url( $ctx['lead_token'] ?? '' ) : home_url( '/tutor-proposals/' );
    $states = [
        'not_found'   => [ 'We couldn’t find this booking session.', 'Back to matched tutors', $back_proposals, 'booking_context_error' ],
        'expired'     => [ 'This proposal has expired. Request fresh matches.', 'Request fresh matches', home_url( '/tutor-proposals/' ), 'booking_context_error' ],
        'unavailable' => [ 'This tutor is no longer available. Please choose another tutor.', 'Back to proposals', $back_proposals, 'booking_context_error' ],
    ];
    $s = $states[ $view['status'] ] ?? $states['not_found'];
    ?>
    <main class="mgk-bk-main mgk-bk-main--state">
        <div class="mgk-shell">
            <div class="mgk-bk-state" role="alert" data-event="<?php echo esc_attr( $s[3] ); ?>">
                <p class="mgk-bk-state-msg"><?php echo esc_html( $s[0] ); ?></p>
                <a class="mgk-bk-continue" href="<?php echo esc_url( $s[2] ); ?>"><?php echo esc_html( $s[1] ); ?></a>
            </div>
        </div>
    </main>
    <?php
    return;
}

// ── OK state ────────────────────────────────────────────────
?>
<?php echo $part( 'booking-progress', [ 'current' => 1 ] ); // phpcs:ignore ?>
<main class="mgk-bk-main">
    <div class="mgk-shell mgk-bk-grid">
        <div class="mgk-bk-col-left">
            <?php
            echo $part( 'chosen-tutor-card', $a ); // phpcs:ignore
            echo $part( 'trial-included', $a );     // phpcs:ignore
            ?>
        </div>
        <aside class="mgk-bk-col-right">
            <?php
            echo $part( 'trial-offer', $a );    // phpcs:ignore
            echo $part( 'price-breakdown', $a ); // phpcs:ignore
            ?>
        </aside>
    </div>
    <div class="mgk-shell">
        <?php echo $part( 'booking-cta', $a ); // phpcs:ignore ?>
    </div>
</main>
