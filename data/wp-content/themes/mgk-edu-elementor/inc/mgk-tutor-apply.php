<?php
/**
 * S19 tutor apply/onboarding.
 *
 * The public apply form is a REAL submission that creates a draft `mg_teacher`
 * application (see inc/mgk-tutor-application.php). There is no OCR / background
 * check — the agency reviews and approves manually (owner decision, Phase 2);
 * any documents are requested through the "more info" loop on /tutor/verification/.
 *
 * Elementor editor keeps the original demo wizard so the section is still
 * designable; live visitors get the working form (same demo-vs-real split the
 * dashboard + lesson-log use).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_apply_url( $step = '' ) {
    $params = [];
    if ( $step !== '' ) {
        $params['step'] = max( 1, min( 6, (int) $step ) );
    }

    return add_query_arg( $params, mgk_url( '/become-a-tutor/' ) );
}

function mgk_tutor_apply_step() {
    $step = (int) mgk_get_query_filter( 'step', 3 );
    return max( 1, min( 6, $step ) );
}

/** Admin / Elementor edit / preview → show the demo wizard, never the live form. */
function mgk_tutor_apply_is_editor() {
    if ( is_admin() ) return true;
    if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance ) {
        $p = \Elementor\Plugin::$instance;
        if ( isset( $p->editor ) && $p->editor->is_edit_mode() ) return true;
        if ( isset( $p->preview ) && $p->preview->is_preview_mode() ) return true;
    }
    return false;
}

/** Subject / level options for the apply form, from the live taxonomies. */
function mgk_tutor_apply_term_options( $taxonomy ) {
    $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
    if ( is_wp_error( $terms ) || ! $terms ) return [];
    $out = [];
    foreach ( $terms as $t ) {
        $out[] = [ 'id' => (int) $t->term_id, 'name' => $t->name ];
    }
    return $out;
}

function mgk_tutor_apply_context() {
    $context = [
        'mode'        => mgk_tutor_apply_is_editor() ? 'demo' : 'form',
        'step'        => mgk_tutor_apply_step(),
        'total_steps' => 6,
        'candidate'   => [
            'name'       => 'Lee Yi Ling',
            'meta'       => 'B.SC MATH · NUS 2014',
            'subjects'   => 'P5-SEC MATH',
            'photo'      => 'Photo',
            'completion' => '60%',
        ],
        'education'   => [
            'university' => 'Nat. Univ. of Singapore',
            'degree'     => 'B.Sc. Mathematics',
            'year'       => '2014',
            'match'      => 'Matched MOE-recognised institution list (FR-TUTOR-03). If no match -> manual review flag.',
        ],
        'identity'    => [
            'name' => 'LEE YI LING',
            'dob'  => '1991-03-12',
            'nric' => 'S91••••2G (MASKED)',
        ],
        'steps'       => [
            1 => [ 'short' => 'Basic', 'label' => 'Basic info' ],
            2 => [ 'short' => 'Subjects', 'label' => 'Subjects / levels' ],
            3 => [ 'short' => 'Education', 'label' => 'Education' ],
            4 => [ 'short' => 'Experience', 'label' => 'Experience' ],
            5 => [ 'short' => 'Payout', 'label' => 'Bank / PayNow' ],
            6 => [ 'short' => 'Docs', 'label' => 'Docs upload' ],
        ],
    ];

    if ( $context['mode'] === 'form' ) {
        $context['subjects'] = mgk_tutor_apply_term_options( 'mgk_subject' );
        $context['levels']   = mgk_tutor_apply_term_options( 'mgk_level' );
        $context['action']   = admin_url( 'admin-post.php' );
        $context['nonce']    = wp_create_nonce( 'mgk_tutor_apply_submit' );

        // Hydrate error + old input from a single-use transient set on a failed submit.
        $context['error'] = '';
        $context['old']   = [];
        $resume = isset( $_GET['apply_err'] ) ? sanitize_text_field( wp_unslash( $_GET['apply_err'] ) ) : '';
        if ( $resume ) {
            $payload = get_transient( 'mgk_app_old_' . $resume );
            if ( is_array( $payload ) ) {
                delete_transient( 'mgk_app_old_' . $resume );
                $context['error'] = (string) ( $payload['err'] ?? '' );
                $context['old']   = (array) ( $payload['old'] ?? [] );
            }
        }
    }

    return apply_filters( 'mgk_tutor_apply_context', $context );
}

