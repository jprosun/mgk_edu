<?php
/**
 * Messaging model (S14) — DATA CORE for in-app parent↔tutor chat.
 * ===============================================================
 * Real thread/message storage in a custom table. A "thread" is the tuple
 * (parent, tutor, child) → key `p{parent}_t{tutor}_c{child}`; it exists once the
 * parent is linked to that tutor for that child (enrolment / booking).
 *
 * Privacy (FR-SYS-03 / poaching): every body is run through
 * mgk_mask_contact_details_in_message() on write — phone/email → [hidden] — and
 * a masked write FLAGS the message for agency review. Agency-monitored: agency
 * admins can read all threads (see mgk_user_can_view_thread).
 *
 * Real-time: on insert we fire `mgk_message_sent` → the realtime layer
 * (inc/mgk-realtime.php) broadcasts `message.received` (or polling updates it).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_MSG_DB_VERSION' ) ) define( 'MGK_MSG_DB_VERSION', '1.0' );

function mgk_msg_table() { global $wpdb; return $wpdb->prefix . 'mgk_messages'; }

/** Create/upgrade the messages table (version-gated; cheap on each load). */
function mgk_msg_install() {
	if ( get_option( 'mgk_msg_db_version' ) === MGK_MSG_DB_VERSION ) return;
	global $wpdb;
	$t  = mgk_msg_table();
	$cs = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( "CREATE TABLE {$t} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		thread_key VARCHAR(190) NOT NULL,
		child_id BIGINT UNSIGNED NULL,
		parent_user_id BIGINT UNSIGNED NOT NULL,
		tutor_post_id BIGINT UNSIGNED NOT NULL,
		sender_role VARCHAR(20) NOT NULL DEFAULT 'PARENT',
		sender_user_id BIGINT UNSIGNED NULL,
		body TEXT NULL,
		attachment VARCHAR(255) NULL,
		lesson_ref_id BIGINT UNSIGNED NULL,
		flagged TINYINT(1) NOT NULL DEFAULT 0,
		created_at_utc DATETIME NOT NULL,
		read_by_parent_at DATETIME NULL,
		read_by_tutor_at DATETIME NULL,
		PRIMARY KEY  (id),
		KEY idx_thread (thread_key, id),
		KEY idx_parent (parent_user_id),
		KEY idx_unread (thread_key, sender_role)
	) {$cs};" );
	update_option( 'mgk_msg_db_version', MGK_MSG_DB_VERSION );
}
add_action( 'init', 'mgk_msg_install', 5 );

/* ── Thread keys ─────────────────────────────────────────────────────────── */

function mgk_thread_key_for( $child_id, $tutor_id ) {
	$child_id = (int) $child_id; $tutor_id = (int) $tutor_id;
	$parent = (int) get_post_meta( $child_id, 'mgk_child_parent_user', true );
	if ( ! $parent || ! $tutor_id ) return '';
	return sprintf( 'p%d_t%d_c%d', $parent, $tutor_id, $child_id );
}
function mgk_thread_parse( $key ) {
	if ( preg_match( '/^p(\d+)_t(\d+)_c(\d+)$/', (string) $key, $m ) ) {
		return [ 'parent' => (int) $m[1], 'tutor' => (int) $m[2], 'child' => (int) $m[3] ];
	}
	return null;
}

/* ── Write / read ────────────────────────────────────────────────────────── */

