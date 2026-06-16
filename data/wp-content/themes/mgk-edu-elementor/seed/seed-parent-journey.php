<?php
/**
 * Seed ONE complete, fully-linked parent journey so S12 (confirmation) and the
 * parent dashboard show REAL data end to end — a real wp_user, real children,
 * a real CONFIRMED booking with a real tutor, slot and paid amount.
 *
 *   parent   : real wp_user (role mgk_parent), keyed by email
 *   children : 2 mg_child linked to that user via mgk_child_parent_user
 *   tutor    : an existing mg_teacher + contact meta (phone/email) so the S12
 *              NFR-10 unlock shows REAL values once paid
 *   booking  : a CONFIRMED row in wp_mgk_bookings (real tutor, future slot, $33)
 *   payment  : a SUCCEEDED payment row (so the method shows real)
 *   lead     : linked + stamped with parent_user_id (matches the live claim)
 *
 * Idempotent: keyed by email + a stable booking_code. Re-running updates in place.
 * Run:  wp eval-file seed/seed-parent-journey.php --allow-root --path=/var/www/html
 * Optional: pass an email to attach the rich booking to YOUR account instead:
 *           wp eval-file seed/seed-parent-journey.php you@example.com --allow-root --path=/var/www/html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'mgk_parent_find_or_create' ) ) { echo "ERR: identity module not loaded\n"; return; }
if ( ! function_exists( 'mgk_booking_table' ) )         { echo "ERR: booking engine not loaded\n"; return; }

global $argv;
$override_email = '';
foreach ( (array) $argv as $a ) {
    if ( is_string( $a ) && strpos( $a, '@' ) !== false && is_email( $a ) ) { $override_email = sanitize_email( $a ); break; }
}

$EMAIL = $override_email ?: 'chen.weiling@example.sg';
$NAME  = $override_email ? '' : 'Mrs Chen Wei Ling';   // keep their own name if attaching to a real account
$PHONE = '+6591234567';
$CODE  = 'MGK-SAMPLE-CHEN';        // stable human confirmation number for the sample
$TUTOR_SLUG = 'ms-sim-pei-hua';

/* ── 1. Parent wp_user (keyed by email) ─────────────────── */
$uid = mgk_parent_find_or_create( $EMAIL, $PHONE, $NAME );
if ( is_wp_error( $uid ) || ! $uid ) { echo "ERR: parent create failed\n"; return; }
if ( $NAME ) update_user_meta( $uid, 'mgk_parent_full_name', $NAME );
update_user_meta( $uid, 'mgk_parent_email_verified', 1 );

/* ── 2. Children (mg_child) linked to the user ──────────── */
$kids = [
    [ 'name' => 'Chen Rui En',  'level' => 'P5-P6',   'school' => 'Nanyang Primary School', 'goals' => 'PSLE Math foundation, fewer careless mistakes, build exam confidence.' ],
    [ 'name' => 'Chen Rui Jie', 'level' => 'Sec 1-2', 'school' => 'Raffles Institution',    'goals' => 'Strengthen Sec 1 Science fundamentals before streaming.' ],
];
$child_ids = [];
foreach ( $kids as $k ) {
    $existing = get_posts( [ 'post_type' => 'mg_child', 'title' => $k['name'], 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ] );
    $cid = $existing ? (int) $existing[0] : (int) wp_insert_post( [ 'post_type' => 'mg_child', 'post_title' => $k['name'], 'post_status' => 'publish' ] );
    if ( ! $cid ) continue;
    update_post_meta( $cid, 'mgk_child_full_name', $k['name'] );
    $lvl = get_term_by( 'name', $k['level'], 'mgk_level' );
    if ( $lvl ) update_post_meta( $cid, 'mgk_child_current_level', (int) $lvl->term_id );
    update_post_meta( $cid, 'mgk_child_school_name',    $k['school'] );
    update_post_meta( $cid, 'mgk_child_learning_goals', $k['goals'] );
    if ( function_exists( 'mgk_child_set_parent' ) ) mgk_child_set_parent( $cid, $uid );
    $child_ids[] = $cid;
}

