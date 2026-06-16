<?php
/**
 * MGK Parent Identity (DATA CORE) — wp_user is the ONE parent identity.
 * =====================================================================
 * Low-friction rule (owner CHỐT 2026-06-10): a parent's identity is a real
 * WordPress user, NOT a CPT. `mg_lead` is a temporary, pre-identity record that a
 * `wp_user` *claims* at S11 (first paid booking). `mg_parent` CPT is deprecated
 * as an identity store. See memory project-auth-identity-model.
 *
 *   - Account is created/attached ONLY on `mgk_booking_confirmed` (verified
 *     payment, see booking-payment-stripe.php) → no junk accounts for abandoned
 *     checkouts (matches owner's "only payers get accounts").
 *   - Identity key = EMAIL (lowercased, WP user_email is unique). Phone is an
 *     attribute + OTP channel, never the dedup key.
 *   - Children stay first-class `mg_child` records, linked here via the
 *     `mgk_child_parent_user` meta (re-pointed off the deprecated mg_parent FK).
 *
 * Passwordless login / session lives in inc/auth/mgk-passwordless.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_PARENT_ROLE' ) )       define( 'MGK_PARENT_ROLE', 'mgk_parent' );
if ( ! defined( 'MGK_CHILD_PARENT_META' ) ) define( 'MGK_CHILD_PARENT_META', 'mgk_child_parent_user' );

/* ── Role: a front-end-only account, no wp-admin ─────────────────────────── */

/** Create the parent role (read-only, no editing caps). Idempotent. */
function mgk_parent_register_role() {
	if ( ! get_role( MGK_PARENT_ROLE ) ) {
		add_role( MGK_PARENT_ROLE, 'Parent', [ 'read' => true ] );
	}
}
add_action( 'after_switch_theme', 'mgk_parent_register_role' );
add_action( 'init', function () {
	if ( ! get_role( MGK_PARENT_ROLE ) ) mgk_parent_register_role();
}, 1 );

/** True when the current/given user is a parent-role account (and not staff). */
function mgk_is_parent_user( $user = null ) {
	$user = $user ?: wp_get_current_user();
	if ( ! $user || ! $user->exists() ) return false;
	return in_array( MGK_PARENT_ROLE, (array) $user->roles, true ) && ! user_can( $user, 'edit_posts' );
}

/** Parents have no business in wp-admin — bounce them to their dashboard. */
add_action( 'admin_init', function () {
	if ( ! is_user_logged_in() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) return;
	if ( mgk_is_parent_user() ) {
		wp_safe_redirect( mgk_cta_url( 'dashboard' ) );
		exit;
	}
} );
add_filter( 'show_admin_bar', function ( $show ) {
	return mgk_is_parent_user() ? false : $show;
} );

/* ── Lead contact — single source of truth for lead → contact keys ───────── */

/**
 * Normalised contact for a lead. Centralises the lead meta-key convention so no
 * caller has to guess between mgk_lead_email / _parent_email / _parent_phone
 * again. Authoritative WRITE keys (mgk_booking_create_lead stores 'mgk_lead_'.$k
 * from the request payload): mgk_lead_email, mgk_lead_phone_e164 (alias
 * mgk_lead_phone), mgk_lead_parent_name.
 *
 * @return array{email:string,phone:string,name:string}
 */
function mgk_lead_contact( $lead_id ) {
	$lead_id = (int) $lead_id;
	if ( ! $lead_id ) return [ 'email' => '', 'phone' => '', 'name' => 'Parent' ];

	$email = (string) get_post_meta( $lead_id, 'mgk_lead_email', true );
	if ( $email === '' ) {
		$email = (string) get_post_meta( $lead_id, 'mgk_lead_parent_email', true ); // legacy fallback
	}

	$phone = '';
	foreach ( [ 'mgk_lead_phone_e164', 'mgk_lead_phone', 'mgk_lead_parent_phone' ] as $k ) {
		$v = (string) get_post_meta( $lead_id, $k, true );
		if ( $v !== '' ) { $phone = $v; break; }
	}

	$name = (string) get_post_meta( $lead_id, 'mgk_lead_parent_name', true );

	return [
		'email' => sanitize_email( $email ),
		'phone' => sanitize_text_field( $phone ),
		'name'  => sanitize_text_field( $name ?: 'Parent' ),
	];
}

