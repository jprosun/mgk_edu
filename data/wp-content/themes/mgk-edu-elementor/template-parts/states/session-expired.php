<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$title   = $args['title']   ?? 'Your session has expired.';
$message = $args['message'] ?? 'For your security, we signed you out after a period of inactivity.';
$login_url = $args['login_url'] ?? mgk_url( '/login/' );
?>
<div class="mgk-state mgk-state-session-expired" role="alert">
    <h2><?php echo esc_html( $title ); ?></h2>
    <p><?php echo esc_html( $message ); ?></p>
    <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( $login_url ); ?>">Sign in again</a>
</div>
