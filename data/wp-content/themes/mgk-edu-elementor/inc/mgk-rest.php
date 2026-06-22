<?php
/**
 * MGK REST API v1.
 *
 * Architecture: WP REST + WP Heartbeat polling (MVP real-time).
 * Base: /wp-json/mgk/v1/
 *
 * Batch 1 (live): tutors, tutors/{slug}, tutors/{slug}/slots
 * Batch 2+  (stub — 501): leads, slots hold/release, bookings status
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Route registration ───────────────────────────────────── */

add_action( 'rest_api_init', function () {

    $ns = 'mgk/v1';

    /* GET /tutors — public listing with filter params */
    register_rest_route( $ns, '/tutors', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mgk_rest_get_tutors',
        'permission_callback' => '__return_true',
        'args'                => [
            'subject'  => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ],
            'level'    => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ],
            'area'     => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ],
            'budget'   => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ],
            'sort'     => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_key',        'default' => 'best-match' ],
            'page'     => [ 'type' => 'integer', 'minimum' => 1, 'default' => 1 ],
            'per_page' => [ 'type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 12 ],
        ],
    ] );

    /* GET /tutors/{slug} — single tutor profile */
    register_rest_route( $ns, '/tutors/(?P<slug>[a-z0-9\-]+)', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mgk_rest_get_tutor',
        'permission_callback' => '__return_true',
        'args'                => [
            'slug' => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_title' ],
        ],
    ] );

    /* GET /tutors/{slug}/slots — available time slots */
    register_rest_route( $ns, '/tutors/(?P<slug>[a-z0-9\-]+)/slots', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mgk_rest_get_slots',
        'permission_callback' => '__return_true',
        'args'                => [
            'slug' => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_title' ],
        ],
    ] );

    /* POST /leads — capture lead (Batch 2) */
    register_rest_route( $ns, '/leads', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'mgk_rest_create_lead',
        'permission_callback' => '__return_true',
    ] );

    /* POST /slots/{id}/hold — hold a slot (Batch 2) */
    register_rest_route( $ns, '/slots/(?P<id>[a-z0-9\-_]+)/hold', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'mgk_rest_hold_slot',
        'permission_callback' => '__return_true',
        'args'                => [
            'id' => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );

    /* DELETE /slots/{id}/hold — release a held slot (Batch 2) */
    register_rest_route( $ns, '/slots/(?P<id>[a-z0-9\-_]+)/hold', [
        'methods'             => WP_REST_Server::DELETABLE,
        'callback'            => 'mgk_rest_release_slot',
        'permission_callback' => '__return_true',
        'args'                => [
            'id' => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );

    /* GET /bookings/{id}/status — booking status poll (Batch 2) */
    register_rest_route( $ns, '/bookings/(?P<id>\d+)/status', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mgk_rest_booking_status',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'id' => [ 'type' => 'integer', 'required' => true, 'minimum' => 1 ],
        ],
    ] );

    // ── Subject catalog (SRS §11) ──
    register_rest_route( $ns, '/subjects', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mgk_rest_get_subjects',
        'permission_callback' => '__return_true',
    ] );

    // ── Pricing estimate (SRS §11) ──
    register_rest_route( $ns, '/pricing/estimate', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'mgk_rest_pricing_estimate',
        'permission_callback' => '__return_true',
    ] );

    // ── Tracking sink (SRS §11/§13) — accepts an event, no-ops safely ──
    register_rest_route( $ns, '/track', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'mgk_rest_track',
        'permission_callback' => '__return_true',
    ] );

} );

/** GET /subjects — catalog + per-subject tutor counts. */
function mgk_rest_get_subjects( WP_REST_Request $req ) {
    $catalog = function_exists( 'mgk_get_subject_catalog' ) ? mgk_get_subject_catalog() : [];
    $subjects = function_exists( 'mgk_get_subjects' ) ? mgk_get_subjects() : [];
    return new WP_REST_Response( [ 'subjects' => $subjects, 'catalog' => $catalog ], 200 );
}

/**
 * GET /pricing/estimate — server-authoritative estimate via the unified quote
 * engine. Params: rate (hourly, SGD) or tutor (slug); item (trial|package_8|
 * package_16); voucher. Falls back to a default rate when none supplied.
 */
