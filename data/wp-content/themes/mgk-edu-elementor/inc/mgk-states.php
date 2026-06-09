<?php
/**
 * MGK state machine constants and transition guards.
 * Single source of truth — import these constants everywhere state is read/written.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Lead / Booking states (13) ──────────────────────────── */
define( 'MGK_LEAD_CAPTURED',          'captured' );
define( 'MGK_LEAD_PENDING_REVIEW',    'pending_review' ); // submitted, awaiting admin accept/reject
define( 'MGK_LEAD_QUALIFIED',         'qualified' );
define( 'MGK_LEAD_MATCHED',           'matched' );
define( 'MGK_LEAD_PROPOSED',          'proposed' );
define( 'MGK_LEAD_ACCEPTED',          'accepted' );    // parent chose a tutor (S08→S09)
define( 'MGK_LEAD_SLOT_HELD',         'slot_held' );
define( 'MGK_LEAD_PAYMENT_PENDING',   'payment_pending' );
define( 'MGK_LEAD_CONFIRMED',         'confirmed' );
define( 'MGK_LEAD_PAID',              'paid' );
define( 'MGK_LEAD_LESSON_SCHEDULED',  'lesson_scheduled' );
define( 'MGK_LEAD_LESSON_COMPLETED',  'lesson_completed' );
define( 'MGK_LEAD_REVIEW_PENDING',    'review_pending' );
define( 'MGK_LEAD_CLOSED_WON',        'closed_won' );
define( 'MGK_LEAD_CLOSED_LOST',       'closed_lost' );
define( 'MGK_LEAD_EXPIRED',           'expired' );      // proposals lapsed (48h, BR-11)

/* ── Slot states (5) ─────────────────────────────────────── */
define( 'MGK_SLOT_AVAILABLE',  'available' );
define( 'MGK_SLOT_HELD',       'held' );
define( 'MGK_SLOT_RESERVED',   'reserved' );
define( 'MGK_SLOT_COMPLETED',  'completed' );
define( 'MGK_SLOT_CANCELLED',  'cancelled' );

/* ── Tutor states (6) ────────────────────────────────────── */
define( 'MGK_TUTOR_DRAFT',        'draft' );
define( 'MGK_TUTOR_ACTIVE',       'active' );
define( 'MGK_TUTOR_PAUSED',       'paused' );
define( 'MGK_TUTOR_SUSPENDED',    'suspended' );
define( 'MGK_TUTOR_DEACTIVATED',  'deactivated' );
define( 'MGK_TUTOR_BANNED',       'banned' );

/* ── Tutor verification states (5) ──────────────────────── */
define( 'MGK_VERIFY_UNVERIFIED',          'unverified' );
define( 'MGK_VERIFY_ID_SUBMITTED',        'id_submitted' );
define( 'MGK_VERIFY_DEGREE_VERIFIED',     'degree_verified' );
define( 'MGK_VERIFY_BACKGROUND_CLEARED',  'background_cleared' );
define( 'MGK_VERIFY_VERIFIED',            'verified' );

/* ── Allowed lead transitions ────────────────────────────── */

function mgk_lead_transitions() {
    return [
        // slot_held added here for the self-serve booking flow (parent books directly without agency matching)
        // S07 submit lands at PENDING_REVIEW. Admin Accept → QUALIFIED (then
        // matching), Reject → CLOSED_LOST. No messages are sent before Accept.
        MGK_LEAD_CAPTURED         => [ MGK_LEAD_PENDING_REVIEW, MGK_LEAD_QUALIFIED, MGK_LEAD_SLOT_HELD, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_PENDING_REVIEW   => [ MGK_LEAD_QUALIFIED, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_QUALIFIED        => [ MGK_LEAD_MATCHED,   MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_MATCHED          => [ MGK_LEAD_PROPOSED,  MGK_LEAD_CLOSED_LOST ],
        // S08 "Book Trial" → ACCEPTED (parent picked a tutor) → S09. From there
        // the slot picker (S10) holds a slot. ACCEPTED can fall back to PROPOSED
        // if the parent returns to S08 to pick a different tutor.
        MGK_LEAD_PROPOSED         => [ MGK_LEAD_ACCEPTED, MGK_LEAD_EXPIRED, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_ACCEPTED         => [ MGK_LEAD_SLOT_HELD, MGK_LEAD_PROPOSED, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_SLOT_HELD        => [ MGK_LEAD_PAYMENT_PENDING, MGK_LEAD_ACCEPTED, MGK_LEAD_PROPOSED ],
        // Expired proposals can be re-engaged: a free re-match sends a fresh
        // set (→ PROPOSED) without the parent re-filling the form (BR-11).
        MGK_LEAD_EXPIRED          => [ MGK_LEAD_MATCHED, MGK_LEAD_PROPOSED, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_PAYMENT_PENDING  => [ MGK_LEAD_PAID, MGK_LEAD_CONFIRMED, MGK_LEAD_SLOT_HELD ],
        MGK_LEAD_CONFIRMED        => [ MGK_LEAD_PAID, MGK_LEAD_LESSON_SCHEDULED, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_PAID             => [ MGK_LEAD_LESSON_SCHEDULED ],
        MGK_LEAD_LESSON_SCHEDULED => [ MGK_LEAD_LESSON_COMPLETED, MGK_LEAD_CLOSED_LOST ],
        MGK_LEAD_LESSON_COMPLETED => [ MGK_LEAD_REVIEW_PENDING, MGK_LEAD_CLOSED_WON ],
        MGK_LEAD_REVIEW_PENDING   => [ MGK_LEAD_CLOSED_WON ],
        MGK_LEAD_CLOSED_WON       => [],
        MGK_LEAD_CLOSED_LOST      => [],
    ];
}

function mgk_lead_can_transition( $from, $to ) {
    $map = mgk_lead_transitions();
    return in_array( $to, $map[ $from ] ?? [], true );
}

/**
 * Transition a lead post to a new state.
 * Returns true on success, WP_Error if the transition is not allowed.
 */
function mgk_lead_transition( $post_id, $new_state ) {
    $current = get_post_meta( $post_id, 'mgk_lead_state', true ) ?: MGK_LEAD_CAPTURED;

    if ( ! mgk_lead_can_transition( $current, $new_state ) ) {
        return new WP_Error(
            'mgk_invalid_transition',
            "Cannot transition lead from '{$current}' to '{$new_state}'."
        );
    }

    update_post_meta( $post_id, 'mgk_lead_previous_state', $current );
    update_post_meta( $post_id, 'mgk_lead_state', $new_state );
    do_action( 'mgk_lead_state_changed', $post_id, $current, $new_state );

    return true;
}
