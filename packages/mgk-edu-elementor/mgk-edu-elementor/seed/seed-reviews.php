<?php
/**
 * Seed verified tutor reviews as real mg_review posts linked to mg_teacher.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$review_sets = [
    'ms-lee-yi-ling' => [
        [ 'Mrs Chen', 5, 'Verified · P5 Math · 2 weeks ago', 'Ms Lee explains concepts clearly and keeps the lesson focused. The post-lesson notes helped us see exactly what to practise.', 5, 5, 5, 5, 'PSLE, P5, With photos', 2 ],
        [ 'Mr Kumar', 5, 'Verified · PSLE Math · 1 month ago', 'The feedback after trial was specific, practical, and easy to act on. My son felt less anxious after the first lesson.', 5, 5, 4.8, 5, 'PSLE, P5', 0 ],
        [ 'Mrs Yeo', 5, 'Verified · P5 · 3 weeks ago', 'Good pace, calm teaching style, and clear homework follow-up. The lesson summary was useful for parents.', 4.8, 5, 4.8, 4.9, 'P5', 0 ],
        [ 'Mr Lim', 4, 'Verified · P6 Science · 2 months ago', 'Structured explanations and neat examples. The trial gave us enough confidence to continue weekly.', 4.7, 4.8, 4.6, 4.8, 'PSLE', 0 ],
    ],
    'ms-wong-shu-fen' => [
        [ 'Mrs Tan', 5, 'Verified · Sec 1 Science · 3 weeks ago', 'Patient and clear. She adjusted the examples to my daughter’s level quickly.', 5, 5, 4.8, 4.9, 'Sec 1, Science', 1 ],
        [ 'Mr Ho', 4.8, 'Verified · Foundation Math · 1 month ago', 'Very organised teacher and easy to communicate with.', 4.8, 4.9, 4.8, 4.8, 'Foundation', 0 ],
    ],
    'ms-tan-xiao-ling' => [
        [ 'Mrs Lee', 5, 'Verified · O-Level Malay · 2 weeks ago', 'Dedicated and strong with bilingual explanation. The lesson plan was clear.', 5, 5, 5, 5, 'O-Level, Malay', 1 ],
        [ 'Mr Ong', 4.8, 'Verified · English · 1 month ago', 'Helpful feedback and practical writing tips.', 4.8, 4.9, 4.8, 4.8, 'English', 0 ],
    ],
];

foreach ( $review_sets as $teacher_slug => $reviews ) {
    $teacher = get_page_by_path( $teacher_slug, OBJECT, 'mg_teacher' );
    if ( ! $teacher ) {
        continue;
    }

    foreach ( $reviews as $review ) {
        [ $parent_name, $rating, $meta, $copy, $teaching, $patience, $punctuality, $communication, $tags, $photos ] = $review;
        $title = $parent_name . ' review for ' . $teacher->post_title;

        $existing = get_posts( [
            'post_type'      => 'mg_review',
            'post_status'    => 'any',
            'title'          => $title,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ] );

        $post_id = $existing ? (int) $existing[0] : wp_insert_post( [
            'post_type'    => 'mg_review',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_content' => $copy,
        ] );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }

        wp_update_post( [
            'ID'           => $post_id,
            'post_status'  => 'publish',
            'post_content' => $copy,
        ] );

        update_post_meta( $post_id, 'mgk_review_teacher_id', $teacher->ID );
        update_post_meta( $post_id, 'mgk_review_parent_name', $parent_name );
        update_post_meta( $post_id, 'mgk_review_rating', $rating );
        update_post_meta( $post_id, 'mgk_review_meta', $meta );
        update_post_meta( $post_id, 'mgk_review_verified', 1 );
        update_post_meta( $post_id, 'mgk_review_teaching', $teaching );
        update_post_meta( $post_id, 'mgk_review_patience', $patience );
        update_post_meta( $post_id, 'mgk_review_punctuality', $punctuality );
        update_post_meta( $post_id, 'mgk_review_communication', $communication );
        update_post_meta( $post_id, 'mgk_review_tags', $tags );
        update_post_meta( $post_id, 'mgk_review_photo_count', $photos );
    }
}

echo "Seeded tutor reviews.\n";
