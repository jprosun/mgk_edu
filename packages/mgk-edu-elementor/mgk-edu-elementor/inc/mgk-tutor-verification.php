<?php
/**
 * S20 tutor verification shell.
 *
 * Application status, video upload, timeline and reviewer messages are DATA
 * CORE. Elementor edits only shell copy, preview variant and style.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_verification_url( $variant = '' ) {
    $params = [];
    if ( $variant !== '' ) {
        $params['variant'] = sanitize_key( (string) $variant );
    }

    return add_query_arg( $params, mgk_url( '/tutor/verification/' ) );
}

function mgk_tutor_verification_variant() {
    $variant = sanitize_key( mgk_get_query_filter( 'variant', 'default' ) );
    return in_array( $variant, [ 'default', 'rejected', 'approved' ], true ) ? $variant : 'default';
}

function mgk_tutor_verification_context() {
    $variant = mgk_tutor_verification_variant();
    $context = [
        'variant'       => $variant,
        'current_state' => $variant === 'approved' ? 'ACTIVE' : ( $variant === 'rejected' ? 'REJECTED' : 'DEMO_PENDING' ),
        'candidate'     => [
            'name'      => 'Lee Yi Ling',
            'submitted' => '28 Jan',
        ],
        'video'         => [
            'filename' => 'DEMO_LESSON.MP4',
            'size'     => '58MB / 90MB',
            'eta'      => '~20S LEFT',
            'progress' => '64%',
        ],
        'requirements'  => [
            [ 'value' => '≤ 2:00', 'label' => 'MAX LENGTH' ],
            [ 'value' => '≤ 100MB', 'label' => 'MAX SIZE' ],
            [ 'value' => 'MP4/MOV', 'label' => 'FORMAT' ],
        ],
        'timeline'      => [
            [ 'key' => 'docs_pending',  'title' => 'DOCS_PENDING',  'meta' => 'DOCS RECEIVED · 28 JAN', 'done' => true,  'active' => false ],
            [ 'key' => 'docs_verified', 'title' => 'DOCS_VERIFIED', 'meta' => 'NRIC + DEGREE CLEARED · 29 JAN', 'done' => true,  'active' => false ],
            [ 'key' => 'demo_pending',  'title' => 'DEMO_PENDING · you are here', 'meta' => 'UPLOAD DEMO TO PROCEED', 'done' => false, 'active' => $variant === 'default' ],
            [ 'key' => 'demo_approved', 'title' => 'DEMO_APPROVED', 'meta' => 'REVIEWER APPROVES VIDEO', 'done' => $variant === 'approved', 'active' => false ],
            [ 'key' => 'interview',     'title' => 'INTERVIEW', 'meta' => 'SHORT CALL · SCHEDULE LINK SENT', 'done' => false, 'active' => false ],
            [ 'key' => 'active',        'title' => 'ACTIVE', 'meta' => 'PROFILE LIVE · JOB INBOX OPENS', 'done' => $variant === 'approved', 'active' => $variant === 'approved' ],
        ],
    ];

    return apply_filters( 'mgk_tutor_verification_context', $context );
}

function mgk_render_tutor_verification_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $context = mgk_tutor_verification_context();
    $preview_variant = sanitize_key( (string) ( $atts['preview_variant'] ?? '' ) );
    if ( in_array( $preview_variant, [ 'default', 'rejected', 'approved' ], true ) ) {
        $context['variant'] = $preview_variant;
        $context['current_state'] = $preview_variant === 'approved' ? 'ACTIVE' : ( $preview_variant === 'rejected' ? 'REJECTED' : 'DEMO_PENDING' );
    }

    return mgk_render_part( 'template-parts/sections/tutor-verification/' . $part, [
        'atts'    => $atts,
        'context' => $context,
    ] );
}

add_shortcode( 'mgk_tutor_verification', function ( $atts ) {
    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( [
            'preview_variant'      => '',
            'hide_status'          => '',
            'sec_status'           => 'SEC 1 Status',
            'status_title'         => 'Application under verification',
            'status_meta'          => "HI {name} · SUBMITTED {date} · WE'LL NOTIFY BY EMAIL + SMS AT EACH STEP",
            'current_state_label'  => 'CURRENT STATE',
            'hide_video'           => '',
            'video_title'          => 'Upload your demo lesson',
            'video_intro'          => 'SHOW YOUR TEACHING STYLE — REVIEWERS APPROVE BEFORE YOU GO LIVE.',
            'uploading_label'      => 'Uploading...',
            'hide_requirements'    => '',
            'requirements_title'   => 'Video requirements (FR-TUTOR-04)',
            'requirements_tip'     => 'TIP: PICK ONE CONCEPT, TEACH TO CAMERA ~90S. APPROVED VIDEO APPEARS ON YOUR PUBLIC PROFILE (S03 DEMO SLOT).',
            'hide_timeline'        => '',
            'timeline_title'       => 'Verification timeline',
            'timeline_meta'        => 'REAL-TIME · FR-TUTOR-05',
            'reviewer_label'       => 'REVIEWER MESSAGE (REQUEST-MORE-INFO VARIANT)',
            'reviewer_message'     => '"Please re-upload degree cert — page 2 unclear."',
            'reviewer_cta'         => 'Re-submit →',
            'rejected_title'       => 'REJECTED VARIANT',
            'rejected_body'        => 'REASON SHOWN + APPEAL LINK. RE-APPLY AFTER 30 DAYS. PM TO CONFIRM COOLDOWN.',
            'hide_actions'         => '',
            'submit_label'         => 'Submit demo for review',
            'contact_label'        => 'Contact verification team',
            'avg_time'             => 'AVG REVIEW TIME: 2-3 BUSINESS DAYS · PM TO CONFIRM',
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_tutor_verification_part( 'verification-page', $atts );
} );
