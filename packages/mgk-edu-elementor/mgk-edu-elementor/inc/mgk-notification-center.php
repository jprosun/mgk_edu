<?php
/**
 * S27 notification center shell.
 *
 * Event/channel preferences, opt-in consent and quiet-hours rules are DATA CORE.
 * Elementor edits shell labels, helper copy, preview state and style only.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_notification_center_url() {
    return mgk_url( '/parent/notifications/' );
}

function mgk_notification_center_context() {
    $context = [
        'events' => [
            [ 'event' => 'Match proposal ready',       'push' => '•', 'email' => '◐', 'sms' => 'off', 'whatsapp' => '◐' ],
            [ 'event' => 'Booking confirmed',          'push' => '•', 'email' => '•', 'sms' => '•',   'whatsapp' => '○' ],
            [ 'event' => 'Lesson reminder (24h / 1h)', 'push' => '•', 'email' => '○', 'sms' => '•',   'whatsapp' => '•' ],
            [ 'event' => 'Reschedule / cancellation',  'push' => '•', 'email' => '•', 'sms' => '○',   'whatsapp' => '•' ],
            [ 'event' => 'Payment / receipt',          'push' => '○', 'email' => '•', 'sms' => '•',   'whatsapp' => '○' ],
            [ 'event' => 'Review request',             'push' => '•', 'email' => '•', 'sms' => '○',   'whatsapp' => '○' ],
            [ 'event' => 'Promotions / newsletter',    'push' => '○', 'email' => '◐', 'sms' => '○',   'whatsapp' => 'off' ],
        ],
        'previews' => [
            [
                'kicker' => 'TUTOR · NEW MATCH',
                'title'  => 'New request: P5 Math · Tampines',
                'button' => 'Accept',
            ],
            [
                'kicker' => 'PARENT · PROPOSAL',
                'title'  => '3 tutors matched to your request',
                'button' => 'View Proposal',
            ],
        ],
    ];

    return apply_filters( 'mgk_notification_center_context', $context );
}

function mgk_render_notification_center_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/notification-center/' . $part, [
        'atts'    => $atts,
        'context' => mgk_notification_center_context(),
    ] );
}

add_shortcode( 'mgk_notification_center', function ( $atts ) {
    $defaults = [
        'hidden'              => '',
        'hide_header'         => '',
        'sec_header'          => 'SEC 1 Header',
        'title'               => 'Notification preferences',
        'subtitle'            => 'CHOOSE HOW WE REACH YOU PER EVENT. DEFAULTS ARE PDPA-SAFE.',
        'profile_label'       => 'PROFILE: PARENT ▾',
        'master_label'        => 'MASTER:',
        'all_on_label'        => 'ALL ON ●',
        'essential_label'     => 'ESSENTIAL ONLY ○',
        'hide_matrix'         => '',
        'sec_matrix'          => 'SEC 2 Matrix',
        'event_col'           => 'EVENT TYPE',
        'push_col'            => 'PUSH',
        'email_col'           => 'EMAIL',
        'sms_col'             => 'SMS',
        'whatsapp_col'        => 'WHATSAPP',
        'matrix_note'         => '• on    ○ off    ◐ on, batched (quiet hours)    Promotions opt-in only (PDPA)',
        'hide_quiet'          => '',
        'sec_quiet'           => 'SEC 3 Quiet Hours',
        'quiet_title'         => 'Quiet hours',
        'quiet_body'          => 'NO PUSH/SMS/WHATSAPP IN THIS WINDOW (EMAIL STILL QUEUED). BR-14 / FR-SYS-07.',
        'quiet_toggle'        => 'ON ●',
        'quiet_from'          => '10:00 PM ▾',
        'quiet_to'            => '7:00 AM ▾',
        'quiet_tz'            => 'SGT',
        'quiet_alert'         => '△ CRITICAL ALERTS (NO-SHOW, URGENT DISPUTE) OVERRIDE QUIET HOURS — PM TO CONFIRM SCOPE.',
        'hide_preview'        => '',
        'sec_preview'         => 'SEC 4 Rich Push',
        'preview_title'       => 'Rich push with inline actions (FR-SYS-02)',
        'preview_note'        => 'TUTOR ACCEPT/DECLINE + PARENT VIEW PROPOSAL FIRE FROM THE NOTIFICATION ITSELF.',
        'hide_pdpa'           => '',
        'sec_pdpa'            => 'SEC 5 PDPA',
        'pdpa_body'           => '▣ PDPA: MARKETING OFF BY DEFAULT; TRANSACTIONAL ALWAYS ON. CONSENT + WHATSAPP OPT-IN LOGGED.',
        'pdpa_link'           => 'MANAGE CONSENT / DATA →',
    ];

    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( $defaults, $atts )
        : shortcode_atts( $defaults, $atts );

    return mgk_render_notification_center_part( 'notification-center-page', $atts );
} );
