<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];

/* ── Live mode: the real, submittable apply form (visitors) ──────────────── */
if ( ( $ctx['mode'] ?? '' ) === 'form' ) :
    $subjects = (array) ( $ctx['subjects'] ?? [] );
    $levels   = (array) ( $ctx['levels'] ?? [] );
    $old      = (array) ( $ctx['old'] ?? [] );
    $err      = (string) ( $ctx['error'] ?? '' );
    $old_subjects = array_map( 'intval', (array) ( $old['subjects'] ?? [] ) );
    $old_levels   = array_map( 'intval', (array) ( $old['levels'] ?? [] ) );
    $ov = function ( $key ) use ( $old ) { return esc_attr( (string) ( $old[ $key ] ?? '' ) ); };
?>
<section class="mgk-tutor-apply mgk-tutor-apply--form">
    <style>
        .mgk-tutor-apply--form{--mgk-line:#e2e2e6;max-width:760px;margin:0 auto;padding:clamp(20px,4vw,40px) clamp(16px,3vw,24px)}
        .mgk-apply-form__head h1{font-size:clamp(1.5rem,3.5vw,2.1rem);margin:0 0 .35em}
        .mgk-apply-form__head p{color:#555;margin:0 0 1.5em;line-height:1.55}
        .mgk-apply-form__err{background:#fde2e1;color:#b32d2e;border:1px solid #f3b6b4;border-radius:8px;padding:12px 14px;margin-bottom:18px}
        .mgk-apply-form__grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .mgk-apply-form__field{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
        .mgk-apply-form__field--full{grid-column:1/-1}
        .mgk-apply-form label{font-weight:600;font-size:.9rem}
        .mgk-apply-form input[type=text],.mgk-apply-form input[type=email],.mgk-apply-form input[type=tel],.mgk-apply-form input[type=number],.mgk-apply-form textarea{
            border:1px solid var(--mgk-line);border-radius:8px;padding:11px 12px;font:inherit;width:100%;box-sizing:border-box}
        .mgk-apply-form textarea{min-height:96px;resize:vertical}
        .mgk-apply-form__chips{display:flex;flex-wrap:wrap;gap:8px}
        .mgk-apply-form__chip{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--mgk-line);border-radius:999px;padding:7px 13px;cursor:pointer;font-size:.88rem;user-select:none}
        .mgk-apply-form__chip input{position:absolute;opacity:0;width:0;height:0}
        .mgk-apply-form__chip:has(input:checked){background:#111;color:#fff;border-color:#111}
        .mgk-apply-form__consent{display:flex;gap:10px;align-items:flex-start;background:#f7f7f8;border-radius:8px;padding:12px 14px;margin:6px 0 20px;font-size:.86rem;line-height:1.5}
        .mgk-apply-form__consent input{margin-top:3px}
        .mgk-apply-form__submit{background:#111;color:#fff;border:0;border-radius:8px;padding:14px 22px;font-size:1rem;font-weight:600;cursor:pointer;width:100%}
        .mgk-apply-form__foot{text-align:center;margin-top:16px;color:#555;font-size:.9rem}
        @media(max-width:600px){.mgk-apply-form__grid{grid-template-columns:1fr}}
    </style>
    <div class="mgk-apply-form__head">
        <h1><?php echo esc_html( $atts['form_title'] ?? 'Apply to teach' ); ?></h1>
        <p><?php echo esc_html( $atts['form_intro'] ?? '' ); ?></p>
    </div>

    <?php if ( $err ) : ?>
        <div class="mgk-apply-form__err"><?php echo esc_html( $err ); ?></div>
    <?php endif; ?>

    <form class="mgk-apply-form" method="post" action="<?php echo esc_url( $ctx['action'] ?? admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="mgk_tutor_apply_submit">
        <input type="hidden" name="mgk_tutor_apply_nonce" value="<?php echo esc_attr( $ctx['nonce'] ?? '' ); ?>">

        <div class="mgk-apply-form__grid">
            <div class="mgk-apply-form__field">
                <label for="mgk_app_name"><?php echo esc_html( $atts['form_name_label'] ?? 'Full name' ); ?></label>
                <input type="text" id="mgk_app_name" name="mgk_app_name" required value="<?php echo $ov( 'name' ); ?>">
            </div>
            <div class="mgk-apply-form__field">
                <label for="mgk_app_email"><?php echo esc_html( $atts['form_email_label'] ?? 'Email' ); ?></label>
                <input type="email" id="mgk_app_email" name="mgk_app_email" required autocomplete="email" value="<?php echo $ov( 'email' ); ?>">
            </div>
            <div class="mgk-apply-form__field">
                <label for="mgk_app_phone"><?php echo esc_html( $atts['form_phone_label'] ?? 'Phone' ); ?></label>
                <input type="tel" id="mgk_app_phone" name="mgk_app_phone" value="<?php echo $ov( 'phone' ); ?>">
            </div>
            <div class="mgk-apply-form__field">
                <label for="mgk_app_rate"><?php echo esc_html( $atts['form_rate_label'] ?? 'Indicative rate (S$/hr)' ); ?></label>
                <input type="number" id="mgk_app_rate" name="mgk_app_rate" min="0" step="1" value="<?php echo $ov( 'rate' ); ?>">
            </div>
        </div>

        <?php if ( $subjects ) : ?>
            <div class="mgk-apply-form__field mgk-apply-form__field--full">
                <label><?php echo esc_html( $atts['form_subjects_label'] ?? 'Subjects you can teach' ); ?></label>
                <div class="mgk-apply-form__chips">
                    <?php foreach ( $subjects as $s ) : ?>
                        <label class="mgk-apply-form__chip"><input type="checkbox" name="mgk_app_subjects[]" value="<?php echo esc_attr( (string) $s['id'] ); ?>" <?php checked( in_array( (int) $s['id'], $old_subjects, true ) ); ?>><?php echo esc_html( $s['name'] ); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $levels ) : ?>
            <div class="mgk-apply-form__field mgk-apply-form__field--full">
                <label><?php echo esc_html( $atts['form_levels_label'] ?? 'Levels' ); ?></label>
                <div class="mgk-apply-form__chips">
                    <?php foreach ( $levels as $l ) : ?>
                        <label class="mgk-apply-form__chip"><input type="checkbox" name="mgk_app_levels[]" value="<?php echo esc_attr( (string) $l['id'] ); ?>" <?php checked( in_array( (int) $l['id'], $old_levels, true ) ); ?>><?php echo esc_html( $l['name'] ); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mgk-apply-form__grid">
            <div class="mgk-apply-form__field">
                <label for="mgk_app_university"><?php echo esc_html( $atts['form_university_label'] ?? 'University / institution' ); ?></label>
                <input type="text" id="mgk_app_university" name="mgk_app_university" value="<?php echo $ov( 'university' ); ?>">
            </div>
            <div class="mgk-apply-form__field">
                <label for="mgk_app_degree"><?php echo esc_html( $atts['form_degree_label'] ?? 'Highest qualification' ); ?></label>
                <input type="text" id="mgk_app_degree" name="mgk_app_degree" value="<?php echo $ov( 'degree' ); ?>">
            </div>
            <div class="mgk-apply-form__field">
                <label for="mgk_app_year"><?php echo esc_html( $atts['form_year_label'] ?? 'Year' ); ?></label>
                <input type="text" id="mgk_app_year" name="mgk_app_year" value="<?php echo $ov( 'year' ); ?>">
            </div>
            <div class="mgk-apply-form__field">
                <label for="mgk_app_payout"><?php echo esc_html( $atts['form_payout_label'] ?? 'Payout — optional' ); ?></label>
                <input type="text" id="mgk_app_payout" name="mgk_app_payout" value="<?php echo $ov( 'payout' ); ?>">
            </div>
        </div>

        <div class="mgk-apply-form__field mgk-apply-form__field--full">
            <label for="mgk_app_experience"><?php echo esc_html( $atts['form_experience_label'] ?? 'Teaching experience & achievements' ); ?></label>
            <textarea id="mgk_app_experience" name="mgk_app_experience"><?php echo esc_textarea( (string) ( $old['experience'] ?? '' ) ); ?></textarea>
        </div>

        <label class="mgk-apply-form__consent">
            <input type="checkbox" name="mgk_app_consent" value="1" required>
            <span><?php echo esc_html( $atts['consent_text'] ?? '' ); ?></span>
        </label>

        <button type="submit" class="mgk-apply-form__submit"><?php echo esc_html( $atts['form_submit_label'] ?? 'Submit application →' ); ?></button>

        <p class="mgk-apply-form__foot"><?php echo esc_html( $atts['form_login_note'] ?? '' ); ?>
            <a href="<?php echo esc_url( function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' ) ); ?>"><?php echo esc_html( $atts['form_login_link'] ?? 'Sign in' ); ?></a>
        </p>
    </form>
</section>
<?php
    return;
endif;

/* ── Demo wizard (Elementor editor) ──────────────────────────────────────── */
$step = max( 1, min( 6, (int) ( $ctx['step'] ?? 3 ) ) );
$total = (int) ( $ctx['total_steps'] ?? 6 );
$steps = (array) ( $ctx['steps'] ?? [] );
$candidate = (array) ( $ctx['candidate'] ?? [] );
$education = (array) ( $ctx['education'] ?? [] );
$identity = (array) ( $ctx['identity'] ?? [] );

$step_url = function ( $next_step ) {
    return mgk_get_tutor_apply_url( $next_step );
};

$is_step = function ( $n ) use ( $step ) {
    return $step === (int) $n;
};

$step_title = function () use ( $step, $atts, $steps ) {
    if ( $step === 3 ) {
        return $atts['education_title'] ?? '';
    }
    $map = [
        1 => [ $atts['basic_title'] ?? '', $atts['basic_body'] ?? '' ],
        2 => [ $atts['subjects_title'] ?? '', $atts['subjects_body'] ?? '' ],
        4 => [ $atts['experience_title'] ?? '', $atts['experience_body'] ?? '' ],
        5 => [ $atts['payout_title'] ?? '', $atts['payout_body'] ?? '' ],
        6 => [ $atts['docs_title_step'] ?? '', $atts['docs_body_step'] ?? '' ],
    ];
    return $map[ $step ][0] ?? ( $steps[ $step ]['label'] ?? '' );
};

$step_body = function () use ( $step, $atts ) {
    $map = [
        1 => $atts['basic_body'] ?? '',
        2 => $atts['subjects_body'] ?? '',
        4 => $atts['experience_body'] ?? '',
        5 => $atts['payout_body'] ?? '',
        6 => $atts['docs_body_step'] ?? '',
    ];
    return $map[ $step ] ?? '';
};
?>
<section class="mgk-tutor-apply mgk-tutor-apply--step-<?php echo esc_attr( (string) $step ); ?>" data-mgk-tutor-apply data-step="<?php echo esc_attr( (string) $step ); ?>" data-verification-url="<?php echo esc_url( function_exists( 'mgk_get_tutor_verification_url' ) ? mgk_get_tutor_verification_url( 'default' ) : mgk_url( '/tutor/verification/' ) ); ?>">
    <div class="mgk-tutor-apply__shell">
        <?php if ( ! mgk_parent_bool( $atts['hide_topbar'] ?? '' ) ) : ?>
            <header class="mgk-tutor-apply-topbar">
                <strong><?php echo esc_html( $atts['topbar_logo'] ?? '' ); ?> · <?php echo esc_html( $atts['topbar_title'] ?? '' ); ?></strong>
                <span><?php echo esc_html( strtoupper( (string) ( $atts['step_prefix'] ?? 'STEP' ) ) . ' ' . $step . ' ' . strtoupper( (string) ( $atts['step_of_label'] ?? 'OF' ) ) . ' ' . $total . ' · ' . ( $atts['topbar_help'] ?? '' ) ); ?></span>
            </header>
        <?php endif; ?>

        <div class="mgk-tutor-apply-mobile-title">
            <h1><?php echo esc_html( $atts['mobile_title'] ?? '' ); ?> · <?php echo esc_html( strtolower( (string) ( $atts['step_prefix'] ?? 'step' ) ) ); ?> <?php echo esc_html( (string) $step ); ?> of <?php echo esc_html( (string) $total ); ?></h1>
            <span><?php echo esc_html( $atts['stepbar_tag'] ?? '' ); ?></span>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_stepbar'] ?? '' ) ) : ?>
            <nav class="mgk-tutor-apply-steps" aria-label="Tutor application steps">
                <span><?php echo esc_html( $atts['stepbar_tag'] ?? '' ); ?></span>
                <?php foreach ( $steps as $n => $item ) : ?>
                    <a class="<?php echo $is_step( $n ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( $step_url( $n ) ); ?>"><?php echo esc_html( $n . ' · ' . ( $item['short'] ?? '' ) ); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="mgk-tutor-apply-layout">
            <main class="mgk-tutor-apply-main">
                <?php if ( $step === 3 && ! mgk_parent_bool( $atts['hide_education'] ?? '' ) ) : ?>
                    <section class="mgk-tutor-apply-education">
                        <span class="mgk-tutor-apply-sec"><?php echo esc_html( $atts['sec_education'] ?? '' ); ?></span>
                        <h2 class="mgk-tutor-apply-desktop-heading"><?php echo esc_html( $atts['education_desktop_title'] ?? '' ); ?></h2>
                        <h2 class="mgk-tutor-apply-mobile-heading"><?php echo esc_html( $atts['education_title'] ?? '' ); ?></h2>
                        <p><?php echo esc_html( $atts['education_intro'] ?? '' ); ?></p>
                        <div class="mgk-tutor-apply-upload">
                            <strong class="mgk-tutor-apply-upload-desktop"><?php echo esc_html( $atts['degree_upload_title'] ?? '' ); ?></strong>
                            <strong class="mgk-tutor-apply-upload-mobile"><?php echo esc_html( $atts['degree_upload_mobile'] ?? '' ); ?></strong>
                            <span><?php echo esc_html( $atts['degree_upload_hint'] ?? '' ); ?></span>
                            <label class="mgk-tutor-apply-file-control">
                                <input type="file" data-mgk-tutor-upload="degree" accept=".pdf,.jpg,.jpeg,.png,image/jpeg,image/png,application/pdf">
                                <span><?php echo esc_html( $atts['choose_file_label'] ?? '' ); ?></span>
                            </label>
                            <small class="mgk-tutor-apply-file-status" data-mgk-file-status="degree"><?php echo esc_html( 'No file selected' ); ?></small>
                        </div>
                        <div class="mgk-tutor-apply-ocr">
                            <strong><?php echo esc_html( $atts['ocr_tag'] ?? '' ); ?></strong>
                            <div class="mgk-tutor-apply-ocr-grid">
                                <article><span><?php echo esc_html( $atts['university_label'] ?? '' ); ?></span><b><?php echo esc_html( $education['university'] ?? '' ); ?></b></article>
                                <article><span><?php echo esc_html( $atts['degree_label'] ?? '' ); ?></span><b><?php echo esc_html( $education['degree'] ?? '' ); ?></b></article>
                                <article><span><?php echo esc_html( $atts['year_label'] ?? '' ); ?></span><b><?php echo esc_html( $education['year'] ?? '' ); ?></b></article>
                            </div>
                            <p><?php echo esc_html( $education['match'] ?? '' ); ?></p>
                        </div>
                    </section>
                <?php else : ?>
                    <section class="mgk-tutor-apply-step-card">
                        <span class="mgk-tutor-apply-sec"><?php echo esc_html( 'STEP ' . $step . ' ' . ( $steps[ $step ]['short'] ?? '' ) ); ?></span>
                        <h2><?php echo esc_html( $step_title() ); ?></h2>
                        <p><?php echo esc_html( $step_body() ); ?></p>
                        <div class="mgk-tutor-apply-placeholder">
                            <span><?php echo esc_html( strtoupper( (string) ( $steps[ $step ]['label'] ?? '' ) ) ); ?></span>
                            <em><?php echo esc_html( 'Fields save automatically per step (FR-TUTOR-01).' ); ?></em>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( ( $step === 3 || $step === 6 ) && ! mgk_parent_bool( $atts['hide_docs_preview'] ?? '' ) ) : ?>
                    <section class="mgk-tutor-apply-docs">
                        <span class="mgk-tutor-apply-sec"><?php echo esc_html( $atts['sec_docs'] ?? '' ); ?></span>
                        <h2 class="mgk-tutor-apply-desktop-heading"><?php echo esc_html( $atts['docs_desktop_title'] ?? '' ); ?></h2>
                        <h2 class="mgk-tutor-apply-mobile-heading"><?php echo esc_html( $atts['docs_title'] ?? '' ); ?></h2>
                        <div class="mgk-tutor-apply-doc-upload">
                            <div data-mgk-doc-preview><?php echo esc_html( $atts['nric_scan_label'] ?? '' ); ?></div>
                            <label class="mgk-tutor-apply-doc-control">
                                <input type="file" data-mgk-tutor-upload="nric" accept=".pdf,.jpg,.jpeg,.png,image/jpeg,image/png,application/pdf">
                                <strong><?php echo esc_html( $atts['nric_upload_label'] ?? '' ); ?></strong>
                            </label>
                            <span><?php echo esc_html( $atts['nric_extracting'] ?? '' ); ?></span>
                            <small class="mgk-tutor-apply-file-status" data-mgk-file-status="nric"><?php echo esc_html( 'No document selected' ); ?></small>
                            <article><b><?php echo esc_html( $atts['name_label'] ?? '' ); ?></b> · <?php echo esc_html( $identity['name'] ?? '' ); ?></article>
                            <article><b><?php echo esc_html( $atts['dob_label'] ?? '' ); ?></b> · <?php echo esc_html( $identity['dob'] ?? '' ); ?></article>
                            <article><b><?php echo esc_html( $atts['nric_label'] ?? '' ); ?></b> · <?php echo esc_html( $identity['nric'] ?? '' ); ?></article>
                        </div>
                        <div class="mgk-tutor-apply-other-docs"><?php echo esc_html( $atts['other_docs_label'] ?? '' ); ?></div>
                    </section>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_consent'] ?? '' ) ) : ?>
                    <aside class="mgk-tutor-apply-consent"><?php echo esc_html( $atts['consent_text'] ?? '' ); ?></aside>
                <?php endif; ?>

                <?php if ( $step === 3 ) : ?>
                    <div class="mgk-tutor-apply-bank-warning"><?php echo esc_html( $atts['bank_warning'] ?? '' ); ?></div>
                <?php endif; ?>

                <footer class="mgk-tutor-apply-actions">
                    <a href="<?php echo esc_url( $step_url( max( 1, $step - 1 ) ) ); ?>"><?php echo esc_html( $atts['back_label'] ?? '' ); ?></a>
                    <a class="mgk-tutor-apply-primary" href="<?php echo esc_url( $step_url( min( 6, $step + 1 ) ) ); ?>" data-mgk-apply-continue><?php echo esc_html( $atts['continue_label'] ?? '' ); ?></a>
                    <span><?php echo esc_html( $atts['autosave_label'] ?? '' ); ?></span>
                </footer>
            </main>

            <?php if ( ! mgk_parent_bool( $atts['hide_preview'] ?? '' ) ) : ?>
                <aside class="mgk-tutor-apply-preview">
                    <h2><?php echo esc_html( $atts['preview_title'] ?? '' ); ?></h2>
                    <div class="mgk-tutor-apply-preview-card">
                        <div><?php echo esc_html( $candidate['photo'] ?? '' ); ?></div>
                        <strong><?php echo esc_html( $candidate['name'] ?? '' ); ?></strong>
                        <span><?php echo esc_html( $candidate['meta'] ?? '' ); ?></span>
                        <em><?php echo esc_html( $candidate['subjects'] ?? '' ); ?></em>
                    </div>
                    <p><?php echo esc_html( $atts['preview_completeness'] ?? '' ); ?></p>
                    <div class="mgk-tutor-apply-progress"><span></span></div>
                    <small><?php echo esc_html( ( $candidate['completion'] ?? '60%' ) . ' · FINISH STEPS 4-6 TO SUBMIT' ); ?></small>
                    <footer><?php echo esc_html( $atts['preview_note'] ?? '' ); ?></footer>
                </aside>
            <?php endif; ?>
        </div>

        <div class="mgk-tutor-apply-mobile-autosave"><?php echo esc_html( $atts['mobile_autosave_label'] ?? '' ); ?></div>
        <footer class="mgk-tutor-apply-footer"><?php echo esc_html( $atts['footer_text'] ?? '' ); ?></footer>
    </div>
</section>
