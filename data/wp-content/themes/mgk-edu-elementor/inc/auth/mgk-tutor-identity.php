<?php
/**
 * MGK Tutor Identity (DATA CORE) — wp_user is the ONE tutor identity.
 * ====================================================================
 * Mirrors the parent identity model (inc/auth/mgk-parent-identity.php): a tutor's
 * login identity is a real WordPress user with role `mgk_tutor`, LINKED to their
 * `mg_teacher` CPT record (which stays the source of truth for the public profile,
 * rate, subjects, reviews). The CPT is the "what they teach"; the wp_user is the
 * "who logs in".
 *
 *   - Link  : `mgk_teacher_user_id` meta on mg_teacher  ↔  `mgk_tutor_post_id`
 *             meta on wp_user. One-to-one.
 *   - Key   : EMAIL (mgk_tutor_email meta on the tutor CPT — also the public
 *             contact email read by booking-view.php).
 *   - Auth  : passwordless magic-link, reusing inc/auth/mgk-passwordless.php
 *             (mgk_magic_link_url / session rules) unchanged.
 *   - Login : /tutor/login/  →  [mgk_tutor_login]  → emailed a one-time link that
 *             lands them on /tutor/dashboard/.
 *
 * Provisioning is an explicit agency action (admin meta-box on the tutor), NOT
 * automatic — verified tutors get a login when the agency invites them.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_TUTOR_ROLE' ) )      define( 'MGK_TUTOR_ROLE', 'mgk_tutor' );
if ( ! defined( 'MGK_TUTOR_USER_META' ) ) define( 'MGK_TUTOR_USER_META', 'mgk_teacher_user_id' );  // on mg_teacher → wp_user id
if ( ! defined( 'MGK_TUTOR_POST_META' ) ) define( 'MGK_TUTOR_POST_META', 'mgk_tutor_post_id' );    // on wp_user → mg_teacher id

/* ── Role: a front-end-only account, no wp-admin ─────────────────────────── */

/** Create the tutor role (read-only, no editing caps). Idempotent. */
function mgk_tutor_register_role() {
	if ( ! get_role( MGK_TUTOR_ROLE ) ) {
		add_role( MGK_TUTOR_ROLE, 'Tutor', [ 'read' => true ] );
	}
}
add_action( 'after_switch_theme', 'mgk_tutor_register_role' );
add_action( 'init', function () {
	if ( ! get_role( MGK_TUTOR_ROLE ) ) mgk_tutor_register_role();
}, 1 );

/** True when the current/given user is a tutor-role account (and not staff). */
function mgk_is_tutor_user( $user = null ) {
	$user = $user ?: wp_get_current_user();
	if ( ! $user || ! $user->exists() ) return false;
	return in_array( MGK_TUTOR_ROLE, (array) $user->roles, true ) && ! user_can( $user, 'edit_posts' );
}

/** The mg_teacher post id owned by the logged-in (or given) tutor user, or 0. */
function mgk_current_tutor_teacher_id( $user = null ) {
	$user = $user ?: wp_get_current_user();
	if ( ! $user || ! $user->exists() || ! mgk_is_tutor_user( $user ) ) return 0;
	$tid = (int) get_user_meta( $user->ID, MGK_TUTOR_POST_META, true );
	// Defensive: confirm the link still points at a real tutor CPT.
	return ( $tid && get_post_type( $tid ) === 'mg_teacher' ) ? $tid : 0;
}

/** The wp_user id that owns a given mg_teacher record, or 0 (unprovisioned). */
function mgk_teacher_owner_user_id( $teacher_id ) {
	$teacher_id = (int) $teacher_id;
	if ( ! $teacher_id ) return 0;
	$uid = (int) get_post_meta( $teacher_id, MGK_TUTOR_USER_META, true );
	return ( $uid && get_user_by( 'id', $uid ) ) ? $uid : 0;
}

