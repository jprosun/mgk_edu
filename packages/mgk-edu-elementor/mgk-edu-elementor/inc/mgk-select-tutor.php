<?php
/**
 * S09 — Trial Booking · Select Tutor — LOCKED DATA CORE.
 * ======================================================
 * Step 1 of the booking flow (SELECT_TUTOR). Confirms the chosen tutor + trial
 * offer, then sends the parent to S10 (pick slot). No payment here.
 *
 * Per the MGK 3-layer rule (ONBOARDING §1.5, PLAYBOOK §3.5): Elementor controls
 * presentation only. Everything in this file is LOCKED:
 *   - booking context (lead + tutor + proposal) resolution + validation
 *   - selected tutor source (proposal batch / CPT)
 *   - trial price / discount / GST calculation
 *   - booking + lead/proposal state
 *   - S10 route, save/resume token logic
 *
 * Reuses mgk-proposals.php (lead/token, proposal batch, expiry, tutor shape)
 * and mgk-commerce.php (money helpers). No real payment / email in this phase.
 *
 * Shortcodes are registered in this file; the thin Elementor widgets live in
 * inc/mgk-elementor.php and only forward SAFE copy + Style.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_TRIAL_DISCOUNT_PCT' ) ) define( 'MGK_TRIAL_DISCOUNT_PCT', 40 );
if ( ! defined( 'MGK_TRIAL_DURATION_MIN' ) ) define( 'MGK_TRIAL_DURATION_MIN', 90 );
if ( ! defined( 'MGK_RESUME_TTL' ) )         define( 'MGK_RESUME_TTL', DAY_IN_SECONDS );

/* ── Booking context (lead + tutor + proposal), LOCKED ───── */

/**
 * Resolve the booking context from the request query (?lead=&tutor=&proposal=).
 * Returns a normalised array; never trusts the client for tutor DATA.
 *
 * @return array{lead_token:string,tutor_slug:string,proposal_id:string}
 */
function mgk_get_booking_context_from_request() {
    return [
        'lead_token'  => mgk_get_query_filter( 'lead', '' ),
        'tutor_slug'  => sanitize_title( mgk_get_query_filter( 'tutor', '' ) ),
        'proposal_id' => mgk_get_query_filter( 'proposal', '' ),
    ];
}

/**
 * Resolve a REAL directory tutor (S02/S03 → "Book") straight from the ?tutor=
 * slug, independent of any proposal batch. This is the direct-booking entry: the
 * tutor the parent actually clicked must carry through S09→S10→S11 with the right
 * name AND price (not a proposal/demo default).
 *
 * The returned array is the canonical profile tutor, enriched with `rate_num`
 * (from meta) so the displayed trial offer (mgk_calculate_trial_offer) equals the
 * server-charged price (mgk_engine_trial_price_for_tutor) — one source of truth.
 *
 * @return array|null  null when the slug isn't a real published mg_teacher.
 */
function mgk_resolve_direct_tutor_for_booking( $tutor_slug ) {
    $tutor_slug = sanitize_title( (string) $tutor_slug );
    if ( ! $tutor_slug || ! function_exists( 'mgk_profile_tutor' ) ) {
        return null;
    }
    $tutor = mgk_profile_tutor( $tutor_slug );
    if ( ! $tutor || empty( $tutor['id'] ) || get_post_type( (int) $tutor['id'] ) !== 'mg_teacher' ) {
        return null;
    }
    if ( empty( $tutor['rate_num'] ) ) {
        $tutor['rate_num'] = (int) get_post_meta( (int) $tutor['id'], 'mgk_rate_num', true );
    }
    if ( ! isset( $tutor['proposal_id'] ) ) {
        $tutor['proposal_id'] = 0;
    }
    return $tutor;
}

/**
 * Resolve the selected tutor for booking from the proposal batch (locked
 * source). Falls back to demo data via mgk_get_proposal_batch() so the page
 * renders in a bare environment. Returns the normalised tutor array or null.
 */
function mgk_get_selected_tutor_for_booking( $lead_token, $tutor_slug ) {
    if ( ! function_exists( 'mgk_get_proposal_batch' ) ) return null;

    $batch = mgk_get_proposal_batch( $lead_token );
    $proposals = $batch['proposals'] ?? [];
    if ( ! $proposals ) return null;

    $tutor_slug = sanitize_title( (string) $tutor_slug );
    if ( $tutor_slug ) {
        foreach ( $proposals as $p ) {
            if ( ( $p['slug'] ?? '' ) === $tutor_slug ) return $p;
        }
    }
    // No/unknown slug → first proposal is the safe default selection.
    return $proposals[0];
}

