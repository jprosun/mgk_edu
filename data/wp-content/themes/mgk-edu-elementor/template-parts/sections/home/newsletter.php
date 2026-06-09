<?php
/** S01 Newsletter. @var array $args — heading, body, button */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'newsletter_heading' );
$body    = $args['body']    ?? mgk_site_setting( 'newsletter_body' );
$button  = $args['button']  ?? mgk_site_setting( 'newsletter_button' );
?>
    <section class="mgk-section-sm mgk-section-surface mgk-home-newsletter">
        <div class="mgk-shell mgk-newsletter-grid">
            <div>
                <h2><?php echo esc_html( $heading ); ?></h2>
                <p class="mgk-muted"><?php echo esc_html( $body ); ?></p>
            </div>
            <form class="mgk-newsletter-form" data-mgk-newsletter>
                <input type="email" name="email" placeholder="EMAIL" required>
                <button class="mgk-btn mgk-btn-accent" type="submit"><?php echo esc_html( $button ); ?></button>
                <p class="mgk-form-message" data-mgk-form-message></p>
            </form>
        </div>
    </section>
