<?php
/**
 * S16 parent review shell.
 *
 * Review records are DATA CORE: the real moderation/publish workflow lives in
 * wp-admin (mg_review). Elementor edits only labels, notices, spacing and style.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_parent_review_url( $tutor = '' ) {
    $params = [];
    if ( $tutor !== '' ) {
        $params['tutor'] = sanitize_title( (string) $tutor );
    }

    return add_query_arg( $params, mgk_url( '/parent/review/' ) );
}

function mgk_parent_review_state() {
    $state = sanitize_key( mgk_get_query_filter( 'state', 'post-trial' ) );
    return in_array( $state, [ 'post-trial', 'post-package', 'submitted', 'not-eligible' ], true ) ? $state : 'post-trial';
}

function mgk_parent_review_teacher_context() {
    $slug = sanitize_title( mgk_get_query_filter( 'tutor', mgk_get_query_filter( 'teacher', 'ms-lee-yi-ling' ) ) );
    $post = $slug ? get_page_by_path( $slug, OBJECT, 'mg_teacher' ) : null;

    if ( ! $post ) {
        $post = get_page_by_path( 'ms-lee-yi-ling', OBJECT, 'mg_teacher' );
    }

    if ( $post ) {
        $short_name = get_post_meta( $post->ID, 'mgk_short_name', true );

        return [
            'id'         => (int) $post->ID,
            'slug'       => $post->post_name,
            'name'       => $short_name ?: $post->post_title,
            'full_name'  => $post->post_title,
            'profile_url'=> get_permalink( $post ),
        ];
    }

    return [
        'id'         => 0,
        'slug'       => 'ms-lee-yi-ling',
        'name'       => 'Ms Lee',
        'full_name'  => 'Ms Lee Yi Ling',
        'profile_url'=> mgk_teacher_profile_url( [ 'slug' => 'ms-lee-yi-ling' ] ),
    ];
}

/** Count ATTENDED/LATE logged lessons for a child with a given tutor (BR-20 gate). */
function mgk_review_attended_count( $child_id, $tutor_id ) {
    $child_id = (int) $child_id; $tutor_id = (int) $tutor_id;
    if ( ! $child_id || ! $tutor_id || ! post_type_exists( 'mg_lesson' ) ) return 0;
    $q = get_posts( [
        'post_type'   => 'mg_lesson',
        'numberposts' => -1,
        'fields'      => 'ids',
        'meta_query'  => [
            [ 'key' => 'mgk_lesson_child_id', 'value' => $child_id ],
            [ 'key' => 'mgk_lesson_tutor_id', 'value' => $tutor_id ],
            [ 'key' => 'mgk_lesson_attendance', 'value' => [ 'ATTENDED', 'LATE' ], 'compare' => 'IN' ],
        ],
    ] );
    return count( $q );
}

function mgk_parent_review_lesson_context( $teacher ) {
    // Demo fallback (preview / not a signed-in parent).
    $context = [
        'child_name'       => 'Emma',
        'subject'          => 'P5 Math',
        'trial_completed'  => '28 May',
        'package_meta'     => 'PKG 16 · 16 LESSONS · 14 DONE',
        'lessons_logged'   => 1,
        'parent_name'      => 'Verified Parent',
        'child_id'         => 0,
    ];

    $tid = (int) ( $teacher['id'] ?? 0 );
    if ( $tid && function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) {
        // REAL gate (BR-20): the parent may review only after ≥1 ATTENDED lesson
        // logged for one of their children with THIS tutor.
        $uid      = get_current_user_id();
        $children = function_exists( 'mgk_parent_children' ) ? mgk_parent_children( $uid ) : [];
        $best_child = 0; $best_count = 0;
        foreach ( $children as $c ) {
            $cid = (int) ( is_object( $c ) ? $c->ID : $c );
            $n   = mgk_review_attended_count( $cid, $tid );
            if ( $n > $best_count ) { $best_count = $n; $best_child = $cid; }
        }
        $cname = $best_child ? ( get_post_meta( $best_child, 'mgk_child_full_name', true ) ?: get_the_title( $best_child ) )
            : ( $children ? ( get_post_meta( (int) ( is_object( $children[0] ) ? $children[0]->ID : $children[0] ), 'mgk_child_full_name', true ) ?: 'your child' ) : 'your child' );
        $subject = '';
        if ( $best_child && function_exists( 'mgk_enrolment_for_child' ) ) {
            $enr = mgk_enrolment_for_child( $best_child );
            if ( $enr ) $subject = (string) get_post_meta( $enr, 'mgk_enr_subject', true );
        }
        $pname = function_exists( 'mgk_dashboard_parent_name' ) ? mgk_dashboard_parent_name( $uid ) : 'Verified Parent';

        $context = array_merge( $context, [
            'child_name'     => $cname,
            'subject'        => $subject ?: $context['subject'],
            'lessons_logged' => $best_count,
            'parent_name'    => $pname,
            'child_id'       => $best_child,
        ] );
    }

    return apply_filters( 'mgk_parent_review_lesson_context', $context, $teacher );
}

