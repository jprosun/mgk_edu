<?php
/**
 * S11 — Checkout · Pay — LOCKED DATA CORE.
 * =============================================
 * The SHARED payment page for every paid service (trial lesson, lesson package,
 * and any future payable service). It is service-agnostic: the amounts always
 * follow the booking row's `price_amount` (the single source of truth for the
 * charge) and the per-service copy (line label, secure-nav label, slotless flag)
 * comes from ONE place — mgk_pay_item_descriptor() — keyed by the booking's
 * `lesson_type` and extensible via the `mgk_pay_item_types` filter.
 *
 * Step 3 (final) of the booking flow (PAY). Shows the order summary with stacked
 * discounts + GST, an account auto-create email capture, the payment method
 * (PayNow QR / Card fallback), terms consent, and the pay CTA. On confirm it
 * moves to S12 (confirmation).
 *
 * Per the MGK 3-layer rule (ONBOARDING §1.5, PLAYBOOK §3.5): Elementor controls
 * presentation only. Everything in this file is LOCKED:
 *   - order summary rows / amounts (trial price, discount stack, GST, total)
 *   - discount rules + stacking cap (BR-05/06)
 *   - payment methods + reference / corporate UEN / surcharge rules
 *   - terms requirement + the FR-* business references
 *   - payment status states (processing/success/failed/ref-mismatch — FR-PAY-*)
 *   - account auto-create (OTP / magic-link, FR-BOOK-07 / BR-22) and S12 route
 *
 * Reuses inc/mgk-select-tutor.php (trial offer + base breakdown + booking
 * context + progress) and inc/mgk-slots.php (slot hold + selected slot). The
 * three-step progress is rendered at step 3 here. No real payment / email.
 *
 * Shortcodes are registered in this file; the thin Elementor widgets live in
 * inc/mgk-elementor.php and only forward SAFE copy + Style.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_CARD_SURCHARGE_PCT' ) ) define( 'MGK_CARD_SURCHARGE_PCT', 2 );   // FR-PAY-05
if ( ! defined( 'MGK_3DS_THRESHOLD' ) )      define( 'MGK_3DS_THRESHOLD', 1000 );      // FR-PAY-05
if ( ! defined( 'MGK_GST_PCT' ) )            define( 'MGK_GST_PCT', 9 );               // BR-04
if ( ! defined( 'MGK_DISCOUNT_STACK_CAP' ) ) define( 'MGK_DISCOUNT_STACK_CAP', 25 );   // BR-05/06

/* ── Booking context (reuse S09/S10), LOCKED ─────────────── */

/**
 * Resolve the pay context from the request (?lead=&tutor=&slot=&hold=).
 * Tutor / slot DATA is never trusted from the client beyond these identifiers.
 */
function mgk_get_pay_context_from_request() {
    $ctx = function_exists( 'mgk_get_booking_context_from_request' )
        ? mgk_get_booking_context_from_request()
        : [ 'lead_token' => '', 'tutor_slug' => '', 'proposal_id' => '' ];
    $ctx['slot_id']    = function_exists( 'mgk_get_query_filter' ) ? (string) mgk_get_query_filter( 'slot', '' ) : '';
    $ctx['hold_token'] = function_exists( 'mgk_get_query_filter' ) ? (string) mgk_get_query_filter( 'hold', '' ) : '';
    // Real engine booking (id OR booking_code). Package checkout returns a code;
    // S10 trial holds usually carry a numeric id. Normalize both to booking_id.
    $raw_booking = function_exists( 'mgk_get_query_filter' ) ? (string) mgk_get_query_filter( 'booking', '' ) : '';
    $ctx['booking']      = $raw_booking;
    $ctx['booking_id']   = 0;
    $ctx['booking_code'] = '';
    if ( $raw_booking !== '' ) {
        $row = mgk_pay_resolve_booking_row( $raw_booking );
        if ( $row ) {
            $ctx['booking_id']   = (int) $row['id'];
            $ctx['booking_code'] = (string) $row['booking_code'];
            if ( empty( $ctx['tutor_slug'] ) && ! empty( $row['tutor_post_id'] ) ) {
                $post = get_post( (int) $row['tutor_post_id'] );
                if ( $post ) $ctx['tutor_slug'] = $post->post_name;
            }
        } elseif ( ctype_digit( $raw_booking ) ) {
            $ctx['booking_id'] = (int) $raw_booking;
        }
    }
    return $ctx;
}

function mgk_pay_resolve_booking_row( $booking ) {
    $booking = trim( (string) $booking );
    if ( $booking === '' ) return null;
    if ( ctype_digit( $booking ) && function_exists( 'mgk_get_booking_row' ) ) {
        return mgk_get_booking_row( (int) $booking );
    }
    if ( function_exists( 'mgk_get_booking_by_code' ) ) {
        return mgk_get_booking_by_code( $booking );
    }
    return null;
}

