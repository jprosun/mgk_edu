<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$candidate = (array) ( $ctx['candidate'] ?? [] );
$video = (array) ( $ctx['video'] ?? [] );
$requirements = (array) ( $ctx['requirements'] ?? [] );
$timeline = (array) ( $ctx['timeline'] ?? [] );
$variant = (string) ( $ctx['variant'] ?? 'default' );
$current_state = (string) ( $ctx['current_state'] ?? 'DEMO_PENDING' );

$token = function ( $text ) use ( $candidate ) {
    return strtr( (string) $text, [
        '{name}' => (string) ( $candidate['name'] ?? 'Tutor' ),
        '{date}' => (string) ( $candidate['submitted'] ?? '' ),
    ] );
};

$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-tutor-verification-sec">' . esc_html( $label ) . '</span>';
    }
};
?>
<section class="mgk-tutor-verification mgk-tutor-verification--<?php echo esc_attr( $variant ); ?>" data-mgk-tutor-verification>
    <div class="mgk-tutor-verification__shell">
        <?php if ( ! mgk_parent_bool( $atts['hide_status'] ?? '' ) ) : ?>
            <header class="mgk-tutor-verification-status">
                <?php $section_label( $atts['sec_status'] ?? '' ); ?>
                <div>
                    <h1><?php echo esc_html( $atts['status_title'] ?? '' ); ?></h1>
                    <p><?php echo esc_html( $token( $atts['status_meta'] ?? '' ) ); ?></p>
                </div>
                <aside>
                    <span><?php echo esc_html( $atts['current_state_label'] ?? '' ); ?></span>
                    <strong><?php echo esc_html( $current_state ); ?></strong>
                </aside>
            </header>
        <?php endif; ?>

        <div class="mgk-tutor-verification-grid">
            <main class="mgk-tutor-verification-left">
                <?php if ( ! mgk_parent_bool( $atts['hide_video'] ?? '' ) ) : ?>
                    <section class="mgk-tutor-verification-video">
                        <h2><?php echo esc_html( $atts['video_title'] ?? '' ); ?></h2>
                        <p><?php echo esc_html( $atts['video_intro'] ?? '' ); ?></p>
                        <div class="mgk-tutor-verification-upload" data-mgk-verification-upload>
                            <div data-mgk-verification-preview><strong><?php echo esc_html( ( $atts['uploading_label'] ?? '' ) . ' ' . ( $video['progress'] ?? '' ) ); ?></strong></div>
                            <span><b data-mgk-verification-progress></b></span>
                            <em data-mgk-verification-meta><?php echo esc_html( ( $video['filename'] ?? '' ) . ' · ' . ( $video['size'] ?? '' ) . ' · ' . ( $video['eta'] ?? '' ) ); ?></em>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_requirements'] ?? '' ) ) : ?>
                    <section class="mgk-tutor-verification-req">
                        <h2><?php echo esc_html( $atts['requirements_title'] ?? '' ); ?></h2>
                        <div class="mgk-tutor-verification-req-grid">
                            <?php foreach ( $requirements as $requirement ) : ?>
                                <article>
                                    <strong><?php echo esc_html( $requirement['value'] ?? '' ); ?></strong>
                                    <span><?php echo esc_html( $requirement['label'] ?? '' ); ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <p><?php echo esc_html( $atts['requirements_tip'] ?? '' ); ?></p>
                    </section>
                <?php endif; ?>
            </main>

            <?php if ( ! mgk_parent_bool( $atts['hide_timeline'] ?? '' ) ) : ?>
                <aside class="mgk-tutor-verification-timeline">
                    <?php $section_label( 'SEC 4 Timeline' ); ?>
                    <h2><?php echo esc_html( $atts['timeline_title'] ?? '' ); ?></h2>
                    <p><?php echo esc_html( $atts['timeline_meta'] ?? '' ); ?></p>
                    <ol>
                        <?php foreach ( $timeline as $item ) : ?>
                            <li class="<?php echo ! empty( $item['active'] ) ? 'is-active' : ''; ?> <?php echo ! empty( $item['done'] ) ? 'is-done' : ''; ?>">
                                <span></span>
                                <div>
                                    <strong><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
                                    <em><?php echo esc_html( $item['meta'] ?? '' ); ?></em>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="mgk-tutor-verification-message">
                        <strong><?php echo esc_html( $atts['reviewer_label'] ?? '' ); ?></strong>
                        <p><?php echo esc_html( $atts['reviewer_message'] ?? '' ); ?> <a href="<?php echo esc_url( mgk_get_tutor_verification_url() ); ?>"><?php echo esc_html( $atts['reviewer_cta'] ?? '' ); ?></a></p>
                    </div>
                    <div class="mgk-tutor-verification-rejected">
                        <strong><?php echo esc_html( $atts['rejected_title'] ?? '' ); ?></strong>
                        <p><?php echo esc_html( $atts['rejected_body'] ?? '' ); ?></p>
                    </div>
                </aside>
            <?php endif; ?>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_actions'] ?? '' ) ) : ?>
            <footer class="mgk-tutor-verification-actions">
                <div>
                    <a class="mgk-tutor-verification-primary" href="<?php echo esc_url( mgk_get_tutor_verification_url() ); ?>"><?php echo esc_html( $atts['submit_label'] ?? '' ); ?></a>
                    <a class="mgk-tutor-verification-secondary" href="mailto:support@example.com"><?php echo esc_html( $atts['contact_label'] ?? '' ); ?></a>
                </div>
                <span><?php echo esc_html( $atts['avg_time'] ?? '' ); ?></span>
            </footer>
        <?php endif; ?>
    </div>
</section>
