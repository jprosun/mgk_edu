<?php
/**
 * Parent dashboard shell states.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_parent_bool( $value ) {
    return $value === true || $value === 1 || $value === '1' || $value === 'yes' || $value === 'true';
}

function mgk_parent_shortcode_atts( $defaults, $atts ) {
    return shortcode_atts( array_merge( [
        'hidden' => '',
    ], $defaults ), $atts );
}

function mgk_render_parent_part( $part, $atts = [] ) {
    if ( mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/parent/' . $part, [ 'atts' => $atts ] );
}

add_shortcode( 'mgk_parent_empty_dashboard', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'greeting'                => 'Welcome,',
        'parent_name'             => 'Mrs Tan',
        'wave'                    => '&#128075;',
        'subline'                 => "NO LESSONS BOOKED YET - LET'S FIND EMMA A TUTOR.",
        'illustration_label'      => 'Empty illustration',
        'ready_title'             => 'Your dashboard is ready',
        'ready_body'              => 'KPIS, LESSON LOGS & PROGRESS APPEAR AFTER YOUR FIRST LESSON.',
        'primary_label'           => 'Find a Tutor - S02',
        'primary_url'             => '/student/teachers/',
        'secondary_label'         => '+ Add another child',
        'secondary_url'           => '/request-match/',
        'note'                    => 'EMPTY STATE REPLACES ALL DATA WIDGETS WITH ONBOARDING CTA. CHILD SWITCHER + ACCOUNT REMAIN AVAILABLE.',
        'hide_greeting'           => '',
        'hide_parent_name'        => '',
        'hide_wave'               => '',
        'hide_subline'            => '',
        'hide_illustration'       => '',
        'hide_illustration_label' => '',
        'hide_ready_title'        => '',
        'hide_ready_body'         => '',
        'hide_primary'            => '',
        'hide_secondary'          => '',
        'hide_note'               => '',
    ], $atts );

    return mgk_render_parent_part( 'empty-dashboard', $atts );
} );

function mgk_render_dashboard_part( $part, $atts = [] ) {
    if ( mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/dashboard/' . $part, [
        'atts'    => $atts,
        'context' => mgk_get_parent_dashboard_context(),
    ] );
}

/** Preview = NOT a signed-in parent (Elementor editor / logged-out) → show sample. */
function mgk_dashboard_is_preview() {
    return ! ( function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() );
}

function mgk_get_parent_dashboard_context() {
    $parent_id  = get_current_user_id();
    $children   = mgk_get_parent_children( $parent_id );
    $active      = mgk_get_active_child( $parent_id );
    // Tri-state child id: real child post id | 0 (signed-in, no child yet) |
    // 'demo-emma' (preview only). Drives real-vs-empty-vs-sample everywhere.
    $child_id   = $active['id'] ?? ( mgk_dashboard_is_preview() ? 'demo-emma' : 0 );

    return [
        'parent_id'        => $parent_id,
        'parent_name'      => mgk_dashboard_parent_name( $parent_id ),
        'date_line'        => mgk_dashboard_date_line( $children ),
        'add_child_url'    => mgk_get_add_child_url(),
        'logout_url'       => function_exists( 'mgk_logout_url' ) ? mgk_logout_url() : wp_logout_url( mgk_url( '/login/' ) ),
        'unread_total'     => function_exists( 'mgk_parent_total_unread' ) ? (int) mgk_parent_total_unread( $parent_id ) : 0,
        'messages_url'     => mgk_url( '/parent/messages/' ),
        'children'         => $children,
        'active_child'     => $active,
        'renewal'          => mgk_get_renewal_nudge( $child_id ),
        'kpis'             => mgk_get_dashboard_kpis( $child_id ),
        'progress'         => mgk_get_child_progress_series( $child_id, '8w' ),
        'latest_log'       => mgk_get_latest_lesson_log( $child_id ),
        'lesson_logs_url'  => mgk_get_lesson_logs_url( $child_id ),
        'upcoming_lessons' => mgk_get_upcoming_lessons( $parent_id ),
        'package'          => mgk_get_active_package_summary( $child_id ),
        'invoices_url'     => mgk_get_invoices_url( $parent_id ),
        'billing'          => mgk_get_parent_billing( $parent_id ),
        'message_thread'   => mgk_get_primary_tutor_thread( $child_id ),
        'buy_package_url'  => mgk_get_buy_package_url( $child_id ),
        'quick_links'      => mgk_get_parent_dashboard_quick_links(),
    ];
}

/** Parent's display name for the welcome line (real wp_user, else demo). */
function mgk_dashboard_parent_name( $parent_id ) {
    $parent_id = (int) $parent_id;
    if ( ! $parent_id ) return 'Mrs Tan';
    $full = (string) get_user_meta( $parent_id, 'mgk_parent_full_name', true );
    if ( $full ) return $full;
    $u = get_user_by( 'id', $parent_id );
    return $u ? ( $u->display_name ?: $u->user_email ) : 'Mrs Tan';
}

/** Subline: "<n> active child(ren)" from the real linked children. */
function mgk_dashboard_date_line( $children ) {
    $n = is_array( $children ) ? count( $children ) : 0;
    if ( ! $n ) return 'WELCOME — LET’S GET STARTED';
    return sprintf( '%d ACTIVE %s', $n, $n === 1 ? 'CHILD' : 'CHILDREN' );
}

/** Add-child routes back to the S07 request-match form (low-friction, BR: no re-entry of identity). */
function mgk_get_add_child_url() {
    return mgk_url( '/request-match/' );
}

