<?php
/** S01 Why choose us. @var array $args — heading, body */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'why_heading' );
$body    = $args['body']    ?? mgk_site_setting( 'why_body' );
?>
    <section class="mgk-section mgk-section-dark mgk-home-why">
        <div class="mgk-shell">
            <?php mgk_render_section_heading( $heading, '', $body ); ?>
            <div class="mgk-why-grid">
                <?php
                $why = ( ! empty( $args['items'] ) && is_array( $args['items'] ) ) ? $args['items'] : mgk_site_home_why_items();
                foreach ( $why as $item ) :
                ?>
                    <div class="mgk-why-card">
                        <h3><?php echo esc_html( $item[0] ); ?></h3>
                        <p><?php echo esc_html( $item[1] ); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
