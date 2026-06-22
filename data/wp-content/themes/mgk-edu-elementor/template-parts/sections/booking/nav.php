<?php
/**
 * S09 — Booking nav. Presentation only; SAFE copy via $args. Security/session
 * logic is not exposed here.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$args = (array) ( $args ?? [] );

// Default secure label, derived from the shared payable-item descriptor so the
// label strings live in ONE place (inc/mgk-pay.php). An explicit `secure_label`
// arg from the pay composite still wins via wp_parse_args below.
$tutor_name = $args['tutor']['name'] ?? '';
$is_package = ! empty( $args['is_package_order'] ) || ( ( $args['item_kind'] ?? '' ) === 'package' );
// Fallback: resolve the tutor directly from the request when the injected view
// didn't carry it (e.g. this nav widget rendered before the page view).
if ( ! $tutor_name && function_exists( 'mgk_get_selected_tutor_for_booking' ) && function_exists( 'mgk_get_query_filter' ) ) {
    $t = mgk_get_selected_tutor_for_booking( mgk_get_query_filter( 'lead', '' ), sanitize_title( mgk_get_query_filter( 'tutor', '' ) ) );
    $tutor_name = $t['name'] ?? '';
}
$default_secure = function_exists( 'mgk_pay_secure_label' )
    ? mgk_pay_secure_label( $is_package ? 'PACKAGE_8' : 'TRIAL', $tutor_name )
    : '🔒 SECURE TRIAL BOOKING';

$a = wp_parse_args( $args, [
    'utility'      => 'Secure booking · SG/EN',
    'logo_label'   => '[LOGO]',
    'secure_label' => $default_secure,
    'signin_label' => 'Sign In',
    'hide_secure'  => '',
] );
?>
<header class="mgk-bk-nav" data-event="nav_view">
    <div class="mgk-bk-utility">
        <div class="mgk-shell mgk-bk-utility__inner">
            <span><?php echo esc_html( $a['utility'] ); ?></span>
        </div>
    </div>
    <nav class="mgk-bk-mainnav" aria-label="Booking navigation">
        <div class="mgk-shell mgk-bk-mainnav__inner">
            <a class="mgk-bk-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-event="nav_click"><?php echo esc_html( $a['logo_label'] ); ?></a>
            <?php if ( $a['hide_secure'] !== 'yes' ) : ?>
            <span class="mgk-bk-secure"><?php echo esc_html( $a['secure_label'] ); ?></span>
            <?php endif; ?>
            <a class="mgk-bk-signin" href="<?php echo esc_url( home_url( '/login/' ) ); ?>" data-event="nav_click"><?php echo esc_html( $a['signin_label'] ); ?></a>
        </div>
    </nav>
</header>
