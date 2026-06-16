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

/** Real threads for a parent: one per (tutor, child) from enrolments + bookings. */
function mgk_get_parent_message_threads( $parent_id ) {
    $parent_id = (int) $parent_id;
    if ( ! $parent_id || ! function_exists( 'mgk_thread_key_for' ) ) {
        // Preview only (Elementor editor / logged out).
        if ( function_exists( 'mgk_dashboard_is_preview' ) && mgk_dashboard_is_preview() ) {
            return [ [ 'id' => 'demo-ms-lee', 'title' => 'Ms Lee · Emma', 'preview' => 'HOMEWORK PHOTO ATTACHED...', 'unread' => '+2', 'active' => true, 'dark_avatar' => false ] ];
        }
        return [];
    }

    $pairs = []; // thread_key => [tutor, child]
    if ( post_type_exists( 'mg_enrolment' ) ) {
        foreach ( get_posts( [ 'post_type' => 'mg_enrolment', 'numberposts' => 50, 'fields' => 'ids', 'meta_query' => [ [ 'key' => 'mgk_enr_parent_user_id', 'value' => $parent_id ] ] ] ) as $eid ) {
            $tid = (int) get_post_meta( $eid, 'mgk_enr_tutor_id', true );
            $cid = (int) get_post_meta( $eid, 'mgk_enr_child_id', true );
            $k   = $tid && $cid ? mgk_thread_key_for( $cid, $tid ) : '';
            if ( $k ) $pairs[ $k ] = [ 'tutor' => $tid, 'child' => $cid ];
        }
    }
    if ( function_exists( 'mgk_booking_table' ) && function_exists( 'mgk_parent_children' ) ) {
        global $wpdb;
        $bt   = mgk_booking_table( 'bookings' );
        $kids = mgk_parent_children( $parent_id );
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT tutor_post_id, student_name FROM {$bt} WHERE parent_user_id = %d AND status IN ('CONFIRMED','COMPLETED')", $parent_id ), ARRAY_A );
        foreach ( (array) $rows as $r ) {
            $tid = (int) $r['tutor_post_id']; $cid = 0;
            foreach ( $kids as $c ) { if ( ( get_post_meta( $c->ID, 'mgk_child_full_name', true ) ?: get_the_title( $c ) ) === $r['student_name'] ) { $cid = (int) $c->ID; break; } }
            $k = $tid && $cid ? mgk_thread_key_for( $cid, $tid ) : '';
            if ( $k && ! isset( $pairs[ $k ] ) ) $pairs[ $k ] = [ 'tutor' => $tid, 'child' => $cid ];
        }
    }

    $active = isset( $_GET['thread'] ) ? sanitize_text_field( wp_unslash( $_GET['thread'] ) ) : '';
    $out = []; $first = true;
    foreach ( $pairs as $key => $p ) {
        $tutor  = get_post( $p['tutor'] );
        $cname  = get_post_meta( $p['child'], 'mgk_child_full_name', true ) ?: get_the_title( $p['child'] );
        $last   = function_exists( 'mgk_msg_last' ) ? mgk_msg_last( $key ) : null;
        $unread = function_exists( 'mgk_msg_unread' ) ? mgk_msg_unread( $key ) : 0;
        $out[] = [
            'id'          => $key,
            'title'       => ( $tutor ? $tutor->post_title : 'Tutor' ) . ' · ' . $cname,
            'preview'     => $last ? wp_trim_words( (string) $last['body'], 9, '…' ) : 'No messages yet',
            'unread'      => $unread ? ( '+' . $unread ) : '',
            'active'      => $active ? ( $active === $key ) : $first,
            'dark_avatar' => false,
        ];
        $first = false;
    }
    return $out;
}

function mgk_get_active_message_thread( $thread_id = null ) {
    $thread_id = (string) ( $thread_id ?: '' );
    return [
        'id'          => $thread_id,
        'participant' => mgk_get_thread_participant( $thread_id ),
        'messages'    => mgk_get_thread_messages( $thread_id ),
        'report_url'  => mgk_get_report_thread_url( $thread_id ),
    ];
}

function mgk_get_thread_unread_count( $thread_id ) {
    return function_exists( 'mgk_msg_unread' ) ? mgk_msg_unread( $thread_id ) : 0;
}