/** Map a real mg_child post → dashboard child shape. */
function mgk_map_child_post( $post, $active = false ) {
    $id    = (int) ( is_object( $post ) ? $post->ID : $post );
    $name  = (string) get_post_meta( $id, 'mgk_child_full_name', true );
    if ( $name === '' && is_object( $post ) ) $name = $post->post_title;
    $level = '';
    $term_id = (int) get_post_meta( $id, 'mgk_child_current_level', true );
    if ( $term_id ) {
        $t = get_term( $term_id );
        if ( $t && ! is_wp_error( $t ) ) $level = $t->name;
    }
    return [ 'id' => $id, 'name' => $name, 'level' => $level, 'active' => (bool) $active ];
}

/**
 * Which child is currently being VIEWED: the `?child=<id>` selection when it
 * belongs to this parent, else the first child. This is the single source of
 * truth for the switcher — every dashboard widget re-renders for this id on
 * load (server-truth-on-load, no client state to drift).
 */
function mgk_resolve_active_child_id( $posts ) {
    $ids = [];
    foreach ( (array) $posts as $p ) {
        $ids[] = (int) ( is_object( $p ) ? $p->ID : $p );
    }
    $ids = array_values( array_filter( $ids ) );
    if ( ! $ids ) return 0;
    $req = isset( $_GET['child'] ) ? (int) $_GET['child'] : 0;
    if ( $req && in_array( $req, $ids, true ) ) return $req;   // validated to this parent
    return $ids[0];
}

function mgk_get_parent_children( $parent_id ) {
    $parent_id = (int) $parent_id;
    if ( $parent_id && function_exists( 'mgk_parent_children' ) ) {
        $posts     = mgk_parent_children( $parent_id );
        $active_id = mgk_resolve_active_child_id( $posts );
        $out       = [];
        foreach ( $posts as $p ) {
            $pid   = (int) ( is_object( $p ) ? $p->ID : $p );
            $out[] = mgk_map_child_post( $p, $pid === $active_id );
        }
        // A signed-in parent sees ONLY their real children — even if empty
        // (NO demo Emma/Ryan). Demo is reserved for preview/editor.
        if ( $out || ! mgk_dashboard_is_preview() ) {
            return $out;
        }
    }
    // Demo fallback — preview / Elementor editor only.
    if ( mgk_dashboard_is_preview() ) {
        return [
            [ 'id' => 'demo-emma', 'name' => 'Emma', 'level' => 'P5', 'active' => true ],
            [ 'id' => 'demo-ryan', 'name' => 'Ryan', 'level' => 'Sec 2', 'active' => false ],
        ];
    }
    return [];
}

function mgk_get_active_child( $parent_id ) {
    $children = mgk_get_parent_children( $parent_id );
    if ( $children ) {
        foreach ( $children as $c ) {
            if ( ! empty( $c['active'] ) ) return $c;   // the ?child= selection
        }
        return $children[0];
    }
    return mgk_dashboard_is_preview()
        ? [ 'id' => 'demo-emma', 'name' => 'Emma', 'level' => 'P5', 'active' => true ]
        : [];
}

/**
 * Bookings for a dashboard child, matched by (parent_user_id, student_name).
 *  - returns NULL  → preview/sample (child_id is non-numeric, e.g. 'demo-emma')
 *  - returns []    → signed-in real parent with no matching bookings (honest empty)
 *  - returns rows  → the child's real bookings
 */
function mgk_dashboard_child_bookings( $child_id ) {
    if ( ! is_numeric( $child_id ) ) return null;   // preview
    $child_id = (int) $child_id;
    if ( ! $child_id || ! function_exists( 'mgk_booking_table' ) ) return [];
    $parent = (int) get_post_meta( $child_id, 'mgk_child_parent_user', true );
    $name   = (string) ( get_post_meta( $child_id, 'mgk_child_full_name', true ) ?: get_the_title( $child_id ) );
    if ( ! $parent || $name === '' ) return [];
    global $wpdb;
    $table = mgk_booking_table( 'bookings' );
    return (array) $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table} WHERE parent_user_id = %d AND student_name = %s ORDER BY start_at_utc ASC",
        $parent, $name
    ), ARRAY_A );
}

/** Resolve a dashboard child's display name (real post, or demo). */
function mgk_dashboard_child_name( $child_id ) {
    if ( ! is_numeric( $child_id ) || ! (int) $child_id ) return 'Emma';
    return (string) ( get_post_meta( (int) $child_id, 'mgk_child_full_name', true ) ?: get_the_title( (int) $child_id ) ?: 'your child' );
}

function mgk_should_show_renewal_nudge( $enrolment ) {
    return true;
}

function mgk_get_renewal_url( $child_id, $booking_id ) {
    return mgk_url( '/parent/trial/' );
}

function mgk_snooze_renewal_nudge( $child_id, $days = 7 ) {
    return true;
}

function mgk_get_dashboard_kpis( $child_id ) {
    $rows = mgk_dashboard_child_bookings( $child_id );

    // Preview / sample.
    if ( $rows === null ) {
        return [
            [ 'value' => '14',   'label' => 'LESSONS DONE' ],
            [ 'value' => '2',    'label' => 'REMAINING' ],
            [ 'value' => '+1.5', 'label' => 'GRADE ↑ (TUTOR-REPORTED)' ],
            [ 'value' => '96%',  'label' => 'ATTENDANCE' ],
        ];
    }

    // Real: prefer the enrolment + lesson_log model.
    $s = function_exists( 'mgk_child_learning_stats' ) ? mgk_child_learning_stats( $child_id ) : [ 'has_enrolment' => false ];
    if ( ! empty( $s['has_enrolment'] ) ) {
        return [
            [ 'value' => (string) $s['lessons_done'],            'label' => 'LESSONS DONE' ],
            [ 'value' => (string) $s['remaining'],               'label' => 'REMAINING' ],
            [ 'value' => strtoupper( $s['engagement_label'] ),   'label' => 'ENGAGEMENT' ],
            [ 'value' => $s['attendance_pct'] . '%',             'label' => 'ATTENDANCE' ],
        ];
    }

    // No enrolment yet → trial/booking state (grade/attendance need lessons → —).
    $now = gmdate( 'Y-m-d H:i:s' );
    $done = $upcoming = 0;
    foreach ( $rows as $r ) {
        if ( $r['status'] === 'COMPLETED' ) {
            $done++;
        } elseif ( $r['status'] === 'CONFIRMED' && $r['end_at_utc'] >= $now ) {
            $upcoming++;
        }
    }
    return [
        [ 'value' => (string) $done,     'label' => 'LESSONS DONE' ],
        [ 'value' => (string) $upcoming, 'label' => 'UPCOMING' ],
        [ 'value' => '—',                'label' => 'ENGAGEMENT (AFTER 1ST LESSON)' ],
        [ 'value' => '—',                'label' => 'ATTENDANCE (AFTER 1ST LESSON)' ],
    ];
}

