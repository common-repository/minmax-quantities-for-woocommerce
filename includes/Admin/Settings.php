<?php

namespace StorePlugin\WooQuantity\Admin;

/**
 * Global Setting Options for Minmax Quantity
 *
 * @package    Woo_Minmax_Quantities
 * @subpackage Woo_Minmax_Quantities/public
 * @author     StorePlugin <contact@storeplugin.net>
 */
class Settings {

    public function __construct() {
        \add_action( 'admin_init', [ $this, 'quantity_setting_page' ] );
        \add_action( 'admin_menu', [ $this, 'quantity_admin_menu' ] );
        \add_filter( 'minmax_pro_version_fields', [$this, 'minmax_version_fields_pro'] );
    }

    /**
     * Create Settings Page for WooCommerce Quantity Manager
     *
     * @return void
     */
    public function quantity_setting_page() {

        register_setting(
            'quantity_setting',
            'quantity_global_args',
        );

        add_settings_section(
            'quantity_section',
            __('Product Quantity Settings', 'minmax-quantities-for-woocommerce'),
            array($this, 'quantity_section_cb'),
            'quantity_settings',
        );

        add_settings_field(
            'quantity_min',
            __('Minimum Item', 'minmax-quantities-for-woocommerce'),
            array($this, 'quantity_minimum_cb'),
            'quantity_settings',
            'quantity_section',
            array(
                'name' => 'min_item',
            ),
        );

        add_settings_field(
            'quantity_max',
            __('Maximum Item', 'minmax-quantities-for-woocommerce'),
            array($this, 'quantity_maximum_cb'),
            'quantity_settings',
            'quantity_section',
            array(
                'name' => 'max_item',
            ),
        );

        add_settings_field(
            'quantity_step',
            __('Step', 'minmax-quantities-for-woocommerce'),
            array($this, 'quantity_step_cb'),
            'quantity_settings',
            'quantity_section',
            array(
                'name' => 'step_item',
            ),
        );

        add_settings_section(
            'order_quantity_section',
            __('Order Quantity Settings', 'minmax-quantities-for-woocommerce'),
            array($this, 'order_quantity_section_cb'),
            'quantity_settings',
        );

        add_settings_field(
            'order_quantity_min',
            __('Minimum Order Quantity', 'minmax-quantities-for-woocommerce'),
            array($this, 'order_quantity_minimum_cb'),
            'quantity_settings',
            'order_quantity_section',
            array(
                'name' => 'min_order_item',
            ),
        );

        add_settings_field(
            'order_quantity_max',
            __('Maximum Order Quantity', 'minmax-quantities-for-woocommerce'),
            array($this, 'order_quantity_maximum_cb'),
            'quantity_settings',
            'order_quantity_section',
            array(
                'name' => 'max_order_item',
            ),
        );

        /* Minimum order amount */
        add_settings_field(
            'order_amount_min',
            __('Minimum Order Amount', 'minmax-quantities-for-woocommerce'),
            array($this, 'order_amount_minimum_cb'),
            'quantity_settings',
            'order_quantity_section',
            array(
                'name' => 'min_order_amount',
            ),
        );

        /* Maximum order amount */
        add_settings_field(
            'order_amount_max',
            __('Maximum Order Amount', 'minmax-quantities-for-woocommerce'),
            array($this, 'order_amount_maximum_cb'),
            'quantity_settings',
            'order_quantity_section',
            array(
                'name' => 'max_order_amount',
            ),
        );
    }

    /**
     * Get option from object
     *
     * @param string $option setting's option name
     * @param array $args provide array offset for curresponding field.
     * @return string|int
     */
    public function get_option( $option, $args ) {
        $options = get_option( $option );
        return ( isset( $options[$args] ) && ! empty( $options[$args] ) ) ? $options[$args] : null;
    }

    /**
     * Section callback
     *
     * @return void
     */
    public function quantity_section_cb() {
        return;
    }

    /**
     * Minimum input field
     *
     * @return void
     */
    public function quantity_minimum_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Minimum Number', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Maximum input field
     *
     * @return void
     */
    public function quantity_maximum_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
    ?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Maximum Number', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Step input field
     *
     * @return void
     */
    public function quantity_step_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
    ?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Step Number', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Order Quantity Section callback
     *
     * @return void
     */
    public function order_quantity_section_cb() {
        return;
    }