function mgk_render_tutor_apply_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $context = mgk_tutor_apply_context();
    $preview_step = (int) ( $atts['preview_step'] ?? 0 );
    if ( $preview_step >= 1 && $preview_step <= 6 ) {
        $context['step'] = $preview_step;
    }

    return mgk_render_part( 'template-parts/sections/tutor-apply/' . $part, [
        'atts'    => $atts,
        'context' => $context,
    ] );
}

/* ── Submit handler: create the application, redirect to verification ──────── */
function mgk_tutor_apply_submit_handler() {
    $back  = mgk_get_tutor_apply_url();
    $nonce = isset( $_POST['mgk_tutor_apply_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mgk_tutor_apply_nonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, 'mgk_tutor_apply_submit' ) ) {
        wp_safe_redirect( add_query_arg( 'apply_err', mgk_tutor_apply_stash_error( 'Your session expired. Please submit again.', [] ), $back ) );
        exit;
    }

    // Per-IP rate limit: 5 / hour (abuse guard on a public create endpoint).
    $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
    $rlk = 'mgk_apply_rl_' . md5( $ip );
    if ( (int) get_transient( $rlk ) >= 5 ) {
        wp_safe_redirect( add_query_arg( 'apply_err', mgk_tutor_apply_stash_error( 'Too many submissions. Please try again later.', [] ), $back ) );
        exit;
    }

    $data = [
        'name'       => sanitize_text_field( wp_unslash( $_POST['mgk_app_name'] ?? '' ) ),
        'email'      => sanitize_email( wp_unslash( $_POST['mgk_app_email'] ?? '' ) ),
        'phone'      => sanitize_text_field( wp_unslash( $_POST['mgk_app_phone'] ?? '' ) ),
        'subjects'   => array_map( 'intval', (array) ( $_POST['mgk_app_subjects'] ?? [] ) ),
        'levels'     => array_map( 'intval', (array) ( $_POST['mgk_app_levels'] ?? [] ) ),
        'university' => sanitize_text_field( wp_unslash( $_POST['mgk_app_university'] ?? '' ) ),
        'degree'     => sanitize_text_field( wp_unslash( $_POST['mgk_app_degree'] ?? '' ) ),
        'year'       => sanitize_text_field( wp_unslash( $_POST['mgk_app_year'] ?? '' ) ),
        'experience' => sanitize_textarea_field( wp_unslash( $_POST['mgk_app_experience'] ?? '' ) ),
        'rate'       => (int) ( $_POST['mgk_app_rate'] ?? 0 ),
        'payout'     => sanitize_text_field( wp_unslash( $_POST['mgk_app_payout'] ?? '' ) ),
    ];

    // Consent (BR-19) is required to proceed.
    if ( empty( $_POST['mgk_app_consent'] ) ) {
        wp_safe_redirect( add_query_arg( 'apply_err', mgk_tutor_apply_stash_error( 'Please tick the background-check consent to continue.', $data ), $back ) );
        exit;
    }

    $res = mgk_application_create( $data );
    if ( is_wp_error( $res ) ) {
        wp_safe_redirect( add_query_arg( 'apply_err', mgk_tutor_apply_stash_error( $res->get_error_message(), $data ), $back ) );
        exit;
    }

    set_transient( $rlk, (int) get_transient( $rlk ) + 1, HOUR_IN_SECONDS );

    $url = add_query_arg( [ 'token' => rawurlencode( $res['token'] ), 'applied' => 1 ], mgk_url( '/tutor/verification/' ) );
    wp_safe_redirect( $url );
    exit;
}
add_action( 'admin_post_nopriv_mgk_tutor_apply_submit', 'mgk_tutor_apply_submit_handler' );
add_action( 'admin_post_mgk_tutor_apply_submit', 'mgk_tutor_apply_submit_handler' );

/** Stash error + old input in a single-use transient, return its lookup token. */
function mgk_tutor_apply_stash_error( $message, $old ) {
    $tok = wp_generate_password( 12, false, false );
    set_transient( 'mgk_app_old_' . $tok, [ 'err' => (string) $message, 'old' => (array) $old ], 10 * MINUTE_IN_SECONDS );
    return $tok;
}

add_shortcode( 'mgk_tutor_apply', function ( $atts ) {
    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( [
            'preview_step'              => '',
            'hide_topbar'               => '',
            'topbar_logo'               => '[LOGO]',
            'topbar_title'              => 'Become a Tutor',
            'topbar_help'               => 'NEED HELP? SUPPORT',
            'hide_stepbar'              => '',
            'stepbar_tag'               => 'STEP BAR',
            'mobile_title'              => 'Apply to teach',
            'step_prefix'               => 'STEP',
            'step_of_label'             => 'OF',
            'hide_preview'              => '',
            'preview_title'             => 'Live profile preview',
            'preview_completeness'      => 'COMPLETENESS',
            'preview_note'              => 'Submit unlocks at 100%. On submit -> status UNDER_REVIEW.',
            'hide_education'            => '',
            'sec_education'             => 'STEP 3 Education',
            'education_title'           => 'Your education',
            'education_desktop_title'   => 'Step 3 · Education',
            'education_intro'           => 'UPLOAD YOUR HIGHEST QUALIFICATION - OCR PRE-FILLS, YOU VERIFY.',
            'degree_upload_title'       => 'Drag degree certificate here',
            'degree_upload_mobile'      => 'UPLOAD DEGREE CERT',
            'degree_upload_hint'        => 'PDF / JPG / PNG · MAX 10MB · OCR AUTO-EXTRACT',
            'choose_file_label'         => 'Choose file',
            'ocr_tag'                   => '+ OCR EXTRACTED · VERIFY & EDIT',
            'university_label'          => 'UNIVERSITY',
            'degree_label'              => 'DEGREE',
            'year_label'                => 'YEAR',
            'hide_docs_preview'         => '',
            'sec_docs'                  => 'STEP 6 Docs',
            'docs_title'                => 'Identity documents',
            'docs_desktop_title'        => 'Identity · NRIC OCR (step 6 preview)',
            'nric_upload_label'         => 'UPLOAD NRIC (FRONT)',
            'nric_scan_label'           => 'NRIC front scan',
            'nric_extracting'           => '+ OCR EXTRACTING... 2S',
            'other_docs_label'          => '+ OTHER DOCS: TESTIMONIALS, TRANSCRIPT (OPTIONAL)',
            'name_label'                => 'NAME',
            'dob_label'                 => 'DOB',
            'nric_label'                => 'NRIC',
            'hide_consent'              => '',
            'consent_text'              => 'Background check consent (BR-19): submitting authorises Margick to run a criminal-record / identity check before activation.',
            'bank_warning'              => '+ BANK ACCOUNT NO. INVALID - RE-CHECK (STEP 5)',
            'back_label'                => '< Back',
            'continue_label'            => 'Save & continue >',
            'autosave_label'            => 'AUTO-SAVED 8S AGO',
            'mobile_autosave_label'     => 'Progress auto-saved · resume anytime',
            'footer_text'               => '© 2026 Margick · Tutor onboarding · MOE Registered partner',
            'basic_title'               => 'Basic info',
            'basic_body'                => 'Tell us who you are. Email and phone will be verified before activation.',
            'subjects_title'            => 'Subjects / levels',
            'subjects_body'             => 'Choose the subjects, levels and exam tracks you can teach confidently.',
            'experience_title'          => 'Experience',
            'experience_body'           => 'Add tutoring history, school experience, achievements and availability notes.',
            'payout_title'              => 'Bank / PayNow',
            'payout_body'               => 'Add payout details. PayNow or bank account will be verified before first payout.',
            'docs_title_step'           => 'Docs upload',
            'docs_body_step'            => 'Upload NRIC and certificates. OCR extracts fields, then you verify before submit.',
            // ── Live form copy (real submission) ──
            'form_title'                => 'Apply to teach',
            'form_intro'                => 'Tell us about yourself. Our team reviews every application and replies by email — no OCR, a real person reads it.',
            'form_name_label'           => 'Full name',
            'form_email_label'          => 'Email',
            'form_phone_label'          => 'Phone (WhatsApp)',
            'form_subjects_label'       => 'Subjects you can teach',
            'form_levels_label'         => 'Levels',
            'form_university_label'     => 'University / institution',
            'form_degree_label'         => 'Highest qualification',
            'form_year_label'           => 'Year',
            'form_rate_label'           => 'Indicative rate (S$/hr)',
            'form_experience_label'     => 'Teaching experience & achievements',
            'form_payout_label'         => 'Payout (PayNow / bank) — optional',
            'form_submit_label'         => 'Submit application →',
            'form_login_note'           => 'Already a tutor with us?',
            'form_login_link'           => 'Sign in',
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_tutor_apply_part( 'apply-page', $atts );
} );
