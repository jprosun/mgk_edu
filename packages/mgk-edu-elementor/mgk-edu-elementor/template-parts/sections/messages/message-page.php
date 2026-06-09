<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$threads = $ctx['threads'] ?? [];
$thread = $ctx['active_thread'] ?? [];
$participant = $thread['participant'] ?? [];
$messages = $thread['messages'] ?? [];
?>
<section class="mgk-parent-messages" data-event="messages_page_view">
    <div class="mgk-parent-messages__shell">
        <?php if ( ! mgk_msg_bool( $atts['hide_utility'] ?? '' ) ) : ?>
            <div class="mgk-parent-messages-utility">
                <a href="<?php echo esc_url( mgk_url( $atts['dashboard_url'] ?? '/parent/dashboard/' ) ); ?>">[AGENCY LOGO]</a>
                <span>·</span>
                <a href="<?php echo esc_url( mgk_url( $atts['dashboard_url'] ?? '/parent/dashboard/' ) ); ?>">Dashboard</a>
                <span>·</span>
                <a href="<?php echo esc_url( mgk_url( $atts['messages_url'] ?? '/parent/messages/' ) ); ?>">Messages</a>
                <span>· SG/EN</span>
            </div>
        <?php endif; ?>
        <div class="mgk-parent-messages-app">
            <aside class="mgk-parent-messages-sidebar">
                <?php if ( ! mgk_msg_bool( $atts['hide_search'] ?? '' ) ) : ?>
                    <label class="mgk-parent-messages-search">
                        <span class="screen-reader-text">Search messages</span>
                        <input type="search" placeholder="<?php echo esc_attr( $atts['search_placeholder'] ?? '⌕ SEARCH MESSAGES' ); ?>" data-event="message_search">
                    </label>
                <?php endif; ?>
                <div class="mgk-parent-messages-thread-list">
                    <?php foreach ( $threads as $item ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'thread', $item['id'] ?? '', mgk_url( '/parent/messages/' ) ) ); ?>" class="mgk-parent-messages-thread <?php echo ! empty( $item['active'] ) ? 'is-active' : ''; ?>" data-event="thread_select">
                            <i class="<?php echo ! empty( $item['dark_avatar'] ) ? 'is-dark' : ''; ?>" aria-hidden="true"></i>
                            <span>
                                <strong><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
                                <em><?php echo esc_html( $item['preview'] ?? '' ); ?></em>
                            </span>
                            <?php if ( ! empty( $item['unread'] ) ) : ?>
                                <b><?php echo esc_html( $item['unread'] ); ?></b>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            <main class="mgk-parent-messages-main">
                <header class="mgk-parent-messages-head">
                    <i aria-hidden="true"></i>
                    <div>
                        <h1><?php echo esc_html( $participant['name'] ?? 'Ms Lee Yi L' ); ?></h1>
                        <p><?php echo esc_html( $participant['status'] ?? '● ONLINE · RE: EMMA (P5 MATH)' ); ?></p>
                    </div>
                    <?php if ( ! mgk_msg_bool( $atts['hide_monitor'] ?? '' ) ) : ?>
                        <div class="mgk-parent-messages-monitor">
                            <span><?php echo esc_html( $atts['monitor_label'] ?? 'Agency-monitored' ); ?></span>
                            <a href="<?php echo esc_url( $thread['report_url'] ?? '#' ); ?>" data-event="report_thread_click"><?php echo esc_html( $atts['report_label'] ?? '⚠ Report' ); ?></a>
                        </div>
                    <?php endif; ?>
                </header>
                <div class="mgk-parent-messages-stream">
                    <div class="mgk-parent-messages-date"><?php echo esc_html( $atts['date_label'] ?? '— TODAY —' ); ?></div>
                    <?php foreach ( $messages as $message ) : ?>
                        <?php if ( $message['kind'] === 'incoming_text' ) : ?>
                            <div class="mgk-parent-message mgk-parent-message--in">
                                <i aria-hidden="true"></i>
                                <div>
                                    <div class="mgk-parent-message-bubble mgk-parent-message-bubble--text"><span></span><span></span></div>
                                    <time><?php echo esc_html( $message['time'] ?? '' ); ?></time>
                                </div>
                            </div>
                        <?php elseif ( $message['kind'] === 'photo' ) : ?>
                            <div class="mgk-parent-message mgk-parent-message--in">
                                <i aria-hidden="true"></i>
                                <div>
                                    <a href="#" class="mgk-parent-message-photo" data-event="message_attachment_click">
                                        <span>📷 <?php echo esc_html( $message['file'] ?? 'homework_p42.jpg' ); ?></span>
                                    </a>
                                    <time><?php echo esc_html( $message['time'] ?? '' ); ?></time>
                                </div>
                            </div>
                        <?php elseif ( $message['kind'] === 'lesson_ref' ) : ?>
                            <div class="mgk-parent-message mgk-parent-message--in">
                                <i aria-hidden="true"></i>
                                <div>
                                    <a href="<?php echo esc_url( $message['url'] ?? '#' ); ?>" class="mgk-parent-message-lesson" data-event="lesson_reference_click">
                                        <strong>📎 <?php echo esc_html( $message['title'] ?? 'LESSON REFERENCE' ); ?></strong>
                                        <span><?php echo esc_html( $message['body'] ?? '' ); ?></span>
                                    </a>
                                    <time><?php echo esc_html( $message['time'] ?? '' ); ?></time>
                                </div>
                            </div>
                        <?php elseif ( $message['kind'] === 'outgoing_text' ) : ?>
                            <div class="mgk-parent-message mgk-parent-message--out">
                                <div>
                                    <div class="mgk-parent-message-bubble mgk-parent-message-bubble--out"><span></span><span></span></div>
                                    <time><?php echo esc_html( $message['time'] ?? '' ); ?></time>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php if ( ! mgk_msg_bool( $atts['hide_privacy'] ?? '' ) ) : ?>
                    <div class="mgk-parent-messages-privacy" data-event="contact_mask_notice_view">
                        <?php echo esc_html( $atts['privacy_notice'] ?? '' ); ?>
                    </div>
                <?php endif; ?>
                <form class="mgk-parent-messages-composer" data-mgk-message-composer>
                    <button type="button" class="mgk-parent-messages-attach" data-event="message_attachment_click" data-mgk-message-compose-open>🖼</button>
                    <button type="button" class="mgk-parent-messages-lesson-chip" data-mgk-message-compose-open>📎 <?php echo esc_html( $atts['lesson_chip'] ?? 'Lesson' ); ?></button>
                    <label>
                        <span class="screen-reader-text">Message</span>
                        <textarea rows="1" placeholder="<?php echo esc_attr( $atts['input_placeholder'] ?? 'TYPE A MESSAGE...' ); ?>"></textarea>
                    </label>
                    <button type="submit" class="mgk-parent-messages-send" data-event="message_send"><?php echo esc_html( $atts['send_label'] ?? 'Send' ); ?></button>
                </form>
            </main>
        </div>
    </div>
    <?php if ( ! mgk_msg_bool( $atts['hide_compose_modal'] ?? '' ) ) : ?>
        <?php get_template_part( 'template-parts/sections/messages/compose-modal', null, [ 'atts' => $atts, 'context' => $ctx ] ); ?>
    <?php endif; ?>
</section>
