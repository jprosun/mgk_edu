<?php
/**
 * S20-21 tutor verification / application status.
 *
 * Live mode shows an applicant their REAL application status, resolved by the
 * view-token on /tutor/verification/?token=<token> (no login needed) or by the
 * logged-in tutor's own record. The timeline + reviewer note come straight from
 * the application data (inc/mgk-tutor-application.php). There is no video upload
 * or OCR step — review is manual (owner decision, Phase 2).
 *
 * The Elementor editor still renders the original demo so the section stays
 * designable (same demo-vs-real split as apply / dashboard).
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

/** Admin / Elementor edit / preview → demo. */
function mgk_tutor_verification_is_editor() {
    if ( is_admin() ) return true;
    if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance ) {
        $p = \Elementor\Plugin::$instance;
        if ( isset( $p->editor ) && $p->editor->is_edit_mode() ) return true;
        if ( isset( $p->preview ) && $p->preview->is_preview_mode() ) return true;
    }
    return false;
}

/** Resolve the application this request is about: ?token=, else the logged-in tutor. */
function mgk_tutor_verification_application() {
    $token = sanitize_text_field( (string) mgk_get_query_filter( 'token', '' ) );
    if ( $token !== '' ) {
        $post = mgk_get_application_by_token( $token );
        if ( $post ) return $post;
    }
    if ( function_exists( 'mgk_current_tutor_teacher_id' ) ) {
        $tid = mgk_current_tutor_teacher_id();
        if ( $tid && mgk_is_tutor_application( $tid ) ) return get_post( $tid );
    }
    return null;
}

/** The demo context (preserved for the Elementor editor). */
function mgk_tutor_verification_demo_context() {
    $variant = mgk_tutor_verification_variant();
    return [
        'mode'          => 'demo',
        'variant'       => $variant,
        'current_state' => $variant === 'approved' ? 'ACTIVE' : ( $variant === 'rejected' ? 'REJECTED' : 'DEMO_PENDING' ),
        'candidate'     => [ 'name' => 'Lee Yi Ling', 'submitted' => '28 Jan' ],
        'video'         => [ 'filename' => 'DEMO_LESSON.MP4', 'size' => '58MB / 90MB', 'eta' => '~20S LEFT', 'progress' => '64%' ],
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
}

/** Real status context for a resolved application post. */
function mgk_tutor_verification_real_context( $post ) {
    $teacher_id = (int) $post->ID;
    $status     = mgk_application_status( $teacher_id );
    $submitted  = (string) get_post_meta( $teacher_id, 'mgk_app_submitted_at', true );
    $submitted_h = $submitted ? date_i18n( 'j M Y', strtotime( $submitted ) ) : '';

    return [
        'mode'          => 'real',
        'state'         => $status,
        'state_label'   => mgk_app_status_label( $status ),
        'current_state' => strtoupper( str_replace( '_', ' ', $status ) ),
        'is_approved'   => $status === MGK_APP_APPROVED,
        'is_rejected'   => $status === MGK_APP_REJECTED,
        'needs_info'    => $status === MGK_APP_INFO_REQUESTED,
        'reviewer_note' => (string) get_post_meta( $teacher_id, 'mgk_app_reviewer_note', true ),
        'just_applied'  => ! empty( $_GET['applied'] ),
        'candidate'     => [ 'name' => get_the_title( $teacher_id ) ?: 'Tutor', 'submitted' => $submitted_h ],
        'timeline'      => mgk_application_timeline( $status ),
        'apply_url'     => mgk_url( '/become-a-tutor/' ),
        'login_url'     => function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' ),
        'dashboard_url' => function_exists( 'mgk_get_tutor_dashboard_url' ) ? mgk_get_tutor_dashboard_url() : mgk_url( '/tutor/dashboard/' ),
    ];
}

function mgk_tutor_verification_context() {
    if ( mgk_tutor_verification_is_editor() ) {
        return apply_filters( 'mgk_tutor_verification_context', mgk_tutor_verification_demo_context() );
    }

    $post = mgk_tutor_verification_application();
    if ( $post ) {
        return apply_filters( 'mgk_tutor_verification_context', mgk_tutor_verification_real_context( $post ) );
    }

    // No token, not logged in → a friendly "nothing to show" pointer.
    return apply_filters( 'mgk_tutor_verification_context', [
        'mode'      => 'empty',
        'apply_url' => mgk_url( '/become-a-tutor/' ),
        'login_url' => function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' ),
    ] );
}

function mgk_render_tutor_verification_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $context = mgk_tutor_verification_context();
    // preview_variant only affects the demo (editor) path.
    if ( ( $context['mode'] ?? '' ) === 'demo' ) {
        $preview_variant = sanitize_key( (string) ( $atts['preview_variant'] ?? '' ) );
        if ( in_array( $preview_variant, [ 'default', 'rejected', 'approved' ], true ) ) {
            $context['variant'] = $preview_variant;
            $context['current_state'] = $preview_variant === 'approved' ? 'ACTIVE' : ( $preview_variant === 'rejected' ? 'REJECTED' : 'DEMO_PENDING' );
        }
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
            // ── Live status copy ──
            'live_title'           => 'Your application status',
            'live_received_title'  => "We've got your application",
            'live_resubmit_label'  => 'Update & resubmit →',
            'live_signin_label'    => 'Sign in to your dashboard →',
            'live_reapply_label'   => 'Apply again →',
            'live_note_label'      => 'Message from our team',
            'live_avg_time'        => 'Most applications are reviewed within 2–3 business days. We email you at every step.',
            'empty_title'          => 'No application found',
            'empty_body'           => "We couldn't find an application for this link. If you've applied, use the status link in your email.",
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_tutor_verification_part( 'verification-page', $atts );
} );
