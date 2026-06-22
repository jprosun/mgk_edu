<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$mode = (string) ( $ctx['mode'] ?? 'demo' );

/* ── Live mode: real application status (applicant via token / logged-in tutor) ── */
if ( $mode === 'real' || $mode === 'empty' ) :
    $vstyle = '<style>
        .mgk-verify-live{--mgk-line:#e2e2e6;max-width:680px;margin:0 auto;padding:clamp(20px,4vw,44px) clamp(16px,3vw,24px)}
        .mgk-verify-live__banner{border-radius:10px;padding:14px 16px;margin-bottom:20px;font-weight:600}
        .mgk-verify-live__banner--ok{background:#d6f3df;color:#1a7f37}
        .mgk-verify-live__head h1{font-size:clamp(1.5rem,3.5vw,2.1rem);margin:0 0 .25em}
        .mgk-verify-live__head p{color:#555;margin:0 0 1.5em}
        .mgk-verify-live__state{display:inline-block;font-size:.8rem;font-weight:700;letter-spacing:.04em;padding:5px 12px;border-radius:999px;margin-bottom:24px}
        .mgk-verify-live__state--pending{background:#fff3cd;color:#8a6d3b}
        .mgk-verify-live__state--ok{background:#d6f3df;color:#1a7f37}
        .mgk-verify-live__state--bad{background:#fde2e1;color:#b32d2e}
        .mgk-verify-live__timeline{list-style:none;margin:0 0 28px;padding:0}
        .mgk-verify-live__timeline li{display:flex;gap:14px;padding:0 0 22px;position:relative}
        .mgk-verify-live__timeline li:before{content:"";position:absolute;left:9px;top:22px;bottom:-4px;width:2px;background:var(--mgk-line)}
        .mgk-verify-live__timeline li:last-child:before{display:none}
        .mgk-verify-live__dot{flex:0 0 20px;height:20px;border-radius:50%;border:2px solid var(--mgk-line);background:#fff;margin-top:1px}
        .mgk-verify-live__timeline li.is-done .mgk-verify-live__dot{background:#1a7f37;border-color:#1a7f37}
        .mgk-verify-live__timeline li.is-active .mgk-verify-live__dot{border-color:#111;box-shadow:0 0 0 3px rgba(17,17,17,.12)}
        .mgk-verify-live__timeline strong{display:block;font-size:.95rem}
        .mgk-verify-live__timeline em{color:#666;font-style:normal;font-size:.86rem}
        .mgk-verify-live__note{background:#fff8e6;border:1px solid #f0e0a8;border-radius:10px;padding:14px 16px;margin-bottom:24px}
        .mgk-verify-live__note strong{display:block;margin-bottom:6px}
        .mgk-verify-live__cta{display:inline-block;background:#111;color:#fff;text-decoration:none;border-radius:8px;padding:13px 22px;font-weight:600}
        .mgk-verify-live__foot{color:#777;font-size:.85rem;margin-top:20px}
    </style>';

    if ( $mode === 'empty' ) :
?>
<section class="mgk-tutor-verification mgk-verify-live"><?php echo $vstyle; // phpcs:ignore ?>
    <div class="mgk-verify-live__head">
        <h1><?php echo esc_html( $atts['empty_title'] ?? 'No application found' ); ?></h1>
        <p><?php echo esc_html( $atts['empty_body'] ?? '' ); ?></p>
    </div>
    <a class="mgk-verify-live__cta" href="<?php echo esc_url( $ctx['apply_url'] ?? mgk_url( '/become-a-tutor/' ) ); ?>"><?php echo esc_html( $atts['live_reapply_label'] ?? 'Apply to teach →' ); ?></a>
</section>
<?php
        return;
    endif;

    // real
    $timeline   = (array) ( $ctx['timeline'] ?? [] );
    $candidate  = (array) ( $ctx['candidate'] ?? [] );
    $note       = (string) ( $ctx['reviewer_note'] ?? '' );
    $is_ok      = ! empty( $ctx['is_approved'] );
    $is_bad     = ! empty( $ctx['is_rejected'] );
    $needs_info = ! empty( $ctx['needs_info'] );
    $state_cls  = $is_ok ? 'ok' : ( $is_bad ? 'bad' : 'pending' );
?>
<section class="mgk-tutor-verification mgk-verify-live"><?php echo $vstyle; // phpcs:ignore ?>
    <?php if ( ! empty( $ctx['just_applied'] ) ) : ?>
        <div class="mgk-verify-live__banner mgk-verify-live__banner--ok"><?php echo esc_html( $atts['live_received_title'] ?? "We've got your application" ); ?> ✓</div>
    <?php endif; ?>

    <div class="mgk-verify-live__head">
        <h1><?php echo esc_html( $atts['live_title'] ?? 'Your application status' ); ?></h1>
        <p>Hi <?php echo esc_html( $candidate['name'] ?? 'there' ); ?><?php echo ! empty( $candidate['submitted'] ) ? ' · submitted ' . esc_html( $candidate['submitted'] ) : ''; ?></p>
    </div>

    <span class="mgk-verify-live__state mgk-verify-live__state--<?php echo esc_attr( $state_cls ); ?>"><?php echo esc_html( $ctx['state_label'] ?? '' ); ?></span>

    <ol class="mgk-verify-live__timeline">
        <?php foreach ( $timeline as $item ) : ?>
            <li class="<?php echo ! empty( $item['done'] ) ? 'is-done ' : ''; ?><?php echo ! empty( $item['active'] ) ? 'is-active' : ''; ?>">
                <span class="mgk-verify-live__dot"></span>
                <div>
                    <strong><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
                    <em><?php echo esc_html( $item['meta'] ?? '' ); ?></em>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>

    <?php if ( $note && ( $needs_info || $is_bad ) ) : ?>
        <div class="mgk-verify-live__note">
            <strong><?php echo esc_html( $atts['live_note_label'] ?? 'Message from our team' ); ?></strong>
            <?php echo esc_html( $note ); ?>
        </div>
    <?php endif; ?>

    <?php if ( $is_ok ) : ?>
        <a class="mgk-verify-live__cta" href="<?php echo esc_url( $ctx['dashboard_url'] ?? mgk_url( '/tutor/dashboard/' ) ); ?>"><?php echo esc_html( $atts['live_signin_label'] ?? 'Sign in to your dashboard →' ); ?></a>
        <p class="mgk-verify-live__foot">Check your inbox for your one-time sign-in link.</p>
    <?php elseif ( $needs_info ) : ?>
        <a class="mgk-verify-live__cta" href="<?php echo esc_url( $ctx['apply_url'] ?? mgk_url( '/become-a-tutor/' ) ); ?>"><?php echo esc_html( $atts['live_resubmit_label'] ?? 'Update & resubmit →' ); ?></a>
    <?php elseif ( $is_bad ) : ?>
        <a class="mgk-verify-live__cta" href="<?php echo esc_url( $ctx['apply_url'] ?? mgk_url( '/become-a-tutor/' ) ); ?>"><?php echo esc_html( $atts['live_reapply_label'] ?? 'Apply again →' ); ?></a>
    <?php else : ?>
        <p class="mgk-verify-live__foot"><?php echo esc_html( $atts['live_avg_time'] ?? '' ); ?></p>
    <?php endif; ?>
</section>
<?php
    return;
endif;

/* ── Demo (Elementor editor) ─────────────────────────────────────────────── */
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