function mgk_pay_tutor_from_booking_row( $row ) {
    $tutor_id = (int) ( $row['tutor_post_id'] ?? 0 );
    $post = $tutor_id ? get_post( $tutor_id ) : null;
    return [
        'id'         => $tutor_id,
        'name'       => $post ? $post->post_title : 'Your tutor',
        'slug'       => $post ? $post->post_name : '',
        'rate_num'   => $tutor_id ? (int) get_post_meta( $tutor_id, 'mgk_rate_num', true ) : 0,
        'avatar_url' => ( $tutor_id && has_post_thumbnail( $tutor_id ) ) ? get_the_post_thumbnail_url( $tutor_id, 'thumbnail' ) : '',
    ];
}

function mgk_pay_is_package_row( $row ) {
    return mgk_pay_item_descriptor( $row )['is_package'];
}

/* ── Generic payable-item descriptor (SHARED checkout, LOCKED) ──
 *
 * Single source of truth for the per-service presentation of the shared payment
 * page (S11). EVERY service that goes through checkout — trial lesson, lesson
 * package, and any future paid service — is described here, keyed by the booking
 * row's `lesson_type`. The summary line label, the secure-nav label, the
 * "slotless" flag and the item "kind" all come from this ONE place, so the page
 * is service-agnostic: the amounts already follow the booking row's
 * `price_amount`, and the copy follows this descriptor.
 *
 * Register a new payable service with the `mgk_pay_item_types` filter — no
 * template-part edits needed. Unknown types fall back to a generic checkout
 * label derived from the lesson_type token.
 */

/**
 * @param array|string $row_or_type  booking row OR a lesson_type string
 * @return array{kind:string,line_label:string,secure_phrase:string,secure_with:string,slotless:bool,lessons:int,is_package:bool}
 */
function mgk_pay_item_descriptor( $row_or_type ) {
    $lesson_type = is_array( $row_or_type )
        ? (string) ( $row_or_type['lesson_type'] ?? 'TRIAL' )
        : (string) $row_or_type;
    if ( $lesson_type === '' ) $lesson_type = 'TRIAL';

    $types = apply_filters( 'mgk_pay_item_types', [
        'TRIAL' => [
            'kind'          => 'trial',
            'line_label'    => 'Trial lesson',          // duration "(1.5h)" appended by the summary
            'secure_phrase' => 'BOOKING TRIAL',
            'secure_with'   => 'BOOKING TRIAL WITH ',
            'slotless'      => false,
        ],
        'PACKAGE_8' => [
            'kind'          => 'package',
            'line_label'    => '8-lesson package',
            'secure_phrase' => 'BUYING LESSON PACKAGE',
            'secure_with'   => 'BUYING PACKAGE WITH ',
            'slotless'      => true,
            'lessons'       => 8,
        ],
        'PACKAGE_16' => [
            'kind'          => 'package',
            'line_label'    => '16-lesson package',
            'secure_phrase' => 'BUYING LESSON PACKAGE',
            'secure_with'   => 'BUYING PACKAGE WITH ',
            'slotless'      => true,
            'lessons'       => 16,
        ],
    ], $row_or_type );

    if ( isset( $types[ $lesson_type ] ) ) {
        $d = $types[ $lesson_type ];
    } else {
        // Generic fallback for any future / unregistered payable service: a
        // human label from the token, treated as a slot-based single item.
        $human = ucwords( strtolower( str_replace( [ '_', '-' ], ' ', $lesson_type ) ) );
        $d = [
            'kind'          => 'service',
            'line_label'    => $human ?: 'Service',
            'secure_phrase' => 'SECURE CHECKOUT',
            'secure_with'   => 'CHECKOUT WITH ',
            'slotless'      => false,
        ];
    }

    return [
        'kind'          => (string) ( $d['kind'] ?? 'service' ),
        'line_label'    => (string) ( $d['line_label'] ?? 'Service' ),
        'secure_phrase' => (string) ( $d['secure_phrase'] ?? 'SECURE CHECKOUT' ),
        'secure_with'   => (string) ( $d['secure_with'] ?? 'CHECKOUT WITH ' ),
        'slotless'      => ! empty( $d['slotless'] ),
        'lessons'       => (int) ( $d['lessons'] ?? 0 ),
        'is_package'    => ( $d['kind'] ?? '' ) === 'package',
    ];
}

/**
 * Secure-checkout nav label for a payable item, e.g.
 * "🔒 BOOKING TRIAL WITH MS SIM" / "🔒 BUYING PACKAGE WITH MS SIM" /
 * "🔒 SECURE CHECKOUT". Driven entirely by the item descriptor so the strings
 * live in one place.
 *
 * @param array|string $row_or_type booking row OR lesson_type
 * @param string       $tutor_name  optional, to personalise "… WITH <NAME>"
 */
