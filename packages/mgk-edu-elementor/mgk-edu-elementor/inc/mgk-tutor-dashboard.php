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

/**
 * Demo context is shown in wp-admin and the Elementor editor/preview so the page
 * stays designable; real tutors get their own data. Mirrors mgk_proposal_is_preview.
 */
function mgk_tutor_dash_is_editor() {
    if ( function_exists( 'is_admin' ) && is_admin() ) return true;
    if ( class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::$instance ) {
        $ed = \Elementor\Plugin::$instance->editor ?? null;
        if ( $ed && method_exists( $ed, 'is_edit_mode' ) && $ed->is_edit_mode() ) return true;
        $pv = \Elementor\Plugin::$instance->preview ?? null;
        if ( $pv && method_exists( $pv, 'is_preview_mode' ) && $pv->is_preview_mode() ) return true;
    }
    return false;
}

/** True if a booking already has a lesson_log (so it's no longer "pending"). */
function mgk_booking_has_lesson_log( $booking_id ) {
    $booking_id = (int) $booking_id;
    if ( ! $booking_id || ! post_type_exists( 'mg_lesson' ) ) return false;
    $found = get_posts( [
        'post_type'   => 'mg_lesson',
        'post_status' => 'any',
        'numberposts' => 1,
        'fields'      => 'ids',
        'meta_query'  => [ [ 'key' => 'mgk_lesson_booking_id', 'value' => $booking_id ] ],
    ] );
    return ! empty( $found );
}

/** All engine bookings for a tutor (newest slot first), or []. */
function mgk_tutor_all_bookings( $teacher_id ) {
    global $wpdb;
    $teacher_id = (int) $teacher_id;
    $table = function_exists( 'mgk_booking_table' ) ? mgk_booking_table( 'bookings' ) : '';
    if ( ! $table || ! $teacher_id ) return [];
    return (array) $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table} WHERE tutor_post_id = %d ORDER BY start_at_utc ASC",
        $teacher_id
    ), ARRAY_A );
}

/** True when a booking row is a package order, not an actual taught session. */
function mgk_tutor_booking_is_package_order( $row ) {
    $lesson_type = (string) ( is_array( $row ) ? ( $row['lesson_type'] ?? '' ) : '' );
    if ( function_exists( 'mgk_package_plan_from_lesson_type' ) ) {
        return (bool) mgk_package_plan_from_lesson_type( $lesson_type );
    }
    return in_array( $lesson_type, [ 'PACKAGE_8', 'PACKAGE_16' ], true );
}

/** True when a booking row represents a concrete lesson/trial session. */
function mgk_tutor_booking_is_lesson_session( $row ) {
    if ( ! is_array( $row ) || mgk_tutor_booking_is_package_order( $row ) ) return false;
    $start = (string) ( $row['start_at_utc'] ?? '' );
    $end   = (string) ( $row['end_at_utc'] ?? '' );
    if ( $start === '' || $end === '' || $start === $end ) return false;
    return true;
}

/**
 * Shared guard for the tutor lesson-log flow. Returns '' when loggable, or a
 * short reason code used by the page and admin-post handler.
 */
function mgk_tutor_booking_log_block_reason( $row, $teacher_id = 0 ) {
    if ( ! is_array( $row ) || empty( $row['id'] ) ) return 'no-booking';
    if ( $teacher_id && (int) ( $row['tutor_post_id'] ?? 0 ) !== (int) $teacher_id ) return 'denied';
    if ( ! mgk_tutor_booking_is_lesson_session( $row ) ) return 'notlesson';
    if ( (string) ( $row['status'] ?? '' ) !== 'CONFIRMED' ) return 'notconfirmed';
    $end_utc = (string) ( $row['end_at_utc'] ?? '' );
    if ( $end_utc === '' || $end_utc >= gmdate( 'Y-m-d H:i:s' ) ) return 'notended';
    if ( empty( $row['child_id'] ) ) return 'nochild';
    return '';
}

/**
 * Build the REAL dashboard context for a logged-in tutor from their bookings,
 * lesson-logs and reviews. Earnings/payout/messages/leaderboard remain neutral
 * placeholders (deferred to later batches) — never fake numbers.
 */
