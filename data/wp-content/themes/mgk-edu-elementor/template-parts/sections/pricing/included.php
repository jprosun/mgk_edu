<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['pricing']['included'] ?? [];
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Always included free', '', 'Things you do not pay extra for.' ); ?>
        <div class="mgk-grid mgk-grid-3 mgk-included-grid">
            <?php foreach ( $items as $item ) : ?>
                <article class="mgk-card">
                    <span aria-hidden="true">&#10003;</span>
                    <h3><?php echo esc_html( $item['title'] ); ?></h3>
                    <p><?php echo esc_html( $item['body'] ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