/* ── Find-or-create the parent wp_user (keyed by email) ──────────────────── */

/**
 * Get-or-create the parent wp_user for an email. Idempotent: returns the
 * existing user for a known email (returning parent), else creates a passwordless
 * mgk_parent user. Returns int user_id or WP_Error.
 *
 * @param string $email identity key (required, validated)
 * @param string $phone E.164 attribute (OTP channel)
 * @param string $name  display name
 */
function mgk_parent_find_or_create( $email, $phone = '', $name = '' ) {
	$email = sanitize_email( $email );
	if ( ! $email || ! is_email( $email ) ) {
		return new WP_Error( 'mgk_no_email', 'A valid email is required to create a parent account.' );
	}

	$existing = get_user_by( 'email', $email );
	if ( $existing ) {
		// Returning parent — backfill phone if we have none on file; never clobber.
		if ( $phone && ! get_user_meta( $existing->ID, 'mgk_parent_phone', true ) ) {
			update_user_meta( $existing->ID, 'mgk_parent_phone', sanitize_text_field( $phone ) );
		}
		return (int) $existing->ID;
	}

	// New passwordless account. user_login = email (WP permits); guarantee unique.
	$login = $email;
	if ( username_exists( $login ) ) {
		$login = $email . '-' . wp_generate_password( 4, false, false );
	}
	$user_id = wp_insert_user( [
		'user_login'   => $login,
		'user_email'   => $email,
		'user_pass'    => wp_generate_password( 24, true, true ), // strong, never disclosed
		'display_name' => sanitize_text_field( $name ?: 'Parent' ),
		'first_name'   => sanitize_text_field( $name ?: '' ),
		'role'         => MGK_PARENT_ROLE,
	] );
	if ( is_wp_error( $user_id ) ) return $user_id;

	if ( $phone ) update_user_meta( $user_id, 'mgk_parent_phone', sanitize_text_field( $phone ) );
	update_user_meta( $user_id, 'mgk_parent_created_via',     'S11' );
	update_user_meta( $user_id, 'mgk_parent_email_verified',  0 ); // proven on first magic-link click
	update_user_meta( $user_id, 'mgk_parent_phone_verified',  0 ); // proven on OTP verify

	do_action( 'mgk_parent_account_created', (int) $user_id, [ 'email' => $email, 'phone' => $phone ] );
	return (int) $user_id;
}

/* ── Claim: lead → wp_user on a CONFIRMED booking (S11) ──────────────────── */

/**
 * On a CONFIRMED booking, create/attach the parent wp_user and stamp it onto the
 * booking row + lead. Hooked to `mgk_booking_confirmed`, which fires ONLY from a
 * verified payment (booking-payment-stripe.php) — so abandoned checkouts never
 * create an account. Idempotent: a booking that already has parent_user_id is a
 * no-op, so duplicate webhook deliveries are safe.
 */