function mgk_get_child_progress_series( $child_id, $range = '8w' ) {
    $rows = mgk_dashboard_child_bookings( $child_id );
    $name = mgk_dashboard_child_name( $child_id );

    if ( $rows === null ) {
        return [
            'title'       => 'Weekly progress — ' . $name,
            'range_label' => 'LAST 8 WEEKS ▾',
            'description' => 'Bar/line chart: lessons attended + topic mastery % per week',
            'legend'      => 'Attendance · Mastery % · Source: tutor lesson log (FR-LESSON-*)',
        ];
    }

    // Real series from the lesson_log (engagement per lesson_number).
    $s = function_exists( 'mgk_child_learning_stats' ) ? mgk_child_learning_stats( $child_id ) : [ 'series' => [] ];
    if ( empty( $s['series'] ) ) {
        return [
            'title'       => 'Progress — ' . $name,
            'range_label' => '',
            'description' => 'No lessons logged yet — your tutor adds progress after each session.',
            'legend'      => '',
            'series'      => [],
            'empty'       => true,
        ];
    }
    $first = $s['series'][0]['label']; $last = end( $s['series'] )['label'];
    return [
        'title'       => 'Progress — ' . $name,
        'range_label' => count( $s['series'] ) . ' LESSONS',
        'description' => 'Engagement per lesson: ' . $first . ' → ' . $last . ' (avg ' . $s['engagement_label'] . ')',
        'legend'      => 'Y = engagement (Needs work → Excellent) · X = lesson · Source: tutor lesson log',
        'series'      => $s['series'],
    ];
}

function mgk_get_latest_lesson_log( $child_id ) {
    $rows = mgk_dashboard_child_bookings( $child_id );

    if ( $rows === null ) {
        return [
            'date_subject' => '31 MAY · P5 MATH',
            'topic'        => 'FRACTIONS',
            'status'       => '✓ LOGGED',
            'summary'      => 'Homework set: Pg 42-44 · Next: Decimals',
        ];
    }

    // Real: newest lesson_log for the child.
    $s = function_exists( 'mgk_child_learning_stats' ) ? mgk_child_learning_stats( $child_id ) : [ 'latest' => null ];
    $l = $s['latest'] ?? null;
    if ( ! $l ) {
        return [
            'date_subject' => '—',
            'topic'        => 'No lessons logged yet',
            'status'       => '',
            'summary'      => 'Your tutor posts a lesson log after each session — it’ll appear here.',
            'empty'        => true,
        ];
    }
    $date = $l['date'] ? strtoupper( gmdate( 'j M', strtotime( $l['date'] ) ) ) : ( 'LESSON ' . $l['number'] );
    $subj = $s['subject'] ? ' · ' . strtoupper( $s['subject'] ) : '';
    $hw   = $l['homework'] ? 'Homework: ' . $l['homework'] : '';
    return [
        'date_subject' => $date . $subj,
        'topic'        => strtoupper( $l['topic'] ?: 'Lesson ' . $l['number'] ),
        'status'       => $l['attendance'] === 'ATTENDED' ? '✓ LOGGED' : strtoupper( $l['attendance'] ),
        'summary'      => trim( $hw . ( $hw && $l['comment'] ? ' · ' : '' ) . $l['comment'] ) ?: ( 'Engagement: ' . $l['eng_label'] ),
    ];
}

function mgk_get_lesson_logs_url( $child_id ) {
    return mgk_url( '/parent/lesson-logs/' );
}

function mgk_get_upcoming_lessons( $parent_id ) {
    $parent_id = (int) $parent_id;

    // ── Real: the parent's confirmed, not-yet-finished bookings ──
    if ( $parent_id && function_exists( 'mgk_booking_table' ) && function_exists( 'mgk_booking_view' ) ) {
        global $wpdb;
        $table = mgk_booking_table( 'bookings' );
        $rows  = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE parent_user_id = %d AND status IN ('CONFIRMED','COMPLETED') AND end_at_utc >= %s ORDER BY start_at_utc ASC LIMIT 10",
            $parent_id, gmdate( 'Y-m-d H:i:s' )
        ), ARRAY_A );
        if ( $rows ) {
            $out = [];
            foreach ( $rows as $r ) {
                $bv = mgk_booking_view( $r );
                if ( empty( $bv['found'] ) ) continue;
                $out[] = [
                    'id'             => $bv['code'],
                    'time'           => strtoupper( trim( $bv['slot']['date_label'] . ' · ' . $bv['slot']['time_label'] ) ),
                    'meta'           => strtoupper( trim( implode( ' · ', array_filter( [ $bv['student_name'], $bv['subject'], $bv['tutor']['name'] ] ) ) ) ),
                    'reschedule_url' => mgk_url( '/trial-confirmed/?booking=' . rawurlencode( $bv['code'] ) ) . '#mgk-reschedule',
                    'message_url'    => mgk_get_message_thread_url( $bv['tutor']['slug'] ?: 'tutor', (string) ( $bv['student_name'] ?: 'child' ) ),
                ];
            }
            if ( $out ) return $out;
        }
    }

    // Demo fallback (preview / no bookings yet).
    return [
        [
            'id' => 'demo-lesson-1', 'time' => 'WED 4 JUN · 4:00PM',
            'meta' => 'EMMA · P5 MATH · MS LEE',
            'reschedule_url' => '#', 'message_url' => mgk_get_message_thread_url( 'demo-ms-lee', 'demo-emma' ),
        ],
        [
            'id' => 'demo-lesson-2', 'time' => 'THU 5 JUN · 7:00PM',
            'meta' => 'RYAN · SEC 2 SCI · MR WONG',
            'reschedule_url' => '#', 'message_url' => mgk_get_message_thread_url( 'demo-mr-wong', 'demo-ryan' ),
        ],
    ];
}

