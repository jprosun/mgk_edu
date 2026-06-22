<?php
/**
 * S10 — Trial Booking · Pick Slot — LOCKED DATA CORE.
 * ===================================================
 * Step 2 of the booking flow (PICK_SLOT). Shows a live week calendar, lets the
 * parent pick + hold a trial slot (10-min hold), then continue to S11 payment.
 * No payment here.
 *
 * Per the MGK 3-layer rule: Elementor controls presentation only. Everything in
 * this file is LOCKED — slot availability source, hold timer, slot status /
 * conflict logic, selected-slot payload, booking state, and the pay route.
 *
 * Reuses the booking slot core in inc/mgk-booking.php (mgk_slot_id,
 * mgk_decode_slot_id, mgk_slot_status, mgk_booking_hold_slot — WP transients,
 * TTL 600s) and the S09 select-tutor context (inc/mgk-select-tutor.php).
 *
 * No real WebSocket in this phase; availability is server-rendered + the JS
 * countdown handles the hold timer. Safe demo week data is used so the screen
 * matches the spec when a tutor has no rich availability configured.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_SLOT_HOLD_SECONDS' ) ) define( 'MGK_SLOT_HOLD_SECONDS', 600 ); // 10 min
if ( ! defined( 'MGK_TRIAL_SLOT_DURATION_MIN' ) ) define( 'MGK_TRIAL_SLOT_DURATION_MIN', 90 );

/* ── Week helpers (logic, locked) ────────────────────────── */

/** Monday (00:00) of the week containing $ts. */
function mgk_week_start( $ts = 0 ) {
    $ts = $ts ?: current_time( 'timestamp' );
    $dow = (int) gmdate( 'N', $ts ); // 1=Mon..7=Sun
    $monday = $ts - ( $dow - 1 ) * DAY_IN_SECONDS;
    return (int) ( $monday - ( $monday % DAY_IN_SECONDS ) ); // floor to midnight (UTC-ish, demo-safe)
}

/** Human label for a week, e.g. "This week · Jan 22-28". */
function mgk_week_label( $week_start, $offset_weeks = 0 ) {
    $start = (int) $week_start;
    $end   = $start + 6 * DAY_IN_SECONDS;
    $same_month = gmdate( 'M', $start ) === gmdate( 'M', $end );
    $range = gmdate( 'M j', $start ) . '-' . ( $same_month ? gmdate( 'j', $end ) : gmdate( 'M j', $end ) );
    $prefix = $offset_weeks === 0 ? 'This week · ' : '';
    return $prefix . $range;
}

/**
 * Build the 7-day week grid for a tutor.
 * Each day: ['date_ts','weekday','date','label','count','full','status'].
 * count = available slots that day; full = no availability.
 *
 * DATA-CORE (Phase 0.5): counts come from the REAL booking engine
 * (mgk_engine_available_slots) when the tutor has availability configured in
 * wp-admin — confirmed bookings + active holds are already subtracted. Falls
 * back to the deterministic demo pattern only when the tutor has NO availability
 * set, so the page still renders in a bare/demo environment.
 *
 * The presentation (markup/CSS) is unchanged — this only swaps the data source.
 */
function mgk_get_week_slots( $tutor_id, $week_start ) {
    $week_start = (int) $week_start;
    $tutor_id   = (int) $tutor_id;

    // Real engine availability, grouped by local (SGT) date.
    $engine_counts = mgk_slots_engine_week_counts( $tutor_id, $week_start );

    // Demo fallback pattern only for non-tutor preview contexts. A real tutor with
    // no availability shows an honest empty week (all days full) — never fake slots.
    $pattern = [ 0, 2, 2, 0, 1, 3, 2 ];
    $use_demo = ( $engine_counts === null ) && ( get_post_type( $tutor_id ) !== 'mg_teacher' );

    $days = [];
    for ( $i = 0; $i < 7; $i++ ) {
        $ts    = $week_start + $i * DAY_IN_SECONDS;
        $iso   = gmdate( 'Y-m-d', $ts );
        $count = $use_demo ? (int) $pattern[ $i ] : (int) ( $engine_counts[ $iso ] ?? 0 );
        $days[] = [
            'date_ts' => $ts,
            'weekday' => strtoupper( gmdate( 'D', $ts ) ),   // MON
            'date'    => gmdate( 'j', $ts ),                  // 22
            'label'   => strtoupper( gmdate( 'D j', $ts ) ),  // MON 22
            'iso'     => $iso,
            'count'   => $count,
            'full'    => $count === 0,
        ];
    }
    return $days;
}

