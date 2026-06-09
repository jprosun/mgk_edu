<?php
/**
 * MGK Booking Engine — Phase 0.5 · Availability data + slot calculation (LOCKED).
 * ===============================================================================
 * Reads a tutor's availability from mg_teacher post meta (entered via the
 * booking-admin.php meta box), then expands it into concrete bookable slots,
 * subtracting confirmed bookings and active (non-expired) block locks.
 *
 * Painpoint C (lazy expiry): when computing free slots we IGNORE any lock whose
 * expires_at_utc < now. We never trust WP-Cron to have cleaned it up first — an
 * expired hold can never block a new booker because we treat it as gone on read.
 *
 * Meta keys (plan §9):
 *   _mgk_weekly_availability_json   { "mon":[{start,end,mode}], ... }
 *   _mgk_availability_exceptions_json [ {type,start_at,end_at,reason}, ... ]
 *   _mgk_lesson_duration_minutes / _mgk_buffer_before_minutes /
 *   _mgk_buffer_after_minutes / _mgk_min_notice_minutes / _mgk_max_advance_days
 *
 * Time model: weekly rules are LOCAL wall-clock (Asia/Singapore) per day; we
 * convert each candidate slot to UTC for storage/locking. Exceptions are stored
 * as ISO8601 with offset.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'MGK_BOOKING_TZ' ) )          define( 'MGK_BOOKING_TZ', 'Asia/Singapore' );
if ( ! defined( 'MGK_BLOCK_MINUTES' ) )       define( 'MGK_BLOCK_MINUTES', 15 );
if ( ! defined( 'MGK_DEFAULT_DURATION' ) )    define( 'MGK_DEFAULT_DURATION', 90 );

/** Ordered weekday keys (Mon-first), matching the meta JSON. */
function mgk_avail_days() {
	return [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
}

/** Allowed lesson location modes. */
function mgk_avail_modes() {
	return [ 'ONLINE', 'HOME', 'CENTER', 'HYBRID' ];
}

/** The site's booking timezone as a DateTimeZone. */
function mgk_booking_tz() {
	try {
		return new DateTimeZone( MGK_BOOKING_TZ );
	} catch ( Exception $e ) {
		return new DateTimeZone( 'UTC' );
	}
}

/**
 * Booking settings for a tutor, with sane defaults. All ints.
 * @return array{duration:int,buffer_before:int,buffer_after:int,min_notice:int,max_advance:int}
 */
function mgk_get_tutor_booking_settings( $tutor_id ) {
	$tutor_id = (int) $tutor_id;
	$get = function ( $key, $default ) use ( $tutor_id ) {
		$v = get_post_meta( $tutor_id, $key, true );
		return ( $v === '' || $v === null ) ? (int) $default : (int) $v;
	};
	return [
		'duration'      => max( 15, $get( '_mgk_lesson_duration_minutes', MGK_DEFAULT_DURATION ) ),
		'buffer_before' => max( 0, $get( '_mgk_buffer_before_minutes', 0 ) ),
		'buffer_after'  => max( 0, $get( '_mgk_buffer_after_minutes', 15 ) ),
		'min_notice'    => max( 0, $get( '_mgk_min_notice_minutes', 1440 ) ),
		'max_advance'   => max( 1, $get( '_mgk_max_advance_days', 30 ) ),
	];
}

/**
 * Weekly availability for a tutor, normalized to:
 *   [ 'mon' => [ ['start'=>'HH:MM','end'=>'HH:MM','mode'=>'ONLINE'], ... ], ... ]
 * Always returns all 7 day keys (possibly empty arrays).
 */
function mgk_get_tutor_weekly_availability( $tutor_id ) {
	$raw = get_post_meta( (int) $tutor_id, '_mgk_weekly_availability_json', true );
	$data = is_string( $raw ) ? json_decode( $raw, true ) : ( is_array( $raw ) ? $raw : [] );
	if ( ! is_array( $data ) ) $data = [];

	$out = [];
	foreach ( mgk_avail_days() as $day ) {
		$ranges = [];
		foreach ( (array) ( $data[ $day ] ?? [] ) as $r ) {
			$start = mgk_avail_clean_time( $r['start'] ?? '' );
			$end   = mgk_avail_clean_time( $r['end'] ?? '' );
			$mode  = strtoupper( (string) ( $r['mode'] ?? 'ONLINE' ) );
			if ( ! in_array( $mode, mgk_avail_modes(), true ) ) $mode = 'ONLINE';
			if ( $start && $end && $start < $end ) {
				$ranges[] = [ 'start' => $start, 'end' => $end, 'mode' => $mode ];
			}
		}
		$out[ $day ] = $ranges;
	}
	return $out;
}

/** Validate/normalize an "HH:MM" 24h time string; '' if invalid. */
function mgk_avail_clean_time( $t ) {
	$t = trim( (string) $t );
	if ( ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $t, $m ) ) return '';
	return sprintf( '%02d:%02d', (int) $m[1], (int) $m[2] );
}

