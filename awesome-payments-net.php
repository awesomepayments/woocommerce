<?php
/**
 * Plugin Name: Awesomepayments.net - Payment Gateway with Instant Approval & Payouts
 * Plugin URI: https://awesomepayments.net/#woocommerce
 * Description: Awesomepayments.net is a High Risk Merchant Gateway with instant approval & payouts to your USDC (Polygon) wallet.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * Requires at least: 5.8
 * Tested up to: 6.7.2
 * WC requires at least: 5.8
 * WC tested up to: 9.7.2
 * Requires PHP: 7.2
 * Author: awesomepayments.net
 * Author URI: https://awesomepayments.net/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

    // Exit if accessed directly.
    if (!defined('ABSPATH')) {
        exit;
    }

    add_action('before_woocommerce_init', function() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });

	add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

/**
 * Enqueue block assets for the gateway.
 */
function awesomepaymentsnetgateway_enqueue_block_assets() {
    // Fetch all enabled WooCommerce payment gateways
    $awesomepaymentsnetgateway_available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    $awesomepaymentsnetgateway_gateways_data = array();

    foreach ($awesomepaymentsnetgateway_available_gateways as $gateway_id => $gateway) {
		if (strpos($gateway_id, 'awesomepayments-net-payment-gateway') === 0) {
        $icon_url = method_exists($gateway, 'awesomepayments_net_payment_gateway_get_icon_url') ? $gateway->awesomepayments_net_payment_gateway_get_icon_url() : '';
        $awesomepaymentsnetgateway_gateways_data[] = array(
            'id' => sanitize_key($gateway_id),
            'label' => sanitize_text_field($gateway->get_title()),
            'description' => wp_kses_post($gateway->get_description()),
            'icon_url' => sanitize_url($icon_url),
        );
		}
    }

    wp_enqueue_script(
        'awesomepaymentsnetgateway-net-block-support',
        plugin_dir_url(__FILE__) . 'assets/js/awesomepaymentsnetgateway-net-block-checkout-support.js',
        array('wc-blocks-registry', 'wp-element', 'wp-i18n', 'wp-components', 'wp-blocks', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/awesomepaymentsnetgateway-block-checkout-support.js'),
        true
    );

    // Localize script with gateway data
    wp_localize_script(
        'awesomepaymentsnetgateway-net-block-support',
        'awesomepaymentsnetgatewayData',
        $awesomepaymentsnetgateway_gateways_data
    );
}
add_action('enqueue_block_assets', 'awesomepaymentsnetgateway_enqueue_block_assets');

/**
 * Enqueue styles for the gateway on checkout page.
 */
function awesomepaymentsnetgateway_enqueue_styles() {
    if (is_checkout()) {
        wp_enqueue_style(
            'awesomepaymentsnetgateway-styles',
            plugin_dir_url(__FILE__) . 'assets/css/awesomepaymentsnetgateway-net-payment-gateway-styles.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/awesomepaymentsnetgateway-net-payment-gateway-styles.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'awesomepaymentsnetgateway_enqueue_styles');

    include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-hostedawesomepaymentsnet.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-wert.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-werteur.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-revolut.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-stripe.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-simpleswap.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-rampnetwork.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-mercuryo.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-transak.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-moonpay.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-banxa.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-guardarian.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-particle.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-utorg.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-transfi.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-alchemypay.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-changenow.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-sardine.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-topper.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-unlimit.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-bitnovo.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-robinhood.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-coinbase.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-upi.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-interac.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-simplex.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-swipelux.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-awesomepayments-net-gateway-kado.php'); // Include the payment gateway class

	// Conditional function that check if Checkout page use Checkout Blocks
function awesomepaymentsnet_is_checkout_block() {
    return WC_Blocks_Utils::has_block_in_page( wc_get_page_id('checkout'), 'woocommerce/checkout' );
}

function awesomepaymentsnetgateway_add_notice($awesomepaymentsnetgateway_message, $awesomepaymentsnetgateway_notice_type = 'error') {
    // Check if the Checkout page is using Checkout Blocks
    if (awesomepaymentsnetgateway_is_checkout_block()) {
        // For blocks, throw a WooCommerce exception
        if ($awesomepaymentsnetgateway_notice_type === 'error') {
            throw new \WC_Data_Exception('checkout_error', esc_html($awesomepaymentsnetgateway_message));
        }
        // Handle other notice types if needed
    } else {
        // Default WooCommerce behavior
        wc_add_notice(esc_html($awesomepaymentsnetgateway_message), $awesomepaymentsnetgateway_notice_type);
    }
}

?>
