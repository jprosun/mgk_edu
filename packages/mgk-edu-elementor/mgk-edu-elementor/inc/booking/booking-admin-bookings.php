<?php
/**
 * MGK Booking Engine — Phase 0.5 · Admin bookings page + override actions.
 * ========================================================================
 * A real admin surface over the engine's source of truth (mgk_bookings):
 * list bookings, drill into one to see payment + the full audit event log, and
 * run override actions. Every action writes a mgk_booking_events row (plan §14).
 *
 * Lives under the existing "Bookings" (mg_booking) menu as "Booking Engine" so
 * admins find it next to the mirrored CPT view.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Central status setter — the ONLY place that mutates booking.status outside the
 * hold/payment engines. Fires mgk_booking_status_changed (mirror listens).
 */
function mgk_engine_set_status( $booking_id, $new_status, $args = [] ) {
	global $wpdb;
	$booking_id = (int) $booking_id;
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) return false;
	$old = $row['status'];
	$now = mgk_booking_now_utc();

	$update = [ 'status' => $new_status, 'updated_at_utc' => $now ];
	if ( $new_status === 'CONFIRMED' && empty( $row['confirmed_at_utc'] ) ) $update['confirmed_at_utc'] = $now;
	if ( in_array( $new_status, [ 'CANCELLED', 'NO_SHOW' ], true ) ) $update['cancelled_at_utc'] = $now;
	if ( ! empty( $args['payment_status'] ) ) $update['payment_status'] = $args['payment_status'];

	$wpdb->update( mgk_booking_table( 'bookings' ), $update, [ 'id' => $booking_id ] );

	mgk_log_booking_event( $booking_id, $args['event_type'] ?? 'STATUS_CHANGED', [
		'old_status' => $old,
		'new_status' => $new_status,
		'actor_type' => $args['actor_type'] ?? 'ADMIN',
		'actor_id'   => get_current_user_id(),
		'metadata'   => $args['metadata'] ?? null,
	] );

	do_action( 'mgk_booking_status_changed', $booking_id, $old, $new_status );
	return true;
}

