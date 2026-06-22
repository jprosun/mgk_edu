<?php
/**
 * MGK Tutor Application + Verification (DATA CORE) — S19 / S20-21.
 * ================================================================
 * A tutor *application* is an `mg_teacher` post created in `draft` status by the
 * public apply form. It carries an application status (`mgk_app_status`) that the
 * agency drives MANUALLY from the post edit screen — there is no OCR, no automated
 * background check (owner decision, Phase 2). The applicant tracks their progress
 * on /tutor/verification/?token=<token> without logging in (the same view-token
 * idea as the S08 lead link).
 *
 *   submitted ─▶ under_review ─▶ approved   (publish + verify + provision login)
 *        │            │     └──▶ rejected    (declined, with reason)
 *        └────────────┴──────▶ info_requested ⟲ (back to under_review on resubmit)
 *
 * On APPROVE the draft is published, `mgk_is_verified` is set (so it surfaces in the
 * S02 listing / proposals), the activation state goes ACTIVE, and a passwordless
 * login is provisioned + invited via the existing identity layer
 * (mgk_tutor_provision_account / mgk_tutor_send_login_link in mgk-tutor-identity.php).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Application status vocabulary ───────────────────────────────────────── */

if ( ! defined( 'MGK_APP_SUBMITTED' ) )      define( 'MGK_APP_SUBMITTED', 'submitted' );
if ( ! defined( 'MGK_APP_UNDER_REVIEW' ) )   define( 'MGK_APP_UNDER_REVIEW', 'under_review' );
if ( ! defined( 'MGK_APP_INFO_REQUESTED' ) ) define( 'MGK_APP_INFO_REQUESTED', 'info_requested' );
if ( ! defined( 'MGK_APP_APPROVED' ) )       define( 'MGK_APP_APPROVED', 'approved' );
if ( ! defined( 'MGK_APP_REJECTED' ) )       define( 'MGK_APP_REJECTED', 'rejected' );

/** status => human label. */
function mgk_app_statuses() {
	return [
		MGK_APP_SUBMITTED      => 'Submitted',
		MGK_APP_UNDER_REVIEW   => 'Under review',
		MGK_APP_INFO_REQUESTED => 'More info requested',
		MGK_APP_APPROVED       => 'Approved · active',
		MGK_APP_REJECTED       => 'Not approved',
	];
}

function mgk_app_status_label( $status ) {
	$map = mgk_app_statuses();
	return $map[ (string) $status ] ?? 'Submitted';
}

/** Allowed status transitions (manual, agency-driven). */
function mgk_app_transitions() {
	return [
		MGK_APP_SUBMITTED      => [ MGK_APP_UNDER_REVIEW, MGK_APP_INFO_REQUESTED, MGK_APP_APPROVED, MGK_APP_REJECTED ],
		MGK_APP_UNDER_REVIEW   => [ MGK_APP_INFO_REQUESTED, MGK_APP_APPROVED, MGK_APP_REJECTED ],
		MGK_APP_INFO_REQUESTED => [ MGK_APP_UNDER_REVIEW, MGK_APP_SUBMITTED, MGK_APP_APPROVED, MGK_APP_REJECTED ],
		MGK_APP_APPROVED       => [],
		MGK_APP_REJECTED       => [],
	];
}

function mgk_app_can_transition( $from, $to ) {
	if ( $from === $to ) return true; // re-saving the same status (e.g. note edit) is a no-op, not an error.
	return in_array( (string) $to, mgk_app_transitions()[ (string) $from ] ?? [], true );
}

/* ── Read helpers ────────────────────────────────────────────────────────── */

/** Is the given post a tutor application (has an application status)? */
function mgk_is_tutor_application( $teacher_id ) {
	$teacher_id = (int) $teacher_id;
	if ( ! $teacher_id || get_post_type( $teacher_id ) !== 'mg_teacher' ) return false;
	return (string) get_post_meta( $teacher_id, 'mgk_app_status', true ) !== '';
}

/** Current application status for a tutor post (defaults to submitted if it's an app). */
function mgk_application_status( $teacher_id ) {
	$s = (string) get_post_meta( (int) $teacher_id, 'mgk_app_status', true );
	return $s !== '' ? $s : '';
}

