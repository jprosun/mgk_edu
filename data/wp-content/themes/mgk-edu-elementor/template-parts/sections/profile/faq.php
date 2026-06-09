<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$tutor = $args['tutor'] ?? [];
?>
<section class="mgk-section mgk-section-surface">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Tutor-specific FAQ' ); ?>
        <div class="mgk-faq-list">
            <?php foreach ( $tutor['faqs'] as $index => $faq ) : ?>
                <div class="mgk-faq-item<?php echo $index === 0 ? ' is-open' : ''; ?>">
                    <button type="button" data-mgk-faq-button aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                        <span><?php echo esc_html( $faq['q'] ); ?></span><span aria-hidden="true">v</span>
                    </button>
                    <div class="mgk-faq-answer"><?php echo esc_html( $faq['a'] ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