function mgk_parent_claim_on_booking( $booking_id ) {
	if ( ! function_exists( 'mgk_get_booking_row' ) ) return;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row || ! empty( $row['parent_user_id'] ) ) return; // gone or already linked

	$contact = ! empty( $row['lead_id'] )
		? mgk_lead_contact( (int) $row['lead_id'] )
		: [ 'email' => '', 'phone' => '', 'name' => 'Parent' ];

	$email = $contact['email'];
	$name  = ( $contact['name'] && $contact['name'] !== 'Parent' )
		? $contact['name']
		: ( ! empty( $row['student_name'] ) ? $row['student_name'] . "'s parent" : 'Parent' );

	if ( ! $email ) {
		// Nothing to key the identity on — flag for manual follow-up, don't guess.
		mgk_log_booking_event( $booking_id, 'ACCOUNT_LINK_SKIPPED', [
			'actor_type' => 'SYSTEM', 'metadata' => [ 'reason' => 'no_email_on_lead' ],
		] );
		return;
	}

	$user_id = mgk_parent_find_or_create( $email, $contact['phone'], $name );
	if ( is_wp_error( $user_id ) ) {
		mgk_log_booking_event( $booking_id, 'ACCOUNT_LINK_FAILED', [
			'actor_type' => 'SYSTEM', 'metadata' => [ 'error' => $user_id->get_error_message() ],
		] );
		return;
	}

	global $wpdb;
	$wpdb->update( mgk_booking_table( 'bookings' ),
		[ 'parent_user_id' => (int) $user_id, 'updated_at_utc' => mgk_booking_now_utc() ],
		[ 'id' => $booking_id ]
	);
	if ( ! empty( $row['lead_id'] ) ) {
		update_post_meta( (int) $row['lead_id'], 'mgk_lead_parent_user_id', (int) $user_id );
	}
	mgk_log_booking_event( $booking_id, 'ACCOUNT_LINKED', [
		'actor_type' => 'SYSTEM', 'actor_id' => (int) $user_id, 'metadata' => [ 'email' => $email ],
	] );

	// Create/link the child entity from the lead (guest path: the S07 child_name
	// only becomes a real linked mg_child here, at confirmation).
	if ( function_exists( 'mgk_child_find_or_create' ) ) {
		$child_name = '';
		$level      = '';
		if ( ! empty( $row['lead_id'] ) ) {
			$child_name = (string) get_post_meta( (int) $row['lead_id'], 'mgk_lead_child_name', true );
			$level      = (string) get_post_meta( (int) $row['lead_id'], 'mgk_lead_level', true );
		}
		if ( $child_name === '' ) $child_name = (string) ( $row['student_name'] ?? '' );
		if ( $child_name !== '' ) {
			$child_id = mgk_child_find_or_create( (int) $user_id, $child_name, $level );
			if ( $child_id && ! empty( $row['lead_id'] ) ) {
				update_post_meta( (int) $row['lead_id'], 'mgk_lead_child_id', (int) $child_id );
			}
		}
	}

	// Hand off to passwordless: email (+WhatsApp) the dashboard magic link.
	if ( function_exists( 'mgk_parent_send_login_link' ) ) {
		mgk_parent_send_login_link( (int) $user_id, mgk_cta_url( 'dashboard' ), 'welcome' );
	}
}
add_action( 'mgk_booking_confirmed', 'mgk_parent_claim_on_booking', 10, 1 );

/* ── Children: mg_child linked to the parent wp_user (replaces mg_parent FK) ─ */

/** mg_child posts owned by a parent wp_user (newest schoolwork first). */
function mgk_parent_children( $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id || ! post_type_exists( 'mg_child' ) ) return [];
	return get_posts( [
		'post_type'   => 'mg_child',
		'numberposts' => 50,
		'meta_key'    => MGK_CHILD_PARENT_META,
		'meta_value'  => $user_id,
		'orderby'     => 'title',
		'order'       => 'ASC',
	] );
}

/** Link an existing mg_child post to a parent wp_user. */
function mgk_child_set_parent( $child_id, $user_id ) {
	$child_id = (int) $child_id; $user_id = (int) $user_id;
	if ( ! $child_id || ! $user_id ) return false;
	return (bool) update_post_meta( $child_id, MGK_CHILD_PARENT_META, $user_id );
}

/**
 * Find-or-create an mg_child for a parent wp_user, idempotent by (parent,name).
 * `$level_slug` is the S07 taxonomy slug (e.g. 'p5-p6') → stored as the
 * mgk_level term id. Returns the child post id, or 0 on failure.
 */
