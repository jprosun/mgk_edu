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

/** Demo context (editor/admin/preview). */
function mgk_tutor_lesson_log_demo_context() {
    return [
        'state'  => 'demo',
        'lesson' => [
            'title'   => 'Aaron Tan · P5 Math',
            'meta'    => 'MON 3 JUN · 16:00-17:30 · STUDENT HOME, TAMPINES · LESSON 14 OF PACKAGE',
            'package' => '8-lesson · 5 left',
        ],
        'attendance' => [
            [ 'label' => '✓ Attended', 'value' => 'ATTENDED', 'active' => true ],
            [ 'label' => 'Late', 'value' => 'LATE', 'active' => false ],
            [ 'label' => 'No-show', 'value' => 'NO_SHOW', 'active' => false ],
        ],
        'note_fields' => [
            [ 'label' => 'TOPIC COVERED', 'value' => 'Fractions · adding unlike denominators' ],
            [ 'label' => 'HOMEWORK SET', 'value' => 'Workbook p.42-43, Q1-8' ],
            [ 'label' => 'ENGAGEMENT', 'value' => 'Focused; asked strong questions' ],
            [ 'label' => 'NEXT FOCUS', 'value' => 'Word problems involving fractions' ],
        ],
        'booking_id' => 0,
        'child_id'   => 0,
    ];
}

function mgk_tutor_lesson_log_block_copy( $reason ) {
    $copy = [
        'notlesson'    => [ 'This booking cannot be logged', 'Package purchases are paid upfront and scheduled later. Open a real lesson session from your dashboard.' ],
        'notended'     => [ 'This lesson has not ended yet', 'You can submit the lesson log after the scheduled end time.' ],
        'notconfirmed' => [ 'This lesson is not ready to log', 'Only confirmed paid lessons can be logged.' ],
        'nochild'      => [ 'Student record missing', 'Ask agency ops to attach the student record before logging this lesson.' ],
    ];
    return $copy[ $reason ] ?? [ 'Pick a lesson to log', 'Open a pending log from your dashboard.' ];
}

/**
 * Real context: read ?booking=, verify the logged-in tutor owns it, expose the
 * lesson details + form scaffold. Falls back to demo for editor/admin, or a gated
 * state for a logged-out visitor.
 */
