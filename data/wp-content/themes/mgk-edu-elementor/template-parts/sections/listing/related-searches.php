<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$heading = $args['related_heading'] ?? 'Related searches';
$links = isset( $args['links'] ) && is_array( $args['links'] ) ? $args['links'] : [];
?>
<section class="mgk-related-searches">
    <h2><?php echo esc_html( $heading ); ?></h2>
    <div class="mgk-grid mgk-grid-2">
        <?php foreach ( $links as $link ) : ?>
            <a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>"><?php echo esc_html( strtoupper( $link['label'] ?? '' ) ); ?></a>
        <?php endforeach; ?>
    </div>
</section>
