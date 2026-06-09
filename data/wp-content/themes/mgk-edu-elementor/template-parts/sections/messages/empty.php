<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
?>
<section class="mgk-parent-messages-empty" data-event="messages_empty_view">
    <div class="mgk-parent-messages-empty__top">
        <?php if ( ! mgk_msg_bool( $atts['hide_heading'] ?? '' ) ) : ?>
            <h1><?php echo esc_html( $atts['heading'] ?? 'EMPTY · NO MESSAGES' ); ?></h1>
        <?php endif; ?>
        <?php if ( ! mgk_msg_bool( $atts['hide_kicker'] ?? '' ) ) : ?>
            <span><?php echo esc_html( $atts['kicker'] ?? 'FIRST-USE' ); ?></span>
        <?php endif; ?>
    </div>
    <div class="mgk-parent-messages-empty__shell">
        <div class="mgk-parent-messages-empty__body">
            <?php if ( ! mgk_msg_bool( $atts['hide_illustration'] ?? '' ) ) : ?>
                <div class="mgk-parent-messages-empty__illustration">
                    <span><?php echo esc_html( $atts['illustration_label'] ?? '☏' ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( ! mgk_msg_bool( $atts['hide_title'] ?? '' ) ) : ?>
                <strong><?php echo esc_html( $atts['title'] ?? 'No messages yet' ); ?></strong>
            <?php endif; ?>
            <?php if ( ! mgk_msg_bool( $atts['hide_message'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $atts['message'] ?? '' ); ?></p>
            <?php endif; ?>
            <?php if ( ! mgk_msg_bool( $atts['hide_button'] ?? '' ) ) : ?>
                <a href="<?php echo esc_url( mgk_url( $atts['button_url'] ?? '/student/teachers/' ) ); ?>"><?php echo esc_html( $atts['button'] ?? 'Find a Tutor → S02' ); ?></a>
            <?php endif; ?>
        </div>
        <?php if ( ! mgk_msg_bool( $atts['hide_note'] ?? '' ) ) : ?>
            <footer><?php echo esc_html( $atts['note'] ?? '' ); ?></footer>
        <?php endif; ?>
    </div>
</section>