/** Public/login email on file for a tutor CPT (single source of truth). */
function mgk_teacher_email( $teacher_id ) {
	$teacher_id = (int) $teacher_id;
	if ( ! $teacher_id ) return '';
	return sanitize_email( (string) get_post_meta( $teacher_id, 'mgk_tutor_email', true ) );
}

/* ── Tutors have no business in wp-admin — bounce to their dashboard ──────── */
add_action( 'admin_init', function () {
	if ( ! is_user_logged_in() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) return;
	// admin-post.php / admin-ajax.php fire admin_init BEFORE their action handlers;
	// never bounce there or we'd kill the tutor's own form submits (e.g. lesson log).
	if ( ! empty( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], [ 'admin-post.php', 'admin-ajax.php' ], true ) ) return;
	if ( mgk_is_tutor_user() ) {
		wp_safe_redirect( mgk_get_tutor_dashboard_url() );
		exit;
	}
}, 0 );
add_filter( 'show_admin_bar', function ( $show ) {
	return mgk_is_tutor_user() ? false : $show;
} );

/* ── Provisioning: create/link the tutor wp_user (agency action) ─────────── */

/**
 * Get-or-create the tutor wp_user for an mg_teacher, keyed by the tutor's email.
 * Idempotent: an already-linked tutor returns its existing user; a known email
 * (e.g. tutor who was also once a parent) is linked + granted the tutor role.
 * Returns int user_id or WP_Error.
 */
function mgk_tutor_provision_account( $teacher_id ) {
	$teacher_id = (int) $teacher_id;
	if ( ! $teacher_id || get_post_type( $teacher_id ) !== 'mg_teacher' ) {
		return new WP_Error( 'mgk_bad_tutor', 'Not a valid tutor record.' );
	}

	// Already linked → done.
	$existing_uid = mgk_teacher_owner_user_id( $teacher_id );
	if ( $existing_uid ) return $existing_uid;

	$email = mgk_teacher_email( $teacher_id );
	if ( ! $email || ! is_email( $email ) ) {
		return new WP_Error( 'mgk_no_email', 'Add a valid tutor email before provisioning a login.' );
	}

	$name = get_the_title( $teacher_id ) ?: 'Tutor';

	$user = get_user_by( 'email', $email );
	if ( $user ) {
		$user_id = (int) $user->ID;
		// Grant the tutor role alongside any existing role (a tutor may also be a parent).
		$u = new WP_User( $user_id );
		if ( ! in_array( MGK_TUTOR_ROLE, (array) $u->roles, true ) ) {
			$u->add_role( MGK_TUTOR_ROLE );
		}
	} else {
		$login = $email;
		if ( username_exists( $login ) ) {
			$login = $email . '-' . wp_generate_password( 4, false, false );
		}
		$user_id = wp_insert_user( [
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24, true, true ), // strong, never disclosed
			'display_name' => $name,
			'first_name'   => $name,
			'role'         => MGK_TUTOR_ROLE,
		] );
		if ( is_wp_error( $user_id ) ) return $user_id;
		$user_id = (int) $user_id;
	}

	// Link both ways (one-to-one).
	update_user_meta( $user_id, MGK_TUTOR_POST_META, $teacher_id );
	update_post_meta( $teacher_id, MGK_TUTOR_USER_META, $user_id );

	do_action( 'mgk_tutor_account_provisioned', $user_id, $teacher_id, $email );
	return $user_id;
}

