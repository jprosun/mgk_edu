<?php
/**
 * S12 — Booking Confirmation composite page partial.
 *
 * Resolves the locked confirmation view and renders the authenticated nav →
 * success hero → 2-col grid (left: booking summary + first lesson; right: tutor
 * contact + next steps + manage) → modals, OR a safe state. All DATA from
 * mgk_get_confirmation_view(); $args carries SAFE copy overrides.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a    = (array) ( $args ?? [] );
$view = function_exists( 'mgk_get_confirmation_view' ) ? mgk_get_confirmation_view() : [ 'status' => 'not_found' ];

$part = function ( $slug, $extra = [] ) use ( $view, $a ) {
    return mgk_render_part( 'template-parts/sections/booking/' . $slug, array_merge( $view, (array) $extra ) );
};
$urls = (array) ( $view['urls'] ?? [] );

// ── Authenticated nav (My Bookings / Messages / Account / Dashboard →) ──
?>
<header class="mgk-cf-nav" data-event="confirm_nav_view">
    <div class="mgk-shell mgk-cf-nav__inner">
        <a class="mgk-cf-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">[LOGO]</a>
        <nav class="mgk-cf-navlinks" aria-label="Account navigation">
            <a href="<?php echo esc_url( $urls['bookings'] ?? home_url( '/parent/bookings/' ) ); ?>">My Bookings</a>
            <a href="<?php echo esc_url( $urls['message'] ?? home_url( '/messages/' ) ); ?>">Messages</a>
            <a href="<?php echo esc_url( $urls['account'] ?? home_url( '/parent/account/' ) ); ?>">Account</a>
        </nav>
        <a class="mgk-cf-dashboard" href="<?php echo esc_url( $urls['dashboard'] ?? home_url( '/parent/dashboard/' ) ); ?>"
           data-event="dashboard_click">Dashboard ↘</a>
    </div>
</header>
<?php

// ── Non-success / error states ──────────────────────────────
$status = $view['status'] ?? 'not_found';
if ( $status !== 'paid' ) {
    $dash = $urls['dashboard'] ?? home_url( '/parent/dashboard/' );
    $pay  = function_exists( 'mgk_get_s11_pay_url' ) ? mgk_get_s11_pay_url( $view['context'] ?? [] ) : home_url( '/trial-pay/' );
    $states = [
        'not_found' => [ 'We couldn’t find this booking.', 'Go to dashboard', $dash ],
        'pending'   => [ 'Payment is still being verified.', 'Refresh status', '' ],
        'failed'    => [ 'Payment failed or was cancelled.', 'Try payment again', $pay ],
    ];
    $s = $states[ $status ] ?? $states['not_found'];
    $href = $s[2] !== '' ? $s[2] : add_query_arg( [] ); // refresh = self
    ?>
    <main class="mgk-cf-main mgk-cf-main--state">
        <div class="mgk-shell">
            <div class="mgk-cf-state mgk-cf-state--<?php echo esc_attr( $status ); ?>" role="alert" data-event="confirm_state_view" data-state="<?php echo esc_attr( $status ); ?>">
                <p class="mgk-cf-state-msg"><?php echo esc_html( $s[0] ); ?></p>
                <a class="mgk-cf-state-cta" href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $s[1] ); ?></a>
            </div>
        </div>
    </main>
    <?php
    return;
}

// ── Success state ───────────────────────────────────────────
echo $part( 'success-hero', $a ); // phpcs:ignore
?>
<main class="mgk-cf-main" data-event="trial_booking_success_view">
    <div class="mgk-shell">
        <div class="mgk-cf-grid">
            <div class="mgk-cf-col mgk-cf-col--left">
                <?php
                echo $part( 'booking-summary', $a ); // phpcs:ignore
                echo $part( 'first-lesson', $a );     // phpcs:ignore
                ?>
            </div>
            <div class="mgk-cf-col mgk-cf-col--right">
                <?php
                echo $part( 'tutor-contact', $a ); // phpcs:ignore
                echo $part( 'next-steps', $a );     // phpcs:ignore
                echo $part( 'manage-booking', $a ); // phpcs:ignore
                ?>
            </div>
        </div>
    </div>
</main>
