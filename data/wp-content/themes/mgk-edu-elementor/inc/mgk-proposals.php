<?php
/**
 * S08 Proposal page: PHP-native data core + editable Elementor shell.
 *
 * The proposal/tutor data, expiry rules, compare data and booking route are
 * resolved here. Elementor widgets only pass safe labels, visibility toggles
 * and style selectors through to the partials.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_proposal_bool( $value ) {
    return in_array( strtolower( (string) $value ), [ '1', 'yes', 'true', 'on' ], true );
}

function mgk_proposal_request_key() {
    $lead = mgk_get_query_filter( 'lead', '' );
    if ( $lead ) {
        return $lead;
    }
    $token = mgk_get_query_filter( 'token', '' );
    if ( $token ) {
        return $token;
    }
    $lead_id = (int) mgk_get_query_filter( 'lead_id', '0' );
    return $lead_id ?: 'demo';
}

function mgk_get_lead_by_token( $token ) {
    $token = sanitize_text_field( (string) $token );
    if ( $token === '' || ! post_type_exists( 'mg_lead' ) ) {
        return null;
    }

    $posts = get_posts( [
        'post_type'      => 'mg_lead',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
        'meta_query'     => [
            [ 'key' => 'mgk_lead_token', 'value' => $token, 'compare' => '=' ],
        ],
    ] );

    return $posts ? $posts[0] : null;
}

function mgk_get_proposal_lead( $lead_token_or_id = '' ) {
    if ( is_numeric( $lead_token_or_id ) && (int) $lead_token_or_id > 0 ) {
        $post = get_post( (int) $lead_token_or_id );
        return ( $post && $post->post_type === 'mg_lead' ) ? $post : null;
    }

    return mgk_get_lead_by_token( $lead_token_or_id );
}

/**
 * Build the S08 magic link for a lead: /tutor-proposals/?token=<lead token>.
 * The token is generated if the lead doesn't have one yet.
 */
function mgk_proposal_magic_link( $lead_id ) {
    $lead_id = (int) $lead_id;
    $token   = (string) get_post_meta( $lead_id, 'mgk_lead_token', true );
    if ( $token === '' ) {
        $token = wp_generate_password( 20, false, false );
        update_post_meta( $lead_id, 'mgk_lead_token', $token );
    }
    return add_query_arg( 'token', rawurlencode( $token ), home_url( '/tutor-proposals/' ) );
}

/**
 * Agency action: send the hand-picked proposals to the parent.
 *
 * Stamps the send time (starts the 48h expiry), moves the lead to PROPOSED, and
 * notifies the parent via WhatsApp + Email with the S08 magic link. This is the
 * S26 "Send Proposals" step. Idempotent enough to re-run (re-send) — it just
 * re-stamps sent_at and re-notifies.
 *
 * @return array{ok:bool,count:int,link:string,wa:string,email:bool,error:string}
 */
function mgk_send_proposals( $lead_id ) {
    $lead_id = (int) $lead_id;
    $lead    = $lead_id ? get_post( $lead_id ) : null;
    if ( ! $lead || $lead->post_type !== 'mg_lead' ) {
        return [ 'ok' => false, 'count' => 0, 'link' => '', 'wa' => '', 'email' => false, 'error' => 'Lead not found.' ];
    }

    $proposals = mgk_get_proposals_for_lead( $lead_id );
    $count     = count( $proposals );
    if ( $count < 1 ) {
        return [ 'ok' => false, 'count' => 0, 'link' => '', 'wa' => '', 'email' => false,
                 'error' => 'No proposals attached to this lead yet. Create mg_proposal records first.' ];
    }

    // Start the 48h expiry window and move the lead to PROPOSED. The lead may be
    // at QUALIFIED (straight after S07), or EXPIRED (a free re-match resend);
    // walk it forward through MATCHED to PROPOSED.
    $state_before = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: MGK_LEAD_CAPTURED;

    // If this send is a re-match out of EXPIRED, consume the one free re-match.
    if ( $state_before === MGK_LEAD_EXPIRED && ! get_post_meta( $lead_id, 'mgk_free_rematch_used', true ) ) {
        update_post_meta( $lead_id, 'mgk_free_rematch_used', time() );
    }

    update_post_meta( $lead_id, 'mgk_proposals_sent_at', time() );
    delete_post_meta( $lead_id, 'mgk_proposals_expired_at' );

    if ( function_exists( 'mgk_lead_transition' ) ) {
        foreach ( [ MGK_LEAD_QUALIFIED, MGK_LEAD_MATCHED, MGK_LEAD_PROPOSED ] as $next ) {
            $state = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: $state_before;
            if ( $state === MGK_LEAD_PROPOSED ) break;
            if ( mgk_lead_can_transition( $state, $next ) ) {
                mgk_lead_transition( $lead_id, $next );
            }
        }
    }

    // Human labels for the messages.
    $subject = mgk_proposal_term_label( 'mgk_subject', get_post_meta( $lead_id, 'mgk_lead_subject', true ) );
    $level   = mgk_proposal_term_label( 'mgk_level',   get_post_meta( $lead_id, 'mgk_lead_child_level', true ) );
    $link    = mgk_proposal_magic_link( $lead_id );

    // 1) WhatsApp (demo-safe; no-op log when not configured).
    $wa = [ 'mode' => 'skipped' ];
    if ( function_exists( 'mgk_wa_send_proposals_ready' ) ) {
        $wa = mgk_wa_send_proposals_ready( $lead_id, $link, $subject, $level, $count );
    }

    // 2) Email via wp_mail (built-in; works without external API).
    $email_ok = false;
    $to = sanitize_email( (string) get_post_meta( $lead_id, 'mgk_lead_parent_email', true ) );
    if ( $to ) {
        $site = get_bloginfo( 'name' );
        $subj = sprintf( '%s — your matched tutors are ready', $site );
        $body = sprintf(
            "Good news! We've hand-picked %d tutor%s for %s%s.\n\n" .
            "View your matches (valid 48 hours):\n%s\n\n" .
            "No login needed — just tap the link.\n\n— %s",
            $count, ( $count === 1 ? '' : 's' ),
            $subject ?: 'your request',
            $level ? ' (' . $level . ')' : '',
            $link,
            $site
        );
        $email_ok = (bool) wp_mail( $to, $subj, $body );
    }

    do_action( 'mgk_proposals_sent', $lead_id, $proposals, $link );

    return [
        'ok'    => true,
        'count' => $count,
        'link'  => $link,
        'wa'    => $wa['mode'] ?? '',
        'email' => $email_ok,
        'error' => '',
    ];
}