/** Email a tutor their passwordless sign-in link (reuses the magic-link stack). */
function mgk_tutor_send_login_link( $user_id, $purpose = 'login' ) {
	$user = get_user_by( 'id', (int) $user_id );
	if ( ! $user ) return [];
	$url  = mgk_magic_link_url( (int) $user_id, mgk_get_tutor_dashboard_url() );
	$site = get_bloginfo( 'name' );

	// Dev aid: record the link when WP_DEBUG is on (same log the parent flow uses),
	// so the owner can test without a configured mail server.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$log = get_option( 'mgk_login_link_log', [] );
		if ( ! is_array( $log ) ) $log = [];
		array_unshift( $log, [ 'user_id' => (int) $user_id, 'email' => $user->user_email, 'url' => $url, 'purpose' => 'tutor_' . $purpose, 'at' => gmdate( 'c' ) ] );
		update_option( 'mgk_login_link_log', array_slice( $log, 0, 20 ) );
	}

	$subject = $purpose === 'invite'
		? sprintf( 'Your %s tutor account — sign in', $site )
		: sprintf( 'Sign in to %s', $site );
	$body = sprintf(
		"Hi,\n\nClick to sign in to your tutor dashboard — no password needed. The link works once and expires in 15 minutes:\n\n%s\n\n— %s",
		$url, $site
	);
	$used = [];
	if ( wp_mail( $user->user_email, $subject, $body ) ) $used[] = 'email';
	do_action( 'mgk_tutor_login_link_sent', (int) $user_id, $used, $purpose );
	return $used;
}

/* ── Login surface: /tutor/login/ → [mgk_tutor_login] (no JS) ─────────────── */

function mgk_get_tutor_login_url() {
	return mgk_url( '/tutor/login/' );
}

