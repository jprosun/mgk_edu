<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$lesson = (array) ( $ctx['lesson'] ?? [] );
$state  = (string) ( $ctx['state'] ?? 'demo' );

$hidden = function ( $key ) use ( $atts ) {
    return function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' );
};
$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-lesson-log-sec">' . esc_html( $label ) . '</span>';
    }
};

/* ── Gated (logged-out) ──────────────────────────────────────────────────── */
if ( $state === 'gated' ) {
    $login = function_exists( 'mgk_get_tutor_login_url' ) ? mgk_get_tutor_login_url() : mgk_url( '/tutor/login/' );
    echo function_exists( 'mgk_auth_styles' ) ? mgk_auth_styles() : ''; // phpcs:ignore
    echo '<div class="mgk-auth"><div class="mgk-auth__card"><h1>Tutor sign in</h1>';
    echo '<p>Sign in to log a lesson.</p>';
    echo '<a class="mgk-auth__btn" style="text-decoration:none;text-align:center" href="' . esc_url( $login ) . '">Sign in →</a>';
    echo '</div></div>';
    return;
}

/* ── No / not-owned booking ──────────────────────────────────────────────── */
if ( $state === 'no-booking' ) {
    echo '<section class="mgk-lesson-log"><div class="mgk-lesson-log__shell" style="max-width:560px;margin:6vh auto;text-align:center">';
    echo '<h1>' . esc_html( $lesson['title'] ?? 'Pick a lesson to log' ) . '</h1>';
    echo '<p>' . esc_html( $lesson['meta'] ?? '' ) . '</p>';
    echo '<a class="mgk-btn mgk-btn-accent" href="' . esc_url( mgk_get_tutor_dashboard_url() ) . '">← Back to dashboard</a>';
    echo '</div></section>';
    return;
}

