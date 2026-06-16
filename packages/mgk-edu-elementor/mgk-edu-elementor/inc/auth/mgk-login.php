<?php
/**
 * Passwordless LOGIN surface — the returning-parent entry point + logout.
 * ======================================================================
 *  /login/  →  [mgk_login]         enter email → emailed (+WhatsApp) a magic
 *                                  sign-in link. No password. The link is
 *                                  consumed by mgk-passwordless.php (?mgk_ml=).
 *  /auth/   →  [mgk_auth_landing]  where the magic link lands; the consumer
 *                                  redirects away on success — this is only the
 *                                  fallback view (bad/again).
 *  logout   →  ?mgk_logout=1 (nonce) → wp_logout → /login/?signed_out=1
 *
 * Privacy: generic responses (never reveal whether an email has an account).
 * Abuse: per-IP rate limit. No JS dependency (posts to admin-post.php).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Logout ──────────────────────────────────────────────────────────────── */

/** Nonce-protected logout URL that lands back on /login/. */
function mgk_logout_url( $redirect = '' ) {
	$redirect = $redirect ?: mgk_url( '/login/?signed_out=1' );
	return wp_nonce_url(
		add_query_arg( [ 'mgk_logout' => 1, 'r' => rawurlencode( $redirect ) ], mgk_url( '/' ) ),
		'mgk_logout'
	);
}

add_action( 'template_redirect', function () {
	if ( empty( $_GET['mgk_logout'] ) ) return;
	$ok = isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mgk_logout' );
	$r  = isset( $_GET['r'] ) ? esc_url_raw( urldecode( wp_unslash( $_GET['r'] ) ) ) : '';
	if ( ! $ok ) { wp_safe_redirect( mgk_url( '/login/' ) ); exit; }
	wp_logout();
	wp_safe_redirect( $r ?: mgk_url( '/login/?signed_out=1' ) );
	exit;
}, 4 );

/* ── Request a sign-in link (admin-post; no JS) ──────────────────────────── */

