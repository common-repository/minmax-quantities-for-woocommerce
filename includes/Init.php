<?php

namespace StorePlugin\WooQuantity;

use StorePlugin\WooQuantity\I18n;
use StorePlugin\WooQuantity\Admin\Admin;
use StorePlugin\WooQuantity\Admin\Settings;
use StorePlugin\WooQuantity\Clients\Clients;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Minmax_Quantities
 * @subpackage Woo_Minmax_Quantities/includes
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Init {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOO_MINMAX_QUANTITIES_VERSION' ) ) {
			$this->version = WOO_MINMAX_QUANTITIES_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'minmax-quantities-for-woocommerce';

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		new Assets( $this->plugin_name, $this->get_version() );
		( new I18n() )->load_textdomain();
		new Admin();
		new Settings();
		new Clients();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
