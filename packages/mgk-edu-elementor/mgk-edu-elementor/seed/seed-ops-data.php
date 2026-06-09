<?php
/**
 * Seed operational sample data the owner can inspect in wp-admin:
 *   - mg_plan      : 4 plans (Trial / 8 / 16 / Single)
 *   - mg_parent    : 1 parent account
 *   - mg_child     : 2 children under that parent
 *   - mg_lead      : 1 lead (S07 enquiry)
 *   - mg_proposal  : 3 proposals for that lead (links to real tutors)
 *
 * Idempotent: keyed by post_title, re-running updates in place.
 * Field names match the ACF groups in inc/mgk-acf-fields.php.
 * 1 site = 1 tenant, so no tenant_id is stored.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Find-or-create a post by title within a CPT; returns post ID. */
if ( ! function_exists( 'mgk_seed_upsert' ) ) {
    function mgk_seed_upsert( $post_type, $title, $args = [] ) {
        $existing = get_posts( [
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'title'          => $title,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ] );

        $base = array_merge( [
            'post_type'   => $post_type,
            'post_title'  => $title,
            'post_status' => 'publish',
        ], $args );

        if ( $existing ) {
            $base['ID'] = (int) $existing[0];
            $id = wp_update_post( $base, true );
        } else {
            $id = wp_insert_post( $base, true );
        }
        return is_wp_error( $id ) ? 0 : (int) $id;
    }
}

/** Resolve the first term_id of a taxonomy by name (for level/subject FKs). */
if ( ! function_exists( 'mgk_seed_term_id' ) ) {
    function mgk_seed_term_id( $taxonomy, $name ) {
        $t = get_term_by( 'name', $name, $taxonomy );
        return $t ? (int) $t->term_id : 0;
    }
}

/* ── 1. Plans ───────────────────────────────────────────── */

$plans = [
    [
        'title' => 'Trial Lesson',
        'meta'  => [
            'mgk_plan_type'                 => 'TRIAL',
            'mgk_plan_lessons_count'        => 1,
            'mgk_plan_default_duration_min' => 60,
            'mgk_plan_discount_percent'     => 40,
            'mgk_plan_validity_days'        => 7,
            'mgk_plan_is_active'            => 1,
            'mgk_plan_sort_order'           => 1,
        ],
    ],
    [
        'title' => '8-Lesson Package',
        'meta'  => [
            'mgk_plan_type'                 => 'PACKAGE_8',
            'mgk_plan_lessons_count'        => 8,
            'mgk_plan_default_duration_min' => 90,
            'mgk_plan_discount_percent'     => 5,
            'mgk_plan_validity_days'        => 90,
            'mgk_plan_is_active'            => 1,
            'mgk_plan_sort_order'           => 2,
        ],
    ],
    [
        'title' => '16-Lesson Package',
        'meta'  => [
            'mgk_plan_type'                 => 'PACKAGE_16',
            'mgk_plan_lessons_count'        => 16,
            'mgk_plan_default_duration_min' => 90,
            'mgk_plan_discount_percent'     => 10,
            'mgk_plan_validity_days'        => 180,
            'mgk_plan_is_active'            => 1,
            'mgk_plan_sort_order'           => 3,
        ],
    ],
    [
        'title' => 'Single Lesson',
        'meta'  => [
            'mgk_plan_type'                 => 'SINGLE',
            'mgk_plan_lessons_count'        => 1,
            'mgk_plan_default_duration_min' => 60,
            'mgk_plan_discount_percent'     => 0,
            'mgk_plan_validity_days'        => 30,
            'mgk_plan_is_active'            => 1,
            'mgk_plan_sort_order'           => 4,
        ],
    ],
];

$plan_ids = [];
foreach ( $plans as $plan ) {
    $id = mgk_seed_upsert( 'mg_plan', $plan['title'] );
    if ( ! $id ) continue;
    foreach ( $plan['meta'] as $k => $v ) {
        update_post_meta( $id, $k, $v );
    }
    $plan_ids[ $plan['meta']['mgk_plan_type'] ] = $id;
}

/* ── 2. Parent account + 2 children ─────────────────────── */

$parent_id = mgk_seed_upsert( 'mg_parent', 'Mrs Chen Wei Ling' );
if ( $parent_id ) {
    update_post_meta( $parent_id, 'mgk_parent_email',             'chen.weiling@example.sg' );
    update_post_meta( $parent_id, 'mgk_parent_phone_e164',        '+6591234567' );
    update_post_meta( $parent_id, 'mgk_parent_full_name',         'Chen Wei Ling' );
    update_post_meta( $parent_id, 'mgk_parent_marketing_consent', 1 );
    update_post_meta( $parent_id, 'mgk_parent_pdpa_accepted_at',  '2026-05-20 09:15:00' );
    update_post_meta( $parent_id, 'mgk_parent_status',            'ACTIVE' );

    $children = [
        [
            'title'  => 'Chen Rui En',
            'level'  => 'P5-P6',
            'school' => 'Nanyang Primary School',
            'goals'  => 'PSLE Math foundation, reduce careless mistakes, build exam confidence.',
        ],
        [
            'title'  => 'Chen Rui Jie',
            'level'  => 'Sec 1-2',
            'school' => 'Raffles Institution',
            'goals'  => 'Strengthen Sec 1 Science fundamentals before streaming.',
        ],
    ];
    foreach ( $children as $child ) {
        $cid = mgk_seed_upsert( 'mg_child', $child['title'] );
        if ( ! $cid ) continue;
        update_post_meta( $cid, 'mgk_child_parent_id',      $parent_id );
        update_post_meta( $cid, 'mgk_child_full_name',      $child['title'] );
        $level_term = mgk_seed_term_id( 'mgk_level', $child['level'] );
        if ( $level_term ) {
            update_post_meta( $cid, 'mgk_child_current_level', $level_term );
        }
        update_post_meta( $cid, 'mgk_child_school_name',    $child['school'] );
        update_post_meta( $cid, 'mgk_child_learning_goals', $child['goals'] );
    }
}

