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
        // DEPRECATED: parents are wp_users keyed by email, NOT this CPT. Menu is
        // hidden — the real "Parents" list lives in inc/auth/mgk-parent-identity.php.
        'show_in_menu'       => false,
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

/* ── Admin: surface Parent + Level on the Children list ──────────────────────
 * mg_child stores the parent as the meta `mgk_child_parent_user` (a wp_user id),
 * but the default post list shows no such column — so a child that DOES have a
 * parent looks parentless. These columns make the link visible (+ the level).
 */
add_filter( 'manage_mg_child_posts_columns', function ( $cols ) {
    $out = [];
    foreach ( $cols as $key => $label ) {
        $out[ $key ] = $label;
        if ( $key === 'title' ) {
            $out['mgk_child_parent'] = 'Parent';
            $out['mgk_child_level']  = 'Level';
        }
    }
    return $out;
} );

add_action( 'manage_mg_child_posts_custom_column', function ( $col, $post_id ) {
    if ( $col === 'mgk_child_parent' ) {
        $uid = (int) get_post_meta( $post_id, 'mgk_child_parent_user', true );
        $user = $uid ? get_user_by( 'id', $uid ) : null;
        if ( $user ) {
            $name = ( $user->display_name && $user->display_name !== $user->user_email ) ? $user->display_name . ' · ' : '';
            printf( '<a href="%s">%s%s</a>', esc_url( get_edit_user_link( $uid ) ), esc_html( $name ), esc_html( $user->user_email ) );
        } else {
            $email = get_post_meta( $post_id, 'mgk_child_parent_email', true );
            echo $email ? esc_html( $email ) . ' <em>(no account yet)</em>' : '<span style="color:#b32d2e;">— no parent —</span>';
        }
    } elseif ( $col === 'mgk_child_level' ) {
        $lvl = get_post_meta( $post_id, 'mgk_child_current_level', true );
        if ( is_numeric( $lvl ) ) {
            $term = get_term( (int) $lvl );
            $lvl  = ( $term && ! is_wp_error( $term ) ) ? $term->name : $lvl;
        }
        echo $lvl ? esc_html( $lvl ) : '—';
    }
}, 10, 2 );

/* ── Admin: surface Child / Parent / Subject / State on the Leads list ───────
 * A request (S07) creates a mg_lead holding the child name + parent email until
 * the booking completes (the real mg_child is created then, or when a signed-in
 * parent adds a child). Without columns the list shows only post titles, so a
 * just-submitted request looks invisible. These make every request scannable.
 */
add_filter( 'manage_mg_lead_posts_columns', function ( $cols ) {
    $date = $cols['date'] ?? null;
    unset( $cols['date'] );
    $cols['mgk_lead_child']   = 'Child';
    $cols['mgk_lead_parent']  = 'Parent';
    $cols['mgk_lead_need']    = 'Subject · Level';
    $cols['mgk_lead_state']   = 'State';
    if ( $date !== null ) $cols['date'] = $date;
    return $cols;
} );

add_action( 'manage_mg_lead_posts_custom_column', function ( $col, $post_id ) {
    switch ( $col ) {
        case 'mgk_lead_child':
            echo esc_html( get_post_meta( $post_id, 'mgk_lead_child_name', true ) ?: '—' );
            break;
        case 'mgk_lead_parent':
            $email = get_post_meta( $post_id, 'mgk_lead_email', true );
            $name  = get_post_meta( $post_id, 'mgk_lead_parent_name', true );
            $uid   = (int) get_post_meta( $post_id, 'mgk_lead_parent_user_id', true );
            echo esc_html( trim( ( $name ? $name . ' · ' : '' ) . $email ) ?: '—' );
            if ( $uid ) echo ' <span style="color:#1a7f37;" title="Linked account">✓</span>';
            break;
        case 'mgk_lead_need':
            $s = get_post_meta( $post_id, 'mgk_lead_subject', true );
            $l = get_post_meta( $post_id, 'mgk_lead_level', true );
            echo esc_html( trim( implode( ' · ', array_filter( [ $s, $l ] ) ) ) ?: '—' );
            break;
        case 'mgk_lead_state':
            echo esc_html( get_post_meta( $post_id, 'mgk_lead_state', true ) ?: '—' );
            break;
    }
}, 10, 2 );