function mgk_pay_secure_label( $row_or_type, $tutor_name = '' ) {
    $d     = mgk_pay_item_descriptor( $row_or_type );
    $label = $d['secure_phrase'];
    if ( $tutor_name && preg_match( '/\b(Ms|Mr|Mrs|Dr)\.?\s+([A-Z][a-z]+)/', (string) $tutor_name, $m ) ) {
        $label = $d['secure_with'] . strtoupper( $m[1] . ' ' . $m[2] );
    }
    return '🔒 ' . $label;
}

/* ── Discount stack + order summary (LOCKED) ─────────────── */

/**
 * Demo discount eligibility. In production these come from the lead/account
 * (existing customer flags); here we read optional query flags so the page can
 * be previewed in each state, defaulting to the screenshot (sibling + returning).
 *
 * @return array<int,array{key:string,label:string,pct:int}>
 */
function mgk_get_trial_discounts( $context = [] ) {
    $sibling   = function_exists( 'mgk_get_query_filter' ) ? mgk_get_query_filter( 'sibling', '1' )   : '1';
    $returning = function_exists( 'mgk_get_query_filter' ) ? mgk_get_query_filter( 'returning', '1' ) : '1';
    $out = [];
    if ( $sibling !== '0' )   $out[] = [ 'key' => 'sibling',   'label' => 'Sibling discount',  'pct' => 3 ];
    if ( $returning !== '0' ) $out[] = [ 'key' => 'returning', 'label' => 'Returning parent', 'pct' => 5 ];
    return $out;
}

/**
 * Compute the full trial order summary with stacked discounts + GST.
 * Trial discount (S09 offer) applies first; loyalty discounts (sibling /
 * returning) stack on the discounted line but the EXTRA stack is capped at
 * MGK_DISCOUNT_STACK_CAP percent of the trial line (BR-05/06).
 *
 * @return array  rows[], subtotal, total, gst_note, cap_note, due, due_num,
 *                base, trial_price, total_num
 */
