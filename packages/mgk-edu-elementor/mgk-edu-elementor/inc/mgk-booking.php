<?php
/**
 * MGK Booking flow — Batch 2 (S07-S12).
 *
 * Slot ID format: {teacher_post_id}-{day_lower}-{time_slug}
 * e.g. "42-mon-9am-10am"
 *
 * Hold mechanism: WP transients (mgk_hold_{slot_id}, TTL 600s).
 * Lead → Booking link: written on mgk_booking_created action.
 *
 * Shortcodes registered:
 *   [mgk_request_form]         S07 — tuition request form
 *   [mgk_proposals]            S08 — matched tutor proposals
 *   [mgk_slot_picker]          S10 — time slot picker
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Slot ID helpers ─────────────────────────────────────── */

function mgk_slot_id( $teacher_post_id, $day, $time ) {
    $day_part  = sanitize_key( strtolower( (string) $day ) );
    $time_part = sanitize_key( str_replace( [ ' ', ':' ], '-', strtolower( (string) $time ) ) );
    return (int) $teacher_post_id . '-' . $day_part . '-' . $time_part;
}

/**
 * Returns ['teacher_id' => int, 'day' => string, 'time' => string] or null.
 */
function mgk_decode_slot_id( $slot_id ) {
    if ( ! preg_match( '/^(\d+)-([a-z]+)-(.+)$/', sanitize_text_field( (string) $slot_id ), $m ) ) {
        return null;
    }
    return [
        'teacher_id' => (int) $m[1],
        'day'        => $m[2],
        'time'       => $m[3],
    ];
}

/* ── Slot availability ───────────────────────────────────── */

function mgk_is_slot_held( $slot_id ) {
    return (bool) get_transient( 'mgk_hold_' . sanitize_key( (string) $slot_id ) );
}

function mgk_is_slot_booked( $slot_id ) {
    $existing = get_posts( [
        'post_type'      => 'mg_booking',
        'post_status'    => [ 'publish' ],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            [ 'key' => 'mgk_slot_id', 'value' => sanitize_text_field( $slot_id ), 'compare' => '=' ],
        ],
    ] );
    return ! empty( $existing );
}

function mgk_slot_status( $slot_id ) {
    if ( mgk_is_slot_booked( $slot_id ) ) return 'booked';
    if ( mgk_is_slot_held( $slot_id ) )   return 'held';
    return 'available';
}

/**
 * Returns availability as a flat list with slot IDs and status.
 * Used by REST /tutors/{slug}/slots and the slot picker shortcode.
 */
function mgk_get_enriched_slots( $tutor_slug ) {
    $tutor = function_exists( 'mgk_profile_tutor' ) ? mgk_profile_tutor( $tutor_slug ) : null;
    if ( ! $tutor || empty( $tutor['id'] ) ) {
        return [];
    }

    $teacher_id = (int) $tutor['id'];
    $avail      = $tutor['availability'] ?? [];
    $flat       = [];

    foreach ( $avail as $day_label => $slots ) {
        // day_label = "Mon 2" → extract abbreviation "Mon"
        $day_abbrev = trim( (string) preg_replace( '/\s+\d+$/', '', (string) $day_label ) );
        foreach ( (array) $slots as $time ) {
            $time = trim( (string) $time );
            if ( ! $time ) continue;
            $id     = mgk_slot_id( $teacher_id, $day_abbrev, $time );
            $flat[] = [
                'id'        => $id,
                'day'       => $day_abbrev,
                'day_label' => $day_label,
                'time'      => $time,
                'status'    => mgk_slot_status( $id ),
            ];
        }
    }

    return $flat;
}

/* ── Lead creation (real DB) ─────────────────────────────── */

