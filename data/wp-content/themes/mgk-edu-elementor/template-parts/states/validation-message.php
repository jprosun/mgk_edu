<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$message = $args['message'] ?? 'Please check the highlighted fields.';
?>
<p class="mgk-form-message" data-mgk-form-message><?php echo esc_html( $message ); ?></p>
