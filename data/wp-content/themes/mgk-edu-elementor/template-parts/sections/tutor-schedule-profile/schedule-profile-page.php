<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$mode = (string) ( $ctx['mode'] ?? 'demo' );

/* ── Gated: not signed in as a tutor ─────────────────────────────────────── */
if ( $mode === 'gated' ) : ?>
<section class="mgk-tutor-schedule mgk-tutor-schedule--gated" style="max-width:520px;margin:0 auto;padding:clamp(28px,6vw,64px) 20px;text-align:center">
    <h1 style="font-size:clamp(1.4rem,3.5vw,2rem);margin:0 0 .4em"><?php echo esc_html( $atts['gated_title'] ?? 'Sign in to edit your schedule' ); ?></h1>
    <p style="color:#555;margin:0 0 1.5em"><?php echo esc_html( $atts['gated_body'] ?? '' ); ?></p>
    <a style="display:inline-block;background:#111;color:#fff;text-decoration:none;border-radius:8px;padding:13px 24px;font-weight:600" href="<?php echo esc_url( $ctx['login_url'] ?? mgk_url( '/tutor/login/' ) ); ?>"><?php echo esc_html( $atts['gated_cta'] ?? 'Tutor sign in →' ); ?></a>
</section>
<?php return; endif;

/* ── Live: real schedule + profile editor for the signed-in tutor ────────── */
if ( $mode === 'real' ) :
    $action     = $ctx['action'] ?? admin_url( 'admin-post.php' );
    $tid        = (int) ( $ctx['teacher_id'] ?? 0 );
    $grid       = (array) ( $ctx['grid'] ?? [] );
    $day_labels = (array) ( $ctx['day_labels'] ?? [] );
    $modes      = (array) ( $ctx['modes'] ?? [] );
    $notice     = (string) ( $ctx['notice'] ?? '' );