/**
 * Build + validate the full S09 view context. Returns:
 *   ['status'=>'ok'|'not_found'|'expired'|'unavailable', 'tutor'=>..,
 *    'offer'=>.., 'breakdown'=>.., 'context'=>.., 'batch'=>..]
 */
function mgk_get_select_tutor_view() {
    $context = mgk_get_booking_context_from_request();

    // Direct booking from the tutor directory (S02/S03): no proposal/lead, just a
    // real ?tutor= slug. Resolve that exact tutor so S09→S10→S11 show the chosen
    // tutor's real name + price and the hold books the right tutor.
    if ( empty( $context['lead_token'] ) ) {
        $direct = mgk_resolve_direct_tutor_for_booking( $context['tutor_slug'] );
        if ( $direct ) {
            return [
                'status'    => 'ok',
                'context'   => $context,
                'batch'     => [],
                'tutor'     => $direct,
                'offer'     => mgk_calculate_trial_offer( $direct ),
                'breakdown' => function_exists( 'mgk_get_trial_price_breakdown' ) ? mgk_get_trial_price_breakdown( $direct ) : [],
            ];
        }
    }

    if ( ! function_exists( 'mgk_get_proposal_batch' ) ) {
        return [ 'status' => 'not_found', 'context' => $context ];
    }

    $batch = mgk_get_proposal_batch( $context['lead_token'] );

    // Proposal batch present but empty → nothing to book.
    if ( empty( $batch['proposals'] ) ) {
        return [ 'status' => 'not_found', 'context' => $context, 'batch' => $batch ];
    }

    // Expired proposals.
    if ( function_exists( 'mgk_is_proposal_expired' ) && mgk_is_proposal_expired( $batch ) ) {
        return [ 'status' => 'expired', 'context' => $context, 'batch' => $batch ];
    }

    $tutor = mgk_get_selected_tutor_for_booking( $context['lead_token'], $context['tutor_slug'] );
    if ( ! $tutor ) {
        return [ 'status' => 'unavailable', 'context' => $context, 'batch' => $batch ];
    }

    // Parent reached S09 with a valid tutor → record the choice and advance the
    // lead PROPOSED → ACCEPTED (per spec). Idempotent + side-effect-free on
    // re-load: only transitions a real lead that is exactly PROPOSED.
    mgk_accept_proposal_for_booking( $batch, $tutor );

    return [
        'status'    => 'ok',
        'context'   => $context,
        'batch'     => $batch,
        'tutor'     => $tutor,
        'offer'     => mgk_calculate_trial_offer( $tutor ),
        'breakdown' => mgk_get_trial_price_breakdown( $tutor ),
    ];
}

/**
 * Mark the chosen tutor as ACCEPTED on the lead when the parent lands on S09.
 * Records the selected tutor + matching proposal, then moves PROPOSED→ACCEPTED.
 * No-op when there's no real lead (demo) or the lead has already moved past
 * PROPOSED (e.g. ACCEPTED/SLOT_HELD) — so re-visiting S09 is safe.
 */
function mgk_accept_proposal_for_booking( $batch, $tutor ) {
    $lead = $batch['lead'] ?? null;
    if ( ! ( $lead instanceof WP_Post ) || empty( $tutor['slug'] ) ) {
        return; // demo / no real lead — nothing to persist
    }
    $lead_id = (int) $lead->ID;

    // Persist which tutor (and proposal) the parent chose — used by S10/S11.
    update_post_meta( $lead_id, 'mgk_lead_selected_tutor_slug', sanitize_title( $tutor['slug'] ) );
    if ( ! empty( $tutor['id'] ) ) {
        update_post_meta( $lead_id, 'mgk_lead_selected_tutor_id', (int) $tutor['id'] );
    }
    if ( ! empty( $tutor['proposal_id'] ) ) {
        update_post_meta( $lead_id, 'mgk_lead_selected_proposal_id', (int) $tutor['proposal_id'] );
        // Reflect the choice on the proposal record itself.
        update_post_meta( (int) $tutor['proposal_id'], 'mgk_prop_status', 'SELECTED' );
        update_post_meta( (int) $tutor['proposal_id'], 'mgk_prop_selected_at', current_time( 'mysql', true ) );
    }

    if ( function_exists( 'mgk_lead_transition' ) && function_exists( 'mgk_lead_can_transition' ) ) {
        $state = get_post_meta( $lead_id, 'mgk_lead_state', true );
        if ( $state === MGK_LEAD_PROPOSED && mgk_lead_can_transition( $state, MGK_LEAD_ACCEPTED ) ) {
            mgk_lead_transition( $lead_id, MGK_LEAD_ACCEPTED );
            do_action( 'mgk_proposal_accepted', $lead_id, $tutor );
        }
    }
}