function mgk_tutor_lesson_log_context() {
    $is_tutor = function_exists( 'mgk_is_tutor_user' ) && mgk_is_tutor_user();
    $is_editor = function_exists( 'mgk_tutor_dash_is_editor' ) && mgk_tutor_dash_is_editor();

    if ( ! $is_tutor ) {
        // Editor/admin → demo so the page stays designable; otherwise gated.
        $ctx = $is_editor ? mgk_tutor_lesson_log_demo_context() : [ 'state' => 'gated' ];
        return apply_filters( 'mgk_tutor_lesson_log_context', $ctx );
    }

    $teacher_id = mgk_current_tutor_teacher_id();
    $booking_id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
    $row = ( $booking_id && function_exists( 'mgk_get_booking_row' ) ) ? mgk_get_booking_row( $booking_id ) : null;

    // No/!owned booking → a chooser-ish empty state (still real, not demo).
    if ( ! $row || (int) $row['tutor_post_id'] !== (int) $teacher_id ) {
        return apply_filters( 'mgk_tutor_lesson_log_context', [
            'state' => 'no-booking',
            'lesson' => [ 'title' => 'Pick a lesson to log', 'meta' => 'Open a pending log from your dashboard.', 'package' => '' ],
            'booking_id' => 0, 'child_id' => 0,
        ] );
    }

    $view = function_exists( 'mgk_booking_view' ) ? mgk_booking_view( $row ) : [];
    $child_id = (int) ( $row['child_id'] ?? 0 );
    $already  = function_exists( 'mgk_booking_has_lesson_log' ) && mgk_booking_has_lesson_log( $booking_id );

    if ( ! $already && function_exists( 'mgk_tutor_booking_log_block_reason' ) ) {
        $block = mgk_tutor_booking_log_block_reason( $row, $teacher_id );
        if ( $block !== '' ) {
            [ $title, $meta ] = mgk_tutor_lesson_log_block_copy( $block );
            return apply_filters( 'mgk_tutor_lesson_log_context', [
                'state'      => 'no-booking',
                'lesson'     => [ 'title' => $title, 'meta' => $meta, 'package' => '' ],
                'booking_id' => 0,
                'child_id'   => 0,
                'reason'     => $block,
            ] );
        }
    }

    // Package position (if this lesson belongs to an enrolment).
    $package_label = '';
    if ( $child_id && function_exists( 'mgk_enrolment_for_child' ) ) {
        $enr = mgk_enrolment_for_child( $child_id );
        if ( $enr ) {
            $total = (int) get_post_meta( $enr, 'mgk_enr_lessons_total', true );
            $used  = function_exists( 'mgk_enrolment_lessons_used' ) ? (int) mgk_enrolment_lessons_used( $enr ) : 0;
            if ( $total ) $package_label = sprintf( '%d-lesson · %d left', $total, max( 0, $total - $used ) );
        }
    }
    if ( $package_label === '' && ( $row['lesson_type'] ?? '' ) === 'TRIAL' ) $package_label = 'Trial lesson';

    $student = (string) ( $row['student_name'] ?? 'Student' );
    $subject = (string) ( $row['subject'] ?? '' );
    $slot    = $view['slot'] ?? [];
    $meta    = strtoupper( trim( ( $slot['datetime'] ?? '' ) . ( $package_label ? ' · ' . $package_label : '' ) ) );

    $ctx = [
        'state'  => $already ? 'logged' : 'log',
        'lesson' => [
            'title'   => trim( $student . ( $subject ? ' · ' . $subject : '' ) ),
            'meta'    => $meta,
            'package' => $package_label,
        ],
        'attendance' => [
            [ 'label' => '✓ Attended', 'value' => 'ATTENDED', 'active' => true ],
            [ 'label' => 'Late', 'value' => 'LATE', 'active' => false ],
            [ 'label' => 'No-show', 'value' => 'NO_SHOW', 'active' => false ],
        ],
        'engagement' => mgk_engagement_levels(), // EXCELLENT..NEEDS_IMPROVEMENT
        'booking_id' => $booking_id,
        'child_id'   => $child_id,
        'note_fields'=> [], // real mode uses editable inputs, not read-only chips
    ];
    return apply_filters( 'mgk_tutor_lesson_log_context', $ctx );
}

/**
 * admin-post handler: a tutor submits a lesson log for one of their bookings.
 * Verifies nonce + ownership + idempotency, writes mg_lesson via the learning
 * model, flips the booking to COMPLETED, and (optionally) attaches a photo.
 */