function mgk_rest_pricing_estimate( WP_REST_Request $req ) {
    if ( ! function_exists( 'mgk_quote' ) ) {
        return new WP_REST_Response( [ 'error' => 'unavailable' ], 501 );
    }
    $rate = (int) $req->get_param( 'rate' );
    $slug = sanitize_title( (string) $req->get_param( 'tutor' ) );
    if ( $rate <= 0 && $slug && function_exists( 'mgk_package_resolve_tutor' ) ) {
        $t = mgk_package_resolve_tutor( $slug );
        if ( $t ) $rate = (int) $t['rate_num'];
    }
    $item = (string) $req->get_param( 'item' );
    if ( ! in_array( $item, [ 'trial', 'package_8', 'package_16' ], true ) ) $item = 'trial';

    $q = mgk_quote( [
        'item_type'    => $item,
        'rate_num'     => $rate > 0 ? $rate : 65,
        'apply_loyalty'=> false, // public estimate: no per-account loyalty
        'voucher_code' => sanitize_text_field( (string) $req->get_param( 'voucher' ) ),
    ] );
    return new WP_REST_Response( [
        'item'        => $item,
        'rate'        => $rate > 0 ? $rate : 65,
        'base'        => $q['base'],
        'total'       => $q['total'],
        'total_str'   => $q['total_str'],
        'rows'        => $q['rows'],
        'gst_note'    => $q['gst_note'],
        'gst_amount'  => $q['gst_amount'],
        'currency'    => $q['currency'],
    ], 200 );
}

/** POST /track — record an analytics event server-side (safe no-op sink). */
function mgk_rest_track( WP_REST_Request $req ) {
    $body = $req->get_json_params();
    if ( ! is_array( $body ) ) $body = $req->get_params();
    $event = sanitize_key( (string) ( $body['event'] ?? '' ) );
    if ( $event === '' ) {
        return new WP_REST_Response( [ 'ok' => false, 'message' => 'event required' ], 422 );
    }
    $props = ( isset( $body['props'] ) && is_array( $body['props'] ) ) ? $body['props'] : [];
    if ( function_exists( 'mgk_track_event' ) ) {
        mgk_track_event( $event, $props );
    }
    do_action( 'mgk_rest_tracked', $event, $props );
    return new WP_REST_Response( [ 'ok' => true ], 200 );
}

/* ── Live handlers ────────────────────────────────────────── */

function mgk_rest_get_tutors( WP_REST_Request $req ) {
    $filters = [
        'subject'  => $req->get_param( 'subject' ),
        'level'    => $req->get_param( 'level' ),
        'area'     => $req->get_param( 'area' ),
        'budget'   => $req->get_param( 'budget' ),
        'sort'     => $req->get_param( 'sort' ),
        'page'     => (int) $req->get_param( 'page' ),
    ];

    $per_page = (int) $req->get_param( 'per_page' );
    $tutors   = function_exists( 'mgk_filter_tutors' ) ? mgk_filter_tutors( $filters ) : [];
    $total    = count( $tutors );
    $offset   = ( $filters['page'] - 1 ) * $per_page;
    $page_data = array_slice( $tutors, $offset, $per_page );

    return rest_ensure_response( [
        'total'    => $total,
        'pages'    => (int) ceil( $total / $per_page ),
        'page'     => $filters['page'],
        'per_page' => $per_page,
        'tutors'   => array_values( $page_data ),
    ] );
}

function mgk_rest_get_tutor( WP_REST_Request $req ) {
    $slug  = $req->get_param( 'slug' );
    $tutor = function_exists( 'mgk_profile_tutor' ) ? mgk_profile_tutor( $slug ) : null;

    if ( ! $tutor ) {
        return new WP_Error( 'mgk_not_found', 'Tutor not found.', [ 'status' => 404 ] );
    }

    return rest_ensure_response( $tutor );
}

function mgk_rest_get_slots( WP_REST_Request $req ) {
    $slug  = $req->get_param( 'slug' );

    if ( function_exists( 'mgk_get_enriched_slots' ) ) {
        $slots = mgk_get_enriched_slots( $slug );
    } elseif ( function_exists( 'mgk_get_available_slots' ) ) {
        $slots = mgk_get_available_slots( $slug );
    } else {
        $slots = [];
    }

    return rest_ensure_response( [ 'slots' => $slots ] );
}

/* ── Batch 2 live handlers ───────────────────────────────── */

