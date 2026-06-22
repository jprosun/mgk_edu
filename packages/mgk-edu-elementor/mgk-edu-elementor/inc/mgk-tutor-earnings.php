<?php
/**
 * S23 tutor earnings + payout.
 *
 * REAL, read-only ledger computed from logged lessons (mg_lesson) × the tutor's
 * hourly rate, for a selected month. Net = gross × (1 − commission%). The
 * commission rate is an AGENCY setting (option `mgk_tutor_commission_pct`),
 * default 0 — Margick itself takes no commission (see payment-tier decision), so
 * the demo's hardcoded "first-month 50%" / Model-A/B split is NOT used in live
 * mode. Payout records aren't tracked yet → the live view shows an honest
 * "pending payout" line, never fabricated payout history.
 *
 * The Elementor editor keeps the original demo so the section stays designable.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_earnings_url() {
    return mgk_url( '/tutor/earnings/' );
}

/** Admin / Elementor edit / preview → demo. */
function mgk_tutor_earnings_is_editor() {
    if ( is_admin() ) return true;
    if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance ) {
        $p = \Elementor\Plugin::$instance;
        if ( isset( $p->editor ) && $p->editor->is_edit_mode() ) return true;
        if ( isset( $p->preview ) && $p->preview->is_preview_mode() ) return true;
    }
    return false;
}

/** Agency commission percentage taken from the tutor's gross (0–100, default 0). */
function mgk_tutor_commission_pct() {
    $pct = (float) get_option( 'mgk_tutor_commission_pct', 0 );
    return max( 0, min( 100, $pct ) );
}

/** Selected earnings month as 'Y-m' (from ?month=, else the current month). */
function mgk_tutor_earnings_month() {
    $m = sanitize_text_field( (string) mgk_get_query_filter( 'month', '' ) );
    if ( preg_match( '/^\d{4}-\d{2}$/', $m ) ) return $m;
    return current_time( 'Y-m' );
}

/** All logged lessons for a tutor (newest first), as raw post ids. */
function mgk_tutor_lesson_ids( $teacher_id ) {
    $teacher_id = (int) $teacher_id;
    if ( ! $teacher_id || ! post_type_exists( 'mg_lesson' ) ) return [];
    return get_posts( [
        'post_type'      => 'mg_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => 500,
        'no_found_rows'  => true,
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => 'mgk_lesson_tutor_id', 'value' => $teacher_id ] ],
    ] );
}

/** Format a money amount with the given symbol, trimming trailing .00. */
function mgk_tutor_money( $amount, $symbol = '$' ) {
    $amount = (float) $amount;
    $s = number_format( $amount, 2 );
    if ( substr( $s, -3 ) === '.00' ) $s = substr( $s, 0, -3 );
    return $symbol . $s;
}

/**
 * Build the real earnings context for a tutor + selected month.
 * Gross per lesson = hourly rate × (duration_min / 60). NO_SHOW lessons are
 * listed but not billable. Net applies the agency commission.
 */
