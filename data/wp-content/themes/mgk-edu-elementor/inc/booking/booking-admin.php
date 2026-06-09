<?php
/**
 * MGK Booking Engine — Phase 0.5 · Tutor availability meta box (admin UI).
 * ========================================================================
 * The single most important admin-side piece (painpoint 3): a real repeater UI
 * on mg_teacher to enter weekly availability, exceptions, and booking settings —
 * NOT a raw JSON textarea. Saves to the meta keys read by booking-availability.php.
 *
 * Self-contained vanilla JS repeater (no build step, matches PHP-native house
 * style). The booking admin list page lives in booking-admin-bookings.php (Step 9).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'mgk_tutor_availability',
		'Booking Availability (live slot engine)',
		'mgk_tutor_availability_metabox',
		'mg_teacher',
		'normal',
		'high'
	);
} );

function mgk_tutor_availability_metabox( $post ) {
	$tutor_id = (int) $post->ID;
	$weekly   = mgk_get_tutor_weekly_availability( $tutor_id );
	$settings = mgk_get_tutor_booking_settings( $tutor_id );
	// Raw exceptions (keep ISO strings for the editor; the normalized reader
	// converts to DateTime, which we don't want in the form fields).
	$exc_raw  = get_post_meta( $tutor_id, '_mgk_availability_exceptions_json', true );
	$exceptions = is_string( $exc_raw ) ? json_decode( $exc_raw, true ) : ( is_array( $exc_raw ) ? $exc_raw : [] );
	if ( ! is_array( $exceptions ) ) $exceptions = [];

	$day_labels = [ 'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday' ];
	$modes = mgk_avail_modes();

	wp_nonce_field( 'mgk_save_tutor_availability_' . $tutor_id, 'mgk_tutor_availability_nonce' );
	?>
	<style>
		.mgk-av-wrap{font-size:13px}
		.mgk-av-wrap h4{margin:14px 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#1d2327}
		.mgk-av-day{display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f0f0f1}
		.mgk-av-day-name{width:90px;font-weight:600;padding-top:6px}
		.mgk-av-ranges{flex:1}
		.mgk-av-row{display:flex;gap:6px;align-items:center;margin-bottom:6px}
		.mgk-av-row input[type=time]{width:110px}
		.mgk-av-row select{min-width:90px}
		.mgk-av-rm,.mgk-exc-rm{color:#b32d2e;cursor:pointer;text-decoration:none;font-size:18px;line-height:1}
		.mgk-av-add,.mgk-exc-add{cursor:pointer}
		.mgk-exc-row{display:flex;gap:6px;align-items:center;margin-bottom:6px;flex-wrap:wrap}
		.mgk-exc-row select{min-width:140px}
		.mgk-set-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;max-width:760px}
		.mgk-set-grid label{display:block;font-weight:600;margin-bottom:3px}
		.mgk-set-grid input{width:100%}
	</style>
	<div class="mgk-av-wrap" id="mgk-av-wrap">
		<p class="description">Enter when this tutor is bookable. The live slot picker (S10) shows only slots derived from here, minus confirmed bookings and active holds. Times are <strong><?php echo esc_html( MGK_BOOKING_TZ ); ?></strong>.</p>

		<h4>Weekly availability</h4>
		<?php foreach ( $day_labels as $key => $label ) : ?>
			<div class="mgk-av-day" data-day="<?php echo esc_attr( $key ); ?>">
				<div class="mgk-av-day-name"><?php echo esc_html( $label ); ?></div>
				<div class="mgk-av-ranges">
					<?php
					$rows = $weekly[ $key ] ?? [];
					if ( empty( $rows ) ) {
						echo '<div class="mgk-av-empty"><em style="color:#777">Not available</em></div>';
					}
					foreach ( $rows as $r ) :
						?>
						<div class="mgk-av-row">
							<input type="time" name="mgk_av[<?php echo esc_attr( $key ); ?>][start][]" value="<?php echo esc_attr( $r['start'] ); ?>">
							<span>–</span>
							<input type="time" name="mgk_av[<?php echo esc_attr( $key ); ?>][end][]" value="<?php echo esc_attr( $r['end'] ); ?>">
							<select name="mgk_av[<?php echo esc_attr( $key ); ?>][mode][]">
								<?php foreach ( $modes as $m ) : ?>
									<option value="<?php echo esc_attr( $m ); ?>" <?php selected( $r['mode'], $m ); ?>><?php echo esc_html( ucfirst( strtolower( $m ) ) ); ?></option>
								<?php endforeach; ?>
							</select>
							<a class="mgk-av-rm" title="Remove">&times;</a>
						</div>
					<?php endforeach; ?>
					<a class="button button-small mgk-av-add" data-day="<?php echo esc_attr( $key ); ?>">+ Add time range</a>
				</div>
			</div>
		<?php endforeach; ?>

		<h4>Exceptions (overrides for specific dates)</h4>
		<div id="mgk-exc-list">
			<?php foreach ( $exceptions as $e ) :
				$etype = strtoupper( (string) ( $e['type'] ?? 'BLOCK' ) ); ?>
				<div class="mgk-exc-row">
					<select name="mgk_exc[type][]">
						<?php foreach ( [ 'BLOCK' => 'Block (unavailable)', 'EXTRA_AVAILABLE' => 'Extra available', 'HOLIDAY' => 'Holiday', 'TRAVEL_BLOCK' => 'Travel block' ] as $tk => $tl ) : ?>
							<option value="<?php echo esc_attr( $tk ); ?>" <?php selected( $etype, $tk ); ?>><?php echo esc_html( $tl ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="datetime-local" name="mgk_exc[start][]" value="<?php echo esc_attr( mgk_exc_to_local_input( $e['start_at'] ?? '' ) ); ?>">
					<span>→</span>
					<input type="datetime-local" name="mgk_exc[end][]" value="<?php echo esc_attr( mgk_exc_to_local_input( $e['end_at'] ?? '' ) ); ?>">
					<input type="text" name="mgk_exc[reason][]" placeholder="Reason" value="<?php echo esc_attr( (string) ( $e['reason'] ?? '' ) ); ?>" style="width:160px">
					<a class="mgk-exc-rm" title="Remove">&times;</a>
				</div>
			<?php endforeach; ?>
		</div>
		<a class="button button-small mgk-exc-add">+ Add exception</a>

		<h4>Booking settings</h4>
		<div class="mgk-set-grid">
			<div><label>Lesson duration (min)</label><input type="number" min="15" step="15" name="mgk_set[duration]" value="<?php echo esc_attr( $settings['duration'] ); ?>"></div>
			<div><label>Buffer before (min)</label><input type="number" min="0" step="5" name="mgk_set[buffer_before]" value="<?php echo esc_attr( $settings['buffer_before'] ); ?>"></div>
			<div><label>Buffer after (min)</label><input type="number" min="0" step="5" name="mgk_set[buffer_after]" value="<?php echo esc_attr( $settings['buffer_after'] ); ?>"></div>
			<div><label>Min notice (min)</label><input type="number" min="0" step="30" name="mgk_set[min_notice]" value="<?php echo esc_attr( $settings['min_notice'] ); ?>"></div>
			<div><label>Max advance (days)</label><input type="number" min="1" name="mgk_set[max_advance]" value="<?php echo esc_attr( $settings['max_advance'] ); ?>"></div>
		</div>
	</div>

	<script>
	( function () {
		var wrap = document.getElementById( 'mgk-av-wrap' );
		if ( ! wrap ) return;
		var MODES = <?php echo wp_json_encode( $modes ); ?>;

		function rangeRow( day ) {
			var opts = MODES.map( function ( m ) {
				return '<option value="' + m + '">' + m.charAt(0) + m.slice(1).toLowerCase() + '</option>';
			} ).join( '' );
			var div = document.createElement( 'div' );
			div.className = 'mgk-av-row';
			div.innerHTML =
				'<input type="time" name="mgk_av[' + day + '][start][]">' +
				'<span>–</span>' +
				'<input type="time" name="mgk_av[' + day + '][end][]">' +
				'<select name="mgk_av[' + day + '][mode][]">' + opts + '</select>' +
				'<a class="mgk-av-rm" title="Remove">&times;</a>';
			return div;
		}
		function excRow() {
			var div = document.createElement( 'div' );
			div.className = 'mgk-exc-row';
			div.innerHTML =
				'<select name="mgk_exc[type][]">' +
					'<option value="BLOCK">Block (unavailable)</option>' +
					'<option value="EXTRA_AVAILABLE">Extra available</option>' +
					'<option value="HOLIDAY">Holiday</option>' +
					'<option value="TRAVEL_BLOCK">Travel block</option>' +
				'</select>' +
				'<input type="datetime-local" name="mgk_exc[start][]">' +
				'<span>→</span>' +
				'<input type="datetime-local" name="mgk_exc[end][]">' +
				'<input type="text" name="mgk_exc[reason][]" placeholder="Reason" style="width:160px">' +
				'<a class="mgk-exc-rm" title="Remove">&times;</a>';
			return div;
		}
		wrap.addEventListener( 'click', function ( ev ) {
			var t = ev.target;
			if ( t.classList.contains( 'mgk-av-add' ) ) {
				ev.preventDefault();
				var box = t.closest( '.mgk-av-ranges' );
				var empty = box.querySelector( '.mgk-av-empty' );
				if ( empty ) empty.remove();
				box.insertBefore( rangeRow( t.getAttribute( 'data-day' ) ), t );
			} else if ( t.classList.contains( 'mgk-av-rm' ) ) {
				ev.preventDefault();
				t.closest( '.mgk-av-row' ).remove();
			} else if ( t.classList.contains( 'mgk-exc-add' ) ) {
				ev.preventDefault();
				document.getElementById( 'mgk-exc-list' ).appendChild( excRow() );
			} else if ( t.classList.contains( 'mgk-exc-rm' ) ) {
				ev.preventDefault();
				t.closest( '.mgk-exc-row' ).remove();
			}
		} );
	} )();
	</script>
	<?php
}

/** Convert a stored ISO/UTC exception string to a local datetime-local input value. */
function mgk_exc_to_local_input( $iso ) {
	$iso = trim( (string) $iso );
	if ( ! $iso ) return '';
	try {
		$dt = new DateTime( $iso );
		$dt->setTimezone( mgk_booking_tz() );
		return $dt->format( 'Y-m-d\TH:i' );
	} catch ( Exception $e ) {
		return '';
	}
}

