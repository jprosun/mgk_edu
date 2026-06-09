<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts  = $args['atts'] ?? [];
$ctx   = $args['context'] ?? [];
$end   = is_array( $ctx['end'] ?? null ) ? $ctx['end'] : [];
$facts = is_array( $end['facts'] ?? null ) ? $end['facts'] : [];
?>
<section class="mgk-parent-package mgk-parent-package-end">
    <div class="mgk-parent-package-end__shell">
        <header class="mgk-parent-package-end__header">
            <?php if ( ! mgk_parent_bool( $atts['hide_heading'] ?? '' ) ) : ?>
                <h1><?php echo esc_html( $atts['heading'] ?? '' ); ?></h1>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_subline'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $atts['subline'] ?? '' ); ?></p>
            <?php endif; ?>
        </header>

        <div class="mgk-parent-package-end__body">
            <?php if ( ! mgk_parent_bool( $atts['hide_facts'] ?? '' ) && $facts ) : ?>
                <ul class="mgk-parent-package-end__facts">
                    <?php foreach ( $facts as $fact ) : ?>
                        <li><?php echo esc_html( $fact ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_actions'] ?? '' ) ) : ?>
                <div class="mgk-parent-package-end__actions">
                    <a class="mgk-parent-package-end__keep" href="<?php echo esc_url( $atts['keep_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['keep_label'] ?? '' ); ?></a>
                    <a class="mgk-parent-package-end__confirm" href="<?php echo esc_url( $atts['confirm_url'] ?? '#' ); ?>"><?php echo esc_html( $atts['confirm_label'] ?? '' ); ?></a>
                </div>
            <?php endif; ?>

            <?php if ( ! mgk_parent_bool( $atts['hide_equal_note'] ?? '' ) ) : ?>
                <p class="mgk-parent-package-end__equal-note"><?php echo esc_html( $atts['equal_note'] ?? '' ); ?></p>
            <?php endif; ?>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_bottom_note'] ?? '' ) ) : ?>
            <footer class="mgk-parent-package-end__bottom-note"><?php echo esc_html( $atts['bottom_note'] ?? '' ); ?></footer>
        <?php endif; ?>
    </div>
</section>
