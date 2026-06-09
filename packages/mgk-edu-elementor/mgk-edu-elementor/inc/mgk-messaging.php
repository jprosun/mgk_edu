<?php
/**
 * Parent messaging shell.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_msg_bool( $value ) {
    return $value === true || $value === 1 || $value === '1' || $value === 'yes' || $value === 'true';
}

function mgk_msg_shortcode_atts( $defaults, $atts ) {
    return shortcode_atts( array_merge( [ 'hidden' => '' ], $defaults ), $atts );
}

function mgk_get_parent_message_threads( $parent_id ) {
    return [
        [
            'id' => 'demo-ms-lee', 'title' => 'Ms Lee · Emma',
            'preview' => 'HOMEWORK PHOTO ATTACHED...', 'unread' => '+2',
            'active' => true, 'dark_avatar' => false,
        ],
        [
            'id' => 'demo-mr-wong', 'title' => 'Mr Wong · Ryan',
            'preview' => 'SEE YOU THURSDAY 7PM ✓✓', 'unread' => '',
            'active' => false, 'dark_avatar' => false,
        ],
        [
            'id' => 'demo-support', 'title' => 'Agency Support',
            'preview' => 'RE: RESCHEDULE POLICY...', 'unread' => '',
            'active' => false, 'dark_avatar' => true,
        ],
    ];
}

function mgk_get_active_message_thread( $thread_id = null ) {
    return [
        'id' => $thread_id ?: 'demo-ms-lee',
        'participant' => mgk_get_thread_participant( $thread_id ?: 'demo-ms-lee' ),
        'messages' => mgk_get_thread_messages( $thread_id ?: 'demo-ms-lee' ),
        'report_url' => mgk_get_report_thread_url( $thread_id ?: 'demo-ms-lee' ),
    ];
}

function mgk_get_thread_unread_count( $thread_id ) {
    return $thread_id === 'demo-ms-lee' ? 2 : 0;
}

function mgk_get_thread_participant( $thread_id ) {
    return [
        'name' => 'Ms Lee Yi L',
        'status' => '● ONLINE · RE: EMMA (P5 MATH)',
        'type' => 'tutor',
    ];
}

function mgk_get_tutor_online_status( $tutor_id ) {
    return 'online';
}

function mgk_get_report_thread_url( $thread_id ) {
    return add_query_arg( 'thread', sanitize_key( $thread_id ), mgk_url( '/parent/messages/report/' ) );
}

function mgk_get_thread_messages( $thread_id ) {
    return [
        [ 'id' => 'm1', 'kind' => 'incoming_text', 'time' => '9:02AM' ],
        [ 'id' => 'm2', 'kind' => 'photo', 'time' => '9:03AM · PHOTO', 'file' => 'homework_p42.jpg' ],
        [ 'id' => 'm3', 'kind' => 'lesson_ref', 'time' => '9:04AM', 'title' => 'LESSON REFERENCE', 'body' => '31 MAY · P5 MATH · FRACTIONS — VIEW LOG', 'url' => '#' ],
        [ 'id' => 'm4', 'kind' => 'outgoing_text', 'time' => '9:10AM · ✓✓ READ' ],
    ];
}

function mgk_get_message_attachments( $message_id ) {
    return $message_id === 'm2' ? [ [ 'type' => 'photo', 'name' => 'homework_p42.jpg' ] ] : [];
}

function mgk_get_lesson_reference_for_message( $message_id ) {
    return $message_id === 'm3' ? [ 'title' => '31 MAY · P5 MATH · FRACTIONS', 'url' => '#' ] : null;
}

function mgk_mark_thread_read( $thread_id, $parent_id ) {
    return true;
}

function mgk_mask_contact_details_in_message( $text ) {
    return preg_replace( '/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}|\+?\d[\d\s().-]{7,}\d)/i', '[hidden]', (string) $text );
}

function mgk_validate_message_payload( $payload ) {
    return is_array( $payload );
}

function mgk_create_message( $thread_id, $sender_id, $payload ) {
    return [ 'ok' => true ];
}

function mgk_user_can_view_thread( $user_id, $thread_id ) {
    return true;
}

function mgk_get_parent_messages_context() {
    $parent_id = get_current_user_id();
    $thread_id = isset( $_GET['thread'] ) ? sanitize_key( wp_unslash( $_GET['thread'] ) ) : 'demo-ms-lee';

    return [
        'parent_id' => $parent_id,
        'threads' => mgk_get_parent_message_threads( $parent_id ),
        'active_thread' => mgk_get_active_message_thread( $thread_id ),
        'compose' => mgk_get_message_compose_context( $thread_id ),
    ];
}

function mgk_get_message_compose_context( $thread_id ) {
    return [
        'photo' => [
            'name' => 'IMG_2231.jpg',
            'status' => 'AUTO-SCANNED · PDPA: STORED IN-PLATFORM ONLY',
        ],
        'lesson_refs' => mgk_get_available_lesson_references_for_thread( $thread_id ),
    ];
}

function mgk_get_available_lesson_references_for_thread( $thread_id ) {
    return [
        [ 'id' => 'lesson-31-may', 'label' => '31 May · Fractions ✓' ],
        [ 'id' => 'lesson-24-may', 'label' => '24 May · Decimals' ],
    ];
}

function mgk_get_thread_escalation_state( $thread_id ) {
    return [
        'title'          => 'This conversation has been escalated to the agency',
        'message'        => 'A flagged message (suspected off-platform contact / policy breach) was detected. Agency support is reviewing. Messaging is paused.',
        'disabled_label' => 'COMPOSER DISABLED · "MESSAGING PAUSED PENDING REVIEW"',
        'masked_example' => 'MASKED ATTEMPT EXAMPLE: "WHATSAPP ME 9••• ••••" → AUTO-REDACTED + FLAGGED',
        'note'           => 'AGENCY MONITORING + AUTO CONTACT-MASKING ENFORCE FR-SYS-03. ESCALATION CAN BLOCK/UNBLOCK A THREAD; BOTH PARTIES NOTIFIED.',
    ];
}

add_shortcode( 'mgk_parent_messages_page', function ( $atts ) {
    $atts = mgk_msg_shortcode_atts( [
        'utility'           => '[AGENCY LOGO] · Dashboard · Messages · SG/EN',
        'dashboard_url'     => '/parent/dashboard/',
        'messages_url'      => '/parent/messages/',
        'search_placeholder'=> '⌕ SEARCH MESSAGES',
        'monitor_label'     => 'Agency-monitored',
        'report_label'      => '⚠ Report',
        'date_label'        => '— TODAY —',
        'privacy_notice'    => '🔒 FOR YOUR SAFETY, PHONE NUMBERS & EMAILS ARE HIDDEN. "CALL ME AT xxxx" → (FR-SYS-03)',
        'lesson_chip'       => 'Lesson',
        'input_placeholder' => 'TYPE A MESSAGE...',
        'send_label'        => 'Send',
        'compose_heading'   => 'PHOTO + LESSON-REF COMPOSE',
        'compose_kicker'    => 'MESSAGE TYPES',
        'photo_heading'     => 'Attaching a photo',
        'preview_label'     => 'preview ·',
        'remove_label'      => '× Remove',
        'lesson_heading'    => 'Sharing a lesson reference',
        'pick_label'        => '📎 PICK A LESSON TO LINK',
        'compose_note'      => 'THREE MESSAGE TYPES: TEXT, PHOTO, LESSON-REFERENCE. READ RECEIPTS (✓ SENT / ✓✓ READ) ON EVERY MESSAGE.',
        'hide_compose_modal'=> '',
        'hide_utility'      => '',
        'hide_search'       => '',
        'hide_monitor'      => '',
        'hide_privacy'      => '',
    ], $atts );

    if ( mgk_msg_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/messages/message-page', [
        'atts' => $atts,
        'context' => mgk_get_parent_messages_context(),
    ] );
} );

add_shortcode( 'mgk_parent_messages_escalation', function ( $atts ) {
    $atts = mgk_msg_shortcode_atts( [
        'title'          => '',
        'message'        => '',
        'disabled_label' => '',
        'masked_example' => '',
        'note'           => '',
        'hide_title'     => '',
        'hide_message'   => '',
        'hide_disabled'  => '',
        'hide_example'   => '',
        'hide_note'      => '',
    ], $atts );

    if ( mgk_msg_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $thread_id = isset( $_GET['thread'] ) ? sanitize_key( wp_unslash( $_GET['thread'] ) ) : 'demo-ms-lee';
    return mgk_render_part( 'template-parts/sections/messages/escalation', [
        'atts' => $atts,
        'state' => mgk_get_thread_escalation_state( $thread_id ),
    ] );
} );

add_shortcode( 'mgk_parent_messages_empty', function ( $atts ) {
    $atts = mgk_msg_shortcode_atts( [
        'heading'            => 'EMPTY · NO MESSAGES',
        'kicker'             => 'FIRST-USE',
        'illustration_label' => '☏',
        'title'              => 'No messages yet',
        'message'            => 'ONCE YOU BOOK A TUTOR YOU CAN MESSAGE THEM HERE. ALL CHATS ARE AGENCY-MONITORED & SECURE.',
        'button'             => 'Find a Tutor → S02',
        'button_url'         => '/student/teachers/',
        'note'               => 'EMPTY THREAD LIST + EMPTY CONVERSATION PANE SHARE ONE CTA. NO COMPOSER UNTIL A THREAD EXISTS.',
        'hide_heading'       => '',
        'hide_kicker'        => '',
        'hide_illustration'  => '',
        'hide_title'         => '',
        'hide_message'       => '',
        'hide_button'        => '',
        'hide_note'          => '',
    ], $atts );

    if ( mgk_msg_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/messages/empty', [ 'atts' => $atts ] );
} );