function mgk_parent_review_context() {
    $teacher = mgk_parent_review_teacher_context();
    $lesson = mgk_parent_review_lesson_context( $teacher );

    return [
        'state'       => mgk_parent_review_state(),
        'teacher'     => $teacher,
        'lesson'      => $lesson,
        'is_eligible' => (int) ( $lesson['lessons_logged'] ?? 0 ) >= 1,
    ];
}

function mgk_render_parent_review_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $context = mgk_parent_review_context();
    $preview_state = sanitize_key( (string) ( $atts['preview_state'] ?? '' ) );
    if ( in_array( $preview_state, [ 'post-trial', 'post-package', 'submitted', 'not-eligible' ], true ) ) {
        $context['state'] = $preview_state;
    }

    return mgk_render_part( 'template-parts/sections/review/' . $part, [
        'atts'    => $atts,
        'context' => $context,
    ] );
}

add_shortcode( 'mgk_parent_review', function ( $atts ) {
    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( [
            'preview_state'           => '',
            'hide_prompt'             => '',
            'hide_avatar'             => '',
            'sec_prompt'              => 'SEC 1 Prompt',
            'prompt_title'            => "How was {child}'s trial with {teacher}?",
            'prompt_meta'             => 'TRIAL COMPLETED {date} · PROMPT AVAILABLE 24H AFTER (FR-REVIEW-01)',
            'hide_post_trial'         => '',
            'sec_post_trial'          => 'SEC 2 Post-trial form',
            'rating_label'            => 'Your rating',
            'rating_hint'             => 'TAP TO RATE 1-5',
            'comment_label'           => 'Add a comment',
            'comment_optional'        => '(optional)',
            'comment_placeholder'     => 'WHAT WENT WELL? ANYTHING TO IMPROVE?',
            'submit_label'            => 'Submit review',
            'skip_label'              => 'Skip for now',
            'hide_post_package'       => '',
            'sec_post_package'        => '4 dimensions',
            'package_heading'         => 'Rate your full experience with {teacher}',
            'package_subline'         => 'PACKAGE COMPLETE · 16 LESSONS · {child} · {subject}',
            'dimension_1'             => 'Teaching',
            'dimension_2'             => 'Patience',
            'dimension_3'             => 'Punctuality',
            'dimension_4'             => 'Communication',
            'package_review_label'    => 'Your review',
            'package_review_optional' => '(free text)',
            'package_comment_placeholder' => 'SHARE YOUR EXPERIENCE FOR OTHER PARENTS_',
            'photo_heading'           => 'Add a photo',
            'photo_optional'          => '(optional)',
            'photo_label'             => '+',
            'photo_note'              => 'PARENT-PERMISSIONED · PDPA',
            'package_submit_label'    => 'Submit full review',
            'hide_moderation'         => '',
            'sec_moderation'          => 'SEC 4 Moderation',
            'moderation_notice'       => '● REVIEWS ARE SCREENED FOR ABUSE/PII BEFORE PUBLISHING (FR-REVIEW-03). PUBLISHED AS "VERIFIED PARENT · {subject}". PDPA: CHILD\'S NAME NEVER SHOWN PUBLICLY.',
            'hide_submitted'          => '',
            'sec_submitted'           => 'SEC 5 Submitted state',
            'submitted_title'         => 'Review submitted',
            'submitted_body'          => 'THANK YOU. YOUR REVIEW IS SENT FOR MODERATION BEFORE IT APPEARS ON THE TUTOR PROFILE.',
            'submitted_button'        => 'Back to tutor profile',
            'hide_not_eligible'       => '',
            'sec_not_eligible'        => 'SEC 6 Not-eligible state',
            'not_eligible_title'      => 'Review unlocks after the first logged lesson',
            'not_eligible_body'       => 'BR-20: PARENTS CAN ONLY REVIEW AFTER THE TUTOR LOGS AT LEAST 1 LESSON.',
            'not_eligible_button'     => 'Back to dashboard',
            'dashboard_url'           => '/parent/dashboard/',
            'bottom_note'             => 'BR-20 ELIGIBILITY + FR-REVIEW-03 MODERATION PROTECT TRUST SIGNALS ON S03.',
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_parent_review_part( 'review-page', $atts );
} );

add_action( 'admin_post_mgk_parent_review_submit', 'mgk_handle_parent_review_submit' );
add_action( 'admin_post_nopriv_mgk_parent_review_submit', 'mgk_handle_parent_review_submit' );

function mgk_handle_parent_review_submit() {
    if ( ! isset( $_POST['mgk_parent_review_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgk_parent_review_nonce'] ) ), 'mgk_parent_review_submit' ) ) {
        wp_die( esc_html__( 'Review session expired. Please try again.', 'mgk-edu' ), 403 );
    }

    $teacher_id = isset( $_POST['mgk_review_teacher_id'] ) ? (int) $_POST['mgk_review_teacher_id'] : 0;
    $teacher = $teacher_id ? get_post( $teacher_id ) : null;
    if ( ! $teacher || $teacher->post_type !== 'mg_teacher' ) {
        wp_safe_redirect( mgk_get_parent_review_url() );
        exit;
    }

    $teacher_context = [
        'id'         => (int) $teacher->ID,
        'slug'       => $teacher->post_name,
        'name'       => get_post_meta( $teacher->ID, 'mgk_short_name', true ) ?: $teacher->post_title,
        'full_name'  => $teacher->post_title,
        'profile_url'=> get_permalink( $teacher ),
    ];
    $lesson = mgk_parent_review_lesson_context( $teacher_context );
    if ( (int) ( $lesson['lessons_logged'] ?? 0 ) < 1 ) {
        wp_safe_redirect( add_query_arg( [
            'tutor' => $teacher->post_name,
            'state' => 'not-eligible',
        ], mgk_url( '/parent/review/' ) ) );
        exit;
    }

    $type = sanitize_key( wp_unslash( $_POST['mgk_review_type'] ?? 'post_trial' ) );
    $rating = isset( $_POST['mgk_review_rating'] ) ? (float) $_POST['mgk_review_rating'] : 4;
    $rating = max( 1, min( 5, $rating ) );
    $comment = sanitize_textarea_field( wp_unslash( $_POST['mgk_review_comment'] ?? $_POST['mgk_review_package_comment'] ?? '' ) );
    $parent_name = sanitize_text_field( (string) ( $lesson['parent_name'] ?? 'Verified Parent' ) );
    $subject = sanitize_text_field( (string) ( $lesson['subject'] ?? '' ) );

    $review_id = wp_insert_post( [
        'post_type'    => 'mg_review',
        'post_status'  => 'pending',
        'post_title'   => sprintf( 'Review for %s - %s', $teacher_context['name'], current_time( 'Y-m-d H:i' ) ),
        'post_content' => $comment,
    ] );

    if ( $review_id && ! is_wp_error( $review_id ) ) {
        update_post_meta( $review_id, 'mgk_review_teacher_id', $teacher->ID );
        update_post_meta( $review_id, 'mgk_review_parent_name', $parent_name );
        update_post_meta( $review_id, 'mgk_review_rating', $rating );
        update_post_meta( $review_id, 'mgk_review_meta', trim( 'Verified parent' . ( $subject ? ' · ' . $subject : '' ) ) );
        update_post_meta( $review_id, 'mgk_review_verified', '1' );
        update_post_meta( $review_id, 'mgk_review_type', $type );
        update_post_meta( $review_id, 'mgk_review_teaching', max( 1, min( 5, (float) ( $_POST['mgk_review_teaching'] ?? $rating ) ) ) );
        update_post_meta( $review_id, 'mgk_review_patience', max( 1, min( 5, (float) ( $_POST['mgk_review_patience'] ?? $rating ) ) ) );
        update_post_meta( $review_id, 'mgk_review_punctuality', max( 1, min( 5, (float) ( $_POST['mgk_review_punctuality'] ?? $rating ) ) ) );
        update_post_meta( $review_id, 'mgk_review_communication', max( 1, min( 5, (float) ( $_POST['mgk_review_communication'] ?? $rating ) ) ) );

        // Optional photo upload (real). Image-only, attached to the review post.
        $photo_count = 0;
        if ( ! empty( $_FILES['mgk_review_photo']['name'] ) && empty( $_FILES['mgk_review_photo']['error'] ) ) {
            $type = wp_check_filetype( (string) $_FILES['mgk_review_photo']['name'] );
            if ( strpos( (string) $type['type'], 'image/' ) === 0 ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                $att = media_handle_upload( 'mgk_review_photo', $review_id );
                if ( ! is_wp_error( $att ) ) {
                    $photo_count = 1;
                    update_post_meta( $review_id, 'mgk_review_photo_id', (int) $att );
                }
            }
        }
        update_post_meta( $review_id, 'mgk_review_photo_count', (string) $photo_count );
    }

    wp_safe_redirect( add_query_arg( [
        'tutor' => $teacher->post_name,
        'state' => 'submitted',
    ], mgk_url( '/parent/review/' ) ) );
    exit;
}
