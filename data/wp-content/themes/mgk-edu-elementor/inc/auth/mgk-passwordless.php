<?php
/**
 * MGK Passwordless Auth (DATA CORE) — magic-link + OTP + session rules.
 * =====================================================================
 * Primary auth for parents (FR-BOOK-07 / BR-22). No passwords, ever.
 *
 *   - Magic link : single-use, 15-min TTL. Click → 30-day session.
 *   - Session    : 30 days, auto-logout after 24h of inactivity (idle).
 *   - OTP        : 6-digit phone fallback, 10-min TTL, 5 tries.
 *   - Delivery   : Email (always) + WhatsApp (phone + configured template).
 *                  SMS/Twilio is a documented stub (mgk_sms_send) for a later
 *                  phase — owner: Email+WhatsApp first, SMS fallback later.
 *
 * Security: tokens/codes are stored HASHED (HMAC w/ wp_salt) in a transient keyed
 * by the hash, so a transient/DB dump can't be replayed. Consuming a link deletes
 * it (single-use). Hash compares use hash_equals (timing-safe).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_MAGIC_TTL' ) )     define( 'MGK_MAGIC_TTL', 15 * MINUTE_IN_SECONDS );
if ( ! defined( 'MGK_SESSION_DAYS' ) )  define( 'MGK_SESSION_DAYS', 30 );
if ( ! defined( 'MGK_IDLE_TTL' ) )      define( 'MGK_IDLE_TTL', DAY_IN_SECONDS );        // 24h
if ( ! defined( 'MGK_OTP_TTL' ) )       define( 'MGK_OTP_TTL', 10 * MINUTE_IN_SECONDS );
if ( ! defined( 'MGK_OTP_MAX_TRIES' ) ) define( 'MGK_OTP_MAX_TRIES', 5 );

/** Stable HMAC of a secret value (token/code) — same key across issue/verify. */
function mgk_auth_hash( $value ) {
	return hash_hmac( 'sha256', (string) $value, wp_salt( 'auth' ) );
}

/* ── Magic link: issue ───────────────────────────────────────────────────── */

/**
 * Issue a single-use magic-link URL for a user. Stores only the token HASH
 * (transient keyed by hash) with the user + redirect; TTL = 15 min.
 */
function mgk_magic_link_url( $user_id, $redirect = '' ) {
	$user_id = (int) $user_id;
	if ( ! $user_id ) return '';
	$token = wp_generate_password( 32, false, false );
	set_transient( 'mgk_ml_' . mgk_auth_hash( $token ), [
		'user_id'  => $user_id,
		'redirect' => esc_url_raw( $redirect ?: mgk_cta_url( 'dashboard' ) ),
	], MGK_MAGIC_TTL );
	return add_query_arg( 'mgk_ml', $token, mgk_url( '/auth/' ) );
}

/* ── Magic link: consume (?mgk_ml=… on any URL) ──────────────────────────── */

add_action( 'template_redirect', function () {
	if ( empty( $_GET['mgk_ml'] ) ) return;

	$token = sanitize_text_field( wp_unslash( $_GET['mgk_ml'] ) );
	$key   = 'mgk_ml_' . mgk_auth_hash( $token );
	$data  = get_transient( $key );
	$fail  = mgk_url( '/login/?mgk_auth=expired' );

	if ( ! is_array( $data ) || empty( $data['user_id'] ) ) {
		wp_safe_redirect( $fail ); exit;
	}
	delete_transient( $key ); // single-use: burn it before logging in

	$user = get_user_by( 'id', (int) $data['user_id'] );
	if ( ! $user ) { wp_safe_redirect( $fail ); exit; }

	// Establish a (filtered) 30-day session.
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	update_user_meta( $user->ID, 'mgk_parent_email_verified', 1 ); // clicking proves email control
	mgk_session_touch( $user->ID );

	wp_safe_redirect( ! empty( $data['redirect'] ) ? $data['redirect'] : mgk_cta_url( 'dashboard' ) );
	exit;
}, 5 );