/**
 * Engine availability for a week, as [ 'Y-m-d' (local) => count ].
 * Returns null when the tutor has NO availability configured (→ demo fallback),
 * vs an array (possibly with 0 counts) when availability exists.
 */
function mgk_slots_engine_week_counts( $tutor_id, $week_start ) {
    if ( ! function_exists( 'mgk_engine_available_slots' ) ) return null;
    if ( get_post_type( $tutor_id ) !== 'mg_teacher' ) return null;

    $weekly = function_exists( 'mgk_get_tutor_weekly_availability' ) ? mgk_get_tutor_weekly_availability( $tutor_id ) : [];
    $has_any = false;
    foreach ( (array) $weekly as $ranges ) { if ( ! empty( $ranges ) ) { $has_any = true; break; } }
    if ( ! $has_any ) return null; // no availability set → caller uses demo data

    $from = gmdate( 'Y-m-d', (int) $week_start );
    $to   = gmdate( 'Y-m-d', (int) $week_start + 6 * DAY_IN_SECONDS );
    $res  = mgk_engine_available_slots( $tutor_id, $from, $to );

    $counts = [];
    foreach ( (array) ( $res['slots'] ?? [] ) as $s ) {
        // display_start is "Y-m-d H:i" in local (SGT); take the date part.
        $date = substr( (string) ( $s['display_start'] ?? '' ), 0, 10 );
        if ( $date ) $counts[ $date ] = ( $counts[ $date ] ?? 0 ) + 1;
    }
    return $counts;
}

/** Available-slot count keyed by weekday abbrev (helper per spec). */
function mgk_get_available_slot_count_by_day( $week_days ) {
    $out = [];
    foreach ( (array) $week_days as $d ) {
        $out[ $d['weekday'] ] = (int) $d['count'];
    }
    return $out;
}

/**
 * Available time slots for a given day. Each slot:
 *   ['id','time','label','status'] where status ∈ available|held|booked|taken.
 * Slot id uses the booking core format {teacher}-{day}-{timeslug} so hold/
 * status reuse mgk_slot_status()/mgk_booking_hold_slot().
 *
 * Demo time set (per screenshot) with two live-disabled examples.
 */
function mgk_get_day_slots( $tutor_id, $day_iso = '', $day_abbrev = '' ) {
    $tutor_id = (int) $tutor_id;

    // DATA-CORE: real engine slots for this local day, mapped to the markup's
    // expected shape (id/label/status). The slot id IS the engine slot_key, which
    // encodes "{tutor}:{startUTC}:{endUTC}" so the JS can derive the hold payload.
    if ( $day_iso && function_exists( 'mgk_engine_available_slots' )
        && get_post_type( $tutor_id ) === 'mg_teacher'
        && function_exists( 'mgk_get_tutor_weekly_availability' ) ) {

        $weekly = mgk_get_tutor_weekly_availability( $tutor_id );
        $has_any = false;
        foreach ( (array) $weekly as $ranges ) { if ( ! empty( $ranges ) ) { $has_any = true; break; } }

        if ( $has_any ) {
            $res = mgk_engine_available_slots( $tutor_id, $day_iso, $day_iso );
            $slots = [];
            foreach ( (array) ( $res['slots'] ?? [] ) as $s ) {
                $label = mgk_slots_format_label( $s );
                $slots[] = [
                    'id'     => $s['slot_key'],   // engine slot_key
                    'time'   => $label,
                    'label'  => $label,
                    'status' => 'available',      // engine already removed taken/held
                    'mode'   => $s['mode'] ?? '',
                ];
            }
            return $slots;
        }
    }

    // A real tutor with no configured availability has an honest empty schedule —
    // never fake demo slots. Demo slots dead-end the flow (legacy id → no engine
    // hold → no booking → "couldn't find this booking" at S12). Demo is only for
    // non-tutor preview/layout contexts (Elementor editor, bare environment).
    if ( get_post_type( (int) $tutor_id ) === 'mg_teacher' ) {
        return [];
    }

    // ── Demo fallback (preview only — not a real tutor) ──
    if ( $tutor_id <= 0 ) $tutor_id = 1;
    $day_abbrev = $day_abbrev ?: 'wed';

    $defs = [
        [ 'label' => '4:00–5:30 PM', 'time' => '4:00-5:30pm', 'state' => 'available' ],
        [ 'label' => '7:00–8:30 PM', 'time' => '7:00-8:30pm', 'state' => 'available' ],
        [ 'label' => '5:30 PM',      'time' => '5:30pm',      'state' => 'taken'     ],
        [ 'label' => '8:30 PM',      'time' => '8:30pm',      'state' => 'booked'    ],
    ];

    $slots = [];
    foreach ( $defs as $d ) {
        $id = function_exists( 'mgk_slot_id' )
            ? mgk_slot_id( $tutor_id, $day_abbrev, $d['time'] )
            : $tutor_id . '-' . sanitize_key( $day_abbrev ) . '-' . sanitize_key( $d['time'] );

        $status = $d['state'];
        if ( $status === 'available' && function_exists( 'mgk_slot_status' ) ) {
            $live = mgk_slot_status( $id );
            if ( $live === 'booked' ) $status = 'booked';
            elseif ( $live === 'held' ) $status = 'held';
        }

        $slots[] = [
            'id'     => $id,
            'time'   => $d['label'],
            'label'  => $d['label'],
            'status' => $status,
        ];
    }
    return $slots;
}