function mgk_get_reschedule_url( $lesson_id ) {
    return '#';
}

function mgk_get_message_thread_url( $tutor_id, $child_id ) {
    return add_query_arg( [
        'thread' => sanitize_key( $tutor_id ),
        'child'  => sanitize_key( $child_id ),
    ], mgk_url( '/parent/messages/' ) );
}

function mgk_get_book_next_lesson_url( $child_id ) {
    return mgk_url( '/book-slot/' );
}

function mgk_get_active_package_summary( $child_id ) {
    $rows = mgk_dashboard_child_bookings( $child_id );
    $name = mgk_dashboard_child_name( $child_id );

    if ( $rows === null ) {
        return [
            'title' => 'EMMA PKG 16',
            'left'  => '2 LEFT',
            'used'  => '14/16 USED · VALID UNTIL 31 JUL 2026',
            'method'=> 'PAYNOW',
        ];
    }

    // Real enrolment → package usage.
    $s = function_exists( 'mgk_child_learning_stats' ) ? mgk_child_learning_stats( $child_id ) : [ 'has_enrolment' => false ];
    if ( ! empty( $s['has_enrolment'] ) ) {
        $valid = $s['valid_until'] ? ' · VALID UNTIL ' . strtoupper( gmdate( 'j M Y', strtotime( $s['valid_until'] ) ) ) : '';
        $plan  = str_replace( '_', ' ', $s['plan_type'] );
        return [
            'title' => strtoupper( $name ) . ' · ' . strtoupper( $plan ),
            'left'  => $s['remaining'] . ' LEFT',
            'used'  => $s['lessons_done'] . '/' . $s['lessons_total'] . ' USED' . $valid,
            'method'=> 'PAYNOW',
        ];
    }

    // No enrolment yet → honest trial state.
    $has_trial = false;
    foreach ( $rows as $r ) {
        if ( $r['lesson_type'] === 'TRIAL' && in_array( $r['status'], [ 'CONFIRMED', 'COMPLETED' ], true ) ) { $has_trial = true; }
    }
    if ( $has_trial ) {
        return [
            'title' => strtoupper( $name ) . ' — TRIAL',
            'left'  => 'TRIAL',
            'used'  => 'No package yet — buy 8 or 16 lessons to keep going',
            'method'=> 'PAYNOW',
        ];
    }
    return [
        'title' => strtoupper( $name ),
        'left'  => '',
        'used'  => 'No active package yet',
        'method'=> '',
    ];
}

/** Renewal nudge — only when a real package is near its end (BR-12). */
function mgk_get_renewal_nudge( $child_id ) {
    if ( ! is_numeric( $child_id ) ) {
        // Preview sample.
        return [
            'show'      => true,
            'title'     => "Emma's package ends in 4 weeks (T-4wk · BR-12)",
            'subline'   => '2 OF 16 LESSONS REMAINING · RENEW NOW TO KEEP MS LEE & SAME SLOT',
            'renew_url' => mgk_get_renewal_url( $child_id, 'demo-booking' ),
        ];
    }
    // Real: BR-12 — nudge when exactly 1 lesson remains in an active package.
    $s = function_exists( 'mgk_child_learning_stats' ) ? mgk_child_learning_stats( $child_id ) : [ 'renewal_due' => false ];
    if ( empty( $s['renewal_due'] ) ) {
        return [ 'show' => false ];
    }
    $name = mgk_dashboard_child_name( $child_id );
    return [
        'show'      => true,
        'title'     => $name . '’s package has 1 lesson left (BR-12)',
        'subline'   => $s['lessons_done'] . ' OF ' . $s['lessons_total'] . ' DONE · RENEW NOW TO KEEP YOUR TUTOR & SLOT',
        'renew_url' => mgk_get_buy_package_url( $child_id ),
    ];
}

/** Latest paid booking's e-invoice for this parent (real), else '#'. */
function mgk_get_invoices_url( $parent_id ) {
    $parent_id = (int) $parent_id;
    if ( $parent_id && function_exists( 'mgk_booking_table' ) ) {
        global $wpdb;
        $t = mgk_booking_table( 'bookings' );
        $code = $wpdb->get_var( $wpdb->prepare(
            "SELECT booking_code FROM {$t} WHERE parent_user_id = %d AND status IN ('CONFIRMED','COMPLETED') ORDER BY COALESCE(confirmed_at_utc, created_at_utc) DESC LIMIT 1",
            $parent_id
        ) );
        if ( $code ) {
            return add_query_arg( [ 'mgk_action' => 'invoice', 'booking' => rawurlencode( $code ) ], home_url( '/trial-confirmed/' ) );
        }
    }
    return '#';
}