/** Lightweight validator (kept separate per the spec's required-helpers list). */
function mgk_validate_booking_context( $context ) {
    $view = mgk_get_select_tutor_view();
    return $view['status'] === 'ok';
}

/* ── Trial offer / pricing (LOCKED) ──────────────────────── */

/**
 * Compute the trial offer for a tutor.
 * Trial price ≈ hourly rate × (1 − discount); rounded to the nearest $5 so the
 * marketing figures stay clean (e.g. $65 → $40, save $25, 40% off).
 *
 * @return array{hourly_rate:int,trial_price:int,old_price:int,discount_percent:int,saving:int,duration_min:int}
 */
function mgk_calculate_trial_offer( $tutor, $duration_min = MGK_TRIAL_DURATION_MIN ) {
    $rate = (int) ( is_array( $tutor ) ? ( $tutor['rate_num'] ?? 0 ) : $tutor );
    if ( $rate <= 0 ) $rate = 65; // safe demo default

    // The trial % is agency-configurable (wp-admin → Discounts). Fall back to the
    // constant so this stays correct even if the discount engine isn't loaded.
    $pct   = function_exists( 'mgk_discount_rule' ) ? (int) mgk_discount_rule( 'trial_pct', MGK_TRIAL_DISCOUNT_PCT ) : (int) MGK_TRIAL_DISCOUNT_PCT;
    if ( function_exists( 'mgk_discount_rule' ) && ! mgk_discount_rule( 'trial_enabled', 1 ) ) $pct = 0;
    $raw   = $rate * ( 1 - $pct / 100 );
    $trial = (int) ( round( $raw / 5 ) * 5 );        // nearest $5
    if ( $trial <= 0 ) $trial = (int) round( $raw );
    if ( $trial >= $rate ) $trial = (int) round( $raw );

    return [
        'hourly_rate'      => $rate,
        'old_price'        => $rate,
        'trial_price'      => $trial,
        'discount_percent' => $pct,
        'saving'           => max( 0, $rate - $trial ),
        'duration_min'     => (int) $duration_min,
    ];
}

/** Back-compat shim per spec's helper list. */
function mgk_calculate_trial_price_offer( $hourly_rate, $duration_min = MGK_TRIAL_DURATION_MIN ) {
    return mgk_calculate_trial_offer( [ 'rate_num' => (int) $hourly_rate ], $duration_min );
}

/** True if GST is configured (agency/site setting). Default: inclusive. */
function mgk_trial_gst_inclusive() {
    $setting = function_exists( 'mgk_site_setting' ) ? mgk_site_setting( 'price_gst_note' ) : '';
    return $setting === '' ? true : ( strtolower( (string) $setting ) !== 'none' );
}

/**
 * Checkout breakdown rows for the trial.
 *
 * @return array{rows:array<int,array{label:string,value:string,accent:bool,strong:bool}>,
 *               gst_note:string,due:string}
 */
function mgk_get_trial_price_breakdown( $tutor, $agency_config = [] ) {
    $offer = mgk_calculate_trial_offer( $tutor );
    $dur_h = rtrim( rtrim( number_format( $offer['duration_min'] / 60, 1 ), '0' ), '.' );

    $money = function ( $n ) {
        return '$' . number_format( (float) $n, 2 );
    };

    $rows = [
        [
            'label'  => sprintf( 'Trial lesson (%sh)', $dur_h ),
            'value'  => $money( $offer['old_price'] ),
            'accent' => false, 'strong' => false,
        ],
        [
            'label'  => sprintf( 'Trial discount (%d%%)', $offer['discount_percent'] ),
            'value'  => '-' . $money( $offer['saving'] ),
            'accent' => true, 'strong' => false,
        ],
        [
            'label'  => 'Due at checkout',
            'value'  => $money( $offer['trial_price'] ),
            'accent' => true, 'strong' => true,
        ],
    ];

    return [
        'rows'     => $rows,
        'due'      => $money( $offer['trial_price'] ),
        'gst_note' => mgk_trial_gst_inclusive() ? 'INCL. GST' : 'GST not applicable',
    ];
}

