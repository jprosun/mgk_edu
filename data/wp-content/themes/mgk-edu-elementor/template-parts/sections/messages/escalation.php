<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$state = $args['state'] ?? [];
$title = $atts['title'] ?: ( $state['title'] ?? '' );
$message = $atts['message'] ?: ( $state['message'] ?? '' );
$disabled = $atts['disabled_label'] ?: ( $state['disabled_label'] ?? '' );
$example = $atts['masked_example'] ?: ( $state['masked_example'] ?? '' );
$note = $atts['note'] ?: ( $state['note'] ?? '' );
?>
<section class="mgk-parent-message-escalation" data-event="thread_escalation_view">
    <div class="mgk-parent-message-escalation__shell">
        <div class="mgk-parent-message-escalation__alert">
            <?php if ( ! mgk_msg_bool( $atts['hide_title'] ?? '' ) ) : ?>
                <strong>△ <?php echo esc_html( $title ); ?></strong>
            <?php endif; ?>
            <?php if ( ! mgk_msg_bool( $atts['hide_message'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $message ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( ! mgk_msg_bool( $atts['hide_disabled'] ?? '' ) ) : ?>
            <div class="mgk-parent-message-escalation__disabled"><?php echo esc_html( $disabled ); ?></div>
        <?php endif; ?>
        <?php if ( ! mgk_msg_bool( $atts['hide_example'] ?? '' ) ) : ?>
            <div class="mgk-parent-message-escalation__example"><?php echo esc_html( $example ); ?></div>
        <?php endif; ?>
        <?php if ( ! mgk_msg_bool( $atts['hide_note'] ?? '' ) ) : ?>
            <footer class="mgk-parent-message-escalation__note"><?php echo esc_html( $note ); ?></footer>
        <?php endif; ?>
    </div>
</section>
