<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts   = $args['atts'] ?? [];
$ctx    = $args['context'] ?? [];
$switch = is_array( $ctx['switch'] ?? null ) ? $ctx['switch'] : [];
$tutors = is_array( $switch['tutors'] ?? null ) ? $switch['tutors'] : [];
$chips  = array_filter( [
    $atts['chip_1'] ?? '',
    $atts['chip_2'] ?? '',
    $atts['chip_3'] ?? '',
    $atts['chip_4'] ?? '',
] );
?>
<section class="mgk-parent-package mgk-parent-package-switch">
    <div class="mgk-parent-package-switch__shell">
        <header class="mgk-parent-package-switch__header">
            <?php if ( ! mgk_parent_bool( $atts['hide_heading'] ?? '' ) ) : ?>
                <h1><?php echo esc_html( $atts['heading'] ?? '' ); ?></h1>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_subline'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $atts['subline'] ?? '' ); ?></p>
            <?php endif; ?>
        </header>

        <div class="mgk-parent-package-switch__body">
            <?php if ( ! mgk_parent_bool( $atts['hide_reason_heading'] ?? '' ) ) : ?>
                <h2><?php echo esc_html( $atts['reason_heading'] ?? '' ); ?></h2>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_chips'] ?? '' ) && $chips ) : ?>
                <div class="mgk-parent-package-switch__chips" aria-label="<?php echo esc_attr( $atts['reason_heading'] ?? '' ); ?>">
                    <?php foreach ( $chips as $chip ) : ?>
                        <button type="button"><?php echo esc_html( $chip ); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_tutors'] ?? '' ) && $tutors ) : ?>
                <div class="mgk-parent-package-switch__tutors">
                    <?php foreach ( $tutors as $tutor ) : ?>
                        <?php $tutor_url = $tutor['url'] ?? ( function_exists( 'mgk_teacher_profile_url' ) ? mgk_teacher_profile_url( $tutor ) : '#' ); ?>
                        <a class="mgk-parent-package-switch-card" href="<?php echo esc_url( $tutor_url ); ?>" data-event="package_switch_tutor_profile" data-mgk-event="package_switch_tutor_profile" data-tutor="<?php echo esc_attr( $tutor['id'] ?? $tutor['name'] ?? '' ); ?>">
                            <div class="mgk-parent-package-switch-card__image" role="img" aria-label="<?php echo esc_attr( $tutor['image_alt'] ?? '' ); ?>"></div>
                            <strong><?php echo esc_html( $tutor['name'] ?? '' ); ?></strong>
                            <p><?php echo esc_html( ( $tutor['rating'] ?? '' ) . ' · ' . ( $tutor['level'] ?? '' ) ); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_button'] ?? '' ) ) : ?>
                <a class="mgk-parent-package-switch__cta" href="<?php echo esc_url( $atts['button_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['button'] ?? '' ); ?></a>
            <?php endif; ?>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_note'] ?? '' ) ) : ?>
            <footer class="mgk-parent-package-switch__note"><?php echo esc_html( $atts['note'] ?? '' ); ?></footer>
        <?php endif; ?>
    </div>
</section>
