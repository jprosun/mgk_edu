<?php
/**
 * MGK — Package purchase (pay-upfront → schedule-later).
 * ======================================================
 * The 8/16-lesson packages on a tutor profile are REAL purchases, not just a
 * price display. Model (owner decision 2026-06-15):
 *   - Pay upfront for N lessons → on confirmed payment an `mg_enrolment` is
 *     created with N lesson credits (lessons_total) + a validity window; the
 *     parent then schedules each lesson later from the dashboard.
 *   - Only a signed-in parent (who has an account / has trialed) may buy — the
 *     profile "Choose" CTA gates logged-out visitors to sign-in / trial first.
 *
 * Pricing/discounts come from the SAME engine as everything else (mgk_quote with
 * item_type package_8|package_16): package saving + sibling(pkg %) + returning
 * (real, requires a prior package) + voucher, capped, GST — so display === charge.
 *
 * Payment reuses the booking engine: a package order is a slotless row in
 * mgk_bookings (lesson_type PACKAGE_8/16, status PENDING_PAYMENT, no slot lock),
 * charged via the existing Stripe checkout. On `mgk_booking_confirmed` the
 * enrolment is materialised (idempotent via a booking event).
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_PACKAGE_VALIDITY_DAYS' ) ) define( 'MGK_PACKAGE_VALIDITY_DAYS', 180 );

/** Lessons in a plan. */
function mgk_package_plan_lessons( $plan ) {
	return $plan === 'package_16' ? 16 : 8;
}

/** Valid plan keys for purchase. */
function mgk_package_is_plan( $plan ) {
	return in_array( $plan, [ 'package_8', 'package_16' ], true );
}

/** plan key (package_8) ↔ enrolment plan_type (PACKAGE_8). */
function mgk_package_plan_type( $plan ) {
	return $plan === 'package_16' ? 'PACKAGE_16' : 'PACKAGE_8';
}
function mgk_package_plan_from_lesson_type( $lesson_type ) {
	if ( $lesson_type === 'PACKAGE_16' ) return 'package_16';
	if ( $lesson_type === 'PACKAGE_8' )  return 'package_8';
	return '';
}

/** Resolve a tutor post (mg_teacher) by slug → minimal shape for pricing. */
function mgk_package_resolve_tutor( $slug ) {
	$slug = sanitize_title( $slug );
	if ( ! $slug ) return null;
	$post = get_page_by_path( $slug, OBJECT, 'mg_teacher' );
	if ( ! $post ) return null;
	return [
		'id'       => (int) $post->ID,
		'name'     => $post->post_title,
		'slug'     => $post->post_name,
		'rate_num' => (int) get_post_meta( $post->ID, 'mgk_rate_num', true ),
	];
}

/* ─────────────────────────────────────────────────────────────────────────
 * Generic per-booking re-quote (trial OR package). Single place that maps a
 * stored booking row → the right mgk_quote() call, so the voucher endpoint and
 * the package order use identical pricing.
 * ───────────────────────────────────────────────────────────────────────── */
function mgk_engine_quote_for_booking( $row, $voucher_code = null ) {
	$lesson_type = (string) ( $row['lesson_type'] ?? 'TRIAL' );
	$tutor_id    = (int) ( $row['tutor_post_id'] ?? 0 );
	$code        = $voucher_code === null ? (string) ( $row['voucher_code'] ?? '' ) : (string) $voucher_code;

	$ctx = function_exists( 'mgk_engine_quote_context' )
		? mgk_engine_quote_context( [
			'parent_user_id' => (int) ( $row['parent_user_id'] ?? 0 ),
			'child_id'       => (int) ( $row['child_id'] ?? 0 ),
			'lead_id'        => (int) ( $row['lead_id'] ?? 0 ),
			'voucher_code'   => $code,
		], $tutor_id )
		: [ 'voucher_code' => $code ];
	$ctx['booking_id'] = (int) ( $row['id'] ?? 0 );

	$plan = mgk_package_plan_from_lesson_type( $lesson_type );
	if ( $plan ) {
		return mgk_quote( [
			'item_type'    => $plan,
			'rate_num'     => (int) get_post_meta( $tutor_id, 'mgk_rate_num', true ),
			'line_label'   => mgk_package_plan_lessons( $plan ) . '-lesson package',
			'context'      => $ctx,
			'voucher_code' => $ctx['voucher_code'] ?? $code,
		] );
	}
	// Default: trial.
	return mgk_quote_trial_for_tutor( $tutor_id, $ctx );
}

