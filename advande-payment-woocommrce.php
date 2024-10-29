<?php
/**
 * Plugin Name: Advance Payment for WooCommerce
 * Plugin URI: https://github.com/shameemreza/wc-advance-payment
 * Description: Accept advance payment on your WooCommerce store to avoid spam or fake order.
 * Version: 1.0
 * Tested up to: 6.5.2
 * Author: Shameem Reza
 * Author URI: https://shameem.dev/
 * License: GPL2 or later
 * Text Domain: advance-payment-wc
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Translation
 */

function advance_payment_wc_load_plugin_textdomain() {
  load_plugin_textdomain('advance-payment-wc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'advance_payment_wc_load_plugin_textdomain');

/**
 * Load the plugin
 */
function load_advance_payment_plugin() {
  if (class_exists('WooCommerce')) {
      require_once 'advance-payment-class-settings.php';
      require_once 'advance-payment-class.php';
  }
}

add_action('plugins_loaded', 'load_advance_payment_plugin');
