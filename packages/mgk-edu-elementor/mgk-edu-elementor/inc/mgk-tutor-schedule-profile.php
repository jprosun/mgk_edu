<?php
/**
 * S24 tutor schedule and profile shell.
 *
 * Availability/profile/rate data is DATA CORE. Elementor edits tabs, labels,
 * visibility and visual shell only.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_schedule_url() {
    return mgk_url( '/tutor/schedule/' );
}

function mgk_tutor_schedule_profile_context() {
    $context = [
        'days' => [ 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN' ],
        'slots' => [
            [ 'label' => '9-12', 'cells' => [ '', '', '', '', '', '✓', '✓' ] ],
            [ 'label' => '12-5', 'cells' => [ '', '', '', '', '', '✓', '✓' ] ],
            [ 'label' => '5-9', 'cells' => [ '✓', '✓', '', '✓', '✓', 'blk', '' ] ],
        ],
        'profile' => [
            'rate' => '$65/h',
            'new_rate' => 'NEW RATE · $75/H',
            'subjects' => [ 'P5-P6 Math', 'PSLE Sci', 'Sec1-2 Math' ],
        ],
    ];

    return apply_filters( 'mgk_tutor_schedule_profile_context', $context );
}

function mgk_render_tutor_schedule_profile_part( $part, $atts = [] ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    return mgk_render_part( 'template-parts/sections/tutor-schedule-profile/' . $part, [
        'atts'    => $atts,
        'context' => mgk_tutor_schedule_profile_context(),
    ] );
}

add_shortcode( 'mgk_tutor_schedule_profile', function ( $atts ) {
    $defaults = [
        'hidden'              => '',
        'hide_topbar'         => '',
        'brand_label'         => '[LOGO] Tutor Portal · Schedule & Profile',
        'schedule_tab'        => 'Schedule',
        'profile_tab'         => 'Profile',
        'hide_availability'   => '',
        'sec_availability'    => 'A · Availability template',
        'availability_title'  => 'Weekly availability (recurring)',
        'availability_sub'    => 'SET ONCE · REPEATS EVERY WEEK · POWERS PARENT SLOT PICKER (S10)',
        'reset_label'         => '↻ Reset',
        'edit_avail_label'    => 'Edit availability',
        'legend_available'    => 'Available (recurring)',
        'legend_block'        => 'Ad-hoc block',
        'legend_off'          => 'Off',
        'hide_block'          => '',
        'sec_block'           => 'B · Add ad-hoc block (override)',
        'block_sub'           => 'ONE-OFF EXCEPTION ON TOP OF THE RECURRING TEMPLATE — E.G. HOLIDAY, EXAM WEEK.',
        'block_date_label'    => 'DATE',
        'block_date'          => 'Sat 14 Jun',
        'block_type_label'    => 'TYPE',
        'block_type'          => 'Block (unavailable)',
        'block_from_label'    => 'FROM',
        'block_from'          => '17:00',
        'block_to_label'      => 'TO',
        'block_to'            => '21:00',
        'add_block_label'     => 'Add block',
        'hide_sync'           => '',
        'sec_sync'            => 'C · Sync',
        'sync_title'          => 'Availability feeds the parent slot picker (S10)',
        'sync_body'           => 'RECURRING TEMPLATE + AD-HOC BLOCKS RESOLVE INTO BOOKABLE SLOTS IN REAL TIME (FR-TUTOR-09). BOOKED SLOTS AUTO-REMOVED; BLOCKS HIDE SLOTS.',
        'sync_status'         => 'LAST SYNCED TO S10: JUST NOW ✓',
        'hide_profile'        => '',
        'sec_profile'         => 'D · Profile edit',
        'profile_title'       => 'Profile edit (FR-TUTOR-10)',
        'bio_label'           => 'BIO ↘',
        'change_photo_label'  => 'Change photo',
        'demo_title'          => 'Demo video',
        'current_label'       => '▶ Current',
        'replace_video_label' => 'Replace video',
        'demo_note'           => '△ NEW DEMO VIDEO — RE-APPROVAL REQUIRED BEFORE IT GOES LIVE',
        'subjects_label'      => 'SUBJECTS & LEVELS ↘',
        'add_subject_label'   => '+ Add',
        'rate_label'          => 'HOURLY RATE ↘',
        'rate_note'           => '⏳ Rate change pending agency approval (Model A). Current rate stays live until approved. Model B may self-set within band — PM to confirm.',
        'save_label'          => 'Save profile',
        'preview_label'       => 'Preview public profile (S03)',
    ];

    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( $defaults, $atts )
        : shortcode_atts( $defaults, $atts );

    return mgk_render_tutor_schedule_profile_part( 'schedule-profile-page', $atts );
} );
