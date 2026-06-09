<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts   = $args['atts'] ?? [];
$ctx    = $args['context'] ?? [];
$lapsed = is_array( $ctx['lapsed'] ?? null ) ? $ctx['lapsed'] : [];
?>
<section class="mgk-parent-package mgk-parent-package-lapsed">
    <div class="mgk-parent-package-lapsed__shell">
        <div class="mgk-parent-package-lapsed__panel">
            <?php if ( ! mgk_parent_bool( $atts['hide_badge'] ?? '' ) ) : ?>
                <b class="mgk-parent-package-lapsed__badge"><?php echo esc_html( $atts['badge'] ?? '' ); ?></b>
            <?php endif; ?>

            <header class="mgk-parent-package-lapsed__header">
                <?php if ( ! mgk_parent_bool( $atts['hide_heading'] ?? '' ) ) : ?>
                    <h1><?php echo esc_html( $atts['heading'] ?? '' ); ?></h1>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_subline'] ?? '' ) ) : ?>
                    <p><?php echo esc_html( $atts['subline'] ?? '' ); ?></p>
                <?php endif; ?>
            </header>

            <?php if ( ! mgk_parent_bool( $atts['hide_tutor'] ?? '' ) ) : ?>
                <div class="mgk-parent-package-lapsed__tutor">
                    <span class="mgk-parent-package-lapsed__avatar" aria-hidden="true"></span>
                    <div>
                        <strong><?php echo esc_html( $lapsed['availability'] ?? '' ); ?></strong>
                        <p><?php echo esc_html( $lapsed['slot_note'] ?? '' ); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mgk-parent-package-lapsed__actions">
                <?php if ( ! mgk_parent_bool( $atts['hide_primary'] ?? '' ) ) : ?>
                    <a class="mgk-parent-package-lapsed__primary" href="<?php echo esc_url( $lapsed['reactivate_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['primary_label'] ?? '' ); ?></a>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_secondary'] ?? '' ) ) : ?>
                    <a class="mgk-parent-package-lapsed__secondary" href="<?php echo esc_url( $lapsed['different_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['secondary_label'] ?? '' ); ?></a>
                <?php endif; ?>
            </div>

            <?php if ( ! mgk_parent_bool( $atts['hide_discount'] ?? '' ) ) : ?>
                <p class="mgk-parent-package-lapsed__discount"><?php echo esc_html( $lapsed['discount_note'] ?? '' ); ?></p>
            <?php endif; ?>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_bottom_note'] ?? '' ) ) : ?>
            <footer class="mgk-parent-package-lapsed__bottom-note"><?php echo esc_html( $atts['bottom_note'] ?? '' ); ?></footer>
        <?php endif; ?>
    </div>
</section>
