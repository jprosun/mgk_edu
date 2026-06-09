<?php
/**
 * S21 tutor dashboard shell.
 *
 * Jobs, lesson logs, earnings, payout and message data are DATA CORE. Elementor
 * edits shell labels, buttons, visibility and style only.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_dashboard_url() {
    return mgk_url( '/tutor/dashboard/' );
}

function mgk_tutor_dashboard_context() {
    $lesson_log_url = function_exists( 'mgk_get_tutor_lesson_log_url' ) ? mgk_get_tutor_lesson_log_url() : mgk_url( '/tutor/lesson-log/' );
    $earnings_url   = function_exists( 'mgk_get_tutor_earnings_url' ) ? mgk_get_tutor_earnings_url() : mgk_url( '/tutor/earnings/' );

    $context = [
        'tutor' => [
            'name' => 'Ms Lee Yi Ling',
            'meta' => 'MON 3 JUN · 2 LESSONS TODAY · ★4.9 · ACTIVE TUTOR',
        ],
        'job' => [
            'subject' => 'P5 Math',
            'badge' => '1 NEW',
            'sla' => '⏳ 18H LEFT · SLA 24H',
            'body' => '2×/WEEK · 1.5H · $65/H · TUE + SAT EVE · PROPOSED BY AGENCY OPS',
            'note' => 'NO RESPONSE IN 24H → AUTO-RELEASED TO NEXT TUTOR.',
        ],
        'today' => [
            [ 'time' => '16:00 Aaron', 'meta' => 'P5 MATH · TAMPINES', 'action' => 'LOG →', 'url' => $lesson_log_url ],
            [ 'time' => '19:30 Mei', 'meta' => 'SEC2 MATH · ONLINE', 'action' => '' ],
        ],
        'week' => [
            [ 'day' => 'Mon', 'count' => '2' ],
            [ 'day' => 'Tue', 'count' => '3', 'hot' => true ],
            [ 'day' => 'Wed', 'count' => '1' ],
            [ 'day' => 'Thu', 'count' => '0' ],
            [ 'day' => 'Fri', 'count' => '2', 'hot' => true ],
            [ 'day' => 'Sat', 'count' => '3', 'hot' => true ],
            [ 'day' => 'Sun', 'count' => '1' ],
        ],
        'logs' => [
            [ 'title' => 'SAT 1 JUN · AARON · OVERDUE (24H SLA)', 'hot' => true ],
            [ 'title' => 'SUN 2 JUN · MEI · DUE IN 6H', 'hot' => false ],
        ],
        'earnings' => [
            'amount' => '$2,340',
            'delta' => '+12% VS LAST MONTH',
            'url' => $earnings_url,
        ],
        'payout' => [
            'label' => 'NEXT PAYOUT',
            'amount' => '$1,640',
            'meta' => '30 JUN · PAYNOW •••26',
            'status' => 'STATUS: SCHEDULED · SEE S23',
            'url' => $earnings_url,
        ],
        'leaderboard' => [
            [ 'name' => '10. MR LIM', 'rating' => '+4.9' ],
            [ 'name' => '11. MS GOH', 'rating' => '+4.9' ],
            [ 'name' => '12. You', 'rating' => '+4.9', 'you' => true ],
        ],
        'ratings' => [
            'score' => '★4.9',
            'meta' => '87 REVIEWS · 2 NEW THIS WEEK',
            'quote' => '"PATIENT, CLEAR" · MRS CHEN',
        ],
        'messages' => [
            [ 'title' => 'MRS TAN · RESCHEDULE?', 'meta' => '1H' ],
            [ 'title' => 'AGENCY OPS · PROPOSAL', 'meta' => '3H' ],
        ],
        'profile' => [
            'percent' => '85%',
            'note' => '85% · ADD 2 DEMO VIDEOS → 100%',
        ],
        'quick' => [
            [ 'label' => '+ Log a lesson', 'url' => $lesson_log_url ],
            [ 'label' => '', 'url' => '#' ],
            [ 'label' => '', 'url' => '#' ],
        ],
    ];

    return apply_filters( 'mgk_tutor_dashboard_context', $context );
}

function mgk_render_tutor_dashboard_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/tutor-dashboard/' . $part, [
        'atts'    => $atts,
        'context' => mgk_tutor_dashboard_context(),
    ] );
}

add_shortcode( 'mgk_tutor_dashboard', function ( $atts ) {
    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( [
            'hidden'              => '',
            'hide_mobilebar'      => '',
            'mobile_greeting'     => 'Hi, Ms Lee 👋',
            'mobile_tools'        => '🔔3 · ☰',
            'mobile_job_title'    => 'New job · 1 proposal',
            'hide_mobile_nav'     => '',
            'mobile_nav_home'     => 'Home',
            'mobile_nav_schedule' => 'Schedule',
            'mobile_nav_log'      => '+ Log',
            'mobile_nav_earn'     => '$ Earn',
            'mobile_nav_more'     => 'More',
            'hide_welcome'        => '',
            'sec_welcome'         => 'W1 Welcome',
            'welcome_prefix'      => 'Welcome back,',
            'log_lesson_label'    => '+ Log a lesson',
            'schedule_label'      => 'View schedule',
            'hide_job'            => '',
            'sec_job'             => 'W3 · Job inbox',
            'accept_label'        => 'Accept proposal',
            'decline_label'       => 'Decline (reason ▾)',
            'decline_note'        => 'DECLINE ASKS A REASON (TOO FAR / CLASH / RATE) · FEEDS RE-MATCH.',
            'hide_today'          => '',
            'sec_today'           => 'W2 · Today’s schedule',
            'hide_week'           => '',
            'sec_week'            => 'W4 · Next 7 days',
            'week_note'           => '12 LESSONS BOOKED THIS WEEK',
            'hide_logs'           => '',
            'sec_logs'            => 'W5 · Pending lesson logs',
            'hide_earnings'       => '',
            'sec_earnings'        => 'W6 · Monthly earnings',
            'hide_payout'         => '',
            'sec_payout'          => 'W7 · Payout status',
            'hide_leaderboard'    => '',
            'sec_leaderboard'     => 'W8 · Leaderboard',
            'leaderboard_note'    => 'REGION · TOP 4% · PM TO CONFIRM METRIC',
            'hide_ratings'        => '',
            'sec_ratings'         => 'W9 · Ratings received',
            'hide_messages'       => '',
            'sec_messages'        => 'W10 · Messages (2)',
            'hide_profile'        => '',
            'sec_profile'         => 'W11 · Profile completeness',
            'hide_quick'          => '',
            'sec_quick'           => 'W12 · Quick actions',
        ], $atts )
        : shortcode_atts( [], $atts );

    return mgk_render_tutor_dashboard_part( 'dashboard-page', $atts );
} );