/** Real billing summary for the parent: last payment + count, from mgk_payments. */
function mgk_get_parent_billing( $parent_id ) {
    $parent_id = (int) $parent_id;
    if ( ! $parent_id || ! function_exists( 'mgk_booking_table' ) ) {
        return [ 'has' => false ];
    }
    global $wpdb;
    $b = mgk_booking_table( 'bookings' );
    $p = mgk_booking_table( 'payments' );
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT pay.amount, pay.currency, pay.provider, pay.paid_at_utc
         FROM {$p} pay JOIN {$b} bk ON bk.id = pay.booking_id
         WHERE bk.parent_user_id = %d AND pay.status = 'SUCCEEDED'
         ORDER BY pay.paid_at_utc DESC LIMIT 1",
        $parent_id
    ), ARRAY_A );
    if ( ! $row ) return [ 'has' => false ];
    $method = [ 'STRIPE' => 'Card', 'PAYNOW' => 'PayNow', 'MOCK' => 'PayNow' ][ strtoupper( (string) $row['provider'] ) ] ?? ucfirst( strtolower( (string) $row['provider'] ) );
    return [
        'has'        => true,
        'last_amount'=> '$' . number_format( (float) $row['amount'], 2 ),
        'last_method'=> $method,
        'last_date'  => $row['paid_at_utc'] ? gmdate( 'j M Y', strtotime( $row['paid_at_utc'] ) ) : '',
    ];
}

function mgk_get_primary_tutor_thread( $child_id ) {
    $rows = mgk_dashboard_child_bookings( $child_id );

    if ( $rows === null ) {
        return [
            'tutor' => 'MS LEE',
            'status'=> 'ONLINE · 2 UNREAD',
            'url'   => mgk_get_message_thread_url( 'demo-ms-lee', $child_id ),
        ];
    }

    // Real: the tutor LINKED to this child — enrolment tutor first, else the
    // child's most recent booking tutor. This is the explicit parent↔child↔tutor
    // relationship surfaced as a field (it lives in mg_enrolment / mgk_bookings).
    $stats    = function_exists( 'mgk_child_learning_stats' ) ? mgk_child_learning_stats( $child_id ) : [];
    $tutor_id = 0;
    if ( ! empty( $stats['enrolment_id'] ) ) {
        $tutor_id = (int) get_post_meta( (int) $stats['enrolment_id'], 'mgk_enr_tutor_id', true );
    }
    if ( ! $tutor_id ) {
        foreach ( array_reverse( $rows ) as $r ) {
            if ( ! empty( $r['tutor_post_id'] ) ) { $tutor_id = (int) $r['tutor_post_id']; break; }
        }
    }
    if ( ! $tutor_id ) {
        return [ 'tutor' => '—', 'status' => 'No tutor assigned yet', 'relation' => '', 'tier' => '', 'unread' => 0, 'url' => mgk_get_message_thread_url( 'tutor', (string) $child_id ) ];
    }

    $p     = get_post( $tutor_id );
    $name  = $p ? $p->post_title : '—';
    $slug  = $p ? $p->post_name : 'tutor';
    $tier  = (string) get_post_meta( $tutor_id, 'mgk_tier', true );
    $child = mgk_dashboard_child_name( $child_id );
    $subj  = ! empty( $stats['subject'] ) ? $stats['subject'] . ' ' : '';
    $relation = trim( $child . '’s ' . $subj . 'tutor' );

    // Real unread from the messaging model when present (else 0).
    $unread = 0;
    if ( function_exists( 'mgk_thread_key_for' ) && function_exists( 'mgk_get_thread_unread_count' ) ) {
        $unread = (int) mgk_get_thread_unread_count( mgk_thread_key_for( (int) $child_id, $tutor_id ) );
    }

    return [
        'tutor'    => strtoupper( $name ),
        'relation' => $relation,
        'status'   => strtoupper( $relation ) . ( $unread ? ' · ' . $unread . ' UNREAD' : '' ),
        'tier'     => $tier,
        'unread'   => $unread,
        'tutor_id' => $tutor_id,
        'url'      => mgk_get_message_thread_url( $slug, (string) $child_id ),
    ];
}

function mgk_get_buy_package_url( $child_id ) {
    return mgk_url( '/parent/trial/' );
}

function mgk_get_switch_tutor_url( $child_id ) {
    return mgk_url( '/parent/trial/switch/' );
}

function mgk_get_child_learning_context( $child_id, $booking_id = '' ) {
    $context = [
        'subject' => 'Math',
        'level'   => 'P5-P6',
    ];

    return apply_filters( 'mgk_parent_child_learning_context', $context, $child_id, $booking_id );
}

function mgk_get_end_tuition_url( $child_id ) {
    return mgk_url( '/parent/trial/end/' );
}

function mgk_get_lapsed_package_url( $child_id ) {
    return mgk_url( '/parent/trial/lapsed/' );
}

function mgk_get_parent_dashboard_quick_links() {
    return [
        [ 'icon' => '&#11088;', 'label' => 'Leave a review',      'note' => '→ S16', 'url' => function_exists( 'mgk_get_parent_review_url' ) ? mgk_get_parent_review_url( 'ms-lee-yi-ling' ) : '#' ],
        [ 'icon' => '&#127873;', 'label' => 'Refer a friend',     'note' => '→ S17', 'url' => function_exists( 'mgk_get_parent_referral_url' ) ? mgk_get_parent_referral_url() : '#' ],
        [ 'icon' => '&#128257;', 'label' => 'Renew / change',     'note' => '→ S15', 'url' => '#' ],
        [ 'icon' => '&#9881;',  'label' => 'Account & settings', 'note' => '→ S18', 'url' => function_exists( 'mgk_get_parent_account_url' ) ? mgk_get_parent_account_url() : '#' ],
    ];
}

function mgk_render_package_part( $part, $atts = [] ) {
    if ( mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/package/' . $part, [
        'atts'    => $atts,
        'context' => mgk_get_parent_package_context(),
    ] );
}

