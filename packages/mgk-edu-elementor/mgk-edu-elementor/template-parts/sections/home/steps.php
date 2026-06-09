<?php
/** S01 Steps. @var array $args — heading, body */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'steps_heading' );
$body    = $args['body']    ?? mgk_site_setting( 'steps_body' );
?>
    <section class="mgk-section mgk-home-steps">
        <div class="mgk-shell">
            <?php mgk_render_section_heading( $heading, '', $body ); ?>
            <div class="mgk-grid mgk-grid-4">
                <?php
                $steps = ( ! empty( $args['items'] ) && is_array( $args['items'] ) ) ? $args['items'] : mgk_site_home_steps();
                foreach ( $steps as $index => $step ) :
                ?>
                    <div class="mgk-step">
                        <span class="mgk-step-num"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
                        <h3><?php echo esc_html( $step[0] ); ?></h3>
                        <p><?php echo esc_html( $step[1] ); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
