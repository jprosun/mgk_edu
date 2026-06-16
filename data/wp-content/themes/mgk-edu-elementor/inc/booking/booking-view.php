<?php
/**
 * Authoritative booking VIEW — the "read real data" layer.
 * =======================================================
 * Resolves a real wp_mgk_bookings row into the fully-linked display data used by
 * S12 (confirmation) and the parent dashboard. Instead of reconstructing the
 * tutor / slot / price from the session-y S11 pay view (lead_token + ?tutor=),
 * everything here comes from the PERSISTED booking row + its linked tutor
 * (mg_teacher), parent (wp_user) and payment row.
 *
 * One booking row → one normalized array. Used everywhere a booking is shown so
 * S12, the email and the dashboard all agree on the same source of truth.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tutor contact (phone / email) from tutor CPT meta. Empty strings when not
 * configured — callers decide on a masked placeholder.
 *
 * @return array{phone:string,email:string,has_phone:bool,has_email:bool}
 */
function mgk_tutor_contact( $tutor_id ) {
    $tutor_id = (int) $tutor_id;
    $phone = $tutor_id ? (string) get_post_meta( $tutor_id, 'mgk_tutor_phone', true ) : '';
    $email = $tutor_id ? (string) get_post_meta( $tutor_id, 'mgk_tutor_email', true ) : '';
    return [
        'phone'     => $phone,
        'email'     => $email,
        'has_phone' => $phone !== '',
        'has_email' => $email !== '',
    ];
}

/** Singapore display timezone (reuse the engine's tz when present). */
function mgk_view_tz() {
    if ( function_exists( 'mgk_booking_tz' ) ) {
        $tz = mgk_booking_tz();
        if ( $tz instanceof DateTimeZone ) return $tz;
    }
    try { return new DateTimeZone( 'Asia/Singapore' ); }
    catch ( Exception $e ) { return new DateTimeZone( 'UTC' ); }
}

/** Payment method label for a booking, from the latest payment row. */
function mgk_booking_payment_method( $booking_id ) {
    global $wpdb;
    $table = function_exists( 'mgk_booking_table' ) ? mgk_booking_table( 'payments' ) : '';
    if ( ! $table || ! $booking_id ) return 'PayNow';
    $prov = $wpdb->get_var( $wpdb->prepare(
        "SELECT provider FROM {$table} WHERE booking_id = %d ORDER BY id DESC LIMIT 1",
        (int) $booking_id
    ) );
    if ( ! $prov ) return 'PayNow';
    $map = [ 'STRIPE' => 'Card', 'PAYNOW' => 'PayNow', 'MOCK' => 'PayNow' ];
    $key = strtoupper( (string) $prov );
    return $map[ $key ] ?? ucfirst( strtolower( $key ) );
}

/**
 * Zoom / meeting link for a booking. Real value would be a stored meeting URL
 * (Zoom API — later phase); until then a stable derived placeholder so the link
 * is consistent for a given booking. STUB — flagged for the integrations pass.
 */
function mgk_booking_zoom_url( $row ) {
    $code = is_array( $row ) ? (string) ( $row['booking_code'] ?? '' ) : '';
    $tail = preg_match( '/([0-9]{3,})/', $code, $m ) ? $m[1] : '8842';
    return 'https://zoom.us/j/' . $tail;
}

/**
 * Resolve a booking (id | booking_code | row array) into the normalized real
 * view. Returns [ 'found' => false ] when the row can't be resolved.
 *
 * @return array
 */