/* ── Session: 30-day cookie + 24h idle auto-logout ───────────────────────── */

add_filter( 'auth_cookie_expiration', function ( $length, $user_id, $remember ) {
	$u = get_user_by( 'id', $user_id );
	if ( $u && in_array( MGK_PARENT_ROLE, (array) $u->roles, true ) ) {
		return MGK_SESSION_DAYS * DAY_IN_SECONDS;
	}
	return $length;
}, 10, 3 );

/** Record last-activity timestamp for the idle check. */
function mgk_session_touch( $user_id ) {
	update_user_meta( (int) $user_id, 'mgk_last_active', time() );
}

add_action( 'init', function () {
	if ( ! is_user_logged_in() ) return;
	$u = wp_get_current_user();
	if ( ! in_array( MGK_PARENT_ROLE, (array) $u->roles, true ) ) return;

	// Never redirect during REST / AJAX / cron — that would break the call.
	$is_infra = wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );

	$last = (int) get_user_meta( $u->ID, 'mgk_last_active', true );
	if ( $last && ( time() - $last ) > MGK_IDLE_TTL ) {
		// Idle past 24h: force the logout+redirect only on a real front-end page
		// view. On infra requests just stop extending — the next page view bounces.
		if ( ! $is_infra && ! is_admin() ) {
			wp_logout();
			wp_safe_redirect( mgk_url( '/login/?mgk_auth=idle' ) );
			exit;
		}
		return;
	}
	// Throttle writes to ~once / 5 min to avoid a usermeta write per request.
	if ( ! $last || ( time() - $last ) > 5 * MINUTE_IN_SECONDS ) {
		mgk_session_touch( $u->ID );
	}
}, 20 );

/* ── OTP (phone fallback) ────────────────────────────────────────────────── */

/** Issue a 6-digit OTP for a user; store hash + try-count; deliver via channels. */
function mgk_otp_issue( $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id ) return new WP_Error( 'mgk_no_user', 'No user.' );
	$code = str_pad( (string) random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );
	set_transient( 'mgk_otp_' . $user_id, [ 'hash' => mgk_auth_hash( $code ), 'tries' => 0 ], MGK_OTP_TTL );
	mgk_auth_send_otp( $user_id, $code, (string) get_user_meta( $user_id, 'mgk_parent_phone', true ) );
	return true;
}

/** Verify an OTP. Returns true / WP_Error. On success, logs the user in. */
function mgk_otp_verify( $user_id, $code ) {
	$user_id = (int) $user_id;
	$rec = get_transient( 'mgk_otp_' . $user_id );
	if ( ! is_array( $rec ) ) {
		return new WP_Error( 'mgk_otp_expired', 'Code expired. Request a new one.' );
	}
	if ( (int) $rec['tries'] >= MGK_OTP_MAX_TRIES ) {
		delete_transient( 'mgk_otp_' . $user_id );
		return new WP_Error( 'mgk_otp_locked', 'Too many attempts. Request a new code.' );
	}
	$input = preg_replace( '/\D/', '', (string) $code );
	if ( ! hash_equals( (string) $rec['hash'], mgk_auth_hash( $input ) ) ) {
		$rec['tries']++;
		set_transient( 'mgk_otp_' . $user_id, $rec, MGK_OTP_TTL );
		return new WP_Error( 'mgk_otp_bad', 'Incorrect code.' );
	}
	delete_transient( 'mgk_otp_' . $user_id );
	update_user_meta( $user_id, 'mgk_parent_phone_verified', 1 );
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );
	mgk_session_touch( $user_id );
	return true;
}

/* ── Delivery channels (Email + WhatsApp now; SMS/Twilio stub) ───────────── */

/**
 * Email (+WhatsApp) a sign-in magic link. The function the identity claim calls
 * after creating/attaching the account. Returns the list of channels used.
 */