/**
 * Find the first week offset (0..N) whose engine slots are non-empty, starting
 * from $start_offset. Returns $start_offset unchanged when the tutor has no
 * engine availability (demo mode) or none is found within the search horizon.
 */
function mgk_slots_first_available_week_offset( $tutor_id, $start_offset = 0 ) {
    if ( ! function_exists( 'mgk_engine_available_slots' ) ) return $start_offset;
    if ( get_post_type( $tutor_id ) !== 'mg_teacher' ) return $start_offset;
    if ( ! function_exists( 'mgk_get_tutor_weekly_availability' ) ) return $start_offset;

    $weekly = mgk_get_tutor_weekly_availability( $tutor_id );
    $has_any = false;
    foreach ( (array) $weekly as $ranges ) { if ( ! empty( $ranges ) ) { $has_any = true; break; } }
    if ( ! $has_any ) return $start_offset; // demo mode — don't move

    // Search up to ~6 weeks ahead (within typical max_advance).
    for ( $w = max( 0, $start_offset ); $w <= 6; $w++ ) {
        $ws = mgk_week_start() + $w * 7 * DAY_IN_SECONDS;
        $counts = mgk_slots_engine_week_counts( $tutor_id, $ws );
        if ( is_array( $counts ) ) {
            foreach ( $counts as $c ) { if ( (int) $c > 0 ) return $w; }
        }
    }
    return $start_offset;
}

/** Pretty time label from an engine slot, e.g. "4:00–5:30 PM" (local). */
function mgk_slots_format_label( $slot ) {
    // display_start = "Y-m-d H:i" (24h local); display_end = "H:i".
    $ds = (string) ( $slot['display_start'] ?? '' );
    $de = (string) ( $slot['display_end'] ?? '' );
    $start_t = strlen( $ds ) >= 16 ? substr( $ds, 11, 5 ) : '';
    $fmt = function ( $hm ) {
        if ( ! preg_match( '/^(\d{2}):(\d{2})$/', $hm, $m ) ) return $hm;
        $h = (int) $m[1]; $min = $m[2];
        $ap = $h >= 12 ? 'PM' : 'AM';
        $h12 = $h % 12; if ( $h12 === 0 ) $h12 = 12;
        return $h12 . ':' . $min . ' ' . $ap;
    };
    $start = $fmt( $start_t );
    $end   = $fmt( $de );
    if ( $start && $end ) {
        // Collapse the AM/PM on start if same period as end (e.g. 4:00–5:30 PM).
        $sp = substr( $start, -2 ); $ep = substr( $end, -2 );
        if ( $sp === $ep ) $start = trim( substr( $start, 0, -2 ) );
        return $start . '–' . $end;
    }
    return $start ?: $de;
}

/* ── Slot hold (logic, locked — wraps booking core) ──────── */

/**
 * Resolve the current hold for a booking context. Returns:
 *   ['active'=>bool,'slot_id'=>str,'hold_token'=>str,'remaining'=>int,'expires_at'=>int]
 * A hold is keyed by ?slot + ?hold in the request (set after a hold POST), and
 * validated against the transient written by mgk_booking_hold_slot().
 */
