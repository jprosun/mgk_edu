<?php
/**
 * S07 — Request Match · PER-FIELD renderer (split widgets).
 * =========================================================
 * Each form field can be dropped as its OWN Elementor widget so owners follow
 * & restyle the flow field-by-field. There is NO real <form> wrapping these
 * (Elementor boxes each widget in its own <div>, which would truncate a form);
 * instead each field renders a bare .mgk-rq-field, and the Submit widget's JS
 * collects every .mgk-rq input within the same Elementor Section and POSTs to
 * the locked REST endpoint. The server (inc/mgk-forms.php) still re-validates
 * and owns all data/lead/SLA/mask logic.
 *
 * 3-layer rule: owners may Show/Hide a field + edit its static label / helper
 * copy + Style — but the OPTIONS (enums), validation, SG-phone rule, PDPA-
 * required, lead/SLA logic and endpoint are LOCKED. Required fields
 * (level/subject/phone/pdpa) cannot be hidden (the form would be invalid).
 *
 * Public API:
 *   mgk_request_field_html( string $type, array $args = [] ) : string
 *   mgk_request_field_types() : array   // type => label (for widget registry)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Field types and whether they are required (required ⇒ cannot be hidden). */
function mgk_request_field_types() {
    return [
        'child_name' => [ 'label' => 'Child Name',      'required' => true  ],
        'level'    => [ 'label' => 'Child Level',       'required' => true  ],
        'subject'  => [ 'label' => 'Subject',           'required' => true  ],
        'schedule' => [ 'label' => 'Preferred Schedule','required' => true  ],
        'budget'   => [ 'label' => 'Budget Range',      'required' => false ],
        'note'     => [ 'label' => 'Note',              'required' => false ],
        'email'    => [ 'label' => 'Email',             'required' => true  ],
        'pdpa'     => [ 'label' => 'PDPA Consent',      'required' => true  ],
    ];
}

/**
 * Render one field's HTML (a single .mgk-rq-field block, wrapped in .mgk-rq so
 * the design tokens + width apply when it stands alone).
 *
 * SAFE $args (Elementor-editable): hide (yes/''), label, helper, opt note,
 * placeholder — copy + visibility only. Options come from the locked enums.
 */