/**
 * Mark expired proposals on a single lead (BR-11): PROPOSED + sent > 48h ago and
 * no parent action → EXPIRED. Releases the proposed tutors (so they're free for
 * other requests), notifies the agency, and opens a 7-day free re-match window.
 *
 * @return bool true if the lead was expired by this call.
 */
function mgk_expire_lead_proposals( $lead_id ) {
    $lead_id = (int) $lead_id;
    $state   = get_post_meta( $lead_id, 'mgk_lead_state', true );
    if ( $state !== MGK_LEAD_PROPOSED ) {
        return false; // only PROPOSED leads expire; SLOT_HELD/paid have acted
    }

    $sent_at = (int) get_post_meta( $lead_id, 'mgk_proposals_sent_at', true );
    if ( ! $sent_at || time() < $sent_at + DAY_IN_SECONDS * 2 ) {
        return false; // still inside the 48h window
    }

    // Transition PROPOSED → EXPIRED.
    if ( function_exists( 'mgk_lead_transition' ) ) {
        mgk_lead_transition( $lead_id, MGK_LEAD_EXPIRED );
    } else {
        update_post_meta( $lead_id, 'mgk_lead_state', MGK_LEAD_EXPIRED );
    }
    update_post_meta( $lead_id, 'mgk_proposals_expired_at', time() );

    // Release the proposed tutors and mark the proposal records EXPIRED so they
    // no longer hold the tutor for this lead.
    $released = [];
    foreach ( get_posts( [
        'post_type'      => 'mg_proposal',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => 'mgk_prop_lead_id', 'value' => $lead_id, 'compare' => '=' ] ],
    ] ) as $prop_id ) {
        update_post_meta( $prop_id, 'mgk_prop_status', 'EXPIRED' );
        $released[] = (int) get_post_meta( $prop_id, 'mgk_prop_tutor_id', true );
    }

    // Open the 7-day free re-match window (BR-11) if not already used.
    if ( ! get_post_meta( $lead_id, 'mgk_free_rematch_used', true ) ) {
        update_post_meta( $lead_id, 'mgk_free_rematch_until', time() + DAY_IN_SECONDS * 7 );
    }

    do_action( 'mgk_proposals_expired', $lead_id, array_filter( $released ) );
    mgk_notify_agency_expired( $lead_id );

    return true;
}

/**
 * Cron sweep: expire every lead whose proposals have lapsed. Runs hourly.
 * Returns the number of leads expired (handy for logging/tests).
 */
function mgk_expire_stale_proposals() {
    if ( ! post_type_exists( 'mg_lead' ) ) {
        return 0;
    }
    $leads = get_posts( [
        'post_type'      => 'mg_lead',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => 'mgk_lead_state', 'value' => MGK_LEAD_PROPOSED, 'compare' => '=' ] ],
    ] );
    $n = 0;
    foreach ( $leads as $lead_id ) {
        if ( mgk_expire_lead_proposals( $lead_id ) ) {
            $n++;
        }
    }
    return $n;
}

/**
 * Notify the agency admin that a lead's proposals expired with no action, so
 * they can re-engage or release. Email goes to the admin; WhatsApp reuses the
 * configured admin number (demo-safe).
 */
