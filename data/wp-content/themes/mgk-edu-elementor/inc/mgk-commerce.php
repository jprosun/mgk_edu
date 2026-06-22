<?php
/**
 * WooCommerce bridge for MGK Edu.
 *
 * MGK owns tutor/profile/availability data. WooCommerce owns payment, checkout,
 * order records, receipts, refunds, coupons, and reporting.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function mgk_woo_available() {
    return class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && class_exists( 'WC_Product_Simple' );
}

function mgk_money_to_decimal( $value, $fallback = 40 ) {
    if ( is_numeric( $value ) ) {
        return max( 0, (float) $value );
    }

    if ( preg_match( '/\d+(?:\.\d+)?/', (string) $value, $matches ) ) {
        return max( 0, (float) $matches[0] );
    }

    return (float) $fallback;
}

function mgk_ensure_trial_product( array $tutor ) {
    if ( ! mgk_woo_available() || empty( $tutor['slug'] ) ) {
        return 0;
    }

    $sku = 'mgk-trial-' . sanitize_title( $tutor['slug'] );
    $product_id = wc_get_product_id_by_sku( $sku );
    $price = mgk_money_to_decimal( $tutor['trial'] ?? '$40' );
    $name = 'Tutor trial - ' . ( $tutor['name'] ?? 'Tutor' );

    $product = $product_id ? wc_get_product( $product_id ) : new WC_Product_Simple();
    if ( ! $product ) {
        $product = new WC_Product_Simple();
    }

    $product->set_name( $name );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'hidden' );
    $product->set_sku( $sku );
    $product->set_virtual( true );
    $product->set_sold_individually( true );
    $product->set_regular_price( (string) $price );
    $product->set_price( (string) $price );

    $product_id = $product->save();
    if ( $product_id ) {
        update_post_meta( $product_id, 'mgk_teacher_id', (int) ( $tutor['id'] ?? 0 ) );
        update_post_meta( $product_id, 'mgk_teacher_slug', sanitize_title( $tutor['slug'] ) );
        update_post_meta( $product_id, 'mgk_package_type', 'trial' );
        update_post_meta( $product_id, 'mgk_template_owned_product', 1 );
    }

    return (int) $product_id;
}

function mgk_trial_checkout_fields_from_post() {
    $field = function ( $key ) {
        return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
    };

    return [
        'parent_name' => $field( 'parent_name' ),
        'phone'       => $field( 'phone' ),
        'level'       => $field( 'level' ),
        'subject'     => $field( 'subject' ),
        'schedule'    => $field( 'schedule' ),
        'budget'      => $field( 'budget' ),
    ];
}

add_action( 'template_redirect', function () {
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
        return;
    }

    $action = isset( $_POST['mgk_action'] ) ? sanitize_key( wp_unslash( $_POST['mgk_action'] ) ) : '';
    if ( $action !== 'trial_checkout' ) {
        return;
    }

    if ( ! mgk_woo_available() ) {
        wp_die( esc_html__( 'WooCommerce is required before collecting trial payments.', 'mgk-edu' ) );
    }

    if ( ! isset( $_POST['mgk_trial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgk_trial_nonce'] ) ), 'mgk_trial_checkout' ) ) {
        wp_die( esc_html__( 'The booking request expired. Please refresh and try again.', 'mgk-edu' ) );
    }

    $tutor_slug = isset( $_POST['tutor'] ) ? sanitize_title( wp_unslash( $_POST['tutor'] ) ) : '';
    $tutor = $tutor_slug ? mgk_profile_tutor( $tutor_slug ) : null;
    if ( ! $tutor ) {
        wp_die( esc_html__( 'Please choose a tutor before checkout.', 'mgk-edu' ) );
    }

    $fields = mgk_trial_checkout_fields_from_post();
    foreach ( [ 'parent_name', 'phone', 'level', 'subject' ] as $required ) {
        if ( empty( $fields[ $required ] ) ) {
            wp_die( esc_html__( 'Please complete the required booking fields.', 'mgk-edu' ) );
        }
    }

    if ( empty( $_POST['consent'] ) ) {
        wp_die( esc_html__( 'Please agree to be contacted about this trial request.', 'mgk-edu' ) );
    }

    $product_id = mgk_ensure_trial_product( $tutor );
    if ( ! $product_id ) {
        wp_die( esc_html__( 'Could not prepare this trial product. Please try another tutor.', 'mgk-edu' ) );
    }

    if ( function_exists( 'wc_load_cart' ) && ! WC()->cart ) {
        wc_load_cart();
    }

    if ( ! WC()->cart ) {
        wp_die( esc_html__( 'WooCommerce cart is not available.', 'mgk-edu' ) );
    }

    $booking = array_merge( $fields, [
        'teacher_id'   => (int) ( $tutor['id'] ?? 0 ),
        'teacher_name' => (string) ( $tutor['name'] ?? '' ),
        'teacher_slug' => (string) ( $tutor['slug'] ?? '' ),
        'package_type' => 'trial',
    ] );

    WC()->cart->empty_cart();
    WC()->cart->add_to_cart( $product_id, 1, 0, [], [
        'mgk_booking' => $booking,
    ] );

    wp_safe_redirect( wc_get_checkout_url() );
    exit;
} );

add_filter( 'woocommerce_get_item_data', function ( $item_data, $cart_item ) {
    if ( empty( $cart_item['mgk_booking'] ) || ! is_array( $cart_item['mgk_booking'] ) ) {
        return $item_data;
    }

    $labels = [
        'teacher_name' => 'Tutor',
        'parent_name'  => 'Parent',
        'phone'        => 'Mobile',
        'level'        => 'Child level',
        'subject'      => 'Subject',
        'schedule'     => 'Preferred schedule',
        'budget'       => 'Budget',
    ];

    foreach ( $labels as $key => $label ) {
        if ( ! empty( $cart_item['mgk_booking'][ $key ] ) ) {
            $item_data[] = [
                'name'  => $label,
                'value' => wc_clean( $cart_item['mgk_booking'][ $key ] ),
            ];
        }
    }

    return $item_data;
}, 10, 2 );

function mgk_current_booking_from_cart() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return [];
    }

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( ! empty( $cart_item['mgk_booking'] ) && is_array( $cart_item['mgk_booking'] ) ) {
            return $cart_item['mgk_booking'];
        }
    }

    return [];
}

function mgk_split_parent_name( $name ) {
    $parts = preg_split( '/\s+/', trim( (string) $name ) );
    $parts = array_values( array_filter( $parts ) );

    if ( ! $parts ) {
        return [ '', '' ];
    }

    $first = array_shift( $parts );
    $last = $parts ? implode( ' ', $parts ) : '-';

    return [ $first, $last ];
}

add_action( 'wp', function () {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
    }
} );

add_action( 'template_redirect', function () {
    if ( function_exists( 'is_cart' ) && is_cart() && mgk_current_booking_from_cart() ) {
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
} );

add_action( 'template_redirect', function () {
    if ( ! is_user_logged_in() || ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
        return;
    }

    if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'customer-logout' ) ) {
        return;
    }

    $user = wp_get_current_user();
    if ( function_exists( 'mgk_is_passwordless_user' ) && mgk_is_passwordless_user( $user ) && ! current_user_can( 'edit_posts' ) ) {
        $target = function_exists( 'mgk_passwordless_dashboard_url' )
            ? mgk_passwordless_dashboard_url( $user )
            : mgk_url( '/parent/dashboard/' );
        wp_safe_redirect( $target );
        exit;
    }
}, 1 );

add_filter( 'body_class', function ( $classes ) {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        $classes[] = 'mgk-woo-checkout';
    }

    if ( function_exists( 'is_cart' ) && is_cart() ) {
        $classes[] = 'mgk-woo-cart';
    }

    return $classes;
} );

add_action( 'woocommerce_before_checkout_form', function () {
    // Booking package type handled by mgk-booking.php at priority 3
    if ( function_exists( 'mgk_current_booking_from_cart' ) ) {
        $booking = mgk_current_booking_from_cart();
        if ( ! empty( $booking['package_type'] ) && $booking['package_type'] === 'booking' ) {
            return;
        }
    }
    ?>
    <section class="mgk-checkout-intro" aria-label="Trial checkout">
        <p class="mgk-eyebrow">Trial request</p>
        <h1>Confirm your tutor trial</h1>
        <p>Review the tutor, contact details, and trial fee before submitting. WooCommerce handles payment and order records behind the scenes.</p>
        <div class="mgk-checkout-steps" aria-label="Checkout steps">
            <span class="is-complete">Choose tutor</span>
            <span class="is-active">Confirm request</span>
            <span>Trial confirmed</span>
        </div>
    </section>
    <?php
}, 5 );

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
    foreach ( [ 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode' ] as $key ) {
        unset( $fields['billing'][ $key ] );
    }

    if ( isset( $fields['billing']['billing_first_name'] ) ) {
        $fields['billing']['billing_first_name']['label'] = 'Parent / guardian first name';
        $fields['billing']['billing_first_name']['placeholder'] = 'Mrs';
    }

    if ( isset( $fields['billing']['billing_last_name'] ) ) {
        $fields['billing']['billing_last_name']['label'] = 'Parent / guardian last name';
        $fields['billing']['billing_last_name']['placeholder'] = 'Tan';
    }

    if ( isset( $fields['billing']['billing_country'] ) ) {
        $fields['billing']['billing_country']['type'] = 'hidden';
        $fields['billing']['billing_country']['default'] = 'SG';
        $fields['billing']['billing_country']['required'] = false;
        $fields['billing']['billing_country']['label'] = 'Country';
    }

    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['label'] = 'WhatsApp / mobile number';
        $fields['billing']['billing_phone']['placeholder'] = '+65 9123 4567';
    }

    if ( isset( $fields['billing']['billing_email'] ) ) {
        $fields['billing']['billing_email']['label'] = 'Email for confirmation';
        $fields['billing']['billing_email']['placeholder'] = 'parent@example.com';
    }

    if ( isset( $fields['order']['order_comments'] ) ) {
        $fields['order']['order_comments']['label'] = 'Trial notes';
        $fields['order']['order_comments']['placeholder'] = 'Anything we should know before confirming the trial?';
    }

    return $fields;
} );

add_filter( 'woocommerce_checkout_get_value', function ( $value, $input ) {
    $booking = mgk_current_booking_from_cart();

    if ( $input === 'billing_country' ) {
        return 'SG';
    }

    if ( $input === 'billing_phone' && ! empty( $booking['phone'] ) ) {
        return $booking['phone'];
    }

    if ( in_array( $input, [ 'billing_first_name', 'billing_last_name' ], true ) && ! empty( $booking['parent_name'] ) ) {
        [ $first, $last ] = mgk_split_parent_name( $booking['parent_name'] );
        return $input === 'billing_first_name' ? $first : $last;
    }

    return $value;
}, 10, 2 );

add_filter( 'woocommerce_order_button_text', function () {
    return 'Confirm trial request';
} );

add_filter( 'woocommerce_cart_item_name', function ( $name, $cart_item ) {
    if ( empty( $cart_item['mgk_booking']['teacher_name'] ) ) {
        return $name;
    }

    return esc_html( 'Tutor trial - ' . $cart_item['mgk_booking']['teacher_name'] );
}, 10, 2 );

add_filter( 'gettext', function ( $translated, $text, $domain ) {
    // Relabel WooCommerce's e-commerce wording to tutoring/booking language across
    // the whole Woo flow (cart, checkout, account, mini-cart). A tutoring site must
    // not show "shopping cart / basket / shop / product" language. We scope to Woo
    // pages so we don't rewrite the same words elsewhere on the site.
    // Not memoized: gettext fires very early/often and the conditional tags depend
    // on the main query being ready, so we evaluate fresh (the checks are cheap).
    $is_woo = ( function_exists( 'is_cart' ) && is_cart() )
        || ( function_exists( 'is_checkout' ) && is_checkout() )
        || ( function_exists( 'is_account_page' ) && is_account_page() )
        || ( function_exists( 'is_woocommerce' ) && is_woocommerce() );
    if ( ! $is_woo ) {
        return $translated;
    }

    $replacements = [
        // Checkout
        'Billing details' => 'Parent contact details',
        'Additional information' => 'Trial notes',
        'Your order' => 'Trial request summary',
        'Product' => 'Tutor trial',
        'Products' => 'Tutor trials',
        'Subtotal' => 'Trial fee',
        'Total' => 'Total due today',
        'Place order' => 'Confirm trial request',
        'Order number:' => 'Booking reference:',
        'Order details' => 'Booking details',
        // Cart / basket → booking summary
        'Cart' => 'Trial summary',
        'Cart totals' => 'Trial summary',
        'Basket' => 'Trial summary',
        'View cart' => 'View trial summary',
        'View basket' => 'View trial summary',
        'Update cart' => 'Update trial',
        'Update basket' => 'Update trial',
        'Proceed to checkout' => 'Continue to confirm',
        'Return to shop' => 'Browse more tutors',
        'Continue shopping' => 'Browse more tutors',
        'Your cart is currently empty.' => 'You have no trial selected yet. Browse tutors to book a trial lesson.',
        'Your basket is currently empty.' => 'You have no trial selected yet. Browse tutors to book a trial lesson.',
        'Remove this item' => 'Remove this trial',
        'Quantity' => 'Lessons',
        'Add to cart' => 'Book trial',
        'Add to basket' => 'Book trial',
        // Account
        'My account' => 'My bookings',
        'Orders' => 'Bookings',
        'My Orders' => 'My bookings',
        'No order has been made yet.' => 'No trial booking yet.',
        'Browse products' => 'Browse tutors',
        // Payment / privacy notices
        'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => 'Online payment is not configured yet. For demo review, you can still confirm the request or contact the centre for offline payment.',
        'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.' => 'Your details are used to confirm this trial request, contact you about tutor availability, and process payment securely.',
    ];

    return $replacements[ $text ] ?? $translated;
}, 10, 3 );

/**
 * Relabel the Woo page TITLES (Cart / Checkout / My account) at render time only,
 * without renaming the actual pages in the DB (owner can still rename in wp-admin).
 * The gettext filter can't catch these because they're the WP page post_title, not
 * a translated string.
 */
