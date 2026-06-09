<?php
/**
 * Listing filter sidebar/drawer body.
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$filters = $args['filters'] ?? [];
$groups = mgk_generate_listing_filters();
$chips = mgk_active_filter_chips( $filters );
$f_heading   = $args['filters_heading'] ?? 'Filters';
$f_apply     = $args['apply_label']      ?? 'Apply';
$f_apply_all = $args['apply_all_label']  ?? 'Apply Filters';
$f_clear     = $args['clear_label']      ?? 'Clear All Filters';
?>
<form class="mgk-filter-panel" action="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>" method="get" data-mgk-filter-form>
    <?php if ( ! empty( $filters['sort'] ) && $filters['sort'] !== 'best-match' ) : ?>
        <input type="hidden" name="sort" value="<?php echo esc_attr( $filters['sort'] ); ?>">
    <?php endif; ?>

    <div class="mgk-filter-head">
        <h2><?php echo esc_html( $f_heading ); ?></h2>
        <button class="mgk-filter-apply-head" type="submit" aria-label="Apply filters"><?php echo esc_html( $f_apply ); ?></button>
    </div>

    <?php foreach ( $groups as $group => $items ) : ?>
        <?php $filter_key = mgk_filter_key_for_group( $group ); ?>
        <fieldset class="mgk-filter-group">
            <legend><?php echo esc_html( $group ); ?></legend>
            <?php foreach ( $items as $item ) : ?>
                <?php
                $value = mgk_filter_value_from_item( $item );
                $checked = $filter_key && mgk_is_filter_active( $filters, $filter_key, $value );
                $input_name = $filter_key === 'budget' ? 'budget' : $filter_key . '[]';
                ?>
                <label class="mgk-check-row<?php echo $checked ? ' is-active' : ''; ?>">
                    <input type="<?php echo $filter_key === 'budget' ? 'radio' : 'checkbox'; ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked ); ?>>
                    <span><?php echo esc_html( $item ); ?></span>
                </label>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>
</form>