function mgk_child_find_or_create( $user_id, $name, $level_slug = '', $extra = [] ) {
	$user_id = (int) $user_id;
	$name    = trim( (string) $name );
	if ( ! $user_id || $name === '' || ! post_type_exists( 'mg_child' ) ) return 0;

	// De-dupe: an existing child of THIS parent with the same name.
	$existing = get_posts( [
		'post_type'   => 'mg_child',
		'post_status' => 'any',
		'numberposts' => 1,
		'fields'      => 'ids',
		'title'       => $name,
		'meta_query'  => [ [ 'key' => MGK_CHILD_PARENT_META, 'value' => $user_id ] ],
	] );
	$child_id = $existing ? (int) $existing[0] : 0;

	if ( ! $child_id ) {
		$child_id = wp_insert_post( [
			'post_type'   => 'mg_child',
			'post_title'  => $name,
			'post_status' => 'publish',
		], true );
		if ( is_wp_error( $child_id ) || ! $child_id ) return 0;
		$child_id = (int) $child_id;
	}

	update_post_meta( $child_id, 'mgk_child_full_name', $name );
	if ( $level_slug !== '' && taxonomy_exists( 'mgk_level' ) ) {
		$term = get_term_by( 'slug', sanitize_title( $level_slug ), 'mgk_level' );
		if ( $term && ! is_wp_error( $term ) ) {
			update_post_meta( $child_id, 'mgk_child_current_level', (int) $term->term_id );
		}
	}
	foreach ( (array) $extra as $k => $v ) {
		update_post_meta( $child_id, sanitize_key( $k ), $v );
	}
	mgk_child_set_parent( $child_id, $user_id );
	return $child_id;
}

/* ── Admin operator: manually attach a contact email to a booking ────────── */

/**
 * Admin-set the parent email on a booking (agency keys in a walk-in / bank
 * transfer). Contact lives on the lead, so this writes the email to the booking's
 * lead — creating a minimal lead and linking it when the booking has none. After
 * this, `mgk_parent_claim_on_booking()` (Force confirm or "Create account") will
 * create/link the wp_user. Returns the lead id, or WP_Error.
 */
function mgk_parent_attach_booking_email( $booking_id, $email, $phone = '', $name = '' ) {
	if ( ! function_exists( 'mgk_get_booking_row' ) ) return new WP_Error( 'mgk_no_engine', 'Booking engine unavailable.' );
	$booking_id = (int) $booking_id;
	$email = sanitize_email( $email );
	if ( ! $email || ! is_email( $email ) ) return new WP_Error( 'mgk_bad_email', 'A valid email is required.' );

	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return new WP_Error( 'mgk_no_booking', 'Booking not found.' );

	$lead_id = (int) $row['lead_id'];
	if ( ! $lead_id ) {
		if ( ! post_type_exists( 'mg_lead' ) ) return new WP_Error( 'mgk_no_lead_cpt', 'Lead CPT unavailable.' );
		$lead_id = wp_insert_post( [
			'post_type'   => 'mg_lead',
			'post_status' => 'publish',
			'post_title'  => 'Manual — ' . ( $row['student_name'] ?: 'booking' ) . ' — ' . $row['booking_code'],
		], true );
		if ( is_wp_error( $lead_id ) ) return $lead_id;
		update_post_meta( $lead_id, 'mgk_lead_state', defined( 'MGK_LEAD_CAPTURED' ) ? MGK_LEAD_CAPTURED : 'captured' );
		update_post_meta( $lead_id, 'mgk_lead_source', 'admin_manual' );
		global $wpdb;
		$wpdb->update( mgk_booking_table( 'bookings' ),
			[ 'lead_id' => (int) $lead_id, 'updated_at_utc' => mgk_booking_now_utc() ],
			[ 'id' => $booking_id ]
		);
	}

	update_post_meta( $lead_id, 'mgk_lead_email', $email );
	if ( $phone ) update_post_meta( $lead_id, 'mgk_lead_phone_e164', sanitize_text_field( $phone ) );
	if ( $name )  update_post_meta( $lead_id, 'mgk_lead_parent_name', sanitize_text_field( $name ) );
	return (int) $lead_id;
}

