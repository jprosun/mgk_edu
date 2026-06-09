<?php
/** S01 Pricing teaser. @var array $args — heading, body, cta, calculator_title */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading          = $args['heading']          ?? mgk_site_setting( 'pricing_heading' );
$body             = $args['body']             ?? mgk_site_setting( 'pricing_body' );
$cta              = $args['cta']              ?? mgk_site_setting( 'pricing_cta' );
$calculator_title = $args['calculator_title'] ?? mgk_site_setting( 'calculator_title' );
?>
    <section class="mgk-section mgk-home-pricing">
        <div class="mgk-shell mgk-pricing-grid">
            <div>
                <?php mgk_render_section_heading( $heading, '', $body ); ?>
                <ul>
                    <?php foreach ( mgk_site_lines( 'pricing_lines' ) as $line ) : ?>
                        <li><?php echo esc_html( $line ); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( mgk_cta_url( 'pricing' ) ); ?>"><?php echo esc_html( $cta ); ?></a>
            </div>
            <div class="mgk-card mgk-calculator">
                <h3><?php echo esc_html( $calculator_title ); ?></h3>
                <?php foreach ( mgk_site_lines( 'calculator_rows' ) as $line ) : ?>
                    <div class="mgk-calculator-row"><?php echo esc_html( $line ); ?></div>
                <?php endforeach; ?>
                <div class="mgk-price-result"><strong><?php echo esc_html( mgk_site_setting( 'calculator_result' ) ); ?></strong><br><span><?php echo esc_html( mgk_site_setting( 'calculator_note' ) ); ?></span></div>
            </div>
        </div>
    </section>
