<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$filters = $args['filters'] ?? [];
$count = isset( $args['count'] ) ? (int) $args['count'] : 0;
$found_suffix = $args['found_suffix'] ?? 'tutors found';
$filter_label = $args['filter_label'] ?? 'Filter';
$sort_label   = $args['sort_label']   ?? 'Sort';
?>
<section class="mgk-listing-toolbar">
    <div>
        <h1><?php echo esc_html( number_format_i18n( max( $count, 0 ) ) . ' ' . $found_suffix ); ?></h1>
        <p><?php echo esc_html( ( $filters['area'] ?: 'Singapore' ) . ' · ' . mgk_budget_label( $filters ) ); ?></p>
    </div>
    <div class="mgk-toolbar-controls">
        <label><?php echo esc_html( $sort_label ); ?> <select data-mgk-sort aria-label="Sort tutor results">
            <?php foreach ( [
                'best-match' => 'Best Match',
                'rating' => 'Highest Rating',
                'reviews' => 'Most Reviews',
                'price-low' => 'Price Low-High',
                'price-high' => 'Price High-Low',
                'fastest' => 'Fastest Response',
            ] as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select></label>
        <div class="mgk-view-toggle" aria-label="View">
            <button type="button" class="is-active" data-mgk-view="grid">Grid</button>
            <button type="button" data-mgk-view="list">List</button>
        </div>
    </div>
</section>