function mgk_get_pay_order_summary( $tutor = null, $context = [] ) {
    if ( $tutor === null && function_exists( 'mgk_get_selected_tutor_for_booking' ) ) {
        $context = $context ?: mgk_get_pay_context_from_request();
        $tutor = mgk_get_selected_tutor_for_booking( $context['lead_token'] ?? '', $context['tutor_slug'] ?? '' );
    }
    $offer = function_exists( 'mgk_calculate_trial_offer' )
        ? mgk_calculate_trial_offer( $tutor )
        : [ 'old_price' => 65, 'trial_price' => 39, 'saving' => 26, 'discount_percent' => 40, 'duration_min' => 90 ];

    $money = function ( $n ) { return '$' . number_format( (float) $n, 2 ); };
    $dur_h = rtrim( rtrim( number_format( $offer['duration_min'] / 60, 1 ), '0' ), '.' );
    $gst_pct = function_exists( 'mgk_discount_rule' ) ? (int) mgk_discount_rule( 'gst_pct', MGK_GST_PCT ) : (int) MGK_GST_PCT;
    $gst_inclusive = function_exists( 'mgk_discount_rule' ) ? ( (int) mgk_discount_rule( 'gst_inclusive', 1 ) === 1 ) : true;
    $gst_note = $gst_inclusive
        ? sprintf( 'INCL. %d%% GST (BR-04) · SGD', $gst_pct )
        : sprintf( '+ %d%% GST (BR-04) · SGD', $gst_pct );

    $base       = (float) $offer['old_price'];
    $trial_line = (float) $offer['trial_price'];   // after the headline trial discount
    $trial_off  = (float) $offer['saving'];

    $rows = [
        [ 'label' => sprintf( 'Trial lesson (%sh)', $dur_h ), 'value' => $money( $base ), 'accent' => false, 'strong' => false ],
        [ 'label' => sprintf( 'Trial discount (%d%%)', (int) $offer['discount_percent'] ), 'value' => '-' . $money( $trial_off ), 'accent' => true, 'strong' => false ],
    ];

    // A real HELD booking's price_amount is what Stripe will actually charge, so
    // it is the single source of truth for the amount due — the displayed total
    // must never differ from the charged amount. We re-derive the trial-discount
    // line from it (base → charged) and skip the demo loyalty stack, which is only
    // ever a preview affordance (real loyalty discounts would have to be applied at
    // hold time to be charged).
    $booking_price = null;
    $booking_voucher = '';
    $booking_line_label = sprintf( 'Trial lesson (%sh)', $dur_h );
    $bid = (int) ( $context['booking_id'] ?? 0 );
    if ( $bid && function_exists( 'mgk_get_booking_row' ) ) {
        $brow = mgk_get_booking_row( $bid );
        if ( $brow && isset( $brow['price_amount'] ) && in_array( $brow['status'], [ 'HELD', 'PENDING_PAYMENT', 'CONFIRMED', 'MANUAL_REVIEW' ], true ) ) {
            $booking_price   = (float) $brow['price_amount'];
            $booking_voucher = (string) ( $brow['voucher_code'] ?? '' );
            // Service-agnostic line label from the payable-item descriptor.
            // Slot-based items (trial / single) append the duration; slotless
            // items (packages) carry their own self-describing label.
            $desc = mgk_pay_item_descriptor( $brow );
            $booking_line_label = $desc['line_label'];
            if ( ! $desc['slotless'] && $dur_h !== '' ) {
                $booking_line_label .= sprintf( ' (%sh)', $dur_h );
            }
        }
    }

    $capped = false;
    if ( $booking_price !== null ) {
        // Charge-authoritative: rebuild the WHOLE breakdown from the booking's
        // stored fields (base_amount + discount_applied JSON + price_amount) so the
        // rows shown are EXACTLY the discounts that were charged — the unified
        // quote (mgk_quote) result, frozen at hold time.
        $stored_base = isset( $brow['base_amount'] ) ? (float) $brow['base_amount'] : 0;
        if ( $stored_base > 0 ) $base = $stored_base;
        $applied = [];
        if ( ! empty( $brow['discount_applied'] ) ) {
            $decoded = json_decode( (string) $brow['discount_applied'], true );
            if ( is_array( $decoded ) ) $applied = $decoded;
        }
        if ( $applied ) {
            $sum_applied = 0.0;
            $has_headline = false;
            foreach ( $applied as $d ) {
                $sum_applied += (float) ( $d['amount'] ?? 0 );
                $key = (string) ( $d['key'] ?? '' );
                $lbl = strtolower( (string) ( $d['label'] ?? '' ) );
                if ( strpos( $key, 'headline:' ) === 0 || strpos( $lbl, 'trial discount' ) !== false ) {
                    $has_headline = true;
                }
            }
            $missing_headline = round( $base - $booking_price - $sum_applied, 2 );
            if ( ! $has_headline && $missing_headline > 0.01 ) {
                $pct = function_exists( 'mgk_discount_rule' ) ? (int) mgk_discount_rule( 'trial_pct', (int) $offer['discount_percent'] ) : (int) $offer['discount_percent'];
                array_unshift( $applied, [
                    'key'    => 'headline:trial',
                    'label'  => sprintf( 'Trial discount (%d%%)', $pct ),
                    'pct'    => $pct,
                    'amount' => $missing_headline,
                ] );
            }
            $sum_applied = 0.0;
            foreach ( $applied as $d ) {
                $sum_applied += (float) ( $d['amount'] ?? 0 );
            }
            $line_subtotal = max( 0, round( $base - $sum_applied, 2 ) );
        }
        $rows = [ [ 'label' => $booking_line_label, 'value' => $money( $base ), 'accent' => false, 'strong' => false ] ];
        if ( $applied ) {
            foreach ( $applied as $d ) {
                $lbl = (string) ( $d['label'] ?? 'Discount' );
                $pct = (int) ( $d['pct'] ?? 0 );
                $has_pct = $pct && strpos( $lbl, '(' . $pct . '%' ) !== false;
                $rows[] = [
                    'label'  => ( $pct && ! $has_pct ) ? sprintf( '%s (%d%%)', $lbl, $pct ) : $lbl,
                    'value'  => '-' . $money( (float) ( $d['amount'] ?? 0 ) ),
                    'accent' => true, 'strong' => false,
                ];
            }
        } else {
            // Legacy booking with no stored breakdown → single derived discount line.
            $line_subtotal = $booking_price;
            if ( ! $gst_inclusive && $gst_pct > 0 ) {
                $line_subtotal = round( $booking_price / ( 1 + $gst_pct / 100 ), 2 );
            }
            $disc_pct = $base > 0 ? (int) round( ( $base - $line_subtotal ) / $base * 100 ) : (int) $offer['discount_percent'];
            $rows[]   = [ 'label' => sprintf( 'Trial discount (%d%%)', $disc_pct ), 'value' => '-' . $money( max( 0, $base - $line_subtotal ) ), 'accent' => true, 'strong' => false ];
        }
        $trial_line = ! empty( $line_subtotal ) ? (float) $line_subtotal : $booking_price;
        $subtotal   = $trial_line;
        $total      = $booking_price;
    } else {
        // Preview/demo: show the stacked loyalty discounts, capped (BR-05/06).
        $discounts   = mgk_get_trial_discounts( $context );
        $cap_amount  = $trial_line * ( MGK_DISCOUNT_STACK_CAP / 100 );
        $stack_taken = 0.0;
        foreach ( $discounts as $d ) {
            $amt = $trial_line * ( (int) $d['pct'] / 100 );
            if ( $stack_taken + $amt > $cap_amount ) {
                $amt = max( 0, $cap_amount - $stack_taken );
                $capped = true;
            }
            $stack_taken += $amt;
            $rows[] = [
                'label'  => sprintf( '%s (%d%%)', $d['label'], (int) $d['pct'] ),
                'value'  => '-' . $money( $amt ),
                'accent' => true, 'strong' => false,
            ];
        }
        $subtotal = max( 0, $trial_line - $stack_taken );
        $total    = $gst_inclusive ? $subtotal : round( $subtotal + ( $subtotal * $gst_pct / 100 ), 2 );
    }

    $cap_note = $capped ? sprintf( 'Stacked discounts capped at %d%% (BR-05/06)', MGK_DISCOUNT_STACK_CAP ) : '';

    return [
        'rows'       => $rows,
        'subtotal'   => $money( $subtotal ),
        'total'      => $money( $total ),
        'due'        => $money( $total ),
        'due_num'    => round( $total, 2 ),
        'total_num'  => round( $total, 2 ),
        'base'       => $base,
        'trial_price'=> $trial_line,
        'gst_note'   => $gst_note,
        'cap_note'   => $cap_note,
        'duration_h' => $dur_h,
        'voucher_code' => $booking_voucher,
    ];
}