function mgk_booking_create_lead( $payload ) {
    if ( ! post_type_exists( 'mg_lead' ) ) {
        return new WP_Error( 'mgk_cpt_missing', 'Lead CPT not registered.', [ 'status' => 503 ] );
    }

    $clean = [];
    foreach ( (array) $payload as $key => $value ) {
        if ( is_scalar( $value ) ) {
            $clean[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
        }
    }

    $token   = wp_generate_password( 20, false, false );
    $subject = $clean['subject'] ?? '';
    $parent  = $clean['parent_name'] ?? 'Parent';

    $lead_id = wp_insert_post( [
        'post_type'   => 'mg_lead',
        'post_title'  => 'Lead — ' . $parent . ( $subject ? ' — ' . $subject : '' ),
        'post_status' => 'publish',
        'post_author' => get_current_user_id() ?: 0,
    ], true );

    if ( is_wp_error( $lead_id ) ) {
        return $lead_id;
    }

    update_post_meta( $lead_id, 'mgk_lead_state', MGK_LEAD_CAPTURED );
    update_post_meta( $lead_id, 'mgk_lead_token', $token );

    foreach ( $clean as $key => $value ) {
        update_post_meta( $lead_id, 'mgk_lead_' . $key, $value );
    }

    do_action( 'mgk_lead_captured', $lead_id, $clean );

    return [
        'id'     => $lead_id,
        'token'  => $token,
        'status' => MGK_LEAD_CAPTURED,
    ];
}

/* ── Slot hold / release ─────────────────────────────────── */

function mgk_booking_hold_slot( $slot_id, $lead_id = 0 ) {
    $slot_id  = sanitize_text_field( (string) $slot_id );
    $lead_id  = (int) $lead_id;
    $tkey     = 'mgk_hold_' . sanitize_key( $slot_id );

    if ( ! mgk_decode_slot_id( $slot_id ) ) {
        return new WP_Error( 'mgk_invalid_slot', 'Invalid slot ID.', [ 'status' => 400 ] );
    }

    if ( mgk_is_slot_booked( $slot_id ) ) {
        return new WP_Error( 'mgk_slot_booked', 'This slot already has a confirmed booking.', [ 'status' => 409 ] );
    }

    $existing = get_transient( $tkey );
    if ( $existing ) {
        if ( $lead_id && ! empty( $existing['lead_id'] ) && (int) $existing['lead_id'] === $lead_id ) {
            // Extend own hold
            set_transient( $tkey, [ 'lead_id' => $lead_id, 'slot_id' => $slot_id ], 600 );
            return [ 'slot_id' => $slot_id, 'lead_id' => $lead_id, 'status' => 'held', 'expires_in' => 600 ];
        }
        return new WP_Error( 'mgk_slot_held', 'This slot is currently held by another user.', [ 'status' => 409 ] );
    }

    set_transient( $tkey, [ 'lead_id' => $lead_id, 'slot_id' => $slot_id ], 600 );

    if ( $lead_id && post_type_exists( 'mg_lead' ) ) {
        $decoded = mgk_decode_slot_id( $slot_id );
        update_post_meta( $lead_id, 'mgk_slot_id',         $slot_id );
        update_post_meta( $lead_id, 'mgk_slot_teacher_id', $decoded['teacher_id'] ?? 0 );
        update_post_meta( $lead_id, 'mgk_slot_day',        $decoded['day'] ?? '' );
        update_post_meta( $lead_id, 'mgk_slot_time',       $decoded['time'] ?? '' );

        if ( function_exists( 'mgk_lead_transition' ) && function_exists( 'mgk_lead_can_transition' ) ) {
            $current = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: MGK_LEAD_CAPTURED;
            if ( mgk_lead_can_transition( $current, MGK_LEAD_SLOT_HELD ) ) {
                mgk_lead_transition( $lead_id, MGK_LEAD_SLOT_HELD );
            }
        }
    }

    return [ 'slot_id' => $slot_id, 'lead_id' => $lead_id, 'status' => 'held', 'expires_in' => 600 ];
}

function mgk_booking_release_slot( $slot_id, $lead_id = 0 ) {
    $slot_id = sanitize_text_field( (string) $slot_id );
    $tkey    = 'mgk_hold_' . sanitize_key( $slot_id );
    $hold    = get_transient( $tkey );

    if ( $hold && $lead_id && ! empty( $hold['lead_id'] ) && (int) $hold['lead_id'] !== (int) $lead_id ) {
        return new WP_Error( 'mgk_not_your_hold', 'You do not hold this slot.', [ 'status' => 403 ] );
    }

    delete_transient( $tkey );
    return [ 'slot_id' => $slot_id, 'status' => 'released' ];
}

/* ── WC booking product ──────────────────────────────────── */

function mgk_ensure_booking_product( array $tutor, $price = 0 ) {
    if ( ! function_exists( 'mgk_woo_available' ) || ! mgk_woo_available() || empty( $tutor['slug'] ) ) {
        return 0;
    }

    $sku   = 'mgk-booking-' . sanitize_title( $tutor['slug'] );
    $pid   = wc_get_product_id_by_sku( $sku );
    $price = $price ?: (
        function_exists( 'mgk_money_to_decimal' )
            ? mgk_money_to_decimal( $tutor['rate'] ?? 0, 80 )
            : 80
    );

    $product = $pid ? wc_get_product( $pid ) : new WC_Product_Simple();
    if ( ! $product ) $product = new WC_Product_Simple();

    $product->set_name( 'Tuition session — ' . ( $tutor['name'] ?? 'Tutor' ) );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'hidden' );
    $product->set_sku( $sku );
    $product->set_virtual( true );
    $product->set_sold_individually( true );
    $product->set_regular_price( (string) $price );
    $product->set_price( (string) $price );

    $pid = $product->save();
    if ( $pid ) {
        update_post_meta( $pid, 'mgk_teacher_id',            (int) ( $tutor['id'] ?? 0 ) );
        update_post_meta( $pid, 'mgk_teacher_slug',          sanitize_title( $tutor['slug'] ) );
        update_post_meta( $pid, 'mgk_package_type',          'booking' );
        update_post_meta( $pid, 'mgk_template_owned_product', 1 );
    }

    return (int) $pid;
}