function mgk_msg_insert( $args ) {
	global $wpdb;
	$a = wp_parse_args( $args, [
		'thread_key' => '', 'child_id' => 0, 'parent_user_id' => 0, 'tutor_post_id' => 0,
		'sender_role' => 'PARENT', 'sender_user_id' => 0, 'body' => '', 'attachment' => '', 'lesson_ref_id' => 0,
	] );
	$parts = mgk_thread_parse( $a['thread_key'] );
	if ( $parts ) {
		$a['parent_user_id'] = $a['parent_user_id'] ?: $parts['parent'];
		$a['tutor_post_id']  = $a['tutor_post_id'] ?: $parts['tutor'];
		$a['child_id']       = $a['child_id'] ?: $parts['child'];
	}
	if ( ! $a['thread_key'] || ! $a['parent_user_id'] || ! $a['tutor_post_id'] ) return 0;

	// Mask contact details on write; a change means an attempt → flag for agency.
	$raw    = (string) $a['body'];
	$masked = function_exists( 'mgk_mask_contact_details_in_message' ) ? mgk_mask_contact_details_in_message( $raw ) : $raw;
	$flagged = ( $masked !== $raw ) ? 1 : 0;

	$now  = gmdate( 'Y-m-d H:i:s' );
	$role = strtoupper( $a['sender_role'] );
	$wpdb->insert( mgk_msg_table(), [
		'thread_key'       => $a['thread_key'],
		'child_id'         => (int) $a['child_id'],
		'parent_user_id'   => (int) $a['parent_user_id'],
		'tutor_post_id'    => (int) $a['tutor_post_id'],
		'sender_role'      => $role,
		'sender_user_id'   => (int) $a['sender_user_id'],
		'body'             => $masked,
		'attachment'       => sanitize_text_field( $a['attachment'] ),
		'lesson_ref_id'    => (int) $a['lesson_ref_id'],
		'flagged'          => $flagged,
		'created_at_utc'   => $now,
		'read_by_parent_at'=> $role === 'PARENT' ? $now : null,
		'read_by_tutor_at' => $role === 'TUTOR'  ? $now : null,
	] );
	$id = (int) $wpdb->insert_id;

	// Notify the recipient side (tutor→parent notifies the parent wp_user).
	$to = ( $role === 'PARENT' ) ? 0 : (int) $a['parent_user_id'];
	do_action( 'mgk_message_sent', $id, $a['thread_key'], $to );
	return $id;
}

function mgk_msg_rows( $thread_key, $limit = 100 ) {
	global $wpdb; $t = mgk_msg_table();
	return (array) $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$t} WHERE thread_key = %s ORDER BY id ASC LIMIT %d", $thread_key, $limit
	), ARRAY_A );
}
function mgk_msg_last( $thread_key ) {
	global $wpdb; $t = mgk_msg_table();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE thread_key = %s ORDER BY id DESC LIMIT 1", $thread_key ), ARRAY_A );
}
function mgk_msg_unread( $thread_key ) {
	global $wpdb; $t = mgk_msg_table();
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$t} WHERE thread_key = %s AND sender_role IN ('TUTOR','AGENCY') AND read_by_parent_at IS NULL", $thread_key
	) );
}
function mgk_msg_mark_read( $thread_key, $parent_id ) {
	global $wpdb; $t = mgk_msg_table();
	return $wpdb->query( $wpdb->prepare(
		"UPDATE {$t} SET read_by_parent_at = %s WHERE thread_key = %s AND sender_role IN ('TUTOR','AGENCY') AND read_by_parent_at IS NULL",
		gmdate( 'Y-m-d H:i:s' ), $thread_key
	) );
}
function mgk_parent_total_unread( $parent_id ) {
	$parent_id = (int) $parent_id;
	if ( ! $parent_id ) return 0;
	global $wpdb; $t = mgk_msg_table();
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$t} WHERE parent_user_id = %d AND sender_role IN ('TUTOR','AGENCY') AND read_by_parent_at IS NULL", $parent_id
	) );
}

/* ── REST: parent sends a message ────────────────────────────────────────── */

add_action( 'rest_api_init', function () {
	register_rest_route( 'mgk/v1', '/messages/send', [
		'methods'             => 'POST',
		'callback'            => 'mgk_rest_message_send',
		'permission_callback' => function () { return is_user_logged_in() && function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user(); },
	] );
} );

function mgk_rest_message_send( WP_REST_Request $req ) {
	$body   = $req->get_json_params() ?: [];
	$thread = sanitize_text_field( $body['thread'] ?? '' );
	$text   = trim( (string) ( $body['body'] ?? '' ) );
	$parts  = mgk_thread_parse( $thread );
	if ( ! $parts || $text === '' ) {
		return new WP_REST_Response( [ 'message' => 'Type a message first.' ], 422 );
	}
	if ( (int) $parts['parent'] !== get_current_user_id() ) {
		return new WP_REST_Response( [ 'message' => 'Not your conversation.' ], 403 );
	}
	$id = mgk_msg_insert( [ 'thread_key' => $thread, 'sender_role' => 'PARENT', 'sender_user_id' => get_current_user_id(), 'body' => $text ] );
	if ( ! $id ) return new WP_REST_Response( [ 'message' => 'Could not send.' ], 500 );

	global $wpdb; $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . mgk_msg_table() . " WHERE id = %d", $id ), ARRAY_A );
	return rest_ensure_response( [ 'ok' => true, 'id' => $id, 'flagged' => (int) $row['flagged'], 'body' => $row['body'] ] );
}
