<?php
/**
 * schemas/edu.php — CPT + ACF + Shortcodes cho category: edu
 * Template: mgk-edu-001
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'mg_teacher', [
        'labels' => [
            'name'          => __( 'MGK Tutors', 'mgk-edu' ),
            'singular_name' => __( 'MGK Tutor', 'mgk-edu' ),
        ],
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'rewrite'      => [
            'slug'       => 'teacher',
            'with_front' => false,
        ],
        'menu_icon'    => 'dashicons-welcome-learn-more',
    ] );

    register_post_type( 'mg_review', [
        'labels' => [
            'name'          => __( 'MGK Reviews', 'mgk-edu' ),
            'singular_name' => __( 'MGK Review', 'mgk-edu' ),
            'add_new_item'  => __( 'Add Tutor Review', 'mgk-edu' ),
            'edit_item'     => __( 'Edit Tutor Review', 'mgk-edu' ),
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'exclude_from_search' => true,
        'supports'            => [ 'title', 'editor', 'thumbnail' ],
        'menu_icon'           => 'dashicons-star-filled',
    ] );

    register_taxonomy( 'mgk_subject', [ 'mg_teacher' ], [
        'labels' => [
            'name'          => __( 'MGK Subjects', 'mgk-edu' ),
            'singular_name' => __( 'MGK Subject', 'mgk-edu' ),
        ],
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'rewrite'           => [ 'slug' => 'teacher-subject' ],
    ] );

    register_taxonomy( 'mgk_level', [ 'mg_teacher' ], [
        'labels' => [
            'name'          => __( 'MGK Levels', 'mgk-edu' ),
            'singular_name' => __( 'MGK Level', 'mgk-edu' ),
        ],
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'rewrite'           => [ 'slug' => 'teacher-level' ],
    ] );
}, 10 );
