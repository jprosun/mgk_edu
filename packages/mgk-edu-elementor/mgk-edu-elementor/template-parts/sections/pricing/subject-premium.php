<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$items = $args['pricing']['subject_premiums'] ?? [];
?>
<section class="mgk-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Subject premium adjustments', '', 'Some subjects command premium due to demand or specialization.' ); ?>
        <div class="mgk-grid mgk-grid-4 mgk-premium-grid">
            <?php foreach ( $items as $item ) : ?>
                <article class="mgk-card">
                    <h3><?php echo esc_html( $item['title'] ); ?></h3>
                    <strong><?php echo esc_html( $item['premium'] ); ?></strong>
                    <p><?php echo esc_html( $item['body'] ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
