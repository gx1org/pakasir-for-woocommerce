<?php

/**
 * Plugin Name: Pakasir for WooCommerce
 * Plugin URI:  https://pakasir.com/
 * Description: Pakasir Payment Gateway (QRIS, Virtual Account, etc) for WooComerce. (compatible with Indonesia banks/e-wallets only)
 * Version:     1.3.1
 * Author:      PT. Geksa Eksplorasi Satu
 * Author URI:  https://gx1.org/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: pakasir-for-woocommerce
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
  exit;
}

// Load gateway after WooCommerce ready
function pakasir_init_gateway()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-pakasir.php';
  require_once __DIR__ . '/includes/class-wc-gateway-blocks-support.php';
}
add_action('plugins_loaded', 'pakasir_init_gateway');

// Add Manage link, next to deactivate plugin link
function pakasir_add_plugin_action_links($links)
{
  $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=pakasir') . '">Manage</a>';
  array_unshift($links, $settings_link);
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pakasir_add_plugin_action_links');


// Register the custom payment gateway with WooCommerce Blocks.
add_action(
  'woocommerce_blocks_payment_method_type_registration',
  function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
    // Register the custom payment gateway with the PaymentMethodRegistry.
    $payment_method_registry->register(new WC_Gateway_Blocks_Support());
  }
);