function mgk_get_parent_package_context() {
    return [
        'child_name'   => 'Emma',
        'tutor_name'   => 'Ms Lee',
        'headline'     => "Emma's package with Ms Lee is ending",
        'meta'         => 'PKG 16 · 2 OF 16 LESSONS LEFT · ENDS 1 JUL 2026 · P5 MATH',
        'prompt'       => 'WHAT WOULD YOU LIKE TO DO? ALL CHOICES BELOW ARE PRESENTED EQUALLY - NO OPTION IS HIDDEN OR DISCOURAGED.',
        'options'      => mgk_get_package_decision_options( 'demo-emma', 'demo-ms-lee' ),
        'pause_detail' => mgk_get_package_pause_detail( 'demo-emma', 'demo-booking' ),
        'switch'       => [
            'tutors' => mgk_get_switch_tutor_suggestions( 'demo-emma', 'demo-ms-lee' ),
        ],
        'end'          => mgk_get_end_tuition_facts( 'demo-emma', 'demo-booking' ),
        'lapsed'       => mgk_get_lapsed_package_context( 'demo-emma', 'demo-ms-lee' ),
    ];
}

function mgk_get_package_decision_options( $child_id, $tutor_id ) {
    return [
        [
            'key'      => 'continue',
            'icon'     => '&#128257;',
            'title'    => 'Continue',
            'summary'  => 'SAME TUTOR, SAME PACKAGE',
            'price'    => '$936',
            'detail'   => '16 LESSONS · 10% OFF',
            'button'   => 'Continue →',
            'url'      => mgk_get_buy_package_url( $child_id ),
            'featured' => true,
        ],
        [
            'key'     => 'upgrade',
            'icon'    => 'I',
            'title'   => 'Upgrade',
            'summary' => 'MORE LESSONS / LONGER PKG',
            'price'   => '$1,728',
            'detail'  => '32 LESSONS · 12% OFF',
            'button'  => 'Upgrade →',
            'url'     => mgk_get_buy_package_url( $child_id ),
        ],
        [
            'key'     => 'pause',
            'icon'    => 'II',
            'title'   => 'Pause',
            'summary' => 'HOLD FOR UP TO 2 WEEKS',
            'price'   => '',
            'detail'  => 'CONDITIONS APPLY (BR-16)',
            'button'  => 'Pause →',
            'url'     => '#pause-detail',
        ],
        [
            'key'     => 'switch',
            'icon'    => '&#128260;',
            'title'   => 'Switch tutor',
            'summary' => 'KEEP LEARNING, NEW TUTOR',
            'price'   => 'free',
            'detail'  => 'FREE MATCH / REVIEW(-06)',
            'button'  => 'Switch →',
            'url'     => mgk_get_switch_tutor_url( $child_id ),
        ],
        [
            'key'     => 'end',
            'icon'    => '&#9632;',
            'title'   => 'End',
            'summary' => 'STOP TUITION FOR NOW',
            'price'   => '',
            'detail'  => 'NO PENALTY · CLEAR EXIT',
            'button'  => 'End →',
            'url'     => mgk_get_end_tuition_url( $child_id ),
        ],
    ];
}

function mgk_get_package_pause_detail( $child_id, $booking_id ) {
    return [
        'conditions' => [
            'Eligible after ≥50% of package used',
            'Max 1 pause per package',
            'Max 2 weeks duration',
            'Slot NOT guaranteed on return',
        ],
        'pause_from' => '5 Jun',
        'resume'     => '19 Jun',
    ];
}

function mgk_get_switch_tutor_suggestions( $child_id, $current_tutor_id ) {
    $learning = mgk_get_child_learning_context( $child_id );
    $subject  = trim( (string) ( $learning['subject'] ?? '' ) );
    $level    = trim( (string) ( $learning['level'] ?? '' ) );
    $fallback = mgk_get_switch_tutor_demo_suggestions();

    $tutors = function_exists( 'mgk_get_tutors_from_db' ) ? mgk_get_tutors_from_db() : [];
    if ( ! $tutors ) {
        return $fallback;
    }

    $excluded_slugs = mgk_switch_tutor_excluded_slugs( $current_tutor_id );
    $prepared = [];

    foreach ( $tutors as $tutor ) {
        $slug = sanitize_title( $tutor['slug'] ?? $tutor['name'] ?? '' );
        if ( ! $slug || in_array( $slug, $excluded_slugs, true ) ) {
            continue;
        }

        $levels   = array_filter( array_map( 'trim', (array) ( $tutor['levels'] ?? [] ) ) );
        $same_subject = mgk_switch_tutor_matches_label( $subject, (array) ( $tutor['subjects'] ?? [] ) );
        $same_level = mgk_switch_tutor_matches_label( $level, $levels );
        $label_level = $level ?: ( $levels ? reset( $levels ) : '' );
        $label_subject = $subject ?: ( ! empty( $tutor['subjects'][0] ) ? $tutor['subjects'][0] : '' );

        $prepared[] = [
            'id'           => (string) $slug,
            'name'         => (string) ( $tutor['name'] ?? '' ),
            'rating'       => (string) ( $tutor['rating'] ?? '0' ),
            'reviews'      => (string) ( $tutor['reviews'] ?? '0' ),
            'level'        => trim( strtoupper( $label_level . ' ' . $label_subject ) ),
            'url'          => function_exists( 'mgk_teacher_profile_url' ) ? mgk_teacher_profile_url( $tutor ) : '#',
            'image_alt'    => (string) ( $tutor['name'] ?? 'Tutor profile preview' ),
            'same_subject' => $same_subject,
            'same_level'   => $same_level,
        ];
    }

    if ( ! $prepared ) {
        return $fallback;
    }

    usort( $prepared, function ( $a, $b ) {
        if ( $a['same_subject'] !== $b['same_subject'] ) {
            return $a['same_subject'] ? -1 : 1;
        }

        if ( $a['same_level'] !== $b['same_level'] ) {
            return $a['same_level'] ? -1 : 1;
        }

        $rating = (float) $b['rating'] <=> (float) $a['rating'];
        if ( $rating !== 0 ) {
            return $rating;
        }

        $reviews = (int) $b['reviews'] <=> (int) $a['reviews'];
        if ( $reviews !== 0 ) {
            return $reviews;
        }

        return strcasecmp( $a['name'], $b['name'] );
    } );

    return array_slice( $prepared, 0, 3 );
}

