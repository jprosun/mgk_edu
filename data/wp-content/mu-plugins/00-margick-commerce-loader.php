<?php
/**
 * Plugin Name: Margick Commerce Loader (vendored)
 * Description: Loads the vendored margick/commerce package (PSR-4) into WordPress.
 *              Baked from the margick-modules source — DO NOT edit the package
 *              here; edit the source and re-vendor (build step).
 */
defined('ABSPATH') || exit;

$mgk_commerce_autoload = __DIR__ . '/margick-commerce/autoload.php';
if (is_file($mgk_commerce_autoload)) {
    require_once $mgk_commerce_autoload;
    if (function_exists('Margick\\Commerce\\bootstrap')) {
        \Margick\Commerce\bootstrap();
    }
}
