<?php
/**
 * Learning model — enrolment + lesson_log (DATA CORE).
 * ====================================================
 * The data the S13 dashboard's learning widgets actually compute from:
 *   - mg_enrolment : a package/trial a child is enrolled in (lessons_total,
 *                    valid_until, status) — the "remaining lessons" denominator.
 *   - mg_lesson    : one lesson_log per session (attendance, child_engagement,
 *                    topic, homework, comment) — the progress-chart source.
 *
 * Per the SRS lesson_log spec + business rules:
 *   BR-08  tutor must submit a log within 24h (48h → agency alert)  [SLA flag]
 *   BR-12  renewal nudge when lessons_remaining == 1                [renewal]
 *   BR-20  parent may review only after ≥1 ATTENDED lesson          [review gate]
 *
 * Progress chart: X = lesson_number, Y = child_engagement mapped to a score
 * (EXCELLENT=4 … NEEDS_IMPROVEMENT=1). Filtered by child via enrolment→child.
 *
 * NOTE on "real-time": true WebSocket (`lesson.logged`) needs a WS server
 * (Pusher/Soketi) — out of scope for this PHP theme. Logs are SERVER-TRUTH on
 * page load; `do_action('mgk_lesson_logged')` is the hook a WS/notify layer will
 * bind to later. No fake real-time.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Enums ───────────────────────────────────────────────────────────────── */

function mgk_engagement_levels() {
	return [ 'EXCELLENT' => 4, 'GOOD' => 3, 'OKAY' => 2, 'NEEDS_IMPROVEMENT' => 1 ];
}
function mgk_engagement_score( $e ) {
	$m = mgk_engagement_levels();
	return $m[ strtoupper( (string) $e ) ] ?? 0;
}
function mgk_engagement_label( $e ) {
	$labels = [ 'EXCELLENT' => 'Excellent', 'GOOD' => 'Good', 'OKAY' => 'Okay', 'NEEDS_IMPROVEMENT' => 'Needs work' ];
	return $labels[ strtoupper( (string) $e ) ] ?? '—';
}
/** Map an average score (1–4) back to the nearest label. */
function mgk_engagement_label_from_score( $score ) {
	if ( $score <= 0 ) return '—';
	$r = (int) round( $score );
	$by = [ 4 => 'EXCELLENT', 3 => 'GOOD', 2 => 'OKAY', 1 => 'NEEDS_IMPROVEMENT' ];
	return mgk_engagement_label( $by[ max( 1, min( 4, $r ) ) ] );
}

/* ── mg_enrolment CPT ────────────────────────────────────────────────────── */

