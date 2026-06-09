<?php
/**
 * MGK site header.
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'mgk-edu' ); ?></a>
<div id="page" class="mgk-site">
    <header class="mgk-site-header" data-mgk-header>
        <?php echo do_shortcode( mgk_site_topbar_shortcode() ); ?>
        <div class="mgk-nav-wrap">
            <div class="mgk-shell mgk-nav">
                <?php echo mgk_site_logo_html( 'mgk-logo' ); ?>
                <nav class="mgk-primary-nav" aria-label="Primary navigation">
                    <?php mgk_site_render_menu( 'mgk_primary', 'mgk-primary-nav-links' ); ?>
                </nav>
                <div class="mgk-nav-actions">
                    <?php $mgk_account = mgk_site_account_link(); ?>
                    <a class="mgk-btn mgk-btn-outline" href="<?php echo esc_url( $mgk_account['url'] ); ?>"><?php echo esc_html( $mgk_account['label'] ); ?></a>
                    <a class="mgk-btn mgk-btn-accent" href="<?php echo esc_url( mgk_cta_url( 'find-tutor' ) ); ?>" data-event="cta_click" data-cta="header_find_tutor"><?php echo esc_html( mgk_site_setting( 'header_primary_label' ) ); ?></a>
                </div>
                <button class="mgk-menu-toggle" type="button" aria-expanded="false" aria-controls="mgk-mobile-nav" aria-label="<?php esc_attr_e( 'Open menu', 'mgk-edu' ); ?>" data-mgk-menu-toggle data-event="mobile_menu_click"><span class="mgk-menu-toggle__icon" aria-hidden="true">&#9776;</span><span class="screen-reader-text"><?php esc_html_e( 'Menu', 'mgk-edu' ); ?></span></button>
            </div>
            <nav id="mgk-mobile-nav" class="mgk-mobile-nav" aria-label="Mobile navigation" hidden>
                <?php mgk_site_render_menu( 'mgk_primary', 'mgk-mobile-nav-links' ); ?>
            </nav>
        </div>
    </header>
    <main id="main" class="mgk-main">
