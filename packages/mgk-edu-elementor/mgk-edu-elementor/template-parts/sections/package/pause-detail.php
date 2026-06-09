<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts   = $args['atts'] ?? [];
$ctx    = $args['context'] ?? [];
$detail = is_array( $ctx['pause_detail'] ?? null ) ? $ctx['pause_detail'] : [];
$rules  = is_array( $detail['conditions'] ?? null ) ? $detail['conditions'] : [];
?>
<section class="mgk-parent-package mgk-parent-package-pause" id="pause-detail">
    <div class="mgk-parent-package__shell">
        <?php if ( ! mgk_parent_bool( $atts['hide_sec_label'] ?? '' ) ) : ?>
            <b class="mgk-parent-package-sec"><?php echo esc_html( $atts['sec_label'] ?? '' ); ?></b>
        <?php endif; ?>

        <div class="mgk-parent-package-pause__box">
            <?php if ( ! mgk_parent_bool( $atts['hide_heading'] ?? '' ) ) : ?>
                <h2><?php echo esc_html( $atts['heading'] ?? '' ); ?></h2>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_conditions'] ?? '' ) ) : ?>
                <div class="mgk-parent-package-pause__rules">
                    <?php foreach ( $rules as $rule ) : ?>
                        <span><?php echo esc_html( $rule ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_date_controls'] ?? '' ) ) : ?>
                <div class="mgk-parent-package-pause__actions">
                    <button type="button"><?php echo esc_html( $atts['pause_from_label'] ?? '' ); ?> <?php echo esc_html( $detail['pause_from'] ?? '' ); ?> ▾</button>
                    <button type="button"><?php echo esc_html( $atts['resume_label'] ?? '' ); ?> <?php echo esc_html( $detail['resume'] ?? '' ); ?> ▾</button>
                    <button type="button" class="mgk-parent-package-pause__confirm"><?php echo esc_html( $atts['confirm_label'] ?? '' ); ?></button>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_footer'] ?? '' ) ) : ?>
            <footer class="mgk-parent-package-pause__footer"><?php echo esc_html( $atts['footer'] ?? '' ); ?></footer>
        <?php endif; ?>
    </div>
</section>
