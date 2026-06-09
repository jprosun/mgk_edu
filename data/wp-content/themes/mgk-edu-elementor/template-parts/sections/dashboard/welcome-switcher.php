<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx  = $args['context'] ?? [];
$children = $ctx['children'] ?? [];
$active = $ctx['active_child'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-welcome" data-event="parent_dashboard_view">
    <div class="mgk-parent-dashboard__shell">
        <div class="mgk-parent-dashboard-welcome__copy">
            <h1>
                <span><?php echo esc_html( $atts['welcome_prefix'] ?? 'Welcome back,' ); ?></span>
                <strong><?php echo esc_html( $ctx['parent_name'] ?? 'Mrs Tan' ); ?></strong>
            </h1>
            <?php if ( ! mgk_parent_bool( $atts['hide_subline'] ?? '' ) ) : ?>
                <p><?php echo esc_html( $ctx['date_line'] ?? '' ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( ! mgk_parent_bool( $atts['hide_switcher'] ?? '' ) ) : ?>
            <div class="mgk-parent-dashboard-switcher">
                <span><?php echo esc_html( $atts['viewing_label'] ?? 'VIEWING' ); ?></span>
                <?php foreach ( $children as $child ) : ?>
                    <button type="button" class="<?php echo ! empty( $child['active'] ) ? 'is-active' : ''; ?>" data-event="child_switcher_click">
                        <strong><?php echo esc_html( $child['name'] ?? '' ); ?></strong>
                        <em>(<?php echo esc_html( $child['level'] ?? '' ); ?>)<?php echo ! empty( $child['active'] ) ? ' ▾' : ''; ?></em>
                    </button>
                <?php endforeach; ?>
                <a href="#" class="mgk-parent-dashboard-switcher__add" data-event="child_switcher_click"><?php echo esc_html( $atts['add_child_label'] ?? '+ Add child' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
