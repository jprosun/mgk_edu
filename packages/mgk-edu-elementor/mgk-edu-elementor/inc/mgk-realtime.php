<?php
/**
 * Real-time layer — PREPARED for production, works now via polling.
 * =================================================================
 * The SRS wants WebSocket events (`lesson.logged`, `message.received`). A WS
 * server (Pusher/Ably/Soketi) is infra you add on the production host. This file
 * makes real-time PLUGGABLE so it "just works" when you add provider keys:
 *
 *   - mgk_realtime_broadcast($channel,$event,$payload) — server-side emit. No-op
 *     (logged) until a provider is configured; sends over Pusher's HTTP API when
 *     keys are set in MGK Site → settings (realtime_driver=pusher + key/secret/app_id).
 *   - JS client (assets/js/mgk-realtime.js): connects to the provider when
 *     configured, else FALLS BACK to polling /mgk/v1/realtime/poll. So the
 *     notification badge stays live with or without a WS server.
 *
 * Hooks: fires on `mgk_lesson_logged` and `mgk_message_sent` → the parent's
 * private channel. Channels: `private-parent-{id}`, `private-tutor-{id}`.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Resolve the real-time provider config from site settings. */
function mgk_realtime_config() {
	$get = function ( $k, $d = '' ) { return function_exists( 'mgk_site_setting' ) ? ( (string) mgk_site_setting( $k ) ?: $d ) : $d; };
	$driver = $get( 'realtime_driver', 'none' );
	$key    = $get( 'pusher_key' );
	$secret = $get( 'pusher_secret' );
	$app_id = $get( 'pusher_app_id' );
	return [
		'driver'  => $driver,
		'key'     => $key,
		'secret'  => $secret,
		'app_id'  => $app_id,
		'cluster' => $get( 'pusher_cluster', 'ap1' ),
		'enabled' => ( $driver === 'pusher' && $key && $secret && $app_id ),
	];
}

/**
 * Emit a real-time event. Returns true if sent over the wire. When no provider
 * is configured it's a no-op (the JS polling fallback covers updates), but it
 * still fires the local `mgk_realtime_broadcast` action + logs under WP_DEBUG.
 */
function mgk_realtime_broadcast( $channel, $event, $payload = [] ) {
	$cfg = mgk_realtime_config();
	do_action( 'mgk_realtime_broadcast', $channel, $event, $payload );

	if ( empty( $cfg['enabled'] ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$log = get_option( 'mgk_realtime_log', [] );
			if ( ! is_array( $log ) ) $log = [];
			array_unshift( $log, [ 'channel' => $channel, 'event' => $event, 'payload' => $payload, 'at' => gmdate( 'c' ), 'sent' => false ] );
			update_option( 'mgk_realtime_log', array_slice( $log, 0, 30 ) );
		}
		return false;
	}
	if ( $cfg['driver'] === 'pusher' ) {
		return mgk_pusher_trigger( $cfg, $channel, $event, $payload );
	}
	return false;
}

/** Pusher HTTP trigger (https://pusher.com/docs/channels/library_auth_reference/rest-api/). */
function mgk_pusher_trigger( $cfg, $channel, $event, $payload ) {
	$body = wp_json_encode( [ 'name' => $event, 'channels' => [ $channel ], 'data' => wp_json_encode( $payload ) ] );
	$path = "/apps/{$cfg['app_id']}/events";
	$params = [
		'auth_key'       => $cfg['key'],
		'auth_timestamp' => (string) time(),
		'auth_version'   => '1.0',
		'body_md5'       => md5( $body ),
	];
	ksort( $params );
	$qs   = http_build_query( $params );
	$sign = hash_hmac( 'sha256', "POST\n{$path}\n{$qs}", $cfg['secret'] );
	$url  = "https://api-{$cfg['cluster']}.pusher.com{$path}?{$qs}&auth_signature={$sign}";
	$res  = wp_remote_post( $url, [ 'headers' => [ 'Content-Type' => 'application/json' ], 'body' => $body, 'timeout' => 4 ] );
	return ! is_wp_error( $res ) && (int) wp_remote_retrieve_response_code( $res ) < 300;
}

/* ── Emit on domain events ───────────────────────────────────────────────── */

add_action( 'mgk_lesson_logged', function ( $lesson_id, $child_id, $enr_id ) {
	$parent = (int) get_post_meta( (int) $child_id, 'mgk_child_parent_user', true );
	if ( $parent ) {
		mgk_realtime_broadcast( "private-parent-{$parent}", 'lesson.logged', [ 'lesson_id' => (int) $lesson_id, 'child_id' => (int) $child_id ] );
	}
}, 10, 3 );

add_action( 'mgk_message_sent', function ( $message_id, $thread_key, $to_user_id ) {
	if ( $to_user_id ) {
		$role = user_can( (int) $to_user_id, 'edit_posts' ) ? 'tutor' : 'parent';
		mgk_realtime_broadcast( "private-{$role}-{$to_user_id}", 'message.received', [ 'message_id' => (int) $message_id, 'thread' => $thread_key ] );
	}
}, 10, 3 );

/* ── Polling fallback endpoint + client ──────────────────────────────────── */

add_action( 'rest_api_init', function () {
	register_rest_route( 'mgk/v1', '/realtime/poll', [
		'methods'             => 'GET',
		'callback'            => 'mgk_realtime_poll',
		'permission_callback' => function () { return is_user_logged_in(); },
	] );
} );

/** Lightweight state poll: unread total + last lesson timestamp for the user. */
function mgk_realtime_poll() {
	$uid = get_current_user_id();
	$unread = function_exists( 'mgk_parent_total_unread' ) ? (int) mgk_parent_total_unread( $uid ) : 0;
	return rest_ensure_response( [
		'unread'   => $unread,
		'server_ts'=> time(),
	] );
}

/** Expose config + enqueue the client for signed-in parents. */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_user_logged_in() ) return;
	if ( ! ( function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) ) return;

	$cfg = mgk_realtime_config();
	$path = get_stylesheet_directory() . '/assets/js/mgk-realtime.js';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : '1';
	wp_enqueue_script( 'mgk-realtime', get_stylesheet_directory_uri() . '/assets/js/mgk-realtime.js', [], $ver, true );
	wp_localize_script( 'mgk-realtime', 'mgkRealtime', [
		'enabled'  => (bool) $cfg['enabled'],
		'driver'   => $cfg['driver'],
		'key'      => $cfg['enabled'] ? $cfg['key'] : '',   // public key only
		'cluster'  => $cfg['cluster'],
		'channel'  => 'private-parent-' . get_current_user_id(),
		'pollUrl'  => esc_url_raw( rest_url( 'mgk/v1/realtime/poll' ) ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		'pollMs'   => 25000,
	] );
} );
