<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$faqs = $args['pricing']['faqs'] ?? [];
?>
<section class="mgk-section mgk-section-surface">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Pricing FAQ</h2>
        </div>
        <?php get_template_part( 'template-parts/components/faq-accordion', null, [
            'items' => $faqs,
            'open_index' => 0,
            'id_prefix' => 'pricing-faq',
        ] ); ?>
    </div>
</section>