/**
 * Exceptions for a tutor, normalized:
 *   [ ['type'=>'BLOCK|EXTRA_AVAILABLE|HOLIDAY|TRAVEL_BLOCK',
 *      'start_at'=>ISO,'end_at'=>ISO,'reason'=>str], ... ]
 */
function mgk_get_tutor_exceptions( $tutor_id ) {
	$raw = get_post_meta( (int) $tutor_id, '_mgk_availability_exceptions_json', true );
	$data = is_string( $raw ) ? json_decode( $raw, true ) : ( is_array( $raw ) ? $raw : [] );
	if ( ! is_array( $data ) ) return [];

	$types = [ 'BLOCK', 'EXTRA_AVAILABLE', 'HOLIDAY', 'TRAVEL_BLOCK' ];
	$out = [];
	foreach ( $data as $e ) {
		$type = strtoupper( (string) ( $e['type'] ?? '' ) );
		if ( ! in_array( $type, $types, true ) ) continue;
		$start = mgk_avail_parse_iso( $e['start_at'] ?? '' );
		$end   = mgk_avail_parse_iso( $e['end_at'] ?? '' );
		if ( ! $start || ! $end || $end <= $start ) continue;
		$out[] = [
			'type'     => $type,
			'start_at' => $start, // DateTime (UTC)
			'end_at'   => $end,
			'reason'   => sanitize_text_field( (string) ( $e['reason'] ?? '' ) ),
		];
	}
	return $out;
}

/** Parse an ISO8601 string to a UTC DateTime, or null. */
function mgk_avail_parse_iso( $s ) {
	$s = trim( (string) $s );
	if ( ! $s ) return null;
	try {
		$dt = new DateTime( $s, mgk_booking_tz() );
		$dt->setTimezone( new DateTimeZone( 'UTC' ) );
		return $dt;
	} catch ( Exception $e ) {
		return null;
	}
}

/* ── Slot key + 15-minute block helpers (shared with the lock engine) ──── */

/**
 * Canonical slot key: "{tutor}:{startUTC}:{endUTC}" with second precision.
 * Used to identify a slot across availability → hold → payment.
 */
function mgk_slot_key( $tutor_id, $start_utc, $end_utc ) {
	return (int) $tutor_id . ':' . $start_utc . ':' . $end_utc;
}

/**
 * Expand a [start,end) UTC interval into the list of MGK_BLOCK_MINUTES block
 * start datetimes it occupies. A 90-min lesson → six 15-min blocks. These are
 * the rows the lock engine INSERTs; one collision = double-book prevented.
 *
 * @return string[] e.g. ['2026-06-10 10:00:00', '2026-06-10 10:15:00', ...]
 */
function mgk_expand_to_blocks( $start_utc, $end_utc ) {
	$blocks = [];
	try {
		$cur = new DateTime( $start_utc, new DateTimeZone( 'UTC' ) );
		$end = new DateTime( $end_utc, new DateTimeZone( 'UTC' ) );
	} catch ( Exception $e ) {
		return $blocks;
	}
	$step = MGK_BLOCK_MINUTES * 60;
	while ( $cur < $end ) {
		$blocks[] = $cur->format( 'Y-m-d H:i:s' );
		$cur->modify( '+' . $step . ' seconds' );
	}
	return $blocks;
}

/* ── Slot generation ───────────────────────────────────────────────────── */

/**
 * Compute bookable slots for a tutor between two dates.
 *
 * Pipeline (plan §11):
 *   weekly rules → expand per day in range
 *   + EXTRA_AVAILABLE exceptions
 *   − BLOCK/HOLIDAY/TRAVEL_BLOCK exceptions
 *   − confirmed bookings
 *   − ACTIVE block locks (lazy: expired locks ignored — painpoint C)
 *   − min_notice / max_advance window
 *
 * @return array{tutor_id:int,timezone:string,duration_minutes:int,slots:array}
 */