/* ─────────────────────────────────────────────────────────────────────────
 * Package order (slotless booking row at the package price).
 * ───────────────────────────────────────────────────────────────────────── */
/**
 * @param array $args tutor_id, parent_user_id, child_id, plan(package_8|16), voucher_code, subject
 * @return int|WP_Error booking id
 */
function mgk_engine_create_package_order( $args ) {
	global $wpdb;
	$tutor_id  = (int) ( $args['tutor_id'] ?? 0 );
	$parent_id = (int) ( $args['parent_user_id'] ?? 0 );
	$child_id  = (int) ( $args['child_id'] ?? 0 );
	$plan      = (string) ( $args['plan'] ?? '' );

	if ( get_post_type( $tutor_id ) !== 'mg_teacher' ) return new WP_Error( 'mgk_no_tutor', 'Tutor not found.', [ 'status' => 404 ] );
	if ( ! mgk_package_is_plan( $plan ) )               return new WP_Error( 'mgk_bad_plan', 'Unknown package.', [ 'status' => 400 ] );
	if ( ! $parent_id )                                 return new WP_Error( 'mgk_no_parent', 'Sign in to buy a package.', [ 'status' => 401 ] );
	if ( ! $child_id )                                  return new WP_Error( 'mgk_no_child', 'Choose which child this package is for.', [ 'status' => 422 ] );
	// The child must belong to this parent.
	if ( (int) get_post_meta( $child_id, 'mgk_child_parent_user', true ) !== $parent_id ) {
		return new WP_Error( 'mgk_bad_child', 'That child is not on your account.', [ 'status' => 403 ] );
	}

	$row_stub = [
		'lesson_type'    => mgk_package_plan_type( $plan ),
		'tutor_post_id'  => $tutor_id,
		'parent_user_id' => $parent_id,
		'child_id'       => $child_id,
		'voucher_code'   => (string) ( $args['voucher_code'] ?? '' ),
	];
	$q = mgk_engine_quote_for_booking( $row_stub, $args['voucher_code'] ?? '' );

	$applied_voucher = '';
	foreach ( (array) $q['discounts_applied'] as $d ) {
		if ( strpos( (string) $d['key'], 'voucher:' ) === 0 ) $applied_voucher = substr( $d['key'], 8 );
	}

	$child_name = get_post_meta( $child_id, 'mgk_child_full_name', true ) ?: get_the_title( $child_id );
	$now        = mgk_booking_now_utc();
	$bookings   = mgk_booking_table( 'bookings' );

	$ok = $wpdb->insert( $bookings, [
		'booking_code'     => mgk_engine_generate_booking_code(),
		'tutor_post_id'    => $tutor_id,
		'parent_user_id'   => $parent_id,
		'child_id'         => $child_id,
		'student_name'     => $child_name,
		'subject'          => sanitize_text_field( (string) ( $args['subject'] ?? '' ) ),
		'lesson_type'      => mgk_package_plan_type( $plan ),
		'slot_key'         => null,
		// Packages have no slot; satisfy NOT NULL with a neutral placeholder.
		'start_at_utc'     => $now,
		'end_at_utc'       => $now,
		'timezone'         => defined( 'MGK_BOOKING_TZ' ) ? MGK_BOOKING_TZ : 'Asia/Singapore',
		'status'           => 'PENDING_PAYMENT',
		'payment_status'   => 'PENDING',
		'price_amount'     => (float) $q['total'],
		'base_amount'      => (float) $q['base'],
		'discount_applied' => wp_json_encode( $q['discounts_applied'] ),
		'voucher_code'     => $applied_voucher ?: null,
		'currency'         => 'SGD',
		'created_at_utc'   => $now,
		'updated_at_utc'   => $now,
	] );
	if ( ! $ok ) return new WP_Error( 'mgk_order_failed', 'Could not create the package order.', [ 'status' => 500 ] );

	$booking_id = (int) $wpdb->insert_id;
	if ( $applied_voucher !== '' && function_exists( 'mgk_voucher_reserve_for_booking' ) ) {
		$booking = mgk_get_booking_row( $booking_id );
		$ctx = function_exists( 'mgk_engine_quote_context' )
			? mgk_engine_quote_context( [
				'parent_user_id' => $parent_id,
				'child_id'       => $child_id,
				'voucher_code'   => $applied_voucher,
			], $tutor_id )
			: [ 'parent_user_id' => $parent_id ];
		$ctx['booking_id'] = $booking_id;
		$lifecycle = mgk_voucher_reserve_for_booking( $booking_id, $booking ?: $row_stub, $q, $ctx );
		if ( empty( $lifecycle['ok'] ) ) {
			$wpdb->delete( $bookings, [ 'id' => $booking_id ] );
			return new WP_Error( 'mgk_voucher_' . sanitize_key( (string) ( $lifecycle['reason'] ?? 'unavailable' ) ),
				(string) ( $lifecycle['message'] ?? 'Voucher is no longer available.' ), [ 'status' => 409 ] );
		}
	}
	mgk_log_booking_event( $booking_id, 'PACKAGE_ORDER_CREATED', [
		'new_status' => 'PENDING_PAYMENT', 'actor_type' => 'PARENT',
		'metadata'   => [ 'plan' => $plan, 'child_id' => $child_id, 'total' => $q['total'] ],
	] );
	return $booking_id;
}

