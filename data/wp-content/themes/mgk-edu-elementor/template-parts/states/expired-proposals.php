<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$button = $args['button'] ?? 'Request fresh matches';
?>
<div class="mgk-proposal-expired" data-event="proposal_expired" data-mgk-event="proposal_expired">
    <div class="mgk-proposal-shell">
        <strong><?php esc_html_e( 'These proposals have expired. Request a fresh set.', 'mgk-edu' ); ?></strong>
        <button type="button" data-event="rematch_request_click" data-mgk-event="rematch_request_click"><?php echo esc_html( $button ); ?></button>
    </div>
</div>
