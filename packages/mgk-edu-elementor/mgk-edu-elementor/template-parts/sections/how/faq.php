<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$groups = $args['how']['faq_groups'] ?? [];
?>
<section id="mgk-how-faq" class="mgk-section mgk-how-faq">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Frequently Asked Questions</h2>
            <p>Everything you need to know before booking.</p>
        </div>
        <div class="mgk-how-faq-groups">
            <?php foreach ( $groups as $title => $items ) : ?>
                <section class="mgk-how-faq-group">
                    <h3><?php echo esc_html( $title ); ?></h3>
                    <?php get_template_part( 'template-parts/components/faq-accordion', null, [
                        'items' => $items,
                        'open_index' => 0,
                        'id_prefix' => 'how-' . sanitize_title( $title ),
                    ] ); ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>
