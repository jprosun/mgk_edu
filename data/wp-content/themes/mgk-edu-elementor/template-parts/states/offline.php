<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$title   = $args['title']   ?? 'You appear to be offline.';
$message = $args['message'] ?? 'Check your connection and try again. Your progress has been saved.';
?>
<div class="mgk-state mgk-state-offline" role="alert">
    <h2><?php echo esc_html( $title ); ?></h2>
    <p><?php echo esc_html( $message ); ?></p>
    <button class="mgk-btn mgk-btn-outline" onclick="window.location.reload()">Try again</button>
</div>