function mgk_switch_tutor_matches_label( $needle, $values ) {
    $needle = mgk_switch_tutor_normalize_label( $needle );
    if ( $needle === '' ) {
        return false;
    }

    foreach ( (array) $values as $value ) {
        $value = mgk_switch_tutor_normalize_label( $value );
        if ( $value === '' ) {
            continue;
        }

        if ( $value === $needle || strpos( $value, $needle ) !== false || strpos( $needle, $value ) !== false ) {
            return true;
        }
    }

    return false;
}

function mgk_switch_tutor_normalize_label( $value ) {
    $value = strtolower( trim( (string) $value ) );
    $value = str_replace( [ 'mathematics', 'maths' ], 'math', $value );
    $value = preg_replace( '/[^a-z0-9]+/', ' ', $value );
    return trim( preg_replace( '/\s+/', ' ', (string) $value ) );
}

function mgk_switch_tutor_excluded_slugs( $current_tutor_id ) {
    $slug = sanitize_title( (string) $current_tutor_id );
    $aliases = [
        'demo-ms-lee' => [ 'demo-ms-lee', 'ms-lee', 'ms-lee-yi-ling' ],
    ];

    return array_values( array_unique( array_filter( array_merge( [ $slug ], $aliases[ $slug ] ?? [] ) ) ) );
}

function mgk_get_switch_tutor_demo_suggestions() {
    return [
        [
            'id'       => 'demo-ms-sim',
            'name'     => 'Ms Sim P.',
            'rating'   => '4.9',
            'level'    => 'P5 MATH',
            'url'      => mgk_teacher_profile_url( [ 'slug' => 'ms-sim-pei-hua' ] ),
            'image_alt'=> 'Tutor profile preview',
        ],
        [
            'id'       => 'demo-mr-lim',
            'name'     => 'Mr Lim Y.',
            'rating'   => '4.8',
            'level'    => 'P5 MATH',
            'url'      => mgk_teacher_profile_url( [ 'slug' => 'mr-lim-boon-kiat' ] ),
            'image_alt'=> 'Tutor profile preview',
        ],
        [
            'id'       => 'demo-ms-tan',
            'name'     => 'Ms Tan A.',
            'rating'   => '4.7',
            'level'    => 'P5 MATH',
            'url'      => mgk_teacher_profile_url( [ 'slug' => 'ms-sarah-tan' ] ),
            'image_alt'=> 'Tutor profile preview',
        ],
    ];
}

function mgk_get_end_tuition_facts( $child_id, $booking_id ) {
    return [
        'facts' => [
            '2 UNUSED LESSONS - REFUND PREVIEW SHOWN PER BR-07 / FR-PAY-10',
            'LESSON HISTORY & PROGRESS STAY IN YOUR ACCOUNT',
            'YOU CAN COME BACK ANYTIME',
        ],
    ];
}

function mgk_get_lapsed_package_context( $child_id, $tutor_id ) {
    return [
        'tutor_name'      => 'Ms Lee',
        'availability'    => 'Ms Lee is still available',
        'slot_note'       => 'SAME SLOT WED 4PM LIKELY OPEN',
        'reactivate_url'  => mgk_get_buy_package_url( $child_id ),
        'different_url'   => mgk_get_switch_tutor_url( $child_id ),
        'discount_note'   => 'RETURNING-STUDENT 5% · AMOUNT PER-TENANT CONFIG · PM TO CONFIRM',
    ];
}

add_shortcode( 'mgk_parent_package_context', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'sec_label'       => 'SEC 1 Context',
        'hide_sec_label'  => '',
        'hide_avatar'     => '',
        'hide_headline'   => '',
        'hide_meta'       => '',
        'hide_prompt'     => '',
    ], $atts );
    return mgk_render_package_part( 'context', $atts );
} );

add_shortcode( 'mgk_parent_package_options', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'sec_label'       => 'SEC 2 Five Options (equal weight)',
        'hide_sec_label'  => '',
        'hide_card_icons' => '',
        'hide_prices'     => '',
        'hide_details'    => '',
        'hide_buttons'    => '',
        'hide_note'       => '',
        'note'            => '☼ ALL 5 BUTTONS SAME SIZE/CONTRAST. "END" IS NOT GREYED, HIDDEN, OR BURIED (FR-REVIEW-05). NO COUNTDOWN TIMERS, NO PRE-TICKED ADD-ONS.',
    ], $atts );
    return mgk_render_package_part( 'options', $atts );
} );

add_shortcode( 'mgk_parent_package_pause', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'sec_label'          => 'SEC 3 Pause detail (BR-16)',
        'hide_sec_label'     => '',
        'hide_heading'       => '',
        'heading'            => 'II Pause conditions (BR-16)',
        'hide_conditions'    => '',
        'hide_date_controls' => '',
        'pause_from_label'   => 'Pause from:',
        'resume_label'       => 'Resume:',
        'confirm_label'      => 'Confirm pause',
        'hide_footer'        => '',
        'footer'             => 'Need help deciding? Message the agency · No charge until you confirm a choice.',
    ], $atts );
    return mgk_render_package_part( 'pause-detail', $atts );
} );

