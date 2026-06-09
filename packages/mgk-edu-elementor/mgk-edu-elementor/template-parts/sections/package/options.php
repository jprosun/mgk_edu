<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts    = $args['atts'] ?? [];
$ctx     = $args['context'] ?? [];
$options = is_array( $ctx['options'] ?? null ) ? $ctx['options'] : [];
?>
<section class="mgk-parent-package mgk-parent-package-options">
    <div class="mgk-parent-package__shell">
        <?php if ( ! mgk_parent_bool( $atts['hide_sec_label'] ?? '' ) ) : ?>
            <b class="mgk-parent-package-sec"><?php echo esc_html( $atts['sec_label'] ?? '' ); ?></b>
        <?php endif; ?>

        <div class="mgk-parent-package-options__grid">
            <?php foreach ( $options as $option ) : ?>
                <?php
                $classes = 'mgk-parent-package-option mgk-parent-package-option--' . sanitize_html_class( $option['key'] ?? 'item' );
                if ( ! empty( $option['featured'] ) ) {
                    $classes .= ' is-featured';
                }
                ?>
                <article class="<?php echo esc_attr( $classes ); ?>">
                    <header>
                        <?php if ( ! mgk_parent_bool( $atts['hide_card_icons'] ?? '' ) ) : ?>
                            <span aria-hidden="true"><?php echo wp_kses_post( $option['icon'] ?? '' ); ?></span>
                        <?php endif; ?>
                        <h2><?php echo esc_html( $option['title'] ?? '' ); ?></h2>
                    </header>

                    <p><?php echo esc_html( $option['summary'] ?? '' ); ?></p>

                    <?php if ( ! mgk_parent_bool( $atts['hide_prices'] ?? '' ) && ! empty( $option['price'] ) ) : ?>
                        <strong><?php echo esc_html( $option['price'] ); ?></strong>
                    <?php endif; ?>

                    <?php if ( ! mgk_parent_bool( $atts['hide_details'] ?? '' ) ) : ?>
                        <em><?php echo esc_html( $option['detail'] ?? '' ); ?></em>
                    <?php endif; ?>

                    <?php if ( ! mgk_parent_bool( $atts['hide_buttons'] ?? '' ) ) : ?>
                        <a href="<?php echo esc_url( $option['url'] ?? '#' ); ?>"><?php echo esc_html( $option['button'] ?? '' ); ?></a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_note'] ?? '' ) ) : ?>
            <footer class="mgk-parent-package-options__note"><?php echo esc_html( $atts['note'] ?? '' ); ?></footer>
        <?php endif; ?>
    </div>
</section>
