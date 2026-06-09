<?php
/** S01 FAQ. @var array $args — heading */
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['heading'] ?? mgk_site_setting( 'faq_heading' );
$faqs    = ( ! empty( $args['items'] ) && is_array( $args['items'] ) ) ? $args['items'] : mgk_get_faqs();
?>
    <section class="mgk-section mgk-section-surface mgk-home-faq">
        <div class="mgk-shell">
            <?php mgk_render_section_heading( $heading ); ?>
            <div class="mgk-faq-list">
                <?php foreach ( $faqs as $index => $faq ) : ?>
                    <div class="mgk-faq-item<?php echo $index === 1 ? ' is-open' : ''; ?>">
                        <button type="button" data-mgk-faq-button aria-expanded="<?php echo $index === 1 ? 'true' : 'false'; ?>">
                            <span><?php echo esc_html( $faq['q'] ); ?></span><span aria-hidden="true">v</span>
                        </button>
                        <div class="mgk-faq-answer"><?php echo esc_html( $faq['a'] ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