function mgk_woo_title_map( $title ) {
    $map = [
        'Cart'       => 'Trial summary',
        'Basket'     => 'Trial summary',
        'Checkout'   => 'Confirm your trial',
        'My account' => 'My bookings',
        'My Account' => 'My bookings',
    ];
    return $map[ $title ] ?? $title;
}
add_filter( 'the_title', function ( $title, $post_id = 0 ) {
    if ( is_admin() ) {
        return $title;
    }
    $is_woo_page = ( function_exists( 'is_cart' ) && is_cart() )
        || ( function_exists( 'is_checkout' ) && is_checkout() )
        || ( function_exists( 'is_account_page' ) && is_account_page() );
    if ( ! $is_woo_page || ! in_the_loop() ) {
        return $title;
    }
    return mgk_woo_title_map( $title );
}, 10, 2 );
add_filter( 'document_title_parts', function ( $parts ) {
    if ( is_admin() || empty( $parts['title'] ) ) {
        return $parts;
    }
    $is_woo_page = ( function_exists( 'is_cart' ) && is_cart() )
        || ( function_exists( 'is_checkout' ) && is_checkout() )
        || ( function_exists( 'is_account_page' ) && is_account_page() );
    if ( $is_woo_page ) {
        $parts['title'] = mgk_woo_title_map( $parts['title'] );
    }
    return $parts;
} );