function mgk_tutor_dashboard_real_context( $teacher_id ) {
    $teacher_id = (int) $teacher_id;
    $lesson_log_url = mgk_get_tutor_lesson_log_url();
    $earnings_url   = function_exists( 'mgk_get_tutor_earnings_url' ) ? mgk_get_tutor_earnings_url() : mgk_url( '/tutor/earnings/' );
    $name = get_the_title( $teacher_id ) ?: 'Tutor';

    $tz = function_exists( 'mgk_view_tz' ) ? mgk_view_tz() : new DateTimeZone( 'Asia/Singapore' );
    $now_utc = gmdate( 'Y-m-d H:i:s' );
    try { $today_sgt = ( new DateTime( 'now', $tz ) )->format( 'Y-m-d' ); }
    catch ( Exception $e ) { $today_sgt = gmdate( 'Y-m-d' ); }

    $bookings = mgk_tutor_all_bookings( $teacher_id );

    $today = [];
    $pending = [];
    $upcoming_count = 0;
    $today_count = 0;
    // 7-day buckets keyed by SGT date.
    $week_dates = [];
    for ( $i = 0; $i < 7; $i++ ) {
        try { $d = ( new DateTime( 'now', $tz ) )->modify( "+{$i} day" ); }
        catch ( Exception $e ) { break; }
        $week_dates[ $d->format( 'Y-m-d' ) ] = [ 'day' => $d->format( 'D' ), 'count' => 0 ];
    }

    foreach ( $bookings as $row ) {
        if ( ! mgk_tutor_booking_is_lesson_session( $row ) ) continue;

        $status = (string) ( $row['status'] ?? '' );
        if ( ! in_array( $status, [ 'CONFIRMED', 'COMPLETED' ], true ) ) continue;

        $start_utc = (string) ( $row['start_at_utc'] ?? '' );
        $end_utc   = (string) ( $row['end_at_utc'] ?? '' );
        $is_past   = $end_utc && $end_utc < $now_utc;

        // SGT labels.
        $sgt_date = ''; $time_lbl = '';
        if ( $start_utc ) {
            try {
                $s = new DateTime( $start_utc . ' UTC' ); $s->setTimezone( $tz );
                $sgt_date = $s->format( 'Y-m-d' );
                $time_lbl = $s->format( 'H:i' );
            } catch ( Exception $e ) {}
        }
        $student = (string) ( $row['student_name'] ?? 'Student' );
        $subject = (string) ( $row['subject'] ?? ( $row['lesson_type'] === 'TRIAL' ? 'Trial' : '' ) );

        // Upcoming (future, confirmed).
        if ( $status === 'CONFIRMED' && ! $is_past ) $upcoming_count++;

        // Week buckets.
        if ( isset( $week_dates[ $sgt_date ] ) && ! $is_past ) {
            $week_dates[ $sgt_date ]['count']++;
        }

        // Today's schedule (any confirmed/completed today).
        if ( $sgt_date === $today_sgt ) {
            $today_count++;
            $needs_log = ( $status === 'CONFIRMED' && $is_past && ! mgk_booking_has_lesson_log( (int) $row['id'] ) );
            $today[] = [
                'time'   => trim( $time_lbl . ' ' . $student ),
                'meta'   => trim( strtoupper( $subject ) . ( $subject ? ' · ' : '' ) . 'LESSON' ),
                'action' => $needs_log ? 'LOG →' : '',
                'url'    => add_query_arg( 'booking', (int) $row['id'], $lesson_log_url ),
            ];
        }

        // Pending lesson logs: a confirmed lesson that has ended with no log yet.
        if ( $status === 'CONFIRMED' && $is_past && ! mgk_booking_has_lesson_log( (int) $row['id'] ) ) {
            $hrs_over = $end_utc ? ( strtotime( $now_utc ) - strtotime( $end_utc ) ) / 3600 : 0;
            $overdue  = $hrs_over > 24; // BR-08 24h SLA
            $when = '';
            if ( $start_utc ) {
                try { $s = new DateTime( $start_utc . ' UTC' ); $s->setTimezone( $tz ); $when = $s->format( 'D j M' ); }
                catch ( Exception $e ) {}
            }
            $pending[] = [
                'title' => strtoupper( trim( $when . ' · ' . $student ) . ( $overdue ? ' · OVERDUE (24H SLA)' : '' ) ),
                'hot'   => $overdue,
                'url'   => add_query_arg( 'booking', (int) $row['id'], $lesson_log_url ),
            ];
        }
    }

    $week = array_values( $week_dates );
    foreach ( $week as &$wd ) { $wd['hot'] = $wd['count'] >= 2; } unset( $wd );

    // Ratings — real reviews for this tutor.
    $ratings = [ 'score' => '—', 'meta' => 'No reviews yet', 'quote' => '' ];
    if ( function_exists( 'mgk_get_teacher_reviews' ) && function_exists( 'mgk_summarize_teacher_reviews' ) ) {
        $reviews = mgk_get_teacher_reviews( $teacher_id, 50 );
        $sum = mgk_summarize_teacher_reviews( $reviews );
        if ( (int) $sum['count'] > 0 ) {
            $first = $reviews[0] ?? null;
            $ratings = [
                'score' => '★' . number_format( (float) $sum['rating'], 1 ),
                'meta'  => sprintf( '%d review%s', (int) $sum['count'], $sum['count'] === 1 ? '' : 's' ),
                'quote' => $first ? trim( '“' . wp_trim_words( (string) $first['copy'], 8, '…' ) . '” · ' . (string) $first['name'] ) : '',
            ];
        }
    }

    // Profile completeness — simple real checklist.
    $checks = [
        'rate'         => (int) get_post_meta( $teacher_id, 'mgk_rate_num', true ) > 0,
        'subjects'     => ! empty( wp_get_post_terms( $teacher_id, 'mgk_subject', [ 'fields' => 'ids' ] ) ),
        'availability' => (bool) get_post_meta( $teacher_id, '_mgk_weekly_availability_json', true ),
        'verified'     => (bool) get_post_meta( $teacher_id, 'mgk_is_verified', true ),
        'photo'        => has_post_thumbnail( $teacher_id ),
    ];
    $done_checks = count( array_filter( $checks ) );
    $pct = (int) round( $done_checks / max( 1, count( $checks ) ) * 100 );
    $missing = [];
    if ( ! $checks['photo'] ) $missing[] = 'add a photo';
    if ( ! $checks['availability'] ) $missing[] = 'set availability';
    if ( ! $checks['subjects'] ) $missing[] = 'add subjects';
    $profile_note = $pct . '% complete' . ( $missing ? ' · ' . strtoupper( implode( ', ', $missing ) ) : ' · ALL SET' );

    $tutor_meta = sprintf(
        '%s · %d LESSON%s TODAY · %s',
        strtoupper( gmdate( 'D j M' ) ),
        $today_count, $today_count === 1 ? '' : 'S',
        $ratings['score'] !== '—' ? $ratings['score'] : 'NEW TUTOR'
    );

    $context = [
        'tutor' => [ 'name' => $name, 'meta' => $tutor_meta ],
        'job'   => [
            'subject' => 'No new jobs', 'badge' => '', 'sla' => '',
            'body' => 'New match proposals from agency ops appear here.', 'note' => '',
        ],
        'today'       => $today,
        'week'        => $week,
        'logs'        => $pending,
        'earnings'    => [ 'amount' => '—', 'delta' => 'Earnings coming soon', 'url' => $earnings_url ],
        'payout'      => [ 'label' => 'NEXT PAYOUT', 'amount' => '—', 'meta' => '', 'status' => 'See earnings', 'url' => $earnings_url ],
        'leaderboard' => [],
        'ratings'     => $ratings,
        'messages'    => [],
        'profile'     => [ 'percent' => $pct . '%', 'note' => $profile_note ],
        'quick'       => [
            [ 'label' => '+ Log a lesson', 'url' => $pending ? $pending[0]['url'] : $lesson_log_url ],
        ],
        'is_real'     => true,
        'pending_count' => count( $pending ),
        'upcoming_count' => $upcoming_count,
    ];

    return apply_filters( 'mgk_tutor_dashboard_context', $context );
}