/**
 * Card surcharge for an amount (FR-PAY-05). Returns ['pct','amount','threshold',
 * 'needs_3ds','total_with_surcharge'].
 */
function mgk_get_card_surcharge( $amount ) {
    $amount = (float) $amount;
    $pct    = (int) MGK_CARD_SURCHARGE_PCT;
    $sur    = round( $amount * ( $pct / 100 ), 2 );
    return [
        'pct'                  => $pct,
        'amount'               => $sur,
        'threshold'            => (int) MGK_3DS_THRESHOLD,
        'needs_3ds'            => $amount > MGK_3DS_THRESHOLD,
        'total_with_surcharge' => round( $amount + $sur, 2 ),
    ];
}

/* ── Payment methods + reference (LOCKED) ────────────────── */

/** Payment reference for this booking (deterministic, demo-safe). */
function mgk_get_payment_reference( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_pay_context_from_request() );
    if ( ! empty( $context['booking_code'] ) ) {
        return (string) $context['booking_code'];
    }
    if ( ! empty( $context['booking_id'] ) && function_exists( 'mgk_get_booking_row' ) ) {
        $row = mgk_get_booking_row( (int) $context['booking_id'] );
        if ( $row && ! empty( $row['booking_code'] ) ) return (string) $row['booking_code'];
    }
    $tutor   = sanitize_title( $context['tutor_slug'] ?? '' );
    $short   = strtoupper( substr( preg_replace( '/[^a-z0-9]/i', '', $tutor ?: 'tutor' ), 0, 4 ) );
    $lead    = strtoupper( substr( preg_replace( '/[^a-z0-9]/i', '', (string) ( $context['lead_token'] ?? '' ) ), 0, 4 ) );
    $lead    = $lead ?: '8842';
    return 'TRIAL-' . $short . '-' . $lead;
}

/** Corporate payee details (agency setting, falls back to demo). */
function mgk_get_payee_details() {
    $uen   = function_exists( 'mgk_site_setting' ) ? (string) mgk_site_setting( 'paynow_uen' )   : '';
    $payee = function_exists( 'mgk_site_setting' ) ? (string) mgk_site_setting( 'paynow_payee' ) : '';
    return [
        'uen'   => $uen   ?: '2024XXXXXX-A',
        'payee' => $payee ?: 'MARGICK TUITION PTE LTD',
    ];
}

/**
 * Available payment methods — gated by the real payment config (tight logic):
 * only methods that are BOTH enabled AND configured appear (mgk_payment_config).
 * Falls back to showing both as a wireframe when the config layer is absent.
 *
 * @return array<int,array{key:string,label:string,tag:string}>
 */
function mgk_get_payment_methods() {
    if ( function_exists( 'mgk_payment_config' ) ) {
        $cfg = mgk_payment_config();
        if ( ! empty( $cfg['methods'] ) ) {
            return $cfg['methods'];
        }
    }
    // Wireframe fallback (no config / preview).
    return [
        [ 'key' => 'paynow', 'label' => 'PayNow QR', 'tag' => 'INSTANT' ],
        [ 'key' => 'card',   'label' => 'Card (Stripe)', 'tag' => '' ],
    ];
}

/* ── Account auto-create copy refs (LOCKED business rules) ── */

function mgk_get_account_create_refs() {
    return [
        'otp_note' => "We'll email a 6-digit OTP + magic link to verify. No password needed (FR-BOOK-07 / BR-22).",
        'ref_book' => 'FR-BOOK-07',
        'ref_br'   => 'BR-22',
    ];
}

/* ── Terms / consent (LOCKED requirement) ────────────────── */

function mgk_get_terms_links() {
    return [
        'terms'  => [ 'label' => 'Terms',                 'url' => home_url( '/terms/' ) ],
        'refund' => [ 'label' => 'Refund Policy (BR-07)', 'url' => home_url( '/refund-policy/' ) ],
        'pdpa'   => [ 'label' => 'PDPA Notice',           'url' => home_url( '/pdpa/' ) ],
    ];
}