function mgk_get_thread_participant( $thread_id ) {
    $p = function_exists( 'mgk_thread_parse' ) ? mgk_thread_parse( $thread_id ) : null;
    if ( ! $p ) {
        return [ 'name' => 'Ms Lee Yi L', 'status' => '● ONLINE · RE: EMMA (P5 MATH)', 'type' => 'tutor' ];
    }
    $tutor = get_post( $p['tutor'] );
    $cname = get_post_meta( $p['child'], 'mgk_child_full_name', true ) ?: get_the_title( $p['child'] );
    $subj  = '';
    if ( function_exists( 'mgk_enrolment_for_child' ) ) {
        $enr = mgk_enrolment_for_child( $p['child'] );
        if ( $enr ) $subj = (string) get_post_meta( $enr, 'mgk_enr_subject', true );
    }
    return [
        'name'   => $tutor ? $tutor->post_title : 'Tutor',
        'status' => 'RE: ' . strtoupper( $cname . ( $subj ? ' (' . $subj . ')' : '' ) ),
        'type'   => 'tutor',
    ];
}

function mgk_get_tutor_online_status( $tutor_id ) {
    return 'online';
}

function mgk_get_report_thread_url( $thread_id ) {
    return add_query_arg( 'thread', sanitize_key( $thread_id ), mgk_url( '/parent/messages/report/' ) );
}

function mgk_get_thread_messages( $thread_id ) {
    if ( ! function_exists( 'mgk_msg_rows' ) || ! mgk_thread_parse( $thread_id ) ) {
        return []; // no real thread → empty conversation
    }
    $out = [];
    foreach ( mgk_msg_rows( $thread_id ) as $r ) {
        $incoming = in_array( $r['sender_role'], [ 'TUTOR', 'AGENCY' ], true );
        $time = $r['created_at_utc'] ? gmdate( 'g:iA', strtotime( $r['created_at_utc'] . ' UTC' ) ) : '';
        if ( ! $incoming && ! empty( $r['read_by_parent_at'] ) ) { $time .= ' · ✓✓ READ'; }
        $kind = $incoming ? 'incoming_text' : 'outgoing_text';
        if ( ! empty( $r['attachment'] ) ) { $kind = 'photo'; }
        $out[] = [
            'id'      => (int) $r['id'],
            'kind'    => $kind,
            'body'    => (string) $r['body'],
            'time'    => $time,
            'file'    => (string) $r['attachment'],
            'flagged' => (int) $r['flagged'],
        ];
    }
    return $out;
}

function mgk_get_message_attachments( $message_id ) {
    return $message_id === 'm2' ? [ [ 'type' => 'photo', 'name' => 'homework_p42.jpg' ] ] : [];
}

function mgk_get_lesson_reference_for_message( $message_id ) {
    return $message_id === 'm3' ? [ 'title' => '31 MAY · P5 MATH · FRACTIONS', 'url' => '#' ] : null;
}

function mgk_mark_thread_read( $thread_id, $parent_id ) {
    return function_exists( 'mgk_msg_mark_read' ) ? mgk_msg_mark_read( $thread_id, $parent_id ) : false;
}

function mgk_mask_contact_details_in_message( $text ) {
    return preg_replace( '/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}|\+?\d[\d\s().-]{7,}\d)/i', '[hidden]', (string) $text );
}

function mgk_validate_message_payload( $payload ) {
    return is_array( $payload );
}

function mgk_create_message( $thread_id, $sender_id, $payload ) {
    if ( ! function_exists( 'mgk_msg_insert' ) ) return [ 'ok' => false ];
    $id = mgk_msg_insert( [
        'thread_key'    => $thread_id,
        'sender_role'   => strtoupper( $payload['role'] ?? 'PARENT' ),
        'sender_user_id'=> (int) $sender_id,
        'body'          => $payload['body'] ?? '',
        'attachment'    => $payload['attachment'] ?? '',
        'lesson_ref_id' => (int) ( $payload['lesson_ref_id'] ?? 0 ),
    ] );
    return [ 'ok' => (bool) $id, 'id' => $id ];
}

function mgk_user_can_view_thread( $user_id, $thread_id ) {
    $p = function_exists( 'mgk_thread_parse' ) ? mgk_thread_parse( $thread_id ) : null;
    if ( ! $p ) return false;
    if ( (int) $p['parent'] === (int) $user_id ) return true;
    return user_can( (int) $user_id, 'manage_options' ); // agency admin monitoring
}

function mgk_get_parent_messages_context() {
    $parent_id = get_current_user_id();
    $threads   = mgk_get_parent_message_threads( $parent_id );
    $thread_id = isset( $_GET['thread'] ) ? sanitize_text_field( wp_unslash( $_GET['thread'] ) ) : ( $threads ? (string) $threads[0]['id'] : '' );

    // Viewing a thread marks its incoming messages read (NFR: live unread).
    if ( $thread_id && $parent_id && mgk_user_can_view_thread( $parent_id, $thread_id ) ) {
        mgk_mark_thread_read( $thread_id, $parent_id );
    }

    return [
        'parent_id'     => $parent_id,
        'threads'       => $threads,
        'active_thread' => mgk_get_active_message_thread( $thread_id ),
        'compose'       => mgk_get_message_compose_context( $thread_id ),
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
