<?php
/**
 * S23 tutor earnings and payout shell.
 *
 * Lesson ledger, commission math, payouts and invoices are DATA CORE. Elementor
 * edits labels, visibility and visual shell only.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_earnings_url() {
    return mgk_url( '/tutor/earnings/' );
}

function mgk_tutor_earnings_context() {
    $context = [
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

    return apply_filters( 'mgk_tutor_earnings_context', $context );
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
    ];

    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( $defaults, $atts )
        : shortcode_atts( $defaults, $atts );

    return mgk_render_tutor_earnings_part( 'earnings-page', $atts );
} );