/* ── booking_checkout POST action ────────────────────────── */

add_action( 'template_redirect', function () {
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) return;

    $action = isset( $_POST['mgk_action'] ) ? sanitize_key( wp_unslash( $_POST['mgk_action'] ) ) : '';
    if ( $action !== 'booking_checkout' ) return;

    if ( ! function_exists( 'WC' ) || ! function_exists( 'mgk_woo_available' ) || ! mgk_woo_available() ) {
        wp_die( esc_html__( 'WooCommerce is required to process bookings.', 'mgk-edu' ) );
    }

    if ( ! isset( $_POST['mgk_booking_nonce'] ) || ! wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['mgk_booking_nonce'] ) ), 'mgk_booking_checkout'
    ) ) {
        wp_die( esc_html__( 'The booking request expired. Please refresh and try again.', 'mgk-edu' ) );
    }

    $tutor_slug = isset( $_POST['tutor_slug'] ) ? sanitize_title( wp_unslash( $_POST['tutor_slug'] ) ) : '';
    $lead_id    = isset( $_POST['lead_id'] )    ? (int) wp_unslash( $_POST['lead_id'] )               : 0;
    $slot_id    = isset( $_POST['slot_id'] )    ? sanitize_text_field( wp_unslash( $_POST['slot_id'] ) ) : '';

    $tutor = $tutor_slug ? mgk_profile_tutor( $tutor_slug ) : null;
    if ( ! $tutor ) {
        wp_die( esc_html__( 'Tutor not found. Please go back and choose a tutor.', 'mgk-edu' ) );
    }

    if ( ! $slot_id ) {
        wp_die( esc_html__( 'Please select a time slot before continuing.', 'mgk-edu' ) );
    }

    $product_id = mgk_ensure_booking_product( $tutor );
    if ( ! $product_id ) {
        wp_die( esc_html__( 'Could not prepare a booking product. Please try again.', 'mgk-edu' ) );
    }

    if ( function_exists( 'wc_load_cart' ) && ! WC()->cart ) {
        wc_load_cart();
    }

    if ( ! WC()->cart ) {
        wp_die( esc_html__( 'WooCommerce cart is not available.', 'mgk-edu' ) );
    }

    $decoded      = mgk_decode_slot_id( $slot_id );
    $booking_data = [
        'teacher_id'   => (int) ( $tutor['id'] ?? 0 ),
        'teacher_name' => (string) ( $tutor['name'] ?? '' ),
        'teacher_slug' => (string) ( $tutor['slug'] ?? '' ),
        'lead_id'      => $lead_id,
        'slot_id'      => $slot_id,
        'slot_day'     => $decoded['day'] ?? '',
        'slot_time'    => $decoded['time'] ?? '',
        'package_type' => 'booking',
    ];

    if ( $lead_id ) {
        $booking_data['parent_name'] = (string) get_post_meta( $lead_id, 'mgk_lead_parent_name', true );
        $booking_data['phone']       = (string) get_post_meta( $lead_id, 'mgk_lead_phone', true );
        $booking_data['level']       = (string) get_post_meta( $lead_id, 'mgk_lead_level', true );
        $booking_data['subject']     = (string) get_post_meta( $lead_id, 'mgk_lead_subject', true );
    }

    WC()->cart->empty_cart();
    WC()->cart->add_to_cart( $product_id, 1, 0, [], [ 'mgk_booking' => $booking_data ] );

    if ( $lead_id && function_exists( 'mgk_lead_transition' ) && function_exists( 'mgk_lead_can_transition' ) ) {
        $current = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: MGK_LEAD_CAPTURED;
        if ( mgk_lead_can_transition( $current, MGK_LEAD_PAYMENT_PENDING ) ) {
            mgk_lead_transition( $lead_id, MGK_LEAD_PAYMENT_PENDING );
        }
    }

    wp_safe_redirect( wc_get_checkout_url() );
    exit;
} );

/* ── Link lead ↔ booking on payment ─────────────────────── */

add_action( 'mgk_booking_created', function ( $booking_id, $order_id ) {
    $order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
    if ( ! $order ) return;

    $lead_id = 0;
    foreach ( $order->get_items() as $item ) {
        $lid = (int) $item->get_meta( '_mgk_lead_id' );
        if ( $lid ) { $lead_id = $lid; break; }
    }

    if ( ! $lead_id ) return;

    update_post_meta( $booking_id, 'mgk_lead_id', $lead_id );
    update_post_meta( $lead_id, 'mgk_booking_id', $booking_id );

    if ( function_exists( 'mgk_lead_transition' ) && function_exists( 'mgk_lead_can_transition' ) ) {
        $current = get_post_meta( $lead_id, 'mgk_lead_state', true ) ?: MGK_LEAD_CAPTURED;
        if ( mgk_lead_can_transition( $current, MGK_LEAD_PAID ) ) {
            mgk_lead_transition( $lead_id, MGK_LEAD_PAID );
        }
    }
}, 10, 3 );

