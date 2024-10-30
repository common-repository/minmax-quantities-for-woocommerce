<?php

namespace StorePlugin\WooQuantity\Clients;

/**
 * The frontend functionality of the plugin.
 *
 * @package    Woo_Minmax_Quantities
 * @subpackage Woo_Minmax_Quantities/public
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Clients {

	/**
	 * Set notice message for min-max product item, quantity and ammount.
	 *
	 * @var string
	 */
	private $notice;

	/**
	 * Client construtor
	 *
	 * @return void
	 */
	public function __construct() {
		\add_filter( 'woocommerce_quantity_input_args', [ $this, 'wc_quantity_input_args' ], 99, 2 );
		\add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'wc_loop_cart_quantity_arg' ], 10, 2 );
		\add_filter( 'woocommerce_available_variation', [ $this, 'wc_available_variation_price_html' ], 10, 3 );
		\add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'wc_add_to_cart_validation' ], 10, 4 );
		\add_action( 'woocommerce_check_cart_items', [ $this, 'wc_check_cart_items' ], 10, 1 );
		\add_filter( 'gettext_woocommerce', [ $this, 'checkout_error_messege' ] );
	}

	/**
	 * Show WooCommerce notice for quantity error.
	 *
	 * @param string $msg
	 * @param array $args
	 * @return void
	 */
	public function show_quantity_error_message( $msg, $args ) {
		$this->notice = preg_replace( array_keys( $args ), array_values( $args ), $msg );
		wc_add_notice( $this->notice, 'error' );
	}

	/**
	 * Cart error message in checkout page is written in the following template without hook
	 * but it's a translated text for being hard coded.
	 * Translated code can be edited by gettext_* fileter.
	 * wp-content/plugins/woocommerce/templates/checkout/cart-errors.php
	 *
	 * @param string $translation
	 * @param string $text
	 * @param string $domain
	 * @return string
	 */
	public function checkout_error_messege( $translation ) {
		$checkout_issue_msg = 'There are some issues with the items in your cart. Please go back to the cart page and resolve these issues before checking out.';
		if ( $checkout_issue_msg === $translation ) {
			$translation = $this->notice;
		}
		return $translation;

	}

	/**
	 * Get client's message from option
	 *
	 * @return array
	 */
	protected function get_error_message() {
		$msg = [
			'active'	=> '',
			'msg'		=> '',
		];

		$message = apply_filters( 'minmax_quantity_error_messages', $msg );

		if( $message['active'] ) {
			return $message['msg'];
		}

		return [];

	}

	/**
	 * Quantity value from different quantity source
	 *
	 * @param object $product
	 * @return array
	 */
	public function product_quantity( $product, $variation = '' ) {

		// Single products' quantities
		$sp_args = $product->get_meta( 'quantity_args' );
		$disable_qty = ( ! empty( $sp_args['disable_quantity'] ) ) ? $sp_args['disable_quantity'] : null;

		$cat_qty = apply_filters( 'product_quantity_cat', [
			'min_item' => '',
			'max_item' => '',
			'step_item' => '',
		], $product );
		$tag_qty = apply_filters( 'product_quantity_tag', [
			'min_item' => '',
			'max_item' => '',
			'step_item' => '',
		], $product );

		if ( ! empty( $variation ) && ! empty( get_post_meta( $variation, 'quantity_var_args', true ) ) ) {

			$quantity_meta_var = get_post_meta( $variation, 'quantity_var_args', true );
			$quantity_meta = [
				'min_item' => $quantity_meta_var['min_item_var'],
				'max_item' => $quantity_meta_var['max_item_var'],
				'step_item' => $quantity_meta_var['step_item_var']
			];

			return $quantity_meta;

		} elseif (
			// For cart page quantity
			( get_post_type( $product->get_id() ) == 'product_variation' ) &&
			! empty( get_post_meta( $product->get_id(), 'quantity_var_args', true ) )
		) {

			$quantity_meta_var = get_post_meta( $product->get_id(), 'quantity_var_args', true );
			$quantity_meta = [
				'min_item' => $quantity_meta_var['min_item_var'],
				'max_item' => $quantity_meta_var['max_item_var'],
				'step_item' => $quantity_meta_var['step_item_var']
			];

			return $quantity_meta;

		} elseif (
			// For cart page quantity
			( get_post_type( $product->get_id() ) == 'product_variation' ) &&
			! empty( get_post_meta( wp_get_post_parent_id( $product->get_id() ), 'quantity_args', true ) )
		) {

			return get_post_meta( wp_get_post_parent_id( $product->get_id() ), 'quantity_args', true );

		} elseif ( ! empty( $sp_args['min_item'] ) || ! empty( $sp_args['max_item'] ) || ! empty( $sp_args['step_item'] ) ) {

			return $sp_args;

		} elseif ( ! empty( $cat_qty['min_item'] ) || ! empty( $cat_qty['max_item'] ) || ! empty( $cat_qty['step_item'] ) ) {

			return $cat_qty;

		} elseif ( ! empty( $tag_qty['min_item'] ) || ! empty( $tag_qty['max_item'] ) || ! empty( $tag_qty['step_item'] ) ) {

			return $tag_qty;

		} elseif ( ! empty( get_option( 'quantity_global_args' ) ) && $disable_qty !== 'yes' ) {

			return get_option( 'quantity_global_args' );

		}
	}

	/**
	 * Get order quantity from global settings
	 *
	 * @return array
	 */
	public function get_order_quantity() {
		$order_quantity = !empty( get_option('quantity_global_args') ) ? get_option('quantity_global_args') : array();
		return $order_quantity;
	}

	/**
	 * Updating quantity values on frontend html input min max step values
	 *
	 * @param array $args
	 * @param object $product
	 * @return array
	 */
	public function wc_quantity_input_args( $args, $product ) {
		$quantity_meta = $this->product_quantity( $product );

		if ( ! empty( $quantity_meta ) ) {
			// Min value
			if ( isset( $quantity_meta['min_item'] ) && $quantity_meta['min_item'] > 1 ) {
				$args['min_value'] = $quantity_meta['min_item'];

				if( ! is_cart() ) {
					$args['input_value'] = $quantity_meta['min_item'];
				}
			}

			// Max value
			if ( isset( $quantity_meta['max_item'] ) && $quantity_meta['max_item'] > 0 ) {
				$args['max_value'] = $quantity_meta['max_item'];

				if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
					$args['max_value'] = min( $product->get_stock_quantity(), $args['max_value'] );
				}
			}

			// Step value
			if ( isset( $quantity_meta['step_item'] ) && $quantity_meta['step_item'] > 1 ) {
				$args['step'] = $quantity_meta['step_item'];
			}
		}
		return $args;

	}

	/**
	 * Validate quantity on product add to cart if anyone bypass min max input html values
	 *
	 * @param bool $passed
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $variation_id
	 * @param array $variations
	 * @return bool
	 */
	public function wc_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {
		global $woocommerce;
		$product = wc_get_product( $product_id );
		$currency_symbol = get_woocommerce_currency_symbol();
		$quantity_meta = $this->product_quantity( $product, $variation_id );
		$order_quantity = $this->get_order_quantity();

		if ( ! empty( $quantity_meta ) ) {
			// Min item value
			if ( isset( $quantity_meta['min_item'] ) && $quantity_meta['min_item'] > 1 ) {
				if ( $quantity < $quantity_meta['min_item'] ) {
					$passed = false;

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You must add {{amount}} or more of “{{product_title}}” to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['min_item_msg'] ) ) ? $client_message['min_item_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*amount\s*}\s*})/'		=> $quantity_meta['min_item'],
						'/({\s*{\s*product_title\s*}\s*})/' => $product->get_name(),
					]);
				}
			}

			// Max item value
			if ( isset( $quantity_meta['max_item'] ) && $quantity_meta['max_item'] > 0 ) {
				if ( $quantity > $quantity_meta['max_item'] ) {
					$passed = false;

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You can not add more than {{amount}} of “{{product_title}}” to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_item_msg'] ) ) ? $client_message['max_item_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*amount\s*}\s*})/'		=> $quantity_meta['max_item'],
						'/({\s*{\s*product_title\s*}\s*})/' => $product->get_name(),
					]);
				}
			}

			/* Check cart to see if product is already added max quantity */
			if ( isset( $quantity_meta['max_item'] ) && $quantity_meta['max_item'] > 0 ) {
				$cart = $woocommerce->cart->get_cart();
				foreach ( $cart as $cart_item_key => $cart_item ) {
					if ( $cart_item['product_id'] == $product_id ) {
						if ( $cart_item['quantity'] + $quantity > $quantity_meta['max_item'] ) {
							$passed = false;

							// Display error on not fulfilling quantity condition.
							$client_message = $this->get_error_message();
							$default_message = __( 'You can not add more than {{amount}} of “{{product_title}}” to your cart.', 'minmax-quantities-for-woocommerce' );
							$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_item_msg'] ) ) ? $client_message['max_item_msg'] : $default_message;
							$this->show_quantity_error_message( $error_message, [
								'/({\s*{\s*amount\s*}\s*})/'		=> $quantity_meta['max_item'],
								'/({\s*{\s*product_title\s*}\s*})/' => $product->get_name(),
							]);
						}
					}
				}
			}
		}

		/* Check order quantity - Only check Max number and Max amount for product */
		if ( ! empty( $order_quantity ) ) {
			// Max item value
			if ( isset( $order_quantity['max_order_item'] ) && $order_quantity['max_order_item'] > 0 ) {
				$cart_quantity	= $woocommerce->cart->get_cart_contents_count();
				$total_quantity	= $cart_quantity + $quantity;

				if ( $total_quantity > $order_quantity['max_order_item'] ) {
					$passed = false;

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You can not add more than {{quantity}} product to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_order_msg'] ) ) ? $client_message['max_order_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*quantity\s*}\s*})/'	=> $order_quantity['max_order_item'],
					]);
				}
			}

			// Max price amount
			if ( isset( $order_quantity['max_order_amount'] ) && $order_quantity['max_order_amount'] > 0 ) {
				$cart_amount	= $woocommerce->cart->total;
				$total_amount	= $cart_amount + ( $product->get_price() * $quantity );

				if ( $total_amount > $order_quantity['max_order_amount'] ) {
					$passed = false;

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You can not add more than {{price}} amount to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_amount_msg'] ) ) ? $client_message['max_amount_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*price\s*}\s*})/'	=> $currency_symbol.$order_quantity['max_order_amount'],
					]);
				}
			}
		}

		return $passed;
	}

	/**
	 * Validate quantity on cart page if anyone bypass min max input html values
	 *
	 * @return bool
	 */
	public function wc_check_cart_items() {
		global $woocommerce;
		$cart = $woocommerce->cart->get_cart();
		$currency_symbol = get_woocommerce_currency_symbol();
		$order_quantity = $this->get_order_quantity();

		foreach ( $cart as $cart_item_key => $cart_item ) {
			$product = wc_get_product( $cart_item['product_id'] );
			$quantity_meta = $this->product_quantity( $product, $cart_item['variation_id'] );
			if ( ! empty( $quantity_meta ) ) {
				// Min item value
				if ( isset( $quantity_meta['min_item'] ) && $quantity_meta['min_item'] > 1 ) {
					if ( $cart_item['quantity'] < $quantity_meta['min_item'] ) {

						// Display error on not fulfilling quantity condition.
						$client_message = $this->get_error_message();
						$default_message = __( 'You must add {{amount}} or more of “{{product_title}}” to your cart.', 'minmax-quantities-for-woocommerce' );
						$error_message = ( is_array( $client_message ) && ! empty( $client_message['min_item_msg'] ) ) ? $client_message['min_item_msg'] : $default_message;
						$this->show_quantity_error_message( $error_message, [
							'/({\s*{\s*amount\s*}\s*})/'		=> $quantity_meta['min_item'],
							'/({\s*{\s*product_title\s*}\s*})/'	=> $product->get_name(),
						]);

						remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
						return;
					}
				}

				// Max item value
				if ( isset( $quantity_meta['max_item'] ) && $quantity_meta['max_item'] > 0 ) {
					if ( $cart_item['quantity'] > $quantity_meta['max_item'] ) {

						// Display error on not fulfilling quantity condition.
						$client_message = $this->get_error_message();
						$default_message = __( 'You can not add more than {{amount}} of “{{product_title}}” to your cart.', 'minmax-quantities-for-woocommerce' );
						$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_item_msg'] ) ) ? $client_message['max_item_msg'] : $default_message;
						$this->show_quantity_error_message( $error_message, [
							'/({\s*{\s*amount\s*}\s*})/'		=> $quantity_meta['max_item'],
							'/({\s*{\s*product_title\s*}\s*})/'	=> $product->get_name(),
						]);

						remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
						return;
					}
				}
			}
		}

		/* Check order quantity - Only check Max number and Max amount for product */
		if ( ! empty( $order_quantity ) ) {
			// Min order quantity
			if ( isset( $order_quantity['min_order_item'] ) && $order_quantity['min_order_item'] > 0 ) {
				$total_quantity	= $woocommerce->cart->get_cart_contents_count();

				if ( $total_quantity < $order_quantity['min_order_item'] ) {

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You must add {{quantity}} or more product to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['min_order_msg'] ) ) ? $client_message['min_order_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*quantity\s*}\s*})/' => $order_quantity['min_order_item'],
					]);

					remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
					return;
				}
			}

			// Max order Quantity
			if ( isset( $order_quantity['max_order_item'] ) && $order_quantity['max_order_item'] > 0 ) {
				$total_quantity	= $woocommerce->cart->get_cart_contents_count();

				if ( $total_quantity > $order_quantity['max_order_item'] ) {

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You can not add more than {{quantity}} product to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_order_msg'] ) ) ? $client_message['max_order_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*quantity\s*}\s*})/' => $order_quantity['max_order_item'],
					]);

					remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
					return;
				}
			}

			// Min order amount
			if ( isset( $order_quantity['min_order_amount'] ) && $order_quantity['min_order_amount'] > 0 ) {
				$total_amount	= $woocommerce->cart->total;

				if ( $total_amount < $order_quantity['min_order_amount'] ) {

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You must add {{price}} or more amount to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['min_amount_msg'] ) ) ? $client_message['min_amount_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*price\s*}\s*})/' => $currency_symbol.$order_quantity['min_order_amount'],
					]);

					remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
					return;
				}
			}

			// Max price amount
			if ( isset( $order_quantity['max_order_amount'] ) && $order_quantity['max_order_amount'] > 0 ) {
				$total_amount	= $woocommerce->cart->total;

				if ( $total_amount > $order_quantity['max_order_amount'] ) {

					// Display error on not fulfilling quantity condition.
					$client_message = $this->get_error_message();
					$default_message = __( 'You can not add more than {{price}} amount to your cart.', 'minmax-quantities-for-woocommerce' );
					$error_message = ( is_array( $client_message ) && ! empty( $client_message['max_amount_msg'] ) ) ? $client_message['max_amount_msg'] : $default_message;
					$this->show_quantity_error_message( $error_message, [
						'/({\s*{\s*price\s*}\s*})/' => $currency_symbol.$order_quantity['max_order_amount'],
					]);

					remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
					return;
				}
			}
		}
	}

	/**
	 * Update "min quantity" as quantity on shop and archives pages
	 * Ajax operation with hook
	 *
	 * @param array $args
	 * @param object $product
	 * @return array
	 */
	function wc_loop_cart_quantity_arg( $args, $product ) {
		$quantity_meta = $this->product_quantity( $product );

		if ( ! empty( $quantity_meta ) ) {
			// Min value
			if ( isset( $quantity_meta['min_item'] ) && $quantity_meta['min_item'] > 1 ) {
				$args['quantity'] = $quantity_meta['min_item'];
			}
		}
		return $args;

	}

	/**
	 * Updating the quantity value on frontend for variable products.
	 *
	 * @param array $data
	 * @param object $product
	 * @param object $variation
	 * @return array
	 */
	public function wc_available_variation_price_html( $data, $product, $variation ) {
		$quantity_meta = $this->product_quantity( $product, $variation->get_id() );

		if ( ! empty( $quantity_meta ) ) {
			if ( isset( $quantity_meta['min_item'] ) && $quantity_meta['min_item'] > 1 ) {
				$data['min_qty'] = $quantity_meta['min_item'];
			}

			if ( isset( $quantity_meta['max_item'] ) && $quantity_meta['max_item'] > 0 ) {
				$data['max_qty'] = $quantity_meta['max_item'];

				if ( $variation->managing_stock() && ! $variation->backorders_allowed() ) {
					$data['max_qty'] = min( $variation->get_stock_quantity(), $data['max_qty'] );
				}
			}
		}

		return $data;
	}

}