/** Resolve a tutor application by its public view token. Returns post or null. */
function mgk_get_application_by_token( $token ) {
	$token = sanitize_text_field( (string) $token );
	if ( $token === '' || ! post_type_exists( 'mg_teacher' ) ) return null;

	$posts = get_posts( [
		'post_type'        => 'mg_teacher',
		'post_status'      => [ 'draft', 'pending', 'publish' ],
		'posts_per_page'   => 1,
		'no_found_rows'    => true,
		'suppress_filters' => false,
		'meta_query'       => [
			[ 'key' => 'mgk_app_token', 'value' => $token, 'compare' => '=' ],
		],
	] );

	return $posts ? $posts[0] : null;
}

/** Get-or-create the view token for an application. */
function mgk_application_token( $teacher_id ) {
	$teacher_id = (int) $teacher_id;
	$token = (string) get_post_meta( $teacher_id, 'mgk_app_token', true );
	if ( $token === '' ) {
		$token = wp_generate_password( 24, false, false );
		update_post_meta( $teacher_id, 'mgk_app_token', $token );
	}
	return $token;
}

/** Public verification URL for an application (token-based, no login). */
function mgk_application_view_url( $teacher_id ) {
	$token = mgk_application_token( $teacher_id );
	return add_query_arg( 'token', rawurlencode( $token ), mgk_url( '/tutor/verification/' ) );
}

/**
 * Timeline steps for the verification screen, derived from the application status.
 * Pure presentation data — keeps the template dumb.
 */
function mgk_application_timeline( $status ) {
	$status = (string) $status;
	$rank = [
		MGK_APP_SUBMITTED      => 1,
		MGK_APP_UNDER_REVIEW   => 2,
		MGK_APP_INFO_REQUESTED => 2,
		MGK_APP_APPROVED       => 3,
		MGK_APP_REJECTED       => 3,
	];
	$pos = $rank[ $status ] ?? 1;
	$approved = $status === MGK_APP_APPROVED;
	$rejected = $status === MGK_APP_REJECTED;

	return [
		[
			'key'    => 'submitted',
			'title'  => 'APPLICATION RECEIVED',
			'meta'   => 'We have your application on file.',
			'done'   => true,
			'active' => $pos === 1 && ! $rejected,
		],
		[
			'key'    => 'review',
			'title'  => $status === MGK_APP_INFO_REQUESTED ? 'MORE INFO REQUESTED' : 'UNDER REVIEW',
			'meta'   => $status === MGK_APP_INFO_REQUESTED ? 'Please send the requested details below.' : 'Our team is reviewing your profile and documents.',
			'done'   => $pos > 2 && ! $rejected,
			'active' => $pos === 2,
		],
		[
			'key'    => $rejected ? 'rejected' : 'active',
			'title'  => $rejected ? 'NOT APPROVED' : 'APPROVED · PROFILE LIVE',
			'meta'   => $rejected ? 'See the reason below. You may re-apply.' : 'Your profile is live and your job inbox is open.',
			'done'   => $approved,
			'active' => $approved || $rejected,
		],
	];
}

/* ── Create an application from the public apply form ────────────────────── */

/**
 * Create a tutor application (draft mg_teacher). Idempotent-ish: if an *open*
 * (non-terminal) application already exists for the email, it is updated rather
 * than duplicated, so a resubmit after "more info requested" flows back in.
 *
 * @param array $data name,email,phone,subjects[],levels[],university,degree,year,experience,rate,payout
 * @return array{teacher_id:int,token:string,resubmitted:bool}|WP_Error
 */