function mgk_notify_agency_expired( $lead_id ) {
    $lead_id = (int) $lead_id;
    $subject = mgk_proposal_term_label( 'mgk_subject', get_post_meta( $lead_id, 'mgk_lead_subject', true ) ) ?: '—';
    $level   = mgk_proposal_term_label( 'mgk_level',   get_post_meta( $lead_id, 'mgk_lead_child_level', true ) ) ?: '—';
    $edit    = admin_url( 'post.php?post=' . $lead_id . '&action=edit' );

    // WhatsApp to the admin notify number, if a template is configured.
    if ( function_exists( 'mgk_wa_config' ) && function_exists( 'mgk_wa_send' ) ) {
        $cfg = mgk_wa_config();
        if ( ! empty( $cfg['admin_notify_number'] ) ) {
            mgk_wa_send(
                $cfg['admin_notify_number'],
                $cfg['tpl_expired_agency'] ?? '',
                [ $subject, $level, (string) $lead_id ],
                $cfg['tpl_admin_lang'] ?? 'en'
            );
        }
    }

    // Email to the site admin.
    $to = get_option( 'admin_email' );
    if ( $to ) {
        wp_mail(
            $to,
            sprintf( '[%s] Proposals expired — lead #%d needs attention', get_bloginfo( 'name' ), $lead_id ),
            sprintf(
                "A parent's proposals expired with no action (48h window, %s / %s).\n\n" .
                "Re-engage or release the tutors here:\n%s\n\n" .
                "A 7-day free re-match window is open for this lead.",
                $subject, $level, $edit
            )
        );
    }
}

function mgk_calculate_trial_price( $hourly_rate ) {
    if ( is_string( $hourly_rate ) && preg_match( '/(\d+(?:\.\d+)?)/', $hourly_rate, $m ) ) {
        $hourly_rate = (float) $m[1];
    }
    $hourly_rate = (float) $hourly_rate;
    if ( $hourly_rate <= 0 ) {
        return 0;
    }
    return (int) round( $hourly_rate * 0.6 );
}

function mgk_get_proposal_expiry( $proposal_batch ) {
    if ( ! empty( $proposal_batch['expires_at'] ) ) {
        return (int) $proposal_batch['expires_at'];
    }
    $sent_at = ! empty( $proposal_batch['sent_at'] ) ? (int) $proposal_batch['sent_at'] : time();
    return $sent_at + DAY_IN_SECONDS * 2;
}

function mgk_is_proposal_expired( $proposal_batch ) {
    return time() >= mgk_get_proposal_expiry( $proposal_batch );
}

function mgk_format_proposal_summary( $lead ) {
    $level    = 'P5';
    $subject  = 'Math';
    $schedule = 'Mon/Wed/Sat';
    $budget   = '$40-90/hr';

    if ( $lead instanceof WP_Post ) {
        $level    = (string) get_post_meta( $lead->ID, 'mgk_lead_level', true ) ?: $level;
        $subject  = (string) get_post_meta( $lead->ID, 'mgk_lead_subject', true ) ?: $subject;
        $schedule = (string) get_post_meta( $lead->ID, 'mgk_lead_schedule', true ) ?: $schedule;
        $budget   = (string) get_post_meta( $lead->ID, 'mgk_lead_budget', true ) ?: $budget;
    } else {
        $level    = mgk_get_query_filter( 'level', $level );
        $subject  = mgk_get_query_filter( 'subject', $subject );
        $schedule = mgk_get_query_filter( 'schedule', $schedule );
        $budget   = mgk_get_query_filter( 'budget', $budget );
    }

    return strtoupper( sprintf(
        'We hand-picked {COUNT} tutors for %s %s - %s - %s',
        trim( $level ),
        trim( $subject ),
        trim( $schedule ),
        trim( $budget )
    ) );
}

function mgk_proposal_compact_name( $name ) {
    $name = trim( preg_replace( '/\s+/', ' ', (string) $name ) );
    $name = str_replace( '.', '', $name );
    $parts = explode( ' ', $name );
    if ( count( $parts ) >= 2 ) {
        return $parts[0] . ' ' . $parts[1];
    }
    return $name;
}

/**
 * Read the REAL agency-sent proposals for a lead from the mg_proposal CPT,
 * ordered by rank. Each proposal links lead → tutor (+ optional suggested plan)
 * and carries the agency's match reason/score. Returns a normalized list the
 * proposal cards can render, or [] when this lead has no proposals yet.
 *
 * This is the production data path (S08 = "proposal result from agency"). The
 * demo tutors below are only a fallback for design/preview when no real
 * proposals exist for the request.
 */
