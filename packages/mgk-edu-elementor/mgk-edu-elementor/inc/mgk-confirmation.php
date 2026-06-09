<?php
/**
 * S12 — Trial Booking · Confirmation — LOCKED DATA CORE.
 * ======================================================
 * The success page the parent lands on after a confirmed payment (S11 →
 * /parent/trial/confirmed/). Confirms the booking, reveals the tutor contact
 * (masked before booking — NFR-10), drives first-lesson readiness, exposes the
 * e-invoice, and offers manage actions (reschedule / cancel-refund) via modals.
 *
 * Per the MGK 3-layer rule (ONBOARDING §1.5, PLAYBOOK §3.5): Elementor controls
 * presentation only. Everything in this file is LOCKED:
 *   - confirmation number + booking status + paid amount + payment method
 *   - tutor-contact UNLOCK logic (only after a paid booking) + masking
 *   - lesson date/time/format + Zoom link
 *   - calendar (.ics / Google / Outlook) generation
 *   - e-invoice id + download + GST
 *   - reschedule limits + cancel/refund tier rules (BR-07 / FR-PAY-10 / FR-BOOK-09)
 *   - message-thread route
 *
 * Reuses inc/mgk-pay.php (mgk_get_pay_view → tutor + selected slot + order
 * summary + payment reference) and inc/mgk-forms.php (mgk_mask_email). Payment /
 * invoice / messaging are mocked in this phase (no real charge / PDF / thread).
 *
 * Shortcodes are registered in this file; the thin Elementor widgets live in
 * inc/mgk-elementor.php and only forward SAFE copy + Style.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_RESCHEDULE_FREE_HOURS' ) ) define( 'MGK_RESCHEDULE_FREE_HOURS', 24 );   // FR-BOOK-09
if ( ! defined( 'MGK_RESCHEDULE_LIMIT' ) )      define( 'MGK_RESCHEDULE_LIMIT', 2 );         // BR-23
if ( ! defined( 'MGK_REFUND_FULL_HOURS' ) )     define( 'MGK_REFUND_FULL_HOURS', 48 );       // BR-07
if ( ! defined( 'MGK_REFUND_HALF_HOURS' ) )     define( 'MGK_REFUND_HALF_HOURS', 24 );       // BR-07

/* ── Context + payment status (LOCKED) ───────────────────── */

/**
 * Resolve the confirmation context from the request (?lead=&tutor=&slot=&ref=).
 * The payment status is read from a locked source — here a query flag for
 * preview (status=paid|pending|failed), defaulting to paid (the happy path the
 * S11 redirect lands on).
 */
function mgk_get_confirm_context() {
    $ctx = function_exists( 'mgk_get_pay_context_from_request' )
        ? mgk_get_pay_context_from_request()
        : [ 'lead_token' => '', 'tutor_slug' => '', 'slot_id' => '', 'hold_token' => '' ];
    $ctx['ref']    = function_exists( 'mgk_get_query_filter' ) ? (string) mgk_get_query_filter( 'ref', '' ) : '';
    $ctx['status'] = function_exists( 'mgk_get_query_filter' ) ? sanitize_key( (string) mgk_get_query_filter( 'status', 'paid' ) ) : 'paid';
    // ?booking= may be a numeric engine id (from S11) or a booking code (from the
    // mock checkout return). Resolve the real row either way.
    $raw = function_exists( 'mgk_get_query_filter' ) ? (string) mgk_get_query_filter( 'booking', '' ) : '';
    $ctx['booking_id'] = 0;
    $ctx['booking_row'] = null;
    if ( $raw !== '' && function_exists( 'mgk_get_booking_row' ) ) {
        $row = ctype_digit( $raw ) ? mgk_get_booking_row( (int) $raw )
            : ( function_exists( 'mgk_get_booking_by_code' ) ? mgk_get_booking_by_code( $raw ) : null );
        if ( $row ) {
            $ctx['booking_id']  = (int) $row['id'];
            $ctx['booking_row'] = $row;
            if ( $ctx['ref'] === '' ) $ctx['ref'] = $row['booking_code'];
        }
    }
    return $ctx;
}

/**
 * Locked payment status for the booking. When a real engine booking is bound,
 * its status is the source of truth (mapped to the page's paid|pending|failed
 * vocabulary). Falls back to the preview ?status flag only in demo/no-booking.
 */