function mgk_application_create( array $data ) {
	$name  = sanitize_text_field( (string) ( $data['name'] ?? '' ) );
	$email = sanitize_email( (string) ( $data['email'] ?? '' ) );

	if ( $name === '' )                 return new WP_Error( 'mgk_app_name',  'Please enter your full name.' );
	if ( ! $email || ! is_email( $email ) ) return new WP_Error( 'mgk_app_email', 'Please enter a valid email address.' );

	$resubmitted = false;
	$teacher_id  = 0;

	// Reuse an existing OPEN application for this email (resubmit), but never touch
	// a record that already has a login (a real, provisioned tutor).
	$existing = get_posts( [
		'post_type'      => 'mg_teacher',
		'post_status'    => [ 'draft', 'pending' ],
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => 'mgk_tutor_email', 'value' => $email, 'compare' => '=' ],
			[ 'key' => 'mgk_app_status', 'value' => [ MGK_APP_SUBMITTED, MGK_APP_UNDER_REVIEW, MGK_APP_INFO_REQUESTED ], 'compare' => 'IN' ],
		],
	] );
	if ( $existing && ! mgk_teacher_owner_user_id( (int) $existing[0]->ID ) ) {
		$teacher_id  = (int) $existing[0]->ID;
		$resubmitted = true;
		wp_update_post( [ 'ID' => $teacher_id, 'post_title' => $name ] );
	} else {
		$teacher_id = wp_insert_post( [
			'post_type'   => 'mg_teacher',
			'post_status' => 'draft',
			'post_title'  => $name,
		], true );
		if ( is_wp_error( $teacher_id ) ) return $teacher_id;
		$teacher_id = (int) $teacher_id;
	}

	// Core identity + contact (mgk_tutor_email is the single-source key the
	// identity/booking layers already read).
	update_post_meta( $teacher_id, 'mgk_tutor_email', $email );
	update_post_meta( $teacher_id, 'mgk_app_phone', sanitize_text_field( (string) ( $data['phone'] ?? '' ) ) );

	// Education + experience + payout (free text, applicant-supplied).
	update_post_meta( $teacher_id, 'mgk_app_university', sanitize_text_field( (string) ( $data['university'] ?? '' ) ) );
	update_post_meta( $teacher_id, 'mgk_app_degree', sanitize_text_field( (string) ( $data['degree'] ?? '' ) ) );
	update_post_meta( $teacher_id, 'mgk_app_year', sanitize_text_field( (string) ( $data['year'] ?? '' ) ) );
	update_post_meta( $teacher_id, 'mgk_app_experience', sanitize_textarea_field( (string) ( $data['experience'] ?? '' ) ) );
	update_post_meta( $teacher_id, 'mgk_app_payout', sanitize_text_field( (string) ( $data['payout'] ?? '' ) ) );

	$rate = (int) ( $data['rate'] ?? 0 );
	if ( $rate > 0 ) update_post_meta( $teacher_id, 'mgk_rate_num', $rate );

	// Subjects / levels → real taxonomy terms (only accept existing term ids).
	mgk_application_set_terms( $teacher_id, 'mgk_subject', $data['subjects'] ?? [] );
	mgk_application_set_terms( $teacher_id, 'mgk_level', $data['levels'] ?? [] );

	// Status → submitted (a resubmit from info_requested also lands here).
	$prev = mgk_application_status( $teacher_id );
	update_post_meta( $teacher_id, 'mgk_app_status', MGK_APP_SUBMITTED );
	update_post_meta( $teacher_id, 'mgk_is_verified', '0' );
	if ( ! get_post_meta( $teacher_id, 'mgk_app_submitted_at', true ) ) {
		update_post_meta( $teacher_id, 'mgk_app_submitted_at', gmdate( 'c' ) );
	}
	update_post_meta( $teacher_id, 'mgk_app_updated_at', gmdate( 'c' ) );

	$token = mgk_application_token( $teacher_id );

	do_action( 'mgk_tutor_application_created', $teacher_id, $email, $resubmitted, $prev );

	// Notify the applicant (status link) + the agency (new application to review).
	mgk_application_notify_applicant( $teacher_id, MGK_APP_SUBMITTED );
	mgk_application_notify_agency( $teacher_id, $resubmitted );

	return [ 'teacher_id' => $teacher_id, 'token' => $token, 'resubmitted' => $resubmitted ];
}

/** Assign taxonomy terms from a list of term ids (ignores anything not a real term). */
function mgk_application_set_terms( $teacher_id, $taxonomy, $ids ) {
	$ids = array_filter( array_map( 'intval', (array) $ids ) );
	$valid = [];
	foreach ( $ids as $tid ) {
		$term = get_term( $tid, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) $valid[] = (int) $term->term_id;
	}
	wp_set_object_terms( (int) $teacher_id, $valid, $taxonomy, false );
}

/* ── Status transition + side effects (the agency review actions) ────────── */

/**
 * Move an application to a new status and run side effects.
 * APPROVE: publish + verify + activate + provision login + invite.
 * Returns true | WP_Error.
 */