function mgk_engine_available_slots( $tutor_id, $from_date, $to_date, $duration_minutes = 0 ) {
	$tutor_id = (int) $tutor_id;
	$settings = mgk_get_tutor_booking_settings( $tutor_id );
	$duration = $duration_minutes > 0 ? (int) $duration_minutes : $settings['duration'];

	$weekly     = mgk_get_tutor_weekly_availability( $tutor_id );
	$exceptions = mgk_get_tutor_exceptions( $tutor_id );

	$tz  = mgk_booking_tz();
	$utc = new DateTimeZone( 'UTC' );

	// Bound the window by min_notice (earliest) and max_advance (latest).
	$now_utc = new DateTime( 'now', $utc );
	$earliest = clone $now_utc;
	$earliest->modify( '+' . $settings['min_notice'] . ' minutes' );
	$latest = clone $now_utc;
	$latest->modify( '+' . $settings['max_advance'] . ' days' );

	try {
		$from = new DateTime( $from_date . ' 00:00:00', $tz );
		$to   = new DateTime( $to_date . ' 23:59:59', $tz );
	} catch ( Exception $e ) {
		return [ 'tutor_id' => $tutor_id, 'timezone' => MGK_BOOKING_TZ, 'duration_minutes' => $duration, 'slots' => [] ];
	}

	$day_keys = mgk_avail_days(); // mon..sun, index 0..6
	$candidates = []; // each: ['start'=>DateTime UTC,'end'=>DateTime UTC,'mode'=>str]

	// 1+2. Expand weekly rules across each day in range, split into duration slots.
	$cursor = clone $from;
	$cursor->setTime( 0, 0, 0 );
	while ( $cursor <= $to ) {
		$dow_iso = (int) $cursor->format( 'N' ); // 1=Mon..7=Sun
		$day_key = $day_keys[ $dow_iso - 1 ];
		foreach ( ( $weekly[ $day_key ] ?? [] ) as $range ) {
			mgk_split_range_into_slots( $cursor, $range, $duration, $settings['buffer_after'], $tz, $utc, $candidates );
		}
		$cursor->modify( '+1 day' );
	}

	// 3. Add EXTRA_AVAILABLE exceptions as extra candidate windows.
	foreach ( $exceptions as $exc ) {
		if ( $exc['type'] === 'EXTRA_AVAILABLE' ) {
			mgk_split_interval_into_slots( $exc['start_at'], $exc['end_at'], $duration, $settings['buffer_after'], $utc, $candidates, 'ONLINE' );
		}
	}

	// 4. Remove blocking exceptions (BLOCK / HOLIDAY / TRAVEL_BLOCK).
	$blocks = array_filter( $exceptions, function ( $e ) {
		return in_array( $e['type'], [ 'BLOCK', 'HOLIDAY', 'TRAVEL_BLOCK' ], true );
	} );

	// 5. Confirmed bookings + active locks for this tutor (single queries).
	$busy_bookings = mgk_get_busy_booking_intervals( $tutor_id, $from->format( 'Y-m-d H:i:s' ), $to->format( 'Y-m-d H:i:s' ) );
	$active_locks  = mgk_get_active_lock_block_set( $tutor_id ); // assoc: 'Y-m-d H:i:s' => true

	$slots = [];
	foreach ( $candidates as $c ) {
		// Window guard.
		if ( $c['start'] < $earliest || $c['start'] > $latest ) continue;

		// Blocking exceptions overlap?
		if ( mgk_interval_overlaps_any( $c['start'], $c['end'], $blocks ) ) continue;

		// Confirmed booking overlap?
		$overlaps_booking = false;
		foreach ( $busy_bookings as $b ) {
			if ( $c['start'] < $b['end'] && $c['end'] > $b['start'] ) { $overlaps_booking = true; break; }
		}
		if ( $overlaps_booking ) continue;

		// Active lock on any of its blocks? (expired locks already filtered out)
		$locked = false;
		foreach ( mgk_expand_to_blocks( $c['start']->format( 'Y-m-d H:i:s' ), $c['end']->format( 'Y-m-d H:i:s' ) ) as $blk ) {
			if ( isset( $active_locks[ $blk ] ) ) { $locked = true; break; }
		}
		if ( $locked ) continue;

		$start_utc = $c['start']->format( 'Y-m-d H:i:s' );
		$end_utc   = $c['end']->format( 'Y-m-d H:i:s' );
		$disp_s = clone $c['start']; $disp_s->setTimezone( $tz );
		$disp_e = clone $c['end'];   $disp_e->setTimezone( $tz );

		$slots[] = [
			'slot_key'      => mgk_slot_key( $tutor_id, $start_utc, $end_utc ),
			'start_at_utc'  => $c['start']->format( 'Y-m-d\TH:i:s\Z' ),
			'end_at_utc'    => $c['end']->format( 'Y-m-d\TH:i:s\Z' ),
			'display_start' => $disp_s->format( 'Y-m-d H:i' ),
			'display_end'   => $disp_e->format( 'H:i' ),
			'display_day'   => $disp_s->format( 'D, j M' ),
			'mode'          => $c['mode'],
			'status'        => 'AVAILABLE',
		];
	}

	// Stable order by start time.
	usort( $slots, function ( $a, $b ) { return strcmp( $a['start_at_utc'], $b['start_at_utc'] ); } );

	return [
		'tutor_id'         => $tutor_id,
		'timezone'         => MGK_BOOKING_TZ,
		'duration_minutes' => $duration,
		'slots'            => $slots,
	];
}

