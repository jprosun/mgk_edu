<?php
/**
 * Parent dashboard empty state.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts          = $args['atts'] ?? [];
$primary_url   = $atts['primary_url'] ?? '/student/teachers/';
$secondary_url = $atts['secondary_url'] ?? '#';
?>
<section class="mgk-parent-empty-dashboard">
    <div class="mgk-parent-empty-dashboard__shell">
        <header class="mgk-parent-empty-dashboard__header">
            <?php if ( ! mgk_parent_bool( $atts['hide_greeting'] ?? '' ) || ! mgk_parent_bool( $atts['hide_parent_name'] ?? '' ) || ! mgk_parent_bool( $atts['hide_wave'] ?? '' ) ) : ?>
                <h1>
                    <?php if ( ! mgk_parent_bool( $atts['hide_greeting'] ?? '' ) ) : ?>
                        <span class="mgk-parent-empty-dashboard__greeting"><?php echo esc_html( $atts['greeting'] ?? 'Welcome,' ); ?></span>
                    <?php endif; ?>
                    <?php if ( ! mgk_parent_bool( $atts['hide_parent_name'] ?? '' ) ) : ?>
                        <span class="mgk-parent-empty-dashboard__name"><?php echo esc_html( $atts['parent_name'] ?? 'Mrs Tan' ); ?></span>
                    <?php endif; ?>
                    <?php if ( ! mgk_parent_bool( $atts['hide_wave'] ?? '' ) ) : ?>
                        <span class="mgk-parent-empty-dashboard__wave" aria-hidden="true"><?php echo wp_kses_post( $atts['wave'] ?? '&#128075;' ); ?></span>
                    <?php endif; ?>
                </h1>
            <?php endif; ?>
            <?php if ( ! mgk_parent_bool( $atts['hide_subline'] ?? '' ) ) : ?>
                <p class="mgk-parent-empty-dashboard__subline"><?php echo esc_html( $atts['subline'] ?? "NO LESSONS BOOKED YET - LET'S FIND EMMA A TUTOR." ); ?></p>
            <?php endif; ?>
        </header>

        <div class="mgk-parent-empty-dashboard__body">
            <?php if ( ! mgk_parent_bool( $atts['hide_illustration'] ?? '' ) ) : ?>
                <div class="mgk-parent-empty-dashboard__illustration">
                    <?php if ( ! mgk_parent_bool( $atts['hide_illustration_label'] ?? '' ) ) : ?>
                        <span><?php echo esc_html( $atts['illustration_label'] ?? 'Empty illustration' ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mgk-parent-empty-dashboard__copy">
                <?php if ( ! mgk_parent_bool( $atts['hide_ready_title'] ?? '' ) ) : ?>
                    <strong><?php echo esc_html( $atts['ready_title'] ?? 'Your dashboard is ready' ); ?></strong>
                <?php endif; ?>
                <?php if ( ! mgk_parent_bool( $atts['hide_ready_body'] ?? '' ) ) : ?>
                    <p><?php echo esc_html( $atts['ready_body'] ?? 'KPIS, LESSON LOGS & PROGRESS APPEAR AFTER YOUR FIRST LESSON.' ); ?></p>
                <?php endif; ?>
            </div>

            <div class="mgk-parent-empty-dashboard__actions">
                <?php if ( ! mgk_parent_bool( $atts['hide_primary'] ?? '' ) ) : ?>
                    <a class="mgk-parent-empty-dashboard__primary" href="<?php echo esc_url( mgk_url( $primary_url ) ); ?>">
                        <?php echo esc_html( $atts['primary_label'] ?? 'Find a Tutor - S02' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( ! mgk_parent_bool( $atts['hide_secondary'] ?? '' ) ) : ?>
                    <a class="mgk-parent-empty-dashboard__secondary" href="<?php echo esc_url( $secondary_url === '#' ? '#' : mgk_url( $secondary_url ) ); ?>">
                        <?php echo esc_html( $atts['secondary_label'] ?? '+ Add another child' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_note'] ?? '' ) ) : ?>
            <footer class="mgk-parent-empty-dashboard__note">
                <?php echo esc_html( $atts['note'] ?? 'EMPTY STATE REPLACES ALL DATA WIDGETS WITH ONBOARDING CTA. CHILD SWITCHER + ACCOUNT REMAIN AVAILABLE.' ); ?>
            </footer>
        <?php endif; ?>
    </div>
</section>