/* ── Admin menu ────────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=mg_booking',
		'Booking Engine',
		'Booking Engine',
		'edit_posts',
		'mgk-booking-engine',
		'mgk_render_booking_engine_page'
	);
} );

function mgk_render_booking_engine_page() {
	if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Permission denied.' );
	$booking_id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
	echo '<div class="wrap">';
	if ( $booking_id ) {
		mgk_render_booking_detail( $booking_id );
	} else {
		mgk_render_booking_list();
	}
	echo '</div>';
}

function mgk_render_booking_list() {
	global $wpdb;
	$bookings = mgk_booking_table( 'bookings' );
	$filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

	$where = '';
	if ( $filter ) {
		$where = $wpdb->prepare( ' WHERE status = %s', $filter );
	}
	$rows = $wpdb->get_results( "SELECT * FROM {$bookings}{$where} ORDER BY id DESC LIMIT 200", ARRAY_A );

	echo '<h1>Booking Engine</h1>';
	// Status counts.
	$counts = $wpdb->get_results( "SELECT status, COUNT(*) c FROM {$bookings} GROUP BY status", ARRAY_A );
	echo '<p>';
	$base = admin_url( 'edit.php?post_type=mg_booking&page=mgk-booking-engine' );
	echo '<a href="' . esc_url( $base ) . '">All</a> ';
	foreach ( $counts as $c ) {
		echo '| <a href="' . esc_url( add_query_arg( 'status', $c['status'], $base ) ) . '">' . esc_html( $c['status'] ) . ' (' . (int) $c['c'] . ')</a> ';
	}
	echo '</p>';

	if ( ! $rows ) { echo '<p>No bookings yet.</p>'; return; }

	echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
	echo '<th>Code</th><th>Tutor</th><th>Student</th><th>When (SGT)</th><th>Status</th><th>Payment</th><th>Amount</th><th></th>';
	echo '</tr></thead><tbody>';
	$tz = mgk_booking_tz();
	foreach ( $rows as $r ) {
		$when = '';
		try { $d = new DateTime( $r['start_at_utc'], new DateTimeZone( 'UTC' ) ); $d->setTimezone( $tz ); $when = $d->format( 'D j M, H:i' ); } catch ( Exception $e ) {}
		$detail = add_query_arg( [ 'page' => 'mgk-booking-engine', 'booking' => (int) $r['id'] ], admin_url( 'edit.php?post_type=mg_booking' ) );
		echo '<tr>';
		echo '<td><a href="' . esc_url( $detail ) . '"><strong>' . esc_html( $r['booking_code'] ) . '</strong></a></td>';
		echo '<td>' . esc_html( get_the_title( (int) $r['tutor_post_id'] ) ?: ( '#' . $r['tutor_post_id'] ) ) . '</td>';
		echo '<td>' . esc_html( $r['student_name'] ?: '—' ) . '</td>';
		echo '<td>' . esc_html( $when ) . '</td>';
		echo '<td>' . mgk_status_badge( $r['status'] ) . '</td>';
		echo '<td>' . esc_html( $r['payment_status'] ) . '</td>';
		echo '<td>' . esc_html( $r['currency'] . ' ' . number_format( (float) $r['price_amount'], 2 ) ) . '</td>';
		echo '<td><a href="' . esc_url( $detail ) . '">View</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

function mgk_status_badge( $status ) {
	$colors = [
		'CONFIRMED' => '#1a7f37', 'HELD' => '#996800', 'PENDING_PAYMENT' => '#996800',
		'EXPIRED' => '#646970', 'CANCELLED' => '#646970', 'FAILED_PAYMENT' => '#b32d2e',
		'MANUAL_REVIEW' => '#b32d2e', 'COMPLETED' => '#1a7f37',
	];
	$c = $colors[ $status ] ?? '#646970';
	return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;color:#fff;font-size:11px;background:' . esc_attr( $c ) . '">' . esc_html( $status ) . '</span>';
}

function mgk_render_booking_detail( $booking_id ) {
	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) { echo '<h1>Booking not found</h1>'; return; }

	$back = admin_url( 'edit.php?post_type=mg_booking&page=mgk-booking-engine' );
	echo '<h1>' . esc_html( $row['booking_code'] ) . ' ' . mgk_status_badge( $row['status'] ) . '</h1>';
	echo '<p><a href="' . esc_url( $back ) . '">&larr; All bookings</a></p>';

	// Result notice — tells the operator for SURE whether the account was created.
	$done = isset( $_GET['done'] ) ? sanitize_key( $_GET['done'] ) : '';
	if ( $done ) {
		$linked_uid = (int) $row['parent_user_id'];
		$link_hint  = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? ' — magic-link login is in <code>mgk_login_link_log</code>.' : ' and a magic-link login was emailed.';
		if ( in_array( $done, [ 'force_confirm', 'create_account' ], true ) ) {
			if ( $linked_uid ) {
				$pu = get_user_by( 'id', $linked_uid );
				echo '<div class="notice notice-success is-dismissible"><p>✅ Parent account <strong>' . esc_html( $pu ? $pu->user_email : ( '#' . $linked_uid ) ) . '</strong> (user #' . $linked_uid . ') is linked' . $link_hint . '</p></div>';
			} else {
				echo '<div class="notice notice-warning"><p>⚠️ Booking is confirmed, but <strong>no parent email was on file</strong>, so NO account was created. Enter a parent email below, then click <em>Create parent account</em>.</p></div>';
			}
		} elseif ( $done === 'email_saved' ) {
			echo '<div class="notice notice-success is-dismissible"><p>Parent email saved. Now click <em>Create parent account</em> (or Force confirm).</p></div>';
		} elseif ( $done === 'email_bad' ) {
			echo '<div class="notice notice-error"><p>That email looks invalid — nothing was saved.</p></div>';
		} elseif ( $done === 'login_sent' ) {
			echo '<div class="notice notice-success is-dismissible"><p>Login link generated' . $link_hint . '</p></div>';
		} else {
			echo '<div class="notice notice-info is-dismissible"><p>Done: ' . esc_html( $done ) . '</p></div>';
		}
	}

	// Summary.
	$tz = mgk_booking_tz();
	$fmt = function ( $utc ) use ( $tz ) {
		if ( ! $utc ) return '—';
		try { $d = new DateTime( $utc, new DateTimeZone( 'UTC' ) ); $d->setTimezone( $tz ); return $d->format( 'D j M Y, H:i' ); } catch ( Exception $e ) { return '—'; }
	};
	echo '<table class="widefat" style="max-width:680px"><tbody>';
	$fields = [
		'Tutor'    => get_the_title( (int) $row['tutor_post_id'] ) ?: ( '#' . $row['tutor_post_id'] ),
		'Student'  => $row['student_name'] ?: '—',
		'Subject'  => $row['subject'] ?: '—',
		'Type'     => $row['lesson_type'],
		'Start'    => $fmt( $row['start_at_utc'] ),
		'End'      => $fmt( $row['end_at_utc'] ),
		'Status'   => $row['status'],
		'Payment'  => $row['payment_status'],
		'Amount'   => $row['currency'] . ' ' . number_format( (float) $row['price_amount'], 2 ),
		'Hold expires' => $fmt( $row['hold_expires_at_utc'] ),
	];
	foreach ( $fields as $k => $v ) {
		echo '<tr><th style="width:140px;text-align:left">' . esc_html( $k ) . '</th><td>' . esc_html( $v ) . '</td></tr>';
	}
	echo '</tbody></table>';

	// Override actions.
	echo '<h2>Actions</h2><p style="display:flex;gap:8px;flex-wrap:wrap">';
	$action = function ( $act, $label, $confirm = '', $primary = false ) use ( $booking_id ) {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=mgk_booking_action&do=' . $act . '&booking=' . $booking_id ),
			'mgk_booking_action_' . $booking_id
		);
		$cls = $primary ? 'button button-primary' : 'button';
		$onclick = $confirm ? ' onclick="return confirm(\'' . esc_js( $confirm ) . '\');"' : '';
		return '<a href="' . esc_url( $url ) . '" class="' . $cls . '"' . $onclick . '>' . esc_html( $label ) . '</a>';
	};
	if ( in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT', 'MANUAL_REVIEW' ], true ) ) {
		echo $action( 'force_confirm', 'Force confirm', 'Force confirm this booking? Use only when payment is verified.', true );
	}
	if ( in_array( $row['status'], [ 'HELD', 'PENDING_PAYMENT' ], true ) ) {
		echo $action( 'release', 'Release hold', 'Release this hold and free the slot?' );
	}
	if ( ! in_array( $row['status'], [ 'CANCELLED', 'EXPIRED', 'COMPLETED' ], true ) ) {
		echo $action( 'cancel', 'Cancel booking', 'Cancel this booking and free the slot?' );
	}
	if ( $row['status'] === 'MANUAL_REVIEW' ) {
		echo $action( 'resolve_review', 'Mark review resolved (cancel + refund tracked)', 'Resolve as cancelled? Track refund manually.' );
	}
	echo '</p>';

	// Parent account — manual email entry + account control (agency operator).
	echo '<h2>Parent account</h2>';
	$linked_uid = (int) $row['parent_user_id'];
	$lead_email = ( ! empty( $row['lead_id'] ) && function_exists( 'mgk_lead_contact' ) )
		? mgk_lead_contact( (int) $row['lead_id'] )['email'] : '';
	if ( $linked_uid ) {
		$pu = get_user_by( 'id', $linked_uid );
		echo '<p>✅ Linked account: <strong>' . esc_html( $pu ? $pu->user_email : ( '#' . $linked_uid ) ) . '</strong> (user #' . $linked_uid . ')</p>';
	} elseif ( $lead_email ) {
		echo '<p>Email on file: <strong>' . esc_html( $lead_email ) . '</strong> — no account yet.</p>';
	} else {
		echo '<p><em>No parent email on this booking yet.</em></p>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php?action=mgk_booking_action&do=set_email&booking=' . $booking_id ) ) . '" style="margin:8px 0;display:flex;gap:8px;align-items:center">';
	wp_nonce_field( 'mgk_booking_action_' . $booking_id );
	echo '<input type="email" name="parent_email" value="' . esc_attr( $lead_email ) . '" placeholder="parent@email.com" class="regular-text" required> ';
	echo '<button class="button">Save parent email</button></form>';
	echo '<p style="display:flex;gap:8px;flex-wrap:wrap">';
	echo $action( 'create_account', $linked_uid ? 'Re-link / refresh account' : 'Create parent account', 'Create or link the parent account for this booking now?' );
	if ( $linked_uid ) echo $action( 'send_login', 'Send login link' );
	echo '</p>';

	// Event log.
	echo '<h2>Event log</h2>';
	$events = mgk_get_booking_events( $booking_id, 100 );
	if ( ! $events ) { echo '<p>No events.</p>'; return; }
	echo '<table class="wp-list-table widefat striped"><thead><tr><th>When (UTC)</th><th>Event</th><th>Actor</th><th>Old→New</th><th>Meta</th></tr></thead><tbody>';
	foreach ( $events as $e ) {
		echo '<tr>';
		echo '<td>' . esc_html( $e['created_at_utc'] ) . '</td>';
		echo '<td><code>' . esc_html( $e['event_type'] ) . '</code></td>';
		echo '<td>' . esc_html( $e['actor_type'] ) . ( $e['actor_id'] ? ' #' . (int) $e['actor_id'] : '' ) . '</td>';
		echo '<td>' . esc_html( ( $e['old_status'] ?: '—' ) . ' → ' . ( $e['new_status'] ?: '—' ) ) . '</td>';
		echo '<td><small>' . esc_html( (string) $e['metadata_json'] ) . '</small></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

/* ── Action handler ────────────────────────────────────────────────────── */