/** Request a tutor sign-in link (admin-post; generic responses; per-IP rate limit). */
function mgk_tutor_login_request_handler() {
	$back  = mgk_get_tutor_login_url();
	$email = isset( $_POST['mgk_tutor_login_email'] ) ? sanitize_email( wp_unslash( $_POST['mgk_tutor_login_email'] ) ) : '';
	$nonce = isset( $_POST['mgk_tutor_login_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mgk_tutor_login_nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'mgk_tutor_login_request' ) || ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'email', $back ) ); exit;
	}

	// Per-IP rate limit: 6 / 15 min.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$rlk = 'mgk_tutor_login_rl_' . md5( $ip );
	$tries = (int) get_transient( $rlk );
	if ( $tries >= 6 ) { wp_safe_redirect( add_query_arg( 'err', 'rate', $back ) ); exit; }
	set_transient( $rlk, $tries + 1, 15 * MINUTE_IN_SECONDS );

	// Send ONLY to a real tutor account — but always respond generically.
	$user = get_user_by( 'email', $email );
	if ( $user && in_array( MGK_TUTOR_ROLE, (array) $user->roles, true ) ) {
		mgk_tutor_send_login_link( (int) $user->ID, 'login' );
	}

	$mask = function_exists( 'mgk_mask_email' ) ? ( mgk_mask_email( $email ) ?: $email ) : $email;
	wp_safe_redirect( add_query_arg( [ 'sent' => 1, 'e' => rawurlencode( $mask ) ], $back ) );
	exit;
}
add_action( 'admin_post_nopriv_mgk_tutor_login_request', 'mgk_tutor_login_request_handler' );
add_action( 'admin_post_mgk_tutor_login_request', 'mgk_tutor_login_request_handler' );

/** [mgk_tutor_login] — the tutor sign-in card (reuses mgk_auth_styles). */
add_shortcode( 'mgk_tutor_login', function () {
	$state = isset( $_GET['mgk_auth'] ) ? sanitize_key( wp_unslash( $_GET['mgk_auth'] ) ) : '';
	$err  = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';
	$sent = ! empty( $_GET['sent'] );
	$mask = isset( $_GET['e'] ) ? sanitize_text_field( wp_unslash( $_GET['e'] ) ) : '';
	$guard_return = isset( $_GET['mgk_return'] ) ? esc_url_raw( urldecode( wp_unslash( $_GET['mgk_return'] ) ) ) : '';
	$styles = function_exists( 'mgk_auth_styles' ) ? mgk_auth_styles() : '';

	ob_start();
	echo $styles; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<div class="mgk-auth"><div class="mgk-auth__card">';

	// Already signed in as a tutor → straight to dashboard.
	if ( mgk_is_tutor_user() ) {
		$me = wp_get_current_user();
		echo '<h1>You’re signed in</h1>';
		echo '<p>Signed in as <strong>' . esc_html( $me->display_name ?: $me->user_email ) . '</strong>.</p>';
		echo '<a class="mgk-auth__btn" style="text-decoration:none;text-align:center" href="' . esc_url( mgk_get_tutor_dashboard_url() ) . '">Go to my dashboard →</a>';
		echo '<p class="mgk-auth__foot">Not you? <a href="' . esc_url( function_exists( 'mgk_logout_url' ) ? mgk_logout_url( mgk_get_tutor_login_url() ) : wp_logout_url() ) . '">Log out</a>.</p>';
		echo '</div></div>';
		return ob_get_clean();
	}

	if ( $sent ) {
		echo '<h1>Check your inbox</h1>';
		echo '<div class="mgk-auth__notice mgk-auth__notice--ok">If <strong>' . esc_html( $mask ?: 'your email' ) . '</strong> is a registered tutor, we’ve sent a one-time sign-in link. It works once and expires in 15 minutes.</div>';
		echo '<p>Didn’t get it? Check spam, or <a href="' . esc_url( mgk_get_tutor_login_url() ) . '">try again</a>.</p>';
		if ( function_exists( 'mgk_auth_dev_link' ) ) echo mgk_auth_dev_link( 'tutor' ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div></div>';
		return ob_get_clean();
	}

	echo '<h1>Tutor sign in</h1>';
	echo '<p>Enter your email and we’ll send you a one-time sign-in link — no password needed.</p>';
	if ( $state === 'staff_guard' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--warn">You are signed in as agency staff in this browser. Open the tutor sign-in link in another browser/profile so wp-admin stays signed in, or explicitly switch this browser to the tutor account.</div>';
		if ( $guard_return ) {
			$switch_url = add_query_arg( 'mgk_switch', '1', $guard_return );
			echo '<a class="mgk-auth__btn" style="text-decoration:none;text-align:center" href="' . esc_url( $switch_url ) . '">Switch this browser to tutor →</a>';
			echo '<p class="mgk-auth__foot">This will replace the current wp-admin session in this browser.</p>';
			echo '</div></div>';
			return ob_get_clean();
		}
	} elseif ( $err === 'email' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--err">Please enter a valid email address.</div>';
	} elseif ( $err === 'rate' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--err">Too many requests. Please wait a few minutes and try again.</div>';
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="mgk_tutor_login_request">';
	wp_nonce_field( 'mgk_tutor_login_request', 'mgk_tutor_login_nonce' );
	echo '<label for="mgk_tutor_login_email">Email</label>';
	echo '<input type="email" id="mgk_tutor_login_email" name="mgk_tutor_login_email" required autocomplete="email" placeholder="you@example.com">';
	echo '<button type="submit" class="mgk-auth__btn">Email me a sign-in link →</button>';
	echo '</form>';
	echo '<p class="mgk-auth__foot">Not a tutor yet? <a href="' . esc_url( mgk_url( '/become-a-tutor/' ) ) . '">Apply to teach</a>.</p>';
	echo '</div></div>';
	return ob_get_clean();
} );

/* ── Page provisioning: /tutor/login/ (nested under the Tutor page) ──────── */
add_action( 'init', function () {
	if ( get_option( 'mgk_tutor_login_page_created' ) ) return;
	$parent = get_page_by_path( 'tutor' );
	if ( ! get_page_by_path( 'tutor/login' ) ) {
		wp_insert_post( [
			'post_type'    => 'page',
			'post_title'   => 'Tutor Login',
			'post_name'    => 'login',
			'post_parent'  => $parent ? (int) $parent->ID : 0,
			'post_content' => '[mgk_tutor_login]',
			'post_status'  => 'publish',
		] );
	}
	update_option( 'mgk_tutor_login_page_created', 1 );
}, 100 );

/* ── Admin: "Tutor login" meta-box on the mg_teacher edit screen ──────────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'mgk_tutor_login_box', 'Tutor login', 'mgk_tutor_login_metabox', 'mg_teacher', 'side', 'default' );
} );

function mgk_tutor_login_metabox( $post ) {
	$teacher_id = (int) $post->ID;
	$email = mgk_teacher_email( $teacher_id );
	$uid   = mgk_teacher_owner_user_id( $teacher_id );

	wp_nonce_field( 'mgk_tutor_email_' . $teacher_id, 'mgk_tutor_email_nonce' );
	echo '<p><label for="mgk_tutor_email_field"><strong>Login / contact email</strong></label>';
	echo '<input type="email" id="mgk_tutor_email_field" name="mgk_tutor_email" value="' . esc_attr( $email ) . '" class="widefat" placeholder="tutor@example.com"></p>';

	if ( $uid ) {
		$u = get_user_by( 'id', $uid );
		echo '<p style="color:#1a7f37"><strong>✓ Login active</strong><br><span class="description">' . esc_html( $u ? $u->user_email : '' ) . '</span></p>';
		$invite = wp_nonce_url( admin_url( 'admin-post.php?action=mgk_tutor_provision&teacher=' . $teacher_id . '&send=1' ), 'mgk_tutor_provision_' . $teacher_id );
		echo '<a class="button button-small" href="' . esc_url( $invite ) . '">Re-send sign-in link</a>';
	} else {
		echo '<p class="description">No login yet. Save the email, then provision a passwordless account and (optionally) email the tutor an invite.</p>';
		$prov = wp_nonce_url( admin_url( 'admin-post.php?action=mgk_tutor_provision&teacher=' . $teacher_id . '&send=1' ), 'mgk_tutor_provision_' . $teacher_id );
		echo '<a class="button button-primary button-small" href="' . esc_url( $prov ) . '">Provision &amp; send invite</a>';
	}
}

/** Persist the tutor email from the meta-box. */
add_action( 'save_post_mg_teacher', function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['mgk_tutor_email_nonce'] ) ) return;
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgk_tutor_email_nonce'] ) ), 'mgk_tutor_email_' . $post_id ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( isset( $_POST['mgk_tutor_email'] ) ) {
		$email = sanitize_email( wp_unslash( $_POST['mgk_tutor_email'] ) );
		update_post_meta( $post_id, 'mgk_tutor_email', $email );
	}
}, 10, 1 );

