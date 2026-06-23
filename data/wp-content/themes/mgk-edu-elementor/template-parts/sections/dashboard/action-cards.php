<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx  = $args['context'] ?? [];
$package = $ctx['package'] ?? [];
$thread = $ctx['message_thread'] ?? [];
$billing = $ctx['billing'] ?? [];
?>
<section class="mgk-parent-dashboard mgk-parent-dashboard-actions-row">
    <div class="mgk-parent-dashboard__shell">
        <article class="mgk-parent-dashboard-action-card">
            <h2><?php echo esc_html( $atts['billing_heading'] ?? 'Billing & package' ); ?></h2>
            <div class="mgk-parent-dashboard-billing-line"><span><?php echo esc_html( $package['title'] ?? '' ); ?></span><strong><?php echo esc_html( $package['left'] ?? '' ); ?></strong></div>
            <div class="mgk-parent-dashboard-package-bar"><span></span></div>
            <p><?php echo esc_html( ( $package['used'] ?? '' ) . ' · ' . ( $package['method'] ?? '' ) ); ?></p>
            <?php if ( ! empty( $billing['has'] ) ) : ?>
                <p style="font-size:13px;color:#646970;margin:4px 0 0;">Last paid: <strong><?php echo esc_html( $billing['last_amount'] ); ?></strong> · <?php echo esc_html( $billing['last_method'] . ' · ' . $billing['last_date'] ); ?></p>
            <?php endif; ?>
            <?php $orders = $ctx['orders'] ?? []; if ( $orders ) : ?>
                <ul class="mgk-parent-dashboard-receipts" style="list-style:none;margin:8px 0 0;padding:8px 0 0;border-top:1px solid #f0f0f1;">
                    <?php foreach ( $orders as $o ) : ?>
                        <li style="display:flex;justify-content:space-between;gap:10px;font-size:12px;color:#646970;padding:3px 0;">
                            <span title="<?php echo esc_attr( $o['date'] ); ?>" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $o['label'] ); ?></span>
                            <strong style="white-space:nowrap;color:#1d2327;"><?php echo esc_html( $o['total'] ); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a class="mgk-parent-dashboard-btn mgk-parent-dashboard-btn--outline mgk-parent-dashboard-btn--full" href="<?php echo esc_url( $ctx['invoices_url'] ?? '#' ); ?>" data-event="billing_view_invoices_click"><?php echo esc_html( $atts['invoice_label'] ?? 'View invoices / receipts' ); ?></a>
        </article>
        <article class="mgk-parent-dashboard-action-card">
            <h2><?php echo esc_html( $atts['message_heading'] ?? 'Message tutor' ); ?></h2>
            <div class="mgk-parent-dashboard-message-tutor">
                <i aria-hidden="true"></i>
                <div><strong><?php echo esc_html( $thread['tutor'] ?? '' ); ?></strong><span><?php echo esc_html( $thread['status'] ?? '' ); ?></span></div>
            </div>
            <a class="mgk-parent-dashboard-btn mgk-parent-dashboard-btn--red mgk-parent-dashboard-btn--full" href="<?php echo esc_url( $thread['url'] ?? '#' ); ?>" data-event="message_tutor_click"><?php echo esc_html( $atts['chat_label'] ?? 'Open chat →' ); ?></a>
            <p><?php echo esc_html( $atts['message_note'] ?? 'AGENCY-MONITORED · PHONE MASKED' ); ?></p>
        </article>
        <article class="mgk-parent-dashboard-action-card mgk-parent-dashboard-action-card--buy">
            <h2><?php echo esc_html( $atts['buy_heading'] ?? 'Need more lessons?' ); ?></h2>
            <p><?php echo esc_html( $atts['buy_copy'] ?? 'BUY A NEW PACKAGE & SAVE UP TO 10%' ); ?></p>
            <a class="mgk-parent-dashboard-btn mgk-parent-dashboard-btn--red mgk-parent-dashboard-btn--full" href="<?php echo esc_url( $ctx['buy_package_url'] ?? '#' ); ?>" data-event="buy_package_click"><?php echo esc_html( $atts['buy_label'] ?? 'Buy Package (FR-BOOK-08) →' ); ?></a>
            <p><?php echo esc_html( $atts['buy_note'] ?? 'RETURNING-STUDENT 5% MAY APPLY (BR-06)' ); ?></p>
        </article>
    </div>
</section>
