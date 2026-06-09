<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts   = $args['atts'] ?? [];
$ctx    = $args['context'] ?? [];
$avatar = strtoupper( substr( (string) ( $ctx['child_name'] ?? 'Emma' ), 0, 1 ) );
?>
<section class="mgk-parent-package mgk-parent-package-context">
    <div class="mgk-parent-package__shell">
        <?php if ( ! mgk_parent_bool( $atts['hide_sec_label'] ?? '' ) ) : ?>
            <b class="mgk-parent-package-sec"><?php echo esc_html( $atts['sec_label'] ?? '' ); ?></b>
        <?php endif; ?>

        <div class="mgk-parent-package-context__row">
            <?php if ( ! mgk_parent_bool( $atts['hide_avatar'] ?? '' ) ) : ?>
                <span class="mgk-parent-package-avatar" aria-hidden="true"><?php echo esc_html( $avatar ); ?></span>
            <?php endif; ?>

            <div class="mgk-parent-package-context__copy">
                <?php if ( ! mgk_parent_bool( $atts['hide_headline'] ?? '' ) ) : ?>
                    <h1><?php echo esc_html( $ctx['headline'] ?? '' ); ?></h1>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_meta'] ?? '' ) ) : ?>
                    <p class="mgk-parent-package-context__meta"><?php echo esc_html( $ctx['meta'] ?? '' ); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( ! mgk_parent_bool( $atts['hide_prompt'] ?? '' ) ) : ?>
            <p class="mgk-parent-package-context__prompt"><?php echo esc_html( $ctx['prompt'] ?? '' ); ?></p>
        <?php endif; ?>
    </div>
</section>