/** Admin action: provision (and optionally invite) a tutor login. */
add_action( 'admin_post_mgk_tutor_provision', function () {
	if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Permission denied.' );
	$teacher_id = isset( $_GET['teacher'] ) ? (int) $_GET['teacher'] : 0;
	$send       = ! empty( $_GET['send'] );
	check_admin_referer( 'mgk_tutor_provision_' . $teacher_id );

	$res = mgk_tutor_provision_account( $teacher_id );
	if ( is_wp_error( $res ) ) {
		$args = [ 'mgk_tutor_prov' => 0, 'mgk_tutor_msg' => rawurlencode( $res->get_error_message() ) ];
	} else {
		if ( $send ) mgk_tutor_send_login_link( (int) $res, 'invite' );
		$args = [ 'mgk_tutor_prov' => 1 ];
	}
	wp_safe_redirect( add_query_arg( $args, get_edit_post_link( $teacher_id, 'raw' ) ) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( ! isset( $_GET['mgk_tutor_prov'] ) ) return;
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'mg_teacher' ) return;
	if ( $_GET['mgk_tutor_prov'] === '1' ) {
		echo '<div class="notice notice-success is-dismissible"><p>Tutor login provisioned. A sign-in link was emailed (and logged under <code>mgk_login_link_log</code> when WP_DEBUG is on).</p></div>';
	} else {
		$m = isset( $_GET['mgk_tutor_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['mgk_tutor_msg'] ) ) : 'Could not provision login.';
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $m ) );
	}
} );