add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $cart_item_key, $values ) {
    if ( empty( $values['mgk_booking'] ) || ! is_array( $values['mgk_booking'] ) ) {
        return;
    }

    $booking = $values['mgk_booking'];
    $public_meta = [
        'teacher_name' => 'Tutor',
        'parent_name'  => 'Parent',
        'phone'        => 'Mobile',
        'level'        => 'Child level',
        'subject'      => 'Subject',
        'schedule'     => 'Preferred schedule',
        'budget'       => 'Budget',
    ];

    foreach ( $public_meta as $key => $label ) {
        if ( ! empty( $booking[ $key ] ) ) {
            $item->add_meta_data( $label, wc_clean( $booking[ $key ] ), true );
        }
    }

    foreach ( $booking as $key => $value ) {
        $item->add_meta_data( '_mgk_' . sanitize_key( $key ), wc_clean( (string) $value ), true );
    }
}, 10, 3 );

/* ── WC order → mg_booking bridge ────────────────────────── */

/**
 * Create or update a mg_booking post when a WC order transitions to
 * processing (payment received) or completed.
 * One booking per WC order; idempotent via _mgk_wc_order_id meta.
 */
add_action( 'woocommerce_order_status_changed', function ( $order_id, $from_status, $to_status ) {
    if ( ! in_array( $to_status, [ 'processing', 'completed' ], true ) ) {
        return;
    }

    if ( ! post_type_exists( 'mg_booking' ) ) {
        return;
    }

    // Prevent creating duplicate bookings for the same WC order.
    $existing = get_posts( [
        'post_type'      => 'mg_booking',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [ 'key' => 'mgk_wc_order_id', 'value' => $order_id, 'type' => 'NUMERIC' ],
        ],
    ] );

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Pull mgk_booking data attached to order line items.
    $booking_meta = [];
    foreach ( $order->get_items() as $item ) {
        $raw = $item->get_meta( '_mgk_teacher_id' );
        if ( $raw ) {
            $booking_meta['teacher_id']   = (int) $item->get_meta( '_mgk_teacher_id' );
            $booking_meta['teacher_name'] = wc_clean( (string) $item->get_meta( '_mgk_teacher_name' ) );
            $booking_meta['teacher_slug'] = sanitize_title( (string) $item->get_meta( '_mgk_teacher_slug' ) );
            $booking_meta['parent_name']  = wc_clean( (string) $item->get_meta( '_mgk_parent_name' ) );
            $booking_meta['phone']        = wc_clean( (string) $item->get_meta( '_mgk_phone' ) );
            $booking_meta['level']        = wc_clean( (string) $item->get_meta( '_mgk_level' ) );
            $booking_meta['subject']      = wc_clean( (string) $item->get_meta( '_mgk_subject' ) );
            $booking_meta['schedule']     = wc_clean( (string) $item->get_meta( '_mgk_schedule' ) );
            $booking_meta['package_type'] = wc_clean( (string) $item->get_meta( '_mgk_package_type' ) );
            break;
        }
    }

    if ( $existing ) {
        // Update state only.
        $booking_id = $existing[0];
        $new_state  = ( $to_status === 'completed' ) ? 'lesson_scheduled' : 'paid';
        update_post_meta( $booking_id, 'mgk_lead_state', $new_state );
        return;
    }

    $title = sprintf( 'Booking #%d — %s', $order_id, $booking_meta['teacher_name'] ?? 'Tutor' );

    $booking_id = wp_insert_post( [
        'post_type'   => 'mg_booking',
        'post_title'  => $title,
        'post_status' => 'publish',
        'post_author' => $order->get_customer_id() ?: 0,
    ], true );

    if ( is_wp_error( $booking_id ) ) {
        return;
    }

    $initial_state = ( $to_status === 'completed' ) ? 'lesson_scheduled' : 'paid';

    update_post_meta( $booking_id, 'mgk_wc_order_id',   $order_id );
    update_post_meta( $booking_id, 'mgk_lead_state',     $initial_state );
    update_post_meta( $booking_id, 'mgk_order_total',    $order->get_total() );
    update_post_meta( $booking_id, 'mgk_order_currency', get_woocommerce_currency() );

    foreach ( $booking_meta as $key => $value ) {
        update_post_meta( $booking_id, 'mgk_' . $key, $value );
    }

    do_action( 'mgk_booking_created', $booking_id, $order_id, $booking_meta );
}, 10, 3 );