/* ── Booking checkout intro (fires before trial intro) ───── */

add_action( 'woocommerce_before_checkout_form', function () {
    if ( ! function_exists( 'mgk_current_booking_from_cart' ) ) return;
    $booking = mgk_current_booking_from_cart();
    if ( empty( $booking['package_type'] ) || $booking['package_type'] !== 'booking' ) return;

    $tutor_name = esc_html( $booking['teacher_name'] ?? 'your tutor' );
    $slot_label = '';
    if ( ! empty( $booking['slot_day'] ) && ! empty( $booking['slot_time'] ) ) {
        $slot_label = ucfirst( $booking['slot_day'] ) . ', ' . $booking['slot_time'];
    }
    ?>
    <section class="mgk-checkout-intro" aria-label="Booking checkout">
        <p class="mgk-eyebrow">Tuition session booking</p>
        <h1>Confirm your tuition slot</h1>
        <?php if ( $slot_label ) : ?>
        <p>You are booking <strong><?php echo esc_html( $slot_label ); ?></strong> with <strong><?php echo $tutor_name; ?></strong>. Review your details and confirm payment.</p>
        <?php endif; ?>
        <div class="mgk-checkout-steps" aria-label="Checkout steps">
            <span class="is-complete">Choose tutor</span>
            <span class="is-complete">Pick a slot</span>
            <span class="is-active">Confirm &amp; pay</span>
            <span>Booking confirmed</span>
        </div>
    </section>
    <?php
}, 3 );

/* ── WC thank you page — booking confirmation ────────────── */

add_action( 'woocommerce_thankyou', function ( $order_id ) {
    $order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
    if ( ! $order ) return;

    $data = [];
    foreach ( $order->get_items() as $item ) {
        $pkg = $item->get_meta( '_mgk_package_type' );
        if ( $pkg !== 'booking' ) continue;
        $data = [
            'teacher_name' => wc_clean( (string) $item->get_meta( '_mgk_teacher_name' ) ),
            'slot_day'     => wc_clean( (string) $item->get_meta( '_mgk_slot_day' ) ),
            'slot_time'    => wc_clean( (string) $item->get_meta( '_mgk_slot_time' ) ),
            'level'        => wc_clean( (string) $item->get_meta( '_mgk_level' ) ),
            'subject'      => wc_clean( (string) $item->get_meta( '_mgk_subject' ) ),
        ];
        break;
    }

    if ( ! $data ) return;

    $slot_label = '';
    if ( $data['slot_day'] && $data['slot_time'] ) {
        $slot_label = ucfirst( $data['slot_day'] ) . ', ' . $data['slot_time'];
    }
    ?>
    <section class="mgk-booking-confirm" aria-label="Booking confirmed">
        <div class="mgk-booking-confirm__icon" aria-hidden="true">&#10003;</div>
        <h2>Booking confirmed!</h2>
        <p>Your tuition session has been requested. The centre will contact you within 24 hours to confirm the exact lesson time.</p>

        <div class="mgk-booking-confirm__details">
            <?php if ( $data['teacher_name'] ) : ?>
            <div class="mgk-booking-confirm__row">
                <span>Tutor</span>
                <strong><?php echo esc_html( $data['teacher_name'] ); ?></strong>
            </div>
            <?php endif; ?>
            <?php if ( $slot_label ) : ?>
            <div class="mgk-booking-confirm__row">
                <span>Requested slot</span>
                <strong><?php echo esc_html( $slot_label ); ?></strong>
            </div>
            <?php endif; ?>
            <?php if ( $data['subject'] ) : ?>
            <div class="mgk-booking-confirm__row">
                <span>Subject</span>
                <strong><?php echo esc_html( $data['subject'] ); ?></strong>
            </div>
            <?php endif; ?>
            <?php if ( $data['level'] ) : ?>
            <div class="mgk-booking-confirm__row">
                <span>Level</span>
                <strong><?php echo esc_html( $data['level'] ); ?></strong>
            </div>
            <?php endif; ?>
        </div>

        <p class="mgk-booking-confirm__next">
            Next step: <?php echo $data['teacher_name'] ? esc_html( $data['teacher_name'] ) : 'Your tutor'; ?> will reach out via WhatsApp to confirm the first lesson date and meeting point.
        </p>
    </section>
    <?php
}, 5 );

/* ── Auto-create booking pages ───────────────────────────── */