add_action( 'init', function () {
	if ( post_type_exists( 'mg_enrolment' ) ) return;
	register_post_type( 'mg_enrolment', [
		'labels' => [
			'name'          => 'Enrolments',
			'singular_name' => 'Enrolment',
			'menu_name'     => 'Enrolments',
			'edit_item'     => 'Edit Enrolment',
			'not_found'     => 'No enrolments found.',
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'supports'        => [ 'title', 'custom-fields' ],
		'menu_icon'       => 'dashicons-welcome-learn-more',
		'menu_position'   => 33,
		'capability_type' => 'post',
	] );
}, 11 );

/* ── Enrolment helpers ───────────────────────────────────────────────────── */

/**
 * Create an enrolment for a child. $args: child_id, parent_user_id, tutor_id,
 * plan_type (TRIAL|PACKAGE_8|PACKAGE_16|SINGLE), lessons_total, subject,
 * valid_until (Y-m-d), source_booking_id. Returns post id or 0.
 */
function mgk_enrolment_create( $args ) {
	$a = wp_parse_args( $args, [
		'child_id' => 0, 'parent_user_id' => 0, 'tutor_id' => 0,
		'plan_type' => 'PACKAGE_8', 'lessons_total' => 0, 'subject' => '',
		'valid_until' => '', 'source_booking_id' => 0, 'status' => 'ACTIVE',
	] );
	if ( ! $a['child_id'] || ! post_type_exists( 'mg_enrolment' ) ) return 0;

	$child_name = get_post_meta( (int) $a['child_id'], 'mgk_child_full_name', true ) ?: get_the_title( (int) $a['child_id'] );
	$title = trim( $child_name . ' — ' . $a['plan_type'] . ( $a['subject'] ? ' ' . $a['subject'] : '' ) );

	$id = wp_insert_post( [ 'post_type' => 'mg_enrolment', 'post_title' => $title, 'post_status' => 'publish' ], true );
	if ( is_wp_error( $id ) || ! $id ) return 0;
	$id = (int) $id;

	update_post_meta( $id, 'mgk_enr_child_id',         (int) $a['child_id'] );
	update_post_meta( $id, 'mgk_enr_parent_user_id',   (int) $a['parent_user_id'] );
	update_post_meta( $id, 'mgk_enr_tutor_id',         (int) $a['tutor_id'] );
	update_post_meta( $id, 'mgk_enr_plan_type',        sanitize_text_field( $a['plan_type'] ) );
	update_post_meta( $id, 'mgk_enr_lessons_total',    (int) $a['lessons_total'] );
	update_post_meta( $id, 'mgk_enr_subject',          sanitize_text_field( $a['subject'] ) );
	update_post_meta( $id, 'mgk_enr_valid_until',      sanitize_text_field( $a['valid_until'] ) );
	update_post_meta( $id, 'mgk_enr_source_booking_id',(int) $a['source_booking_id'] );
	update_post_meta( $id, 'mgk_enr_status',           sanitize_text_field( $a['status'] ) );
	return $id;
}

/** The active enrolment for a child (newest), or 0. */
function mgk_enrolment_for_child( $child_id ) {
	$child_id = (int) $child_id;
	if ( ! $child_id || ! post_type_exists( 'mg_enrolment' ) ) return 0;
	$q = get_posts( [
		'post_type'   => 'mg_enrolment',
		'numberposts' => 1,
		'fields'      => 'ids',
		'orderby'     => 'date',
		'order'       => 'DESC',
		'meta_query'  => [
			[ 'key' => 'mgk_enr_child_id', 'value' => $child_id ],
			[ 'key' => 'mgk_enr_status',   'value' => 'ACTIVE' ],
		],
	] );
	return $q ? (int) $q[0] : 0;
}

/* ── Lesson log (mg_lesson) ──────────────────────────────────────────────── */

/**
 * Create a lesson log. $args: enrolment_id, child_id, tutor_id, booking_id,
 * lesson_number (auto if omitted), attendance (ATTENDED|LATE|NO_SHOW),
 * engagement (EXCELLENT|GOOD|OKAY|NEEDS_IMPROVEMENT), topic, homework, comment,
 * duration_min, lesson_date (Y-m-d), submitted_at (Y-m-d H:i:s UTC).
 * Fires do_action('mgk_lesson_logged', $id) for downstream notify/WS.
 */
function mgk_lesson_log_create( $args ) {
	$a = wp_parse_args( $args, [
		'enrolment_id' => 0, 'child_id' => 0, 'tutor_id' => 0, 'booking_id' => 0,
		'lesson_number' => 0, 'attendance' => 'ATTENDED', 'engagement' => 'GOOD',
		'topic' => '', 'homework' => '', 'comment' => '', 'duration_min' => 90,
		'lesson_date' => '', 'submitted_at' => '',
	] );
	if ( ! post_type_exists( 'mg_lesson' ) ) return 0;

	// Auto lesson_number = (existing for this enrolment) + 1.
	$num = (int) $a['lesson_number'];
	if ( ! $num && $a['enrolment_id'] ) {
		$num = count( mgk_lessons_for_enrolment( (int) $a['enrolment_id'] ) ) + 1;
	}
	$num = $num ?: 1;

	$child_name = $a['child_id'] ? ( get_post_meta( (int) $a['child_id'], 'mgk_child_full_name', true ) ?: get_the_title( (int) $a['child_id'] ) ) : 'Lesson';
	$title = sprintf( '%s — Lesson %d', $child_name, $num );

	$id = wp_insert_post( [ 'post_type' => 'mg_lesson', 'post_title' => $title, 'post_status' => 'publish' ], true );
	if ( is_wp_error( $id ) || ! $id ) return 0;
	$id = (int) $id;

	update_post_meta( $id, 'mgk_lesson_enrolment_id', (int) $a['enrolment_id'] );
	update_post_meta( $id, 'mgk_lesson_child_id',     (int) $a['child_id'] );
	update_post_meta( $id, 'mgk_lesson_tutor_id',     (int) $a['tutor_id'] );
	update_post_meta( $id, 'mgk_lesson_booking_id',   (int) $a['booking_id'] );
	update_post_meta( $id, 'mgk_lesson_number',       $num );
	update_post_meta( $id, 'mgk_lesson_attendance',   strtoupper( sanitize_text_field( $a['attendance'] ) ) );
	update_post_meta( $id, 'mgk_lesson_engagement',   strtoupper( sanitize_text_field( $a['engagement'] ) ) );
	update_post_meta( $id, 'mgk_lesson_topic',        sanitize_textarea_field( $a['topic'] ) );
	update_post_meta( $id, 'mgk_lesson_homework',     sanitize_textarea_field( $a['homework'] ) );
	update_post_meta( $id, 'mgk_lesson_comment',      sanitize_textarea_field( $a['comment'] ) );
	update_post_meta( $id, 'mgk_lesson_duration_min', (int) $a['duration_min'] );
	update_post_meta( $id, 'mgk_lesson_date',         sanitize_text_field( $a['lesson_date'] ) );
	update_post_meta( $id, 'mgk_lesson_submitted_at', sanitize_text_field( $a['submitted_at'] ?: gmdate( 'Y-m-d H:i:s' ) ) );

	do_action( 'mgk_lesson_logged', $id, (int) $a['child_id'], (int) $a['enrolment_id'] );
	return $id;
}

/** Lesson logs for an enrolment, ordered by lesson_number ASC. */
function mgk_lessons_for_enrolment( $enrolment_id ) {
	$enrolment_id = (int) $enrolment_id;
	if ( ! $enrolment_id || ! post_type_exists( 'mg_lesson' ) ) return [];
	$ids = get_posts( [
		'post_type'   => 'mg_lesson',
		'numberposts' => 200,
		'fields'      => 'ids',
		'meta_query'  => [ [ 'key' => 'mgk_lesson_enrolment_id', 'value' => $enrolment_id ] ],
	] );
	$rows = array_map( 'mgk_lesson_row', $ids );
	usort( $rows, function ( $a, $b ) { return $a['number'] <=> $b['number']; } );
	return $rows;
}

/** Lesson logs for a child (across enrolments), newest lesson_number first. */
function mgk_lessons_for_child( $child_id ) {
	$child_id = (int) $child_id;
	if ( ! $child_id || ! post_type_exists( 'mg_lesson' ) ) return [];
	$ids = get_posts( [
		'post_type'   => 'mg_lesson',
		'numberposts' => 200,
		'fields'      => 'ids',
		'meta_query'  => [ [ 'key' => 'mgk_lesson_child_id', 'value' => $child_id ] ],
	] );
	$rows = array_map( 'mgk_lesson_row', $ids );
	usort( $rows, function ( $a, $b ) { return $b['number'] <=> $a['number']; } );
	return $rows;
}

/** Normalize a mg_lesson post id → lesson_log row. */
function mgk_lesson_row( $id ) {
	$id = (int) $id;
	$tutor_id = (int) get_post_meta( $id, 'mgk_lesson_tutor_id', true );
	$eng = (string) get_post_meta( $id, 'mgk_lesson_engagement', true );
	return [
		'id'         => $id,
		'number'     => (int) get_post_meta( $id, 'mgk_lesson_number', true ),
		'attendance' => (string) get_post_meta( $id, 'mgk_lesson_attendance', true ),
		'engagement' => $eng,
		'eng_score'  => mgk_engagement_score( $eng ),
		'eng_label'  => mgk_engagement_label( $eng ),
		'topic'      => (string) get_post_meta( $id, 'mgk_lesson_topic', true ),
		'homework'   => (string) get_post_meta( $id, 'mgk_lesson_homework', true ),
		'comment'    => (string) get_post_meta( $id, 'mgk_lesson_comment', true ),
		'duration'   => (int) get_post_meta( $id, 'mgk_lesson_duration_min', true ),
		'date'       => (string) get_post_meta( $id, 'mgk_lesson_date', true ),
		'submitted'  => (string) get_post_meta( $id, 'mgk_lesson_submitted_at', true ),
		'tutor'      => $tutor_id ? get_the_title( $tutor_id ) : '',
	];
}

/* ── Aggregate learning stats for a child (the dashboard's source) ───────── */

/**
 * Everything the dashboard learning widgets need, computed from real rows.
 * Always returns a consistent shape; has_enrolment=false when none.
 */
function mgk_child_learning_stats( $child_id ) {
	$child_id = (int) $child_id;
	$enr = mgk_enrolment_for_child( $child_id );

	$lessons = $enr ? mgk_lessons_for_enrolment( $enr ) : [];
	$attended = array_values( array_filter( $lessons, function ( $l ) { return $l['attendance'] === 'ATTENDED'; } ) );

	$total   = $enr ? (int) get_post_meta( $enr, 'mgk_enr_lessons_total', true ) : 0;
	$done    = count( $attended );
	$logged  = count( $lessons );
	$remain  = $total ? max( 0, $total - $done ) : 0;
	$hours   = 0;
	$score_sum = 0;
	foreach ( $attended as $l ) { $hours += $l['duration']; $score_sum += $l['eng_score']; }
	$hours = round( $hours / 60, 1 );
	$eng_avg = $done ? round( $score_sum / $done, 1 ) : 0;
	$attendance_pct = $logged ? (int) round( $done / $logged * 100 ) : 0;

	// Series for the progress chart (ATTENDED only, by lesson_number ASC).
	$series = [];
	foreach ( $attended as $l ) {
		$series[] = [ 'n' => $l['number'], 'score' => $l['eng_score'], 'label' => $l['eng_label'], 'topic' => $l['topic'], 'date' => $l['date'] ];
	}
	usort( $series, function ( $a, $b ) { return $a['n'] <=> $b['n']; } );

	$valid_until = $enr ? (string) get_post_meta( $enr, 'mgk_enr_valid_until', true ) : '';
	$expired = $valid_until && strtotime( $valid_until ) < time();
	$status  = ! $enr ? 'NONE' : ( $expired ? 'EXPIRED' : ( $remain <= 1 ? 'RENEWAL_DUE' : 'ACTIVE' ) );

	return [
		'has_enrolment' => (bool) $enr,
		'enrolment_id'  => $enr,
		'plan_type'     => $enr ? (string) get_post_meta( $enr, 'mgk_enr_plan_type', true ) : '',
		'subject'       => $enr ? (string) get_post_meta( $enr, 'mgk_enr_subject', true ) : '',
		'lessons_total' => $total,
		'lessons_done'  => $done,
		'lessons_logged'=> $logged,
		'remaining'     => $remain,
		'hours'         => $hours,
		'attendance_pct'=> $attendance_pct,
		'engagement_avg'=> $eng_avg,
		'engagement_label' => mgk_engagement_label_from_score( $eng_avg ),
		'series'        => $series,
		'lessons'       => $lessons,           // ASC by number (history uses reverse)
		'latest'        => $lessons ? end( $lessons ) : null,
		'valid_until'   => $valid_until,
		'status'        => $status,            // NONE|ACTIVE|RENEWAL_DUE|EXPIRED
		'review_enabled'=> $done >= 1,         // BR-20
		'renewal_due'   => $enr && $remain === 1 && ! $expired, // BR-12
	];
}

/* ── [mgk_lesson_history] — full lesson-log history (S13 "view all") ─────── */
add_shortcode( 'mgk_lesson_history', function () {
	if ( ! ( function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user() ) ) {
		return '<div class="mgk-auth"><div class="mgk-auth__card"><h1>Lesson history</h1><p>Please <a href="' . esc_url( mgk_url( '/login/' ) ) . '">sign in</a> to see your child’s lesson logs.</p></div></div>';
	}
	$uid = get_current_user_id();
	$kids = function_exists( 'mgk_parent_children' ) ? mgk_parent_children( $uid ) : [];
	// Optional ?child= (must belong to this parent).
	$active = 0;
	if ( isset( $_GET['child'] ) ) {
		$req = (int) $_GET['child'];
		foreach ( $kids as $k ) { if ( $k->ID === $req ) { $active = $req; break; } }
	}
	if ( ! $active && $kids ) { $active = (int) $kids[0]->ID; }

	$name    = $active ? ( get_post_meta( $active, 'mgk_child_full_name', true ) ?: get_the_title( $active ) ) : '';
	$lessons = $active ? mgk_lessons_for_child( $active ) : [];

	ob_start();
	echo '<div class="mgk-lh" style="max-width:760px;margin:5vh auto;padding:0 20px;font:15px/1.6 -apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#1a1a1a;">';
	echo '<h1 style="font-size:24px;margin:0 0 4px;">Lesson logs</h1>';

	// Child switcher (when >1).
	if ( count( $kids ) > 1 ) {
		echo '<p style="margin:0 0 18px;">';
		foreach ( $kids as $k ) {
			$on = $k->ID === $active;
			echo '<a href="' . esc_url( add_query_arg( 'child', $k->ID, mgk_url( '/parent/lesson-logs/' ) ) ) . '" style="display:inline-block;margin-right:8px;padding:4px 12px;border-radius:20px;text-decoration:none;' . ( $on ? 'background:#1a1a1a;color:#fff;' : 'background:#f0f1f3;color:#1a1a1a;' ) . '">' . esc_html( get_post_meta( $k->ID, 'mgk_child_full_name', true ) ?: get_the_title( $k ) ) . '</a>';
		}
		echo '</p>';
	} elseif ( $name ) {
		echo '<p style="color:#646970;margin:0 0 18px;">' . esc_html( $name ) . '</p>';
	}

	if ( ! $lessons ) {
		echo '<div style="background:#f6f7f9;border:1px solid #e3e5e8;border-radius:12px;padding:28px;text-align:center;color:#646970;">No lessons logged yet. Your tutor posts a log after each session — they’ll appear here.</div>';
	} else {
		$badge = [ 'EXCELLENT' => '#1a7f37', 'GOOD' => '#4a90d9', 'OKAY' => '#e0a23a', 'NEEDS_IMPROVEMENT' => '#d9534f' ];
		foreach ( $lessons as $l ) {
			$c = $badge[ strtoupper( $l['engagement'] ) ] ?? '#646970';
			$date = $l['date'] ? gmdate( 'j M Y', strtotime( $l['date'] ) ) : '';
			echo '<div style="background:#fff;border:1px solid #e3e5e8;border-radius:12px;padding:18px 20px;margin:0 0 12px;">';
			echo '<div style="display:flex;justify-content:space-between;align-items:baseline;gap:12px;">';
			echo '<strong style="font-size:16px;">Lesson ' . (int) $l['number'] . ( $l['topic'] ? ' · ' . esc_html( $l['topic'] ) : '' ) . '</strong>';
			echo '<span style="font-size:12px;color:#646970;">' . esc_html( $date ) . '</span>';
			echo '</div>';
			echo '<div style="margin:8px 0 0;font-size:13px;">';
			echo '<span style="display:inline-block;padding:2px 9px;border-radius:6px;color:#fff;background:' . esc_attr( $c ) . ';">' . esc_html( $l['eng_label'] ) . '</span> ';
			echo '<span style="color:#646970;">· ' . esc_html( ucfirst( strtolower( $l['attendance'] ) ) ) . ' · ' . (int) $l['duration'] . ' min' . ( $l['tutor'] ? ' · ' . esc_html( $l['tutor'] ) : '' ) . '</span>';
			echo '</div>';
			if ( $l['homework'] ) echo '<p style="margin:10px 0 0;"><strong>Homework:</strong> ' . esc_html( $l['homework'] ) . '</p>';
			if ( $l['comment'] )  echo '<p style="margin:6px 0 0;color:#3c434a;">' . esc_html( $l['comment'] ) . '</p>';
			echo '</div>';
		}
	}
	echo '<p style="margin:18px 0 0;"><a href="' . esc_url( mgk_url( '/parent/dashboard/' ) ) . '">← Back to dashboard</a></p>';
	echo '</div>';
	return ob_get_clean();
} );