function mgk_booking_view( $booking ) {
    // ── Resolve to a row array ──
    $row = null;
    if ( is_array( $booking ) ) {
        $row = $booking;
    } elseif ( is_numeric( $booking ) && function_exists( 'mgk_get_booking_row' ) ) {
        $row = mgk_get_booking_row( (int) $booking );
    } elseif ( is_string( $booking ) && $booking !== '' && function_exists( 'mgk_get_booking_by_code' ) ) {
        $row = mgk_get_booking_by_code( $booking );
    }
    if ( ! is_array( $row ) || empty( $row['id'] ) ) {
        return [ 'found' => false ];
    }

    // ── Tutor (mg_teacher) ──
    $tutor_id   = (int) ( $row['tutor_post_id'] ?? 0 );
    $tutor_post = $tutor_id ? get_post( $tutor_id ) : null;
    $contact    = mgk_tutor_contact( $tutor_id );
    $tutor = [
        'id'    => $tutor_id,
        'name'  => $tutor_post ? $tutor_post->post_title : 'Your tutor',
        'slug'  => $tutor_post ? $tutor_post->post_name : '',
        'photo' => ( $tutor_id && has_post_thumbnail( $tutor_id ) ) ? get_the_post_thumbnail_url( $tutor_id, 'medium' ) : '',
        'phone' => $contact['phone'],
        'email' => $contact['email'],
        'rate'  => $tutor_id ? (string) get_post_meta( $tutor_id, 'mgk_rate_num', true ) : '',
    ];

    // ── Parent (wp_user, keyed by email) ──
    $parent = [ 'user_id' => 0, 'email' => '', 'name' => '' ];
    $puid = (int) ( $row['parent_user_id'] ?? 0 );
    if ( $puid ) {
        $u = get_user_by( 'id', $puid );
        if ( $u ) {
            $full = (string) get_user_meta( $puid, 'mgk_parent_full_name', true );
            $parent = [
                'user_id' => $puid,
                'email'   => $u->user_email,
                'name'    => $full ?: $u->display_name,
            ];
        }
    }
    // Not yet claimed → fall back to the lead's contact.
    if ( $parent['email'] === '' && ! empty( $row['lead_id'] ) && function_exists( 'mgk_lead_contact' ) ) {
        $lc = mgk_lead_contact( (int) $row['lead_id'] );
        $parent['email'] = (string) ( $lc['email'] ?? '' );
        if ( $parent['name'] === '' ) $parent['name'] = (string) ( $lc['name'] ?? '' );
    }

    // ── Child / subject (carried on the row) ──
    $student = (string) ( $row['student_name'] ?? '' );
    $subject = (string) ( $row['subject'] ?? '' );

    // ── Slot times (UTC → SGT) ──
    $tz = mgk_view_tz();
    $start_local = $end_local = null;
    $date_label = $time_label = '';
    $dur_h = '1.5';
    if ( ! empty( $row['start_at_utc'] ) ) {
        try {
            $start_local = new DateTime( $row['start_at_utc'] . ' UTC' );
            $start_local->setTimezone( $tz );
            $date_label = $start_local->format( 'D j M Y' );  // Wed 24 Jun 2026
            $time_label = $start_local->format( 'g:i A' );    // 4:00 PM
        } catch ( Exception $e ) {}
    }
    if ( $start_local && ! empty( $row['end_at_utc'] ) ) {
        try {
            $end_local = new DateTime( $row['end_at_utc'] . ' UTC' );
            $end_local->setTimezone( $tz );
            $time_label .= '–' . $end_local->format( 'g:i A' );
            $mins = ( $end_local->getTimestamp() - $start_local->getTimestamp() ) / 60;
            if ( $mins > 0 ) $dur_h = rtrim( rtrim( number_format( $mins / 60, 1 ), '0' ), '.' );
        } catch ( Exception $e ) {}
    }

    // ── Amount ──
    $amount_num = (float) ( $row['price_amount'] ?? 0 );
    $currency   = (string) ( $row['currency'] ?? 'SGD' );

    $status = (string) ( $row['status'] ?? '' );

    return [
        'found'          => true,
        'id'             => (int) $row['id'],
        'code'           => (string) ( $row['booking_code'] ?? '' ),
        'status'         => $status,
        'payment_status' => (string) ( $row['payment_status'] ?? '' ),
        'is_paid'        => in_array( $status, [ 'CONFIRMED', 'COMPLETED' ], true ),
        'lesson_type'    => (string) ( $row['lesson_type'] ?? 'TRIAL' ),
        'tutor'          => $tutor,
        'parent'         => $parent,
        'student_name'   => $student,
        'subject'        => $subject,
        'subject_level'  => $subject !== '' ? $subject : 'Trial lesson',
        'slot'           => [
            'start_utc'  => (string) ( $row['start_at_utc'] ?? '' ),
            'end_utc'    => (string) ( $row['end_at_utc'] ?? '' ),
            'date_label' => $date_label,
            'time_label' => $time_label,
            'datetime'   => trim( $date_label . ( $time_label ? ' · ' . $time_label : '' ) ),
            'duration_h' => $dur_h,
            'tz'         => $tz->getName(),
        ],
        'format'         => 'Online (Zoom)', // in-person carries a location in a later phase
        'amount_num'     => $amount_num,
        'currency'       => $currency,
        'amount_str'     => '$' . number_format( $amount_num, 2 ),
        'method'         => mgk_booking_payment_method( (int) $row['id'] ),
        'zoom'           => mgk_booking_zoom_url( $row ),
        'row'            => $row,
    ];
}
