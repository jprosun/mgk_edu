<?php
/**
 * Seed script: 15 teachers with full ACF meta, subjects, levels.
 * Run: wp eval-file seed/seed-teachers.php --allow-root --path=/var/www/html
 */

// ── Taxonomy terms ───────────────────────────────────────────────────────────

$subjects = [
    'Math', 'English', 'Chinese', 'Science', 'Chemistry',
    'Physics', 'A-Math', 'E-Math', 'H2 Math', 'H2 Chemistry',
    'H2 Physics', 'Biology', 'GP', 'History', 'Higher Chinese',
];

$levels = [ 'P1-P4', 'P5-P6', 'Sec 1-2', 'Sec 3-4', 'JC', 'IB' ];

foreach ( $subjects as $s ) {
    if ( ! term_exists( $s, 'mgk_subject' ) ) {
        wp_insert_term( $s, 'mgk_subject' );
    }
}
foreach ( $levels as $l ) {
    if ( ! term_exists( $l, 'mgk_level' ) ) {
        wp_insert_term( $l, 'mgk_level' );
    }
}

echo "Terms created.\n";

// ── Teacher data ─────────────────────────────────────────────────────────────

$teachers = [
    [
        'name'        => 'Ms. Lee Yi Ling',
        'bio'         => 'Former MOE teacher with a structured PSLE problem-solving method and weekly parent progress updates.',
        'tier'        => 'Ex-MOE',
        'experience'  => '8y',
        'rate_num'    => 65,
        'trial'       => '$40',
        'rating'      => '4.9',
        'reviews'     => '87',
        'response'    => '4h',
        'locations'   => [ 'Central SG', 'Online', 'Home tuition' ],
        'tags'        => 'P5-P6 Math, PSLE Sci, Online OK, Demo',
        'subjects'    => [ 'Math', 'Science' ],
        'levels'      => [ 'P5-P6' ],
    ],
    [
        'name'        => 'Mr. Tan Jun Wei',
        'bio'         => 'Full-time tutor focused on exam drills, careless mistake reduction, and confidence building for O-Level.',
        'tier'        => 'Full-time',
        'experience'  => '5y',
        'rate_num'    => 55,
        'trial'       => '$35',
        'rating'      => '4.7',
        'reviews'     => '124',
        'response'    => '2h',
        'locations'   => [ 'Central SG', 'East', 'Online' ],
        'tags'        => 'A-Math, E-Math, O-Level, Demo',
        'subjects'    => [ 'A-Math', 'E-Math', 'Math' ],
        'levels'      => [ 'Sec 3-4' ],
    ],
    [
        'name'        => 'Ms. Goh Ai Wei',
        'bio'         => 'NUS undergraduate with patient coaching style. Clear step-by-step explanations for Primary Math and English.',
        'tier'        => 'NUS Part-time',
        'experience'  => '3y',
        'rate_num'    => 45,
        'trial'       => '$30',
        'rating'      => '4.8',
        'reviews'     => '62',
        'response'    => '6h',
        'locations'   => [ 'East', 'Online' ],
        'tags'        => 'P3-P6 Math, English, Patient coach',
        'subjects'    => [ 'Math', 'English' ],
        'levels'      => [ 'P1-P4', 'P5-P6' ],
    ],
    [
        'name'        => 'Mr. Wong Kai Ming',
        'bio'         => 'PhD holder specialising in stretch problems and advanced exam strategy for top-scoring JC students.',
        'tier'        => 'Premium',
        'experience'  => '12y',
        'rate_num'    => 120,
        'trial'       => '$80',
        'rating'      => '5.0',
        'reviews'     => '45',
        'response'    => '1h',
        'locations'   => [ 'Central SG', 'Online' ],
        'tags'        => 'H2 Math, H2 Chem, PSLE specialist, 87% A* rate',
        'subjects'    => [ 'H2 Math', 'H2 Chemistry', 'Chemistry' ],
        'levels'      => [ 'JC', 'Sec 3-4' ],
    ],
    [
        'name'        => 'Ms. Sim Pei Hua',
        'bio'         => 'Bilingual ex-MOE specialist. Structured parent communication every 2 weeks with detailed progress reports.',
        'tier'        => 'Ex-MOE',
        'experience'  => '10y',
        'rate_num'    => 75,
        'trial'       => '$50',
        'rating'      => '4.9',
        'reviews'     => '102',
        'response'    => '3h',
        'locations'   => [ 'West', 'Online', 'Home tuition' ],
        'tags'        => 'P5-P6 Math, Higher Chinese, Bilingual, Online OK',
        'subjects'    => [ 'Math', 'Chinese', 'Higher Chinese' ],
        'levels'      => [ 'P5-P6', 'Sec 1-2' ],
    ],
    [
        'name'        => 'Mr. Daniel Chen',
        'bio'         => 'NTU Physics PhD. Uses visual derivations and exam-note systems that consistently produce A/B results.',
        'tier'        => 'Premium',
        'experience'  => '11y',
        'rate_num'    => 95,
        'trial'       => '$65',
        'rating'      => '4.8',
        'reviews'     => '54',
        'response'    => '2h',
        'locations'   => [ 'West', 'Online' ],
        'tags'        => 'H2 Physics, H2 Math, Demo video',
        'subjects'    => [ 'H2 Physics', 'Physics', 'H2 Math' ],
        'levels'      => [ 'JC', 'IB' ],
    ],
    [
        'name'        => 'Ms. Cheryl Lim',
        'bio'         => 'Language tutor who builds oral confidence and composition structure for PSLE Chinese and English.',
        'tier'        => 'Ex-MOE',
        'experience'  => '9y',
        'rate_num'    => 70,
        'trial'       => '$45',
        'rating'      => '4.9',
        'reviews'     => '98',
        'response'    => '3h',
        'locations'   => [ 'East', 'Online' ],
        'tags'        => 'PSLE Chinese, Bilingual, Oral coaching',
        'subjects'    => [ 'Chinese', 'English', 'Higher Chinese' ],
        'levels'      => [ 'P1-P4', 'P5-P6' ],
    ],
    [
        'name'        => 'Mr. Raj Kumar',
        'bio'         => 'Engaging Science tutor known for real-world demonstrations. 92% of students improve by at least 1 grade.',
        'tier'        => 'Full-time',
        'experience'  => '7y',
        'rate_num'    => 60,
        'trial'       => '$40',
        'rating'      => '4.8',
        'reviews'     => '76',
        'response'    => '3h',
        'locations'   => [ 'Central SG', 'North', 'Online' ],
        'tags'        => 'Sec Science, Chemistry, Biology, 92% improvement',
        'subjects'    => [ 'Science', 'Chemistry', 'Biology' ],
        'levels'      => [ 'Sec 1-2', 'Sec 3-4' ],
    ],
    [
        'name'        => 'Ms. Sarah Tan',
        'bio'         => 'Former editor turned English tutor. Specialises in comprehension, essay writing, and oral exam techniques.',
        'tier'        => 'Full-time',
        'experience'  => '6y',
        'rate_num'    => 55,
        'trial'       => '$35',
        'rating'      => '4.7',
        'reviews'     => '89',
        'response'    => '4h',
        'locations'   => [ 'Central SG', 'East', 'Online', 'Home tuition' ],
        'tags'        => 'O-Level English, Essay, Comprehension',
        'subjects'    => [ 'English' ],
        'levels'      => [ 'Sec 1-2', 'Sec 3-4' ],
    ],
    [
        'name'        => 'Mr. Lim Boon Kiat',
        'bio'         => 'Ex-Hwa Chong JC lecturer. Covers H2 Math from first principles with exam-pattern drilling.',
        'tier'        => 'Ex-MOE',
        'experience'  => '15y',
        'rate_num'    => 100,
        'trial'       => '$70',
        'rating'      => '4.9',
        'reviews'     => '67',
        'response'    => '5h',
        'locations'   => [ 'Central SG', 'North', 'Online' ],
        'tags'        => 'H2 Math, A-Level, Ex-JC lecturer',
        'subjects'    => [ 'H2 Math', 'Math' ],
        'levels'      => [ 'JC', 'Sec 3-4' ],
    ],
    [
        'name'        => 'Ms. Fatimah Binte Ali',
        'bio'         => 'Warm and encouraging tutor for young learners. Specialises in foundational Malay and Primary English.',
        'tier'        => 'Part-time',
        'experience'  => '4y',
        'rate_num'    => 40,
        'trial'       => '$25',
        'rating'      => '4.6',
        'reviews'     => '33',
        'response'    => '8h',
        'locations'   => [ 'North', 'NE', 'Online' ],
        'tags'        => 'Malay, English, P1-P4, Young learners',
        'subjects'    => [ 'English' ],
        'levels'      => [ 'P1-P4' ],
    ],
    [
        'name'        => 'Mr. Chen Wei Jie',
        'bio'         => 'NUS Physics undergrad who makes complex concepts intuitive. Strong track record at O and A Level.',
        'tier'        => 'NUS Part-time',
        'experience'  => '2y',
        'rate_num'    => 50,
        'trial'       => '$30',
        'rating'      => '4.7',
        'reviews'     => '28',
        'response'    => '5h',
        'locations'   => [ 'West', 'Online' ],
        'tags'        => 'Physics, A-Math, O-Level, A-Level',
        'subjects'    => [ 'Physics', 'A-Math', 'H2 Physics' ],
        'levels'      => [ 'Sec 3-4', 'JC' ],
    ],
    [
        'name'        => 'Ms. Ho Mei Ling',
        'bio'         => 'IB specialist with 13 years experience. Covers HL and SL across Math, Chemistry and Biology.',
        'tier'        => 'IB Specialist',
        'experience'  => '13y',
        'rate_num'    => 130,
        'trial'       => '$90',
        'rating'      => '5.0',
        'reviews'     => '41',
        'response'    => '2h',
        'locations'   => [ 'Central SG', 'Online' ],
        'tags'        => 'IB Math, IB Chemistry, IB Biology, HL/SL',
        'subjects'    => [ 'H2 Math', 'H2 Chemistry', 'Biology' ],
        'levels'      => [ 'IB', 'JC' ],
    ],
    [
        'name'        => 'Mr. Xavier Loh',
        'bio'         => 'Exam-focused Sec Math tutor. Breaks down A-Math and E-Math into clear weekly study plans.',
        'tier'        => 'Full-time',
        'experience'  => '5y',
        'rate_num'    => 55,
        'trial'       => '$35',
        'rating'      => '4.8',
        'reviews'     => '58',
        'response'    => '3h',
        'locations'   => [ 'East', 'NE', 'Online' ],
        'tags'        => 'A-Math, E-Math, Sec 3-4, Study plan',
        'subjects'    => [ 'A-Math', 'E-Math' ],
        'levels'      => [ 'Sec 1-2', 'Sec 3-4' ],
    ],
    [
        'name'        => 'Ms. Patricia Ng',
        'bio'         => 'Dedicated Chinese language tutor with proven oral and composition coaching. MOE-trained, 16 years.',
        'tier'        => 'Ex-MOE',
        'experience'  => '16y',
        'rate_num'    => 80,
        'trial'       => '$55',
        'rating'      => '4.9',
        'reviews'     => '115',
        'response'    => '4h',
        'locations'   => [ 'North', 'Central SG', 'Online', 'Home tuition' ],
        'tags'        => 'Chinese, Higher Chinese, Oral, O-Level Chinese',
        'subjects'    => [ 'Chinese', 'Higher Chinese' ],
        'levels'      => [ 'P5-P6', 'Sec 1-2', 'Sec 3-4' ],
    ],
];

