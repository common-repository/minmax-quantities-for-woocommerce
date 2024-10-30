<?php

namespace StorePlugin\WooQuantity\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Woo_Minmax_Quantities
 * @subpackage Woo_Minmax_Quantities/admin
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Admin {

	public function __construct() {
		// Product Panel Tab
		\add_action( 'woocommerce_product_options_general_product_data', [ $this, 'product_meta_fields' ] );
		\add_action( 'woocommerce_process_product_meta', [ $this, 'save_meta_fields' ] );

		// Product Variation fields
		\add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'product_variation_meta_fields' ], 10, 3 );
		\add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_meta_fields' ] );
	}

	/**
	 * Adding metabox for the simple product
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function product_meta_fields() {
		global $product_object;
		$quantity_meta = $product_object->get_meta('quantity_args');
		$min_item = (isset($quantity_meta['min_item']) && ! empty( $quantity_meta['min_item'] )) ? intval($quantity_meta['min_item']) : null;
		$max_item = (isset($quantity_meta['max_item']) && ! empty( $quantity_meta['max_item'] )) ? intval($quantity_meta['max_item']) : null;
		$step_item = (isset($quantity_meta['step_item']) && ! empty( $quantity_meta['step_item'] )) ? intval($quantity_meta['step_item']) : null;
		$disable_quantity = (isset($quantity_meta['disable_quantity']) && ! empty( $quantity_meta['disable_quantity'] )) ? $quantity_meta['disable_quantity'] : null;

		echo '<div class="options_group show_if_simple">';

		// MinMax Variation Product Nonce
		wp_nonce_field( 'minmax_save_data', '_minmax_nonce' );

		woocommerce_wp_checkbox(
			array(
				'id'	=> 'disable_quantity',
				'label'	=> __( 'Disable MinMax Quantity', 'minmax-quantities-for-woocommerce' ),
				'value'	=> $disable_quantity,
				'desc_tip'	=> true,
				'description' => __( 'If you don\'t need Min/Max quantity for this product, You can disable it', 'minmax-quantities-for-woocommerce' )
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'        	=> 'min_item',
				'label'     	=> __('Minimum Item', 'minmax-quantities-for-woocommerce'),
				'type'      	=> 'number',
				'placeholder' 	=> __('Enter minimum item number', 'minmax-quantities-for-woocommerce'),
				'value' 		=> $min_item,
				'desc_tip'		=> true,
				'description'	=> __('Enter minimum item number', 'minmax-quantities-for-woocommerce')
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'        	=> 'max_item',
				'label'        	=> __('Maximum Item', 'minmax-quantities-for-woocommerce'),
				'type'        	=> 'number',
				'placeholder' 	=> __('Enter maximum item number', 'minmax-quantities-for-woocommerce'),
				'value' 		=> $max_item,
				'desc_tip'		=> true,
				'description'	=> __('Enter maximum item number', 'minmax-quantities-for-woocommerce')
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'        	=> 'step_item',
				'label'        	=> __('Step', 'minmax-quantities-for-woocommerce'),
				'type'        	=> 'number',
				'placeholder' 	=> __('Set step number', 'minmax-quantities-for-woocommerce'),
				'value' 		=> $step_item,
				'desc_tip'		=> true,
				'description'	=> __('Set step number', 'minmax-quantities-for-woocommerce')
			)
		);

		echo '</div>';
	}

	/**
	 * Saving the custom fields items
	 *
	 * @param int $post_ID
	 * @return void
	 * @since 1.0.0
	 */
	public function save_meta_fields( $post_ID ) {
		// Verify nonce value
		if ( ! isset( $_POST['_minmax_nonce'] ) && ! wp_verify_nonce( wp_unslash( $_POST['_minmax_nonce'] ), 'minmax_save_data' ) ) { // WPCS: input var ok, sanitization ok.
			return;
		}

		// Check user capability
		if ( ! current_user_can( 'edit_post', $post_ID ) ) return;

		// MinMax Items
		$product = wc_get_product( $post_ID );
		$product->update_meta_data('quantity_args', array(
			'min_item' => ( isset( $_POST[ 'min_item' ] ) && ! empty( $_POST[ 'min_item' ] ) ) ? intval( $_POST[ 'min_item' ] ) : null,
			'max_item' => ( isset( $_POST[ 'max_item' ] ) && ! empty( $_POST[ 'max_item' ] ) ) ? intval( $_POST[ 'max_item' ] ) : null,
			'step_item' => ( isset( $_POST[ 'step_item' ] ) && ! empty( $_POST[ 'step_item' ] ) ) ? intval( $_POST[ 'step_item' ] ) : null,
			'disable_quantity' => ( isset( $_POST[ 'disable_quantity' ] ) && ! empty( $_POST[ 'disable_quantity' ] ) ) ? sanitize_text_field( 'yes' ) : sanitize_text_field( 'no' ),
		));

		$product->save();

	}

	/**
	 * Add metabox for variable products
	 *
	 * @param mixed $loop
	 * @param mixed $variation_data
	 * @param mixed $variation
	 * @return void
	 */
	public function product_variation_meta_fields($loop, $variation_data, $variation) {

		// get the custom field value of the variation
		$quantity_meta  = get_post_meta( $variation->ID, 'quantity_var_args', true );
		$min_item_var	= (isset( $quantity_meta['min_item_var'] ) && ! empty( $quantity_meta['min_item_var'] )) ? intval($quantity_meta['min_item_var']) : null;
		$max_item_var	= (isset( $quantity_meta['max_item_var'] ) && ! empty( $quantity_meta['max_item_var'] )) ? intval($quantity_meta['max_item_var']) : null;
		$step_item_var	= (isset( $quantity_meta['step_item_var'] ) && ! empty( $quantity_meta['step_item_var'] )) ? intval($quantity_meta['step_item_var']) : null;

		// MinMax Variation Product Nonce
		wp_nonce_field( 'minmax_save_data', '_minmax_nonce' );

		woocommerce_wp_text_input(
			array(
				'id'			=> 'min_item_var[' . $variation->ID . ']',
				'label'			=> __('Minimum Item', 'minmax-quantities-for-woocommerce'),
				'type'			=> 'number',
				'wrapper_class'	=> ' form-row form-row-first',
				'placeholder'	=> __('Enter minimum item number', 'minmax-quantities-for-woocommerce'),
				'value'			=> $min_item_var,
				'desc_tip'		=> true,
				'description'	=> __('Enter minimum item number', 'minmax-quantities-for-woocommerce')
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'			=> 'max_item_var[' . $variation->ID . ']',
				'label'			=> __('Maximum Item', 'minmax-quantities-for-woocommerce'),
				'type'			=> 'number',
				'wrapper_class'	=> ' form-row form-row-last',
				'placeholder'	=> __('Enter maximum item number', 'minmax-quantities-for-woocommerce'),
				'value'			=> $max_item_var,
				'desc_tip'		=> true,
				'description'	=> __('Enter maximum item number', 'minmax-quantities-for-woocommerce')
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'			=> 'step_item_var[' . $variation->ID . ']',
				'label'			=> __('Step', 'minmax-quantities-for-woocommerce'),
				'type'			=> 'number',
				'wrapper_class'	=> ' form-row form-row-full',
				'placeholder'	=> __('Set step number number', 'minmax-quantities-for-woocommerce'),
				'value'			=> $step_item_var,
				'desc_tip'		=> true,
				'description'	=> __('Set step number', 'minmax-quantities-for-woocommerce')
			)
		);
	}

	/**
	 * Save variation quantity fields
	 *
	 * @param int $variation_id
	 * @return void
	 */
	public function save_variation_meta_fields($variation_id) {
		// Verify nonce value
		if ( ! isset( $_POST['_minmax_nonce'] ) && ! wp_verify_nonce( wp_unslash( $_POST['_minmax_nonce'] ), 'minmax_save_data' ) ) { // WPCS: input var ok, sanitization ok.
			return;
		}

		// Check user capability
		if ( ! current_user_can( 'edit_post', $variation_id ) ) return;

		// Save fields
		update_post_meta( $variation_id, 'quantity_var_args', array(
			'min_item_var'	=> (isset( $_POST['min_item_var'][$variation_id] ) && ! empty( $_POST['min_item_var'][$variation_id] )) ? intval($_POST['min_item_var'][$variation_id]) : null,
			'max_item_var'	=> (isset( $_POST['max_item_var'][$variation_id] ) && ! empty( $_POST['max_item_var'][$variation_id] )) ? intval($_POST['max_item_var'][$variation_id]) : null,
			'step_item_var'	=> (isset( $_POST['step_item_var'][$variation_id] ) && ! empty( $_POST['step_item_var'][$variation_id] )) ? intval($_POST['step_item_var'][$variation_id]) : null,
		));

	}
}