?>
<section class="mgk-tutor-schedule mgk-tutor-schedule--live" data-mgk-tutor-schedule-profile>
    <style>
        .mgk-tutor-schedule--live{--mgk-line:#e6e6ea;max-width:920px;margin:0 auto;padding:clamp(18px,3vw,32px) clamp(14px,3vw,24px)}
        .mgk-tutor-schedule--live .mgk-el-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;border-bottom:1px solid var(--mgk-line);padding-bottom:14px;margin-bottom:20px}
        .mgk-tutor-schedule--live .mgk-el-topbar nav a{margin-left:14px;text-decoration:none;color:#555;font-size:.92rem}
        .mgk-tutor-schedule--live .mgk-el-topbar nav a.is-active{color:#111;font-weight:700}
        .mgk-tutor-schedule--live .mgk-el-notice{border-radius:9px;padding:12px 14px;margin-bottom:18px;font-weight:600}
        .mgk-tutor-schedule--live .mgk-el-notice--ok{background:#d6f3df;color:#1a7f37}
        .mgk-tutor-schedule--live .mgk-el-notice--err{background:#fde2e1;color:#b32d2e}
        .mgk-tutor-schedule--live .mgk-el-card{border:1px solid var(--mgk-line);border-radius:12px;padding:clamp(16px,3vw,24px);margin-bottom:22px}
        .mgk-tutor-schedule--live h2{font-size:1.15rem;margin:0 0 4px}
        .mgk-tutor-schedule--live .mgk-el-sub{color:#666;font-size:.88rem;margin:0 0 18px}
        .mgk-tutor-schedule--live .mgk-el-day{display:grid;grid-template-columns:64px 1fr;gap:12px;align-items:start;padding:12px 0;border-top:1px solid #f0f0f2}
        .mgk-tutor-schedule--live .mgk-el-day:first-of-type{border-top:0}
        .mgk-tutor-schedule--live .mgk-el-dayname{font-weight:700;padding-top:8px}
        .mgk-tutor-schedule--live .mgk-el-rows{display:flex;flex-direction:column;gap:8px}
        .mgk-tutor-schedule--live .mgk-el-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .mgk-tutor-schedule--live input[type=time],.mgk-tutor-schedule--live input[type=number],.mgk-tutor-schedule--live select,.mgk-tutor-schedule--live textarea{border:1px solid var(--mgk-line);border-radius:8px;padding:9px 10px;font:inherit}
        .mgk-tutor-schedule--live .mgk-el-row span{color:#999;font-size:.82rem}
        .mgk-tutor-schedule--live .mgk-el-save{background:#111;color:#fff;border:0;border-radius:8px;padding:13px 24px;font-weight:600;cursor:pointer;margin-top:16px}
        .mgk-tutor-schedule--live .mgk-el-field{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
        .mgk-tutor-schedule--live label{font-weight:600;font-size:.9rem}
        .mgk-tutor-schedule--live textarea{min-height:90px;width:100%;box-sizing:border-box;resize:vertical}
        .mgk-tutor-schedule--live .mgk-el-chips{display:flex;flex-wrap:wrap;gap:8px}
        .mgk-tutor-schedule--live .mgk-el-chip{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--mgk-line);border-radius:999px;padding:7px 13px;cursor:pointer;font-size:.88rem;user-select:none}
        .mgk-tutor-schedule--live .mgk-el-chip input{position:absolute;opacity:0;width:0;height:0}
        .mgk-tutor-schedule--live .mgk-el-chip:has(input:checked){background:#111;color:#fff;border-color:#111}
        @media(max-width:560px){.mgk-tutor-schedule--live .mgk-el-day{grid-template-columns:1fr}}
    </style>

    <?php if ( ! ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts['hide_topbar'] ?? '' ) ) ) : ?>
        <header class="mgk-el-topbar">
            <strong><?php echo esc_html( $atts['brand_label'] ?? '' ); ?></strong>
            <nav>
                <a href="<?php echo esc_url( $ctx['dashboard_url'] ?? '#' ); ?>">Dashboard</a>
                <a href="<?php echo esc_url( $ctx['earnings_url'] ?? '#' ); ?>">Earnings</a>
                <a class="is-active" href="<?php echo esc_url( mgk_get_tutor_schedule_url() ); ?>">Schedule</a>
            </nav>
        </header>
    <?php endif; ?>

    <?php if ( $notice === 'schedule' || $notice === 'profile' ) : ?>
        <div class="mgk-el-notice mgk-el-notice--ok"><?php echo esc_html( $notice === 'schedule' ? ( $atts['live_saved_sched'] ?? 'Availability saved.' ) : ( $atts['live_saved_prof'] ?? 'Profile saved.' ) ); ?></div>
    <?php elseif ( $notice === 'denied' ) : ?>
        <div class="mgk-el-notice mgk-el-notice--err"><?php echo esc_html( $atts['live_saved_denied'] ?? 'Could not save.' ); ?></div>
    <?php endif; ?>

    <!-- Availability -->
    <form class="mgk-el-card" method="post" action="<?php echo esc_url( $action ); ?>">
        <input type="hidden" name="action" value="mgk_tutor_save_schedule">
        <input type="hidden" name="mgk_teacher_id" value="<?php echo esc_attr( (string) $tid ); ?>">
        <input type="hidden" name="_mgk_nonce" value="<?php echo esc_attr( $ctx['sched_nonce'] ?? '' ); ?>">
        <h2><?php echo esc_html( $atts['live_sched_title'] ?? 'Weekly availability' ); ?></h2>
        <p class="mgk-el-sub"><?php echo esc_html( $atts['live_sched_sub'] ?? '' ); ?></p>

        <?php foreach ( $grid as $day => $ranges ) : ?>
            <div class="mgk-el-day">
                <div class="mgk-el-dayname"><?php echo esc_html( $day_labels[ $day ] ?? ucfirst( $day ) ); ?></div>
                <div class="mgk-el-rows">
                    <?php foreach ( (array) $ranges as $r ) : ?>
                        <div class="mgk-el-row">
                            <input type="time" name="mgk_av[<?php echo esc_attr( $day ); ?>][start][]" value="<?php echo esc_attr( $r['start'] ?? '' ); ?>" aria-label="<?php echo esc_attr( ( $day_labels[ $day ] ?? $day ) . ' ' . ( $atts['live_from'] ?? 'From' ) ); ?>">
                            <span>–</span>
                            <input type="time" name="mgk_av[<?php echo esc_attr( $day ); ?>][end][]" value="<?php echo esc_attr( $r['end'] ?? '' ); ?>" aria-label="<?php echo esc_attr( ( $day_labels[ $day ] ?? $day ) . ' ' . ( $atts['live_to'] ?? 'To' ) ); ?>">
                            <select name="mgk_av[<?php echo esc_attr( $day ); ?>][mode][]" aria-label="<?php echo esc_attr( $atts['live_mode'] ?? 'Mode' ); ?>">
                                <?php foreach ( $modes as $m ) : ?>
                                    <option value="<?php echo esc_attr( $m ); ?>" <?php selected( strtoupper( (string) ( $r['mode'] ?? 'ONLINE' ) ), $m ); ?>><?php echo esc_html( ucfirst( strtolower( $m ) ) ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="mgk-el-save"><?php echo esc_html( $atts['live_save_sched'] ?? 'Save availability' ); ?></button>
    </form>

    <!-- Profile -->
    <form class="mgk-el-card" method="post" action="<?php echo esc_url( $action ); ?>">
        <input type="hidden" name="action" value="mgk_tutor_save_profile">
        <input type="hidden" name="mgk_teacher_id" value="<?php echo esc_attr( (string) $tid ); ?>">
        <input type="hidden" name="_mgk_nonce" value="<?php echo esc_attr( $ctx['prof_nonce'] ?? '' ); ?>">
        <h2><?php echo esc_html( $atts['live_prof_title'] ?? 'Public profile' ); ?></h2>
        <p class="mgk-el-sub"><?php echo esc_html( $atts['live_prof_sub'] ?? '' ); ?></p>

        <div class="mgk-el-field" style="max-width:220px">
            <label for="mgk_rate"><?php echo esc_html( $atts['live_rate_label'] ?? 'Hourly rate (S$)' ); ?></label>
            <input type="number" id="mgk_rate" name="mgk_rate" min="0" step="1" value="<?php echo esc_attr( (string) ( $ctx['rate'] ?? 0 ) ); ?>">
        </div>

        <div class="mgk-el-field">
            <label for="mgk_bio"><?php echo esc_html( $atts['live_bio_label'] ?? 'Short bio' ); ?></label>
            <textarea id="mgk_bio" name="mgk_bio"><?php echo esc_textarea( (string) ( $ctx['bio'] ?? '' ) ); ?></textarea>
        </div>

        <?php if ( ! empty( $ctx['subjects'] ) ) : ?>
            <div class="mgk-el-field">
                <label><?php echo esc_html( $atts['live_subjects_label'] ?? 'Subjects you teach' ); ?></label>
                <div class="mgk-el-chips">
                    <?php foreach ( (array) $ctx['subjects'] as $s ) : ?>
                        <label class="mgk-el-chip"><input type="checkbox" name="mgk_subjects[]" value="<?php echo esc_attr( (string) $s['id'] ); ?>" <?php checked( ! empty( $s['checked'] ) ); ?>><?php echo esc_html( $s['name'] ); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $ctx['levels'] ) ) : ?>
            <div class="mgk-el-field">
                <label><?php echo esc_html( $atts['live_levels_label'] ?? 'Levels' ); ?></label>
                <div class="mgk-el-chips">
                    <?php foreach ( (array) $ctx['levels'] as $l ) : ?>
                        <label class="mgk-el-chip"><input type="checkbox" name="mgk_levels[]" value="<?php echo esc_attr( (string) $l['id'] ); ?>" <?php checked( ! empty( $l['checked'] ) ); ?>><?php echo esc_html( $l['name'] ); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="mgk-el-save"><?php echo esc_html( $atts['live_save_prof'] ?? 'Save profile' ); ?></button>
    </form>
</section>
<?php return; endif;

/* ── Demo (Elementor editor) ─────────────────────────────────────────────── */
$profile = (array) ( $ctx['profile'] ?? [] );

$hidden = function ( $key ) use ( $atts ) {
    return function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' );
};

$section_label = function ( $label ) {
    if ( $label !== '' ) {
        echo '<span class="mgk-tutor-schedule-sec">' . esc_html( $label ) . '</span>';
    }
};
?>
<section class="mgk-tutor-schedule" data-mgk-tutor-schedule-profile>
    <div class="mgk-tutor-schedule__shell">
        <?php if ( ! $hidden( 'topbar' ) ) : ?>
            <header class="mgk-tutor-schedule-topbar">
                <strong><?php echo esc_html( $atts['brand_label'] ?? '' ); ?></strong>
                <nav>
                    <a class="is-active" href="<?php echo esc_url( mgk_get_tutor_schedule_url() ); ?>"><?php echo esc_html( $atts['schedule_tab'] ?? '' ); ?></a>
                    <a href="#profile"><?php echo esc_html( $atts['profile_tab'] ?? '' ); ?></a>
                </nav>
            </header>
        <?php endif; ?>

        <?php if ( ! $hidden( 'availability' ) ) : ?>
            <section class="mgk-tutor-schedule-availability">
                <?php $section_label( $atts['sec_availability'] ?? '' ); ?>
                <header>
                    <div>
                        <h1><?php echo esc_html( $atts['availability_title'] ?? '' ); ?></h1>
                        <p><?php echo esc_html( $atts['availability_sub'] ?? '' ); ?></p>
                    </div>
                    <nav>
                        <a href="#"><?php echo esc_html( $atts['reset_label'] ?? '' ); ?></a>
                        <a class="is-primary" href="#"><?php echo esc_html( $atts['edit_avail_label'] ?? '' ); ?></a>
                    </nav>
                </header>
                <div class="mgk-tutor-schedule-grid">
                    <span></span>
                    <?php foreach ( (array) ( $ctx['days'] ?? [] ) as $day ) : ?>
                        <b><?php echo esc_html( $day ); ?></b>
                    <?php endforeach; ?>
                    <?php foreach ( (array) ( $ctx['slots'] ?? [] ) as $slot ) : ?>
                        <em><?php echo esc_html( $slot['label'] ?? '' ); ?></em>
                        <?php foreach ( (array) ( $slot['cells'] ?? [] ) as $cell ) : ?>
                            <span class="<?php echo $cell === '✓' ? 'is-on' : ( $cell === 'blk' ? 'is-block' : '' ); ?>"><?php echo esc_html( $cell ); ?></span>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <footer>
                    <span><i></i><?php echo esc_html( $atts['legend_available'] ?? '' ); ?></span>
                    <span><i></i><?php echo esc_html( $atts['legend_block'] ?? '' ); ?></span>
                    <span><i></i><?php echo esc_html( $atts['legend_off'] ?? '' ); ?></span>
                </footer>
            </section>
        <?php endif; ?>

        <div class="mgk-tutor-schedule-row">
            <?php if ( ! $hidden( 'block' ) ) : ?>
                <section class="mgk-tutor-schedule-panel mgk-tutor-schedule-block">
                    <?php $section_label( $atts['sec_block'] ?? '' ); ?>
                    <h2><?php echo esc_html( $atts['sec_block'] ?? '' ); ?></h2>
                    <p><?php echo esc_html( $atts['block_sub'] ?? '' ); ?></p>
                    <div>
                        <label><span><?php echo esc_html( $atts['block_date_label'] ?? '' ); ?></span><strong><?php echo esc_html( $atts['block_date'] ?? '' ); ?></strong></label>
                        <label><span><?php echo esc_html( $atts['block_type_label'] ?? '' ); ?></span><strong><?php echo esc_html( $atts['block_type'] ?? '' ); ?></strong></label>
                        <label><span><?php echo esc_html( $atts['block_from_label'] ?? '' ); ?></span><strong><?php echo esc_html( $atts['block_from'] ?? '' ); ?></strong></label>
                        <label><span><?php echo esc_html( $atts['block_to_label'] ?? '' ); ?></span><strong><?php echo esc_html( $atts['block_to'] ?? '' ); ?></strong></label>
                    </div>
                    <a class="is-primary" href="#"><?php echo esc_html( $atts['add_block_label'] ?? '' ); ?></a>
                </section>
            <?php endif; ?>

            <?php if ( ! $hidden( 'sync' ) ) : ?>
                <section class="mgk-tutor-schedule-sync">
                    <?php $section_label( $atts['sec_sync'] ?? '' ); ?>
                    <h2><?php echo esc_html( $atts['sync_title'] ?? '' ); ?></h2>
                    <p><?php echo esc_html( $atts['sync_body'] ?? '' ); ?></p>
                    <strong><?php echo esc_html( $atts['sync_status'] ?? '' ); ?></strong>
                </section>
            <?php endif; ?>
        </div>

        <?php if ( ! $hidden( 'profile' ) ) : ?>
            <section id="profile" class="mgk-tutor-schedule-profile">
                <?php $section_label( $atts['sec_profile'] ?? '' ); ?>
                <h2><?php echo esc_html( $atts['profile_title'] ?? '' ); ?></h2>
                <div class="mgk-tutor-schedule-profile-grid">
                    <div class="mgk-tutor-schedule-media">
                        <article class="mgk-tutor-schedule-photo">
                            <div><span>Photo</span></div>
                            <a href="#"><?php echo esc_html( $atts['change_photo_label'] ?? '' ); ?></a>
                        </article>
                        <article class="mgk-tutor-schedule-demo">
                            <h3><?php echo esc_html( $atts['demo_title'] ?? '' ); ?></h3>
                            <div><span><?php echo esc_html( $atts['current_label'] ?? '' ); ?></span></div>
                            <a href="#"><?php echo esc_html( $atts['replace_video_label'] ?? '' ); ?></a>
                            <p><?php echo esc_html( $atts['demo_note'] ?? '' ); ?></p>
                        </article>
                    </div>
                    <div class="mgk-tutor-schedule-fields">
                        <article class="mgk-tutor-schedule-bio">
                            <span><?php echo esc_html( $atts['bio_label'] ?? '' ); ?></span>
                            <i></i><i></i>
                        </article>
                        <article class="mgk-tutor-schedule-subjects">
                            <span><?php echo esc_html( $atts['subjects_label'] ?? '' ); ?></span>
                            <div>
                                <?php foreach ( (array) ( $profile['subjects'] ?? [] ) as $subject ) : ?>
                                    <b><?php echo esc_html( $subject ); ?></b>
                                <?php endforeach; ?>
                                <b><?php echo esc_html( $atts['add_subject_label'] ?? '' ); ?></b>
                            </div>
                        </article>
                        <article class="mgk-tutor-schedule-rate">
                            <span><?php echo esc_html( $atts['rate_label'] ?? '' ); ?></span>
                            <div><strong><?php echo esc_html( $profile['rate'] ?? '' ); ?></strong><em><?php echo esc_html( $profile['new_rate'] ?? '' ); ?></em></div>
                            <p><?php echo esc_html( $atts['rate_note'] ?? '' ); ?></p>
                        </article>
                        <nav>
                            <a class="is-primary" href="#"><?php echo esc_html( $atts['save_label'] ?? '' ); ?></a>
                            <a href="#"><?php echo esc_html( $atts['preview_label'] ?? '' ); ?></a>
                        </nav>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
