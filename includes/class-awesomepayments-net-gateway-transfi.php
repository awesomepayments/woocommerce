<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'init_awesomepayments_net_transficom_gateway');

function init_awesomepayments_net_transficom_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

class AwesomePayments_Instant_Payment_Gateway_Transfi extends WC_Payment_Gateway {

    protected $icon_url;
    protected $transficom_wallet_address;

    public function __construct() {
        $this->id                 = 'awesomepayments-net-gateway-transfi';
        $this->icon = sanitize_url($this->get_option('icon_url'));
        $this->method_title       = esc_html__('Awesomepayments.net (transfi.com)', 'awesomepayments-net'); // Escaping title
        $this->method_description = esc_html__('Use Awesomepayments.net High Risk Merchant Gateway with instant approval & payouts to your USDC POLYGON address using transfi.com infrastructure', 'awesomepayments-net'); // Escaping description
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'));

        // Use the configured settings for redirect and icon URLs
        $this->transficom_wallet_address = sanitize_text_field($this->get_option('transficom_wallet_address'));
        $this->icon_url     = sanitize_url($this->get_option('icon_url'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => esc_html__('Enable/Disable', 'awesomepayments-net'), // Escaping title
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable transfi.com payment gateway', 'awesomepayments-net'), // Escaping label
                'default' => 'no',
            ),
            'title' => array(
                'title'       => esc_html__('Title', 'awesomepayments-net'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Payment method title that users will see during checkout.', 'awesomepayments-net'), // Escaping description
                'default'     => esc_html__('Credit Card', 'awesomepayments-net'), // Escaping default value
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'awesomepayments-net'), // Escaping title
                'type'        => 'textarea',
                'description' => esc_html__('Payment method description that users will see during checkout.', 'awesomepayments-net'), // Escaping description
                'default'     => esc_html__('Pay via credit card', 'awesomepayments-net'), // Escaping default value
                'desc_tip'    => true,
            ),
            'transficom_wallet_address' => array(
                'title'       => esc_html__('Wallet Address', 'awesomepayments-net'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert your USDC (Polygon) wallet address to receive instant payouts. Payouts maybe sent in USDC or USDT (Polygon or BEP-20) or POL native token. Same wallet should work to receive all. Make sure you use a self-custodial wallet to receive payouts.', 'awesomepayments-net'), // Escaping description
                'desc_tip'    => true,
            ),
            'icon_url' => array(
                'title'       => esc_html__('Icon URL', 'awesomepayments-net'), // Escaping title
                'type'        => 'url',
                'description' => esc_html__('Enter the URL of the icon image for the payment method.', 'awesomepayments-net'), // Escaping description
                'desc_tip'    => true,
            ),
        );
    }
	 // Add this method to validate the wallet address in wp-admin
    public function process_admin_options() {
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'woocommerce-settings')) {
    WC_Admin_Settings::add_error(__('Nonce verification failed. Please try again.', 'awesomepayments-net'));
    return false;
}
        $transficom_admin_wallet_address = isset($_POST[$this->plugin_id . $this->id . '_transficom_wallet_address']) ? sanitize_text_field( wp_unslash( $_POST[$this->plugin_id . $this->id . '_transficom_wallet_address'])) : '';

        // Check if wallet address starts with "0x"
        if (substr($transficom_admin_wallet_address, 0, 2) !== '0x') {
            WC_Admin_Settings::add_error(__('Invalid Wallet Address: Please insert your USDC Polygon wallet address.', 'awesomepayments-net'));
            return false;
        }

        // Check if wallet address matches the USDC contract address
        if (strtolower($transficom_admin_wallet_address) === '0x3c499c542cef5e3811e1192ce70d8cc03d5c3359') {
            WC_Admin_Settings::add_error(__('Invalid Wallet Address: Please insert your USDC Polygon wallet address.', 'awesomepayments-net'));
            return false;
        }