/* ── 3. Tutor + contact meta (so the S12 unlock shows real values) ── */
$tutor = get_page_by_path( $TUTOR_SLUG, OBJECT, 'mg_teacher' );
if ( ! $tutor ) { echo "ERR: tutor {$TUTOR_SLUG} not found — run seed-teachers.php first\n"; return; }
update_post_meta( $tutor->ID, 'mgk_tutor_phone', '+65 8123 4567' );
update_post_meta( $tutor->ID, 'mgk_tutor_email', 'sim.peihua@tutors.margick.sg' );

/* ── 4. Lead (reuse the sample, stamp parent_user_id) ───── */
$lead = get_posts( [ 'post_type' => 'mg_lead', 'title' => 'Lead — Mrs Chen — P5 Math (sample)', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ] );
$lead_id = $lead ? (int) $lead[0] : 0;
if ( $lead_id ) {
    update_post_meta( $lead_id, 'mgk_lead_email',          $EMAIL );
    update_post_meta( $lead_id, 'mgk_lead_parent_user_id', $uid );
}

/* ── 5. CONFIRMED booking row (real tutor, future slot, paid) ── */
global $wpdb;
$bookings = mgk_booking_table( 'bookings' );
$tz       = new DateTimeZone( 'Asia/Singapore' );

$start_local = new DateTime( 'now', $tz );
$start_local->modify( '+5 days' );
$start_local->setTime( 16, 0, 0 );          // 4:00 PM SGT
$end_local = clone $start_local;
$end_local->modify( '+90 minutes' );        // 90-min trial

$to_utc = function ( DateTime $dt ) {
    $u = clone $dt; $u->setTimezone( new DateTimeZone( 'UTC' ) );
    return $u->format( 'Y-m-d H:i:s' );
};
$start_utc = $to_utc( $start_local );
$end_utc   = $to_utc( $end_local );
$now_utc   = gmdate( 'Y-m-d H:i:s' );

$data = [
    'booking_code'     => $CODE,
    'tutor_post_id'    => (int) $tutor->ID,
    'lead_id'          => $lead_id ?: null,
    'parent_user_id'   => (int) $uid,
    'student_name'     => 'Chen Rui En',
    'subject'          => 'P5 Math',
    'lesson_type'      => 'TRIAL',
    'slot_key'         => $tutor->ID . '|' . $start_utc,
    'start_at_utc'     => $start_utc,
    'end_at_utc'       => $end_utc,
    'timezone'         => 'Asia/Singapore',
    'status'           => 'CONFIRMED',
    'payment_status'   => 'PAID',
    'price_amount'     => 33.00,
    'currency'         => 'SGD',
    'confirmed_at_utc' => $now_utc,
    'updated_at_utc'   => $now_utc,
];

$existing = mgk_get_booking_by_code( $CODE );
if ( $existing ) {
    $wpdb->update( $bookings, $data, [ 'id' => (int) $existing['id'] ] );
    $booking_id = (int) $existing['id'];
} else {
    $data['created_at_utc'] = $now_utc;
    $wpdb->insert( $bookings, $data );
    $booking_id = (int) $wpdb->insert_id;
}

/* ── 6. Payment row (SUCCEEDED PayNow) ──────────────────── */
$payments = mgk_booking_table( 'payments' );
$pay_id   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$payments} WHERE booking_id = %d ORDER BY id DESC LIMIT 1", $booking_id ) );
$pdata = [
    'booking_id'    => $booking_id,
    'provider'      => 'PAYNOW',
    'amount'        => 33.00,
    'currency'      => 'SGD',
    'status'        => 'SUCCEEDED',
    'paid_at_utc'   => $now_utc,
    'updated_at_utc'=> $now_utc,
];
if ( $pay_id ) {
    $wpdb->update( $payments, $pdata, [ 'id' => $pay_id ] );
} else {
    $pdata['created_at_utc'] = $now_utc;
    $wpdb->insert( $payments, $pdata );
}

