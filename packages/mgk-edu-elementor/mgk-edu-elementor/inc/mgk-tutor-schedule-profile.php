<?php
/**
 * S24 tutor schedule + profile.
 *
 * Live mode lets a signed-in tutor edit their OWN recurring weekly availability
 * (`_mgk_weekly_availability_json`, the exact format the booking engine + admin
 * metabox read — see inc/booking/booking-availability.php) and their public
 * profile (rate, subjects/levels, bio). No JS — plain admin-post forms, mirroring
 * the admin availability saver's field shape so the engine consumes it unchanged.
 *
 * The Elementor editor keeps the original demo so the section stays designable.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_get_tutor_schedule_url() {
    return mgk_url( '/tutor/schedule/' );
}

/** Admin / Elementor edit / preview → demo. */
function mgk_tutor_schedule_is_editor() {
    if ( is_admin() ) return true;
    if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance ) {
        $p = \Elementor\Plugin::$instance;
        if ( isset( $p->editor ) && $p->editor->is_edit_mode() ) return true;
        if ( isset( $p->preview ) && $p->preview->is_preview_mode() ) return true;
    }
    return false;
}

/** Max recurring time-ranges a tutor can set per weekday in the front-end editor. */
function mgk_tutor_schedule_rows_per_day() {
    return (int) apply_filters( 'mgk_tutor_schedule_rows_per_day', 3 );
}

/** Subject/level options with the tutor's current selection flagged. */
function mgk_tutor_schedule_term_options( $taxonomy, $teacher_id ) {
    $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
    if ( is_wp_error( $terms ) || ! $terms ) return [];
    $selected = wp_get_post_terms( (int) $teacher_id, $taxonomy, [ 'fields' => 'ids' ] );
    $selected = is_wp_error( $selected ) ? [] : array_map( 'intval', $selected );
    $out = [];
    foreach ( $terms as $t ) {
        $out[] = [ 'id' => (int) $t->term_id, 'name' => $t->name, 'checked' => in_array( (int) $t->term_id, $selected, true ) ];
    }
    return $out;
}