function mgk_tutor_earnings_real_context( $teacher_id ) {
    $teacher_id = (int) $teacher_id;
    $month      = mgk_tutor_earnings_month();
    $rate       = (int) get_post_meta( $teacher_id, 'mgk_rate_num', true );
    $pct        = mgk_tutor_commission_pct();
    $symbol     = '$';

    $ids        = mgk_tutor_lesson_ids( $teacher_id );
    $has_any    = ! empty( $ids );

    $ledger      = [];
    $gross_total = 0.0;
    $net_total   = 0.0;
    $billable    = 0;

    foreach ( $ids as $id ) {
        $id         = (int) $id;
        $date_raw   = (string) get_post_meta( $id, 'mgk_lesson_date', true );
        if ( $date_raw === '' ) {
            $sub = (string) get_post_meta( $id, 'mgk_lesson_submitted_at', true );
            $date_raw = $sub ? substr( $sub, 0, 10 ) : '';
        }
        // Filter to the selected month (by the lesson date string).
        if ( $date_raw === '' || substr( $date_raw, 0, 7 ) !== $month ) continue;

        $att      = strtoupper( (string) get_post_meta( $id, 'mgk_lesson_attendance', true ) );
        $dur_min  = (int) get_post_meta( $id, 'mgk_lesson_duration_min', true ) ?: 60;
        $hours    = $dur_min / 60;
        $is_bill  = in_array( $att, [ 'ATTENDED', 'LATE' ], true ); // BR-20 unified
        $gross    = $is_bill ? round( $rate * $hours, 2 ) : 0.0;
        $net      = round( $gross * ( 1 - $pct / 100 ), 2 );

        if ( $is_bill ) {
            $gross_total += $gross;
            $net_total   += $net;
            $billable++;
        }

        $ts = strtotime( $date_raw . ' 00:00:00' );
        $ledger[] = [
            'lesson'   => get_the_title( $id ) ?: 'Lesson',
            'date'     => $ts ? date_i18n( 'j M', $ts ) : $date_raw,
            'date_ts'  => $ts ?: 0,
            'rate'     => $rate ? mgk_tutor_money( $rate, $symbol ) . '/h × ' . rtrim( rtrim( number_format( $hours, 1 ), '0' ), '.' ) . 'h' : '—',
            'status'   => $is_bill ? ( $att === 'LATE' ? 'Logged (late)' : 'Logged' ) : 'No-show',
            'billable' => $is_bill,
            'net'      => $is_bill ? mgk_tutor_money( $net, $symbol ) : '—',
        ];
    }

    // Newest lesson first.
    usort( $ledger, function ( $a, $b ) { return $b['date_ts'] <=> $a['date_ts']; } );

    $commission_total = round( $gross_total - $net_total, 2 );
    $ts_month = strtotime( $month . '-01' );

    return [
        'mode'           => 'real',
        'month'          => $month,
        'month_label'    => $ts_month ? date_i18n( 'F Y', $ts_month ) : $month,
        'prev_url'       => add_query_arg( 'month', date( 'Y-m', strtotime( $month . '-01 -1 month' ) ), mgk_get_tutor_earnings_url() ),
        'next_url'       => add_query_arg( 'month', date( 'Y-m', strtotime( $month . '-01 +1 month' ) ), mgk_get_tutor_earnings_url() ),
        'prev_label'     => '‹ ' . date_i18n( 'M', strtotime( $month . '-01 -1 month' ) ),
        'next_label'     => date_i18n( 'M', strtotime( $month . '-01 +1 month' ) ) . ' ›',
        'rate'           => $rate,
        'rate_label'     => $rate ? mgk_tutor_money( $rate, $symbol ) . '/h' : 'not set',
        'commission_pct' => $pct,
        'summary'        => [
            'gross'      => mgk_tutor_money( $gross_total, $symbol ),
            'commission' => $pct > 0 ? '-' . mgk_tutor_money( $commission_total, $symbol ) : mgk_tutor_money( 0, $symbol ),
            'net'        => mgk_tutor_money( $net_total, $symbol ),
            'lessons'    => $billable,
        ],
        'commission_note' => $pct > 0
            ? sprintf( 'Net reflects the agency commission of %s%%. Earnings are confirmed once each lesson is logged.', rtrim( rtrim( number_format( $pct, 1 ), '0' ), '.' ) )
            : 'You keep 100% of your rate — no commission is deducted. Earnings are confirmed once each lesson is logged.',
        'pending_net'    => mgk_tutor_money( $net_total, $symbol ),
        'ledger'         => $ledger,
        'has_any'        => $has_any,
        'month_empty'    => empty( $ledger ),
        'dashboard_url'  => function_exists( 'mgk_get_tutor_dashboard_url' ) ? mgk_get_tutor_dashboard_url() : mgk_url( '/tutor/dashboard/' ),
        'schedule_url'   => function_exists( 'mgk_get_tutor_schedule_url' ) ? mgk_get_tutor_schedule_url() : mgk_url( '/tutor/schedule/' ),
    ];
}

function mgk_tutor_earnings_demo_context() {
    return [
        'mode'    => 'demo',
        'summary' => [
            [ 'label' => 'GROSS EARNED', 'value' => '$3,340', 'hot' => true ],
            [ 'label' => 'COMMISSION', 'value' => '-$1,000' ],
            [ 'label' => 'NET PAYABLE', 'value' => '$2,340', 'hot_value' => true ],
            [ 'label' => 'LESSONS', 'value' => '36', 'dark' => true ],
        ],
        'model_a' => [
            [ 'label' => 'GROSS (36 LESSONS)', 'value' => '$3,340' ],
            [ 'label' => 'AGENCY COMMISSION (FIRST-MONTH 50%)', 'value' => '-$1,000', 'hot' => true ],
            [ 'label' => 'Net to you', 'value' => '$2,340', 'hot' => true, 'strong' => true ],
        ],
        'payouts' => [
            [ 'date' => '31 May', 'amount' => '$2,180', 'status' => '✓ Paid' ],
            [ 'date' => '30 Apr', 'amount' => '$1,950', 'status' => '✓ Paid' ],
            [ 'date' => '31 Mar', 'amount' => '$2,020', 'status' => '✓ Paid' ],
        ],
        'ledger' => [
            [ 'lesson' => 'Aaron · P5 Math', 'date' => '3 Jun', 'rate' => '$65', 'status' => 'Log ✓', 'net' => '$45.50' ],
            [ 'lesson' => 'Mei · Sec2 Math', 'date' => '3 Jun', 'rate' => '$70', 'status' => 'Log ✓', 'net' => '$49.00' ],
            [ 'lesson' => 'Aaron · P5 Math', 'date' => '1 Jun', 'rate' => '$65', 'status' => 'Log overdue', 'net' => 'pending' ],
        ],
        'invoices' => [
            [ 'label' => 'JUNE 2026', 'status' => 'GENERATING ○' ],
            [ 'label' => 'MAY 2026', 'status' => 'DOWNLOAD ↓' ],
            [ 'label' => 'APR 2026', 'status' => 'DOWNLOAD ↓' ],
            [ 'label' => 'MAR 2026', 'status' => 'DOWNLOAD ↓' ],
        ],
    ];
}