/* ── Routes + tokens (LOCKED) ────────────────────────────── */

/** S10 slot-picker URL carrying the booking context. */
function mgk_get_s10_slot_url( $context ) {
    $context = (array) $context;
    $url = home_url( '/book-slot/' );
    $args = array_filter( [
        'lead'  => $context['lead_token'] ?? '',
        'tutor' => $context['tutor_slug'] ?? '',
    ] );
    return $args ? add_query_arg( $args, $url ) : $url;
}

/** Back-to-proposals URL with lead context. */
function mgk_get_back_to_proposals_url( $lead_token ) {
    $url = home_url( '/tutor-proposals/' );
    $lead_token = sanitize_text_field( (string) $lead_token );
    return $lead_token ? add_query_arg( [ 'lead' => $lead_token ], $url ) : $url;
}

/** Create a short-lived resume token bound to the booking context. */
function mgk_create_resume_token( $context ) {
    $context = (array) $context;
    $token = wp_generate_password( 18, false, false );
    set_transient( 'mgk_resume_' . $token, [
        'lead_token' => $context['lead_token'] ?? '',
        'tutor_slug' => $context['tutor_slug'] ?? '',
        'created'    => time(),
    ], MGK_RESUME_TTL );
    return $token;
}

/** Save & resume URL (mock-safe; real email only when backend ready). */
function mgk_get_save_resume_url( $context ) {
    $context = (array) $context;
    $args = array_filter( [
        'lead'  => $context['lead_token'] ?? '',
        'tutor' => $context['tutor_slug'] ?? '',
        'mgk_action' => 'save_resume',
    ] );
    return add_query_arg( $args, home_url( '/parent/trial/' ) );
}

/* ── Verification badge (presentation-safe DATA read) ────── */

function mgk_get_tutor_verification_badge( $tutor ) {
    $label = is_array( $tutor ) ? ( $tutor['verified_label'] ?? '' ) : '';
    return $label !== '' ? '✓ VERIFIED' : '';
}

/* ── Booking progress indicator (reusable S09/S10/S11) ───── */

/**
 * Render the 3-step booking progress. $current_step ∈ {1,2,3}.
 * Reusable across S09 (1), S10 (2), S11 (3). Step state is LOCKED — not an
 * Elementor control.
 *
 * @param int   $current_step
 * @param array $labels  optional SAFE label overrides ['select','slot','pay']
 */
function mgk_render_booking_progress( $current_step = 1, $labels = [] ) {
    $current_step = max( 1, min( 3, (int) $current_step ) );
    $steps = [
        1 => $labels['select'] ?? 'Select tutor',
        2 => $labels['slot']   ?? 'Pick slot',
        3 => $labels['pay']    ?? 'Pay',
    ];

    ob_start();
    ?>
    <div class="mgk-bk-progress" role="list" aria-label="Booking steps">
        <?php foreach ( $steps as $n => $label ) :
            $state = $n < $current_step ? 'is-done' : ( $n === $current_step ? 'is-active' : 'is-todo' ); ?>
        <?php if ( $n > 1 ) : ?><span class="mgk-bk-progress-line" aria-hidden="true"></span><?php endif; ?>
        <div class="mgk-bk-step <?php echo esc_attr( $state ); ?>" role="listitem"<?php echo $n === $current_step ? ' aria-current="step"' : ''; ?>>
            <span class="mgk-bk-step-num"><?php echo (int) $n; ?></span>
            <span class="mgk-bk-step-label"><?php echo esc_html( $label ); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/* ── Tracking (server-side hook; JS mirrors client-side) ─── */

function mgk_select_tutor_track( $event, $tutor = [], $offer = [] ) {
    if ( ! function_exists( 'mgk_track_event' ) ) return;
    mgk_track_event( $event, array_filter( [
        'tutor_id'         => (int) ( $tutor['id'] ?? 0 ),
        'tutor_tier'       => (string) ( $tutor['tier'] ?? '' ),
        'hourly_rate'      => (int) ( $offer['hourly_rate'] ?? 0 ),
        'trial_price'      => (int) ( $offer['trial_price'] ?? 0 ),
        'discount_percent' => (int) ( $offer['discount_percent'] ?? 0 ),
    ] ) );
}

/* ── Render helper + shortcodes (thin shells → partials) ─── */

/** Render an S09 partial, merging the locked view + SAFE copy atts. */
function mgk_select_tutor_part( $part, $atts = [] ) {
    $atts = is_array( $atts ) ? array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } ) : [];
    // Composite page partial resolves its own view; section partials get it injected.
    if ( $part === 'select-tutor' ) {
        return mgk_render_part( 'template-parts/sections/booking/select-tutor', $atts );
    }
    $view = mgk_get_select_tutor_view();
    return mgk_render_part( 'template-parts/sections/booking/' . $part, array_merge( $view, $atts ) );
}

