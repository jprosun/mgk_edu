<?php
/**
 * MGK — Voucher CPT + validation.
 * ===============================
 * Admin-managed discount codes (`mgk_voucher`). One voucher per order (BR-11);
 * the cart/checkout replaces an existing code rather than stacking two vouchers.
 * Validation is server-authoritative and runs both at apply-time and again at
 * charge-time so a tampered client can never widen a discount.
 *
 * Meta:
 *   mgk_v_code         (string, uppercased, unique-ish)   — the code customers type
 *   mgk_v_type         ('pct' | 'fixed')
 *   mgk_v_value        (number) — percent (0-100) or fixed SGD amount
 *   mgk_v_is_stackable (0|1)    — may combine with auto loyalty discounts
 *   mgk_v_min_spend    (number) — minimum order subtotal to qualify
 *   mgk_v_max_uses     (int)    — 0 = unlimited
 *   mgk_v_used_count   (int)    — incremented on successful payment
 *   mgk_v_valid_from   (Y-m-d)  — optional
 *   mgk_v_valid_to     (Y-m-d)  — optional
 *   mgk_v_applies_to   ('all'|'trial'|'package'|'retail')
 *   mgk_v_exclude_sale (0|1)    — retail: skip already-discounted items
 *   mgk_v_active       (0|1)
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── CPT registration ───────────────────────────────────────────────────── */

add_action( 'init', function () {
	register_post_type( 'mgk_voucher', [
		'labels' => [
			'name'               => 'Vouchers',
			'singular_name'      => 'Voucher',
			'add_new'            => 'Add Voucher',
			'add_new_item'       => 'Add Voucher',
			'edit_item'          => 'Edit Voucher',
			'new_item'           => 'New Voucher',
			'view_item'          => 'View Voucher',
			'search_items'       => 'Search Vouchers',
			'not_found'          => 'No vouchers yet — create your first code',
			'not_found_in_trash' => 'No vouchers in trash',
			'menu_name'          => 'Vouchers',
		],
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'mgk-discounts',  // nested under the Discounts top menu
		'supports'      => [ 'title' ],
		'capability_type' => 'post',
		'map_meta_cap'  => true,
	] );
} );

/* ── Helpers ────────────────────────────────────────────────────────────── */

/** Normalise a code: trimmed, uppercased, no spaces. */
function mgk_voucher_norm_code( $code ) {
	return strtoupper( preg_replace( '/\s+/', '', (string) $code ) );
}

/** Find the published voucher post for a code (or null). */
function mgk_voucher_find( $code ) {
	$code = mgk_voucher_norm_code( $code );
	if ( $code === '' ) return null;
	$q = get_posts( [
		'post_type'   => 'mgk_voucher',
		'post_status' => 'publish',
		'meta_key'    => 'mgk_v_code',
		'meta_value'  => $code,
		'numberposts' => 1,
		'fields'      => 'ids',
	] );
	return $q ? (int) $q[0] : null;
}

/**
 * Validate a voucher code against an order context.
 *
 * @param string $code
 * @param array  $args  subtotal, base, item_type, context
 * @return array{valid:bool, type?:string, value?:float, is_stackable?:bool, message?:string, post_id?:int}
 */
function mgk_voucher_validate( $code, $args = [] ) {
	$code = mgk_voucher_norm_code( $code );
	$fail = function ( $msg ) { return [ 'valid' => false, 'message' => $msg ]; };

	$pid = mgk_voucher_find( $code );
	if ( ! $pid ) return $fail( 'Invalid voucher code' );

	$m = function ( $k, $d = '' ) use ( $pid ) { return get_post_meta( $pid, $k, true ) ?: $d; };

	if ( (int) $m( 'mgk_v_active', 1 ) !== 1 ) return $fail( 'This voucher is no longer active' );

	$today = gmdate( 'Y-m-d' );
	if ( ( $from = $m( 'mgk_v_valid_from' ) ) && $today < $from ) return $fail( 'This voucher is not active yet' );
	if ( ( $to   = $m( 'mgk_v_valid_to' ) )   && $today > $to )   return $fail( 'This voucher has expired' );

	$max  = (int) $m( 'mgk_v_max_uses', 0 );
	$used = (int) $m( 'mgk_v_used_count', 0 );
	if ( $max > 0 && $used >= $max ) return $fail( 'This voucher has reached its usage limit' );

	$min = (float) $m( 'mgk_v_min_spend', 0 );
	$sub = (float) ( $args['subtotal'] ?? $args['base'] ?? 0 );
	if ( $min > 0 && $sub < $min ) {
		return $fail( sprintf( 'Spend at least $%s to use this voucher', number_format( $min, 2 ) ) );
	}

	$applies = $m( 'mgk_v_applies_to', 'all' );
	$item    = (string) ( $args['item_type'] ?? '' );
	if ( $applies === 'trial'   && $item !== 'trial' )                                  return $fail( 'This voucher applies to trial lessons only' );
	if ( $applies === 'package' && strpos( $item, 'package' ) !== 0 )                   return $fail( 'This voucher applies to packages only' );
	if ( $applies === 'retail'  && $item !== 'retail' )                                 return $fail( 'This voucher applies to store purchases only' );

	$type  = $m( 'mgk_v_type', 'pct' ) === 'fixed' ? 'fixed' : 'pct';
	$value = (float) $m( 'mgk_v_value', 0 );
	if ( $value <= 0 ) return $fail( 'Voucher misconfigured' );

	return [
		'valid'        => true,
		'type'         => $type,
		'value'        => $value,
		'is_stackable' => (int) $m( 'mgk_v_is_stackable', 0 ) === 1,
		'post_id'      => $pid,
		'message'      => '',
	];
}