function mgk_get_confirm_payment_status( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );

    $row = $context['booking_row'] ?? null;
    if ( is_array( $row ) ) {
        switch ( $row['status'] ) {
            case 'CONFIRMED':
            case 'COMPLETED':
                return 'paid';
            case 'HELD':
            case 'PENDING_PAYMENT':
            case 'MANUAL_REVIEW':
                return 'pending';
            case 'FAILED_PAYMENT':
                return 'failed';
            default: // EXPIRED / CANCELLED …
                return 'failed';
        }
    }

    $status = $context['status'] ?? 'paid';
    return in_array( $status, [ 'paid', 'pending', 'failed' ], true ) ? $status : 'paid';
}

/* ── Confirmation number + email (LOCKED) ────────────────── */

/**
 * The human confirmation number (#MGK-TRL-XXXX). Derived from the payment
 * reference so it stays stable for a booking.
 */
function mgk_get_booking_confirmation( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );

    // Real engine booking → its booking_code IS the human confirmation number
    // (e.g. MGK-20260606-IOIKBV). Stable, unique, already shown in admin.
    $row = $context['booking_row'] ?? null;
    if ( is_array( $row ) && ! empty( $row['booking_code'] ) ) {
        return (string) $row['booking_code'];
    }

    // Demo fallback: derive a stable tail from the payment reference.
    $ref = (string) ( $context['ref'] ?? '' );
    if ( $ref === '' && function_exists( 'mgk_get_payment_reference' ) ) {
        $ref = mgk_get_payment_reference( $context );
    }
    // TRIAL-MSGO-8842 → 8842 ; fall back to a stable demo tail.
    $tail = '';
    if ( preg_match( '/([0-9]{3,})$/', $ref, $m ) ) { $tail = $m[1]; }
    if ( $tail === '' ) { $tail = '0842'; }
    return 'MGK-TRL-' . $tail;
}

/** Parent email for the confirmation line (masked per privacy). */
function mgk_get_confirm_email( $context = [] ) {
    $email = function_exists( 'mgk_get_query_filter' ) ? (string) mgk_get_query_filter( 'email', '' ) : '';
    if ( $email === '' ) { $email = 'your.email@example.sg'; }
    // Show as-is when it is the demo placeholder; otherwise mask.
    if ( $email !== 'your.email@example.sg' && function_exists( 'mgk_mask_email' ) ) {
        $masked = mgk_mask_email( $email );
        if ( $masked ) { $email = $masked; }
    }
    return $email;
}

/* ── Booking + payment summary (LOCKED) ──────────────────── */

/**
 * The booking summary rows (tutor, subject/level, date/time, format, paid,
 * method). All values come from the locked S11 pay view + reference.
 *
 * @return array{title:string,duration_h:string,tutor:string,subject_level:string,
 *               datetime:string,format:string,paid:string,method:string}
 */
function mgk_get_booking_summary( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    $view    = function_exists( 'mgk_get_pay_view' ) ? mgk_get_pay_view() : [];

    $tutor    = (array) ( $view['tutor'] ?? [] );
    $selected = (array) ( $view['selected'] ?? [] );
    $summary  = (array) ( $view['summary'] ?? [] );
    $day_lbl  = (string) ( $view['active_label'] ?? '' );
    $dur_h    = (string) ( $summary['duration_h'] ?? '1.5' );

    // Subject/level from the proposal lead (locked source).
    $subject = 'Math';
    $level   = 'P5';
    if ( function_exists( 'mgk_get_proposal_batch' ) ) {
        $batch  = mgk_get_proposal_batch( $context['lead_token'] ?? '' );
        $filt   = (array) ( $batch['filters'] ?? [] );
        $subject = (string) ( $filt['subject'] ?? $subject );
        $level   = (string) ( $filt['level'] ?? $level );
    }

    $time = $selected['label'] ?? '4:00–5:30 PM';
    $datetime = trim( ( $day_lbl ?: 'Wed 24 Jan' ) . ' · ' . $time );

    $ref  = (string) ( $context['ref'] ?? ( $view['reference'] ?? '' ) );
    $ref_tail = preg_match( '/([0-9]{3,})$/', $ref, $m ) ? $m[1] : '8842';

    return [
        'title'         => 'Trial lesson · ' . $dur_h . 'h',
        'duration_h'    => $dur_h,
        'tutor'         => (string) ( $tutor['name'] ?? 'Ms Lee Yi Ling' ),
        'subject_level' => $level . ' ' . $subject,
        'datetime'      => $datetime,
        'format'        => 'Online (Zoom)',
        'paid'          => (string) ( $summary['total'] ?? '$33.00' ) . ' incl. GST',
        'method'        => 'PayNow · Ref ' . $ref_tail,
    ];
}