        // Proceed with the default processing if validations pass
        return parent::process_admin_options();
    }
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $awesomepaymentsnet_transficom_currency = get_woocommerce_currency();
		$awesomepaymentsnet_transficom_total = $order->get_total();
		$awesomepaymentsnet_transficom_nonce = wp_create_nonce( 'awesomepaymentsnet_transficom_nonce_' . $order_id );
		$awesomepaymentsnet_transficom_callback = add_query_arg(array('order_id' => $order_id, 'nonce' => $awesomepaymentsnet_transficom_nonce,), rest_url('awesomepaymentsnet/v1/awesomepaymentsnetgateway-transficom/'));
		$awesomepaymentsnet_transficom_email = urlencode(sanitize_email($order->get_billing_email()));
		
		if ($awesomepaymentsnet_transficom_currency === 'USD') {
        $awesomepaymentsnet_transficom_final_total = $awesomepaymentsnet_transficom_total;
		} else {
		
$awesomepaymentsnet_transficom_response = wp_remote_get('https://api.awesomepayments.net/control/convert.php?value=' . $awesomepaymentsnet_transficom_total . '&from=' . strtolower($awesomepaymentsnet_transficom_currency), array('timeout' => 30));

if (is_wp_error($awesomepaymentsnet_transficom_response)) {
    // Handle error
    awesomepaymentsnet_add_notice(__('Payment error:', 'awesomepayments-net') . __('Payment could not be processed due to failed currency conversion process, please try again', 'awesomepayments-net'), 'error');
    return null;
} else {

$awesomepaymentsnet_transficom_body = wp_remote_retrieve_body($awesomepaymentsnet_transficom_response);
$awesomepaymentsnet_transficom_conversion_resp = json_decode($awesomepaymentsnet_transficom_body, true);

if ($awesomepaymentsnet_transficom_conversion_resp && isset($awesomepaymentsnet_transficom_conversion_resp['value_coin'])) {
    // Escape output
    $awesomepaymentsnet_transficom_final_total	= sanitize_text_field($awesomepaymentsnet_transficom_conversion_resp['value_coin']);      
} else {
    awesomepaymentsnet_add_notice(__('Payment error:', 'awesomepayments-net') . __('Payment could not be processed, please try again (unsupported store currency)', 'awesomepayments-net'), 'error');
    return null;
}	
		}
		}
if ($awesomepaymentsnet_transficom_final_total < 70) {
awesomepaymentsnet_add_notice(__('Payment error:', 'awesomepayments-net') . __('Order total for this payment provider must be $70 USD or more.', 'awesomepayments-net'), 'error');
return null;
}	
		
$awesomepaymentsnet_transficom_gen_wallet = wp_remote_get('https://api.awesomepayments.net/control/wallet.php?address=' . $this->transficom_wallet_address .'&callback=' . urlencode($awesomepaymentsnet_transficom_callback), array('timeout' => 30));

if (is_wp_error($awesomepaymentsnet_transficom_gen_wallet)) {
    // Handle error
    awesomepaymentsnet_add_notice(__('Wallet error:', 'awesomepayments-net') . __('Payment could not be processed due to incorrect payout wallet settings, please contact website admin', 'awesomepayments-net'), 'error');
    return null;
} else {
	$awesomepaymentsnet_transficom_wallet_body = wp_remote_retrieve_body($awesomepaymentsnet_transficom_gen_wallet);
	$awesomepaymentsnet_transficom_wallet_decbody = json_decode($awesomepaymentsnet_transficom_wallet_body, true);

 // Check if decoding was successful
    if ($awesomepaymentsnet_transficom_wallet_decbody && isset($awesomepaymentsnet_transficom_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $awesomepaymentsnet_transficom_gen_addressIn = wp_kses_post($awesomepaymentsnet_transficom_wallet_decbody['address_in']);
        $awesomepaymentsnet_transficom_gen_polygon_addressIn = sanitize_text_field($awesomepaymentsnet_transficom_wallet_decbody['polygon_address_in']);
		$awesomepaymentsnet_transficom_gen_callback = sanitize_url($awesomepaymentsnet_transficom_wallet_decbody['callback_url']);
		// Save $transficomresponse in order meta data
    $order->add_meta_data('awesomepaymentsnet_transficom_tracking_address', $awesomepaymentsnet_transficom_gen_addressIn, true);
    $order->add_meta_data('awesomepaymentsnet_transficom_polygon_temporary_order_wallet_address', $awesomepaymentsnet_transficom_gen_polygon_addressIn, true);
    $order->add_meta_data('awesomepaymentsnet_transficom_callback', $awesomepaymentsnet_transficom_gen_callback, true);
	$order->add_meta_data('awesomepaymentsnet_transficom_converted_amount', $awesomepaymentsnet_transficom_final_total, true);
	$order->add_meta_data('awesomepaymentsnet_transficom_nonce', $awesomepaymentsnet_transficom_nonce, true);
    $order->save();
    } else {
        awesomepaymentsnet_add_notice(__('Payment error:', 'awesomepayments-net') . __('Payment could not be processed, please try again (wallet address error)', 'awesomepayments-net'), 'error');

        return null;
    }
}

// Check if the Checkout page is using Checkout Blocks
if (awesomepaymentsnet_is_checkout_block()) {
    global $woocommerce;
	$woocommerce->cart->empty_cart();
}

        // Redirect to payment page
        return array(
            'result'   => 'success',
            'redirect' => 'https://checkout.awesomepayments.net/process-payment.php?address=' . $awesomepaymentsnet_transficom_gen_addressIn . '&amount=' . (float)$awesomepaymentsnet_transficom_final_total . '&provider=transfi&email=' . $awesomepaymentsnet_transficom_email . '&currency=' . $awesomepaymentsnet_transficom_currency,
        );
    }