/* ── Materialise the enrolment on a confirmed package payment ───────────── */
/* MIGRATED to the commerce FULFILLMENT seam (SCHEMA-AND-MIGRATIONS.md): "paid
 * edu_package_* → grant the enrolment" is textbook fulfillment, so it now triggers
 * on Dispatcher → mgk_commerce_fulfilled (fired once on the PENDING→PAID core-order
 * edge) instead of the raw mgk_booking_confirmed event. Safe to move: by seam-time
 * parent-claim (prio 10) has already set parent_user_id + child; idempotent via the
 * ENROLMENT_CREATED event guard. Trials (edu_trial) carry no plan → no-op. */
add_action( 'mgk_commerce_fulfilled', function ( $item_type, $ref_id, $order ) {
	if ( strncmp( (string) $item_type, 'edu_', 4 ) !== 0 ) return; // edu sellables only
	$meta       = isset( $order['metadata_json'] ) ? json_decode( (string) $order['metadata_json'], true ) : [];
	$booking_id = is_array( $meta ) && isset( $meta['booking_id'] ) ? (int) $meta['booking_id'] : 0;
	if ( $booking_id ) {
		mgk_fulfill_package_enrolment( $booking_id );
	}
}, 10, 3 );

/**
 * Create the ACTIVE enrolment for a confirmed PACKAGE booking. No-op for non-package
 * bookings (no plan) and idempotent via the ENROLMENT_CREATED booking event, so it's
 * safe to call more than once for the same booking.
 *
 * @param int $booking_id
 * @return int enrolment id (0 on no-op)
 */
function mgk_fulfill_package_enrolment( $booking_id ) {
	$booking_id = (int) $booking_id;
	if ( ! function_exists( 'mgk_get_booking_row' ) || ! function_exists( 'mgk_enrolment_create' ) ) return 0;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return 0;
	$plan = mgk_package_plan_from_lesson_type( (string) $row['lesson_type'] );
	if ( ! $plan ) return 0; // not a package order (e.g. a trial)

	if ( function_exists( 'mgk_booking_has_event' ) && mgk_booking_has_event( $booking_id, 'ENROLMENT_CREATED' ) ) return 0;

	$lessons     = mgk_package_plan_lessons( $plan );
	$valid_until = gmdate( 'Y-m-d', time() + MGK_PACKAGE_VALIDITY_DAYS * DAY_IN_SECONDS );
	$enr = mgk_enrolment_create( [
		'child_id'          => (int) $row['child_id'],
		'parent_user_id'    => (int) $row['parent_user_id'],
		'tutor_id'          => (int) $row['tutor_post_id'],
		'plan_type'         => mgk_package_plan_type( $plan ),
		'lessons_total'     => $lessons,
		'subject'           => (string) $row['subject'],
		'valid_until'       => $valid_until,
		'source_booking_id' => $booking_id,
		'status'            => 'ACTIVE',
	] );
	if ( $enr ) {
		mgk_log_booking_event( $booking_id, 'ENROLMENT_CREATED', [
			'metadata' => [ 'enrolment_id' => $enr, 'plan' => $plan, 'lessons' => $lessons ],
		] );
	}
	return (int) $enr;
}

