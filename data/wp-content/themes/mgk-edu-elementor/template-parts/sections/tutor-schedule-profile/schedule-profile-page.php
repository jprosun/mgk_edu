<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
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