function mgk_tutor_dashboard_context() {
    // Real data for a signed-in tutor; demo for editor/preview/admin.
    if ( function_exists( 'mgk_is_tutor_user' ) && mgk_is_tutor_user() ) {
        $tid = mgk_current_tutor_teacher_id();
        if ( $tid ) return mgk_tutor_dashboard_real_context( $tid );
    }

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
    // Gate: a real front-end visitor who isn't a signed-in tutor gets a login CTA.
    // (The editor/admin still sees the demo layout so the page stays designable.)
    if ( ! ( function_exists( 'mgk_is_tutor_user' ) && mgk_is_tutor_user() ) && ! mgk_tutor_dash_is_editor() ) {
        $login = function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' );
        $styles = function_exists( 'mgk_auth_styles' ) ? mgk_auth_styles() : '';
        return $styles
            . '<div class="mgk-auth"><div class="mgk-auth__card">'
            . '<h1>Tutor sign in</h1>'
            . '<p>Sign in to see your schedule, lesson logs and ratings.</p>'
            . '<a class="mgk-auth__btn" style="text-decoration:none;text-align:center" href="' . esc_url( $login ) . '">Sign in to your dashboard →</a>'
            . '<p class="mgk-auth__foot">Not a tutor yet? <a href="' . esc_url( mgk_url( '/become-a-tutor/' ) ) . '">Apply to teach</a>.</p>'
            . '</div></div>';
    }

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
