<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$title   = $args['title']   ?? 'Access denied.';
$message = $args['message'] ?? 'You do not have permission to view this page. Contact your agency admin if you think this is a mistake.';
$back_url = $args['back_url'] ?? mgk_url( '/' );
?>
<div class="mgk-state mgk-state-permission-denied" role="alert">
    <h2><?php echo esc_html( $title ); ?></h2>
    <p><?php echo esc_html( $message ); ?></p>
    <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( $back_url ); ?>">Go home</a>
</div>
