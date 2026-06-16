<?php
/**
 * MGK — Discounts admin (wp-admin → Discounts).
 * =============================================
 * Top-level "Discounts" menu with two screens:
 *   • Discount Rules — friendly toggles + % for the automatic business rules
 *     (BR-01 trial / BR-05 sibling / BR-06 returning / package tiers / cap / GST).
 *     Saves to the `mgk_discount_rules` option read by mgk_discount_rules().
 *   • Vouchers — the mgk_voucher CPT (registered in mgk-voucher-cpt.php) nests
 *     here via show_in_menu => 'mgk-discounts'.
 *
 * Designed to be self-explanatory for a non-technical agency admin: every rule
 * is one row with an on/off switch and a number, plus a live "what a parent
 * pays" preview so they see the effect before saving.
 *
 * @package mgk-edu-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
	add_menu_page(
		'Discounts', 'Discounts', 'manage_options', 'mgk-discounts',
		'mgk_discounts_rules_page', 'dashicons-tag', 27
	);
	add_submenu_page( 'mgk-discounts', 'Discount Rules', 'Discount Rules', 'manage_options', 'mgk-discounts', 'mgk_discounts_rules_page' );
	// (Vouchers submenu is injected by the CPT registration.)
} );

/** Handle the rules form POST. */
function mgk_discounts_save_rules() {
	if ( ! isset( $_POST['mgk_discount_rules_nonce'] ) || ! wp_verify_nonce( $_POST['mgk_discount_rules_nonce'], 'mgk_discount_rules_save' ) ) return false;
	if ( ! current_user_can( 'manage_options' ) ) return false;

	$ints = [ 'trial_pct', 'package_8_pct', 'package_16_pct', 'sibling_pct', 'sibling_pkg_pct', 'returning_pct', 'returning_days', 'stack_cap_pct', 'gst_pct' ];
	$bools= [ 'trial_enabled', 'package_enabled', 'sibling_enabled', 'returning_enabled', 'gst_inclusive' ];

	$out = [];
	foreach ( $ints as $k )  $out[ $k ] = max( 0, (int) ( $_POST[ $k ] ?? 0 ) );
	foreach ( $bools as $k ) $out[ $k ] = isset( $_POST[ $k ] ) ? 1 : 0;

	// Clamp percentages to sane ranges.
	foreach ( [ 'trial_pct', 'package_8_pct', 'package_16_pct', 'sibling_pct', 'sibling_pkg_pct', 'returning_pct', 'stack_cap_pct', 'gst_pct' ] as $k ) {
		$out[ $k ] = min( 100, $out[ $k ] );
	}
	$out['returning_days'] = max( 1, $out['returning_days'] );

	update_option( 'mgk_discount_rules', $out );
	return true;
}