add_shortcode( 'mgk_parent_package_switch', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'hide_heading'       => '',
        'heading'            => 'Switch to a new tutor',
        'hide_subline'       => '',
        'subline'            => "KEEP EMMA'S REMAINING 2 LESSONS · WE RE-MATCH TO A SIMILAR TUTOR (FR-REVIEW-06)",
        'hide_reason_heading'=> '',
        'reason_heading'     => 'Why are you switching? (optional, helps re-match)',
        'hide_chips'         => '',
        'chip_1'             => 'Scheduling',
        'chip_2'             => 'Teaching style',
        'chip_3'             => 'Location',
        'chip_4'             => 'Other',
        'hide_tutors'        => '',
        'hide_button'        => '',
        'button'             => 'Request re-match (free) →',
        'button_url'         => '#',
        'hide_note'          => '',
        'note'               => 'REMAINING LESSONS TRANSFER TO NEW TUTOR. REASON CHIPS FEED MATCHING ALGORITHM. NO FEE TO SWITCH.',
    ], $atts );
    return mgk_render_package_part( 'switch-tutor', $atts );
} );

add_shortcode( 'mgk_parent_package_end', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'hide_heading'      => '',
        'heading'           => 'End tuition for Emma?',
        'hide_subline'      => '',
        'subline'           => "YOU'RE CHOOSING TO STOP AFTER THE CURRENT PACKAGE. THAT'S COMPLETELY FINE.",
        'hide_facts'        => '',
        'hide_actions'      => '',
        'keep_label'        => 'Keep my package',
        'keep_url'          => '/parent/trial/',
        'confirm_label'     => 'Confirm end',
        'confirm_url'       => '/parent/trial/lapsed/',
        'hide_equal_note'   => '',
        'equal_note'        => 'NO "ARE YOU SURE YOU\'LL REGRET THIS?" COPY. BOTH BUTTONS EQUAL. OPTIONAL 1-TAP REASON (SKIPPABLE).',
        'hide_bottom_note'  => '',
        'bottom_note'       => 'FR-REVIEW-05: CONFIRM STEP GIVES FACTS (REFUND, DATA, RETURN), NOT FRICTION OR GUILT.',
    ], $atts );
    return mgk_render_package_part( 'end-tuition', $atts );
} );

add_shortcode( 'mgk_parent_package_lapsed', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'hide_badge'       => '',
        'badge'            => 'Lapsed variant',
        'hide_heading'     => '',
        'heading'          => 'Welcome back — pick up where Emma left off?',
        'hide_subline'     => '',
        'subline'          => 'YOUR PACKAGE LAPSED 14 DAYS AGO. AS A RETURNING STUDENT, ENJOY 5% OFF YOUR NEXT PACKAGE (BR-06).',
        'hide_tutor'       => '',
        'hide_primary'     => '',
        'primary_label'    => 'Reactivate with Ms Lee →',
        'hide_secondary'   => '',
        'secondary_label'  => 'Choose a different tutor',
        'hide_discount'    => '',
        'hide_bottom_note' => '',
        'bottom_note'      => 'FR-REVIEW-07 WIN-BACK: GENTLE RE-ENGAGEMENT FOR LAPSED PARENTS. 5% RETURNING DISCOUNT (BR-06). STILL NO DARK PATTERNS - OPT-IN, DISMISSABLE.',
    ], $atts );
    return mgk_render_package_part( 'lapsed-package', $atts );
} );

add_shortcode( 'mgk_parent_dash_welcome', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'welcome_prefix'  => 'Welcome back,',
        'viewing_label'   => 'VIEWING',
        'add_child_label' => '+ Add child',
        'hide_subline'    => '',
        'hide_switcher'   => '',
    ], $atts );
    return mgk_render_dashboard_part( 'welcome-switcher', $atts );
} );

add_shortcode( 'mgk_parent_dash_renewal', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'renew_label'   => 'Renew Package →',
        'snooze_label'  => 'Snooze 7d ×',
        'hide_snooze'   => '',
    ], $atts );
    return mgk_render_dashboard_part( 'renewal-nudge', $atts );
} );

add_shortcode( 'mgk_parent_dash_kpis', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [], $atts );
    return mgk_render_dashboard_part( 'kpi-tiles', $atts );
} );

add_shortcode( 'mgk_parent_dash_progress_logs', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'logs_button' => 'View all lesson logs →',
    ], $atts );
    return mgk_render_dashboard_part( 'progress-logs', $atts );
} );

add_shortcode( 'mgk_parent_dash_upcoming', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'heading'       => 'Upcoming lessons',
        'all_label'     => 'ALL CHILDREN',
        'calendar_label'=> '+ CALENDAR SYNC',
        'book_label'    => '+ BOOK NEXT LESSON',
        'reschedule'    => 'Reschedule',
        'message'       => 'Message',
    ], $atts );
    return mgk_render_dashboard_part( 'upcoming-lessons', $atts );
} );

add_shortcode( 'mgk_parent_dash_action_cards', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'billing_heading' => 'Billing & package',
        'invoice_label'   => 'View invoices / receipts',
        'message_heading' => 'Message tutor',
        'chat_label'      => 'Open chat →',
        'message_note'    => 'AGENCY-MONITORED · PHONE MASKED',
        'buy_heading'     => 'Need more lessons?',
        'buy_copy'        => 'BUY A NEW PACKAGE & SAVE UP TO 10%',
        'buy_label'       => 'Buy Package (FR-BOOK-08) →',
        'buy_note'        => 'RETURNING-STUDENT 5% MAY APPLY (BR-06)',
    ], $atts );
    return mgk_render_dashboard_part( 'action-cards', $atts );
} );

add_shortcode( 'mgk_parent_dash_quick_links', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [], $atts );
    return mgk_render_dashboard_part( 'quick-links', $atts );
} );

add_shortcode( 'mgk_parent_dash_footer', function ( $atts ) {
    $atts = mgk_parent_shortcode_atts( [
        'logo' => '[AGENCY LOGO]',
        'line' => '© 2026 · Powered by Margick · MOE Registered · PDPA compliant',
    ], $atts );
    return mgk_render_dashboard_part( 'dashboard-footer', $atts );
} );