/**
 * Atomically increment a voucher's used_count, but ONLY while it is still under
 * its max_uses limit. The conditional UPDATE makes the check-and-increment a
 * single DB operation, so concurrent confirmations can never push used_count past
 * max_uses (the race the old read-then-write had). max_uses = 0 means unlimited.
 *
 * @return bool true if the count was incremented.
 */
function mgk_voucher_mark_used( $code ) {
	global $wpdb;
	$pid = mgk_voucher_find( $code );
	if ( ! $pid ) return false;

	// Guarantee the counter row exists so the atomic UPDATE has a row to touch.
	if ( get_post_meta( $pid, 'mgk_v_used_count', true ) === '' ) {
		add_post_meta( $pid, 'mgk_v_used_count', 0, true );
	}

	$res = $wpdb->query( $wpdb->prepare(
		"UPDATE {$wpdb->postmeta} uc
		 LEFT JOIN {$wpdb->postmeta} mx
		   ON mx.post_id = uc.post_id AND mx.meta_key = 'mgk_v_max_uses'
		 SET uc.meta_value = CAST(uc.meta_value AS UNSIGNED) + 1
		 WHERE uc.post_id = %d AND uc.meta_key = 'mgk_v_used_count'
		   AND ( COALESCE(CAST(mx.meta_value AS UNSIGNED), 0) = 0
		         OR CAST(uc.meta_value AS UNSIGNED) < CAST(mx.meta_value AS UNSIGNED) )",
		$pid
	) );
	wp_cache_delete( $pid, 'post_meta' ); // raw SQL bypassed the object cache
	return $res > 0;
}

/**
 * On a confirmed (paid) booking, count the voucher usage EXACTLY once. Idempotency
 * is anchored in the booking-events log (VOUCHER_COUNTED), so it survives webhook
 * retries even after a transient would have expired.
 */
add_action( 'mgk_booking_confirmed', function ( $booking_id ) {
	$booking_id = (int) $booking_id;
	if ( ! function_exists( 'mgk_get_booking_row' ) ) return;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row || empty( $row['voucher_code'] ) ) return;

	if ( mgk_booking_has_event( $booking_id, 'VOUCHER_COUNTED' ) ) return; // already counted
	if ( mgk_voucher_mark_used( $row['voucher_code'] ) ) {
		mgk_log_booking_event( $booking_id, 'VOUCHER_COUNTED', [
			'metadata' => [ 'code' => $row['voucher_code'] ],
		] );
	}
}, 20 );

/** True if a booking already has at least one event of the given type. */
function mgk_booking_has_event( $booking_id, $event_type ) {
	global $wpdb;
	if ( ! function_exists( 'mgk_booking_table' ) ) return false;
	$t = mgk_booking_table( 'events' );
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$t} WHERE booking_id = %d AND event_type = %s",
		(int) $booking_id, $event_type
	) ) > 0;
}

/* ── Admin edit screen: friendly meta box ───────────────────────────────── */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'mgk_voucher_cfg', 'Voucher settings', 'mgk_voucher_metabox', 'mgk_voucher', 'normal', 'high' );
} );