/** Payment summary (kept separate per the spec helper list). */
function mgk_get_payment_summary( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    $view    = function_exists( 'mgk_get_pay_view' ) ? mgk_get_pay_view() : [];
    $summary = (array) ( $view['summary'] ?? [] );
    $ref     = (string) ( $context['ref'] ?? ( $view['reference'] ?? '' ) );
    return [
        'amount'   => (string) ( $summary['total'] ?? '$33.00' ),
        'method'   => 'PayNow',
        'ref'      => $ref,
        'gst_note' => (string) ( $summary['gst_note'] ?? 'INCL. 9% GST (BR-04) · SGD' ),
        'status'   => mgk_get_confirm_payment_status( $context ),
    ];
}

/* ── Tutor contact unlock (LOCKED — NFR-10) ──────────────── */

/**
 * Contact is ONLY unlocked after a paid booking. Before booking the contact is
 * masked. This gate is locked — Elementor cannot change it.
 */
function mgk_is_tutor_contact_unlocked( $context = [] ) {
    return mgk_get_confirm_payment_status( $context ) === 'paid';
}

/**
 * Tutor contact for a (paid) booking. Returns name + phone + email + avatar.
 * If not unlocked, phone/email are masked. Demo-safe values when no real
 * contact is configured.
 *
 * @return array{name:string,short:string,phone:string,email:string,avatar:string,unlocked:bool}
 */
function mgk_get_tutor_contact_for_booking( $context = [] ) {
    $context  = (array) ( $context ?: mgk_get_confirm_context() );
    $view     = function_exists( 'mgk_get_pay_view' ) ? mgk_get_pay_view() : [];
    $tutor    = (array) ( $view['tutor'] ?? [] );
    $unlocked = mgk_is_tutor_contact_unlocked( $context );

    $name  = (string) ( $tutor['name'] ?? 'Ms Lee Yi Ling' );
    $slug  = sanitize_title( $tutor['slug'] ?? $name );
    $short = $name;
    if ( preg_match( '/\b(Ms|Mr|Mrs|Dr)\.?\s+([A-Z][a-z]+)/', $name, $m ) ) { $short = $m[1] . ' ' . $m[2]; }

    // Demo-safe contact (real value would come from tutor CPT meta).
    $phone = '+65 9XXX 1234';
    $email = $slug ? str_replace( '-', '_', $slug ) . '@tutors.example.sg' : 'tutor@tutors.example.sg';

    if ( ! $unlocked ) {
        $phone = '+65 ••••  ••••';
        $email = '••••••@tutors.example.sg';
    }

    return [
        'name'     => $name,
        'short'    => $short,
        'phone'    => $phone,
        'email'    => $email,
        'avatar'   => (string) ( $tutor['photo'] ?? '' ),
        'unlocked' => $unlocked,
    ];
}

/** In-app message thread for this booking (mock-safe route). */
function mgk_get_booking_message_thread_url( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    return add_query_arg( array_filter( [
        'tutor' => $context['tutor_slug'] ?? '',
        'lead'  => $context['lead_token'] ?? '',
    ] ), home_url( '/messages/' ) );
}

/* ── First lesson + calendar (LOCKED) ────────────────────── */

/** Zoom link for the booking (demo derived from the reference). */
function mgk_get_lesson_zoom_url( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    $ref     = (string) ( $context['ref'] ?? '' );
    $tail    = preg_match( '/([0-9]{3,})$/', $ref, $m ) ? $m[1] : '8842';
    return 'https://zoom.us/j/' . $tail . 'XXXX';
}

/** First-lesson readiness items (SAFE copy lives in the partial; data here). */
function mgk_get_first_lesson_items( $context = [] ) {
    $zoom = mgk_get_lesson_zoom_url( $context );
    return [
        [ 'icon' => '🔗', 'text' => 'Zoom link: ' . $zoom . ' (also emailed)', 'url' => $zoom ],
        [ 'icon' => '🕘', 'text' => 'Join 5 min early', 'url' => '' ],
        [ 'icon' => '📄', 'text' => 'Bring last test/worksheet', 'url' => '' ],
    ];
}

