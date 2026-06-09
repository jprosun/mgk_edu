<?php
/**
 * S22 lesson log shell.
 *
 * Lesson/student/package data is DATA CORE. Elementor edits labels, visibility
 * and visual shell only; saved lesson records later come from wp-admin/data APIs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_lesson_log_url() {
    return mgk_url( '/tutor/lesson-log/' );
}

function mgk_tutor_lesson_log_context() {
    $context = [
        'lesson' => [
            'title'   => 'Aaron Tan · P5 Math',
            'meta'    => 'MON 3 JUN · 16:00-17:30 · STUDENT HOME, TAMPINES · LESSON 14 OF PACKAGE',
            'package' => '8-lesson · 5 left',
        ],
        'attendance' => [
            [ 'label' => '✓ Attended', 'active' => true ],
            [ 'label' => 'Late · MIN', 'active' => false ],
            [ 'label' => 'No-show', 'active' => false ],
        ],
        'note_fields' => [
            [ 'label' => 'TOPIC COVERED', 'value' => 'Fractions · adding unlike denominators' ],
            [ 'label' => 'HOMEWORK SET', 'value' => 'Workbook p.42-43, Q1-8' ],
            [ 'label' => 'ENGAGEMENT', 'value' => 'Focused; asked strong questions' ],
            [ 'label' => 'NEXT FOCUS', 'value' => 'Word problems involving fractions' ],
        ],
    ];

    return apply_filters( 'mgk_tutor_lesson_log_context', $context );
}

function mgk_render_tutor_lesson_log_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/tutor-lesson-log/' . $part, [
        'atts'    => $atts,
        'context' => mgk_tutor_lesson_log_context(),
    ] );
}

add_shortcode( 'mgk_tutor_lesson_log', function ( $atts ) {
    $defaults = [
        'hidden'            => '',
        'hide_topbar'       => '',
        'back_label'        => '‹ Lesson log',
        'entry_label'       => '',
        'autosave_label'    => '• Saved 12s',
        'hide_header'       => '',
        'sec_lesson'        => 'SEC 1 Lesson',
        'package_label'     => 'PACKAGE',
        'hide_attendance'   => '',
        'sec_attendance'    => 'SEC 2 Attendance',
        'attendance_title'  => 'Attendance',
        'hide_notes'        => '',
        'sec_notes'         => 'SEC 3 Voice-Text',
        'note_title'        => 'Lesson note',
        'recording_label'   => '▾ Recording…',
        'transcript_label'  => '• TRANSCRIBING · AUTO-SORTS INTO FIELDS',
        'note_footer'       => 'EACH FIELD EDITABLE — VOICE FILLS A DRAFT, TUTOR CORRECTS.',
        'hide_photos'       => '',
        'sec_photos'        => 'SEC 4 Photos',
        'photos_title'      => 'Photos (1/3)',
        'photo_existing'    => '▣',
        'photo_add_label'   => '+',
        'photo_plus_label'  => '+',
        'photos_note'       => 'MAX 2 · ≤200KB EACH · AUTO-COMPRESS',
        'hide_save'         => '',
        'sec_save'          => '5',
        'autosave_title'    => 'Draft auto-saved every 30s · resume from dashboard if you close',
        'autosave_meta'     => '',
        'hide_sla'          => '',
        'sla_title'         => '',
        'sla_body'          => 'SUBMIT IN 24H (BY TUE 16:00)',
        'hide_actions'      => '',
        'submit_label'      => 'Submit log',
        'draft_label'       => 'Save draft & close',
    ];

    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( $defaults, $atts )
        : shortcode_atts( $defaults, $atts );

    return mgk_render_tutor_lesson_log_part( 'lesson-log-page', $atts );
} );