/* ── Admin: the REAL "Parents" list = wp_users by email (replaces mg_parent CPT) ─ */

/** How many engine bookings a parent user owns. */
function mgk_parent_booking_count( $user_id ) {
	global $wpdb;
	if ( ! function_exists( 'mgk_booking_table' ) ) return 0;
	$t = mgk_booking_table( 'bookings' );
	if ( ! $t ) return 0;
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE parent_user_id = %d", (int) $user_id ) );
}

add_action( 'admin_menu', function () {
	add_menu_page(
		'Parents', 'Parents', 'list_users', 'mgk-parents',
		'mgk_render_parents_admin_page', 'dashicons-groups', 34
	);
}, 9 );

function mgk_render_parents_admin_page() {
	if ( ! current_user_can( 'list_users' ) ) wp_die( 'Permission denied.' );
	echo '<div class="wrap"><h1>Parents</h1>';
	echo '<p class="description">Parent accounts are real WordPress users keyed by <strong>email</strong> (created at S11, or by an agency operator on a booking). The old "Parents" custom-post type is deprecated.</p>';

	if ( isset( $_GET['mgk_done'] ) && $_GET['mgk_done'] === 'login_sent' ) {
		$hint = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? ' — see option <code>mgk_login_link_log</code>.' : ' and emailed.';
		echo '<div class="notice notice-success is-dismissible"><p>Login link generated' . $hint . '</p></div>';
	}

	$parents = get_users( [ 'role' => MGK_PARENT_ROLE, 'number' => 300, 'orderby' => 'registered', 'order' => 'DESC' ] );
	if ( ! $parents ) { echo '<p>No parent accounts yet.</p></div>'; return; }

	echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
	echo '<th>Email</th><th>Name</th><th>Phone</th><th>Created via</th><th>Bookings</th><th>Registered</th><th></th>';
	echo '</tr></thead><tbody>';
	foreach ( $parents as $u ) {
		$login = wp_nonce_url( admin_url( 'admin-post.php?action=mgk_parent_send_login&user=' . $u->ID ), 'mgk_parent_send_login_' . $u->ID );
		echo '<tr>';
		echo '<td><strong>' . esc_html( $u->user_email ) . '</strong></td>';
		echo '<td>' . esc_html( $u->display_name ) . '</td>';
		echo '<td>' . esc_html( get_user_meta( $u->ID, 'mgk_parent_phone', true ) ?: '—' ) . '</td>';
		echo '<td>' . esc_html( get_user_meta( $u->ID, 'mgk_parent_created_via', true ) ?: '—' ) . '</td>';
		echo '<td>' . (int) mgk_parent_booking_count( $u->ID ) . '</td>';
		echo '<td>' . esc_html( mysql2date( 'j M Y', $u->user_registered ) ) . '</td>';
		echo '<td><a class="button button-small" href="' . esc_url( get_edit_user_link( $u->ID ) ) . '">Edit</a> ';
		echo '<a class="button button-small" href="' . esc_url( $login ) . '">Send login link</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}

add_action( 'admin_post_mgk_parent_send_login', function () {
	$uid = isset( $_GET['user'] ) ? (int) $_GET['user'] : 0;
	if ( ! current_user_can( 'list_users' ) ) wp_die( 'Permission denied.' );
	check_admin_referer( 'mgk_parent_send_login_' . $uid );
	if ( $uid && function_exists( 'mgk_parent_send_login_link' ) ) {
		mgk_parent_send_login_link(
			$uid,
			function_exists( 'mgk_cta_url' ) ? mgk_cta_url( 'dashboard' ) : home_url( '/parent/dashboard/' ),
			'login'
		);
	}
	wp_safe_redirect( add_query_arg( 'mgk_done', 'login_sent', admin_url( 'admin.php?page=mgk-parents' ) ) );
	exit;
} );
