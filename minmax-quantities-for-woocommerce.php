<?php

/**
 * Plugin Name:       MinMax Quantities for WooCommerce
 * Requires Plugins:  woocommerce
 * Plugin URI:        https://storeplugin.net
 * Description:       MinMax Quantities for WooCommerce plugin lets you set minimum and maximum quantities for items, orders, and amounts of the products in your store.
 * Version:           1.5.0
 * Author:            StorePlugin
 * Author URI:        https://storeplugin.net/plugins/minmax-quantities-for-woocommerce
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       minmax-quantities-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * PSR-4 Autoloader
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * Currently plugin version.
 */
define( 'WOO_MINMAX_QUANTITIES_VERSION', '1.5.0' );

/**
 * Plugin activation.
 */
function activate_woo_minmax_quantities() {
	\StorePlugin\WooQuantity\Activator::activate();
}

/**
 * Plugin deactivation.
 */
function deactivate_woo_minmax_quantities() {
	\StorePlugin\WooQuantity\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_minmax_quantities' );
register_deactivation_hook( __FILE__, 'deactivate_woo_minmax_quantities' );

/**
 * Add a link to the settings page from the plugins screen
 */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), function ( $links ) {
	$links[] = '<a href="' . admin_url( 'admin.php?page=sp_minmax_quantity_settings' ) . '">Settings</a>';
	$links[] = '<a href="https://storeplugin.net/plugins/minmax-quantities-for-woocommerce/?utm_source=activesite&utm_campaign=minmax&utm_medium=link" target="_blank">Get Pro</a>';
	return $links;
} );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action( 'plugins_loaded', 'run_woo_minmax_quantities' );
function run_woo_minmax_quantities() {

	( new \StorePlugin\WooQuantity\Init() )->run();

}

/**
 * Declare compatibility with WooCommerce HPOS
 *
 * @return void
 */
add_action( 'before_woocommerce_init', 'declare_hpos_compatibility' );
function declare_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