function mgk_voucher_metabox( $post ) {
	wp_nonce_field( 'mgk_voucher_save', 'mgk_voucher_nonce' );
	$g = function ( $k, $d = '' ) use ( $post ) { $v = get_post_meta( $post->ID, $k, true ); return $v === '' ? $d : $v; };
	$code   = $g( 'mgk_v_code' );
	$type   = $g( 'mgk_v_type', 'pct' );
	$value  = $g( 'mgk_v_value' );
	$applies= $g( 'mgk_v_applies_to', 'all' );
	?>
	<style>
		.mgk-vform{display:grid;grid-template-columns:1fr 1fr;gap:16px 24px;max-width:760px;margin-top:8px}
		.mgk-vform .full{grid-column:1/-1}
		.mgk-vform label{display:block;font-weight:600;margin-bottom:4px}
		.mgk-vform input[type=text],.mgk-vform input[type=number],.mgk-vform input[type=date],.mgk-vform select{width:100%}
		.mgk-vform .hint{color:#646970;font-size:12px;margin-top:3px}
		.mgk-vrow-inline{display:flex;gap:8px;align-items:center}
		.mgk-vchk{display:flex;gap:8px;align-items:flex-start;background:#f6f7f7;border:1px solid #dcdcde;padding:10px;border-radius:4px}
	</style>
	<div class="mgk-vform">
		<div>
			<label for="mgk_v_code">Code</label>
			<input type="text" id="mgk_v_code" name="mgk_v_code" value="<?php echo esc_attr( $code ); ?>" placeholder="WELCOME10" style="text-transform:uppercase">
			<p class="hint">What the customer types at checkout. Auto-uppercased.</p>
		</div>
		<div>
			<label>Discount</label>
			<div class="mgk-vrow-inline">
				<select name="mgk_v_type" style="max-width:130px">
					<option value="pct"   <?php selected( $type, 'pct' ); ?>>Percent %</option>
					<option value="fixed" <?php selected( $type, 'fixed' ); ?>>Fixed $</option>
				</select>
				<input type="number" name="mgk_v_value" value="<?php echo esc_attr( $value ); ?>" step="0.01" min="0" placeholder="10">
			</div>
			<p class="hint">e.g. <strong>10</strong> = 10% off (Percent) or $10 off (Fixed).</p>
		</div>
		<div>
			<label for="mgk_v_min_spend">Minimum spend ($)</label>
			<input type="number" id="mgk_v_min_spend" name="mgk_v_min_spend" value="<?php echo esc_attr( $g( 'mgk_v_min_spend', 0 ) ); ?>" step="0.01" min="0">
			<p class="hint">0 = no minimum.</p>
		</div>
		<div>
			<label for="mgk_v_max_uses">Max uses</label>
			<input type="number" id="mgk_v_max_uses" name="mgk_v_max_uses" value="<?php echo esc_attr( $g( 'mgk_v_max_uses', 0 ) ); ?>" step="1" min="0">
			<p class="hint">0 = unlimited. Used so far: <strong><?php echo (int) $g( 'mgk_v_used_count', 0 ); ?></strong>.</p>
		</div>
		<div>
			<label for="mgk_v_valid_from">Valid from</label>
			<input type="date" id="mgk_v_valid_from" name="mgk_v_valid_from" value="<?php echo esc_attr( $g( 'mgk_v_valid_from' ) ); ?>">
		</div>
		<div>
			<label for="mgk_v_valid_to">Valid until</label>
			<input type="date" id="mgk_v_valid_to" name="mgk_v_valid_to" value="<?php echo esc_attr( $g( 'mgk_v_valid_to' ) ); ?>">
		</div>
		<div>
			<label for="mgk_v_applies_to">Applies to</label>
			<select id="mgk_v_applies_to" name="mgk_v_applies_to">
				<option value="all"     <?php selected( $applies, 'all' ); ?>>Everything</option>
				<option value="trial"   <?php selected( $applies, 'trial' ); ?>>Trial lessons only</option>
				<option value="package" <?php selected( $applies, 'package' ); ?>>Packages only</option>
				<option value="retail"  <?php selected( $applies, 'retail' ); ?>>Store purchases only (retail)</option>
			</select>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_is_stackable" name="mgk_v_is_stackable" value="1" <?php checked( (int) $g( 'mgk_v_is_stackable', 0 ), 1 ); ?>>
			<label for="mgk_v_is_stackable" style="font-weight:600;margin:0">
				Allow stacking with automatic discounts (sibling / returning)
				<span class="hint" style="display:block;font-weight:400">Off (default) = this voucher is the only discount applied when used. Total still capped at <?php echo (int) mgk_discount_rule( 'stack_cap_pct', 25 ); ?>%.</span>
			</label>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_exclude_sale" name="mgk_v_exclude_sale" value="1" <?php checked( (int) $g( 'mgk_v_exclude_sale', 0 ), 1 ); ?>>
			<label for="mgk_v_exclude_sale" style="font-weight:600;margin:0">Exclude already-discounted items (retail)</label>
		</div>
		<div class="full mgk-vchk">
			<input type="checkbox" id="mgk_v_active" name="mgk_v_active" value="1" <?php checked( (int) $g( 'mgk_v_active', 1 ), 1 ); ?>>
			<label for="mgk_v_active" style="font-weight:600;margin:0">Active</label>
		</div>
	</div>
	<?php
}

add_action( 'save_post_mgk_voucher', function ( $post_id ) {
	if ( ! isset( $_POST['mgk_voucher_nonce'] ) || ! wp_verify_nonce( $_POST['mgk_voucher_nonce'], 'mgk_voucher_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$text = [ 'mgk_v_valid_from', 'mgk_v_valid_to', 'mgk_v_applies_to' ];
	foreach ( $text as $k ) {
		if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
	}
	update_post_meta( $post_id, 'mgk_v_code', mgk_voucher_norm_code( $_POST['mgk_v_code'] ?? '' ) );
	update_post_meta( $post_id, 'mgk_v_type', ( ( $_POST['mgk_v_type'] ?? 'pct' ) === 'fixed' ) ? 'fixed' : 'pct' );
	update_post_meta( $post_id, 'mgk_v_value', (float) ( $_POST['mgk_v_value'] ?? 0 ) );
	update_post_meta( $post_id, 'mgk_v_min_spend', (float) ( $_POST['mgk_v_min_spend'] ?? 0 ) );
	update_post_meta( $post_id, 'mgk_v_max_uses', (int) ( $_POST['mgk_v_max_uses'] ?? 0 ) );
	foreach ( [ 'mgk_v_is_stackable', 'mgk_v_exclude_sale', 'mgk_v_active' ] as $cb ) {
		update_post_meta( $post_id, $cb, isset( $_POST[ $cb ] ) ? 1 : 0 );
	}
	if ( get_post_meta( $post_id, 'mgk_v_used_count', true ) === '' ) {
		update_post_meta( $post_id, 'mgk_v_used_count', 0 );
	}
} );

/* ── Admin list columns ─────────────────────────────────────────────────── */

add_filter( 'manage_mgk_voucher_posts_columns', function ( $cols ) {
	$new = [ 'cb' => $cols['cb'] ?? '', 'title' => 'Name' ];
	$new['mgk_code']    = 'Code';
	$new['mgk_disc']    = 'Discount';
	$new['mgk_applies'] = 'Applies to';
	$new['mgk_uses']    = 'Used';
	$new['mgk_status']  = 'Status';
	return $new;
} );

add_action( 'manage_mgk_voucher_posts_custom_column', function ( $col, $post_id ) {
	$g = function ( $k, $d = '' ) use ( $post_id ) { $v = get_post_meta( $post_id, $k, true ); return $v === '' ? $d : $v; };
	switch ( $col ) {
		case 'mgk_code':
			echo '<code style="font-size:13px">' . esc_html( $g( 'mgk_v_code', '—' ) ) . '</code>';
			break;
		case 'mgk_disc':
			$v = (float) $g( 'mgk_v_value', 0 );
			echo $g( 'mgk_v_type', 'pct' ) === 'fixed' ? '$' . esc_html( number_format( $v, 2 ) ) : esc_html( (int) $v ) . '%';
			break;
		case 'mgk_applies':
			echo esc_html( ucfirst( $g( 'mgk_v_applies_to', 'all' ) ) );
			break;
		case 'mgk_uses':
			$max = (int) $g( 'mgk_v_max_uses', 0 );
			echo (int) $g( 'mgk_v_used_count', 0 ) . ( $max ? ' / ' . $max : ' / ∞' );
			break;
		case 'mgk_status':
			$active = (int) $g( 'mgk_v_active', 1 ) === 1;
			$to     = $g( 'mgk_v_valid_to' );
			$expired= $to && gmdate( 'Y-m-d' ) > $to;
			if ( $expired )      echo '<span style="color:#b32d2e">Expired</span>';
			elseif ( $active )   echo '<span style="color:#1a7f37">● Active</span>';
			else                 echo '<span style="color:#646970">Inactive</span>';
			break;
	}
}, 10, 2 );
