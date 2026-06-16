<?php
/**
 * S12 — Manage booking actions + modals.
 *
 * Two outline buttons (Reschedule / Cancel & refund) open modals. The refund
 * tier rules (BR-07), reschedule limits (BR-23 / FR-BOOK-09) and calendar data
 * all come from the locked view ($args['refund'], $args['reschedule'],
 * $args['calendar']). NO refund/reschedule is processed on click — modals only
 * preview; confirm posts to a locked handler. Button labels are SAFE copy.
 *
 * @var array $args
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$a   = (array) ( $args ?? [] );
$rf  = (array) ( $a['refund'] ?? [] );
$rs  = (array) ( $a['reschedule'] ?? [] );
$cal = (array) ( $a['calendar'] ?? [] );

$reschedule_label = $a['reschedule_label'] ?? '↻ Reschedule';
$cancel_label     = $a['cancel_label']     ?? '× Cancel & refund';

if ( ! empty( $a['is_package'] ) ) :
    $bookings_url = (string) ( $a['urls']['bookings'] ?? home_url( '/my-bookings/' ) );
    ?>
<section class="mgk-cf-manage" data-event="confirm_manage_view">
    <a class="mgk-cf-manage-btn" href="<?php echo esc_url( $bookings_url ); ?>">View package &amp; schedule lessons</a>
</section>
<?php
    return;
endif;
?>
<section class="mgk-cf-manage" data-event="confirm_manage_view">
    <button type="button" class="mgk-cf-manage-btn" data-mgk-modal-open="reschedule"
            data-event="reschedule_click"><?php echo esc_html( $reschedule_label ); ?></button>
    <button type="button" class="mgk-cf-manage-btn" data-mgk-modal-open="cancel-refund"
            data-event="cancel_refund_click"><?php echo esc_html( $cancel_label ); ?></button>
</section>

<!-- ── Cancel & refund preview modal (FR-PAY-10) ─────────────── -->
<div class="mgk-cf-modal" data-mgk-modal="cancel-refund" hidden>
    <div class="mgk-cf-modal__overlay" data-mgk-modal-close></div>
    <div class="mgk-cf-modal__panel" role="dialog" aria-modal="true" aria-label="Cancel and refund preview">
        <div class="mgk-cf-modal__head">
            <span class="mgk-cf-modal__title">CANCEL &amp; REFUND PREVIEW (FR-PAY-10)</span>
            <button type="button" class="mgk-cf-modal__x" data-mgk-modal-close aria-label="Close">×</button>
        </div>
        <div class="mgk-cf-modal__body">
            <p class="mgk-cf-modal__h">Cancel trial — refund preview</p>
            <p class="mgk-cf-modal__sub">BASED ON BR-07 TIERS &amp; TIME TO LESSON</p>
            <div class="mgk-cf-refund-tiers">
                <?php foreach ( (array) ( $rf['tiers'] ?? [] ) as $t ) :
                    $on = ( $t['key'] ?? '' ) === ( $rf['entitled'] ?? '' ); ?>
                <div class="mgk-cf-refund-tier<?php echo $on ? ' is-entitled' : ''; ?>">
                    <span class="mgk-cf-refund-when"><?php echo esc_html( $t['label'] ?? '' ); ?></span>
                    <span class="mgk-cf-refund-amt"><?php echo esc_html( ( $t['pct'] ?? '' ) . ' · ' . ( $t['value'] ?? '' ) ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="mgk-cf-refund-note">⚠ <?php echo esc_html( $rf['note'] ?? '' ); ?></p>
            <div class="mgk-cf-modal__actions">
                <button type="button" class="mgk-cf-btn-outline" data-mgk-modal-close>Keep booking</button>
                <button type="button" class="mgk-cf-btn-danger" data-mgk-confirm-cancel
                        data-event="cancel_refund_confirm">Confirm cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Reschedule modal (FR-BOOK-09 / BR-23) ─────────────────── -->
<div class="mgk-cf-modal" data-mgk-modal="reschedule" hidden>
    <div class="mgk-cf-modal__overlay" data-mgk-modal-close></div>
    <div class="mgk-cf-modal__panel" role="dialog" aria-modal="true" aria-label="Reschedule lesson">
        <div class="mgk-cf-modal__head">
            <span class="mgk-cf-modal__title">RESCHEDULE (FR-BOOK-09 / BR-23)</span>
            <button type="button" class="mgk-cf-modal__x" data-mgk-modal-close aria-label="Close">×</button>
        </div>
        <div class="mgk-cf-modal__body">
            <p class="mgk-cf-resched-note"><?php echo esc_html( $rs['note'] ?? '' ); ?></p>
            <p class="mgk-cf-resched-config"><?php echo esc_html( $rs['config_note'] ?? '' ); ?></p>
            <p class="mgk-cf-modal__sub">PICK A NEW TIME (SLOT RE-HELD <?php echo (int) ( $rs['reheld_min'] ?? 10 ); ?> MIN):</p>
            <div class="mgk-cf-resched-slots">
                <?php foreach ( (array) ( $rs['slots'] ?? [] ) as $i => $sl ) : ?>
                <button type="button" class="mgk-cf-resched-slot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                        data-mgk-resched-slot="<?php echo esc_attr( $sl['id'] ?? '' ); ?>"
                        <?php echo $i === 0 ? 'aria-pressed="true"' : ''; ?>><?php echo esc_html( $sl['label'] ?? '' ); ?></button>
                <?php endforeach; ?>
            </div>
            <button type="button" class="mgk-cf-btn-danger mgk-cf-btn-block" data-mgk-confirm-resched
                    data-event="reschedule_confirm">Confirm new time</button>
        </div>
    </div>
</div>

<!-- ── Add to calendar modal (FR-PAY-06) ─────────────────────── -->
<div class="mgk-cf-modal" data-mgk-modal="calendar" hidden>
    <div class="mgk-cf-modal__overlay" data-mgk-modal-close></div>
    <div class="mgk-cf-modal__panel" role="dialog" aria-modal="true" aria-label="Add to calendar">
        <div class="mgk-cf-modal__head">
            <span class="mgk-cf-modal__title">ADD TO CALENDAR</span>
            <button type="button" class="mgk-cf-modal__x" data-mgk-modal-close aria-label="Close">×</button>
        </div>
        <div class="mgk-cf-modal__body">
            <a class="mgk-cf-btn-danger mgk-cf-btn-block" href="<?php echo esc_url( $cal['ics'] ?? '#' ); ?>"
               data-event="calendar_add_click">⬇ Download .ics file</a>
            <p class="mgk-cf-modal__foot">.ICS INCLUDES ZOOM LINK + REMINDERS (FR-PAY-06). The same file is attached to your confirmation email.</p>
        </div>
    </div>
</div>
