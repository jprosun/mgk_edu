<?php
/**
 * MGK asset loading.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_asset_version( $relative_path ) {
    $path = get_stylesheet_directory() . '/' . ltrim( $relative_path, '/' );
    return file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0';
}

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'mgk-inter-font',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
        [],
        null
    );

    $css_files = [
        'mgk-tokens'     => 'assets/css/mgk-tokens.css',
        'mgk-base'       => 'assets/css/mgk-base.css',
        'mgk-layout'     => 'assets/css/mgk-layout.css',
        'mgk-components' => 'assets/css/mgk-components.css',
        'mgk-states'     => 'assets/css/mgk-states.css',
        'mgk-home'       => 'assets/css/mgk-home.css',
        'mgk-listing'    => 'assets/css/mgk-listing.css',
        'mgk-profile'    => 'assets/css/mgk-profile.css',
        'mgk-subjects'   => 'assets/css/mgk-subjects.css',
        'mgk-how'        => 'assets/css/mgk-how.css',
        'mgk-pricing'    => 'assets/css/mgk-pricing.css',
        'mgk-woo'        => 'assets/css/mgk-woo.css',
        'mgk-modal'      => 'assets/css/mgk-modal.css',
        'mgk-booking'    => 'assets/css/mgk-booking.css',
        'mgk-proposals'  => 'assets/css/mgk-proposals.css',
        'mgk-parent-dashboard' => 'assets/css/mgk-parent-dashboard.css',
        'mgk-package'    => 'assets/css/mgk-package.css',
        'mgk-messages'   => 'assets/css/mgk-messages.css',
        'mgk-parent-review' => 'assets/css/mgk-parent-review.css',
        'mgk-parent-referral' => 'assets/css/mgk-parent-referral.css',
        'mgk-parent-account' => 'assets/css/mgk-parent-account.css',
        'mgk-notification-center' => 'assets/css/mgk-notification-center.css',
        'mgk-tutor-apply' => 'assets/css/mgk-tutor-apply.css',
        'mgk-tutor-verification' => 'assets/css/mgk-tutor-verification.css',
        'mgk-tutor-dashboard' => 'assets/css/mgk-tutor-dashboard.css',
        'mgk-tutor-lesson-log' => 'assets/css/mgk-tutor-lesson-log.css',
        'mgk-tutor-earnings' => 'assets/css/mgk-tutor-earnings.css',
        'mgk-tutor-schedule-profile' => 'assets/css/mgk-tutor-schedule-profile.css',
        'mgk-request'    => 'assets/css/mgk-request.css',
        'mgk-responsive' => 'assets/css/mgk-responsive.css',
    ];

    // Chain MGK stylesheets so cascade order is deterministic. The first one has
    // no parent-style dependency (Hello Elementor's reset loads independently);
    // each subsequent sheet depends on the previous so order is preserved.
    $previous = '';
    foreach ( $css_files as $handle => $file ) {
        $deps = $previous === '' ? [] : [ $previous ];
        wp_enqueue_style( $handle, get_stylesheet_directory_uri() . '/' . $file, $deps, mgk_asset_version( $file ) );
        $previous = $handle;
    }

    wp_enqueue_script(
        'mgk-main',
        get_stylesheet_directory_uri() . '/assets/js/mgk-main.js',
        [],
        mgk_asset_version( 'assets/js/mgk-main.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-filters',
        get_stylesheet_directory_uri() . '/assets/js/mgk-filters.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-filters.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-analytics',
        get_stylesheet_directory_uri() . '/assets/js/mgk-analytics.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-analytics.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-tutor-onboarding',
        get_stylesheet_directory_uri() . '/assets/js/mgk-tutor-onboarding.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-tutor-onboarding.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-pricing',
        get_stylesheet_directory_uri() . '/assets/js/mgk-pricing.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-pricing.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-reviews',
        get_stylesheet_directory_uri() . '/assets/js/mgk-reviews.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-reviews.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-modal',
        get_stylesheet_directory_uri() . '/assets/js/mgk-modal.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-modal.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-proposals',
        get_stylesheet_directory_uri() . '/assets/js/mgk-proposals.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-proposals.js' ),
        true
    );

    wp_enqueue_script(
        'mgk-messages',
        get_stylesheet_directory_uri() . '/assets/js/mgk-messages.js',
        [ 'mgk-main' ],
        mgk_asset_version( 'assets/js/mgk-messages.js' ),
        true
    );

    // Booking flow JS — loaded on request/proposal/slot-picker/package/payment pages.
    $load_booking_js = is_page( [ 'request-tutor', 'tutor-proposals', 'proposals', 'book-slot', 'buy-package', 'trial-pay', 'pay', 'trial-confirmed', 'confirmed' ] )
        || ( function_exists( 'is_checkout' ) && is_checkout() )
        || ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) );

    if ( $load_booking_js ) {
        wp_enqueue_script(
            'mgk-booking',
            get_stylesheet_directory_uri() . '/assets/js/mgk-booking.js',
            [ 'mgk-main' ],
            mgk_asset_version( 'assets/js/mgk-booking.js' ),
            true
        );

        wp_localize_script( 'mgk-booking', 'mgkBookingData', [
            'restUrl'   => esc_url_raw( rest_url( 'mgk/v1/' ) ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'tutorsUrl' => esc_url_raw( home_url( '/student/teachers/' ) ),
        ] );

        // S10 Pick Slot JS (countdown + slot hold). Loaded on the slot page, the
        // S11 pay page (it reuses the hold banner), or any singular using the
        // S10/S11 widgets/shortcodes.
        $load_slots_js = is_page( [ 'book-slot', 'trial-pay', 'pay' ] );
        if ( ! $load_slots_js && is_singular() ) {
            $qid = get_queried_object_id();
            $content = (string) get_post_field( 'post_content', $qid );
            $el      = (string) get_post_meta( $qid, '_elementor_data', true );
            foreach ( [ 'mgk_pick_slot', 'mgk_live_calendar', 'mgk_available_times', 'mgk_slot_hold_banner', 'mgk_selected_slot', 'mgk_pay' ] as $sc ) {
                if ( strpos( $content, $sc ) !== false || strpos( $el, $sc ) !== false ) { $load_slots_js = true; break; }
            }
        }
        if ( apply_filters( 'mgk_load_slots_js', $load_slots_js ) ) {
            wp_enqueue_script(
                'mgk-slots',
                get_stylesheet_directory_uri() . '/assets/js/mgk-slots.js',
                [ 'mgk-booking' ],
                mgk_asset_version( 'assets/js/mgk-slots.js' ),
                true
            );
        }

        // S11 Pay JS (payment-method toggle, terms gating, processing/status
        // states, sequential section reveal). Loaded on the pay page or any
        // singular using the S11 widgets/shortcodes.
        $load_pay_js = is_page( [ 'trial-pay', 'pay' ] );
        if ( ! $load_pay_js && is_singular() ) {
            $qid = get_queried_object_id();
            $content = (string) get_post_field( 'post_content', $qid );
            $el      = (string) get_post_meta( $qid, '_elementor_data', true );
            foreach ( [ 'mgk_pay', 'mgk_pay_method', 'mgk_pay_summary', 'mgk_pay_account', 'mgk_pay_terms', 'mgk_pay_cta' ] as $sc ) {
                if ( strpos( $content, $sc ) !== false || strpos( $el, $sc ) !== false ) { $load_pay_js = true; break; }
            }
        }
        if ( apply_filters( 'mgk_load_pay_js', $load_pay_js ) ) {
            // PayNow QR encoder (proven qrcodejs lib) — drawn client-side so the
            // payload never leaves the page. mgk-pay depends on it.
            wp_enqueue_script(
                'mgk-qrcode',
                get_stylesheet_directory_uri() . '/assets/js/mgk-qrcode.js',
                [],
                mgk_asset_version( 'assets/js/mgk-qrcode.js' ),
                true
            );
            wp_enqueue_script(
                'mgk-pay',
                get_stylesheet_directory_uri() . '/assets/js/mgk-pay.js',
                [ 'mgk-booking', 'mgk-qrcode' ],
                mgk_asset_version( 'assets/js/mgk-pay.js' ),
                true
            );
        }

        // S12 Booking Confirmation JS (manage-booking modals, calendar add,
        // safe tracking). Loaded on the confirmation page or any singular using
        // the S12 widgets/shortcodes.
        $load_success_js = is_page( [ 'trial-confirmed', 'confirmed' ] );
        if ( ! $load_success_js && is_singular() ) {
            $qid = get_queried_object_id();
            $content = (string) get_post_field( 'post_content', $qid );
            $el      = (string) get_post_meta( $qid, '_elementor_data', true );
            foreach ( [ 'mgk_booking_success', 'mgk_manage_booking', 'mgk_tutor_contact', 'mgk_first_lesson', 'mgk_next_steps', 'mgk_success_hero' ] as $sc ) {
                if ( strpos( $content, $sc ) !== false || strpos( $el, $sc ) !== false ) { $load_success_js = true; break; }
            }
        }
        if ( apply_filters( 'mgk_load_success_js', $load_success_js ) ) {
            wp_enqueue_script(
                'mgk-booking-success',
                get_stylesheet_directory_uri() . '/assets/js/mgk-booking-success.js',
                [ 'mgk-booking' ],
                mgk_asset_version( 'assets/js/mgk-booking-success.js' ),
                true
            );
        }
    }

    // Request Match flow (S07) JS — request-match page, or any singular whose
    // content uses the composite OR a split request widget/shortcode.
    $load_request_js = is_page( [ 'request-match' ] );
    if ( ! $load_request_js && is_singular() ) {
        $content = (string) get_post_field( 'post_content', get_queried_object_id() );
        foreach ( [
            'mgk_request_match', 'mgk_request_fields', 'mgk_request_intro',
            'mgk_request_submit', 'mgk_request_confirm',
            'mgk_request_field_level', 'mgk_request_field_subject', 'mgk_request_field_schedule',
            'mgk_request_field_budget', 'mgk_request_field_note', 'mgk_request_field_phone',
            'mgk_request_field_pdpa',
        ] as $sc ) {
            if ( has_shortcode( $content, $sc ) || strpos( $content, $sc ) !== false ) { $load_request_js = true; break; }
        }
        // Elementor-built page: widgets render via do_shortcode but live in
        // _elementor_data (not post_content) — scan that too.
        if ( ! $load_request_js ) {
            $el = (string) get_post_meta( get_queried_object_id(), '_elementor_data', true );
            if ( $el !== '' && strpos( $el, 'mgk_request_' ) !== false ) { $load_request_js = true; }
        }
    }

    if ( apply_filters( 'mgk_load_request_js', $load_request_js ) ) {
        wp_enqueue_script(
            'mgk-request',
            get_stylesheet_directory_uri() . '/assets/js/mgk-request.js',
            [ 'mgk-main' ],
            mgk_asset_version( 'assets/js/mgk-request.js' ),
            true
        );
    }
} );
