<?php
/** S01 Spotlight tutor. @var array $args — eyebrow, name, meta, profile_label, trial_label */
if ( ! defined( 'ABSPATH' ) ) exit;
$eyebrow       = $args['eyebrow']       ?? mgk_site_setting( 'spotlight_eyebrow' );
$name          = $args['name']          ?? mgk_site_setting( 'spotlight_name' );
$meta          = $args['meta']          ?? mgk_site_setting( 'spotlight_meta' );
$profile_label = $args['profile_label'] ?? mgk_site_setting( 'spotlight_profile_label' );
$trial_label   = $args['trial_label']   ?? mgk_site_setting( 'spotlight_trial_label' );
?>
    <section class="mgk-section mgk-home-spotlight">
        <div class="mgk-shell mgk-spotlight">
            <div class="mgk-placeholder"<?php echo mgk_site_image_style( 'spotlight_image_id' ); ?>>
                <?php if ( ! (int) mgk_site_setting( 'spotlight_image_id' ) ) : ?>
                    <?php echo esc_html( mgk_site_setting( 'spotlight_label' ) ); ?>
                <?php endif; ?>
            </div>
            <div>
                <p class="mgk-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
                <h2><?php echo esc_html( $name ); ?></h2>
                <p class="mgk-check"><?php echo esc_html( $meta ); ?></p>
                <div class="mgk-line"></div>
                <div class="mgk-line"></div>
                <div class="mgk-line short"></div>
                <div class="mgk-mini-stats">
                    <?php foreach ( [ 'spotlight_stat_1', 'spotlight_stat_2', 'spotlight_stat_3' ] as $stat_key ) : ?>
                        <?php [ $value, $label ] = mgk_site_split_stat( mgk_site_setting( $stat_key ) ); ?>
                        <div class="mgk-mini-stat"><strong><?php echo esc_html( $value ); ?></strong><span><?php echo esc_html( $label ); ?></span></div>
                    <?php endforeach; ?>
                </div>
                <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_teacher_profile_url( $name ) ); ?>"><?php echo esc_html( $profile_label ); ?></a>
                <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_get_trial_url( [ 'tutor' => sanitize_title( $name ) ] ) ); ?>" data-mgk-event="trial_cta_clicked"><?php echo esc_html( $trial_label ); ?></a>
            </div>
        </div>
    </section>