/* ── 3. Lead (S07 enquiry) ──────────────────────────────── */

$lead_id = mgk_seed_upsert( 'mg_lead', 'Lead — Mrs Chen — P5 Math (sample)' );
if ( $lead_id ) {
    update_post_meta( $lead_id, 'mgk_lead_parent_email',        'chen.weiling@example.sg' );
    update_post_meta( $lead_id, 'mgk_lead_parent_phone',        '+6591234567' );
    update_post_meta( $lead_id, 'mgk_lead_child_name',          'Chen Rui En' );
    $lead_level   = mgk_seed_term_id( 'mgk_level', 'P5-P6' );
    $lead_subject = mgk_seed_term_id( 'mgk_subject', 'Math' );
    if ( $lead_level )   update_post_meta( $lead_id, 'mgk_lead_child_level', $lead_level );
    if ( $lead_subject ) update_post_meta( $lead_id, 'mgk_lead_subject',     $lead_subject );
    update_post_meta( $lead_id, 'mgk_lead_schedule_preference', 'Weekday evenings after 5pm, 1-2x/week' );
    update_post_meta( $lead_id, 'mgk_lead_budget_min_sgd',      50 );
    update_post_meta( $lead_id, 'mgk_lead_budget_max_sgd',      80 );
    update_post_meta( $lead_id, 'mgk_lead_location_type',       'EITHER' );
    update_post_meta( $lead_id, 'mgk_lead_location_area',       'Bishan / Central' );
    update_post_meta( $lead_id, 'mgk_lead_note',                'Prefers a patient tutor with PSLE track record. Trial first.' );
    update_post_meta( $lead_id, 'mgk_lead_marketing_consent',   1 );
    update_post_meta( $lead_id, 'mgk_lead_state',               'PROPOSED' );
    update_post_meta( $lead_id, 'mgk_lead_sla_due_at',          '2026-05-20 15:15:00' );
    // Stable magic-link token for the sample lead (real leads get a random one
    // from mgk_booking_create_lead). Lets you preview S08 at:
    //   /tutor-proposals/?token=mgk-sample-chen
    update_post_meta( $lead_id, 'mgk_lead_token',               'mgk-sample-chen' );
    // Proposals "sent" timestamp drives the 48h expiry countdown. Use a recent
    // fixed time so the sample stays inside the window when demoed.
    update_post_meta( $lead_id, 'mgk_proposals_sent_at',        time() - 600 );

    /* ── 4. Proposals for the lead (link to real tutors) ── */

    $proposals = [
        [ 'slug' => 'ms-sim-pei-hua',  'rank' => 1, 'score' => 94.0, 'rate' => 75,
          'reason' => 'Ex-MOE PSLE Math specialist with structured parent communication and a strong A/A* track record. Available weekday evenings.',
          'status' => 'PROPOSED' ],
        [ 'slug' => 'mr-tan-jun-wei',  'rank' => 2, 'score' => 88.5, 'rate' => 55,
          'reason' => 'Full-time tutor focused on careless-mistake reduction; sits within the budget and matches the evening schedule.',
          'status' => 'PROPOSED' ],
        [ 'slug' => 'ms-goh-ai-wei',   'rank' => 3, 'score' => 82.0, 'rate' => 45,
          'reason' => 'NUS Part-time, patient coach, lowest rate of the three — a relaxed option for the trial.',
          'status' => 'PROPOSED' ],
    ];

    foreach ( $proposals as $i => $p ) {
        $tutor = get_page_by_path( $p['slug'], OBJECT, 'mg_teacher' );
        if ( ! $tutor ) continue;
        $title = 'Proposal — ' . $tutor->post_title . ' for Mrs Chen (sample)';
        $pid = mgk_seed_upsert( 'mg_proposal', $title );
        if ( ! $pid ) continue;
        update_post_meta( $pid, 'mgk_prop_lead_id',                  $lead_id );
        update_post_meta( $pid, 'mgk_prop_tutor_id',                 $tutor->ID );
        update_post_meta( $pid, 'mgk_prop_rank_order',               $p['rank'] );
        update_post_meta( $pid, 'mgk_prop_match_score',              $p['score'] );
        update_post_meta( $pid, 'mgk_prop_match_reason',             $p['reason'] );
        if ( isset( $plan_ids['TRIAL'] ) ) {
            update_post_meta( $pid, 'mgk_prop_suggested_plan_id',    $plan_ids['TRIAL'] );
        }
        update_post_meta( $pid, 'mgk_prop_suggested_hourly_rate_sgd', $p['rate'] );
        update_post_meta( $pid, 'mgk_prop_status',                   $p['status'] );
        update_post_meta( $pid, 'mgk_prop_expires_at',               '2026-05-22 15:15:00' );
    }
}

echo "Seeded ops data: plans, parent+children, lead, proposals.\n";
