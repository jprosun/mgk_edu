<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$atts = $args['atts'] ?? [];
$ctx = $args['context'] ?? [];
$state = (string) ( $ctx['state'] ?? 'default' );
$parent = (array) ( $ctx['parent'] ?? [] );
$payments = (array) ( $ctx['payments'] ?? [] );
$children = (array) ( $ctx['children'] ?? [] );
$languages = (array) ( $ctx['languages'] ?? [] );

$section_label = function ( $key ) use ( $atts ) {
    if ( function_exists( 'mgk_parent_bool' ) && mgk_parent_bool( $atts[ 'hide_' . $key ] ?? '' ) ) {
        return;
    }
    $label = $atts[ 'sec_' . $key ] ?? '';
    if ( $label !== '' ) {
        echo '<span class="mgk-parent-account-sec">' . esc_html( $label ) . '</span>';
    }
};

$state_head = function ( $title, $kicker ) {
    echo '<header class="mgk-parent-account-state-head"><strong>' . esc_html( $title ) . '</strong><span>' . esc_html( $kicker ) . '</span></header>';
};
?>
<section class="mgk-parent-account mgk-parent-account--<?php echo esc_attr( $state ); ?>">
    <?php if ( $state === 'otp' && ! mgk_parent_bool( $atts['hide_otp'] ?? '' ) ) : ?>
        <div class="mgk-parent-account-state">
            <?php $state_head( $atts['otp_title'] ?? '', $atts['otp_kicker'] ?? '' ); ?>
            <div class="mgk-parent-account-state-shell">
                <h1><?php echo esc_html( $atts['otp_heading'] ?? '' ); ?></h1>
                <div class="mgk-parent-account-otp-new"><?php echo esc_html( $atts['otp_new_label'] ?? '' ); ?></div>
                <div class="mgk-parent-account-otp-box">
                    <h2><?php echo esc_html( $atts['otp_verify_title'] ?? '' ); ?></h2>
                    <p><?php echo esc_html( $atts['otp_verify_body'] ?? '' ); ?></p>
                    <div class="mgk-parent-account-otp-digits" aria-label="OTP code">
                        <?php for ( $i = 0; $i < 6; $i++ ) : ?>
                            <input type="text" inputmode="numeric" maxlength="1" aria-label="<?php echo esc_attr( 'Digit ' . ( $i + 1 ) ); ?>">
                        <?php endfor; ?>
                    </div>
                    <div class="mgk-parent-account-otp-actions">
                        <button type="button"><?php echo esc_html( $atts['otp_button'] ?? '' ); ?></button>
                        <span><?php echo esc_html( $atts['otp_resend'] ?? '' ); ?></span>
                    </div>
                </div>
                <footer><?php echo esc_html( $atts['otp_note'] ?? '' ); ?></footer>
            </div>
        </div>
    <?php elseif ( $state === 'dsar-export' && ! mgk_parent_bool( $atts['hide_export_state'] ?? '' ) ) : ?>
        <div class="mgk-parent-account-state">
            <?php $state_head( $atts['export_state_title'] ?? '', $atts['export_state_kicker'] ?? '' ); ?>
            <div class="mgk-parent-account-state-shell">
                <h1><?php echo esc_html( $atts['export_state_heading'] ?? '' ); ?></h1>
                <p><?php echo esc_html( $atts['export_state_body'] ?? '' ); ?></p>
                <div class="mgk-parent-account-export-format">
                    <strong><?php echo esc_html( $atts['export_format'] ?? '' ); ?></strong>
                    <span><?php echo esc_html( $atts['export_delivery'] ?? '' ); ?></span>
                </div>
                <div class="mgk-parent-account-export-status"><?php echo esc_html( $atts['export_status'] ?? '' ); ?></div>
                <button type="button" class="mgk-parent-account-primary"><?php echo esc_html( $atts['export_state_button'] ?? '' ); ?></button>
                <footer><?php echo esc_html( $atts['export_state_note'] ?? '' ); ?></footer>
            </div>
        </div>
    <?php elseif ( $state === 'delete-account' && ! mgk_parent_bool( $atts['hide_delete_state'] ?? '' ) ) : ?>
        <div class="mgk-parent-account-state">
            <?php $state_head( $atts['delete_state_title'] ?? '', $atts['delete_state_kicker'] ?? '' ); ?>
            <div class="mgk-parent-account-state-shell mgk-parent-account-delete">
                <span class="mgk-parent-account-destructive">Destructive</span>
                <h1><?php echo esc_html( $atts['delete_state_heading'] ?? '' ); ?></h1>
                <div class="mgk-parent-account-delete-warning"><?php echo esc_html( $atts['delete_warning'] ?? '' ); ?></div>
                <label>
                    <?php echo esc_html( $atts['delete_confirm_label'] ?? '' ); ?>
                    <input type="text" placeholder="______">
                </label>
                <div class="mgk-parent-account-delete-actions">
                    <a href="<?php echo esc_url( mgk_get_parent_account_url() ); ?>"><?php echo esc_html( $atts['delete_cancel'] ?? '' ); ?></a>
                    <button type="button"><?php echo esc_html( $atts['delete_permanent'] ?? '' ); ?></button>
                </div>
                <footer><?php echo esc_html( $atts['delete_state_note'] ?? '' ); ?></footer>
            </div>
        </div>
    <?php else : ?>
        <div class="mgk-parent-account__shell">
            <?php if ( ! mgk_parent_bool( $atts['hide_nav'] ?? '' ) ) : ?>
                <aside class="mgk-parent-account-nav">
                    <?php $section_label( 'nav' ); ?>
                    <a class="is-active" href="#profile"><?php echo esc_html( $atts['nav_profile'] ?? '' ); ?></a>
                    <a href="#payment"><?php echo esc_html( $atts['nav_payment'] ?? '' ); ?></a>
                    <a href="#children"><?php echo esc_html( $atts['nav_children'] ?? '' ); ?></a>
                    <a href="<?php echo esc_url( mgk_url( '/parent/notifications/' ) ); ?>"><?php echo esc_html( $atts['nav_notifications'] ?? '' ); ?></a>
                    <a href="#language"><?php echo esc_html( $atts['nav_language'] ?? '' ); ?></a>
                    <a class="is-danger" href="#dsar"><?php echo esc_html( $atts['nav_dsar'] ?? '' ); ?></a>
                </aside>
            <?php endif; ?>

            <div class="mgk-parent-account-main">
                <?php if ( ! mgk_parent_bool( $atts['hide_profile'] ?? '' ) ) : ?>
                    <section id="profile" class="mgk-parent-account-profile">
                        <?php $section_label( 'profile' ); ?>
                        <h1><?php echo esc_html( $atts['profile_title'] ?? '' ); ?></h1>
                        <div class="mgk-parent-account-field-grid">
                            <article><span><?php echo esc_html( $atts['full_name_label'] ?? '' ); ?></span><strong><?php echo esc_html( $parent['name'] ?? '' ); ?></strong></article>
                            <article><span><?php echo esc_html( $atts['email_label'] ?? '' ); ?></span><strong><?php echo esc_html( $parent['email'] ?? '' ); ?></strong><a href="<?php echo esc_url( mgk_get_parent_account_url( 'otp', [ 'type' => 'email' ] ) ); ?>"><?php echo esc_html( $atts['change_otp_label'] ?? '' ); ?></a></article>
                            <article><span><?php echo esc_html( $atts['phone_label'] ?? '' ); ?></span><strong><?php echo esc_html( $parent['phone'] ?? '' ); ?></strong><a href="<?php echo esc_url( mgk_get_parent_account_url( 'otp', [ 'type' => 'phone' ] ) ); ?>"><?php echo esc_html( $atts['change_otp_label'] ?? '' ); ?></a></article>
                            <article><span><?php echo esc_html( $atts['password_label'] ?? '' ); ?></span><strong><?php echo esc_html( $parent['password'] ?? '' ); ?></strong><a href="<?php echo esc_url( mgk_get_parent_account_url( 'otp', [ 'type' => 'password' ] ) ); ?>"><?php echo esc_html( $atts['password_update_label'] ?? '' ); ?></a></article>
                        </div>
                        <a href="<?php echo esc_url( mgk_get_parent_account_url( 'otp', [ 'type' => 'profile' ] ) ); ?>" class="mgk-parent-account-secondary"><?php echo esc_html( $atts['edit_profile_label'] ?? '' ); ?></a>
                    </section>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_payment'] ?? '' ) ) : ?>
                    <section id="payment" class="mgk-parent-account-payment">
                        <?php $section_label( 'payment' ); ?>
                        <h2><?php echo esc_html( $atts['payment_title'] ?? '' ); ?></h2>
                        <div class="mgk-parent-account-payment-grid">
                            <?php foreach ( $payments as $payment ) : ?>
                                <article class="<?php echo ! empty( $payment['active'] ) ? 'is-active' : ''; ?>">
                                    <strong><?php echo esc_html( $payment['type'] ?? '' ); ?> · <?php echo esc_html( $payment['label'] ?? '' ); ?></strong>
                                    <span><?php echo esc_html( $payment['meta'] ?? '' ); ?></span>
                                </article>
                            <?php endforeach; ?>
                            <button type="button"><?php echo esc_html( $atts['add_payment_label'] ?? '' ); ?></button>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_children'] ?? '' ) ) : ?>
                    <section id="children" class="mgk-parent-account-children">
                        <?php $section_label( 'children' ); ?>
                        <h2><?php echo esc_html( $atts['children_title'] ?? '' ); ?></h2>
                        <div class="mgk-parent-account-child-grid">
                            <?php foreach ( $children as $child ) : ?>
                                <article>
                                    <div aria-hidden="true"></div>
                                    <strong><?php echo esc_html( $child['name'] ?? '' ); ?></strong>
                                    <span><?php echo esc_html( $child['meta'] ?? '' ); ?></span>
                                    <em><?php echo esc_html( $atts['child_edit_label'] ?? '' ); ?></em>
                                </article>
                            <?php endforeach; ?>
                            <button type="button"><?php echo esc_html( $atts['add_child_label'] ?? '' ); ?></button>
                        </div>
                        <p><?php echo esc_html( $atts['children_note'] ?? '' ); ?></p>
                    </section>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_pref_lang'] ?? '' ) ) : ?>
                    <section id="language" class="mgk-parent-account-pref-lang">
                        <?php $section_label( 'pref_lang' ); ?>
                        <article>
                            <h2><?php echo esc_html( $atts['notifications_title'] ?? '' ); ?></h2>
                            <p><?php echo esc_html( $atts['notifications_body'] ?? '' ); ?></p>
                            <a href="<?php echo esc_url( mgk_url( '/parent/notifications/' ) ); ?>"><?php echo esc_html( $atts['notifications_button'] ?? '' ); ?></a>
                        </article>
                        <article>
                            <h2><?php echo esc_html( $atts['language_title'] ?? '' ); ?></h2>
                            <div class="mgk-parent-account-lang-buttons">
                                <?php foreach ( $languages as $i => $language ) : ?>
                                    <button type="button" class="<?php echo $i === 0 ? 'is-active' : ''; ?>"><?php echo esc_html( $language ); ?></button>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    </section>
                <?php endif; ?>

                <?php if ( ! mgk_parent_bool( $atts['hide_dsar'] ?? '' ) ) : ?>
                    <section id="dsar" class="mgk-parent-account-dsar">
                        <?php $section_label( 'dsar' ); ?>
                        <h2><?php echo esc_html( $atts['dsar_title'] ?? '' ); ?></h2>
                        <div class="mgk-parent-account-dsar-grid">
                            <article>
                                <h3><?php echo esc_html( $atts['export_title'] ?? '' ); ?></h3>
                                <p><?php echo esc_html( $atts['export_body'] ?? '' ); ?></p>
                                <a href="<?php echo esc_url( mgk_get_parent_account_url( 'dsar-export' ) ); ?>"><?php echo esc_html( $atts['export_button'] ?? '' ); ?></a>
                            </article>
                            <article class="is-danger">
                                <h3><?php echo esc_html( $atts['delete_title'] ?? '' ); ?></h3>
                                <p><?php echo esc_html( $atts['delete_body'] ?? '' ); ?></p>
                                <a href="<?php echo esc_url( mgk_get_parent_account_url( 'delete-account' ) ); ?>"><?php echo esc_html( $atts['delete_button'] ?? '' ); ?></a>
                            </article>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