/* ── REST: create a package order (logged-in parent only) ───────────────── */
add_action( 'rest_api_init', function () {
	register_rest_route( 'mgk/v1', '/booking/package-order', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mgk_rest_create_package_order',
		'permission_callback' => function () { return is_user_logged_in() && function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user(); },
	] );
} );

function mgk_rest_create_package_order( WP_REST_Request $req ) {
	$body = $req->get_json_params();
	if ( ! is_array( $body ) ) $body = $req->get_params();

	$tutor = isset( $body['tutor_slug'] ) ? mgk_package_resolve_tutor( (string) $body['tutor_slug'] ) : null;
	$tutor_id = $tutor ? $tutor['id'] : (int) ( $body['tutor_id'] ?? 0 );

	$booking_id = mgk_engine_create_package_order( [
		'tutor_id'       => $tutor_id,
		'parent_user_id' => get_current_user_id(),
		'child_id'       => (int) ( $body['child_id'] ?? 0 ),
		'plan'           => sanitize_text_field( (string) ( $body['plan'] ?? '' ) ),
		'voucher_code'   => sanitize_text_field( (string) ( $body['voucher_code'] ?? '' ) ),
		'subject'        => sanitize_text_field( (string) ( $body['subject'] ?? '' ) ),
	] );
	if ( is_wp_error( $booking_id ) ) {
		$data = $booking_id->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		return new WP_REST_Response( [ 'error' => $booking_id->get_error_code(), 'message' => $booking_id->get_error_message() ], $status );
	}

	// Hand off to the SHARED payment page (S11), where the payment method
	// (PayNow QR / Card) and the order summary are clearly presented — the same
	// page the trial flow uses. The S11 pay CTA then creates the Stripe/PayNow
	// charge against this booking row. This keeps every paid service on one
	// consistent checkout instead of jumping straight into Stripe.
	$row        = mgk_get_booking_row( $booking_id );
	$code       = $row ? (string) $row['booking_code'] : '';
	$tutor_slug = $tutor ? $tutor['slug'] : ( $row ? get_post_field( 'post_name', (int) $row['tutor_post_id'] ) : '' );
	$pay_url    = add_query_arg( array_filter( [
		'booking' => $code ?: $booking_id,
		'tutor'   => $tutor_slug,
	] ), home_url( '/trial-pay/' ) );
	return new WP_REST_Response( [ 'booking_id' => $booking_id, 'checkout_url' => $pay_url ], 200 );
}

/* ── Page provisioning: /buy-package/ ───────────────────────────────────── */
add_action( 'init', function () {
	if ( get_option( 'mgk_buy_package_page_created' ) ) return;
	if ( ! get_page_by_path( 'buy-package' ) ) {
		wp_insert_post( [
			'post_type'    => 'page',
			'post_title'   => 'Buy a Package',
			'post_name'    => 'buy-package',
			'post_content' => '[mgk_buy_package]',
			'post_status'  => 'publish',
		] );
	}
	update_option( 'mgk_buy_package_page_created', 1 );
}, 100 );

/** URL to the package checkout for a tutor + plan. */
function mgk_get_buy_package_url_for( $tutor_slug, $plan ) {
	return add_query_arg( [ 'tutor' => sanitize_title( $tutor_slug ), 'plan' => $plan ], home_url( '/buy-package/' ) );
}