function mgk_request_field_html( $type, $args = [] ) {
    $type  = sanitize_key( $type );
    $types = mgk_request_field_types();
    if ( ! isset( $types[ $type ] ) ) return '';

    $required = ! empty( $types[ $type ]['required'] );
    $a = (array) $args;

    // Hide is opt-IN ('yes'); default shows. Required fields can NEVER be hidden
    // (the form would be invalid) — the toggle is simply ignored for them.
    $hidden = isset( $a['hide'] ) && ( $a['hide'] === 'yes' || $a['hide'] === true );
    if ( ! $required && $hidden ) return '';

    $enums  = function_exists( 'mgk_request_enums' ) ? mgk_request_enums() : [];
    $levels = $enums['levels']   ?? [];
    $subs   = $enums['subjects'] ?? [];
    $days   = $enums['days']     ?? [];
    $times  = $enums['times']    ?? [];
    $budget = $enums['budget']   ?? [ 'min' => 25, 'max' => 200, 'default_min' => 40, 'default_max' => 90, 'step' => 5 ];
    $notemax = $enums['note_max'] ?? 500;

    $default_days = [ 'wed', 'sat' ];
    $default_from = '16:00';
    $default_to   = '21:00';

    // Required-marker / optional-marker helpers.
    $req  = '<span class="mgk-rq-req">*</span>';
    $opt  = '<span class="mgk-rq-opt">(OPTIONAL)</span>';

    // Decorative toggles (opt-IN 'yes' to hide). These hide presentation only —
    // never the data field itself. Required fields stay submittable regardless.
    $hide_helper = isset( $a['hide_helper'] ) && $a['hide_helper'] === 'yes';
    $hide_multi  = isset( $a['hide_multi'] )  && $a['hide_multi']  === 'yes';

    ob_start();
    echo '<div class="mgk-rq mgk-rq--field-only">';

    switch ( $type ) {

        case 'child_name':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : "1 · CHILD'S NAME";
            $ph    = isset( $a['placeholder'] ) && $a['placeholder'] !== '' ? $a['placeholder'] : 'E.G. EMMA TAN';
            ?>
            <div class="mgk-rq-field" data-field="child_name">
                <label class="mgk-rq-label" for="rq_child_name"><?php echo esc_html( $label ); ?> <?php echo $req; // phpcs:ignore ?></label>
                <input type="text" id="rq_child_name" name="child_name" class="mgk-rq-input mgk-rq-child-name-input"
                       autocomplete="off" maxlength="120" placeholder="<?php echo esc_attr( $ph ); ?>" required aria-required="true">
                <?php if ( ! $hide_helper ) : ?><p class="mgk-rq-enum"><?php echo esc_html( isset( $a['helper'] ) && $a['helper'] !== '' ? $a['helper'] : 'First name is fine — used on your dashboard + lesson logs.' ); ?></p><?php endif; ?>
                <p class="mgk-rq-err" data-err="child_name" aria-live="polite"></p>
            </div>
            <?php
            break;

        case 'level':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : "1 · CHILD'S LEVEL";
            $level_enum_text = isset( $a['helper'] ) && $a['helper'] !== ''
                ? $a['helper']
                : 'ENUM: ' . implode( ' · ', array_map( function ( $o ) { return $o['label']; }, $levels ) );
            ?>
            <div class="mgk-rq-field" data-field="child_level">
                <label class="mgk-rq-label" for="rq_level"><?php echo esc_html( $label ); ?> <?php echo $req; // phpcs:ignore ?></label>
                <div class="mgk-rq-select">
                    <select id="rq_level" name="child_level" required aria-required="true">
                        <option value=""><?php echo esc_html( isset( $a['placeholder'] ) && $a['placeholder'] !== '' ? $a['placeholder'] : 'SELECT LEVEL...' ); ?></option>
                        <?php foreach ( $levels as $o ) : ?>
                        <option value="<?php echo esc_attr( $o['value'] ); ?>"><?php echo esc_html( $o['label'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ( ! $hide_helper ) : ?><p class="mgk-rq-enum"><?php echo esc_html( $level_enum_text ); ?></p><?php endif; ?>
                <p class="mgk-rq-err" data-err="child_level" aria-live="polite"></p>
            </div>
            <?php
            break;

        case 'subject':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : '2 · SUBJECT';
            $subj_enum_text = isset( $a['helper'] ) && $a['helper'] !== ''
                ? $a['helper']
                : 'ENUM: ' . implode( ' · ', array_map( function ( $o ) { return $o['label']; }, $subs ) );
            ?>
            <div class="mgk-rq-field" data-field="subject">
                <label class="mgk-rq-label" for="rq_subject"><?php echo esc_html( $label ); ?> <?php echo $req; // phpcs:ignore ?></label>
                <div class="mgk-rq-select">
                    <select id="rq_subject" name="subject" required aria-required="true">
                        <option value=""><?php echo esc_html( isset( $a['placeholder'] ) && $a['placeholder'] !== '' ? $a['placeholder'] : 'SELECT SUBJECT...' ); ?></option>
                        <?php foreach ( $subs as $o ) : ?>
                        <option value="<?php echo esc_attr( $o['value'] ); ?>"><?php echo esc_html( $o['full'] ?? $o['label'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ( ! $hide_helper ) : ?><p class="mgk-rq-enum"><?php echo esc_html( $subj_enum_text ); ?></p><?php endif; ?>
                <p class="mgk-rq-err" data-err="subject" aria-live="polite"></p>
            </div>
            <?php
            break;

        case 'schedule':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : '3 · PREFERRED SCHEDULE';
            $multi = isset( $a['multi_note'] ) && $a['multi_note'] !== '' ? $a['multi_note'] : '(MULTI-SELECT)';
            ?>
            <div class="mgk-rq-field" data-field="preferred_days">
                <label class="mgk-rq-label"><?php echo esc_html( $label ); ?> <?php echo $req; // phpcs:ignore ?>
                    <?php if ( ! $hide_multi ) : ?><span class="mgk-rq-multi"><?php echo esc_html( $multi ); ?></span><?php endif; ?></label>
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
            <?php
            break;

        case 'budget':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : '4 · BUDGET RANGE';
            ?>
            <div class="mgk-rq-field" data-field="budget" data-budget-min="<?php echo esc_attr( $budget['min'] ); ?>"
                 data-budget-max="<?php echo esc_attr( $budget['max'] ); ?>" data-budget-step="<?php echo esc_attr( $budget['step'] ); ?>">
                <label class="mgk-rq-label"><?php echo esc_html( $label ); ?> <?php echo $opt; // phpcs:ignore ?></label>
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
            <?php
            break;

        case 'note':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : '5 · NOTE TO US';
            $ph    = isset( $a['placeholder'] ) && $a['placeholder'] !== '' ? $a['placeholder'] : 'E.G. "PSLE IN OCT, NEEDS HELP WITH WORD PROBLEMS"';
            ?>
            <div class="mgk-rq-field" data-field="note" data-note-max="<?php echo esc_attr( $notemax ); ?>">
                <label class="mgk-rq-label" for="rq_note"><?php echo esc_html( $label ); ?> <?php echo $opt; // phpcs:ignore ?></label>
                <textarea id="rq_note" name="note" class="mgk-rq-note" rows="3" maxlength="<?php echo esc_attr( $notemax ); ?>"
                          placeholder="<?php echo esc_attr( $ph ); ?>" data-note-input></textarea>
                <p class="mgk-rq-counter"><span data-note-count>0</span> / <?php echo esc_html( $notemax ); ?></p>
                <p class="mgk-rq-err" data-err="note" aria-live="polite"></p>
            </div>
            <?php
            break;

        case 'email':
            $label = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : 'EMAIL';
            $ph    = isset( $a['placeholder'] ) && $a['placeholder'] !== '' ? $a['placeholder'] : 'you@example.com';
            $help  = isset( $a['helper'] ) && $a['helper'] !== ''
                ? $a['helper']
                : 'We’ll email you the tutor matches and a link to continue — no account needed.';
            // Option 3: a signed-in parent is ADDING a child — don't re-ask the email.
            // Prefill + lock to their account email; the new child links to them.
            $signed_in = function_exists( 'mgk_is_parent_user' ) && mgk_is_parent_user();
            $me        = $signed_in ? wp_get_current_user() : null;
            $my_email  = $me ? $me->user_email : '';
            $my_name   = $me ? ( get_user_meta( $me->ID, 'mgk_parent_full_name', true ) ?: $me->display_name ) : '';
            ?>
            <div class="mgk-rq-field" data-field="email">
                <label class="mgk-rq-label" for="rq_email"><?php echo esc_html( $label ); ?> <?php echo $req; // phpcs:ignore ?></label>
                <input type="email" id="rq_email" name="email" class="mgk-rq-input mgk-rq-email-input"
                       autocomplete="email" placeholder="<?php echo esc_attr( $ph ); ?>" required aria-required="true"
                       value="<?php echo esc_attr( $my_email ); ?>"<?php echo $signed_in ? ' readonly' : ''; ?>>
                <?php if ( $signed_in ) : ?>
                    <div class="mgk-rq-phonehelp" role="note">Signed in as <strong><?php echo esc_html( $my_name ?: $my_email ); ?></strong> — this child will be added to your account.</div>
                <?php elseif ( ! $hide_helper ) : ?>
                    <div class="mgk-rq-phonehelp" role="note"><?php echo esc_html( $help ); ?></div>
                <?php endif; ?>
                <p class="mgk-rq-err" data-err="email" aria-live="polite"></p>
            </div>
            <?php
            break;

        case 'pdpa':
            $text = isset( $a['label'] ) && $a['label'] !== ''
                ? $a['label']
                : 'I agree to receive tutor proposals & updates by email/SMS, and to the processing of my data per the';
            $link = isset( $a['link_label'] ) && $a['link_label'] !== '' ? $a['link_label'] : 'PDPA Notice';
            $url  = isset( $a['pdpa_url'] ) && $a['pdpa_url'] !== '' ? $a['pdpa_url'] : home_url( '/privacy-policy/' );
            ?>
            <div class="mgk-rq-field" data-field="pdpa_consent">
                <label class="mgk-rq-consent">
                    <input type="checkbox" name="pdpa_consent" id="rq_consent" value="yes" checked required aria-required="true">
                    <span><?php echo esc_html( $text ); ?>
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $link ); ?></a>.</span>
                </label>
                <p class="mgk-rq-err" data-err="pdpa_consent" aria-live="polite"></p>
            </div>
            <?php
            break;
    }

    echo '</div>';
    return ob_get_clean();
}
