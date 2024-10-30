<?php

namespace StorePlugin\WooQuantity;

/**
 * Frontend and Admin Assets for Minmax Quantity
 *
 * @package    Woo_Minmax_Quantities
 * @subpackage Woo_Minmax_Quantities/public
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Assets {
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		\add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );
		\add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'client_styles' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'client_scripts' ] );

		// Plugin info data
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function client_styles() {
		wp_enqueue_style( $this->plugin_name, plugins_url('', __DIR__) . '/assets/public/css/woo-minmax-quantities-public.css', array(), $this->version, 'all' );

	}

    /**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function client_scripts() {
		wp_enqueue_script( $this->plugin_name, plugins_url('', __DIR__) . '/assets/public/js/woo-minmax-quantities-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_styles() {
		wp_enqueue_style($this->plugin_name, plugins_url('', __DIR__) . '/assets/admin/css/woo-minmax-quantities-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_script($this->plugin_name, plugins_url('', __DIR__) . '/assets/admin/js/woo-minmax-quantities-admin.js', array('jquery'), $this->version, false);

	}
}
