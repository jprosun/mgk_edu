<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['pricing']['not_included'] ?? [];
?>
<section class="mgk-section mgk-section-surface">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'What you will pay separately', '', 'Honest list of additional charges so you are never surprised.' ); ?>
        <div class="mgk-grid mgk-grid-2 mgk-extra-grid">
            <?php foreach ( $items as $item ) : ?>
                <article class="mgk-card">
                    <h3><?php echo esc_html( $item['title'] ); ?></h3>
                    <strong><?php echo esc_html( $item['cost'] ); ?></strong>
                    <p><?php echo esc_html( $item['body'] ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