function mgk_tutor_schedule_demo_context() {
    return [
        'mode'  => 'demo',
        'days'  => [ 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN' ],
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
}

/** Real edit context for the signed-in tutor. */
function mgk_tutor_schedule_real_context( $teacher_id ) {
    $teacher_id = (int) $teacher_id;
    $weekly = function_exists( 'mgk_get_tutor_weekly_availability' ) ? mgk_get_tutor_weekly_availability( $teacher_id ) : [];
    $rows   = mgk_tutor_schedule_rows_per_day();

    // Pad each day to a fixed number of rows so the no-JS form has empty slots.
    $day_keys = function_exists( 'mgk_avail_days' ) ? mgk_avail_days() : [ 'mon','tue','wed','thu','fri','sat','sun' ];
    $grid = [];
    foreach ( $day_keys as $d ) {
        $ranges = (array) ( $weekly[ $d ] ?? [] );
        for ( $i = count( $ranges ); $i < $rows; $i++ ) {
            $ranges[] = [ 'start' => '', 'end' => '', 'mode' => 'ONLINE' ];
        }
        $grid[ $d ] = array_slice( $ranges, 0, max( $rows, count( $ranges ) ) );
    }

    $post = get_post( $teacher_id );
    $notice = sanitize_key( (string) mgk_get_query_filter( 'mgk_saved', '' ) );

    return [
        'mode'         => 'real',
        'teacher_id'   => $teacher_id,
        'day_keys'     => $day_keys,
        'day_labels'   => [ 'mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun' ],
        'grid'         => $grid,
        'modes'        => function_exists( 'mgk_avail_modes' ) ? mgk_avail_modes() : [ 'ONLINE','HOME','CENTER','HYBRID' ],
        'rows_per_day' => $rows,
        'rate'         => (int) get_post_meta( $teacher_id, 'mgk_rate_num', true ),
        'bio'          => $post ? (string) ( $post->post_excerpt ?: wp_strip_all_tags( $post->post_content ) ) : '',
        'subjects'     => mgk_tutor_schedule_term_options( 'mgk_subject', $teacher_id ),
        'levels'       => mgk_tutor_schedule_term_options( 'mgk_level', $teacher_id ),
        'action'       => admin_url( 'admin-post.php' ),
        'sched_nonce'  => wp_create_nonce( 'mgk_tutor_save_schedule_' . $teacher_id ),
        'prof_nonce'   => wp_create_nonce( 'mgk_tutor_save_profile_' . $teacher_id ),
        'notice'       => $notice,
        'dashboard_url'=> function_exists( 'mgk_get_tutor_dashboard_url' ) ? mgk_get_tutor_dashboard_url() : mgk_url( '/tutor/dashboard/' ),
        'earnings_url' => function_exists( 'mgk_get_tutor_earnings_url' ) ? mgk_get_tutor_earnings_url() : mgk_url( '/tutor/earnings/' ),
        'verified'     => (bool) get_post_meta( $teacher_id, 'mgk_is_verified', true ),
    ];
}

function mgk_tutor_schedule_profile_context() {
    if ( mgk_tutor_schedule_is_editor() ) {
        return apply_filters( 'mgk_tutor_schedule_profile_context', mgk_tutor_schedule_demo_context() );
    }
    $tid = function_exists( 'mgk_current_tutor_teacher_id' ) ? mgk_current_tutor_teacher_id() : 0;
    if ( ! $tid ) {
        return apply_filters( 'mgk_tutor_schedule_profile_context', [
            'mode'      => 'gated',
            'login_url' => function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' ),
        ] );
    }
    return apply_filters( 'mgk_tutor_schedule_profile_context', mgk_tutor_schedule_real_context( $tid ) );
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

/* ── Save handlers (tutor edits their OWN record only) ───────────────────── */

/** Guard: resolve + verify the current tutor owns $teacher_id, else redirect. */
function mgk_tutor_schedule_guard( $teacher_id, $nonce_action ) {
    $teacher_id = (int) $teacher_id;
    $nonce = isset( $_POST['_mgk_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_mgk_nonce'] ) ) : '';
    $mine  = function_exists( 'mgk_current_tutor_teacher_id' ) ? mgk_current_tutor_teacher_id() : 0;
    if ( ! $mine || $mine !== $teacher_id || ! wp_verify_nonce( $nonce, $nonce_action . '_' . $teacher_id ) ) {
        wp_safe_redirect( add_query_arg( 'mgk_saved', 'denied', mgk_get_tutor_schedule_url() ) );
        exit;
    }
    return $teacher_id;
}

/** Save weekly availability (mirrors the admin saver's JSON shape exactly). */
function mgk_tutor_save_schedule_handler() {
    $teacher_id = mgk_tutor_schedule_guard( (int) ( $_POST['mgk_teacher_id'] ?? 0 ), 'mgk_tutor_save_schedule' );

    $weekly = [];
    $av_in  = isset( $_POST['mgk_av'] ) && is_array( $_POST['mgk_av'] ) ? wp_unslash( $_POST['mgk_av'] ) : [];
    foreach ( mgk_avail_days() as $day ) {
        $starts = (array) ( $av_in[ $day ]['start'] ?? [] );
        $ends   = (array) ( $av_in[ $day ]['end'] ?? [] );
        $modes  = (array) ( $av_in[ $day ]['mode'] ?? [] );
        $ranges = [];
        foreach ( $starts as $i => $s ) {
            $start = mgk_avail_clean_time( $s );
            $end   = mgk_avail_clean_time( $ends[ $i ] ?? '' );
            $mode  = strtoupper( (string) ( $modes[ $i ] ?? 'ONLINE' ) );
            if ( ! in_array( $mode, mgk_avail_modes(), true ) ) $mode = 'ONLINE';
            if ( $start && $end && $start < $end ) {
                $ranges[] = [ 'start' => $start, 'end' => $end, 'mode' => $mode ];
            }
        }
        if ( $ranges ) $weekly[ $day ] = $ranges;
    }
    update_post_meta( $teacher_id, '_mgk_weekly_availability_json', wp_slash( wp_json_encode( $weekly ) ) );
    do_action( 'mgk_tutor_availability_saved', $teacher_id, $weekly );

    wp_safe_redirect( add_query_arg( 'mgk_saved', 'schedule', mgk_get_tutor_schedule_url() ) );
    exit;
}
add_action( 'admin_post_mgk_tutor_save_schedule', 'mgk_tutor_save_schedule_handler' );

/** Save public profile fields the tutor controls: rate, subjects/levels, bio. */
function mgk_tutor_save_profile_handler() {
    $teacher_id = mgk_tutor_schedule_guard( (int) ( $_POST['mgk_teacher_id'] ?? 0 ), 'mgk_tutor_save_profile' );

    $rate = max( 0, (int) ( $_POST['mgk_rate'] ?? 0 ) );
    if ( $rate > 0 ) update_post_meta( $teacher_id, 'mgk_rate_num', $rate );

    // Subjects / levels → only existing term ids.
    if ( function_exists( 'mgk_application_set_terms' ) ) {
        mgk_application_set_terms( $teacher_id, 'mgk_subject', $_POST['mgk_subjects'] ?? [] );
        mgk_application_set_terms( $teacher_id, 'mgk_level', $_POST['mgk_levels'] ?? [] );
    }

    $bio = sanitize_textarea_field( wp_unslash( $_POST['mgk_bio'] ?? '' ) );
    wp_update_post( [ 'ID' => $teacher_id, 'post_excerpt' => $bio ] );

    do_action( 'mgk_tutor_profile_saved', $teacher_id );
    wp_safe_redirect( add_query_arg( 'mgk_saved', 'profile', mgk_get_tutor_schedule_url() ) );
    exit;
}
add_action( 'admin_post_mgk_tutor_save_profile', 'mgk_tutor_save_profile_handler' );

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
        // ── Live copy ──
        'live_sched_title'    => 'Weekly availability',
        'live_sched_sub'      => 'Set once — it repeats every week and powers the parent slot picker. Times are in Singapore time (24h). Leave a row blank to skip it.',
        'live_from'           => 'From',
        'live_to'             => 'To',
        'live_mode'           => 'Mode',
        'live_save_sched'     => 'Save availability',
        'live_prof_title'     => 'Public profile',
        'live_prof_sub'       => 'This is what parents see on your profile and in match results.',
        'live_rate_label'     => 'Hourly rate (S$)',
        'live_bio_label'      => 'Short bio',
        'live_subjects_label' => 'Subjects you teach',
        'live_levels_label'   => 'Levels',
        'live_save_prof'      => 'Save profile',
        'live_saved_sched'    => 'Availability saved. The parent slot picker now reflects your new times.',
        'live_saved_prof'     => 'Profile saved.',
        'live_saved_denied'   => 'Could not save — please sign in again and retry.',
        'gated_title'         => 'Sign in to edit your schedule',
        'gated_body'          => 'Please sign in to your tutor account to manage availability and your profile.',
        'gated_cta'           => 'Tutor sign in →',
    ];

    $atts = function_exists( 'mgk_parent_shortcode_atts' )
        ? mgk_parent_shortcode_atts( $defaults, $atts )
        : shortcode_atts( $defaults, $atts );

    return mgk_render_tutor_schedule_profile_part( 'schedule-profile-page', $atts );
} );