function mgk_discounts_rules_page() {
	$saved = false;
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		$saved = mgk_discounts_save_rules();
	}
	$r = mgk_discount_rules();

	// Live preview at a sample $65/hr rate (no loyalty) so admin sees the effect.
	$sample = function_exists( 'mgk_quote' ) ? mgk_quote( [ 'item_type' => 'trial', 'rate_num' => 65, 'line_label' => 'Trial lesson', 'apply_loyalty' => false ] ) : null;
	?>
	<div class="wrap mgk-disc-admin">
		<h1>Discounts</h1>
		<p style="font-size:14px;color:#50575e;max-width:760px">
			These rules decide every price a parent sees and pays — the same numbers appear on tutor profiles, the booking checkout, and the receipt.
			Turn a rule off to remove it everywhere instantly. Manage one-off codes under <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mgk_voucher' ) ); ?>">Vouchers</a>.
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>Discount rules saved. The new prices are live across the site.</p></div>
		<?php endif; ?>

		<style>
			.mgk-disc-admin .mgk-rule{display:grid;grid-template-columns:46px 1fr 150px;gap:14px;align-items:center;background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:14px 18px;margin-bottom:10px}
			.mgk-disc-admin .mgk-rule h3{margin:0 0 2px;font-size:14px}
			.mgk-disc-admin .mgk-rule p{margin:0;color:#646970;font-size:12px}
			.mgk-disc-admin .mgk-rule .num{display:flex;align-items:center;gap:6px;justify-content:flex-end}
			.mgk-disc-admin .mgk-rule .num input{width:72px}
			.mgk-disc-admin .mgk-sw{position:relative;display:inline-block;width:42px;height:24px}
			.mgk-disc-admin .mgk-sw input{opacity:0;width:0;height:0}
			.mgk-disc-admin .mgk-sw span{position:absolute;cursor:pointer;inset:0;background:#c3c4c7;border-radius:24px;transition:.15s}
			.mgk-disc-admin .mgk-sw span:before{content:"";position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.15s}
			.mgk-disc-admin .mgk-sw input:checked + span{background:#2271b1}
			.mgk-disc-admin .mgk-sw input:checked + span:before{transform:translateX(18px)}
			.mgk-disc-admin .mgk-grp{margin:22px 0 8px;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#646970}
			.mgk-disc-admin .mgk-preview{background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;padding:16px 18px;max-width:340px}
			.mgk-disc-admin .mgk-preview .row{display:flex;justify-content:space-between;font-size:13px;padding:3px 0}
			.mgk-disc-admin .mgk-preview .tot{border-top:1px solid #dcdcde;margin-top:6px;padding-top:8px;font-weight:700}
			.mgk-disc-admin .mgk-cols{display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start}
			@media(max-width:1100px){.mgk-disc-admin .mgk-cols{grid-template-columns:1fr}}
		</style>

		<form method="post" class="mgk-cols">
			<?php wp_nonce_field( 'mgk_discount_rules_save', 'mgk_discount_rules_nonce' ); ?>
			<div>
				<div class="mgk-grp">Automatic discounts</div>

				<?php
				$row = function ( $enabled_key, $title, $desc, $num_fields ) use ( $r ) {
					?>
					<div class="mgk-rule">
						<label class="mgk-sw">
							<input type="checkbox" name="<?php echo esc_attr( $enabled_key ); ?>" value="1" <?php checked( (int) $r[ $enabled_key ], 1 ); ?>>
							<span></span>
						</label>
						<div><h3><?php echo esc_html( $title ); ?></h3><p><?php echo wp_kses_post( $desc ); ?></p></div>
						<div class="num">
							<?php foreach ( $num_fields as $nf ) : ?>
								<input type="number" name="<?php echo esc_attr( $nf['key'] ); ?>" value="<?php echo esc_attr( $r[ $nf['key'] ] ); ?>" min="0" max="<?php echo (int) ( $nf['max'] ?? 100 ); ?>" step="1" title="<?php echo esc_attr( $nf['title'] ?? '' ); ?>">
								<span><?php echo esc_html( $nf['suffix'] ?? '%' ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				};
				$row( 'trial_enabled',     'Trial lesson discount (BR-01)', 'First trial lesson, off the tutor’s hourly rate.', [ [ 'key' => 'trial_pct' ] ] );
				$row( 'package_enabled',   'Package savings',               '8-lesson and 16-lesson package discounts.', [ [ 'key' => 'package_8_pct', 'title' => '8-lesson %' ], [ 'key' => 'package_16_pct', 'title' => '16-lesson %' ] ] );
				$row( 'sibling_enabled',   'Sibling discount (BR-05)',      'Auto-applied when the same parent account already has a child. Trial % then package %.', [ [ 'key' => 'sibling_pct', 'title' => 'trial %' ], [ 'key' => 'sibling_pkg_pct', 'title' => 'package %' ] ] );
				$row( 'returning_enabled', 'Returning student (BR-06)',     'Re-signs within the window below.', [ [ 'key' => 'returning_pct', 'title' => '%' ], [ 'key' => 'returning_days', 'max' => 365, 'suffix' => 'days', 'title' => 'window' ] ] );
				?>

				<div class="mgk-grp">Limits &amp; tax</div>
				<div class="mgk-rule" style="grid-template-columns:1fr 150px">
					<div><h3>Maximum stacked discount (BR-05/06)</h3><p>Hard ceiling on all discounts combined, as a % of the order. Protects the agency’s margin.</p></div>
					<div class="num"><input type="number" name="stack_cap_pct" value="<?php echo esc_attr( $r['stack_cap_pct'] ); ?>" min="0" max="100" step="1"><span>%</span></div>
				</div>
				<div class="mgk-rule">
					<label class="mgk-sw"><input type="checkbox" name="gst_inclusive" value="1" <?php checked( (int) $r['gst_inclusive'], 1 ); ?>><span></span></label>
					<div><h3>GST (BR-04)</h3><p>On = prices shown already include GST. Off = GST added on top.</p></div>
					<div class="num"><input type="number" name="gst_pct" value="<?php echo esc_attr( $r['gst_pct'] ); ?>" min="0" max="100" step="1"><span>%</span></div>
				</div>

				<p style="margin-top:18px"><?php submit_button( 'Save discount rules', 'primary', 'submit', false ); ?></p>
			</div>

			<div>
				<div class="mgk-grp">Live preview · trial @ $65/hr</div>
				<div class="mgk-preview">
					<?php if ( $sample ) : foreach ( $sample['rows'] as $row ) : ?>
						<div class="row"><span><?php echo esc_html( $row['label'] ); ?></span><span><?php echo esc_html( $row['value'] ); ?></span></div>
					<?php endforeach; ?>
						<div class="row tot"><span>Due at checkout</span><span><?php echo esc_html( $sample['total_str'] ); ?></span></div>
						<div class="row" style="color:#646970;font-size:11px"><span><?php echo esc_html( $sample['gst_note'] ); ?></span></div>
					<?php endif; ?>
					<p style="font-size:11px;color:#646970;margin:10px 0 0">Loyalty &amp; voucher discounts apply per-parent on top, capped at <?php echo (int) $r['stack_cap_pct']; ?>%.</p>
				</div>
			</div>
		</form>
	</div>
	<?php
}
