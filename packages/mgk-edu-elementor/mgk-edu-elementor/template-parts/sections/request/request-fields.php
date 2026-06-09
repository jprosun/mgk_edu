<?php
/**
 * S07 — Request Match · Form fields (split widget).
 *
 * The complete, SINGLE <form> (5 fields + phone + PDPA + submit). Kept as one
 * form so it submits in one POST — only the surrounding BLOCKS are split into
 * separate widgets, never the form itself.
 *
 * Presentation only. ALL options come from the locked core (mgk_request_enums).
 * SAFE marketing copy via $args. No data/logic is Elementor-editable.
 *
 * Standalone widget [mgk_request_fields] OR included by request-form.php.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$enums  = function_exists( 'mgk_request_enums' ) ? mgk_request_enums() : [];
$levels = $enums['levels']   ?? [];
$subs   = $enums['subjects'] ?? [];
$days   = $enums['days']     ?? [];
$times  = $enums['times']    ?? [];
$budget = $enums['budget']   ?? [ 'min' => 25, 'max' => 200, 'default_min' => 40, 'default_max' => 90, 'step' => 5 ];
$notemax = $enums['note_max'] ?? 500;
$sla     = $enums['sla_hours'] ?? 6;

$a = wp_parse_args( (array) ( $args ?? [] ), [
    'form_heading' => 'Tell us what you need',
    'form_note'    => '(TAKES ~60S)',
    'submit_label' => 'Get My Matches →',
    'submit_note'  => 'NO ACCOUNT CREATED · YOU’LL HEAR BACK WITHIN ' . $sla . ' HOURS',
    'pdpa_url'     => home_url( '/privacy-policy/' ),
] );

$level_enum_text = 'ENUM: ' . implode( ' · ', array_map( function ( $o ) { return $o['label']; }, $levels ) );
$subj_enum_text  = 'ENUM: ' . implode( ' · ', array_map( function ( $o ) { return $o['label']; }, $subs ) );

$default_days = [ 'wed', 'sat' ];
$default_from = '16:00';
$default_to   = '21:00';

$err = isset( $_GET['mgk_err'] ) ? sanitize_key( wp_unslash( $_GET['mgk_err'] ) ) : '';
$err_msg = '';
if ( $err === 'invalid' ) $err_msg = 'Please complete the highlighted fields and try again.';
elseif ( $err === 'expired' ) $err_msg = 'Your session expired. Please review and submit again.';
elseif ( $err === 'server' ) $err_msg = 'Something went wrong. Please try again or contact us.';
?>
<div class="mgk-rq mgk-rq--fields-only" data-mgk-request data-sla-hours="<?php echo esc_attr( $sla ); ?>"
     data-budget-min="<?php echo esc_attr( $budget['min'] ); ?>"
     data-budget-max="<?php echo esc_attr( $budget['max'] ); ?>"
     data-budget-step="<?php echo esc_attr( $budget['step'] ); ?>"
     data-note-max="<?php echo esc_attr( $notemax ); ?>">

    <?php if ( $err_msg ) : ?>
    <div class="mgk-rq-banner" role="alert"><?php echo esc_html( $err_msg ); ?></div>
    <?php endif; ?>

    <!-- Form section heading -->
    <div class="mgk-rq-formhead">
        <h2><?php echo esc_html( $a['form_heading'] ); ?> <small><?php echo esc_html( $a['form_note'] ); ?></small></h2>
        <div class="mgk-rq-underline" aria-hidden="true"><span></span></div>
    </div>

    <form class="mgk-rq-form" id="js-mgk-request-form" method="post"
          action="<?php echo esc_url( home_url( '/request-match/' ) ); ?>" novalidate
          data-rest-url="<?php echo esc_attr( rest_url( 'mgk/v1/request-match' ) ); ?>"
          data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
          data-confirm-url="<?php echo esc_attr( home_url( '/request-match/' ) ); ?>">

        <input type="hidden" name="mgk_action" value="request_match">
        <?php wp_nonce_field( 'mgk_request_match', 'mgk_request_nonce' ); ?>
        <input type="hidden" name="source_utm" id="rq_source_utm" value="">

        <!-- Field 1 — Child level -->
        <div class="mgk-rq-field" data-field="child_level">
            <label class="mgk-rq-label" for="rq_level">1 · CHILD'S LEVEL <span class="mgk-rq-req">*</span></label>
            <div class="mgk-rq-select">
                <select id="rq_level" name="child_level" required aria-required="true">
                    <option value="">SELECT LEVEL...</option>
                    <?php foreach ( $levels as $o ) : ?>
                    <option value="<?php echo esc_attr( $o['value'] ); ?>"><?php echo esc_html( $o['label'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="mgk-rq-enum"><?php echo esc_html( $level_enum_text ); ?></p>
            <p class="mgk-rq-err" data-err="child_level" aria-live="polite"></p>
        </div>

        <!-- Field 2 — Subject -->
        <div class="mgk-rq-field" data-field="subject">
            <label class="mgk-rq-label" for="rq_subject">2 · SUBJECT <span class="mgk-rq-req">*</span></label>
            <div class="mgk-rq-select">
                <select id="rq_subject" name="subject" required aria-required="true">
                    <option value="">SELECT SUBJECT...</option>
                    <?php foreach ( $subs as $o ) : ?>
                    <option value="<?php echo esc_attr( $o['value'] ); ?>"><?php echo esc_html( $o['full'] ?? $o['label'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="mgk-rq-enum"><?php echo esc_html( $subj_enum_text ); ?></p>
            <p class="mgk-rq-err" data-err="subject" aria-live="polite"></p>
        </div>

        <!-- Field 3 — Preferred schedule -->
        <div class="mgk-rq-field" data-field="preferred_days">
            <label class="mgk-rq-label">3 · PREFERRED SCHEDULE <span class="mgk-rq-req">*</span>
                <span class="mgk-rq-multi">(MULTI-SELECT)</span></label>
            <div class="mgk-rq-chips" role="group" aria-label="Preferred days">
                <?php foreach ( $days as $o ) :
                    $on = in_array( $o['value'], $default_days, true ); ?>
                <button type="button" class="mgk-rq-chip<?php echo $on ? ' is-on' : ''; ?>"
                        data-day="<?php echo esc_attr( $o['value'] ); ?>"
                        aria-pressed="<?php echo $on ? 'true' : 'false'; ?>"><?php echo esc_html( $o['label'] ); ?></button>
                <input type="hidden" name="preferred_days[]" value="<?php echo esc_attr( $o['value'] ); ?>"<?php echo $on ? '' : ' disabled'; ?> data-day-input="<?php echo esc_attr( $o['value'] ); ?>">
                <?php endforeach; ?>
            </div>
            <div class="mgk-rq-times">
                <div class="mgk-rq-select">
                    <span class="mgk-rq-time-pre">FROM</span>
                    <select name="time_from" id="rq_time_from" aria-label="From time">
                        <?php foreach ( $times as $t ) : ?>
                        <option value="<?php echo esc_attr( $t ); ?>"<?php selected( $t, $default_from ); ?>><?php echo esc_html( mgk_request_time_label( $t ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mgk-rq-select">
                    <span class="mgk-rq-time-pre">TO</span>
                    <select name="time_to" id="rq_time_to" aria-label="To time">
                        <?php foreach ( $times as $t ) : ?>
                        <option value="<?php echo esc_attr( $t ); ?>"<?php selected( $t, $default_to ); ?>><?php echo esc_html( mgk_request_time_label( $t ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <p class="mgk-rq-err" data-err="preferred_days" aria-live="polite"></p>
            <p class="mgk-rq-err" data-err="time_to" aria-live="polite"></p>
        </div>

        <!-- Field 4 — Budget (optional) -->
        <div class="mgk-rq-field" data-field="budget">
            <label class="mgk-rq-label">4 · BUDGET RANGE <span class="mgk-rq-opt">(OPTIONAL)</span></label>
            <div class="mgk-rq-budget" data-budget>
                <div class="mgk-rq-budget-track">
                    <span class="mgk-rq-budget-fill" data-budget-fill></span>
                    <input type="range" class="mgk-rq-budget-input mgk-rq-budget-input--min" data-budget-min-input
                           min="<?php echo esc_attr( $budget['min'] ); ?>" max="<?php echo esc_attr( $budget['max'] ); ?>"
                           step="<?php echo esc_attr( $budget['step'] ); ?>" value="<?php echo esc_attr( $budget['default_min'] ); ?>"
                           aria-label="Minimum budget per hour">
                    <input type="range" class="mgk-rq-budget-input mgk-rq-budget-input--max" data-budget-max-input
                           min="<?php echo esc_attr( $budget['min'] ); ?>" max="<?php echo esc_attr( $budget['max'] ); ?>"
                           step="<?php echo esc_attr( $budget['step'] ); ?>" value="<?php echo esc_attr( $budget['default_max'] ); ?>"
                           aria-label="Maximum budget per hour">
                </div>
                <div class="mgk-rq-budget-scale">
                    <span>$<?php echo esc_html( $budget['min'] ); ?></span>
                    <span class="mgk-rq-budget-value" data-budget-label>$<?php echo esc_html( $budget['default_min'] ); ?> - $<?php echo esc_html( $budget['default_max'] ); ?> / hr</span>
                    <span>$<?php echo esc_html( $budget['max'] ); ?></span>
                </div>
                <input type="hidden" name="budget_min" data-budget-min-field value="">
                <input type="hidden" name="budget_max" data-budget-max-field value="">
            </div>
            <p class="mgk-rq-err" data-err="budget" aria-live="polite"></p>
        </div>

        <!-- Field 5 — Note (optional) -->
        <div class="mgk-rq-field" data-field="note">
            <label class="mgk-rq-label" for="rq_note">5 · NOTE TO US <span class="mgk-rq-opt">(OPTIONAL)</span></label>
            <textarea id="rq_note" name="note" class="mgk-rq-note" rows="3" maxlength="<?php echo esc_attr( $notemax ); ?>"
                      placeholder='E.G. "PSLE IN OCT, NEEDS HELP WITH WORD PROBLEMS"' data-note-input></textarea>
            <p class="mgk-rq-counter"><span data-note-count>0</span> / <?php echo esc_html( $notemax ); ?></p>
            <p class="mgk-rq-err" data-err="note" aria-live="polite"></p>
        </div>

        <hr class="mgk-rq-sep">

        <!-- Phone -->
        <div class="mgk-rq-field" data-field="email">
            <label class="mgk-rq-label" for="rq_email">EMAIL <span class="mgk-rq-req">*</span></label>
            <input type="email" id="rq_email" name="email" class="mgk-rq-input mgk-rq-email-input"
                   autocomplete="email" placeholder="you@example.com" required aria-required="true">
            <div class="mgk-rq-phonehelp" role="note">
                We’ll email you the tutor matches and a link to continue — no account needed.
            </div>
            <p class="mgk-rq-err" data-err="email" aria-live="polite"></p>
        </div>

        <!-- PDPA consent -->
        <div class="mgk-rq-field" data-field="pdpa_consent">
            <label class="mgk-rq-consent">
                <input type="checkbox" name="pdpa_consent" id="rq_consent" value="yes" checked required aria-required="true">
                <span>I agree to receive tutor proposals &amp; updates by email/SMS, and to the processing of my data per the
                    <a href="<?php echo esc_url( $a['pdpa_url'] ); ?>" target="_blank" rel="noopener">PDPA Notice</a>.</span>
            </label>
            <p class="mgk-rq-err" data-err="pdpa_consent" aria-live="polite"></p>
        </div>

        <!-- Submit -->
        <button type="submit" class="mgk-rq-submit" id="js-rq-submit"
                data-event="cta_click" data-screen="request_match">
            <span class="mgk-rq-submit-label"><?php echo esc_html( $a['submit_label'] ); ?></span>
            <span class="mgk-rq-submit-loading" hidden>Submitting…</span>
        </button>
        <p class="mgk-rq-subnote"><?php echo esc_html( $a['submit_note'] ); ?></p>
    </form>
</div>
