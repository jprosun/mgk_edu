<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
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