/**
 * Calendar export URLs. The .ics is served by our own action endpoint
 * (?mgk_action=ics); Google / Outlook are built from booking data.
 */
function mgk_get_calendar_ics_url( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    return add_query_arg( array_filter( [
        'mgk_action' => 'ics',
        'lead'       => $context['lead_token'] ?? '',
        'tutor'      => $context['tutor_slug'] ?? '',
        'slot'       => $context['slot_id'] ?? '',
        'ref'        => $context['ref'] ?? '',
    ] ), home_url( '/trial-confirmed/' ) );
}

/** Calendar event payload (title / start / end / details) — locked from booking. */
function mgk_get_calendar_event( $context = [] ) {
    $summary = mgk_get_booking_summary( $context );
    $zoom    = mgk_get_lesson_zoom_url( $context );
    // Demo fixed start; a real booking would compute from the slot timestamp.
    $start = gmdate( 'Ymd\THis\Z', strtotime( '2026-01-24 08:00:00 UTC' ) ); // 4:00pm SGT
    $end   = gmdate( 'Ymd\THis\Z', strtotime( '2026-01-24 09:30:00 UTC' ) ); // 5:30pm SGT
    return [
        'title'   => 'Trial lesson with ' . $summary['tutor'],
        'details' => $summary['subject_level'] . ' · ' . $summary['format'] . "\nZoom: " . $zoom,
        'location'=> $zoom,
        'start'   => $start,
        'end'     => $end,
    ];
}

function mgk_get_google_calendar_url( $context = [] ) {
    $e = mgk_get_calendar_event( $context );
    return add_query_arg( array_map( 'rawurlencode', [
        'action'   => 'TEMPLATE',
        'text'     => $e['title'],
        'dates'    => $e['start'] . '/' . $e['end'],
        'details'  => $e['details'],
        'location' => $e['location'],
    ] ), 'https://calendar.google.com/calendar/render' );
}

function mgk_get_outlook_calendar_url( $context = [] ) {
    $e = mgk_get_calendar_event( $context );
    return add_query_arg( array_map( 'rawurlencode', [
        'path'     => '/calendar/action/compose',
        'rru'      => 'addevent',
        'subject'  => $e['title'],
        'body'     => $e['details'],
        'location' => $e['location'],
        'startdt'  => gmdate( 'c', strtotime( '2026-01-24 08:00:00 UTC' ) ),
        'enddt'    => gmdate( 'c', strtotime( '2026-01-24 09:30:00 UTC' ) ),
    ] ), 'https://outlook.live.com/calendar/0/deeplink/compose' );
}

/* ── Next steps + invoice (LOCKED) ───────────────────────── */

function mgk_get_next_steps( $context = [] ) {
    return [
        [ 'label' => 'Verify email OTP (confirmed)',          'done' => true ],
        [ 'label' => 'Add lesson to your calendar',           'done' => false ],
        [ 'label' => 'Message your tutor any prep notes',     'done' => false ],
        [ 'label' => 'Test Zoom audio/video before lesson',   'done' => false ],
    ];
}

/**
 * E-invoice for the booking. Demo: id derived from the reference, ready when
 * paid. Returns ['id','label','ready','url'].
 */
function mgk_get_invoice_for_booking( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    $ref     = (string) ( $context['ref'] ?? '' );
    $tail    = preg_match( '/([0-9]{3,})$/', $ref, $m ) ? $m[1] : '8842';
    $id      = 'INV-' . $tail;
    $ready   = mgk_get_confirm_payment_status( $context ) === 'paid';
    return [
        'id'    => $id,
        'label' => 'E-Invoice ' . $id . ' (incl. GST)',
        'ready' => $ready,
        'url'   => $ready ? mgk_get_invoice_download_url( $id, $context ) : '',
    ];
}

function mgk_get_invoice_download_url( $invoice_id, $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    return add_query_arg( array_filter( [
        'mgk_action' => 'invoice',
        'inv'        => sanitize_text_field( (string) $invoice_id ),
        'ref'        => $context['ref'] ?? '',
    ] ), home_url( '/trial-confirmed/' ) );
}

/* ── Manage: reschedule + cancel/refund (LOCKED rules) ───── */