/* ── Payment status states (LOCKED — FR-PAY-03/05/08) ────── */

/**
 * The locked set of post-submit payment states the UI can be in. Presentation
 * (copy) is overridable per-state in the partial but the STATE MACHINE and the
 * FR references are locked here.
 *
 * @return array<string,array{ref:string,...}>
 */
function mgk_get_payment_states() {
    return [
        'processing' => [
            'ref'   => 'FR-PAY-03',
            'title' => 'Confirming your payment…',
            'note'  => 'WEBHOOK CONFIRM < 5S (FR-PAY-03) · DON’T CLOSE THIS TAB',
        ],
        'success' => [
            'ref'   => 'S12',
            'title' => 'Paid! Redirecting to confirmation…',
            'note'  => '',
        ],
        'failed' => [
            'ref'   => '',
            'title' => 'Payment didn’t go through. Your slot is still held ({hold}).',
            'note'  => 'Try again',
        ],
        'mismatch' => [
            'ref'   => 'FR-PAY-08',
            'title' => 'We received a payment but the reference didn’t match. Sent to manual review — our team will confirm within 1h. No need to pay again.',
            'note'  => '',
        ],
    ];
}

/* ── S12 confirmation route (LOCKED) ─────────────────────── */

function mgk_get_s12_confirm_url( $context = [] ) {
    $context = (array) ( $context ?: mgk_get_pay_context_from_request() );
    $args = array_filter( [
        'lead'    => $context['lead_token'] ?? '',
        'tutor'   => $context['tutor_slug'] ?? '',
        'slot'    => $context['slot_id'] ?? '',
        'booking' => $context['booking_code'] ?? ( $context['booking_id'] ?? 0 ),
        'ref'     => mgk_get_payment_reference( $context ),
    ] );
    return add_query_arg( $args, home_url( '/trial-confirmed/' ) );
}

/**
 * If the parent lands on S11 with a booking that is ALREADY confirmed (e.g. they
 * returned from Stripe / mock-pay), send them straight to S12. The page never
 * shows "pay" for a paid booking.
 */
add_action( 'template_redirect', function () {
    if ( ! function_exists( 'is_page' ) || ! ( is_page( [ 'trial-pay', 'pay' ] ) ) ) return;
    $ctx = mgk_get_pay_context_from_request();
    $bid = (int) ( $ctx['booking_id'] ?? 0 );
    if ( ! $bid || ! function_exists( 'mgk_get_booking_row' ) ) return;
    $row = mgk_get_booking_row( $bid );
    if ( $row && in_array( $row['status'], [ 'CONFIRMED', 'COMPLETED' ], true ) ) {
        wp_safe_redirect( mgk_get_s12_confirm_url( $ctx ) );
        exit;
    }
} );

/* ── Build + validate the full S11 view ──────────────────── */

/**
 * @return array  status: ok|not_found|expired|unavailable, + context, tutor,
 *                 selected, hold, summary, methods, reference, payee,
 *                 surcharge, states, account, terms
 */