/** Split one weekly range on a given local day into back-to-back duration slots. */
function mgk_split_range_into_slots( $local_day, $range, $duration, $buffer_after, $tz, $utc, &$out ) {
	list( $sh, $sm ) = array_map( 'intval', explode( ':', $range['start'] ) );
	list( $eh, $em ) = array_map( 'intval', explode( ':', $range['end'] ) );
	$start = clone $local_day; $start->setTime( $sh, $sm, 0 );
	$end   = clone $local_day; $end->setTime( $eh, $em, 0 );

	$slot_secs = $duration * 60;
	$advance   = ( $duration + $buffer_after ) * 60;

	$cur = clone $start;
	while ( true ) {
		$slot_end = clone $cur; $slot_end->modify( '+' . $slot_secs . ' seconds' );
		if ( $slot_end > $end ) break;
		$su = clone $cur;      $su->setTimezone( $utc );
		$eu = clone $slot_end; $eu->setTimezone( $utc );
		$out[] = [ 'start' => $su, 'end' => $eu, 'mode' => $range['mode'] ];
		$cur->modify( '+' . $advance . ' seconds' );
	}
}

/** Split an absolute UTC interval into duration slots (for EXTRA_AVAILABLE). */
function mgk_split_interval_into_slots( $start_utc_dt, $end_utc_dt, $duration, $buffer_after, $utc, &$out, $mode = 'ONLINE' ) {
	$slot_secs = $duration * 60;
	$advance   = ( $duration + $buffer_after ) * 60;
	$cur = clone $start_utc_dt;
	while ( true ) {
		$slot_end = clone $cur; $slot_end->modify( '+' . $slot_secs . ' seconds' );
		if ( $slot_end > $end_utc_dt ) break;
		$out[] = [ 'start' => clone $cur, 'end' => clone $slot_end, 'mode' => $mode ];
		$cur->modify( '+' . $advance . ' seconds' );
	}
}

/** True if [start,end) overlaps any exception interval in $list. */
function mgk_interval_overlaps_any( $start, $end, $list ) {
	foreach ( $list as $e ) {
		if ( $start < $e['end_at'] && $end > $e['start_at'] ) return true;
	}
	return false;
}

/**
 * Confirmed/active booking intervals for a tutor in a window.
 * Statuses that occupy a slot: HELD, PENDING_PAYMENT, CONFIRMED, RESCHEDULED,
 * COMPLETED. (HELD/PENDING are also covered by locks, but bookings are the
 * durable record.)
 * @return array of ['start'=>DateTime UTC,'end'=>DateTime UTC]
 */
function mgk_get_busy_booking_intervals( $tutor_id, $from_utc, $to_utc ) {
	global $wpdb;
	$table = mgk_booking_table( 'bookings' );
	if ( ! $table ) return [];
	$utc = new DateTimeZone( 'UTC' );
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT start_at_utc, end_at_utc FROM {$table}
		 WHERE tutor_post_id = %d
		   AND status IN ('HELD','PENDING_PAYMENT','CONFIRMED','RESCHEDULED','COMPLETED')
		   AND end_at_utc > %s AND start_at_utc < %s",
		(int) $tutor_id, $from_utc, $to_utc
	), ARRAY_A );
	$out = [];
	foreach ( (array) $rows as $r ) {
		try {
			$out[] = [
				'start' => new DateTime( $r['start_at_utc'], $utc ),
				'end'   => new DateTime( $r['end_at_utc'], $utc ),
			];
		} catch ( Exception $e ) { /* skip bad row */ }
	}
	return $out;
}

/**
 * Set of block_start datetimes that are currently locked for a tutor.
 * Painpoint C: a HOLD lock with expires_at_utc < now is treated as GONE, even if
 * the cron hasn't deleted the row yet. BOOKING locks (expires_at_utc NULL) always
 * count.
 * @return array<string,true>  keyed by 'Y-m-d H:i:s'
 */
function mgk_get_active_lock_block_set( $tutor_id ) {
	global $wpdb;
	$table = mgk_booking_table( 'locks' );
	if ( ! $table ) return [];
	$now = mgk_booking_now_utc();
	$rows = $wpdb->get_col( $wpdb->prepare(
		"SELECT block_start_at_utc FROM {$table}
		 WHERE tutor_post_id = %d
		   AND ( lock_type = 'BOOKING' OR expires_at_utc IS NULL OR expires_at_utc > %s )",
		(int) $tutor_id, $now
	) );
	$set = [];
	foreach ( (array) $rows as $b ) $set[ $b ] = true;
	return $set;
}