function mgk_get_slot_hold( $booking_context ) {
    $ctx     = (array) $booking_context;
    $slot_id = sanitize_text_field( $ctx['slot_id'] ?? '' );
    $default = [ 'active' => false, 'slot_id' => '', 'hold_token' => '', 'remaining' => 0, 'expires_at' => 0 ];

    if ( ! $slot_id || ! function_exists( 'mgk_is_slot_held' ) ) {
        return $default;
    }

    $tkey = 'mgk_hold_' . sanitize_key( $slot_id );
    $hold = get_transient( $tkey );
    if ( ! $hold ) {
        return $default;
    }

    // Transients don't expose their own TTL portably; track expiry alongside.
    $expires_at = (int) get_transient( $tkey . '_exp' );
    if ( ! $expires_at ) {
        // First sight of this hold on this page — stamp expiry now.
        $expires_at = current_time( 'timestamp', true ) + MGK_SLOT_HOLD_SECONDS;
        set_transient( $tkey . '_exp', $expires_at, MGK_SLOT_HOLD_SECONDS );
    }

    $remaining = max( 0, $expires_at - current_time( 'timestamp', true ) );

    return [
        'active'     => $remaining > 0,
        'slot_id'    => $slot_id,
        'hold_token' => sanitize_text_field( $ctx['hold_token'] ?? '' ),
        'remaining'  => (int) $remaining,
        'expires_at' => (int) $expires_at,
    ];
}

/** Seconds remaining on a hold (helper per spec). */
function mgk_get_slot_hold_remaining( $hold ) {
    return (int) ( is_array( $hold ) ? ( $hold['remaining'] ?? 0 ) : 0 );
}

/** Release an expired/abandoned hold by slot id (helper per spec). */
function mgk_release_expired_slot_hold( $slot_id ) {
    $slot_id = sanitize_text_field( (string) $slot_id );
    if ( ! $slot_id ) return false;
    delete_transient( 'mgk_hold_' . sanitize_key( $slot_id ) );
    delete_transient( 'mgk_hold_' . sanitize_key( $slot_id ) . '_exp' );
    return true;
}

/* ── Booking context for S10 (reuses S09) ────────────────── */

/**
 * Pick-slot view model. Resolves the S09 select-tutor view, then layers the
 * week grid + day times + current hold. Returns 'status' for state handling.
 */
