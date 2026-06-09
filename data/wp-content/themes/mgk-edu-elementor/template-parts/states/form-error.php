<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$title = $args['title'] ?? 'Please review the form.';
$message = $args['message'] ?? 'Some fields need attention before we can continue.';
?>
<div class="mgk-state mgk-state-error" role="alert">
    <h2><?php echo esc_html( $title ); ?></h2>
    <p><?php echo esc_html( $message ); ?></p>
</div>