function mgk_get_proposals_for_lead( $lead_id ) {
    $lead_id = (int) $lead_id;
    if ( $lead_id <= 0 || ! post_type_exists( 'mg_proposal' ) ) {
        return [];
    }

    $q = new WP_Query( [
        'post_type'      => 'mg_proposal',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'no_found_rows'  => true,
        'meta_key'       => 'mgk_prop_rank_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'meta_query'     => [
            [ 'key' => 'mgk_prop_lead_id', 'value' => $lead_id, 'compare' => '=' ],
        ],
    ] );

    $out = [];
    foreach ( $q->posts as $prop ) {
        $tutor_id = (int) get_post_meta( $prop->ID, 'mgk_prop_tutor_id', true );
        $tutor    = $tutor_id ? get_post( $tutor_id ) : null;
        if ( ! $tutor || $tutor->post_type !== 'mg_teacher' ) {
            continue;
        }

        // Tutor DATA CORE comes from the mg_teacher CPT / ACF (wp-admin).
        $rate_num = (int) get_post_meta( $tutor_id, 'mgk_rate_num', true );
        $prop_rate = (int) get_post_meta( $prop->ID, 'mgk_prop_suggested_hourly_rate_sgd', true );
        if ( $prop_rate > 0 ) {
            $rate_num = $prop_rate; // agency may suggest a specific rate
        }

        $subjects = wp_get_post_terms( $tutor_id, 'mgk_subject', [ 'fields' => 'names' ] );
        $tier     = (string) get_post_meta( $tutor_id, 'mgk_tier', true );
        $exp      = (string) get_post_meta( $tutor_id, 'mgk_experience', true );

        $out[] = [
            'id'             => $tutor_id,
            'name'           => $tutor->post_title,
            'slug'           => $tutor->post_name,
            'photo'          => (string) ( get_the_post_thumbnail_url( $tutor_id, 'medium' ) ?: '' ),
            'tier'           => $tier,
            'experience'     => $exp,
            'credential'     => (string) get_post_meta( $tutor_id, 'mgk_credential_badge', true ),
            'rating'         => (string) ( get_post_meta( $tutor_id, 'mgk_rating', true ) ?: '5.0' ),
            'reviews'        => (string) ( get_post_meta( $tutor_id, 'mgk_reviews', true ) ?: '0' ),
            'response'       => (string) get_post_meta( $tutor_id, 'mgk_response', true ),
            'active'         => (string) ( get_post_meta( $tutor_id, 'mgk_last_active', true ) ?: '' ),
            'students'       => (string) ( get_post_meta( $tutor_id, 'mgk_active_students', true ) ?: '' ),
            'rate_num'       => $rate_num,
            'demo_duration'  => '',
            'verified_label' => get_post_meta( $tutor_id, 'mgk_is_verified', true ) ? 'Verified' : '',
            // The agency's hand-written match reason (S08's key trust element).
            'why'            => (string) get_post_meta( $prop->ID, 'mgk_prop_match_reason', true ),
            // Carry proposal metadata through for downstream steps.
            'proposal_id'    => $prop->ID,
            'rank'           => (int) get_post_meta( $prop->ID, 'mgk_prop_rank_order', true ),
            'plan_id'        => (int) get_post_meta( $prop->ID, 'mgk_prop_suggested_plan_id', true ),
        ];
    }

    return $out;
}

function mgk_proposal_demo_tutors() {
    return [
        [
            'id' => 101, 'name' => 'Ms Lee Yi Ling', 'slug' => 'ms-lee-yi-ling',
            'tier' => 'Ex-MOE', 'experience' => '8 yrs', 'credential' => 'NIE-trained',
            'rating' => '4.9', 'reviews' => '87', 'response' => '4h', 'active' => '2d',
            'students' => '12', 'rate_num' => 65, 'demo_duration' => '2:15', 'verified_label' => 'MOE',
            'why' => '87% PSLE A-rate - teaches your Mon/Wed/Sat slots - in budget at $65/hr',
        ],
        [
            'id' => 102, 'name' => 'Mr Tan Jun H.', 'slug' => 'mr-tan-jun-h',
            'tier' => 'Full-time', 'experience' => '5 yrs', 'credential' => 'NUS BSc',
            'rating' => '4.7', 'reviews' => '124', 'response' => '2h', 'active' => '1d',
            'students' => '18', 'rate_num' => 55, 'demo_duration' => '1:58', 'verified_label' => 'Verified',
            'why' => 'Strong word-problem focus - available weekends - $55/hr under budget',
        ],
        [
            'id' => 103, 'name' => 'Ms Goh Ai L.', 'slug' => 'ms-goh-ai-l',
            'tier' => 'NUS Part-time', 'experience' => '4 yrs', 'credential' => 'Demo',
            'rating' => '4.8', 'reviews' => '62', 'response' => '6h', 'active' => '1d',
            'students' => '9', 'rate_num' => 45, 'demo_duration' => '', 'verified_label' => 'Verified',
            'why' => 'Patient with visual learners - Mon/Sat fit - $45/hr',
        ],
        [
            'id' => 104, 'name' => 'Mr Wong Kai S.', 'slug' => 'mr-wong-kai-s',
            'tier' => 'Premium PhD', 'experience' => '10 yrs', 'credential' => 'Demo',
            'rating' => '5.0', 'reviews' => '45', 'response' => '5h', 'active' => '3d',
            'students' => '6', 'rate_num' => 90, 'demo_duration' => '', 'verified_label' => 'Verified',
            'why' => 'Top results, slightly above budget - Wed/Sat fit - $90/hr',
        ],
    ];
}