/** Reschedule modal data: notice rule, count, slot options (BR-23 / FR-BOOK-09). */
function mgk_get_reschedule_options( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    return [
        'free_hours'  => (int) MGK_RESCHEDULE_FREE_HOURS,
        'used'        => 1,
        'limit'       => (int) MGK_RESCHEDULE_LIMIT,
        'note'        => sprintf( '≥%dh notice = free · this is reschedule %d of %d for this package.', MGK_RESCHEDULE_FREE_HOURS, 1, MGK_RESCHEDULE_LIMIT ),
        'config_note' => 'per-tenant config · PM to confirm limits',
        'reheld_min'  => 10,
        'slots'       => [
            [ 'id' => 'sat-27-1400', 'label' => 'Sat 27 · 2 PM' ],
            [ 'id' => 'sun-28-1500', 'label' => 'Sun 28 · 3 PM' ],
        ],
    ];
}

/** Cancel/refund preview: BR-07 tiers + the parent's current entitlement. */
function mgk_get_refund_preview( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    $pay     = mgk_get_payment_summary( $context );
    $amount  = $pay['amount'];
    $amount_num = (float) preg_replace( '/[^0-9.]/', '', $amount );
    $half    = '$' . number_format( $amount_num * 0.5, 2 );

    // Demo entitlement: >48h out → full refund (the happy default).
    $hours_out = 50;
    $entitled  = $hours_out >= MGK_REFUND_FULL_HOURS ? 'full'
        : ( $hours_out >= MGK_REFUND_HALF_HOURS ? 'half' : 'none' );

    $tiers = [
        [ 'key' => 'full', 'label' => '≥48h before',    'value' => $amount,   'pct' => 'Full' ],
        [ 'key' => 'half', 'label' => '24–48h before',  'value' => $half,     'pct' => '50%' ],
        [ 'key' => 'none', 'label' => '<24h before',    'value' => '$0.00',   'pct' => '0%' ],
    ];

    $entitled_amount = $entitled === 'full' ? $amount : ( $entitled === 'half' ? $half : '$0.00' );

    return [
        'tiers'           => $tiers,
        'entitled'        => $entitled,
        'entitled_amount' => $entitled_amount,
        'note'            => sprintf( 'You’re >%dh out → you’ll be refunded %s (%s). Shown before you confirm.', MGK_REFUND_FULL_HOURS, $entitled_amount, $entitled ),
    ];
}

/* ── Routes (LOCKED) ─────────────────────────────────────── */

function mgk_get_dashboard_url()     { return home_url( '/parent/dashboard/' ); }
function mgk_get_my_bookings_url()   { return home_url( '/parent/bookings/' ); }
function mgk_get_account_url()       { return home_url( '/parent/account/' ); }

/** Cancel/refund confirm action route (does NOT refund on click — opens modal). */
function mgk_get_cancel_refund_url( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    return add_query_arg( array_filter( [
        'mgk_action' => 'cancel_preview',
        'lead'       => $context['lead_token'] ?? '',
        'ref'        => $context['ref'] ?? '',
    ] ), home_url( '/trial-confirmed/' ) );
}

/* ── Build the full S12 view ─────────────────────────────── */

/**
 * @return array  status: paid|pending|failed|not_found, + context, confirmation,
 *                 email, summary, payment, contact, lesson, calendar, next_steps,
 *                 invoice, reschedule, refund, urls
 */
function mgk_get_confirmation_view() {
    $context = mgk_get_confirm_context();
    $status  = mgk_get_confirm_payment_status( $context );

    // Booking resolution: reuse the S11 pay view's tutor/slot gate.
    $pay = function_exists( 'mgk_get_pay_view' ) ? mgk_get_pay_view() : [ 'status' => 'not_found' ];
    if ( ( $pay['status'] ?? '' ) !== 'ok' ) {
        return [ 'status' => 'not_found', 'context' => $context ];
    }

    return [
        'status'       => $status, // paid | pending | failed
        'context'      => $context,
        'confirmation' => mgk_get_booking_confirmation( $context ),
        'email'        => mgk_get_confirm_email( $context ),
        'summary'      => mgk_get_booking_summary( $context ),
        'payment'      => mgk_get_payment_summary( $context ),
        'contact'      => mgk_get_tutor_contact_for_booking( $context ),
        'lesson'       => mgk_get_first_lesson_items( $context ),
        'calendar'     => [
            'ics'     => mgk_get_calendar_ics_url( $context ),
            'google'  => mgk_get_google_calendar_url( $context ),
            'outlook' => mgk_get_outlook_calendar_url( $context ),
        ],
        'next_steps'   => mgk_get_next_steps( $context ),
        'invoice'      => mgk_get_invoice_for_booking( $context ),
        'reschedule'   => mgk_get_reschedule_options( $context ),
        'refund'       => mgk_get_refund_preview( $context ),
        'urls'         => [
            'message'   => mgk_get_booking_message_thread_url( $context ),
            'reschedule'=> '#mgk-reschedule',
            'cancel'    => '#mgk-cancel-refund',
            'dashboard' => mgk_get_dashboard_url(),
            'bookings'  => mgk_get_my_bookings_url(),
            'account'   => mgk_get_account_url(),
        ],
    ];
}