/** Composite — whole S09 page. */
add_shortcode( 'mgk_select_tutor', function ( $atts ) {
    return mgk_select_tutor_part( 'select-tutor', (array) $atts );
} );

/** Nav. */
add_shortcode( 'mgk_booking_nav', function ( $atts ) {
    $atts = shortcode_atts( [
        'utility' => '', 'logo_label' => '', 'secure_label' => '', 'signin_label' => '', 'hide_secure' => '',
    ], (array) $atts, 'mgk_booking_nav' );
    return mgk_select_tutor_part( 'nav', $atts );
} );

/** Progress (current step locked = 1 for S09). */
add_shortcode( 'mgk_booking_progress', function ( $atts ) {
    $atts = shortcode_atts( [ 'select' => '', 'slot' => '', 'pay' => '', 'step' => 0 ], (array) $atts, 'mgk_booking_progress' );

    // Step is DATA, not an Elementor control. Resolve from an explicit att
    // (used when composing in PHP) else from the current booking page.
    $step = (int) $atts['step'];
    if ( $step < 1 ) {
        if ( function_exists( 'is_page' ) && is_page( 'book-slot' ) )      $step = 2;
        elseif ( function_exists( 'is_page' ) && is_page( [ 'trial-pay', 'pay' ] ) ) $step = 3;
        else $step = 1;
    }
    $atts['current'] = max( 1, min( 3, $step ) );
    return mgk_select_tutor_part( 'booking-progress', $atts );
} );

/** Chosen tutor card. */
add_shortcode( 'mgk_chosen_tutor', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'subjects' => '', 'location' => '', 'change_label' => '', 'back_label' => '',
        'avatar_label' => '', 'hide_heading' => '',
    ], (array) $atts, 'mgk_chosen_tutor' );
    return mgk_select_tutor_part( 'chosen-tutor-card', $atts );
} );

/** What's in the trial lesson. */
add_shortcode( 'mgk_trial_included', function ( $atts ) {
    $atts = shortcode_atts( [ 'heading' => '', 'bullet_1' => '', 'bullet_2' => '', 'bullet_3' => '' ], (array) $atts, 'mgk_trial_included' );
    return mgk_select_tutor_part( 'trial-included', $atts );
} );

/** Trial offer box. */
add_shortcode( 'mgk_trial_offer', function ( $atts ) {
    $atts = shortcode_atts( [ 'label' => '', 'badge' => '', 'note' => '' ], (array) $atts, 'mgk_trial_offer' );
    return mgk_select_tutor_part( 'trial-offer', $atts );
} );

/** Checkout price breakdown. */
add_shortcode( 'mgk_trial_breakdown', function ( $atts ) {
    return mgk_select_tutor_part( 'price-breakdown', (array) $atts );
} );

/** Continue CTA + save/resume. */
add_shortcode( 'mgk_booking_cta', function ( $atts ) {
    $atts = shortcode_atts( [ 'cta_label' => '', 'resume_label' => '', 'nopay_label' => '' ], (array) $atts, 'mgk_booking_cta' );
    return mgk_select_tutor_part( 'booking-cta', $atts );
} );

/* ── Save & resume POST handler (mock-safe, no payment) ──── */

add_action( 'template_redirect', function () {
    $action = isset( $_GET['mgk_action'] ) ? sanitize_key( wp_unslash( $_GET['mgk_action'] ) ) : '';
    if ( $action !== 'save_resume' ) return;

    $context = mgk_get_booking_context_from_request();
    $token   = mgk_create_resume_token( $context );

    // No real email in this phase — redirect back with a safe success flag.
    $back = add_query_arg( array_filter( [
        'lead'        => $context['lead_token'],
        'tutor'       => $context['tutor_slug'],
        'mgk_resumed' => $token,
    ] ), home_url( '/parent/trial/' ) );

    wp_safe_redirect( $back );
    exit;
} );