function mgk_get_pay_view() {
    $context = mgk_get_pay_context_from_request();
    $booking_id = (int) ( $context['booking_id'] ?? 0 );

    // Package orders are slotless booking rows. Do not run them through the
    // trial S09/S10 selected-slot flow, otherwise the page falls back to trial
    // copy/amounts. The booking row is the source of truth.
    if ( $booking_id && function_exists( 'mgk_get_booking_row' ) ) {
        $booking_row = mgk_get_booking_row( $booking_id );
        if ( $booking_row && mgk_pay_is_package_row( $booking_row ) ) {
            if ( in_array( $booking_row['status'], [ 'CONFIRMED', 'COMPLETED' ], true ) ) {
                return [ 'status' => 'confirmed', 'context' => $context, 'booking_id' => $booking_id ];
            }
            if ( ! in_array( $booking_row['status'], [ 'PENDING_PAYMENT', 'HELD', 'MANUAL_REVIEW' ], true ) ) {
                return [ 'status' => 'expired', 'context' => $context, 'booking_id' => $booking_id ];
            }

            $tutor = mgk_pay_tutor_from_booking_row( $booking_row );
            $summary = mgk_get_pay_order_summary( $tutor, $context );
            $desc    = mgk_pay_item_descriptor( $booking_row );
            $lessons = $desc['lessons'] ?: ( function_exists( 'mgk_confirm_package_lessons' )
                ? mgk_confirm_package_lessons( (string) $booking_row['lesson_type'] )
                : ( (string) $booking_row['lesson_type'] === 'PACKAGE_16' ? 16 : 8 ) );
            $email = '';
            if ( ! empty( $booking_row['parent_user_id'] ) ) {
                $u = get_user_by( 'id', (int) $booking_row['parent_user_id'] );
                if ( $u ) $email = $u->user_email;
            }

            return [
                'status'       => 'ok',
                'context'      => $context,
                'tutor'        => $tutor,
                'selected'     => [ 'id' => 'package-' . $booking_id, 'label' => 'Schedule lesson-by-lesson after purchase' ],
                'active_label' => $lessons ? sprintf( '%d-lesson package', $lessons ) : 'Package',
                'duration_min' => 0,
                'hold'         => [ 'active' => false, 'slot_id' => '', 'hold_token' => (string) $booking_id, 'remaining' => 0 ],
                'hold_seconds' => 0,
                'summary'      => $summary,
                'methods'      => mgk_get_payment_methods(),
                'reference'    => mgk_get_payment_reference( $context ),
                'payee'        => mgk_get_payee_details(),
                'surcharge'    => mgk_get_card_surcharge( $summary['total_num'] ),
                'states'       => mgk_get_payment_states(),
                'account'      => array_merge( mgk_get_account_create_refs(), [ 'email' => $email ] ),
                'terms'        => mgk_get_terms_links(),
                'booking_id'   => $booking_id,
                'booking_status' => (string) $booking_row['status'],
                'is_package_order' => true,
                'item_kind'    => $desc['kind'],
                'secure_label' => mgk_pay_secure_label( $booking_row, $tutor['name'] ?? '' ),
            ];
        }
    }

    // Reuse the S09 view to resolve + validate tutor / proposal state.
    $sel = function_exists( 'mgk_get_select_tutor_view' ) ? mgk_get_select_tutor_view() : [ 'status' => 'not_found' ];
    if ( ( $sel['status'] ?? '' ) !== 'ok' ) {
        return [ 'status' => $sel['status'] ?? 'not_found', 'context' => $context ];
    }
    $tutor = $sel['tutor'];

    // Reuse the S10 view for the selected slot + hold (so the summary line +
    // countdown match what the parent picked).
    $slotview = function_exists( 'mgk_get_pick_slot_view' ) ? mgk_get_pick_slot_view() : [];
    $selected = $slotview['selected'] ?? null;
    $hold     = $slotview['hold'] ?? [];

    $summary   = mgk_get_pay_order_summary( $tutor, $context );
    $surcharge = mgk_get_card_surcharge( $summary['total_num'] );

    // ── Real engine booking overlay (Phase 0.5) ──
    // When S10 carried a real ?booking=<id>, bind the live booking: its actual
    // status drives the page (so an expired/paid hold is reflected), its slot
    // label feeds the summary, and the countdown comes from the real hold. The
    // presentation/markup is unchanged — only the DATA source is the engine.
    $booking_status = '';
    $row = null;
    if ( $booking_id && function_exists( 'mgk_get_booking_row' ) ) {
        $row = mgk_get_booking_row( $booking_id );
        if ( $row ) {
            $booking_status = $row['status'];

            // Already paid/confirmed → send straight to S12.
            if ( in_array( $row['status'], [ 'CONFIRMED', 'COMPLETED' ], true ) ) {
                return [ 'status' => 'confirmed', 'context' => $context, 'booking_id' => $booking_id ];
            }
            // Hold gone (expired/cancelled) → surface the unavailable state.
            if ( ! in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT' ], true ) ) {
                return [ 'status' => 'expired', 'context' => $context, 'booking_id' => $booking_id ];
            }

            // Real selected-slot label from the booking row (local time).
            $selected = mgk_pay_selected_from_booking( $row ) ?: $selected;

            // Real hold countdown.
            if ( ! empty( $row['hold_expires_at_utc'] ) && $row['status'] === 'HELD' ) {
                $remaining = max( 0, strtotime( $row['hold_expires_at_utc'] . ' UTC' ) - time() );
                $hold = [ 'active' => $remaining > 0, 'slot_id' => $row['slot_key'], 'hold_token' => (string) $booking_id, 'remaining' => (int) $remaining, 'expires_at' => strtotime( $row['hold_expires_at_utc'] . ' UTC' ) ];
            }
        }
    }
    $account = mgk_get_account_create_refs();
    if ( ! empty( $row['parent_user_id'] ) ) {
        $u = get_user_by( 'id', (int) $row['parent_user_id'] );
        if ( $u ) $account['email'] = $u->user_email;
    }

    return [
        'status'    => 'ok',
        'context'   => $context,
        'tutor'     => $tutor,
        'selected'  => $selected,
        'active_label' => $slotview['active_label'] ?? '',
        'duration_min' => $slotview['duration_min'] ?? 90,
        'hold'      => $hold,
        'hold_seconds' => $slotview['hold_seconds'] ?? 600,
        'summary'   => $summary,
        'methods'   => mgk_get_payment_methods(),
        'reference' => mgk_get_payment_reference( $context ),
        'payee'     => mgk_get_payee_details(),
        'surcharge' => $surcharge,
        'states'    => mgk_get_payment_states(),
        'account'   => $account,
        'terms'     => mgk_get_terms_links(),
        'booking_id'     => $booking_id,
        'booking_status' => $booking_status,
        'item_kind'      => mgk_pay_item_descriptor( $row ?: 'TRIAL' )['kind'],
        'secure_label'   => mgk_pay_secure_label( $row ?: 'TRIAL', $tutor['name'] ?? '' ),
    ];
}