function mgk_get_pick_slot_view() {
    // Reuse the S09 context resolver if present (lead + tutor + proposal).
    $base = function_exists( 'mgk_get_select_tutor_view' ) ? mgk_get_select_tutor_view() : [ 'status' => 'not_found' ];

    if ( ( $base['status'] ?? '' ) !== 'ok' ) {
        return [ 'status' => $base['status'] ?? 'not_found', 'context' => $base['context'] ?? [] ];
    }

    $tutor   = (array) $base['tutor'];
    $context = (array) ( $base['context'] ?? [] );

    // Week offset from ?week (number of weeks from current).
    $offset = isset( $_GET['week'] ) ? (int) $_GET['week'] : 0;

    // On first load (no explicit ?week), if the current week has no real
    // availability, auto-advance to the first upcoming week that does — so the
    // page opens on a useful week instead of an empty past/current one. Only
    // applies when the tutor has engine availability configured.
    if ( ! isset( $_GET['week'] ) ) {
        $offset = mgk_slots_first_available_week_offset( (int) ( $tutor['id'] ?? 0 ), $offset );
    }

    $week_start = mgk_week_start() + $offset * 7 * DAY_IN_SECONDS;

    $week_days = mgk_get_week_slots( (int) ( $tutor['id'] ?? 0 ), $week_start );

    // Active day = first day with availability (demo: Wed).
    $active_iso = '';
    $active_abbrev = '';
    foreach ( $week_days as $d ) {
        if ( ! $d['full'] ) { $active_iso = $d['iso']; $active_abbrev = strtolower( $d['weekday'] ); break; }
    }
    // Honour ?day if provided + valid.
    // NOTE: use 'mgk_day' — 'day' is a reserved WordPress public query var
    // (year/monthnum/day), and ?day=YYYY-MM-DD corrupts the main WP_Date_Query
    // ("Invalid value 2026 for day") and mis-routes the page.
    $req_day = isset( $_GET['mgk_day'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_day'] ) ) : '';
    foreach ( $week_days as $d ) {
        if ( $req_day && $d['iso'] === $req_day && ! $d['full'] ) { $active_iso = $d['iso']; $active_abbrev = strtolower( $d['weekday'] ); }
    }

    $times = mgk_get_day_slots( (int) ( $tutor['id'] ?? 0 ), $active_iso, $active_abbrev );

    // Selected/held slot from ?slot.
    $context['slot_id']    = isset( $_GET['slot'] ) ? sanitize_text_field( wp_unslash( $_GET['slot'] ) ) : '';
    $context['hold_token'] = isset( $_GET['hold'] ) ? sanitize_text_field( wp_unslash( $_GET['hold'] ) ) : '';
    $hold = mgk_get_slot_hold( $context );

    // Default selected slot = the first available time (demo: 4:00–5:30 PM) so
    // the page matches the screenshot's pre-selected state on first load.
    $selected = null;
    foreach ( $times as $t ) {
        if ( $context['slot_id'] && $t['id'] === $context['slot_id'] ) { $selected = $t; break; }
    }
    if ( ! $selected ) {
        foreach ( $times as $t ) { if ( $t['status'] === 'available' ) { $selected = $t; break; } }
    }

    $active_label = '';
    foreach ( $week_days as $d ) { if ( $d['iso'] === $active_iso ) { $active_label = ucwords( strtolower( gmdate( 'D j M', $d['date_ts'] ) ) ); break; } }

    return [
        'status'       => 'ok',
        'context'      => $context,
        'tutor'        => $tutor,
        'week_start'   => $week_start,
        'week_offset'  => $offset,
        'week_label'   => mgk_week_label( $week_start, $offset ),
        'week_days'    => $week_days,
        'active_iso'   => $active_iso,
        'active_label' => $active_label,
        'times'        => $times,
        'selected'     => $selected,
        'hold'         => $hold,
        'duration_min' => MGK_TRIAL_SLOT_DURATION_MIN,
        'hold_seconds' => MGK_SLOT_HOLD_SECONDS,
    ];
}

/* ── Pay route (S11) — locked ────────────────────────────── */

function mgk_get_s11_pay_url( $context, $slot_id = '', $hold_token = '' ) {
    $context = (array) $context;
    $args = array_filter( [
        'lead'  => $context['lead_token'] ?? '',
        'tutor' => $context['tutor_slug'] ?? '',
        'slot'  => $slot_id ?: ( $context['slot_id'] ?? '' ),
        'hold'  => $hold_token ?: ( $context['hold_token'] ?? '' ),
    ] );
    return add_query_arg( $args, home_url( '/trial-pay/' ) );
}

/* ── Render helper + shortcodes (thin shells → partials) ─── */

function mgk_pick_slot_part( $part, $atts = [] ) {
    $atts = is_array( $atts ) ? array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } ) : [];
    if ( $part === 'pick-slot' ) {
        return mgk_render_part( 'template-parts/sections/booking/pick-slot', $atts );
    }
    if ( $part === 'slot-hold-banner' && function_exists( 'is_page' ) && is_page( [ 'trial-pay', 'pay' ] ) && function_exists( 'mgk_get_pay_view' ) ) {
        $pay_view = mgk_get_pay_view();
        if ( ! empty( $pay_view['is_package_order'] ) || ( ( $pay_view['item_kind'] ?? '' ) === 'package' ) ) {
            return '';
        }
    }
    $view = mgk_get_pick_slot_view();
    return mgk_render_part( 'template-parts/sections/booking/' . $part, array_merge( $view, $atts ) );
}

/** Composite — whole S10 page. */
add_shortcode( 'mgk_pick_slot', function ( $atts ) {
    return mgk_pick_slot_part( 'pick-slot', (array) $atts );
} );

/** Slot hold banner. */
add_shortcode( 'mgk_slot_hold_banner', function ( $atts ) {
    $atts = shortcode_atts( [ 'title' => '', 'note' => '' ], (array) $atts, 'mgk_slot_hold_banner' );
    return mgk_pick_slot_part( 'slot-hold-banner', $atts );
} );

/** Live calendar (heading + week nav + week strip). */
add_shortcode( 'mgk_live_calendar', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'live_note' => '', 'prev_label' => '', 'next_label' => '',
    ], (array) $atts, 'mgk_live_calendar' );
    return mgk_pick_slot_part( 'live-calendar', $atts );
} );

/** Available times + legend. */
add_shortcode( 'mgk_available_times', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'legend_available' => '', 'legend_taken' => '', 'legend_hold' => '',
    ], (array) $atts, 'mgk_available_times' );
    return mgk_pick_slot_part( 'available-times', $atts );
} );

/** Selected slot summary + confirm CTA. */
add_shortcode( 'mgk_selected_slot', function ( $atts ) {
    $atts = shortcode_atts( [ 'eyebrow' => '', 'cta_label' => '', 'location' => '' ], (array) $atts, 'mgk_selected_slot' );
    return mgk_pick_slot_part( 'selected-slot-confirm', $atts );
} );

/* ── Tracking ────────────────────────────────────────────── */

function mgk_slots_track( $event, $props = [] ) {
    if ( ! function_exists( 'mgk_track_event' ) ) return;
    mgk_track_event( $event, array_filter( (array) $props ) );
}
