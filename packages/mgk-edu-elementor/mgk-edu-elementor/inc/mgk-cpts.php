<?php
/**
 * MGK operations CPTs.
 * All CPTs are private (no public URLs) — admin backend records only.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {

    /* ── mg_lead — parent enquiry, pre-payment ───────────── */
    register_post_type( 'mg_lead', [
        'labels' => [
            'name'               => 'Leads',
            'singular_name'      => 'Lead',
            'menu_name'          => 'Leads',
            'add_new_item'       => 'Add Lead',
            'edit_item'          => 'Edit Lead',
            'search_items'       => 'Search Leads',
            'not_found'          => 'No leads found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-email',
        'menu_position'      => 30,
        'capability_type'    => 'post',
    ] );

    /* ── mg_booking — confirmed booking, WC order linkage ── */
    register_post_type( 'mg_booking', [
        'labels' => [
            'name'               => 'Bookings',
            'singular_name'      => 'Booking',
            'menu_name'          => 'Bookings',
            'add_new_item'       => 'Add Booking',
            'edit_item'          => 'Edit Booking',
            'search_items'       => 'Search Bookings',
            'not_found'          => 'No bookings found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-calendar-alt',
        'menu_position'      => 31,
        'capability_type'    => 'post',
    ] );

    /* ── mg_lesson — individual lesson session log ───────── */
    register_post_type( 'mg_lesson', [
        'labels' => [
            'name'               => 'Lessons',
            'singular_name'      => 'Lesson',
            'menu_name'          => 'Lessons',
            'add_new_item'       => 'Add Lesson',
            'edit_item'          => 'Edit Lesson',
            'search_items'       => 'Search Lessons',
            'not_found'          => 'No lessons found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-book-alt',
        'menu_position'      => 32,
        'capability_type'    => 'post',
    ] );

    /* ── mg_plan — recurring tuition plan ───────────────── */
    register_post_type( 'mg_plan', [
        'labels' => [
            'name'               => 'Plans',
            'singular_name'      => 'Plan',
            'menu_name'          => 'Plans',
            'add_new_item'       => 'Add Plan',
            'edit_item'          => 'Edit Plan',
            'search_items'       => 'Search Plans',
            'not_found'          => 'No plans found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-clipboard',
        'menu_position'      => 33,
        'capability_type'    => 'post',
    ] );

    /* ── mg_parent — parent account (post-payment) ────────── */
    register_post_type( 'mg_parent', [
        'labels' => [
            'name'               => 'Parent Accounts',
            'singular_name'      => 'Parent Account',
            'menu_name'          => 'Parents',
            'add_new_item'       => 'Add Parent Account',
            'edit_item'          => 'Edit Parent Account',
            'search_items'       => 'Search Parents',
            'not_found'          => 'No parent accounts found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-groups',
        'menu_position'      => 34,
        'capability_type'    => 'post',
    ] );

    /* ── mg_child — a child under a parent account ────────── */
    register_post_type( 'mg_child', [
        'labels' => [
            'name'               => 'Children',
            'singular_name'      => 'Child',
            'menu_name'          => 'Children',
            'add_new_item'       => 'Add Child',
            'edit_item'          => 'Edit Child',
            'search_items'       => 'Search Children',
            'not_found'          => 'No children found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-buddicons-buddypress-logo',
        'menu_position'      => 35,
        'capability_type'    => 'post',
    ] );

    /* ── mg_proposal — tutor match proposal for a lead ────── */
    register_post_type( 'mg_proposal', [
        'labels' => [
            'name'               => 'Proposals',
            'singular_name'      => 'Proposal',
            'menu_name'          => 'Proposals',
            'add_new_item'       => 'Add Proposal',
            'edit_item'          => 'Edit Proposal',
            'search_items'       => 'Search Proposals',
            'not_found'          => 'No proposals found.',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'supports'           => [ 'title', 'custom-fields' ],
        'menu_icon'          => 'dashicons-randomize',
        'menu_position'      => 36,
        'capability_type'    => 'post',
    ] );

}, 10 );