/**
 * Save handler — validates nonce + caps, then rebuilds the JSON meta from the
 * repeater fields.
 */
add_action( 'save_post_mg_teacher', function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['mgk_tutor_availability_nonce'] ) ) return;
	if ( ! wp_verify_nonce( wp_unslash( $_POST['mgk_tutor_availability_nonce'] ), 'mgk_save_tutor_availability_' . $post_id ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	// ── Weekly availability ──
	$weekly = [];
	$av_in = isset( $_POST['mgk_av'] ) && is_array( $_POST['mgk_av'] ) ? wp_unslash( $_POST['mgk_av'] ) : [];
	foreach ( mgk_avail_days() as $day ) {
		$starts = (array) ( $av_in[ $day ]['start'] ?? [] );
		$ends   = (array) ( $av_in[ $day ]['end'] ?? [] );
		$modes  = (array) ( $av_in[ $day ]['mode'] ?? [] );
		$ranges = [];
		foreach ( $starts as $i => $s ) {
			$start = mgk_avail_clean_time( $s );
			$end   = mgk_avail_clean_time( $ends[ $i ] ?? '' );
			$mode  = strtoupper( (string) ( $modes[ $i ] ?? 'ONLINE' ) );
			if ( ! in_array( $mode, mgk_avail_modes(), true ) ) $mode = 'ONLINE';
			if ( $start && $end && $start < $end ) {
				$ranges[] = [ 'start' => $start, 'end' => $end, 'mode' => $mode ];
			}
		}
		if ( $ranges ) $weekly[ $day ] = $ranges;
	}
	update_post_meta( $post_id, '_mgk_weekly_availability_json', wp_slash( wp_json_encode( $weekly ) ) );

	// ── Exceptions ──
	$exceptions = [];
	$exc_in = isset( $_POST['mgk_exc'] ) && is_array( $_POST['mgk_exc'] ) ? wp_unslash( $_POST['mgk_exc'] ) : [];
	$types  = (array) ( $exc_in['type'] ?? [] );
	$starts = (array) ( $exc_in['start'] ?? [] );
	$ends   = (array) ( $exc_in['end'] ?? [] );
	$reasons = (array) ( $exc_in['reason'] ?? [] );
	$valid_types = [ 'BLOCK', 'EXTRA_AVAILABLE', 'HOLIDAY', 'TRAVEL_BLOCK' ];
	foreach ( $types as $i => $type ) {
		$type = strtoupper( (string) $type );
		if ( ! in_array( $type, $valid_types, true ) ) continue;
		$start_iso = mgk_local_input_to_iso( $starts[ $i ] ?? '' );
		$end_iso   = mgk_local_input_to_iso( $ends[ $i ] ?? '' );
		if ( ! $start_iso || ! $end_iso ) continue;
		$exceptions[] = [
			'type'     => $type,
			'start_at' => $start_iso,
			'end_at'   => $end_iso,
			'reason'   => sanitize_text_field( (string) ( $reasons[ $i ] ?? '' ) ),
		];
	}
	update_post_meta( $post_id, '_mgk_availability_exceptions_json', wp_slash( wp_json_encode( $exceptions ) ) );

	// ── Booking settings ──
	$set = isset( $_POST['mgk_set'] ) && is_array( $_POST['mgk_set'] ) ? wp_unslash( $_POST['mgk_set'] ) : [];
	update_post_meta( $post_id, '_mgk_lesson_duration_minutes', max( 15, (int) ( $set['duration'] ?? MGK_DEFAULT_DURATION ) ) );
	update_post_meta( $post_id, '_mgk_buffer_before_minutes', max( 0, (int) ( $set['buffer_before'] ?? 0 ) ) );
	update_post_meta( $post_id, '_mgk_buffer_after_minutes', max( 0, (int) ( $set['buffer_after'] ?? 15 ) ) );
	update_post_meta( $post_id, '_mgk_min_notice_minutes', max( 0, (int) ( $set['min_notice'] ?? 1440 ) ) );
	update_post_meta( $post_id, '_mgk_max_advance_days', max( 1, (int) ( $set['max_advance'] ?? 30 ) ) );
} );

/** datetime-local input (local tz) → ISO8601 with offset, for storage. */
function mgk_local_input_to_iso( $val ) {
	$val = trim( (string) $val );
	if ( ! $val ) return '';
	try {
		$dt = new DateTime( $val, mgk_booking_tz() );
		return $dt->format( 'c' );
	} catch ( Exception $e ) {
		return '';
	}
}