function mgk_normalize_proposal_tutor( array $tutor, $position = 0 ) {
    $fallbacks = mgk_proposal_demo_tutors();
    $fallback  = $fallbacks[ $position % count( $fallbacks ) ];
    $rate_num  = (int) ( $tutor['rate_num'] ?? $fallback['rate_num'] );
    $trial     = mgk_calculate_trial_price( $rate_num );
    $tier      = trim( (string) ( $tutor['tier'] ?? $fallback['tier'] ) );
    $exp       = trim( (string) ( $tutor['experience'] ?? $fallback['experience'] ) );
    $cred      = trim( (string) ( $tutor['credential'] ?? $fallback['credential'] ) );

    $id = ! empty( $tutor['id'] ) ? (int) $tutor['id'] : (int) $fallback['id'];
    if ( ! $id && ! empty( $tutor['slug'] ) ) {
        $id = abs( crc32( (string) $tutor['slug'] ) );
    }

    return [
        'id'             => $id,
        'name'           => (string) ( $tutor['name'] ?? $fallback['name'] ),
        'short_name'     => mgk_proposal_compact_name( (string) ( $tutor['name'] ?? $fallback['name'] ) ),
        'slug'           => sanitize_title( $tutor['slug'] ?? $fallback['slug'] ),
        'photo'          => (string) ( $tutor['photo'] ?? '' ),
        'meta'           => strtoupper( implode( ' - ', array_filter( [ $tier, $exp, $cred ] ) ) ),
        'tier'           => $tier,
        'experience'     => $exp,
        'rating'         => (string) ( $tutor['rating'] ?? $fallback['rating'] ),
        'reviews'        => (string) ( $tutor['reviews'] ?? $fallback['reviews'] ),
        'response'       => (string) ( $tutor['response'] ?? $fallback['response'] ),
        'active'         => (string) ( $tutor['active'] ?? $fallback['active'] ),
        'students'       => (string) ( $tutor['students'] ?? $fallback['students'] ),
        'rate_num'       => $rate_num,
        'rate'           => '$' . $rate_num . '/hr',
        'trial_price'    => $trial,
        'trial_label'    => 'Trial $' . $trial,
        'demo_duration'  => (string) ( $tutor['demo_duration'] ?? $fallback['demo_duration'] ),
        'verified_label' => (string) ( $tutor['verified_label'] ?? $fallback['verified_label'] ),
        'why'            => (string) ( $tutor['why'] ?? $fallback['why'] ),
    ];
}

function mgk_get_proposal_batch( $lead_token_or_id = '' ) {
    $lead_token_or_id = $lead_token_or_id ?: mgk_proposal_request_key();
    $lead = mgk_get_proposal_lead( $lead_token_or_id );

    $lead_token = 'demo-proposal';
    $sent_at    = time() - 470;
    $lead_data  = [
        'level'    => 'P5',
        'subject'  => 'Math',
        'schedule' => 'Mon/Wed/Sat',
        'budget'   => '$40-90/hr',
    ];

    $real_proposals = [];

    if ( $lead instanceof WP_Post ) {
        $lead_token = (string) get_post_meta( $lead->ID, 'mgk_lead_token', true ) ?: 'lead-' . (int) $lead->ID;
        $sent_meta  = (int) get_post_meta( $lead->ID, 'mgk_proposals_sent_at', true );
        $sent_at    = $sent_meta ?: ( get_post_time( 'U', true, $lead ) ?: $sent_at );

        // Lead fields are stored as taxonomy term IDs (subject/level) + raw
        // strings (schedule/budget). Resolve to human labels for the summary.
        $lead_data = [
            'level'    => mgk_proposal_term_label( 'mgk_level',   get_post_meta( $lead->ID, 'mgk_lead_child_level', true ) ) ?: $lead_data['level'],
            'subject'  => mgk_proposal_term_label( 'mgk_subject', get_post_meta( $lead->ID, 'mgk_lead_subject', true ) )     ?: $lead_data['subject'],
            'schedule' => (string) get_post_meta( $lead->ID, 'mgk_lead_schedule_preference', true ) ?: $lead_data['schedule'],
            'budget'   => mgk_proposal_budget_label( $lead->ID ) ?: $lead_data['budget'],
        ];

        // PRODUCTION path: the agency's hand-picked proposals for this lead.
        $real_proposals = mgk_get_proposals_for_lead( $lead->ID );
    }

    $filters = [
        'subject' => $lead_data['subject'],
        'level'   => $lead_data['level'],
        'budget'  => $lead_data['budget'],
        'sort'    => 'best-match',
    ];

    $proposals = [];
    if ( $real_proposals ) {
        // Real agency proposals — render exactly these, in rank order.
        foreach ( $real_proposals as $i => $tutor ) {
            $proposals[] = mgk_normalize_proposal_tutor( $tutor, $i );
        }
    } else {
        // Fallback for design/preview only (no real proposals for this request).
        $source_tutors = function_exists( 'mgk_filter_tutors' ) ? mgk_filter_tutors( $filters ) : [];
        if ( count( $source_tutors ) < 3 ) {
            $source_tutors = mgk_proposal_demo_tutors();
        } elseif ( count( $source_tutors ) < 4 ) {
            $source_tutors = array_merge( $source_tutors, array_slice( mgk_proposal_demo_tutors(), count( $source_tutors ) ) );
        }
        foreach ( array_slice( $source_tutors, 0, 4 ) as $i => $tutor ) {
            $proposals[] = mgk_normalize_proposal_tutor( $tutor, $i );
        }
    }

    return [
        'lead'         => $lead,
        'lead_token'   => sanitize_text_field( $lead_token ),
        'lead_data'    => $lead_data,
        'sent_at'      => $sent_at,
        'expires_at'   => $sent_at + DAY_IN_SECONDS * 2,
        'proposals'    => $proposals,
        'filters'      => $filters,
        'is_real'      => ! empty( $real_proposals ),
        'has_lead'     => ( $lead instanceof WP_Post ),
    ];
}

