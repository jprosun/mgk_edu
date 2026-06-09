<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$lesson = (array) ( $ctx['lesson'] ?? [] );

$hidden = function ( $key ) use ( $atts ) {
    return function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' );
};

$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-lesson-log-sec">' . esc_html( $label ) . '</span>';
    }
};
?>
<section class="mgk-lesson-log" data-mgk-tutor-lesson-log>
    <div class="mgk-lesson-log__shell">
        <?php if ( ! $hidden( 'topbar' ) ) : ?>
            <header class="mgk-lesson-log-topbar">
                <a href="<?php echo esc_url( mgk_get_tutor_dashboard_url() ); ?>"><?php echo esc_html( $atts['back_label'] ?? '' ); ?></a>
                <strong><?php echo esc_html( $atts['entry_label'] ?? '' ); ?></strong>
                <span><?php echo esc_html( $atts['autosave_label'] ?? '' ); ?></span>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'header' ) ) : ?>
            <header class="mgk-lesson-log-head">
                <?php $section_label( $atts['sec_lesson'] ?? '' ); ?>
                <div>
                    <h1><?php echo esc_html( $lesson['title'] ?? '' ); ?></h1>
                    <p><?php echo esc_html( $lesson['meta'] ?? '' ); ?></p>
                </div>
                <aside>
                    <span><?php echo esc_html( $atts['package_label'] ?? '' ); ?></span>
                    <strong><?php echo esc_html( $lesson['package'] ?? '' ); ?></strong>
                </aside>
            </header>
        <?php endif; ?>

        <div class="mgk-lesson-log-layout">
            <main class="mgk-lesson-log-main">
                <?php if ( ! $hidden( 'attendance' ) ) : ?>
                    <section class="mgk-lesson-log-card mgk-lesson-log-attendance">
                        <?php $section_label( $atts['sec_attendance'] ?? '' ); ?>
                        <h2><?php echo esc_html( $atts['attendance_title'] ?? '' ); ?></h2>
                        <div class="mgk-lesson-log-attendance-grid">
                            <?php foreach ( (array) ( $ctx['attendance'] ?? [] ) as $option ) : ?>
                                <button type="button" class="<?php echo ! empty( $option['active'] ) ? 'is-active' : ''; ?>"><?php echo esc_html( $option['label'] ?? '' ); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( ! $hidden( 'notes' ) ) : ?>
                    <section class="mgk-lesson-log-card mgk-lesson-log-notes">
                        <?php $section_label( $atts['sec_notes'] ?? '' ); ?>
                        <header>
                            <h2><?php echo esc_html( $atts['note_title'] ?? '' ); ?></h2>
                            <span><?php echo esc_html( $atts['recording_label'] ?? '' ); ?></span>
                        </header>
                        <div class="mgk-lesson-log-transcript">
                            <b><?php echo esc_html( $atts['transcript_label'] ?? '' ); ?></b>
                            <i></i>
                            <i></i>
                        </div>
                        <div class="mgk-lesson-log-field-grid">
                            <?php foreach ( (array) ( $ctx['note_fields'] ?? [] ) as $field ) : ?>
                                <article>
                                    <span><?php echo esc_html( $field['label'] ?? '' ); ?></span>
                                    <strong><?php echo esc_html( $field['value'] ?? '' ); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <p><?php echo esc_html( $atts['note_footer'] ?? '' ); ?></p>
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
                                <input type="file" accept="image/*">
                                <span><?php echo esc_html( $atts['photo_existing'] ?? '' ); ?></span>
                            </label>
                            <label>
                                <input type="file" accept="image/*">
                                <span><?php echo esc_html( $atts['photo_add_label'] ?? '' ); ?></span>
                            </label>
                            <label>
                                <input type="file" accept="image/*">
                                <span><?php echo esc_html( $atts['photo_plus_label'] ?? '' ); ?></span>
                            </label>
                        </div>
                        <p><?php echo esc_html( $atts['photos_note'] ?? '' ); ?></p>
                    </section>
                <?php endif; ?>

                <?php if ( ! $hidden( 'save' ) ) : ?>
                    <section class="mgk-lesson-log-autosave">
                        <?php $section_label( $atts['sec_save'] ?? '' ); ?>
                        <strong><?php echo esc_html( $atts['autosave_title'] ?? '' ); ?></strong>
                        <p><?php echo esc_html( $atts['autosave_meta'] ?? '' ); ?></p>
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
                        <button type="button" class="is-primary"><?php echo esc_html( $atts['submit_label'] ?? '' ); ?></button>
                        <button type="button"><?php echo esc_html( $atts['draft_label'] ?? '' ); ?></button>
                    </nav>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>