function mgk_application_set_status( $teacher_id, $new_status, $note = '' ) {
	$teacher_id = (int) $teacher_id;
	$new_status = (string) $new_status;
	if ( ! mgk_is_tutor_application( $teacher_id ) ) {
		return new WP_Error( 'mgk_app_missing', 'Not a tutor application.' );
	}
	if ( ! array_key_exists( $new_status, mgk_app_statuses() ) ) {
		return new WP_Error( 'mgk_app_badstatus', 'Unknown application status.' );
	}

	$current = mgk_application_status( $teacher_id );
	if ( ! mgk_app_can_transition( $current, $new_status ) ) {
		return new WP_Error( 'mgk_app_transition', sprintf( 'Cannot move application from %s to %s.', mgk_app_status_label( $current ), mgk_app_status_label( $new_status ) ) );
	}

	$note = sanitize_textarea_field( (string) $note );

	// APPROVE needs an email-backed identity before we commit anything.
	if ( $new_status === MGK_APP_APPROVED ) {
		if ( ! mgk_teacher_email( $teacher_id ) ) {
			return new WP_Error( 'mgk_app_noemail', 'Add a login/contact email before approving.' );
		}
	}

	update_post_meta( $teacher_id, 'mgk_app_status', $new_status );
	update_post_meta( $teacher_id, 'mgk_app_updated_at', gmdate( 'c' ) );
	if ( $note !== '' || in_array( $new_status, [ MGK_APP_INFO_REQUESTED, MGK_APP_REJECTED ], true ) ) {
		update_post_meta( $teacher_id, 'mgk_app_reviewer_note', $note );
	}

	if ( $new_status === MGK_APP_APPROVED ) {
		// Publish + verify + activate.
		wp_update_post( [ 'ID' => $teacher_id, 'post_status' => 'publish' ] );
		update_post_meta( $teacher_id, 'mgk_is_verified', '1' );
		if ( defined( 'MGK_TUTOR_ACTIVE' ) ) update_post_meta( $teacher_id, 'mgk_tutor_state', MGK_TUTOR_ACTIVE );
		update_post_meta( $teacher_id, 'mgk_app_approved_at', gmdate( 'c' ) );

		// Provision the passwordless login and email the invite (idempotent).
		$uid = mgk_tutor_provision_account( $teacher_id );
		if ( ! is_wp_error( $uid ) ) {
			mgk_tutor_send_login_link( (int) $uid, 'invite' );
		}
	}

	do_action( 'mgk_tutor_application_status_changed', $teacher_id, $current, $new_status, $note );

	// Tell the applicant (approved invite is sent above; others get a status email).
	if ( $new_status !== MGK_APP_APPROVED ) {
		mgk_application_notify_applicant( $teacher_id, $new_status );
	} else {
		mgk_application_notify_applicant( $teacher_id, $new_status );
	}

	return true;
}

/* ── Notifications (best-effort; respects WP_DEBUG dev log via identity layer) ── */

function mgk_application_notify_applicant( $teacher_id, $status ) {
	$email = mgk_teacher_email( $teacher_id );
	if ( ! $email ) return;
	$site = get_bloginfo( 'name' );
	$name = get_the_title( $teacher_id ) ?: 'there';
	$url  = mgk_application_view_url( $teacher_id );

	switch ( $status ) {
		case MGK_APP_APPROVED:
			$subject = sprintf( '%s · your tutor application is approved 🎉', $site );
			$body    = sprintf( "Hi %s,\n\nGreat news — your application is approved and your profile is going live. We've emailed a separate sign-in link so you can access your tutor dashboard.\n\nTrack your status: %s\n\n— %s", $name, $url, $site );
			break;
		case MGK_APP_REJECTED:
			$note    = (string) get_post_meta( $teacher_id, 'mgk_app_reviewer_note', true );
			$subject = sprintf( '%s · update on your tutor application', $site );
			$body    = sprintf( "Hi %s,\n\nThank you for applying. After review we're unable to approve your application at this time.%s\n\nYou're welcome to re-apply later.\n\n— %s", $name, $note ? "\n\nReason: " . $note : '', $site );
			break;
		case MGK_APP_INFO_REQUESTED:
			$note    = (string) get_post_meta( $teacher_id, 'mgk_app_reviewer_note', true );
			$subject = sprintf( '%s · we need a bit more info', $site );
			$body    = sprintf( "Hi %s,\n\nWe're reviewing your application and need a little more from you:%s\n\nUpdate and resubmit here: %s\n\n— %s", $name, $note ? "\n\n" . $note : '', $url, $site );
			break;
		case MGK_APP_UNDER_REVIEW:
			$subject = sprintf( '%s · your application is under review', $site );
			$body    = sprintf( "Hi %s,\n\nYour application is now under review. We'll be in touch shortly.\n\nTrack your status: %s\n\n— %s", $name, $url, $site );
			break;
		default: // submitted
			$subject = sprintf( '%s · we received your tutor application', $site );
			$body    = sprintf( "Hi %s,\n\nThanks for applying to teach with %s. Your application has been received and is in our review queue.\n\nTrack your status anytime (no password needed): %s\n\n— %s", $name, $site, $url, $site );
	}

	wp_mail( $email, $subject, $body );

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$log = get_option( 'mgk_login_link_log', [] );
		if ( ! is_array( $log ) ) $log = [];
		array_unshift( $log, [ 'teacher_id' => (int) $teacher_id, 'email' => $email, 'url' => $url, 'purpose' => 'application_' . $status, 'at' => gmdate( 'c' ) ] );
		update_option( 'mgk_login_link_log', array_slice( $log, 0, 20 ) );
	}
}

