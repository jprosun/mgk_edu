<?php
/** S01 Final CTA. @var array $args — heading, body, primary, secondary */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading   = $args['heading']   ?? mgk_site_setting( 'final_cta_heading' );
$body      = $args['body']      ?? mgk_site_setting( 'final_cta_body' );
$primary   = $args['primary']   ?? mgk_site_setting( 'final_cta_primary' );
$secondary = $args['secondary'] ?? mgk_site_setting( 'final_cta_secondary' );
?>
    <section class="mgk-section mgk-section-accent mgk-final-cta mgk-home-final-cta">
        <div class="mgk-shell">
            <h2><?php echo esc_html( $heading ); ?></h2>
            <p><?php echo esc_html( $body ); ?></p>
            <a class="mgk-btn mgk-btn-light" href="<?php echo esc_url( mgk_cta_url( 'find-tutor' ) ); ?>"><?php echo esc_html( $primary ); ?></a>
            <a class="mgk-btn mgk-btn-accent" style="border-color:#fff;" href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>"><?php echo esc_html( $secondary ); ?></a>
        </div>
    </section>
