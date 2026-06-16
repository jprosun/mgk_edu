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
            <span style="display:inline-flex;align-items:center;gap:12px;margin-top:6px;">
                <a href="<?php echo esc_url( $ctx['messages_url'] ?? mgk_url( '/parent/messages/' ) ); ?>" data-event="notification_badge_click" style="position:relative;display:inline-block;font-size:18px;text-decoration:none;color:#3c434a;" aria-label="Messages">
                    &#128276;<?php $u = (int) ( $ctx['unread_total'] ?? 0 ); ?>
                    <b data-mgk-notif-badge data-count="<?php echo (int) $u; ?>" style="<?php echo $u ? '' : 'display:none;'; ?>position:absolute;top:-6px;right:-10px;min-width:16px;height:16px;padding:0 4px;border-radius:9px;background:#d34836;color:#fff;font:700 10px/16px -apple-system,Arial,sans-serif;text-align:center;"><?php echo $u ? esc_html( $u > 99 ? '99+' : $u ) : ''; ?></b>
                </a>
                <?php if ( ! empty( $ctx['logout_url'] ) ) : ?>
                    <a class="mgk-parent-dashboard-welcome__logout" href="<?php echo esc_url( $ctx['logout_url'] ); ?>" data-event="parent_logout_click" style="font-size:13px;color:#646970;text-decoration:underline;"><?php echo esc_html( $atts['logout_label'] ?? 'Log out' ); ?></a>
                <?php endif; ?>
            </span>
        </div>
        <?php if ( ! mgk_parent_bool( $atts['hide_switcher'] ?? '' ) ) : ?>
            <div class="mgk-parent-dashboard-switcher">
                <span><?php echo esc_html( $atts['viewing_label'] ?? 'VIEWING' ); ?></span>
                <?php foreach ( $children as $child ) :
                    $cid       = $child['id'] ?? '';
                    $is_active = ! empty( $child['active'] );
                    // Switching = reload the dashboard for this child (?child=<id>);
                    // every widget re-renders server-side for the selection.
                    $switch_url = add_query_arg( 'child', rawurlencode( (string) $cid ) );
                ?>
                    <a href="<?php echo esc_url( $switch_url ); ?>"
                       class="mgk-parent-dashboard-switcher__child<?php echo $is_active ? ' is-active' : ''; ?>"
                       data-event="child_switcher_click" data-child-id="<?php echo esc_attr( $cid ); ?>"
                       <?php echo $is_active ? 'aria-current="true"' : ''; ?>>
                        <strong><?php echo esc_html( $child['name'] ?? '' ); ?></strong>
                        <em>(<?php echo esc_html( $child['level'] ?? '' ); ?>)<?php echo $is_active ? ' ▾' : ''; ?></em>
                    </a>
                <?php endforeach; ?>
                <a href="<?php echo esc_url( $ctx['add_child_url'] ?? mgk_url( '/request-match/' ) ); ?>" class="mgk-parent-dashboard-switcher__add" data-event="add_child_click"><?php echo esc_html( $atts['add_child_label'] ?? '+ Add child' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
