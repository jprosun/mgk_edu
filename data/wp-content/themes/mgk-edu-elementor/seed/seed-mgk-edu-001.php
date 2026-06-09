<?php
/**
 * seed/seed-mgk-edu-001.php
 * Chạy tự động khi activate theme lần đầu trên Margick pod.
 * PHẢI idempotent — kiểm tra tồn tại trước khi insert.
 */

function mgk_seed_page( $title, $slug, $parent_id = 0 ) {
    $path = $parent_id ? get_page_uri( $parent_id ) . '/' . $slug : $slug;
    $existing = get_page_by_path( $path );

    if ( $existing ) {
        return (int) $existing->ID;
    }

    return (int) wp_insert_post( [
        'post_type'   => 'page',
        'post_title'  => $title,
        'post_name'   => $slug,
        'post_parent' => $parent_id,
        'post_status' => 'publish',
    ] );
}

// Home page — front page. front-page.php renders it; seed-layouts.php fills its
// Elementor layout so it is editable in the Elementor editor. Pointing the front
// page here also retires any leftover demo page.
$home_id = mgk_seed_page( 'Home', 'home' );

$student_id = mgk_seed_page( 'Student', 'student' );
mgk_seed_page( 'Teachers', 'teachers', $student_id );

$parent_id = mgk_seed_page( 'Parent', 'parent' );
mgk_seed_page( 'Dashboard', 'dashboard', $parent_id );
$messages_id = mgk_seed_page( 'Messages', 'messages', $parent_id );
mgk_seed_page( 'Empty', 'empty', $messages_id );
mgk_seed_page( 'Report', 'report', $messages_id );
$trial_id = mgk_seed_page( 'Trial', 'trial', $parent_id );
mgk_seed_page( 'Switch', 'switch', $trial_id );
mgk_seed_page( 'End', 'end', $trial_id );
mgk_seed_page( 'Lapsed', 'lapsed', $trial_id );
mgk_seed_page( 'Proposals', 'proposals', $parent_id );
mgk_seed_page( 'Proposal States', 'proposal-states' );
mgk_seed_page( 'Review', 'review', $parent_id );
mgk_seed_page( 'Referrals', 'referrals', $parent_id );
mgk_seed_page( 'Account', 'account', $parent_id );
mgk_seed_page( 'Notifications', 'notifications', $parent_id );

mgk_seed_page( 'Subjects', 'subjects' );
mgk_seed_page( 'How It Works', 'how-it-works' );
mgk_seed_page( 'Pricing', 'pricing' );
mgk_seed_page( 'Become a Tutor', 'become-a-tutor' );
$tutor_id = mgk_seed_page( 'Tutor', 'tutor' );
mgk_seed_page( 'Verification', 'verification', $tutor_id );
mgk_seed_page( 'Dashboard', 'dashboard', $tutor_id );
mgk_seed_page( 'Lesson Log', 'lesson-log', $tutor_id );
mgk_seed_page( 'Earnings', 'earnings', $tutor_id );
mgk_seed_page( 'Schedule', 'schedule', $tutor_id );

if ( get_option( 'show_on_front' ) !== 'page' ) {
    update_option( 'show_on_front', 'page' );
}

// Point front page to our Home page (only if not already set to it).
if ( $home_id && (int) get_option( 'page_on_front' ) !== $home_id ) {
    update_option( 'page_on_front', $home_id );
}

if ( class_exists( 'WooCommerce' ) ) {
    update_option( 'woocommerce_coming_soon', 'no' );
}
