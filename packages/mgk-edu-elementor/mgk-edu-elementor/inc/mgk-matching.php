<?php
/**
 * MGK — Admin-assist matching (suggest tutors for a lead).
 * ========================================================
 * The agency still owns the final match decision (quality control), but instead
 * of hunting for tutors by hand they get a RANKED shortlist on the Lead edit
 * screen and one-click "Add as proposal". Ranking is a transparent score over
 * subject / level / budget / area / rating / availability — no black box.
 *
 * Creating a proposal here writes the same mg_proposal shape the parent-facing
 * S08 reads (mgk_prop_lead_id / _tutor_id / _rank_order / _suggested_hourly_rate_sgd
 * / _match_reason / _status), so the existing "Send Proposals" button then works.
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Normalise a label for fuzzy comparison (maths→math, strip punctuation). */
function mgk_match_norm( $s ) {
    $s = strtolower( trim( (string) $s ) );
    $s = str_replace( [ 'mathematics', 'maths' ], 'math', $s );
    return trim( preg_replace( '/\s+/', ' ', preg_replace( '/[^a-z0-9]+/', ' ', $s ) ) );
}

/** True if a needle label fuzzy-matches any of the values. */
function mgk_match_any( $needle, $values ) {
    $needle = mgk_match_norm( $needle );
    if ( $needle === '' ) return false;
    foreach ( (array) $values as $v ) {
        $v = mgk_match_norm( $v );
        if ( $v !== '' && ( $v === $needle || strpos( $v, $needle ) !== false || strpos( $needle, $v ) !== false ) ) return true;
    }
    return false;
}

/**
 * Rank verified tutors for a lead. Returns up to $limit:
 *   [ id, name, slug, rate, rating, score, subject_match, level_match, why ]
 * Tutors already proposed for this lead are excluded.
 */
function mgk_match_rank_tutors_for_lead( $lead_id, $limit = 5 ) {
    $lead_id = (int) $lead_id;
    $subject = (string) get_post_meta( $lead_id, 'mgk_lead_subject', true );
    $level   = (string) get_post_meta( $lead_id, 'mgk_lead_level', true );
    $budget  = (int) ( get_post_meta( $lead_id, 'mgk_lead_budget_max', true ) ?: get_post_meta( $lead_id, 'mgk_lead_budget', true ) );
    $area    = (string) ( get_post_meta( $lead_id, 'mgk_lead_area', true ) ?: get_post_meta( $lead_id, 'mgk_lead_location', true ) );

    $existing = [];
    if ( function_exists( 'mgk_get_proposals_for_lead' ) ) {
        foreach ( (array) mgk_get_proposals_for_lead( $lead_id ) as $p ) $existing[] = (int) ( $p['id'] ?? 0 );
    }

    $q = new WP_Query( [
        'post_type' => 'mg_teacher', 'post_status' => 'publish', 'posts_per_page' => -1,
        'no_found_rows' => true, 'meta_query' => [ [ 'key' => 'mgk_is_verified', 'value' => '1' ] ],
    ] );

    $ranked = [];
    foreach ( $q->posts as $t ) {
        if ( in_array( $t->ID, $existing, true ) ) continue;
        $subs   = wp_get_post_terms( $t->ID, 'mgk_subject', [ 'fields' => 'names' ] );
        $lvls   = wp_get_post_terms( $t->ID, 'mgk_level',   [ 'fields' => 'names' ] );
        $rate   = (int) get_post_meta( $t->ID, 'mgk_rate_num', true );
        $rating = (float) get_post_meta( $t->ID, 'mgk_rating', true );
        $locs   = get_post_meta( $t->ID, 'mgk_locations', true );
        $locs   = is_array( $locs ) ? $locs : ( $locs ? [ $locs ] : [] );

        $score = 0.0; $why = [];
        $sub_match = $subject !== '' && mgk_match_any( $subject, $subs );
        if ( $sub_match ) { $score += 50; $why[] = $subject; }
        $lvl_match = $level !== '' && mgk_match_any( $level, $lvls );
        if ( $lvl_match ) { $score += 30; $why[] = $level; }
        if ( $budget > 0 && $rate > 0 ) {
            if ( $rate <= $budget ) { $score += 15; $why[] = 'in budget ($' . $rate . '/hr)'; }
            else { $score -= 10; $why[] = '$' . $rate . '/hr (above budget)'; }
        }
        if ( $area !== '' ) {
            foreach ( $locs as $loc ) {
                if ( stripos( (string) $loc, $area ) !== false || stripos( $area, (string) $loc ) !== false ) { $score += 8; $why[] = (string) $loc; break; }
            }
        }
        if ( get_post_meta( $t->ID, '_mgk_weekly_availability_json', true ) ) { $score += 8; }
        $score += min( 10, $rating * 2 );
        if ( $rating > 0 ) $why[] = $rating . '★';

        $ranked[] = [
            'id' => (int) $t->ID, 'name' => $t->post_title, 'slug' => $t->post_name,
            'rate' => $rate, 'rating' => $rating, 'score' => round( $score, 1 ),
            'subject_match' => $sub_match, 'level_match' => $lvl_match,
            'why' => implode( ' · ', array_slice( $why, 0, 4 ) ) ?: 'available tutor',
        ];
    }
    usort( $ranked, function ( $a, $b ) { return $b['score'] <=> $a['score']; } );
    return array_slice( $ranked, 0, (int) $limit );
}