/** Resolve a single taxonomy term ID (stored on a lead) to its name. */
function mgk_proposal_term_label( $taxonomy, $term_id ) {
    $term_id = (int) $term_id;
    if ( $term_id <= 0 ) return '';
    $term = get_term( $term_id, $taxonomy );
    return ( $term && ! is_wp_error( $term ) ) ? $term->name : '';
}

/** Build a "$50-80/hr" budget label from the lead's min/max meta. */
function mgk_proposal_budget_label( $lead_id ) {
    $min = (int) get_post_meta( $lead_id, 'mgk_lead_budget_min_sgd', true );
    $max = (int) get_post_meta( $lead_id, 'mgk_lead_budget_max_sgd', true );
    if ( ! $min && ! $max ) return '';
    if ( $min && $max )    return '$' . $min . '-' . $max . '/hr';
    return '$' . ( $min ?: $max ) . '/hr';
}

function mgk_get_proposal_view_model( $atts = [] ) {
    $batch = mgk_get_proposal_batch( $atts['lead'] ?? '' );
    $summary = str_replace( '{COUNT}', (string) count( $batch['proposals'] ), mgk_format_proposal_summary( $batch['lead'] ) );

    return [
        'batch'     => $batch,
        'proposals' => $batch['proposals'],
        'expired'   => mgk_is_proposal_expired( $batch ),
        'expiry'    => mgk_get_proposal_expiry( $batch ),
        'summary'   => $summary,
        'has_lead'  => ! empty( $batch['has_lead'] ),
        'is_real'   => ! empty( $batch['is_real'] ),
    ];
}

function mgk_proposal_shortcode_atts( $defaults, $atts ) {
    return shortcode_atts( array_merge( [
        'hidden' => '',
        'lead'   => '',
    ], $defaults ), $atts );
}

/**
 * True when proposals may be shown without a real lead: Elementor editor or
 * wp-admin (so the layout stays previewable while editing the page).
 */
function mgk_proposal_is_preview() {
    if ( function_exists( 'is_admin' ) && is_admin() ) {
        return true;
    }
    if ( class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::$instance ) {
        $ed = \Elementor\Plugin::$instance->editor ?? null;
        if ( $ed && method_exists( $ed, 'is_edit_mode' ) && $ed->is_edit_mode() ) {
            return true;
        }
        $pv = \Elementor\Plugin::$instance->preview ?? null;
        if ( $pv && method_exists( $pv, 'is_preview_mode' ) && $pv->is_preview_mode() ) {
            return true;
        }
    }
    return false;
}

/**
 * Content widgets that present proposal DATA. On a real front-end request with
 * no lead, these are gated to the empty state. Chrome (nav) and the S08 state
 * showcase widgets (state-*) are exempt.
 */
function mgk_proposal_is_data_part( $part ) {
    return in_array( $part, [ 'proposal-header', 'proposal-cards', 'rematch-banner', 'compare-drawer', 'proposals-page' ], true );
}

function mgk_render_proposal_part( $part, $atts = [] ) {
    if ( mgk_proposal_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }

    $vm = mgk_get_proposal_view_model( $atts );

    // GATE: S08 depends on a lead (from S07). On a real front-end hit with no
    // valid lead+token, never expose demo tutors — show the empty state once
    // (on the first data widget) and render nothing for the rest, so the page
    // doesn't repeat it per Elementor widget.
    if ( mgk_proposal_is_data_part( $part ) && empty( $vm['has_lead'] ) && ! mgk_proposal_is_preview() ) {
        static $empty_shown = false;
        if ( $empty_shown ) {
            return '';
        }
        $empty_shown = true;
        return mgk_render_part( 'template-parts/sections/proposals/empty-no-lead', [] );
    }

    return mgk_render_part( 'template-parts/sections/proposals/' . $part, array_merge( $vm, [ 'atts' => $atts ] ) );
}

add_shortcode( 'mgk_proposal_nav', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'utility'       => 'Logged-out - proposal via magic link - SG/EN',
        'logo_label'    => '[LOGO]',
        'browse_label'  => 'Browse Tutors',
        'subjects_label'=> 'Subjects',
        'how_label'     => 'How It Works',
        'pricing_label' => 'Pricing',
        'signin_label'  => 'Sign In',
    ], $atts );
    return mgk_render_proposal_part( 'nav', $atts );
} );