/* ── 7. Enrolment + lesson logs for the 1st child (real dashboard data) ──── */
$enr_id = 0; $log_count = 0;
if ( ! empty( $child_ids ) && function_exists( 'mgk_enrolment_create' ) && function_exists( 'mgk_lesson_log_create' ) ) {
    $kid = (int) $child_ids[0];   // Chen Rui En

    // Idempotent: clear prior enrolments + lessons for this child, then rebuild.
    foreach ( get_posts( [ 'post_type' => 'mg_enrolment', 'numberposts' => 50, 'fields' => 'ids', 'post_status' => 'any', 'meta_query' => [ [ 'key' => 'mgk_enr_child_id', 'value' => $kid ] ] ] ) as $e ) wp_delete_post( $e, true );
    foreach ( get_posts( [ 'post_type' => 'mg_lesson', 'numberposts' => 200, 'fields' => 'ids', 'post_status' => 'any', 'meta_query' => [ [ 'key' => 'mgk_lesson_child_id', 'value' => $kid ] ] ] ) as $e ) wp_delete_post( $e, true );

    // PACKAGE_8: 7 done → remaining 1 → triggers renewal nudge (BR-12) + review (BR-20).
    $enr_id = mgk_enrolment_create( [
        'child_id' => $kid, 'parent_user_id' => $uid, 'tutor_id' => (int) $tutor->ID,
        'plan_type' => 'PACKAGE_8', 'lessons_total' => 8, 'subject' => 'P5 Math',
        'valid_until' => gmdate( 'Y-m-d', strtotime( '+45 days' ) ), 'source_booking_id' => $booking_id, 'status' => 'ACTIVE',
    ] );

    // 7 attended lessons, engagement trending upward.
    $plan = [
        [ 'OKAY',              'Place value & rounding',      'Workbook p.12-14' ],
        [ 'OKAY',              'Fractions: equivalent forms', 'Worksheet A' ],
        [ 'GOOD',              'Fractions: + and −',          'Workbook p.20-22' ],
        [ 'GOOD',              'Decimals ↔ fractions',        'Worksheet B' ],
        [ 'GOOD',              'Ratio basics',                'Past-paper Q1-5' ],
        [ 'EXCELLENT',         'Word problems: model method', 'Past-paper Q6-10' ],
        [ 'EXCELLENT',         'Speed & rate problems',       'Revision set 3' ],
    ];
    foreach ( $plan as $i => $row ) {
        $weeks_ago = count( $plan ) - $i;   // oldest first
        $ldate = gmdate( 'Y-m-d', strtotime( "-{$weeks_ago} weeks" ) );
        mgk_lesson_log_create( [
            'enrolment_id' => $enr_id, 'child_id' => $kid, 'tutor_id' => (int) $tutor->ID,
            'lesson_number' => $i + 1, 'attendance' => 'ATTENDED', 'engagement' => $row[0],
            'topic' => $row[1], 'homework' => $row[2],
            'comment' => 'Engaged well; ' . strtolower( $row[1] ) . ' is improving.',
            'duration_min' => 90, 'lesson_date' => $ldate, 'submitted_at' => $ldate . ' 14:30:00',
        ] );
        $log_count++;
    }
}

/* ── Done ───────────────────────────────────────────────── */
$home = home_url();
echo "✓ Parent journey seeded.\n";
echo "  enrolment : #{$enr_id} PACKAGE_8 · {$log_count} lessons logged (Chen Rui En) → remaining 1 (renewal due)\n";
echo "  user      : #{$uid}  {$EMAIL}" . ( $NAME ? " ({$NAME})" : '' ) . "  role=mgk_parent\n";
echo "  children  : " . count( $child_ids ) . " linked (mgk_child_parent_user)\n";
echo "  tutor     : {$tutor->post_title} (#{$tutor->ID}) + contact meta\n";
echo "  booking   : {$CODE} (#{$booking_id}) CONFIRMED · PAID · \$33.00 · slot {$start_utc} UTC\n";
echo "  S12 URL   : {$home}/trial-confirmed/?booking={$CODE}\n";
echo "  invoice   : {$home}/trial-confirmed/?mgk_action=invoice&ref={$CODE}\n";