/** Create an mg_proposal linking a tutor to a lead (admin-assist one-click). */
function mgk_match_create_proposal( $lead_id, $tutor_id ) {
    $lead_id = (int) $lead_id; $tutor_id = (int) $tutor_id;
    if ( ! $lead_id || get_post_type( $tutor_id ) !== 'mg_teacher' || ! post_type_exists( 'mg_proposal' ) ) {
        return new WP_Error( 'mgk_bad', 'Invalid lead or tutor.' );
    }
    // Don't duplicate.
    $dupe = get_posts( [
        'post_type' => 'mg_proposal', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids',
        'meta_query' => [ 'relation' => 'AND',
            [ 'key' => 'mgk_prop_lead_id', 'value' => $lead_id ],
            [ 'key' => 'mgk_prop_tutor_id', 'value' => $tutor_id ],
        ],
    ] );
    if ( $dupe ) return (int) $dupe[0];

    // Reason from the ranker (so the parent-facing "why" is meaningful).
    $why = ''; foreach ( mgk_match_rank_tutors_for_lead( $lead_id, 50 ) as $r ) { if ( $r['id'] === $tutor_id ) { $why = $r['why']; break; } }
    $rank = count( (array) mgk_get_proposals_for_lead( $lead_id ) ) + 1;

    $pid = wp_insert_post( [
        'post_type' => 'mg_proposal', 'post_status' => 'publish',
        'post_title' => 'Proposal — ' . get_the_title( $tutor_id ) . ' → Lead #' . $lead_id,
    ], true );
    if ( is_wp_error( $pid ) ) return $pid;

    update_post_meta( $pid, 'mgk_prop_lead_id', $lead_id );
    update_post_meta( $pid, 'mgk_prop_tutor_id', $tutor_id );
    update_post_meta( $pid, 'mgk_prop_rank_order', $rank );
    update_post_meta( $pid, 'mgk_prop_suggested_hourly_rate_sgd', (int) get_post_meta( $tutor_id, 'mgk_rate_num', true ) );
    update_post_meta( $pid, 'mgk_prop_match_reason', $why );
    update_post_meta( $pid, 'mgk_prop_status', 'ATTACHED' );
    return (int) $pid;
}

/* ── Meta-box: suggested matches on the Lead edit screen ────────────────── */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'mgk_match_suggest', 'Suggested matches (auto-rank)', 'mgk_match_suggest_metabox', 'mg_lead', 'normal', 'high' );
} );

function mgk_match_suggest_metabox( $post ) {
    $lead_id = (int) $post->ID;
    $subject = get_post_meta( $lead_id, 'mgk_lead_subject', true );
    $level   = get_post_meta( $lead_id, 'mgk_lead_level', true );
    echo '<p class="description">Ranked by subject/level/budget/area/rating/availability. You choose who to propose.</p>';
    echo '<p><strong>Need:</strong> ' . esc_html( trim( ( $subject ?: '—' ) . ' · ' . ( $level ?: '—' ) ) ) . '</p>';

    $sugg = mgk_match_rank_tutors_for_lead( $lead_id, 5 );
    if ( ! $sugg ) { echo '<p>No verified tutors to suggest (all may already be proposed).</p>'; return; }

    echo '<table class="widefat striped"><thead><tr><th>Tutor</th><th>Match</th><th>Score</th><th></th></tr></thead><tbody>';
    foreach ( $sugg as $s ) {
        $url = wp_nonce_url(
            admin_url( 'admin-post.php?action=mgk_add_proposal&lead=' . $lead_id . '&tutor=' . $s['id'] ),
            'mgk_add_proposal_' . $lead_id . '_' . $s['id']
        );
        $badges = ( $s['subject_match'] ? '<span style="color:#1a7f37">✓subject</span> ' : '<span style="color:#b32d2e">✗subject</span> ' )
                . ( $s['level_match'] ? '<span style="color:#1a7f37">✓level</span>' : '<span style="color:#646970">~level</span>' );
        echo '<tr>';
        echo '<td><strong>' . esc_html( $s['name'] ) . '</strong><br><span class="description">' . esc_html( $s['why'] ) . '</span></td>';
        echo '<td>' . $badges . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput
        echo '<td>' . esc_html( (string) $s['score'] ) . '</td>';
        echo '<td><a class="button button-primary" href="' . esc_url( $url ) . '">+ Propose</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

add_action( 'admin_post_mgk_add_proposal', function () {
    if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Permission denied.' );
    $lead_id  = isset( $_GET['lead'] ) ? (int) $_GET['lead'] : 0;
    $tutor_id = isset( $_GET['tutor'] ) ? (int) $_GET['tutor'] : 0;
    check_admin_referer( 'mgk_add_proposal_' . $lead_id . '_' . $tutor_id );

    $res = mgk_match_create_proposal( $lead_id, $tutor_id );
    $args = is_wp_error( $res )
        ? [ 'mgk_prop_added' => 0, 'mgk_prop_msg' => rawurlencode( $res->get_error_message() ) ]
        : [ 'mgk_prop_added' => 1 ];
    wp_safe_redirect( add_query_arg( $args, get_edit_post_link( $lead_id, 'raw' ) ) );
    exit;
} );

add_action( 'admin_notices', function () {
    if ( ! isset( $_GET['mgk_prop_added'] ) ) return;
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'mg_lead' ) return;
    if ( $_GET['mgk_prop_added'] === '1' ) {
        echo '<div class="notice notice-success is-dismissible"><p>Tutor added as a proposal. Use “Send Proposals” when ready.</p></div>';
    } else {
        $m = isset( $_GET['mgk_prop_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_prop_msg'] ) ) : 'Could not add proposal.';
        printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $m ) );
    }
} );
