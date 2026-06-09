<?php
/**
 * Seed weekly availability for all mg_teacher posts.
 * Run: wp eval-file seed/seed-availability.php --allow-root --path=/var/www/html
 */

// Availability patterns — varied by tutor type
$patterns = [
    // Part-time / weekday evenings
    'evenings' => [
        [ 'day' => 'Tue', 'slots' => '4pm-6pm, 7pm-9pm' ],
        [ 'day' => 'Thu', 'slots' => '4pm-6pm, 7pm-9pm' ],
        [ 'day' => 'Sat', 'slots' => '9am-11am, 2pm-4pm' ],
        [ 'day' => 'Sun', 'slots' => '10am-12pm' ],
    ],
    // Weekend-heavy
    'weekends' => [
        [ 'day' => 'Mon', 'slots' => '' ],
        [ 'day' => 'Wed', 'slots' => '7pm-9pm' ],
        [ 'day' => 'Sat', 'slots' => '9am-11am, 11am-1pm, 2pm-4pm, 5pm-7pm' ],
        [ 'day' => 'Sun', 'slots' => '9am-11am, 2pm-4pm, 5pm-7pm' ],
    ],
    // Full-time flexible
    'fulltime' => [
        [ 'day' => 'Mon', 'slots' => '10am-12pm, 2pm-4pm, 5pm-7pm' ],
        [ 'day' => 'Tue', 'slots' => '10am-12pm, 4pm-6pm' ],
        [ 'day' => 'Wed', 'slots' => '2pm-4pm, 5pm-7pm' ],
        [ 'day' => 'Thu', 'slots' => '10am-12pm, 4pm-6pm, 7pm-9pm' ],
        [ 'day' => 'Fri', 'slots' => '' ],
        [ 'day' => 'Sat', 'slots' => '9am-11am, 2pm-4pm' ],
        [ 'day' => 'Sun', 'slots' => '10am-12pm, 3pm-5pm' ],
    ],
    // Premium / limited slots
    'premium' => [
        [ 'day' => 'Tue', 'slots' => '5pm-7pm' ],
        [ 'day' => 'Thu', 'slots' => '5pm-7pm' ],
        [ 'day' => 'Sat', 'slots' => '9am-11am, 11am-1pm' ],
    ],
    // Mostly evenings + full Saturdays
    'mixed' => [
        [ 'day' => 'Mon', 'slots' => '7pm-9pm' ],
        [ 'day' => 'Wed', 'slots' => '4pm-6pm, 7pm-9pm' ],
        [ 'day' => 'Fri', 'slots' => '4pm-6pm' ],
        [ 'day' => 'Sat', 'slots' => '9am-11am, 11am-1pm, 2pm-4pm' ],
        [ 'day' => 'Sun', 'slots' => '10am-12pm, 2pm-4pm' ],
    ],
    // Ex-MOE daytime + some evenings
    'exmoe' => [
        [ 'day' => 'Mon', 'slots' => '9am-11am, 2pm-4pm, 5pm-7pm' ],
        [ 'day' => 'Tue', 'slots' => '9am-11am, 11am-1pm' ],
        [ 'day' => 'Wed', 'slots' => '2pm-4pm' ],
        [ 'day' => 'Thu', 'slots' => '9am-11am, 5pm-7pm' ],
        [ 'day' => 'Sat', 'slots' => '9am-11am, 11am-1pm, 2pm-4pm' ],
    ],
    // Online-only, flexible hours
    'online' => [
        [ 'day' => 'Mon', 'slots' => '8pm-10pm' ],
        [ 'day' => 'Tue', 'slots' => '6pm-8pm, 8pm-10pm' ],
        [ 'day' => 'Thu', 'slots' => '6pm-8pm' ],
        [ 'day' => 'Sat', 'slots' => '10am-12pm, 3pm-5pm, 7pm-9pm' ],
        [ 'day' => 'Sun', 'slots' => '10am-12pm, 2pm-4pm, 7pm-9pm' ],
    ],
];

// Map pattern to each teacher by tier / index
$tier_pattern = [
    'Part-time'     => 'evenings',
    'Full-time'     => 'fulltime',
    'NUS Part-time' => 'mixed',
    'Ex-MOE'        => 'exmoe',
    'IB Specialist' => 'weekends',
    'Premium'       => 'premium',
];

$teachers = get_posts( [
    'post_type'      => 'mg_teacher',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'ID',
    'order'          => 'ASC',
] );

$pattern_keys = array_keys( $patterns );
$updated = 0;

foreach ( $teachers as $idx => $post ) {
    $tier = get_post_meta( $post->ID, 'mgk_tier', true );
    $pattern_name = $tier_pattern[ $tier ] ?? $pattern_keys[ $idx % count( $pattern_keys ) ];
    $rows = $patterns[ $pattern_name ];

    if ( function_exists( 'update_field' ) ) {
        update_field( 'mgk_availability', $rows, $post->ID );
    } else {
        update_post_meta( $post->ID, 'mgk_availability', $rows );
    }

    $slot_count = array_sum( array_map( fn( $r ) => $r['slots'] ? count( explode( ',', $r['slots'] ) ) : 0, $rows ) );
    echo "  [{$post->ID}] {$post->post_title} → $pattern_name ($slot_count slots)\n";
    $updated++;
}

echo "\nDone. Updated $updated teachers.\n";