function mgk_login_request_handler() {
	$back  = mgk_url( '/login/' );
	$email = isset( $_POST['mgk_login_email'] ) ? sanitize_email( wp_unslash( $_POST['mgk_login_email'] ) ) : '';
	$nonce = isset( $_POST['mgk_login_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mgk_login_nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'mgk_login_request' ) || ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'email', $back ) ); exit;
	}

	// Per-IP rate limit: 6 / 15 min.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$rlk = 'mgk_login_rl_' . md5( $ip );
	$tries = (int) get_transient( $rlk );
	if ( $tries >= 6 ) { wp_safe_redirect( add_query_arg( 'err', 'rate', $back ) ); exit; }
	set_transient( $rlk, $tries + 1, 15 * MINUTE_IN_SECONDS );

	// Send the link ONLY to a real parent account — but always respond generically.
	$user = get_user_by( 'email', $email );
	if ( $user && in_array( 'mgk_parent', (array) $user->roles, true ) && function_exists( 'mgk_parent_send_login_link' ) ) {
		mgk_parent_send_login_link( (int) $user->ID, mgk_cta_url( 'dashboard' ), 'login' );
	}

	$mask = function_exists( 'mgk_mask_email' ) ? ( mgk_mask_email( $email ) ?: $email ) : $email;
	wp_safe_redirect( add_query_arg( [ 'sent' => 1, 'e' => rawurlencode( $mask ) ], $back ) );
	exit;
}
add_action( 'admin_post_nopriv_mgk_login_request', 'mgk_login_request_handler' );
add_action( 'admin_post_mgk_login_request', 'mgk_login_request_handler' );

/* ── Shared minimal styling for the auth pages ───────────────────────────── */
function mgk_auth_styles() {
	return '<style>
	.mgk-auth{max-width:440px;margin:6vh auto;padding:0 20px;font:15px/1.6 -apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#1a1a1a}
	.mgk-auth__card{background:#fff;border:1px solid #e3e5e8;border-radius:14px;padding:32px}
	.mgk-auth h1{font-size:22px;margin:0 0 6px}
	.mgk-auth p{color:#50575e;margin:0 0 18px}
	.mgk-auth label{display:block;font-size:12px;letter-spacing:.04em;text-transform:uppercase;color:#646970;margin:0 0 6px}
	.mgk-auth input[type=email]{width:100%;padding:12px 14px;border:1.5px solid #cfd3d8;border-radius:9px;font:inherit;box-sizing:border-box}
	.mgk-auth input[type=email]:focus{outline:none;border-color:#d34836;box-shadow:0 0 0 3px rgba(211,72,54,.12)}
	.mgk-auth__btn{display:block;width:100%;margin-top:14px;padding:13px 18px;background:#d34836;color:#fff;border:0;border-radius:9px;font:600 15px/1 inherit;cursor:pointer}
	.mgk-auth__btn:hover{background:#bb3b2b}
	.mgk-auth__notice{padding:12px 14px;border-radius:9px;margin:0 0 18px;font-size:14px}
	.mgk-auth__notice--ok{background:#eaf6ec;border:1px solid #b5e0bd;color:#1a7f37}
	.mgk-auth__notice--warn{background:#fff5e6;border:1px solid #f5d8a8;color:#8a5a00}
	.mgk-auth__notice--err{background:#fdecec;border:1px solid #f3b9b6;color:#b32d2e}
	.mgk-auth__dev{margin-top:16px;padding:12px 14px;background:#f3f4f6;border:1px dashed #c3c7cc;border-radius:9px;font-size:12px;word-break:break-all}
	.mgk-auth__foot{margin-top:16px;font-size:13px;color:#646970;text-align:center}
	.mgk-auth__foot a{color:#d34836}
	</style>';
}

/** Dev-only: the most recent generated link (so it is testable without SMTP). */
function mgk_auth_dev_link() {
	if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) return '';
	$log = get_option( 'mgk_login_link_log', [] );
	if ( empty( $log ) || empty( $log[0]['url'] ) ) return '';
	return '<div class="mgk-auth__dev"><strong>DEV (WP_DEBUG):</strong> mail isn’t configured locally, so here’s the link:<br><a href="' . esc_url( $log[0]['url'] ) . '">' . esc_html( $log[0]['url'] ) . '</a></div>';
}

/* ── [mgk_login] ─────────────────────────────────────────────────────────── */
add_shortcode( 'mgk_login', function () {
	$state = isset( $_GET['mgk_auth'] ) ? sanitize_key( wp_unslash( $_GET['mgk_auth'] ) ) : '';
	$err   = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';
	$sent  = ! empty( $_GET['sent'] );
	$out   = ! empty( $_GET['signed_out'] );
	$mask  = isset( $_GET['e'] ) ? sanitize_text_field( wp_unslash( $_GET['e'] ) ) : '';

	ob_start();
	echo mgk_auth_styles(); // phpcs:ignore
	echo '<div class="mgk-auth"><div class="mgk-auth__card">';

	// Already signed in → offer dashboard + logout (so another parent can switch).
	if ( function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) {
		$me = wp_get_current_user();
		$nm = get_user_meta( $me->ID, 'mgk_parent_full_name', true ) ?: $me->display_name;
		echo '<h1>You’re signed in</h1>';
		echo '<p>Signed in as <strong>' . esc_html( $nm ?: $me->user_email ) . '</strong>.</p>';
		echo '<a class="mgk-auth__btn" style="text-decoration:none;text-align:center" href="' . esc_url( mgk_cta_url( 'dashboard' ) ) . '">Go to my dashboard →</a>';
		echo '<p class="mgk-auth__foot">Not you? <a href="' . esc_url( mgk_logout_url() ) . '">Log out</a> to sign in with another email.</p>';
		echo '</div></div>';
		return ob_get_clean();
	}

	// Sent confirmation.
	if ( $sent ) {
		echo '<h1>Check your inbox</h1>';
		echo '<div class="mgk-auth__notice mgk-auth__notice--ok">If <strong>' . esc_html( $mask ?: 'your email' ) . '</strong> has an account, we’ve sent a one-time sign-in link. It works once and expires in 15 minutes.</div>';
		echo '<p>Didn’t get it? Check spam, or <a href="' . esc_url( mgk_url( '/login/' ) ) . '">try again</a>.</p>';
		echo mgk_auth_dev_link(); // phpcs:ignore
		echo '</div></div>';
		return ob_get_clean();
	}

	// State notices.
	echo '<h1>Sign in</h1>';
	echo '<p>Enter your email and we’ll send you a one-time sign-in link — no password needed.</p>';
	if ( $out ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--ok">You’ve been signed out.</div>';
	} elseif ( $state === 'expired' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--warn">That sign-in link expired or was already used. Enter your email for a fresh one.</div>';
	} elseif ( $state === 'idle' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--warn">You were signed out after 24 hours of inactivity. Enter your email to sign back in.</div>';
	}
	if ( $err === 'email' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--err">Please enter a valid email address.</div>';
	} elseif ( $err === 'rate' ) {
		echo '<div class="mgk-auth__notice mgk-auth__notice--err">Too many requests. Please wait a few minutes and try again.</div>';
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="mgk_login_request">';
	wp_nonce_field( 'mgk_login_request', 'mgk_login_nonce' );
	echo '<label for="mgk_login_email">Email</label>';
	echo '<input type="email" id="mgk_login_email" name="mgk_login_email" required autocomplete="email" placeholder="you@example.com">';
	echo '<button type="submit" class="mgk-auth__btn">Email me a sign-in link →</button>';
	echo '</form>';
	echo '<p class="mgk-auth__foot">New here? <a href="' . esc_url( mgk_cta_url( 'find-tutor' ) ) . '">Find a tutor</a> — your account is created automatically when you book.</p>';
	echo '</div></div>';
	return ob_get_clean();
} );

/* ── [mgk_auth_landing] — fallback view at /auth/ ────────────────────────── */
add_shortcode( 'mgk_auth_landing', function () {
	ob_start();
	echo mgk_auth_styles(); // phpcs:ignore
	echo '<div class="mgk-auth"><div class="mgk-auth__card">';
	echo '<h1>Signing you in…</h1>';
	echo '<p>If you’re not redirected automatically, your link may have expired.</p>';
	echo '<a class="mgk-auth__btn" style="text-decoration:none;text-align:center" href="' . esc_url( mgk_url( '/login/' ) ) . '">Request a new sign-in link →</a>';
	echo '</div></div>';
	return ob_get_clean();
} );
