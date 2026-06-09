<?php
/**
 * Query tutors and listing filters from WordPress DB.
 * Replaces the hardcoded demo data for the listing page.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutors_from_db() {
    $query = new WP_Query( [
        'post_type'      => 'mg_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
        // BR-19: only show tutors explicitly marked as verified by agency
        'meta_query'     => [
            [ 'key' => 'mgk_is_verified', 'value' => '1' ],
        ],
    ] );

    $tutors = [];

    foreach ( $query->posts as $post ) {
        $subjects  = wp_get_post_terms( $post->ID, 'mgk_subject', [ 'fields' => 'names' ] );
        $levels    = wp_get_post_terms( $post->ID, 'mgk_level',   [ 'fields' => 'names' ] );
        $rate_num  = (int) get_post_meta( $post->ID, 'mgk_rate_num', true );
        $tags_raw  = get_post_meta( $post->ID, 'mgk_tags', true );
        $locations = get_post_meta( $post->ID, 'mgk_locations', true );
        $rating    = (string) get_post_meta( $post->ID, 'mgk_rating', true ) ?: '0';
        $reviews   = (string) get_post_meta( $post->ID, 'mgk_reviews', true ) ?: '0';

        if ( function_exists( 'mgk_get_teacher_reviews' ) && function_exists( 'mgk_summarize_teacher_reviews' ) ) {
            $review_summary = mgk_summarize_teacher_reviews( mgk_get_teacher_reviews( $post->ID ) );
            if ( ! empty( $review_summary['count'] ) ) {
                $rating  = (string) $review_summary['rating'];
                $reviews = (string) $review_summary['count'];
            }
        }

        $tutors[] = [
            'name'       => $post->post_title,
            'slug'       => $post->post_name,
            'tier'       => (string) get_post_meta( $post->ID, 'mgk_tier', true ),
            'experience' => (string) get_post_meta( $post->ID, 'mgk_experience', true ),
            'rating'     => $rating,
            'reviews'    => $reviews,
            'response'   => (string) get_post_meta( $post->ID, 'mgk_response', true ),
            'rate'       => $rate_num ? '$' . $rate_num . '/hr' : '',
            'rate_num'   => $rate_num,
            'trial'      => (string) get_post_meta( $post->ID, 'mgk_trial_price', true ),
            'subjects'   => is_array( $subjects ) ? $subjects : [],
            'levels'     => is_array( $levels )   ? $levels   : [],
            'locations'  => is_array( $locations ) ? $locations : ( $locations ? [ $locations ] : [] ),
            'tags'       => $tags_raw ? array_map( 'trim', explode( ',', $tags_raw ) ) : [],
            'bio'        => $post->post_excerpt ?: wp_strip_all_tags( $post->post_content ),
            'photo'      => (string) ( get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: '' ),
        ];
    }

    return $tutors;
}

function mgk_generate_listing_filters() {
    $groups = [];

    // Level — from mgk_level taxonomy
    $level_terms = get_terms( [ 'taxonomy' => 'mgk_level', 'hide_empty' => false ] );
    if ( ! is_wp_error( $level_terms ) && $level_terms ) {
        $groups['Level'] = array_map( function ( $t ) {
            return $t->name . ' (' . $t->count . ')';
        }, $level_terms );
    }

    // Subject — from mgk_subject taxonomy
    $subject_terms = get_terms( [ 'taxonomy' => 'mgk_subject', 'hide_empty' => false ] );
    if ( ! is_wp_error( $subject_terms ) && $subject_terms ) {
        $groups['Subject'] = array_map( function ( $t ) {
            return $t->name . ' (' . $t->count . ')';
        }, $subject_terms );
    }

    // Budget — fixed ranges
    $groups['Budget'] = [ '$30-$80/hr', '$50-$100/hr', '$80-$150/hr' ];

    // Location — aggregate from post meta
    $location_choices = [ 'Online', 'Central SG', 'East', 'West', 'North', 'NE', 'Home tuition' ];
    $location_counts  = [];
    foreach ( $location_choices as $loc ) {
        $count = ( new WP_Query( [
            'post_type'      => 'mg_teacher',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => false,
            'meta_query'     => [ [ 'key' => 'mgk_locations', 'value' => $loc, 'compare' => 'LIKE' ] ],
            'fields'         => 'ids',
        ] ) )->found_posts;
        if ( $count > 0 ) {
            $location_counts[] = $loc . ' (' . $count . ')';
        }
    }
    if ( $location_counts ) {
        $groups['Location'] = $location_counts;
    }

    // Tutor Tier — aggregate from post meta
    $tier_choices = [ 'Part-time', 'Full-time', 'NUS Part-time', 'Ex-MOE', 'IB Specialist', 'Premium' ];
    $tier_counts  = [];
    foreach ( $tier_choices as $tier ) {
        $count = ( new WP_Query( [
            'post_type'      => 'mg_teacher',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => false,
            'meta_query'     => [ [ 'key' => 'mgk_tier', 'value' => $tier, 'compare' => '=' ] ],
            'fields'         => 'ids',
        ] ) )->found_posts;
        if ( $count > 0 ) {
            $tier_counts[] = $tier . ' (' . $count . ')';
        }
    }
    if ( $tier_counts ) {
        $groups['Tutor Tier'] = $tier_counts;
    }

    // Rating — fixed options
    $groups['Rating'] = [ '5 stars', '4 stars and up', '3 stars and up' ];

    return $groups;
}