/* ── Tracking ────────────────────────────────────────────── */

function mgk_confirm_track( $event, $props = [] ) {
    if ( ! function_exists( 'mgk_track_event' ) ) return;
    mgk_track_event( $event, array_filter( (array) $props ) );
}

/* ── Render helper + shortcodes (thin shells → partials) ─── */

function mgk_confirm_part( $part, $atts = [] ) {
    $atts = is_array( $atts ) ? array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } ) : [];
    if ( $part === 'booking-success' ) {
        return mgk_render_part( 'template-parts/sections/booking/booking-success', $atts );
    }
    $view = mgk_get_confirmation_view();
    return mgk_render_part( 'template-parts/sections/booking/' . $part, array_merge( $view, $atts ) );
}

/** Composite — whole S12 page. */
add_shortcode( 'mgk_booking_success', function ( $atts ) {
    return mgk_confirm_part( 'booking-success', (array) $atts );
} );

/** Success hero. */
add_shortcode( 'mgk_success_hero', function ( $atts ) {
    $atts = shortcode_atts( [ 'heading' => '', 'sent_prefix' => '' ], (array) $atts, 'mgk_success_hero' );
    return mgk_confirm_part( 'success-hero', $atts );
} );

/** Booking summary card. */
add_shortcode( 'mgk_booking_summary', function ( $atts ) {
    $atts = shortcode_atts( [
        'l_tutor' => '', 'l_subject' => '', 'l_datetime' => '', 'l_format' => '', 'l_paid' => '', 'l_method' => '',
    ], (array) $atts, 'mgk_booking_summary' );
    return mgk_confirm_part( 'booking-summary', $atts );
} );

/** Tutor contact (unlocked) card. */
add_shortcode( 'mgk_tutor_contact', function ( $atts ) {
    $atts = shortcode_atts( [
        'unlocked_label' => '', 'cta_label' => '', 'masked_note' => '',
    ], (array) $atts, 'mgk_tutor_contact' );
    return mgk_confirm_part( 'tutor-contact', $atts );
} );

/** First lesson + calendar card. */
add_shortcode( 'mgk_first_lesson', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'ics_label' => '', 'google_label' => '', 'outlook_label' => '',
    ], (array) $atts, 'mgk_first_lesson' );
    return mgk_confirm_part( 'first-lesson', $atts );
} );

/** Next steps + invoice card. */
add_shortcode( 'mgk_next_steps', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'download_label' => '',
    ], (array) $atts, 'mgk_next_steps' );
    return mgk_confirm_part( 'next-steps', $atts );
} );

/** Manage booking actions + modals. */
add_shortcode( 'mgk_manage_booking', function ( $atts ) {
    $atts = shortcode_atts( [
        'reschedule_label' => '', 'cancel_label' => '',
    ], (array) $atts, 'mgk_manage_booking' );
    return mgk_confirm_part( 'manage-booking', $atts );
} );

/* ── Calendar .ics (shared by the endpoint + the email attachment) ───────── */

/**
 * Build the VCALENDAR/.ics string for a booking context. Single source so the
 * download endpoint and the confirmation email attach the exact same event.
 */
function mgk_booking_ics_string( $context = [] ) {
    $e   = mgk_get_calendar_event( $context );
    $uid = 'mgk-' . substr( md5( $e['title'] . $e['start'] ), 0, 12 ) . '@margick';
    $esc = function ( $s ) { return str_replace( [ "\\", ";", ",", "\n" ], [ "\\\\", "\\;", "\\,", "\\n" ], (string) $s ); };

    return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Margick//Trial//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nBEGIN:VEVENT\r\n"
        . 'UID:' . $uid . "\r\n"
        . 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) . "\r\n"
        . 'DTSTART:' . $e['start'] . "\r\n"
        . 'DTEND:' . $e['end'] . "\r\n"
        . 'SUMMARY:' . $esc( $e['title'] ) . "\r\n"
        . 'DESCRIPTION:' . $esc( $e['details'] ) . "\r\n"
        . 'LOCATION:' . $esc( $e['location'] ) . "\r\n"
        . "END:VEVENT\r\nEND:VCALENDAR\r\n";
}