$is_form   = ( $state === 'log' );
$form_open = $is_form;
$form_action = admin_url( 'admin-post.php' );
$booking_id = (int) ( $ctx['booking_id'] ?? 0 );
?>
<section class="mgk-lesson-log" data-mgk-tutor-lesson-log>
    <div class="mgk-lesson-log__shell">
        <?php if ( ! $hidden( 'topbar' ) ) : ?>
            <header class="mgk-lesson-log-topbar">
                <a href="<?php echo esc_url( mgk_get_tutor_dashboard_url() ); ?>"><?php echo esc_html( $atts['back_label'] ?? '' ); ?></a>
                <strong><?php echo esc_html( $atts['entry_label'] ?? '' ); ?></strong>
                <span><?php echo esc_html( $state === 'logged' ? '• Logged' : '' ); ?></span>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'header' ) ) : ?>
            <header class="mgk-lesson-log-head">
                <?php $section_label( $atts['sec_lesson'] ?? '' ); ?>
                <div>
                    <h1><?php echo esc_html( $lesson['title'] ?? '' ); ?></h1>
                    <p><?php echo esc_html( $lesson['meta'] ?? '' ); ?></p>
                </div>
                <?php if ( ! empty( $lesson['package'] ) ) : ?>
                <aside>
                    <span><?php echo esc_html( $atts['package_label'] ?? '' ); ?></span>
                    <strong><?php echo esc_html( $lesson['package'] ); ?></strong>
                </aside>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <?php if ( $state === 'logged' ) : ?>
            <div class="mgk-lesson-log-card" style="text-align:center;padding:32px">
                <h2>✓ This lesson is already logged</h2>
                <p>The parent has been notified. You can’t submit a second log for the same lesson.</p>
                <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_get_tutor_dashboard_url() ); ?>">← Back to dashboard</a>
            </div>
        <?php else : ?>

        <?php if ( $form_open ) : ?>
        <form class="mgk-lesson-log-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( $form_action ); ?>">
            <input type="hidden" name="action" value="mgk_tutor_log_lesson">
            <input type="hidden" name="mgk_booking_id" value="<?php echo esc_attr( (string) $booking_id ); ?>">
            <?php wp_nonce_field( 'mgk_tutor_log_lesson_' . $booking_id, 'mgk_tutor_log_nonce' ); ?>
        <?php endif; ?>

        <div class="mgk-lesson-log-layout">
            <main class="mgk-lesson-log-main">
                <?php if ( ! $hidden( 'attendance' ) ) : ?>
                    <section class="mgk-lesson-log-card mgk-lesson-log-attendance">
                        <?php $section_label( $atts['sec_attendance'] ?? '' ); ?>
                        <h2><?php echo esc_html( $atts['attendance_title'] ?? '' ); ?></h2>
                        <div class="mgk-lesson-log-attendance-grid">
                            <?php foreach ( (array) ( $ctx['attendance'] ?? [] ) as $i => $option ) : ?>
                                <?php if ( $is_form ) : ?>
                                    <label class="<?php echo ! empty( $option['active'] ) ? 'is-active' : ''; ?>" style="cursor:pointer">
                                        <input type="radio" name="mgk_attendance" value="<?php echo esc_attr( $option['value'] ?? 'ATTENDED' ); ?>" <?php checked( ! empty( $option['active'] ) ); ?> style="margin-right:6px">
                                        <?php echo esc_html( $option['label'] ?? '' ); ?>
                                    </label>
                                <?php else : ?>
                                    <button type="button" class="<?php echo ! empty( $option['active'] ) ? 'is-active' : ''; ?>"><?php echo esc_html( $option['label'] ?? '' ); ?></button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php if ( $is_form && ! empty( $ctx['engagement'] ) ) : ?>
                            <h2 style="margin-top:18px">Engagement</h2>
                            <div class="mgk-lesson-log-attendance-grid">
                                <?php foreach ( (array) $ctx['engagement'] as $eng_key => $eng_score ) :
                                    $eng_label = function_exists( 'mgk_engagement_label' ) ? mgk_engagement_label( $eng_key ) : $eng_key; ?>
                                    <label style="cursor:pointer">
                                        <input type="radio" name="mgk_engagement" value="<?php echo esc_attr( $eng_key ); ?>" <?php checked( $eng_key === 'GOOD' ); ?> style="margin-right:6px">
                                        <?php echo esc_html( $eng_label ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ( ! $hidden( 'notes' ) ) : ?>
                    <section class="mgk-lesson-log-card mgk-lesson-log-notes">
                        <?php $section_label( $atts['sec_notes'] ?? '' ); ?>
                        <header>
                            <h2><?php echo esc_html( $atts['note_title'] ?? '' ); ?></h2>
                        </header>
                        <?php if ( $is_form ) : ?>
                            <div class="mgk-lesson-log-field-grid">
                                <article>
                                    <label for="mgk_topic"><span>TOPIC COVERED</span></label>
                                    <textarea id="mgk_topic" name="mgk_topic" rows="2" placeholder="What did you cover this lesson?"></textarea>
                                </article>
                                <article>
                                    <label for="mgk_homework"><span>HOMEWORK SET</span></label>
                                    <textarea id="mgk_homework" name="mgk_homework" rows="2" placeholder="Any homework or practice?"></textarea>
                                </article>
                                <article>
                                    <label for="mgk_comment"><span>NOTE TO PARENT</span></label>
                                    <textarea id="mgk_comment" name="mgk_comment" rows="3" placeholder="How is the student doing? Next focus?"></textarea>
                                </article>
                            </div>
                        <?php else : ?>
                            <div class="mgk-lesson-log-field-grid">
                                <?php foreach ( (array) ( $ctx['note_fields'] ?? [] ) as $field ) : ?>
                                    <article>
                                        <span><?php echo esc_html( $field['label'] ?? '' ); ?></span>
                                        <strong><?php echo esc_html( $field['value'] ?? '' ); ?></strong>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <p><?php echo esc_html( $atts['note_footer'] ?? '' ); ?></p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </main>

            <aside class="mgk-lesson-log-side">
                <?php if ( ! $hidden( 'photos' ) ) : ?>
                    <section class="mgk-lesson-log-card mgk-lesson-log-photos">
                        <?php $section_label( $atts['sec_photos'] ?? '' ); ?>
                        <h2><?php echo esc_html( $atts['photos_title'] ?? '' ); ?></h2>
                        <div class="mgk-lesson-log-photo-grid">
                            <label>
                                <input type="file" name="<?php echo $is_form ? 'mgk_lesson_photo' : ''; ?>" accept="image/*">
                                <span><?php echo esc_html( $atts['photo_add_label'] ?? '+' ); ?></span>
                            </label>
                        </div>
                        <p><?php echo esc_html( $atts['photos_note'] ?? '' ); ?></p>
                    </section>
                <?php endif; ?>

                <?php if ( ! $hidden( 'sla' ) ) : ?>
                    <section class="mgk-lesson-log-sla">
                        <span><?php echo esc_html( $atts['sla_title'] ?? '' ); ?></span>
                        <strong><?php echo esc_html( $atts['sla_body'] ?? '' ); ?></strong>
                    </section>
                <?php endif; ?>

                <?php if ( ! $hidden( 'actions' ) ) : ?>
                    <nav class="mgk-lesson-log-actions">
                        <?php if ( $is_form ) : ?>
                            <button type="submit" class="is-primary"><?php echo esc_html( $atts['submit_label'] ?? 'Submit log' ); ?></button>
                            <a href="<?php echo esc_url( mgk_get_tutor_dashboard_url() ); ?>" class="mgk-lesson-log-cancel" style="text-align:center;display:block;margin-top:8px">Cancel</a>
                        <?php else : ?>
                            <button type="button" class="is-primary"><?php echo esc_html( $atts['submit_label'] ?? '' ); ?></button>
                            <button type="button"><?php echo esc_html( $atts['draft_label'] ?? '' ); ?></button>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </aside>
        </div>

        <?php if ( $form_open ) : ?>
        </form>
        <?php endif; ?>

        <?php endif; /* logged */ ?>
    </div>
</section>