add_shortcode( 'mgk_proposal_header', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'heading'      => 'Your matched tutors',
        'expiry_label' => 'PROPOSALS EXPIRE IN',
        'expiry_note'  => 'FREE RE-SEND AFTER',
        'hide_heading' => '',
        'hide_summary' => '',
        'hide_expiry'  => '',
        'hide_expiry_label' => '',
        'hide_expiry_note'  => '',
    ], $atts );
    return mgk_render_proposal_part( 'proposal-header', $atts );
} );

add_shortcode( 'mgk_proposal_cards', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'compare_label'      => '+ Compare',
        'select_label'       => 'Select',
        'suggested_label'    => 'Suggested',
        'why_label'          => 'Why matched',
        'verified_label'     => 'Verified',
        'demo_label'         => 'Demo',
        'demo_empty_label'   => 'Demo coming soon',
        'hide_demo'          => '',
        'hide_trust'         => '',
        'hide_match_reason'  => '',
        'hide_suggested'     => '',
        'hide_verified_badge'=> '',
        'hide_compare'       => '',
        'hide_select'        => '',
        'hide_why_label'     => '',
        'hide_suggested_label' => '',
    ], $atts );
    return mgk_render_proposal_part( 'proposal-cards', $atts );
} );

add_shortcode( 'mgk_proposal_rematch', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'heading' => 'None quite right?',
        'body'    => 'GET A FRESH SET OF MATCHES - FREE, NO LIMIT ON FIRST RE-MATCH (BR-11)',
        'button'  => 'Request re-match (free)',
        'hide_heading' => '',
        'hide_body'    => '',
        'hide_button'  => '',
    ], $atts );
    return mgk_render_proposal_part( 'rematch-banner', $atts );
} );

add_shortcode( 'mgk_proposal_compare', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'heading' => 'Compare',
        'button'  => 'View comparison',
        'hide_heading' => '',
        'hide_button'  => '',
        'hide_table'   => '',
    ], $atts );
    return mgk_render_proposal_part( 'compare-drawer', $atts );
} );

add_shortcode( 'mgk_proposal_state_intro', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'heading'      => 'State slices',
        'nav'          => 'Expired · Re-match · Skeleton',
        'hide_heading' => '',
        'hide_nav'     => '',
    ], $atts );
    return mgk_render_proposal_part( 'state-intro', $atts );
} );

add_shortcode( 'mgk_proposal_state_expired', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'label'        => 'Proposed - expired (BR-11)',
        'icon'         => '⌛',
        'title'        => 'These proposals expired',
        'message'      => '48H WINDOW CLOSED. TUTOR AVAILABILITY MAY HAVE CHANGED.',
        'button'       => 'Re-send proposals (free)',
        'hide_label'   => '',
        'hide_icon'    => '',
        'hide_title'   => '',
        'hide_message' => '',
        'hide_button'  => '',
    ], $atts );
    return mgk_render_proposal_part( 'state-expired', $atts );
} );

add_shortcode( 'mgk_proposal_state_selected', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'label'       => 'Selected (card highlighted)',
        'tutor'       => 'Ms Lee',
        'status'      => 'Selected',
        'button'      => 'Continue to trial',
        'hide_label'  => '',
        'hide_tutor'  => '',
        'hide_status' => '',
        'hide_dot'    => '',
        'hide_button' => '',
    ], $atts );
    return mgk_render_proposal_part( 'state-selected', $atts );
} );

add_shortcode( 'mgk_proposal_state_rematch_requested', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'label'        => 'Re-match requested',
        'message'      => 'FINDING NEW MATCHES. YOU WILL GET A FRESH SET WITHIN 6H.',
        'timer'        => '05:58:00',
        'hide_label'   => '',
        'hide_message' => '',
        'hide_timer'   => '',
    ], $atts );
    return mgk_render_proposal_part( 'state-rematch-requested', $atts );
} );

add_shortcode( 'mgk_proposal_state_skeleton', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [
        'label'       => 'Loading skeleton',
        'lines'       => 4,
        'hide_label'  => '',
        'hide_avatar' => '',
        'hide_lines'  => '',
    ], $atts );
    return mgk_render_proposal_part( 'state-skeleton', $atts );
} );

add_shortcode( 'mgk_proposals', function ( $atts ) {
    $atts = mgk_proposal_shortcode_atts( [], $atts );
    if ( mgk_proposal_bool( $atts['hidden'] ?? '' ) ) {
        return '';
    }
    return mgk_render_proposal_part( 'proposals-page', $atts );
} );

/* ── Agency "Send Proposals" control (S26) on the Lead edit screen ─────────── */

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'mgk_send_proposals',
        'Send Proposals (S08)',
        'mgk_send_proposals_metabox',
        'mg_lead',
        'side',
        'high'
    );
} );