    /**
     * Minimum Order Quantity input field
     *
     * @return void
     */
    public function order_quantity_minimum_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
    ?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Minimum Order Quantity', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Maximum Order Quantity input field
     *
     * @return void
     */
    public function order_quantity_maximum_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
    ?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Maximum Order Quantity', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Minimum Order Amount input field
     *
     * @return void
     */
    public function order_amount_minimum_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
    ?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Minimum Order Amount', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Maximum Order Amount input field
     *
     * @return void
     */
    public function order_amount_maximum_cb($args) {
        $value = $this->get_option('quantity_global_args', $args['name']);
    ?>
        <input type="number" name="quantity_global_args[<?php echo esc_attr($args['name']); ?>]" id="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_html($value); ?>" />
        <p class="description">
            <?php esc_html_e('Enter Maximum Order Amount', 'minmax-quantities-for-woocommerce'); ?>
        </p>
    <?php
    }

    /**
     * Create WooCommerce Menu Page
     *
     * @return void
     */
    public function quantity_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Product Min Max Quantity', 'minmax-quantities-for-woocommerce'),
            __('Min Max Quantity', 'minmax-quantities-for-woocommerce'),
            'manage_woocommerce',
            'sp_minmax_quantity_settings',
            array($this, 'quantity_page_cb'),
        );
    }

    /**
     * Add dummy premium features in the free version
     *
     * @param mixed $pro
     * @return string
     */
    public function minmax_version_fields_pro( $pro ) {
        ob_start();
        ?>
        <a href="https://storeplugin.net/plugins/minmax-quantities-for-woocommerce/?utm_source=activesite&utm_campaign=minmax&utm_medium=link" target="_blank" class="check-pro-wrap">
            <span class="check-pro-button"><?php _e('Check the pro version', 'minmax-quantities-for-woocommerce') ?></span>

        <h2><?php _e('Message section:', 'minmax-quantities-for-woocommerce') ?></h2>
        <table class="form-table minmax-dummy-form" role="presentation" style="position: relative;">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Message for minimum item', 'minmax-quantities-for-woocommerce') ?></th>
                    <td>
                        <textarea id="min_item_msg" cols="30" rows="5" disabled></textarea>
                        <p class="description"><?php _e('Enter Maximum Order Amount', 'minmax-quantities-for-woocommerce') ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Message for maximum item', 'minmax-quantities-for-woocommerce') ?></th>
                    <td>
                        <textarea id="max_item_msg" cols="30" rows="5" disabled></textarea>
                        <p class="description"><?php _e('Enter Maximum Item Message', 'minmax-quantities-for-woocommerce') ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Message for minimum order', 'minmax-quantities-for-woocommerce') ?></th>
                    <td>
                        <textarea id="min_order_msg" cols="30" rows="5" disabled></textarea>
                        <p class="description"><?php _e('Enter Minimum Order Message', 'minmax-quantities-for-woocommerce') ?></p>
                    </td>
                </tr>
                    <tr>
                        <th scope="row"><?php _e('Message for maximum order', 'minmax-quantities-for-woocommerce') ?></th>
                        <td>
                            <textarea id="max_order_msg" cols="30" rows="5" disabled></textarea>
                            <p class="description"><?php _e('Enter Maximum Order Message', 'minmax-quantities-for-woocommerce') ?></p>
                        </td>
                </tr>
            </tbody>
        </table>
        </a>
        <?php
        $pro = ob_get_clean();
        return $pro;
    }

    /**
     * WooCommerce Settigs Menu Page
     *
     * @return void
     */
    public function quantity_page_cb() {
        // check the capabilities of a WooCommerce store manager
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'quantity_messages',
                'quantity_message',
                __('Settings Saved', 'minmax-quantities-for-woocommerce'),
                'updated'
            );
        }

        // display with error and update messages of settings form
        settings_errors('quantity_messages');
    ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('quantity_setting');
                do_settings_sections('quantity_settings');
                submit_button('Save Settings');
                echo apply_filters('minmax_pro_version_fields', true);
                ?>
            </form>
        </div>
    <?php
    }
}