add_action( 'admin_post_mgk_booking_action', function () {
	if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Permission denied.' );
	$booking_id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
	$do = isset( $_GET['do'] ) ? sanitize_key( $_GET['do'] ) : '';
	check_admin_referer( 'mgk_booking_action_' . $booking_id );

	$row = mgk_get_booking_row( $booking_id );
	if ( ! $row ) wp_die( 'Booking not found.' );

	$done_flag = $do; // what the result notice keys on (a case may override it)

	switch ( $do ) {
		case 'force_confirm':
			mgk_engine_promote_locks_to_booking( $booking_id );
			mgk_engine_set_status( $booking_id, 'CONFIRMED', [
				'event_type' => 'ADMIN_FORCE_CONFIRM', 'payment_status' => 'PAID',
			] );
			do_action( 'mgk_booking_confirmed', $booking_id );
			break;

		case 'release':
			mgk_engine_release_locks( $booking_id );
			mgk_engine_set_status( $booking_id, 'EXPIRED', [ 'event_type' => 'ADMIN_RELEASE', 'payment_status' => 'EXPIRED' ] );
			break;

		case 'cancel':
			mgk_engine_release_locks( $booking_id );
			mgk_engine_set_status( $booking_id, 'CANCELLED', [ 'event_type' => 'ADMIN_CANCEL' ] );
			break;

		case 'resolve_review':
			mgk_engine_release_locks( $booking_id );
			mgk_engine_set_status( $booking_id, 'CANCELLED', [
				'event_type' => 'MANUAL_REVIEW_RESOLVED',
				'metadata'   => [ 'note' => 'Resolved from manual review; refund tracked manually.' ],
			] );
			break;

		case 'set_email':
			$email = isset( $_POST['parent_email'] ) ? sanitize_email( wp_unslash( $_POST['parent_email'] ) ) : '';
			if ( ! $email || ! is_email( $email ) || ! function_exists( 'mgk_parent_attach_booking_email' ) ) {
				$done_flag = 'email_bad';
				break;
			}
			$res = mgk_parent_attach_booking_email( $booking_id, $email );
			$done_flag = is_wp_error( $res ) ? 'email_bad' : 'email_saved';
			break;

		case 'create_account':
			if ( function_exists( 'mgk_parent_claim_on_booking' ) ) {
				mgk_parent_claim_on_booking( $booking_id );
			}
			$done_flag = 'create_account';
			break;

		case 'send_login':
			$fresh = mgk_get_booking_row( $booking_id );
			if ( ! empty( $fresh['parent_user_id'] ) && function_exists( 'mgk_parent_send_login_link' ) ) {
				mgk_parent_send_login_link(
					(int) $fresh['parent_user_id'],
					function_exists( 'mgk_cta_url' ) ? mgk_cta_url( 'dashboard' ) : home_url( '/parent/dashboard/' ),
					'login'
				);
			}
			$done_flag = 'login_sent';
			break;

		default:
			wp_die( 'Unknown action.' );
	}

	wp_safe_redirect( add_query_arg(
		[ 'page' => 'mgk-booking-engine', 'booking' => $booking_id, 'done' => $done_flag ],
		admin_url( 'edit.php?post_type=mg_booking' )
	) );
	exit;
} );