/* ── Shortcode: [mgk_buy_package] (the checkout page) ───────────────────── */
add_shortcode( 'mgk_buy_package', function () {
	$slug = isset( $_GET['tutor'] ) ? sanitize_title( wp_unslash( $_GET['tutor'] ) ) : '';
	$plan = isset( $_GET['plan'] ) ? sanitize_text_field( wp_unslash( $_GET['plan'] ) ) : 'package_8';
	if ( ! mgk_package_is_plan( $plan ) ) $plan = 'package_8';
	$tutor = mgk_package_resolve_tutor( $slug );

	ob_start();
	echo '<div class="mgk-section"><div class="mgk-shell mgk-buy-package" style="max-width:640px">';

	if ( ! $tutor ) {
		echo '<h1>Choose a tutor first</h1><p>Pick a tutor, then choose a package on their profile.</p>';
		echo '<p><a class="mgk-btn mgk-btn-accent" href="' . esc_url( home_url( '/student/teachers/' ) ) . '">Browse tutors →</a></p>';
		echo '</div></div>';
		return ob_get_clean();
	}

	// GATE: only a signed-in parent may buy a package (owner decision).
	$is_parent = function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user();
	if ( ! $is_parent ) {
		$login = function_exists( 'mgk_url' ) ? mgk_url( '/login/' ) : home_url( '/login/' );
		$trial = function_exists( 'mgk_get_trial_url' ) ? mgk_get_trial_url( [ 'tutor' => $tutor['slug'] ] ) : home_url( '/parent/trial/?tutor=' . $tutor['slug'] );
		echo '<h1>Packages are for existing students</h1>';
		echo '<p>Book a trial lesson first — once you’ve met ' . esc_html( $tutor['name'] ) . ' you can buy a package from your dashboard or here.</p>';
		echo '<p style="display:flex;gap:10px;flex-wrap:wrap">';
		echo '<a class="mgk-btn mgk-btn-accent" href="' . esc_url( $trial ) . '">Book a trial with ' . esc_html( $tutor['name'] ) . ' →</a>';
		echo '<a class="mgk-btn" href="' . esc_url( $login ) . '">I already have an account — sign in</a>';
		echo '</p></div></div>';
		return ob_get_clean();
	}

	$uid      = get_current_user_id();
	$children = function_exists( 'mgk_parent_children' ) ? mgk_parent_children( $uid ) : [];
	if ( ! $children ) {
		echo '<h1>Add a child first</h1><p>Book a trial to add your child, then come back to buy a package.</p>';
		echo '<p><a class="mgk-btn mgk-btn-accent" href="' . esc_url( mgk_get_trial_url( [ 'tutor' => $tutor['slug'] ] ) ) . '">Book a trial →</a></p></div></div>';
		return ob_get_clean();
	}

	// Selected child (validated to this parent) + voucher (preview via GET).
	$child_ids = array_map( function ( $p ) { return (int) ( is_object( $p ) ? $p->ID : $p ); }, $children );
	$sel_child = isset( $_GET['child'] ) ? (int) $_GET['child'] : 0;
	if ( ! in_array( $sel_child, $child_ids, true ) ) $sel_child = $child_ids[0];
	$voucher = isset( $_GET['voucher'] ) ? sanitize_text_field( wp_unslash( $_GET['voucher'] ) ) : '';

	$q = mgk_quote( [
		'item_type'    => $plan,
		'rate_num'     => $tutor['rate_num'],
		'line_label'   => mgk_package_plan_lessons( $plan ) . '-lesson package',
		'context'      => [ 'parent_user_id' => $uid, 'child_id' => $sel_child ],
		'voucher_code' => $voucher,
	] );
	$applied_voucher = '';
	foreach ( (array) ( $q['discounts_applied'] ?? [] ) as $d ) {
		if ( strpos( (string) ( $d['key'] ?? '' ), 'voucher:' ) === 0 ) {
			$applied_voucher = substr( (string) $d['key'], 8 );
		}
	}

	$lessons = mgk_package_plan_lessons( $plan );
	$other   = $plan === 'package_8' ? 'package_16' : 'package_8';
	?>
	<h1 style="margin-bottom:4px"><?php echo (int) $lessons; ?>-lesson package</h1>
	<p class="mgk-muted" style="margin-top:0">with <?php echo esc_html( $tutor['name'] ); ?> · pay once, schedule each lesson later</p>

	<form method="get" class="mgk-bk-card" style="margin-top:16px">
		<input type="hidden" name="tutor" value="<?php echo esc_attr( $tutor['slug'] ); ?>">
		<input type="hidden" name="plan" value="<?php echo esc_attr( $plan ); ?>">

		<?php if ( count( $children ) > 1 ) : ?>
		<label style="display:block;font-weight:700;font-size:13px;margin-bottom:6px">For which child?</label>
		<select name="child" onchange="this.form.submit()" style="width:100%;padding:9px;margin-bottom:14px">
			<?php foreach ( $children as $c ) :
				$cid = (int) ( is_object( $c ) ? $c->ID : $c );
				$cn  = get_post_meta( $cid, 'mgk_child_full_name', true ) ?: get_the_title( $cid ); ?>
				<option value="<?php echo esc_attr( $cid ); ?>" <?php selected( $cid, $sel_child ); ?>><?php echo esc_html( $cn ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php else : ?>
		<input type="hidden" name="child" value="<?php echo esc_attr( $sel_child ); ?>">
		<?php endif; ?>

		<div class="mgk-bk-breakdown">
			<?php foreach ( $q['rows'] as $r ) :
				$cls = 'mgk-bk-bd-row' . ( ! empty( $r['accent'] ) ? ' is-accent' : '' ); ?>
				<div class="<?php echo esc_attr( $cls ); ?>"><span class="mgk-bk-bd-label"><?php echo esc_html( $r['label'] ); ?></span><span class="mgk-bk-bd-value"><?php echo esc_html( $r['value'] ); ?></span></div>
			<?php endforeach; ?>
			<?php if ( ! empty( $q['cap_note'] ) ) : ?><p class="mgk-pay-cap-note"><?php echo esc_html( $q['cap_note'] ); ?></p><?php endif; ?>
			<div class="mgk-bk-bd-row mgk-pay-total"><span class="mgk-bk-bd-label">Total</span><span class="mgk-bk-bd-value"><?php echo esc_html( $q['total_str'] ); ?></span></div>
			<p class="mgk-pay-gst"><?php echo esc_html( $q['gst_note'] ); ?></p>
		</div>

		<label style="display:block;font-weight:700;font-size:12px;margin:12px 0 6px">Have a voucher?</label>
		<div style="display:flex;gap:8px">
			<input type="text" name="voucher" value="<?php echo esc_attr( $voucher ); ?>" placeholder="Enter code" style="flex:1;padding:9px;text-transform:uppercase">
			<button type="submit" class="mgk-btn">Apply</button>
		</div>
		<?php if ( $voucher && ! empty( $q['voucher_note'] ) ) : ?>
			<p style="font-size:12px;margin:6px 0 0;color:#b32d2e"><?php echo esc_html( $q['voucher_note'] ); ?></p>
		<?php elseif ( $voucher && $applied_voucher ) : ?>
			<p style="font-size:12px;margin:6px 0 0;color:#1a7f37">✓ Voucher <strong><?php echo esc_html( $applied_voucher ); ?></strong> applied</p>
		<?php endif; ?>
	</form>

	<button type="button" class="mgk-btn mgk-btn-accent" data-mgk-buy-package
		data-tutor="<?php echo esc_attr( $tutor['slug'] ); ?>" data-plan="<?php echo esc_attr( $plan ); ?>"
		data-child="<?php echo esc_attr( $sel_child ); ?>" data-voucher="<?php echo esc_attr( $applied_voucher ); ?>"
		style="width:100%;margin-top:14px;padding:14px;font-size:16px">Pay <?php echo esc_html( $q['total_str'] ); ?> →</button>
	<p class="mgk-muted" style="font-size:12px;text-align:center;margin-top:8px">Switch to <a href="<?php echo esc_url( mgk_get_buy_package_url_for( $tutor['slug'], $other ) ); ?>"><?php echo (int) mgk_package_plan_lessons( $other ); ?>-lesson package</a></p>

	<script>
	(function(){
		var btn = document.querySelector('[data-mgk-buy-package]');
		if(!btn||!window.fetch) return;
		btn.addEventListener('click',function(){
			// Read rest/nonce at click time: mgkBookingData is localised with the
			// footer scripts, AFTER this inline script in the body, so reading it
			// at init would capture an empty nonce → "Cookie check failed".
			var rest=(window.mgkBookingData&&window.mgkBookingData.restUrl)||'/wp-json/mgk/v1/';
			var nonce=(window.mgkBookingData&&window.mgkBookingData.nonce)||'';
			btn.disabled=true; var old=btn.textContent; btn.textContent='Redirecting to payment…';
			fetch(rest+'booking/package-order',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
				body:JSON.stringify({tutor_slug:btn.getAttribute('data-tutor'),plan:btn.getAttribute('data-plan'),child_id:parseInt(btn.getAttribute('data-child'),10),voucher_code:btn.getAttribute('data-voucher')})})
			.then(function(r){return r.json().then(function(j){return {ok:r.ok,body:j};});})
			.then(function(res){
				if(res.ok&&res.body&&res.body.checkout_url){ window.location.href=res.body.checkout_url; return; }
				btn.disabled=false; btn.textContent=old;
				alert((res.body&&res.body.message)||'Could not start checkout. Please try again.');
			}).catch(function(){ btn.disabled=false; btn.textContent=old; alert('Network error — please try again.'); });
		});
	})();
	</script>
	<?php
	echo '</div></div>';
	return ob_get_clean();
} );