/** Build a {id,label} selected-slot from a real booking row (local SGT). */
function mgk_pay_selected_from_booking( $row ) {
    if ( empty( $row['start_at_utc'] ) ) return null;
    try {
        $tz = function_exists( 'mgk_booking_tz' ) ? mgk_booking_tz() : new DateTimeZone( 'Asia/Singapore' );
        $s = new DateTime( $row['start_at_utc'], new DateTimeZone( 'UTC' ) ); $s->setTimezone( $tz );
        $e = new DateTime( $row['end_at_utc'], new DateTimeZone( 'UTC' ) ); $e->setTimezone( $tz );
        $fmt = function ( $d ) {
            $h = (int) $d->format( 'G' ); $min = $d->format( 'i' );
            $ap = $h >= 12 ? 'PM' : 'AM'; $h12 = $h % 12; if ( $h12 === 0 ) $h12 = 12;
            return $h12 . ':' . $min . ' ' . $ap;
        };
        $sl = $fmt( $s ); $el = $fmt( $e );
        if ( substr( $sl, -2 ) === substr( $el, -2 ) ) $sl = trim( substr( $sl, 0, -2 ) );
        return [ 'id' => $row['slot_key'], 'label' => $sl . '–' . $el ];
    } catch ( Exception $ex ) {
        return null;
    }
}

/* ── Tracking ────────────────────────────────────────────── */

function mgk_pay_track( $event, $props = [] ) {
    if ( ! function_exists( 'mgk_track_event' ) ) return;
    mgk_track_event( $event, array_filter( (array) $props ) );
}

/* ── Render helper + shortcodes (thin shells → partials) ─── */

function mgk_pay_part( $part, $atts = [] ) {
    $atts = is_array( $atts ) ? array_filter( $atts, function ( $v ) { return $v !== '' && $v !== null; } ) : [];
    if ( $part === 'pay' ) {
        return mgk_render_part( 'template-parts/sections/booking/pay', $atts );
    }
    $view = mgk_get_pay_view();
    return mgk_render_part( 'template-parts/sections/booking/' . $part, array_merge( $view, $atts ) );
}

/** Composite — whole S11 page. */
add_shortcode( 'mgk_pay', function ( $atts ) {
    return mgk_pay_part( 'pay', (array) $atts );
} );

/** Account auto-create (email capture + OTP note). */
add_shortcode( 'mgk_pay_account', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'subnote' => '', 'placeholder' => '', 'otp_note' => '', 'section_tag' => '',
    ], (array) $atts, 'mgk_pay_account' );
    return mgk_pay_part( 'pay-account', $atts );
} );

/** Order summary (slot line + stacked discounts + total). */
add_shortcode( 'mgk_pay_summary', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'subtotal_label' => '', 'total_label' => '', 'section_tag' => '',
    ], (array) $atts, 'mgk_pay_summary' );
    return mgk_pay_part( 'pay-summary', $atts );
} );

/** Payment method (PayNow QR / Card) + status panels. */
add_shortcode( 'mgk_pay_method', function ( $atts ) {
    $atts = shortcode_atts( [
        'heading' => '', 'paynow_help' => '', 'waiting_note' => '', 'section_tag' => '',
        'state' => '', // preview override: paynow|card|processing|success|failed|mismatch
    ], (array) $atts, 'mgk_pay_method' );
    return mgk_pay_part( 'pay-method', $atts );
} );

/** Terms consent. */
add_shortcode( 'mgk_pay_terms', function ( $atts ) {
    $atts = shortcode_atts( [ 'lead_text' => '' ], (array) $atts, 'mgk_pay_terms' );
    return mgk_pay_part( 'pay-terms', $atts );
} );

/** Pay CTA + reassurance. */
add_shortcode( 'mgk_pay_cta', function ( $atts ) {
    $atts = shortcode_atts( [ 'cta_label' => '', 'reassure' => '' ], (array) $atts, 'mgk_pay_cta' );
    return mgk_pay_part( 'pay-cta', $atts );
} );

/* ── Mock pay handler (no real charge in this phase) ─────── */

add_action( 'template_redirect', function () {
    $action = isset( $_GET['mgk_action'] ) ? sanitize_key( wp_unslash( $_GET['mgk_action'] ) ) : '';
    if ( $action !== 'mock_pay' ) return;

    $context = mgk_get_pay_context_from_request();
    wp_safe_redirect( mgk_get_s12_confirm_url( $context ) );
    exit;
} );
