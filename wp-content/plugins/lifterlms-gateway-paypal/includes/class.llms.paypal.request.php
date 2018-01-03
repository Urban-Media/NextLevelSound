<?php
/**
 * Handle all API requests for the LifterLMS PayPal payment gateway
 * @since    1.0.0
 * @version  1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_PayPal_Request {

	/**
	 * PayPal API Version
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const API_VERSION = 204;

	/**
	 * PayPal API URL for LIVE API Requests
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const LIVE_REQUEST_URL = 'https://api-3t.paypal.com/nvp';

	/**
	 * PayPal API URL for SANDBOX API Requests
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const SANDBOX_REQUEST_URL = 'https://api-3t.sandbox.paypal.com/nvp';

	/**
	 * PayPal API URL for LIVE redirects
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const LIVE_REDIRECT_URL = 'https://www.paypal.com/cgi-bin/webscr';

	/**
	 * PayPal API URL for SANDBOX redirects
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const SANDBOX_REDIRECT_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

	/**
	 * Constructor
	 * @param    obj     $gateway  Instance of the LifterLMS PayPal Payment Gateway
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;
		$this->sandbox = $this->gateway->is_test_mode_enabled();

	}

	/**
	 * Builds the request object required by $this->make_request
	 * @param    string     $action  PayPal Method
	 * @param    array      $data    array of NVP data
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function build_request( $action, $data ) {

		$body = array_merge(
			$this->get_credentials(),
			array(
				'VERSION' => self::API_VERSION,
				'METHOD' => $action,
			),
			$data
		);

		$request = array(
			'body' => $body,
			'httpversion' => '1.1',
			'method' => 'POST',
			'timeout' => 30,
		);

		$this->gateway->log( 'PayPal `LLMS_PayPal_Request()->build_request()` called', $request );

		return apply_filters( 'llms_paypal_build_request', $request, $action, $data );

	}

	/**
	 * Make a SetExpressCheckout request with minimal information
	 * error code 11452 will be returned if they are not enabled, otherwise we know they are enabled
	 * a huge thanks to the developers behined WooCommerce Subscriptions who figured out how to do this!
	 * @return   WP_Error|array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function check_reference_transactions() {

		// for this call we're going to force a check on live mode
		// because ref transactions are always enabled on sandbox
		// and user's won't think to check in both modes
		$this->sandbox = false;

		$data = array(
			'PAYMENTREQUEST_0_AMT'           => 0,
			'PAYMENTREQUEST_0_ITEMAMT'       => 0,
			'PAYMENTREQUEST_0_SHIPPINGAMT'   => 0,
			'PAYMENTREQUEST_0_TAXAMT'        => 0,
			'PAYMENTREQUEST_0_CURRENCYCODE'  => get_lifterlms_currency(),

			'L_BILLINGTYPE0'                 => 'MerchantInitiatedBilling',
			'L_BILLINGAGREEMENTDESCRIPTION0' => sprintf( _x( 'Orders with %s', 'billing agreement description for PayPal', 'lifterlms-paypal' ), $this->get_brand_name() ),

			'RETURNURL'                      => llms_cancel_payment_url(),
			'CANCELURL'                      => llms_cancel_payment_url(),
			'BRANDNAME'                      => $this->get_brand_name(),

			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
		);

		$r = $this->make_request( 'SetExpressCheckout', $data );

		// switch the api mode back to the default
		$this->sandbox = $this->gateway->is_test_mode_enabled();

		return $r;

	}

	/**
	 * Create a billing agreement for use later
	 * @param    string     $token  PayPal Token from SetExpressCheckout
	 * @return   WP_Error|array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function create_billing_agreement( $token ) {
		$r = $this->make_request( 'CreateBillingAgreement', array( 'TOKEN' => $token ) );
		// if it's not an error and there's no billing agreement id in the response
		if ( ! is_wp_error( $r ) && ! isset( $r['BILLINGAGREEMENTID'] ) ) {
			return new WP_Error( 'no-agreement', __( 'PayPal Billing Agreement ID not found.', 'lifterlms' ) );
		}
		// otherwise just return either the error or the array of data
		return $r;
	}

	/**
	 * Execute an express checkout
	 * This actually CHARGES an Order
	 * @param    obj        $order     Instance of the LLMS_Order
	 * @param    string     $token     Token received from PayPal that can be used to execute the charge
	 * @param    string     $payer_id  PayPal PayerID
	 * @return   array|WP_Error
	 * @since    1.0.0
	 * @version  1.0.2
	 */
	public function do_express_checkout( $order, $token, $payer_id ) {
		$data = array_merge(
			$this->get_single_express_checkout_data( $order ),
			array(
				'BUTTONSOURCE' => 'codeBOX_SP',
				'PAYERID' => $payer_id,
				'TOKEN' => $token,
			)
		);
		return $this->make_request( 'DoExpressCheckoutPayment', $data );
	}

	/**
	 * Process a reference transaction
	 * @param    obj      $order         instance of the LLMS_Order
	 * @param    int      $txn_id        WP_Post ID of the transaction
	 * @param    string   $payment_type  type of payment, either "initial" or "recurring", used to determine the amount of the charge
	 * @return   WP_Error|array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function do_reference_transaction( $order, $txn_id, $payment_type = 'recurring' ) {
		$amount = 'initial' === $payment_type ? $order->get_initial_price( array(), 'float' ) : $order->get_price( 'total', array(), 'float' );
		$data = array(
			'REFERENCEID' => $order->get( 'gateway_subscription_id' ),
			'PAYMENTACTION' => 'Sale',
			'PAYMENTTYPE' => 'InstantOnly',
			'IPADDRESS' => llms_get_ip_address(),
			'REQCONFIRMSHIPPING' => 0,
			'AMT' => $amount,
			'CURRENCYCODE' => get_lifterlms_currency(),
			'CUSTOM' => json_encode( array( 'order_id' => $order->get( 'id' ), 'order_key' => $order->get( 'order_key' ), 'transaction_id' => $txn_id ) ),
			'INVNUM' => $this->gateway->get_invoice_prefix() . $order->get( 'id' ) . '-' . $txn_id,
		);
		return $this->make_request( 'DoReferenceTransaction', $data );
	}

	/**
	 * Retrieve a filterable brand name to pass with PayPal requests
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function get_brand_name() {
		return apply_filters( 'llms_paypal_brand_name', html_entity_decode( get_bloginfo( 'name' ), ENT_NOQUOTES, 'UTF-8' ) );
	}

	/**
	 * Get API credentials according to the gateway's current mode
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function get_credentials() {

		return array(
			'PWD' =>  $this->sandbox ? $this->gateway->get_test_api_password() : $this->gateway->get_live_api_password(),
			'SIGNATURE' => $this->sandbox ? $this->gateway->get_test_api_signature() : $this->gateway->get_live_api_signature(),
			'USER' => $this->sandbox ? $this->gateway->get_test_api_username() : $this->gateway->get_live_api_username(),
		);

	}

	/**
	 * Retrieve a length-adjusted string for a purchase based on the order details
	 * @param    obj        $order  instance of the LLMS_Order
	 * @param    string     $type   type of description string to retrieve
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function get_description( $order, $type ) {

		$string = '';

		if ( $type === 'single-name' || $type === 'recurring-description' ) {
			$string .= sprintf( '%s: %s', ucwords( $order->get( 'product_type' ) ), $order->get( 'product_title' ) );
		}

		if ( $type === 'recurring-description' ) {
			$string .= ' &ndash; ';
		}

		if ( $type === 'single-description' || $type === 'recurring-description' ) {
			$string .= sprintf( _x( 'Access Plan: %s', 'PayPal Item Description', 'lifterlms-paypal' ), $order->get( 'plan_title' ) );
		}

		return html_entity_decode( llms_trim_string( apply_filters( 'llms_paypal_get_description', $string, $order, $type ), 127 ), ENT_NOQUOTES, 'UTF-8' );

	}

	/**
	 * Get details of an express checkout by token
	 * @param    string     $token  checkout token provided by PayPal during a SetExpressCheckout call
	 * @return   obj|array          WP_Error during failure, an array of details from PayPal on success
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_express_checkout_details( $token ) {
		$data = array(
			'TOKEN' => $token,
		);
		return $this->make_request( 'GetExpressCheckoutDetails', $data );
	}

	/**
	 * Get NVP data to pass to PayPal during a SetExpressCheckout call for a recurring payment transaction
	 * This creates a billing agreement the customer can agree to, this doesn't make any actual charges
	 *
	 * @param    obj        $order  Instance of the LLMS_Order
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function get_recurring_express_checkout_data( $order ) {
		return array(
			'RETURNURL' => llms_confirm_payment_url( $order->get( 'order_key' ) ),
			'CANCELURL' => llms_cancel_payment_url(),
			'REQCONFIRMSHIPPING' => 0,
			'NOSHIPPING' => 1,
			'ALLOWNOTE' => 0,

			'EMAIL' => $order->get( 'billing_email' ),

			'BRANDNAME' => $this->get_brand_name(),
			'CHANNELTYPE' => 'Merchant',
			'LANDINGPAGE' => apply_filters( 'llms_paypal_landing_page', 'Login', $order ),
			'PAGESTYLE' => $this->gateway->get_page_style(),

			'PAYMENTREQUEST_0_AMT' => $order->get_price( 'total', array(), 'float' ),
			'PAYMENTREQUEST_0_CURRENCYCODE' => get_lifterlms_currency(),
			'PAYMENTREQUEST_0_ITEMAMT' => 0,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
			'PAYMENTREQUEST_0_TAXAMT' => 0,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',

			'L_BILLINGTYPE0' => 'MerchantInitiatedBilling',
			'L_BILLINGAGREEMENTDESCRIPTION0' => $this->get_description( $order, 'recurring-description' ),
			'L_BILLINGAGREEMENTCUSTOM0' => json_encode( array( 'order_id' => $order->get( 'id' ), 'order_key' => $order->get( 'order_key' ) ) ),

		);

	}

	/**
	 * Get the PayPal redirect URL based on API Mode
	 * @param    string     $token  token to append from SetExpressCheckout
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function get_redirect_url( $token ) {

		$query_data = array(
			'cmd' => '_express-checkout',
			'token' => $token,
		);

		$url = $this->sandbox ? self::SANDBOX_REDIRECT_URL : self::LIVE_REDIRECT_URL;
		$url .= '?' . http_build_query( $query_data );

		return $url;

	}

	/**
	 * Retrieve the Request URL based on current API mode
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function get_request_url() {
		return $this->sandbox ? self::SANDBOX_REQUEST_URL : self::LIVE_REQUEST_URL;
	}

	/**
	 * Get NVP data to pass to PayPal during a SetExpressCheckout call for a single payment transaction
	 * @param    obj        $order  Instance of the LLMS_Order
	 * @return   array
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	private function get_single_express_checkout_data( $order ) {

		// manual recurring orders need to have unique order ids
		if ( $order->is_recurring() ) {
			$txns = $order->get_transactions();
			$invoice_number = sprintf( '%1$s%2$d-%3$d', $this->gateway->get_invoice_prefix(), $order->get( 'id' ), $txns['count'] + 1 );
		} else {
			$invoice_number = sprintf( '%1$s%2$d', $this->gateway->get_invoice_prefix(), $order->get( 'id' ) );
		}

		return array(
			'RETURNURL' => llms_confirm_payment_url( $order->get( 'order_key' ) ),
			'CANCELURL' => llms_cancel_payment_url(),
			'REQCONFIRMSHIPPING' => 0,
			'NOSHIPPING' => 1,
			'ALLOWNOTE' => 0,

			'EMAIL' => $order->get( 'billing_email' ),

			'BRANDNAME' => $this->get_brand_name(),
			'CHANNELTYPE' => 'Merchant',
			'LANDINGPAGE' => apply_filters( 'llms_paypal_landing_page', 'Login', $order ),
			'PAGESTYLE' => $this->gateway->get_page_style(),

			'PAYMENTREQUEST_0_AMT' => $order->get_price( 'total', array(), 'float' ),
			'PAYMENTREQUEST_0_CURRENCYCODE' => get_lifterlms_currency(),
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
			'PAYMENTREQUEST_0_TAXAMT' => 0,
			'PAYMENTREQUEST_0_DESC' => $this->get_description( $order, 'single-description' ),
			'PAYMENTREQUEST_0_CUSTOM' => json_encode( array( 'order_id' => $order->get( 'id' ), 'order_key' => $order->get( 'order_key' ) ) ),
			'PAYMENTREQUEST_0_INVNUM' => $invoice_number,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',

			'L_PAYMENTREQUEST_0_AMT0' => $order->get_price( 'total', array(), 'float' ),
			'L_PAYMENTREQUEST_0_DESC0' => $this->get_description( $order, 'single-description' ),
			'L_PAYMENTREQUEST_0_NAME0' => $this->get_description( $order, 'single-name' ),
			'L_PAYMENTREQUEST_0_NUMBER0' => $order->get( 'product_sku' ),
		);
	}

	/**
	 * Execute a PayPal Request via wp_safe_remote_post
	 * @param    string    $action  the METHOD to pass to PayPal
	 * @param    array     $data    array of NVP data to pass to PayPal
	 * @return   array|WP_Error
	 * @since    1.0.0
	 * @version  1.1.2
	 */
	private function make_request( $action, $data ) {

		$req = wp_safe_remote_post( $this->get_request_url(), $this->build_request( $action, $data ) );

		$this->gateway->log( 'PayPal make_request `LLMS_PayPal_Request()->make_request()` called', $action, $data, $req );

		// return an error if it's an error
		if ( is_wp_error( $req ) ) {

			return $req;

		}
		// ensure we have a requst body, code, etc...
		elseif ( isset( $req['body'] ) && isset( $req['response'] ) && isset( $req['response']['code'] ) ) {

			// 200 is good
			if ( 200 === $req['response']['code'] ) {

				parse_str( urldecode( $req['body'] ), $data );

				$this->gateway->log( 'PayPal parsed response body', $data );

				// make sure we get an actual success
				if ( isset( $data['ACK'] ) && ( 'Success' === $data['ACK'] || 'SuccessWithWarning' === $data['ACK'] ) ) {

					return $data;

				}
				// failure or something else...
				else {

					return new WP_Error( $data['L_ERRORCODE0'], $data['L_SEVERITYCODE0'] . ': ' . $data['L_LONGMESSAGE0'] . ' (' . $data['L_ERRORCODE0'] . ')', $data );

				}

			}
			// not 200
			else {

				return new WP_Error( $req['response']['code'], $req['response']['message'] );

			}

		}

		// what happened?
		return new WP_Error( '', sprintf( _x( 'An unknown error occurred while attempting to communicate with %s', 'PayPal communication error', 'lifterlms' ), $this->gateway->get_title() ) );

	}

	/**
	 * Process a Refund for a transaction
	 * @param    float      $amount       amount to refund
	 * @param    obj        $transaction  instance of the LLMS_Transaction
	 * @param    string     $note         optional refund note to pass to PayPal
	 * @return   WP_Error|array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function refund_transaction( $amount, $transaction, $note = '' ) {

		$data = array(
			'AMT' => $amount,
			'CURRENCYCODE' => $transaction->get( 'currency' ),
			'PAYERID' => $transaction->get( 'gateway_customer_id' ),
			'REFUNDSOURCE' => apply_filters( 'llms_paypal_refund_source', 'any', $amount, $transaction ),
			'SHIPPINGAMT' => 0,
			'TAXAMT' => 0,
			'TRANSACTIONID' => $transaction->get( 'gateway_transaction_id' ),
		);

		// full refund
		$data['REFUNDTYPE'] = ( $amount == $transaction->get_price( 'amount', array(), 'float' ) ) ? 'Full' : 'Partial';

		if ( $note ) {
			$data['NOTE'] = $note;
		}

		return $this->make_request( 'RefundTransaction', $data );

	}

	/**
	 * Execute a SetExpressCheckout API call based on a pending order
	 * @param    obj     $order   Instance of the LLMS_Order
	 * @param    string  $intent  Intetion of the express checkout
	 *                            "charge" for a new order
	 *                            "switch" when updating payment source for an existing order
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	public function set_express_checkout( $order, $intent = 'charge' ) {

		// if it's a recurring order and reference transactions are enabled we'll setup a billing agreement
		if ( $order->is_recurring() && 'yes' === $this->gateway->are_reference_transactions_enabled() ) {
			$data = $this->get_recurring_express_checkout_data( $order );
		// if it's not recurring or it is recurring & we don't have reference transactions enabled
		} else {
			$data = $this->get_single_express_checkout_data( $order );
		}

		if ( 'switch' === $intent ) {
			$data['RETURNURL'] = add_query_arg( array(
				'confirm-switch' => 'paypal',
				'order' => $order->get( 'order_key' ),
			), $order->get_view_link() );
			$data['CANCELURL'] = add_query_arg( 'confirm-switch', 'paypal', $order->get_view_link() );
		}
		$req = $this->make_request( 'SetExpressCheckout', $data );
		if ( is_wp_error( $req ) ) {
			return $req;
		} elseif ( isset( $req['TOKEN'] ) ) {
			return $this->get_redirect_url( $req['TOKEN'] );
		}
	}

}