// ── Insert teachers ───────────────────────────────────────────────────────────

$created = 0;

foreach ( $teachers as $t ) {
    // Skip if already exists
    $existing = get_posts( [
        'post_type'   => 'mg_teacher',
        'post_status' => 'publish',
        'title'       => $t['name'],
        'numberposts' => 1,
    ] );
    if ( $existing ) {
        echo "Skip (exists): {$t['name']}\n";
        continue;
    }

    $post_id = wp_insert_post( [
        'post_type'    => 'mg_teacher',
        'post_status'  => 'publish',
        'post_title'   => $t['name'],
        'post_name'    => sanitize_title( $t['name'] ),
        'post_excerpt' => $t['bio'],
        'post_content' => $t['bio'],
    ] );

    if ( is_wp_error( $post_id ) ) {
        echo "ERROR: {$t['name']} — " . $post_id->get_error_message() . "\n";
        continue;
    }

    // ACF / post meta
    update_post_meta( $post_id, 'mgk_tier',        $t['tier'] );
    update_post_meta( $post_id, 'mgk_experience',  $t['experience'] );
    update_post_meta( $post_id, 'mgk_rate_num',    $t['rate_num'] );
    update_post_meta( $post_id, 'mgk_trial_price', $t['trial'] );
    update_post_meta( $post_id, 'mgk_rating',      $t['rating'] );
    update_post_meta( $post_id, 'mgk_reviews',     $t['reviews'] );
    update_post_meta( $post_id, 'mgk_response',    $t['response'] );
    update_post_meta( $post_id, 'mgk_locations',   $t['locations'] );
    update_post_meta( $post_id, 'mgk_tags',        $t['tags'] );
    update_post_meta( $post_id, 'mgk_bio',         $t['bio'] );

    // Subjects taxonomy
    $subject_ids = [];
    foreach ( $t['subjects'] as $subj ) {
        $term = get_term_by( 'name', $subj, 'mgk_subject' );
        if ( $term ) $subject_ids[] = $term->term_id;
    }
    if ( $subject_ids ) wp_set_post_terms( $post_id, $subject_ids, 'mgk_subject' );

    // Levels taxonomy
    $level_ids = [];
    foreach ( $t['levels'] as $lvl ) {
        $term = get_term_by( 'name', $lvl, 'mgk_level' );
        if ( $term ) $level_ids[] = $term->term_id;
    }
    if ( $level_ids ) wp_set_post_terms( $post_id, $level_ids, 'mgk_level' );

    echo "Created: {$t['name']} (ID {$post_id})\n";
    $created++;
}

echo "\nDone. Created {$created} teachers.\n";
