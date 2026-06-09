<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$ctx = $args['context'] ?? [];
$links = $ctx['quick_links'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-quick-links">
    <div class="mgk-parent-dashboard__shell">
        <?php foreach ( $links as $link ) : ?>
            <a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>" data-event="dashboard_quick_link_click">
                <span><?php echo wp_kses_post( $link['icon'] ?? '' ); ?></span>
                <strong><?php echo esc_html( $link['label'] ?? '' ); ?></strong>
                <em><?php echo esc_html( $link['note'] ?? '' ); ?></em>
            </a>
        <?php endforeach; ?>
    </div>
</section>