function mgk_rest_create_lead( WP_REST_Request $req ) {
    $body = $req->get_json_params() ?: (array) $req->get_body_params();

    if ( ! function_exists( 'mgk_create_lead' ) ) {
        return new WP_Error( 'mgk_not_ready', 'Lead handler not loaded.', [ 'status' => 503 ] );
    }

    $result = mgk_create_lead( $body );

    if ( is_wp_error( $result ) ) {
        $data = $result->get_error_data() ?: [];
        $status = is_array( $data ) && ! empty( $data['status'] ) ? (int) $data['status'] : 422;
        return new WP_REST_Response( [
            'code'    => $result->get_error_code(),
            'message' => $result->get_error_message(),
            'data'    => $result->get_error_data(),
        ], $status );
    }

    return rest_ensure_response( $result );
}

function mgk_rest_hold_slot( WP_REST_Request $req ) {
    $slot_id = $req->get_param( 'id' );
    $body    = $req->get_json_params() ?: [];
    $lead_id = isset( $body['lead_id'] ) ? (int) $body['lead_id'] : 0;

    $result = function_exists( 'mgk_hold_slot' ) ? mgk_hold_slot( $slot_id, $lead_id ) : null;

    if ( ! $result ) {
        return new WP_Error( 'mgk_not_ready', 'Slot hold handler not loaded.', [ 'status' => 503 ] );
    }

    if ( is_wp_error( $result ) ) {
        $data   = $result->get_error_data() ?: [];
        $status = is_array( $data ) && ! empty( $data['status'] ) ? (int) $data['status'] : 409;
        return new WP_REST_Response( [
            'code'    => $result->get_error_code(),
            'message' => $result->get_error_message(),
        ], $status );
    }

    return rest_ensure_response( $result );
}

function mgk_rest_release_slot( WP_REST_Request $req ) {
    $slot_id = $req->get_param( 'id' );
    $body    = $req->get_json_params() ?: [];
    $lead_id = isset( $body['lead_id'] ) ? (int) $body['lead_id'] : 0;

    $result = function_exists( 'mgk_release_slot' ) ? mgk_release_slot( $slot_id, $lead_id ) : null;

    if ( ! $result ) {
        return new WP_Error( 'mgk_not_ready', 'Slot release handler not loaded.', [ 'status' => 503 ] );
    }

    if ( is_wp_error( $result ) ) {
        $data   = $result->get_error_data() ?: [];
        $status = is_array( $data ) && ! empty( $data['status'] ) ? (int) $data['status'] : 409;
        return new WP_REST_Response( [
            'code'    => $result->get_error_code(),
            'message' => $result->get_error_message(),
        ], $status );
    }

    return rest_ensure_response( $result );
}

function mgk_rest_booking_status( WP_REST_Request $req ) {
    $booking_id = (int) $req->get_param( 'id' );

    if ( ! post_type_exists( 'mg_booking' ) ) {
        return new WP_Error( 'mgk_not_ready', 'Booking CPT not registered.', [ 'status' => 503 ] );
    }

    $booking = get_post( $booking_id );
    if ( ! $booking || $booking->post_type !== 'mg_booking' ) {
        return new WP_Error( 'mgk_not_found', 'Booking not found.', [ 'status' => 404 ] );
    }

    $author_id = (int) $booking->post_author;
    if ( $author_id && get_current_user_id() !== $author_id && ! current_user_can( 'manage_options' ) ) {
        return new WP_Error( 'mgk_forbidden', 'You do not have access to this booking.', [ 'status' => 403 ] );
    }

    return rest_ensure_response( [
        'id'       => $booking_id,
        'status'   => get_post_meta( $booking_id, 'mgk_lead_state', true ) ?: 'pending',
        'wc_order' => (int) get_post_meta( $booking_id, 'mgk_wc_order_id', true ) ?: null,
    ] );
}

/* ── WP Heartbeat — booking status polling (MVP real-time) ── */

add_filter( 'heartbeat_received', function ( $response, $data ) {
    if ( empty( $data['mgk_booking_id'] ) ) {
        return $response;
    }

    $booking_id = (int) $data['mgk_booking_id'];
    if ( ! $booking_id ) {
        return $response;
    }

    $booking = get_post( $booking_id );
    if ( ! $booking || $booking->post_type !== 'mg_booking' ) {
        $response['mgk_booking'] = [ 'status' => 'not_found' ];
        return $response;
    }

    $state    = get_post_meta( $booking_id, 'mgk_lead_state', true ) ?: 'pending';
    $wc_order = (int) get_post_meta( $booking_id, 'mgk_wc_order_id', true );

    $response['mgk_booking'] = [
        'id'       => $booking_id,
        'status'   => $state,
        'wc_order' => $wc_order ?: null,
    ];

    return $response;
}, 10, 2 );