function mgk_parent_send_login_link( $user_id, $redirect = '', $purpose = 'login' ) {
	$user = get_user_by( 'id', (int) $user_id );
	if ( ! $user ) return [];
	$url  = mgk_magic_link_url( $user_id, $redirect );
	$site = get_bloginfo( 'name' );
	$used = [];

	// Dev aid: when WP_DEBUG is on, record the link so the owner can test
	// S11→S13 without a configured mail server. Disable in production.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$log = get_option( 'mgk_login_link_log', [] );
		if ( ! is_array( $log ) ) $log = [];
		array_unshift( $log, [ 'user_id' => (int) $user_id, 'email' => $user->user_email, 'url' => $url, 'purpose' => $purpose, 'at' => gmdate( 'c' ) ] );
		update_option( 'mgk_login_link_log', array_slice( $log, 0, 20 ) );
	}

	// 1) Email — the primary auth channel.
	$subject = $purpose === 'welcome'
		? sprintf( 'Your %s account — sign in', $site )
		: sprintf( 'Sign in to %s', $site );
	$body = sprintf(
		"Hi,\n\nClick to sign in — no password needed. The link works once and expires in 15 minutes:\n\n%s\n\n— %s",
		$url, $site
	);
	if ( wp_mail( $user->user_email, $subject, $body ) ) $used[] = 'email';

	// 2) WhatsApp — when a phone is on file and a template is configured.
	$phone = (string) get_user_meta( $user_id, 'mgk_parent_phone', true );
	if ( $phone && function_exists( 'mgk_wa_send' ) && function_exists( 'mgk_wa_config' ) ) {
		$cfg = mgk_wa_config();
		if ( ! empty( $cfg['tpl_login_link'] ) ) {
			mgk_wa_send( $phone, $cfg['tpl_login_link'], [ $site, $url ], $cfg['tpl_proposals_lang'] ?? 'en' );
			$used[] = 'whatsapp';
		}
	}

	do_action( 'mgk_parent_login_link_sent', (int) $user_id, $used, $purpose );
	return $used;
}

/** Deliver an OTP over the available channels (Email always; WhatsApp, then SMS stub). */
function mgk_auth_send_otp( $user_id, $code, $phone = '' ) {
	$user = get_user_by( 'id', (int) $user_id );
	$sent = [];

	if ( $user ) {
		wp_mail( $user->user_email, 'Your verification code',
			sprintf( "Your %s code is %s. It expires in 10 minutes.", get_bloginfo( 'name' ), $code ) );
		$sent[] = 'email';
	}

	if ( $phone && function_exists( 'mgk_wa_send' ) && function_exists( 'mgk_wa_config' ) ) {
		$cfg = mgk_wa_config();
		if ( ! empty( $cfg['tpl_otp'] ) ) {
			mgk_wa_send( $phone, $cfg['tpl_otp'], [ $code ], $cfg['tpl_proposals_lang'] ?? 'en' );
			$sent[] = 'whatsapp';
		} elseif ( function_exists( 'mgk_sms_send' ) ) {
			mgk_sms_send( $phone, sprintf( 'Your code is %s', $code ) );
			$sent[] = 'sms';
		}
	}
	return $sent;
}

/**
 * SMS via Twilio — STUB for a later phase (owner: Email+WhatsApp first, SMS
 * fallback later). Logs to the `mgk_sms_log` option in demo mode; drop the real
 * Twilio REST call here once credentials live in MGK Site Settings.
 */
function mgk_sms_send( $to, $message ) {
	$log = get_option( 'mgk_sms_log', [] );
	if ( ! is_array( $log ) ) $log = [];
	array_unshift( $log, [ 'to' => sanitize_text_field( $to ), 'message' => (string) $message, 'at' => gmdate( 'c' ) ] );
	update_option( 'mgk_sms_log', array_slice( $log, 0, 50 ) );
	return [ 'ok' => true, 'mode' => 'stub' ];
}