add_action( 'init', function () {
    if ( get_option( 'mgk_booking_pages_created' ) ) return;

    $pages = [
        'request-tutor'    => [ 'title' => 'Request a Tutor',    'content' => '[mgk_request_form]' ],
        'tutor-proposals'  => [ 'title' => 'Matching Tutors',    'content' => '[mgk_proposals]' ],
        'book-slot'        => [ 'title' => 'Pick a Time Slot',   'content' => '[mgk_slot_picker]' ],
    ];

    $created = 0;
    foreach ( $pages as $slug => $page ) {
        $existing = get_page_by_path( $slug );
        if ( $existing ) { $created++; continue; }

        $pid = wp_insert_post( [
            'post_type'    => 'page',
            'post_title'   => $page['title'],
            'post_name'    => $slug,
            'post_content' => $page['content'],
            'post_status'  => 'publish',
        ] );

        if ( $pid && ! is_wp_error( $pid ) ) $created++;
    }

    if ( $created === count( $pages ) ) {
        update_option( 'mgk_booking_pages_created', 1 );
    }
}, 99 );

/* ── Shortcode: [mgk_request_form] (S07) ────────────────── */

add_shortcode( 'mgk_request_form', function () {
    $subjects = [
        'English', 'Math', 'Chinese', 'Science', 'Higher Chinese',
        'E-Math', 'A-Math', 'Chemistry', 'Physics', 'Biology',
        'H2 Math', 'H2 Chemistry', 'H2 Physics', 'GP', 'Economics',
        'IB Math AA', 'IB Math AI', 'IB English', 'Mother Tongue', 'Other',
    ];
    $levels = [
        'Preschool (K1-K2)', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6 / PSLE prep',
        'Sec 1', 'Sec 2', 'Sec 3', 'Sec 4 / O-Level', 'JC1', 'JC2 / A-Level',
        'IB Year 1', 'IB Year 2', 'IGCSE', 'University / Adult', 'Other',
    ];
    $schedules = [
        'Weekday mornings (8am–12pm)',
        'Weekday afternoons (12pm–5pm)',
        'Weekday evenings (5pm–9pm)',
        'Saturday mornings',
        'Saturday afternoons',
        'Sunday mornings',
        'Sunday afternoons',
        'Flexible / any time',
    ];

    ob_start();
    ?>
    <div class="mgk-request-form-wrap" id="mgk-request-form-wrap">

        <div class="mgk-booking-steps" aria-label="Booking steps">
            <span class="is-active"><span class="mgk-step-num">1</span> Tell us what you need</span>
            <span><span class="mgk-step-num">2</span> See matched tutors</span>
            <span><span class="mgk-step-num">3</span> Pick a slot &amp; pay</span>
        </div>

        <form class="mgk-request-form" id="js-mgk-request-form" novalidate
              data-rest-url="<?php echo esc_attr( rest_url( 'mgk/v1/leads' ) ); ?>"
              data-tutors-url="<?php echo esc_attr( home_url( '/student/teachers/' ) ); ?>">

            <div class="mgk-form-row mgk-form-row--2col">
                <div class="mgk-form-group">
                    <label for="rf_parent_name">Parent / Guardian name <span class="mgk-required" aria-hidden="true">*</span></label>
                    <input type="text" id="rf_parent_name" name="parent_name" class="mgk-form-input" required autocomplete="name" placeholder="Mrs Tan">
                    <span class="mgk-form-error" id="rf-err-parent_name" aria-live="polite"></span>
                </div>
                <div class="mgk-form-group">
                    <label for="rf_phone">WhatsApp / mobile <span class="mgk-required" aria-hidden="true">*</span></label>
                    <input type="tel" id="rf_phone" name="phone" class="mgk-form-input" required autocomplete="tel" placeholder="+65 9123 4567">
                    <span class="mgk-form-error" id="rf-err-phone" aria-live="polite"></span>
                </div>
            </div>

            <div class="mgk-form-row mgk-form-row--2col">
                <div class="mgk-form-group">
                    <label for="rf_subject">Subject <span class="mgk-required" aria-hidden="true">*</span></label>
                    <select id="rf_subject" name="subject" class="mgk-form-input" required>
                        <option value="">— Select subject —</option>
                        <?php foreach ( $subjects as $s ) : ?>
                        <option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $s ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="mgk-form-error" id="rf-err-subject" aria-live="polite"></span>
                </div>
                <div class="mgk-form-group">
                    <label for="rf_level">Child's current level <span class="mgk-required" aria-hidden="true">*</span></label>
                    <select id="rf_level" name="level" class="mgk-form-input" required>
                        <option value="">— Select level —</option>
                        <?php foreach ( $levels as $l ) : ?>
                        <option value="<?php echo esc_attr( $l ); ?>"><?php echo esc_html( $l ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="mgk-form-error" id="rf-err-level" aria-live="polite"></span>
                </div>
            </div>

            <div class="mgk-form-group">
                <label>Preferred schedule <span class="mgk-form-hint">(select all that apply)</span></label>
                <div class="mgk-checkbox-grid">
                    <?php foreach ( $schedules as $sched ) : ?>
                    <label class="mgk-checkbox-item">
                        <input type="checkbox" name="schedule" value="<?php echo esc_attr( $sched ); ?>">
                        <span><?php echo esc_html( $sched ); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mgk-form-row mgk-form-row--2col">
                <div class="mgk-form-group">
                    <label for="rf_budget">Budget (SGD/hr) <span class="mgk-form-hint">(optional)</span></label>
                    <select id="rf_budget" name="budget" class="mgk-form-input">
                        <option value="">— Any budget —</option>
                        <option value="$30-$60/hr">$30–$60/hr (Part-time tutors)</option>
                        <option value="$60-$100/hr">$60–$100/hr (Full-time tutors)</option>
                        <option value="$100-$150/hr">$100–$150/hr (Ex-MOE / Premium)</option>
                        <option value="$150+/hr">$150+/hr (IB Specialist)</option>
                    </select>
                </div>
                <div class="mgk-form-group">
                    <label for="rf_start_date">Preferred start <span class="mgk-form-hint">(optional)</span></label>
                    <select id="rf_start_date" name="start_date" class="mgk-form-input">
                        <option value="">— Flexible —</option>
                        <option value="ASAP">As soon as possible</option>
                        <option value="Next week">Next week</option>
                        <option value="Next month">Next month</option>
                    </select>
                </div>
            </div>

            <div class="mgk-form-group">
                <label for="rf_notes">Additional notes <span class="mgk-form-hint">(optional)</span></label>
                <textarea id="rf_notes" name="notes" class="mgk-form-input" rows="3"
                          placeholder="Specific topics, upcoming exam date, preferred location, etc."></textarea>
            </div>

            <div class="mgk-form-group">
                <label class="mgk-checkbox-item mgk-checkbox-item--consent">
                    <input type="checkbox" name="consent" id="rf_consent" required>
                    <span>I consent to be contacted via WhatsApp / email about tutor matching and lesson scheduling. <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" target="_blank" rel="noopener">Privacy Policy</a></span>
                </label>
                <span class="mgk-form-error" id="rf-err-consent" aria-live="polite"></span>
            </div>

            <div class="mgk-form-footer">
                <button type="submit" class="mgk-btn mgk-btn-accent mgk-btn-lg" id="js-request-submit">
                    Find matching tutors
                </button>
                <p class="mgk-form-footnote">No commitment required &mdash; browsing is free.</p>
            </div>
        </form>

        <div class="mgk-request-success" id="js-request-success" hidden>
            <div class="mgk-success-icon" aria-hidden="true">&#10003;</div>
            <h2>Request received!</h2>
            <p>We found tutors matching your request. Browse and pick a slot to confirm your first session.</p>
            <a href="#" class="mgk-btn mgk-btn-accent" id="js-view-tutors-link">See matching tutors &rarr;</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
} );

/* ── Shortcode: [mgk_proposals] (S08) ───────────────────── */

add_shortcode( 'mgk_proposals', function ( $atts ) {
    $atts = shortcode_atts( [ 'lead_id' => '', 'limit' => 6 ], $atts );

    $lead_id = (int) ( $atts['lead_id'] ?: ( isset( $_GET['lead_id'] ) ? wp_unslash( $_GET['lead_id'] ) : 0 ) );
    $limit   = max( 1, min( 12, (int) $atts['limit'] ) );

    if ( $lead_id ) {
        $subject = (string) get_post_meta( $lead_id, 'mgk_lead_subject', true );
        $level   = (string) get_post_meta( $lead_id, 'mgk_lead_level', true );
        $budget  = (string) get_post_meta( $lead_id, 'mgk_lead_budget', true );
    } else {
        $subject = isset( $_GET['subject'] ) ? sanitize_text_field( wp_unslash( $_GET['subject'] ) ) : '';
        $level   = isset( $_GET['level'] )   ? sanitize_text_field( wp_unslash( $_GET['level'] ) )   : '';
        $budget  = isset( $_GET['budget'] )  ? sanitize_text_field( wp_unslash( $_GET['budget'] ) )  : '';
    }

    $filters = array_filter( [ 'subject' => $subject, 'level' => $level, 'budget' => $budget ] );
    $tutors  = function_exists( 'mgk_filter_tutors' ) ? array_slice( mgk_filter_tutors( $filters ), 0, $limit ) : [];
    $picker  = home_url( '/book-slot/' );

    ob_start();
    ?>
    <div class="mgk-proposals-wrap">
        <div class="mgk-booking-steps" aria-label="Booking steps">
            <span class="is-complete"><span class="mgk-step-num">1</span> Tell us what you need</span>
            <span class="is-active"><span class="mgk-step-num">2</span> Choose a tutor</span>
            <span><span class="mgk-step-num">3</span> Pick a slot &amp; pay</span>
        </div>

        <?php if ( $subject || $level ) : ?>
        <div class="mgk-proposals-header">
            <h2>
                <?php if ( $subject && $level ) : ?>
                <?php echo esc_html( count( $tutors ) ); ?> tutors for <em><?php echo esc_html( $subject ); ?></em> &middot; <?php echo esc_html( $level ); ?>
                <?php elseif ( $subject ) : ?>
                <?php echo esc_html( count( $tutors ) ); ?> tutors for <em><?php echo esc_html( $subject ); ?></em>
                <?php else : ?>
                Matching tutors
                <?php endif; ?>
            </h2>
        </div>
        <?php endif; ?>

        <?php if ( ! $tutors ) : ?>
        <p class="mgk-notice">No matching tutors found. <a href="<?php echo esc_url( home_url( '/student/teachers/' ) ); ?>">Browse all tutors</a>.</p>
        <?php else : ?>

        <div class="mgk-proposals-grid">
            <?php foreach ( $tutors as $tutor ) :
                $book_url = add_query_arg( array_filter( [
                    'tutor'   => $tutor['slug'],
                    'lead_id' => $lead_id ?: '',
                ] ), $picker );
                $profile_url = function_exists( 'mgk_teacher_profile_url' ) ? mgk_teacher_profile_url( $tutor ) : '#';
                $stars = $tutor['rating'] ? number_format( (float) $tutor['rating'], 1 ) . ' &#9733;' : '';
            ?>
            <div class="mgk-proposal-card">
                <a href="<?php echo esc_url( $profile_url ); ?>" class="mgk-proposal-card__photo-wrap" tabindex="-1" aria-hidden="true">
                    <?php if ( ! empty( $tutor['photo'] ) ) : ?>
                    <img src="<?php echo esc_url( $tutor['photo'] ); ?>" alt="<?php echo esc_attr( $tutor['name'] ); ?>" class="mgk-proposal-card__photo" loading="lazy">
                    <?php else : ?>
                    <div class="mgk-proposal-card__photo-placeholder"><?php echo esc_html( mb_substr( $tutor['name'], 0, 1 ) ); ?></div>
                    <?php endif; ?>
                </a>
                <div class="mgk-proposal-card__body">
                    <div class="mgk-proposal-card__top">
                        <a href="<?php echo esc_url( $profile_url ); ?>" class="mgk-proposal-card__name"><?php echo esc_html( $tutor['name'] ); ?></a>
                        <?php if ( $stars ) : ?>
                        <span class="mgk-proposal-card__rating"><?php echo $stars; // phpcs:ignore ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( ! empty( $tutor['tier'] ) ) : ?>
                    <span class="mgk-badge mgk-badge-info"><?php echo esc_html( $tutor['tier'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( ! empty( $tutor['subjects'] ) ) : ?>
                    <p class="mgk-proposal-card__subjects"><?php echo esc_html( implode( ' · ', array_slice( $tutor['subjects'], 0, 4 ) ) ); ?></p>
                    <?php endif; ?>
                    <?php if ( ! empty( $tutor['bio'] ) ) : ?>
                    <p class="mgk-proposal-card__bio"><?php echo esc_html( wp_trim_words( $tutor['bio'], 20 ) ); ?></p>
                    <?php endif; ?>
                    <div class="mgk-proposal-card__footer">
                        <?php if ( ! empty( $tutor['rate'] ) ) : ?>
                        <span class="mgk-proposal-card__rate"><?php echo esc_html( $tutor['rate'] ); ?></span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $book_url ); ?>" class="mgk-btn mgk-btn-accent mgk-btn-sm">
                            Pick a slot
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
} );

/* ── Shortcode: [mgk_slot_picker] (S10) ─────────────────── */

add_shortcode( 'mgk_slot_picker', function ( $atts ) {
    $atts = shortcode_atts( [ 'tutor' => '', 'lead' => '' ], $atts );

    $tutor_slug = sanitize_title( $atts['tutor'] ?: ( isset( $_GET['tutor'] ) ? wp_unslash( $_GET['tutor'] ) : '' ) );
    $lead_id    = (int) ( $atts['lead'] ?: ( isset( $_GET['lead_id'] ) ? wp_unslash( $_GET['lead_id'] ) : 0 ) );

    if ( ! $tutor_slug ) {
        return '<p class="mgk-notice">Please <a href="' . esc_url( home_url( '/student/teachers/' ) ) . '">choose a tutor</a> first.</p>';
    }

    $tutor = function_exists( 'mgk_profile_tutor' ) ? mgk_profile_tutor( $tutor_slug ) : null;
    if ( ! $tutor ) {
        return '<p class="mgk-notice">Tutor not found. <a href="' . esc_url( home_url( '/student/teachers/' ) ) . '">Browse tutors</a>.</p>';
    }

    $slots  = mgk_get_enriched_slots( $tutor_slug );
    $by_day = [];
    foreach ( $slots as $slot ) {
        $by_day[ $slot['day_label'] ][] = $slot;
    }

    $subjects_label = ! empty( $tutor['subjects'] ) ? implode( ' · ', array_slice( $tutor['subjects'], 0, 3 ) ) : '';
    $post_url       = esc_url( get_permalink() ?: home_url( '/book-slot/' ) );

    ob_start();
    ?>
    <div class="mgk-slot-picker" id="mgk-slot-picker"
         data-tutor="<?php echo esc_attr( $tutor_slug ); ?>"
         data-lead="<?php echo esc_attr( $lead_id ); ?>">

        <div class="mgk-booking-steps" aria-label="Booking steps">
            <span class="is-complete"><span class="mgk-step-num">1</span> Tell us what you need</span>
            <span class="is-complete"><span class="mgk-step-num">2</span> Choose a tutor</span>
            <span class="is-active"><span class="mgk-step-num">3</span> Pick a slot &amp; pay</span>
        </div>

        <div class="mgk-slot-tutor-card">
            <?php if ( ! empty( $tutor['photo'] ) ) : ?>
            <img src="<?php echo esc_url( $tutor['photo'] ); ?>" alt="<?php echo esc_attr( $tutor['name'] ); ?>" class="mgk-slot-tutor-photo" loading="lazy">
            <?php endif; ?>
            <div class="mgk-slot-tutor-info">
                <strong class="mgk-slot-tutor-name"><?php echo esc_html( $tutor['name'] ); ?></strong>
                <?php if ( $subjects_label ) : ?>
                <span class="mgk-slot-tutor-subjects"><?php echo esc_html( $subjects_label ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $tutor['rate'] ) ) : ?>
                <span class="mgk-slot-tutor-rate"><?php echo esc_html( $tutor['rate'] ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="mgk-slot-picker__body">
            <h2 class="mgk-slot-picker__heading">Choose a time slot</h2>
            <p class="mgk-slot-picker__hint">Slots are held for 10 minutes after selection. Pay immediately to confirm.</p>

            <?php if ( ! $slots ) : ?>
            <div class="mgk-notice">No available slots this week for <?php echo esc_html( $tutor['name'] ); ?>. Please <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact us</a> to arrange a custom time.</div>
            <?php else : ?>
            <div class="mgk-slot-days" role="group" aria-label="Available time slots by day">
                <?php foreach ( $by_day as $day_label => $day_slots ) : ?>
                <div class="mgk-slot-day">
                    <div class="mgk-slot-day__label"><?php echo esc_html( $day_label ); ?></div>
                    <div class="mgk-slot-times">
                        <?php foreach ( $day_slots as $slot ) :
                            $is_taken = $slot['status'] !== 'available';
                            $badge    = '';
                            if ( $slot['status'] === 'held' )   $badge = '<span class="mgk-slot-badge">Held</span>';
                            if ( $slot['status'] === 'booked' ) $badge = '<span class="mgk-slot-badge">Booked</span>';
                        ?>
                        <button type="button"
                                class="mgk-slot-btn<?php echo $is_taken ? ' is-taken' : ''; ?>"
                                data-slot-id="<?php echo esc_attr( $slot['id'] ); ?>"
                                data-slot-time="<?php echo esc_attr( $slot['time'] ); ?>"
                                data-slot-day="<?php echo esc_attr( $slot['day_label'] ); ?>"
                                <?php echo $is_taken ? 'disabled aria-disabled="true"' : ''; ?>>
                            <?php echo esc_html( $slot['time'] ); ?>
                            <?php echo $badge; // phpcs:ignore ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ( $slots ) : ?>
        <div class="mgk-slot-selected-wrap" id="js-slot-selected-wrap" aria-live="polite">
            <div class="mgk-slot-selected-summary" id="js-slot-selected-summary" hidden>
                <div class="mgk-slot-selected-label">
                    Selected: <strong id="js-selected-label-text"></strong>
                </div>
                <div class="mgk-slot-countdown" id="js-slot-countdown"></div>
            </div>

            <form class="mgk-slot-checkout-form" id="js-slot-checkout-form"
                  method="post" action="<?php echo $post_url; ?>" hidden>
                <input type="hidden" name="mgk_action"  value="booking_checkout">
                <input type="hidden" name="tutor_slug"  value="<?php echo esc_attr( $tutor_slug ); ?>">
                <input type="hidden" name="lead_id"     value="<?php echo esc_attr( $lead_id ); ?>">
                <input type="hidden" name="slot_id"     id="js-slot-id-input" value="">
                <?php wp_nonce_field( 'mgk_booking_checkout', 'mgk_booking_nonce' ); ?>
                <button type="submit" class="mgk-btn mgk-btn-accent mgk-btn-lg" id="js-slot-pay-btn">
                    Confirm slot &amp; pay &rarr;
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
} );
