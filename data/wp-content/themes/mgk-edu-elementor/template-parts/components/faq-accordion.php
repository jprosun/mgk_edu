<?php
/**
 * Reusable FAQ accordion.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$items = $args['items'] ?? [];
$open_index = isset( $args['open_index'] ) ? (int) $args['open_index'] : 0;
$id_prefix = sanitize_key( $args['id_prefix'] ?? 'mgk-faq' );

if ( empty( $items ) ) {
    return;
}
?>
<div class="mgk-faq-list">
    <?php foreach ( $items as $index => $faq ) : ?>
        <?php
        $is_open = $index === $open_index;
        $button_id = $id_prefix . '-button-' . $index;
        $panel_id = $id_prefix . '-panel-' . $index;
        ?>
        <div class="mgk-faq-item<?php echo $is_open ? ' is-open' : ''; ?>">
            <button id="<?php echo esc_attr( $button_id ); ?>" type="button" data-mgk-faq-button data-event="faq_toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
                <span><?php echo esc_html( $faq['q'] ?? '' ); ?></span><span class="mgk-faq-icon" aria-hidden="true"></span>
            </button>
            <div id="<?php echo esc_attr( $panel_id ); ?>" class="mgk-faq-answer" role="region" aria-labelledby="<?php echo esc_attr( $button_id ); ?>">
                <?php echo esc_html( $faq['a'] ?? '' ); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
