<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$title = $args['title'] ?? 'Something went wrong.';
$message = $args['message'] ?? 'Please try again, or contact support if the issue continues.';
?>
<div class="mgk-state mgk-state-error" role="alert">
    <h2><?php echo esc_html( $title ); ?></h2>
    <p><?php echo esc_html( $message ); ?></p>
</div>