public function AwesomePayments_Instant_Payment_gateway_get_icon_url() {
        return !empty($this->icon_url) ? esc_url($this->icon_url) : '';
    }
}

function awesomepaymentsnet_add_instant_payment_gateway_transficom($gateways) {
    $gateways[] = 'AwesomePayments_Instant_Payment_Gateway_Transfi';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'awesomepaymentsnet_add_instant_payment_gateway_transficom');
}

// Add custom endpoint for changing order status
function awesomepaymentsnet_transficom_change_order_status_rest_endpoint() {
    // Register custom route
    register_rest_route( 'awesomepaymentsnet/v1', '/awesomepaymentsnetgateway-transficom/', array(
        'methods'  => 'GET',
        'callback' => 'awesomepaymentsnet_transficom_change_order_status_callback',
        'permission_callback' => '__return_true',
    ));
}
add_action( 'rest_api_init', 'awesomepaymentsnet_transficom_change_order_status_rest_endpoint' );

// Callback function to change order status
function awesomepaymentsnet_transficom_change_order_status_callback( $request ) {
    $order_id = absint($request->get_param( 'order_id' ));
	$awesomepaymentsnet_transficomgetnonce = sanitize_text_field($request->get_param( 'nonce' ));
	$awesomepaymentsnet_transficompaid_txid_out = sanitize_text_field($request->get_param('txid_out'));

    // Check if order ID parameter exists
    if ( empty( $order_id ) ) {
        return new WP_Error( 'missing_order_id', __( 'Order ID parameter is missing.', 'awesomepayments-net' ), array( 'status' => 400 ) );
    }

    // Get order object
    $order = wc_get_order( $order_id );

    // Check if order exists
    if ( ! $order ) {
        return new WP_Error( 'invalid_order', __( 'Invalid order ID.', 'awesomepayments-net' ), array( 'status' => 404 ) );
    }
	
	// Verify nonce
    if ( empty( $awesomepaymentsnet_transficomgetnonce ) || $order->get_meta('awesomepaymentsnet_transficom_nonce', true) !== $awesomepaymentsnet_transficomgetnonce ) {
        return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'awesomepayments-net' ), array( 'status' => 403 ) );
    }

    // Check if the order is pending and payment method is 'awesomepayments-net-gateway-transfi'
    if ( $order && $order->get_status() !== 'processing' && $order->get_status() !== 'completed' && 'awesomepayments-net-gateway-transfi' === $order->get_payment_method() ) {
        // Change order status to processing
		$order->payment_complete();
		/* translators: 1: Transaction ID */
		$order->add_order_note( sprintf(__('Payment completed by the provider TXID: %1$s', 'awesomepayments-net'), $awesomepaymentsnet_transficompaid_txid_out) );
        // Return success response
        return array( 'message' => 'Order marked as paid and status changed.' );
    } else {
        // Return error response if conditions are not met
        return new WP_Error( 'order_not_eligible', __( 'Order is not eligible for status change.', 'awesomepayments-net' ), array( 'status' => 400 ) );
    }
}
?>