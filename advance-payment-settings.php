<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Add settings page to WooCommerce settings menu
add_filter('woocommerce_get_settings_pages', 'advance_payment_wc_settings_page');
function advance_payment_wc_settings_page($settings)
{
    $settings[] = include dirname(__FILE__) . '/advance-payment-settings.php';
    return $settings;
}