function mgk_application_notify_agency( $teacher_id, $resubmitted ) {
	$to = sanitize_email( (string) ( get_option( 'mgk_agency_notify_email' ) ?: get_option( 'admin_email' ) ) );
	if ( ! $to ) return;
	$site = get_bloginfo( 'name' );
	$name = get_the_title( $teacher_id ) ?: 'A tutor';
	$edit = get_edit_post_link( $teacher_id, 'raw' );
	$subject = sprintf( '[%s] %s tutor application: %s', $site, $resubmitted ? 'Updated' : 'New', $name );
	$body    = sprintf( "%s %s an application.\n\nReview: %s\n", $name, $resubmitted ? 'updated' : 'submitted', $edit );
	wp_mail( $to, $subject, $body );
}

/* ── Admin: "Application review" meta-box on mg_teacher ──────────────────── */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'mgk_tutor_application_box', 'Tutor application review', 'mgk_tutor_application_metabox', 'mg_teacher', 'side', 'high' );
} );

function mgk_tutor_application_metabox( $post ) {
	$teacher_id = (int) $post->ID;
	if ( ! mgk_is_tutor_application( $teacher_id ) ) {
		echo '<p class="description">Not an application — this tutor was created directly in admin.</p>';
		return;
	}

	$status = mgk_application_status( $teacher_id );
	$note   = (string) get_post_meta( $teacher_id, 'mgk_app_reviewer_note', true );
	$email  = mgk_teacher_email( $teacher_id );
	$phone  = (string) get_post_meta( $teacher_id, 'mgk_app_phone', true );
	$uni    = (string) get_post_meta( $teacher_id, 'mgk_app_university', true );
	$deg    = (string) get_post_meta( $teacher_id, 'mgk_app_degree', true );
	$year   = (string) get_post_meta( $teacher_id, 'mgk_app_year', true );
	$exp    = (string) get_post_meta( $teacher_id, 'mgk_app_experience', true );
	$rate   = (int) get_post_meta( $teacher_id, 'mgk_rate_num', true );
	$pay    = (string) get_post_meta( $teacher_id, 'mgk_app_payout', true );
	$subs   = wp_get_post_terms( $teacher_id, 'mgk_subject', [ 'fields' => 'names' ] );
	$lvls   = wp_get_post_terms( $teacher_id, 'mgk_level', [ 'fields' => 'names' ] );
	$terminal = in_array( $status, [ MGK_APP_APPROVED, MGK_APP_REJECTED ], true );

	wp_nonce_field( 'mgk_app_review_' . $teacher_id, 'mgk_app_review_nonce' );

	echo '<p><strong>Status:</strong> <span style="padding:2px 6px;border-radius:3px;background:' . ( $status === MGK_APP_APPROVED ? '#d6f3df;color:#1a7f37' : ( $status === MGK_APP_REJECTED ? '#fde2e1;color:#b32d2e' : '#fff3cd;color:#8a6d3b' ) ) . '">' . esc_html( mgk_app_status_label( $status ) ) . '</span></p>';

	echo '<div style="font-size:12px;line-height:1.6;border:1px solid #e0e0e0;border-radius:4px;padding:8px;margin-bottom:8px">';
	echo '<strong>Email:</strong> ' . esc_html( $email ?: '—' ) . '<br>';
	echo '<strong>Phone:</strong> ' . esc_html( $phone ?: '—' ) . '<br>';
	echo '<strong>Subjects:</strong> ' . esc_html( $subs ? implode( ', ', $subs ) : '—' ) . '<br>';
	echo '<strong>Levels:</strong> ' . esc_html( $lvls ? implode( ', ', $lvls ) : '—' ) . '<br>';
	echo '<strong>Education:</strong> ' . esc_html( trim( $deg . ' · ' . $uni . ' · ' . $year, ' ·' ) ?: '—' ) . '<br>';
	echo '<strong>Rate:</strong> ' . ( $rate ? '$' . esc_html( (string) $rate ) . '/hr' : '—' ) . '<br>';
	echo '<strong>Payout:</strong> ' . esc_html( $pay ?: '—' ) . '<br>';
	if ( $exp ) echo '<strong>Experience:</strong><br><span>' . nl2br( esc_html( $exp ) ) . '</span>';
	echo '</div>';

	if ( $terminal ) {
		echo '<p class="description">This application is closed (' . esc_html( mgk_app_status_label( $status ) ) . ').';
		if ( $note ) echo '<br><em>Note:</em> ' . esc_html( $note );
		echo '</p>';
		return;
	}

	echo '<p><label for="mgk_app_decision"><strong>Set decision</strong></label>';
	echo '<select id="mgk_app_decision" name="mgk_app_decision" class="widefat">';
	echo '<option value="">— keep current —</option>';
	$choices = [
		MGK_APP_UNDER_REVIEW   => 'Mark under review',
		MGK_APP_INFO_REQUESTED => 'Request more info (note ↓)',
		MGK_APP_APPROVED       => 'Approve → publish + login',
		MGK_APP_REJECTED       => 'Reject (reason ↓)',
	];
	foreach ( $choices as $val => $label ) {
		if ( ! mgk_app_can_transition( $status, $val ) ) continue;
		echo '<option value="' . esc_attr( $val ) . '">' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';

	echo '<p><label for="mgk_app_reviewer_note"><strong>Note to applicant</strong> <span class="description">(shown for info-request / reject)</span></label>';
	echo '<textarea id="mgk_app_reviewer_note" name="mgk_app_reviewer_note" rows="3" class="widefat" placeholder="e.g. Please re-send your degree certificate (page 2 was unclear).">' . esc_textarea( $note ) . '</textarea></p>';
	echo '<p class="description">Pick a decision and click <strong>Update</strong> to apply. Approving emails the tutor a sign-in link.</p>';
}

/**
 * Apply the review decision on save. Lives on save_post so the admin's normal
 * "Update" button drives the transition (the meta-box select + note are part of
 * the post form). Guarded against the recursive save that publishing triggers.
 */
add_action( 'save_post_mg_teacher', function ( $post_id ) {
	static $busy = false;
	if ( $busy ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( ! isset( $_POST['mgk_app_review_nonce'] ) ) return;
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mgk_app_review_nonce'] ) ), 'mgk_app_review_' . $post_id ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( ! mgk_is_tutor_application( $post_id ) ) return;

	$decision = isset( $_POST['mgk_app_decision'] ) ? sanitize_key( wp_unslash( $_POST['mgk_app_decision'] ) ) : '';
	$note     = isset( $_POST['mgk_app_reviewer_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mgk_app_reviewer_note'] ) ) : '';

	// Persist a note edit even without a decision (e.g. drafting the info request).
	if ( $decision === '' ) {
		if ( $note !== (string) get_post_meta( $post_id, 'mgk_app_reviewer_note', true ) ) {
			update_post_meta( $post_id, 'mgk_app_reviewer_note', $note );
		}
		return;
	}

	$busy = true;
	$res  = mgk_application_set_status( $post_id, $decision, $note );
	$busy = false;

	set_transient( 'mgk_app_review_msg_' . get_current_user_id(), is_wp_error( $res ) ? [ 'ok' => 0, 'm' => $res->get_error_message() ] : [ 'ok' => 1, 'm' => mgk_app_status_label( $decision ) ], 30 );
}, 20, 1 );

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'mg_teacher' ) return;
	$key = 'mgk_app_review_msg_' . get_current_user_id();
	$msg = get_transient( $key );
	if ( ! $msg ) return;
	delete_transient( $key );
	if ( ! empty( $msg['ok'] ) ) {
		printf( '<div class="notice notice-success is-dismissible"><p>Application updated: <strong>%s</strong>. The applicant has been notified.</p></div>', esc_html( $msg['m'] ) );
	} else {
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $msg['m'] ) );
	}
} );