function mgk_tutor_earnings_context() {
    if ( mgk_tutor_earnings_is_editor() ) {
        return apply_filters( 'mgk_tutor_earnings_context', mgk_tutor_earnings_demo_context() );
    }

    $tid = function_exists( 'mgk_current_tutor_teacher_id' ) ? mgk_current_tutor_teacher_id() : 0;
    if ( ! $tid ) {
        return apply_filters( 'mgk_tutor_earnings_context', [
            'mode'      => 'gated',
            'login_url' => function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' ),
        ] );
    }

    return apply_filters( 'mgk_tutor_earnings_context', mgk_tutor_earnings_real_context( $tid ) );
}

function mgk_render_tutor_earnings_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/tutor-earnings/' . $part, [
        'atts'    => $atts,
        'context' => mgk_tutor_earnings_context(),
    ] );
}

add_shortcode( 'mgk_tutor_earnings', function ( $atts ) {
    $defaults = [
        'hidden'              => '',
        'hide_topbar'         => '',
        'brand_label'         => '[LOGO] Tutor Portal · Earnings',
        'nav_dashboard'       => 'Dashboard',
        'nav_earnings'        => 'Earnings',
        'nav_schedule'        => 'Schedule',
        'nav_profile'         => 'Profile',
        'hide_summary'        => '',
        'sec_summary'         => 'SEC 1 Summary',
        'page_title'          => 'Earnings',
        'month_label'         => 'June 2026',
        'prev_month_label'    => '‹ May',
        'current_month_label' => 'June 2026',
        'next_month_label'    => 'Jul ›',
        'hide_commission'     => '',
        'sec_commission'      => '2',
        'commission_title'    => 'Commission breakdown',
        'model_a_label'       => 'Model A',
        'model_b_label'       => 'Model B',
        'model_a_title'       => 'MODEL A · ACTIVE (BR-17)',
        'model_a_body'        => 'Agency keeps 50% of first month, balance paid end of month',
        'model_a_note'        => 'FIRST-MONTH SPLIT APPLIES TO NEW STUDENT-TUTOR PAIRINGS; SUBSEQUENT MONTHS AT STANDARD RATE. EXACT % & DURATION PM TO CONFIRM.',
        'model_b_title'       => 'MODEL B · ALTERNATE (BR-18)',
        'model_b_body'        => 'Tutor take-rate · default 70%',
        'model_b_note'        => 'CONFIGURABLE 60-80% (AGENCY-SET). AT 70%: NET = $2,338. SWITCH MODEL = AGENCY APPROVAL.',
        'hide_payouts'        => '',
        'sec_payouts'         => '3',
        'payout_title'        => 'Payout history',
        'pending_label'       => 'PENDING',
        'pending_body'        => '$2,340 · scheduled 30 Jun · PayNow ••• 26',
        'hide_ledger'         => '',
        'sec_ledger'          => '4',
        'ledger_title'        => 'Earnings ledger · June (per lesson)',
        'ledger_note'         => 'NET PER LESSON REFLECTS ACTIVE COMMISSION MODEL. EARNINGS CONFIRMED ONLY AFTER LESSON LOG SUBMITTED (LINKS S22).',
        'hide_invoices'       => '',
        'sec_invoices'        => '5',
        'invoice_title'       => 'Invoices & statements',
        'download_all_label'  => 'Download all (PDF)',
        'hide_empty'          => '',
        'empty_title'         => 'EMPTY STATE · new tutor',
        'empty_body'          => '"NO EARNINGS YET — COMPLETE YOUR FIRST LESSON & LOG TO SEE PAYOUTS HERE." · CTA — SET AVAILABILITY (S24).',
        // ── Live copy ──
        'live_gross_label'    => 'Gross earned',
        'live_commission_label' => 'Commission',
        'live_net_label'      => 'Net payable',
        'live_lessons_label'  => 'Lessons',
        'live_ledger_title'   => 'Lessons this month',
        'live_pending_label'  => 'Pending payout',
        'live_pending_note'   => 'Paid out by your agency. Payout scheduling is handled outside this dashboard for now.',
        'live_empty_month'    => 'No logged lessons in this month yet.',
        'live_empty_title'    => 'No earnings yet',
        'live_empty_body'     => 'Complete and log your first lesson to see it here.',
        'live_set_availability' => 'Set your availability →',
        'gated_title'         => 'Sign in to view earnings',
        'gated_body'          => 'Your earnings are private. Please sign in to your tutor account.',
        'gated_cta'           => 'Tutor sign in →',
    ];

    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( $defaults, $atts )
        : shortcode_atts( $defaults, $atts );

    return mgk_render_tutor_earnings_part( 'earnings-page', $atts );
} );
