<?php

namespace StorePlugin\WooQuantity;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_Minmax_Quantities
 * @subpackage Woo_Minmax_Quantities/includes
 * @author     StorePlugin <contact@storeplugin.net>
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_textdomain() {

		load_plugin_textdomain(
			'minmax-quantities-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