function mgk_tutor_log_lesson_handler() {
    $dash = mgk_get_tutor_dashboard_url();
    if ( ! ( function_exists( 'mgk_is_tutor_user' ) && mgk_is_tutor_user() ) ) {
        wp_safe_redirect( mgk_get_tutor_login_url() ); exit;
    }
    $booking_id = isset( $_POST['mgk_booking_id'] ) ? (int) $_POST['mgk_booking_id'] : 0;
    $nonce = isset( $_POST['mgk_tutor_log_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mgk_tutor_log_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'mgk_tutor_log_lesson_' . $booking_id ) ) {
        wp_safe_redirect( add_query_arg( 'mgk_log', 'badnonce', $dash ) ); exit;
    }

    $teacher_id = mgk_current_tutor_teacher_id();
    $row = $booking_id ? mgk_get_booking_row( $booking_id ) : null;
    if ( ! $row || (int) $row['tutor_post_id'] !== (int) $teacher_id ) {
        wp_safe_redirect( add_query_arg( 'mgk_log', 'denied', $dash ) ); exit;
    }

    // Idempotency: one log per booking.
    if ( mgk_booking_has_lesson_log( $booking_id ) ) {
        wp_safe_redirect( add_query_arg( 'mgk_log', 'dup', $dash ) ); exit;
    }

    if ( function_exists( 'mgk_tutor_booking_log_block_reason' ) ) {
        $block = mgk_tutor_booking_log_block_reason( $row, $teacher_id );
        if ( $block !== '' ) {
            wp_safe_redirect( add_query_arg( 'mgk_log', $block, $dash ) ); exit;
        }
    }

    $attendance = strtoupper( sanitize_text_field( wp_unslash( $_POST['mgk_attendance'] ?? 'ATTENDED' ) ) );
    if ( ! in_array( $attendance, [ 'ATTENDED', 'LATE', 'NO_SHOW' ], true ) ) $attendance = 'ATTENDED';
    $engagement = strtoupper( sanitize_text_field( wp_unslash( $_POST['mgk_engagement'] ?? 'GOOD' ) ) );
    if ( ! array_key_exists( $engagement, mgk_engagement_levels() ) ) $engagement = 'GOOD';

    $child_id = (int) ( $row['child_id'] ?? 0 );
    $enrolment_id = ( $child_id && function_exists( 'mgk_enrolment_for_child' ) ) ? (int) mgk_enrolment_for_child( $child_id ) : 0;

    // Lesson date from the slot (SGT), duration from the booking window.
    $lesson_date = '';
    $duration = 90;
    if ( ! empty( $row['start_at_utc'] ) ) {
        try {
            $tz = function_exists( 'mgk_view_tz' ) ? mgk_view_tz() : new DateTimeZone( 'Asia/Singapore' );
            $s = new DateTime( $row['start_at_utc'] . ' UTC' ); $s->setTimezone( $tz );
            $lesson_date = $s->format( 'Y-m-d' );
            if ( ! empty( $row['end_at_utc'] ) ) {
                $e = new DateTime( $row['end_at_utc'] . ' UTC' );
                $mins = ( $e->getTimestamp() - ( new DateTime( $row['start_at_utc'] . ' UTC' ) )->getTimestamp() ) / 60;
                if ( $mins > 0 ) $duration = (int) round( $mins );
            }
        } catch ( Exception $e ) {}
    }

    $lesson_id = mgk_lesson_log_create( [
        'enrolment_id' => $enrolment_id,
        'child_id'     => $child_id,
        'tutor_id'     => (int) $teacher_id,
        'booking_id'   => $booking_id,
        'attendance'   => $attendance,
        'engagement'   => $engagement,
        'topic'        => wp_unslash( $_POST['mgk_topic'] ?? '' ),
        'homework'     => wp_unslash( $_POST['mgk_homework'] ?? '' ),
        'comment'      => wp_unslash( $_POST['mgk_comment'] ?? '' ),
        'duration_min' => $duration,
        'lesson_date'  => $lesson_date,
    ] );

    if ( ! $lesson_id ) {
        wp_safe_redirect( add_query_arg( 'mgk_log', 'fail', $dash ) ); exit;
    }

    // Optional photo upload.
    if ( ! empty( $_FILES['mgk_lesson_photo']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        $att_id = media_handle_upload( 'mgk_lesson_photo', 0 );
        if ( ! is_wp_error( $att_id ) ) {
            update_post_meta( $lesson_id, 'mgk_lesson_photo_id', (int) $att_id );
        }
    }

    // Flip the booking to COMPLETED + audit.
    global $wpdb;
    $now = mgk_booking_now_utc();
    $wpdb->update( mgk_booking_table( 'bookings' ),
        [ 'status' => 'COMPLETED', 'updated_at_utc' => $now ],
        [ 'id' => $booking_id ]
    );
    mgk_log_booking_event( $booking_id, 'LESSON_LOGGED', [
        'actor_type' => 'TUTOR', 'actor_id' => get_current_user_id(),
        'old_status' => (string) $row['status'], 'new_status' => 'COMPLETED',
        'metadata'   => [ 'lesson_id' => (int) $lesson_id, 'attendance' => $attendance, 'engagement' => $engagement ],
    ] );

    wp_safe_redirect( add_query_arg( 'mgk_log', 'ok', $dash ) ); exit;
}
add_action( 'admin_post_mgk_tutor_log_lesson', 'mgk_tutor_log_lesson_handler' );

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