add_action( 'template_redirect', function () {
    $action = isset( $_GET['mgk_action'] ) ? sanitize_key( wp_unslash( $_GET['mgk_action'] ) ) : '';
    if ( $action !== 'ics' ) return;

    $ics = mgk_booking_ics_string( mgk_get_confirm_context() );

    nocache_headers();
    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="trial-lesson.ics"' );
    echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput
    exit;
} );

/* ── S12 confirmation email (with .ics attachment) ─────────────────────────
 * When the parent lands on a PAID confirmation with a real lead, email them the
 * booking confirmation and attach the .ics so Gmail/Outlook show an inline
 * "Add to calendar" button. Sent once per booking (guarded by a lead meta flag).
 */
add_action( 'template_redirect', function () {
    if ( is_admin() ) return;
    $post = get_post();
    if ( ! $post || $post->post_name !== 'trial-confirmed' ) return;

    $ctx = mgk_get_confirm_context();
    if ( mgk_get_confirm_payment_status( $ctx ) !== 'paid' ) return;

    $token = (string) ( $ctx['lead_token'] ?? '' );
    if ( $token === '' || ! function_exists( 'mgk_get_lead_by_token' ) ) return;

    $lead = mgk_get_lead_by_token( $token );
    if ( ! $lead ) return;
    $lead_id = (int) $lead->ID;

    // Send once per booking reference.
    $ref       = (string) ( $ctx['ref'] ?? '' );
    $sent_flag = (string) get_post_meta( $lead_id, 'mgk_booking_email_sent', true );
    if ( $sent_flag !== '' && ( $ref === '' || $sent_flag === $ref ) ) return;

    $to = sanitize_email( (string) get_post_meta( $lead_id, 'mgk_lead_email', true ) );
    if ( ! $to ) return;

    mgk_send_booking_confirmation_email( $to, $ctx );
    update_post_meta( $lead_id, 'mgk_booking_email_sent', $ref ?: '1' );
}, 30 );

/**
 * Email the booking confirmation with the .ics attached. Writes the .ics to a
 * temp file (wp_mail attachments need a path), sends, then cleans up.
 *
 * @return bool wp_mail result
 */
function mgk_send_booking_confirmation_email( $to, $context = [] ) {
    $context = (array) ( $context ?: mgk_get_confirm_context() );
    $summary = function_exists( 'mgk_get_booking_summary' ) ? mgk_get_booking_summary( $context ) : [];
    $site    = get_bloginfo( 'name' );
    $tutor   = $summary['tutor'] ?? 'your tutor';
    $when    = $summary['datetime'] ?? ( $summary['when'] ?? '' );
    $ref     = (string) ( $context['ref'] ?? '' );
    $zoom    = function_exists( 'mgk_get_lesson_zoom_url' ) ? mgk_get_lesson_zoom_url( $context ) : '';

    $body = sprintf(
        "Your trial lesson is booked!\n\n" .
        "Tutor: %s\n%s%s%s\n" .
        "The calendar invite is attached — open it to add the lesson to your calendar.\n\n— %s",
        $tutor,
        $when ? 'When: ' . $when . "\n" : '',
        $zoom ? 'Zoom: ' . $zoom . "\n" : '',
        $ref ? 'Reference: ' . $ref . "\n" : '',
        $site
    );

    // Build the .ics and write to a temp file for the attachment.
    $ics      = mgk_booking_ics_string( $context );
    $tmp_dir  = get_temp_dir();
    $tmp_file = trailingslashit( $tmp_dir ) . 'mgk-trial-' . ( $ref ?: substr( md5( $to . $when ), 0, 8 ) ) . '.ics';
    $attachments = [];
    if ( false !== file_put_contents( $tmp_file, $ics ) ) {
        $attachments[] = $tmp_file;
    }

    $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
    $ok = wp_mail( $to, sprintf( '%s — trial lesson booked', $site ), $body, $headers, $attachments );

    if ( $attachments && file_exists( $tmp_file ) ) {
        @unlink( $tmp_file );
    }
    return (bool) $ok;
}
