<?php
/**
 * S18 parent account/settings shell.
 *
 * Profile, payment, children, notification and DSAR records are DATA CORE.
 * Elementor edits only shell labels, helper copy, preview state and style.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_parent_account_url( $state = '', $extra = [] ) {
    $params = [];
    if ( $state !== '' ) {
        $params['state'] = sanitize_key( (string) $state );
    }
    foreach ( (array) $extra as $key => $value ) {
        if ( $value !== '' ) {
            $params[ sanitize_key( (string) $key ) ] = sanitize_text_field( (string) $value );
        }
    }

    return add_query_arg( $params, mgk_url( '/parent/account/' ) );
}

function mgk_parent_account_state() {
    $state = sanitize_key( mgk_get_query_filter( 'state', 'default' ) );
    return in_array( $state, [ 'default', 'otp', 'dsar-export', 'delete-account' ], true ) ? $state : 'default';
}

function mgk_parent_account_context() {
    $context = [
        'state'       => mgk_parent_account_state(),
        'otp_type'    => sanitize_key( mgk_get_query_filter( 'type', 'phone' ) ),
        'parent'      => [
            'name'     => 'Mrs Tan Mei Ling',
            'email'    => 'm***@gmail.com',
            'phone'    => '+65 9... ..21',
            'password' => '••••••••',
        ],
        'payments'    => [
            [ 'type' => 'PAYNOW', 'label' => 'DEFAULT', 'meta' => 'LINKED TO +65 9... ..21', 'active' => true ],
            [ 'type' => 'VISA', 'label' => '•••• 4242', 'meta' => 'EXP 09/27 · SET DEFAULT', 'active' => false ],
        ],
        'children'    => [
            [ 'name' => 'Emma', 'meta' => 'P5 · MATH' ],
            [ 'name' => 'Ryan', 'meta' => 'SEC 2 · SCIENCE' ],
        ],
        'languages'   => [ 'EN', '中文', 'BM' ],
    ];

    return apply_filters( 'mgk_parent_account_context', $context );
}

function mgk_render_parent_account_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $context = mgk_parent_account_context();
    $preview_state = sanitize_key( (string) ( $atts['preview_state'] ?? '' ) );
    if ( in_array( $preview_state, [ 'default', 'otp', 'dsar-export', 'delete-account' ], true ) ) {
        $context['state'] = $preview_state;
    }

    return mgk_render_part( 'template-parts/sections/account/' . $part, [
        'atts'    => $atts,
        'context' => $context,
    ] );
}

add_shortcode( 'mgk_parent_account', function ( $atts ) {
    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( [
            'preview_state'             => '',
            'hide_nav'                  => '',
            'sec_nav'                   => 'SEC 1 Nav',
            'nav_profile'               => 'Profile & contact',
            'nav_payment'               => 'Payment methods',
            'nav_children'              => 'Children',
            'nav_notifications'         => 'Notifications -> S27',
            'nav_language'              => 'Language',
            'nav_dsar'                  => 'Privacy & data (DSAR)',
            'hide_profile'              => '',
            'sec_profile'               => 'SEC 2 Profile + Contact',
            'profile_title'             => 'Profile & contact',
            'full_name_label'           => 'FULL NAME',
            'email_label'               => 'EMAIL',
            'phone_label'               => 'PHONE',
            'password_label'            => 'PASSWORD',
            'change_otp_label'          => 'change (OTP)',
            'password_update_label'     => 'update',
            'edit_profile_label'        => 'Edit profile',
            'hide_payment'              => '',
            'sec_payment'               => 'SEC 3 Payment',
            'payment_title'             => 'Payment methods',
            'add_payment_label'         => '+ Add payment method',
            'hide_children'             => '',
            'sec_children'              => 'SEC 4 Children',
            'children_title'            => 'Children',
            'child_edit_label'          => 'EDIT · SCHOOL, LEVEL, SUBJECTS',
            'add_child_label'           => '+ ADD A CHILD',
            'children_note'             => 'CHILDREN FEED THE DASHBOARD SWITCHER (FR-SYS-10). PDPA: MINOR DATA MINIMISED.',
            'hide_pref_lang'            => '',
            'sec_pref_lang'             => 'SEC 5 Notif + SEC 7 Lang',
            'notifications_title'       => 'Notification preferences',
            'notifications_body'        => 'EMAIL / PUSH / SMS · DIGESTS',
            'notifications_button'      => 'Manage -> S27',
            'language_title'            => 'Language',
            'hide_dsar'                 => '',
            'sec_dsar'                  => 'SEC 6 DSAR (NFR 10.3)',
            'dsar_title'                => 'Privacy & your data',
            'export_title'              => 'Export my data',
            'export_body'               => 'DOWNLOAD ALL YOUR DATA (PDPA ACCESS REQUEST · NFR 10.3)',
            'export_button'             => 'Request export',
            'delete_title'              => 'Delete account',
            'delete_body'               => 'ERASE YOUR ACCOUNT & PERSONAL DATA (NFR 10.3)',
            'delete_button'             => 'Delete account...',
            'hide_otp'                  => '',
            'otp_title'                 => 'EDIT PROFILE · OTP RE-VERIFY',
            'otp_kicker'                => 'SECURITY',
            'otp_heading'               => 'Change phone number',
            'otp_new_label'             => 'NEW NUMBER: +65 8•• ••••',
            'otp_verify_title'          => 'Verify it is you',
            'otp_verify_body'           => 'WE SENT A 6-DIGIT OTP TO THE NEW NUMBER.',
            'otp_button'                => 'Verify & save',
            'otp_resend'                => 'RESEND IN 0:38',
            'otp_note'                  => 'EMAIL + PHONE CHANGES BOTH REQUIRE OTP RE-VERIFY BEFORE COMMIT. OLD CONTACT NOTIFIED OF CHANGE.',
            'hide_export_state'         => '',
            'export_state_title'        => 'DSAR EXPORT',
            'export_state_kicker'       => 'NFR 10.3',
            'export_state_heading'      => 'Export my data',
            'export_state_body'         => "WE'LL COMPILE YOUR PROFILE, CHILDREN, BOOKINGS, LESSON LOGS, MESSAGES & PAYMENTS.",
            'export_format'             => 'FORMAT: JSON + PDF',
            'export_delivery'           => 'DELIVERED TO YOUR VERIFIED EMAIL WITHIN 30 DAYS (PDPA / NFR 10.3)',
            'export_status'             => 'STATUS: EXPORT REQUESTED 3 JUN · READY BY 8 JUN · YOU WILL BE EMAILED A SECURE LINK',
            'export_state_button'       => 'Request export',
            'export_state_note'         => 'PDPA ACCESS REQUEST. RATE-LIMITED. LINK EXPIRES; RE-AUTH TO DOWNLOAD.',
            'hide_delete_state'         => '',
            'delete_state_title'        => 'DELETE-ACCOUNT CONFIRM',
            'delete_state_kicker'       => 'EDGE · NFR 10.3',
            'delete_state_heading'      => 'Delete your account?',
            'delete_warning'            => 'THIS PERMANENTLY ERASES YOUR PROFILE, CHILDREN & PERSONAL DATA (NFR 10.3). ACTIVE PACKAGE? YOU WILL SEE REFUND PREVIEW (BR-07) FIRST. SOME RECORDS KEPT FOR LEGAL/FINANCE RETENTION (ANONYMISED).',
            'delete_confirm_label'      => 'TYPE DELETE TO CONFIRM:',
            'delete_cancel'             => 'Cancel, keep account',
            'delete_permanent'          => 'Delete permanently',
            'delete_state_note'         => 'TYPE-TO-CONFIRM + OTP RE-VERIFY GATE. GRACE PERIOD (E.G. 14D) BEFORE HARD-DELETE, PER-TENANT CONFIG · PM TO CONFIRM.',
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_parent_account_part( 'account-page', $atts );
} );