function mgk_send_proposals_metabox( $post ) {
    $lead_id   = (int) $post->ID;
    $proposals = mgk_get_proposals_for_lead( $lead_id );
    $count     = count( $proposals );
    $sent_at   = (int) get_post_meta( $lead_id, 'mgk_proposals_sent_at', true );
    $state     = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: '—';
    $token     = (string) get_post_meta( $lead_id, 'mgk_lead_token', true );
    $link      = $token ? add_query_arg( 'token', rawurlencode( $token ), home_url( '/tutor-proposals/' ) ) : '';
    $s07_link  = $token ? add_query_arg( 'mgk_lead', rawurlencode( $token ), home_url( '/request-match/' ) ) : '';

    echo '<p><strong>Attached proposals:</strong> ' . (int) $count . '</p>';
    echo '<p><strong>Lead state:</strong> ' . esc_html( $state ) . '</p>';
    if ( $sent_at ) {
        $exp = $sent_at + DAY_IN_SECONDS * 2;
        echo '<p><strong>Sent:</strong> ' . esc_html( date_i18n( 'Y-m-d H:i', $sent_at ) ) . '<br>';
        echo '<strong>Expires:</strong> ' . esc_html( date_i18n( 'Y-m-d H:i', $exp ) )
            . ( time() >= $exp ? ' <span style="color:#b32d2e;">(EXPIRED)</span>' : '' ) . '</p>';
    }
    // Test links so you can open each screen without typing URLs.
    if ( $s07_link ) {
        echo '<p><strong>S07 confirmation (test):</strong><br><a href="' . esc_url( $s07_link ) . '" target="_blank" rel="noopener">' . esc_html( $s07_link ) . '</a></p>';
    }
    if ( $link ) {
        echo '<p><strong>S08 magic link (test):</strong><br><a href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( $link ) . '</a></p>';
    }

    if ( $count < 1 ) {
        echo '<p style="color:#b32d2e;">Attach at least one <code>mg_proposal</code> (linking this lead to a tutor) before sending.</p>';
        return;
    }

    $url = wp_nonce_url(
        admin_url( 'admin-post.php?action=mgk_send_proposals&lead=' . $lead_id ),
        'mgk_send_proposals_' . $lead_id
    );
    $label = $sent_at ? 'Re-send proposals (resets 48h)' : 'Send proposals to parent';
    echo '<a href="' . esc_url( $url ) . '" class="button button-primary" style="width:100%;text-align:center;">' . esc_html( $label ) . '</a>';
    echo '<p class="description" style="margin-top:8px;">Stamps the send time, moves the lead to PROPOSED, and notifies the parent (WhatsApp + Email) with the magic link.</p>';
}

add_action( 'admin_post_mgk_send_proposals', function () {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Permission denied.' );
    }
    $lead_id = isset( $_GET['lead'] ) ? (int) $_GET['lead'] : 0;
    check_admin_referer( 'mgk_send_proposals_' . $lead_id );

    $result = mgk_send_proposals( $lead_id );

    $args = $result['ok']
        ? [ 'mgk_sent' => 1, 'mgk_count' => (int) $result['count'], 'mgk_wa' => rawurlencode( (string) $result['wa'] ), 'mgk_email' => $result['email'] ? 1 : 0 ]
        : [ 'mgk_sent' => 0, 'mgk_msg' => rawurlencode( (string) $result['error'] ) ];

    wp_safe_redirect( add_query_arg( $args, get_edit_post_link( $lead_id, 'raw' ) ) );
    exit;
} );

add_action( 'admin_notices', function () {
    if ( ! isset( $_GET['mgk_sent'] ) || get_current_screen()->id !== 'mg_lead' ) {
        return;
    }
    if ( $_GET['mgk_sent'] === '1' ) {
        $c  = isset( $_GET['mgk_count'] ) ? (int) $_GET['mgk_count'] : 0;
        $wa = isset( $_GET['mgk_wa'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_wa'] ) ) : '';
        $em = ! empty( $_GET['mgk_email'] ) ? 'sent' : 'not sent';
        printf(
            '<div class="notice notice-success is-dismissible"><p>Sent %d proposal(s). WhatsApp: <strong>%s</strong>. Email: <strong>%s</strong>.</p></div>',
            $c, esc_html( $wa ?: 'skipped' ), esc_html( $em )
        );
    } else {
        $msg = isset( $_GET['mgk_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_msg'] ) ) : 'Could not send.';
        printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $msg ) );
    }
} );

/* ── WP-Cron: hourly sweep to expire stale proposals (BR-11) ──────────────── */

add_action( 'mgk_cron_expire_proposals', 'mgk_expire_stale_proposals' );

add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'mgk_cron_expire_proposals' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'mgk_cron_expire_proposals' );
    }
} );

// Clean up the scheduled event if the theme is switched away.
add_action( 'switch_theme', function () {
    $ts = wp_next_scheduled( 'mgk_cron_expire_proposals' );
    if ( $ts ) {
        wp_unschedule_event( $ts, 'mgk_cron_expire_proposals' );
    }
} );
