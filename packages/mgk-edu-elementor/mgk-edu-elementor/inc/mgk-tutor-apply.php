<?php
/**
 * S19 tutor apply/onboarding shell.
 *
 * Tutor application records, OCR extraction, background check status and
 * payout verification are DATA CORE. Elementor edits shell copy, wizard labels,
 * preview step and style only.
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

function mgk_tutor_apply_context() {
    $context = [
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
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_tutor_apply_part( 'apply-page', $atts );
} );
