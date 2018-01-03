<?php
/**
 * PayPal Payment Gateway for LifterLMS
 *
 * @since    1.0.0
 * @version  1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Payment_Gateway_PayPal extends LLMS_Payment_Gateway {

	/**
	 * PayPal Dashboard URL for LIVE transactions
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const LIVE_DASHBOARD_URL = 'https://www.paypal.com/cgi-bin/webscr';

	/**
	 * Maximum transaction amount
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const MAX_AMOUNT = 10000.00;

	/**
	 * Minimum transaction amount
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const MIN_AMOUNT = 0.01;

	/**
	 * PayPal API states that transactions can only be refunded
	 * for up to 60 days after the transaction date
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const REFUND_DAYS = 60;

	/**
	 * PayPal Dashboard URL for SANDBOX transactions
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	const SANDBOX_DASHBOARD_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

	/**
	 * Invoice Prefix
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $invoice_prefix = '';

	/**
	 * Live API password
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $live_api_password = '';

	/**
	 * Live API signature
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $live_api_signature = '';

	/**
	 * Live API username
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $live_api_username = '';

	/**
	 * Page Style
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $page_style = '';

	/**
	 * Reference Transactions status
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $reference_transactions_enabled = '';

	/**
	 * Test API password
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $test_api_password = '';
	/**
	 * Test API signature
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $test_api_signature = '';

	/**
	 * Test API username
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $test_api_username = '';

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	public function __construct() {

		$this->id = 'paypal';
		$this->icon = '<a href="https://www.paypal.com/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open(\'https://www.paypal.com/webapps/mpp/paypal-popup\',\'WIPaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;"><img src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_SbyPP_mc_vs_dc_ae.jpg" border="0" alt="PayPal Acceptance Mark"></a>';
		$this->admin_description = __( 'Allow customers to purchase courses and memberships using PayPal Express Checkout.', 'lifterlms-paypal' );
		$this->admin_title = 'PayPal';
		$this->title = 'PayPal';
		$this->description = __( 'Pay via PayPal', 'lifterlms-paypal' );

		$this->reference_transactions_enabled = $this->are_reference_transactions_enabled();

		$this->supports = array(
			'refunds' => true,
			'single_payments' => true,
			'recurring_payments' => true,
			'recurring_retry' => ( 'yes' === $this->reference_transactions_enabled ) ? true : false,
			'test_mode' => true,
		);

		$this->admin_order_fields = wp_parse_args( array(
			'subscription' => ( 'yes' === $this->reference_transactions_enabled ) ? true : false,
			'customer' => true,
		), $this->admin_order_fields );

		$this->test_mode_description = __( 'PayPal Sandbox can be used to process test transactions. Register for a developer account <a href="https://developer.paypal.com/">here</a>.', 'lifterlms-paypal' ) ;
		$this->test_mode_title = __( 'PayPal Sandbox', 'lifterlms-paypal' );

		// add paypal specific fields
		add_filter( 'llms_get_gateway_settings_fields', array( $this, 'settings_fields' ), 10, 2 );

		// output paypal account details on confirm screen
		add_action( 'lifterlms_checkout_confirm_after_payment_method', array( $this, 'after_payment_method_details' ) );

	}

	/**
	 * Output some information we need on the confirmation screen
	 * @return   void
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	public function after_payment_method_details() {

		$key = isset( $_GET['order'] ) ? $_GET['order'] : '';

		$order = llms_get_order_by_key( $key );
		if ( ! $order ) {
			return;
		} elseif ( 'paypal' !== $order->get( 'payment_gateway' ) ) {
			if ( ! isset( $_GET['confirm-switch'] ) || 'paypal' !== $_GET['confirm-switch'] ) {
				return;
			}
		}

		$req = new LLMS_PayPal_Request( $this );
		$r = $req->get_express_checkout_details( $_GET['token'] );

		echo '<input name="llms_paypal_token" type="hidden" value="' . $_GET['token'] . '">';
		echo '<input name="llms_paypal_payer_id" type="hidden" value="' . $_GET['PayerID'] . '">';

		if ( isset( $r['EMAIL'] ) ) {
			echo '<div class=""><span class="llms-label">' . __( 'PayPal Email:', 'lifterlms-paypal' ) . '</span> ' . $r['EMAIL'] . '</div>';
		}

	}

	/**
	 * Get reference_transactions_enabled option
	 * @return   string  [yes|no]
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	public function are_reference_transactions_enabled() {
		// reference transactions are automatically enabled for test mode
		if ( $this->is_test_mode_enabled() ) {
			return 'yes';
		}
		return $this->get_option( 'reference_transactions_enabled', 'no' );
	}

	/**
	 * Retrieve the base dashboard URL based on api mode
	 * @param    string     $api_mode  live or test
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_dashboard_url( $api_mode = 'live' ) {
		return 'live' === $api_mode ? self::LIVE_DASHBOARD_URL : self::SANDBOX_DASHBOARD_URL;
	}

	/**
	 * Get a direct URL to a subscription (billing agreement) on PayPal's website
	 * @param    string     $subscription_id  Gateway's subscription ID
	 * @param    string     $api_mode         Link to either the live or test site for the gateway, where applicabale
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_subscription_url( $subscription_id, $api_mode = 'live' ) {

		return add_query_arg(
			array(
				'cmd' => '_profile-merchant-pull',
				'flag_flow' => 'merchant',
				'mp_id' => $subscription_id,
			),
			$this->get_dashboard_url( $api_mode )
		);

	}

	/**
	 * Checks if ref transactions are enabled
	 * Saves the the related option and outputs error / success messages
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function check_reference_transactions() {

		$req = new LLMS_PayPal_Request( $this );
		$r = $req->check_reference_transactions();

		// errorrrrrrr
		if ( is_wp_error( $r ) ) {

			// this is the not enabled message, we'll make the message a little more instructive for our purposes
			if ( 11452 == $r->get_error_code() ) {

				LLMS_Admin_Settings::set_error( sprintf( __( 'Reference transactions are not enabled for your PayPal account, please follow the instructions <a href="https://lifterlms.com/docs/lifterlms-paypal-reference-transactions" target="_blank">here</a> and try again later.', 'lifterlms-paypal' ), '#' ) );

			}

			// a different error, display the generic error
			else {

				LLMS_Admin_Settings::set_error( $r->get_error_message() );

			}

			update_option( $this->get_option_name( 'reference_transactions_enabled' ), 'no' );

		}

		// success
		else {

			LLMS_Admin_Settings::set_message( __( 'Reference transactions are enabled on your PayPal account. Recurring access plans can now be purchased using PayPal.', 'lifterlms-paypal' ) );
			update_option( $this->get_option_name( 'reference_transactions_enabled' ), 'yes' );

		}

	}


	/**
	 * Confirm a Payment
	 * Called by LLMS_Controller_Orders->confirm_pending_order() on confirm form submission
	 *
	 * @param    obj       $order   Instance LLMS_Order for the order being processed
	 * @return   void
	 * @since    1.0.0
	 * @version  1.1.1
	 */
	public function confirm_pending_order( $order, $action = 'pay' ) {

		$this->log( 'PayPal `confirm_pending_order()` started', $order, $_POST );

		if ( ! isset( $_POST['llms_paypal_token'] ) || ! isset( $_POST['llms_paypal_payer_id'] ) ) {
			return llms_add_notice( __( 'Missing required data necessary to confirm the order.', 'lifterlms' ), 'error' );
		}

		$order->set( 'gateway_customer_id', $_POST['llms_paypal_payer_id'] );

		$req = new LLMS_PayPal_Request( $this );

		if ( $order->is_recurring() && 'yes' === $this->are_reference_transactions_enabled() ) {

			$r = $req->get_express_checkout_details( $_POST['llms_paypal_token'] );

			if ( isset( $r['BILLINGAGREEMENTACCEPTEDSTATUS'] ) && 1 == $r['BILLINGAGREEMENTACCEPTEDSTATUS'] ) {

				$agreement = $req->create_billing_agreement( $_POST['llms_paypal_token'] );

				// error
				if ( is_wp_error( $agreement ) ) {

					$this->log( $agreement, 'PayPal `confirm_pending_order()` finished with errors' );

					return llms_add_notice( $agreement->get_error_message(), 'error' );

				}

				// success
				else {

					// record the billing agreement id
					$order->set( 'gateway_subscription_id', $agreement['BILLINGAGREEMENTID'] );

					if ( 'pay' === $action ) {

						$ref_trans = $this->do_reference_transaction( $order, 'initial' );

						if ( is_wp_error( $ref_trans ) ) {

							$this->log( $ref_trans, 'PayPal `confirm_pending_order()` finished with errors' );
							return llms_add_notice( $ref_trans->get_error_message(), 'error' );

						} else {

							$this->complete_transaction( $order );

						}

					}

				}


			}
			// billing agreement wasn't accepted
			else {

				$this->log( $r, 'PayPal `confirm_pending_order()` finished, billing agreement was not accepted.' );
				$order->set( 'status', 'llms-failed' );

				llms_add_notice( __( 'The PayPal billing agreement was not accepted. The pending order has been cancelled.', 'lifterlms-paypal' ), 'error' );
				wp_safe_redirect( llms_cancel_payment_url() );
				exit();

			}

		} else {

			$r = $req->do_express_checkout( $order, $_POST['llms_paypal_token'], $_POST['llms_paypal_payer_id'] );

			if ( is_wp_error( $r ) ) {

				$this->log( $r, 'PayPal `confirm_pending_order()` finished with errors' );

				return llms_add_notice( $r->get_error_message(), 'error' );

			} else {

				$txn_data = array();

				$payer_id = sanitize_text_field( $_POST['llms_paypal_payer_id'] );

				$txn_data['amount'] = $r['PAYMENTINFO_0_AMT'];
				$txn_data['customer_id'] = $payer_id;
				$txn_data['transaction_id'] = $r['PAYMENTINFO_0_TRANSACTIONID'];
				$txn_data['fee_amount'] = $r['PAYMENTINFO_0_FEEAMT'];
				$txn_data['completed_date'] = $r['PAYMENTINFO_0_ORDERTIME']; // record function transposes date automatically
				$txn_data['status'] = 'llms-txn-succeeded';
				$txn_data['payment_type'] = 'single';
				$txn_data['source_description'] = __( 'PayPal Account', 'lifterlms-paypal' );
				$txn_data['source_id'] = $payer_id;

				$order->record_transaction( $txn_data );

				$order->add_note( sprintf(
					__( 'Charge Succeeded! PayPal Account ID: "%s". Transaction ID: %s', 'lifterlms-paypal' ),
					$payer_id,
					$r['PAYMENTINFO_0_TRANSACTIONID']
				) );

				$this->log( $r, 'PayPal `confirm_pending_order()` finished' );

				$this->complete_transaction( $order );

			}

		}

	}

	/**
	 * Process reference transactions
	 * This is called by $this->handle_recurring_transaction() to process the recurring payment
	 *                by $this->confirm_payment() to process the initial payment on a new order
	 *
	 * @param    object     $order         instance of the LLMS_Order
	 * @param    string     $payment_type  payment type [initial|recurring]
	 * @return   WP_Error|LLMS_Transaction
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function do_reference_transaction( $order, $payment_type = 'recurring' ) {

		$this->log( 'PayPal `do_reference_transaction()` started' );

		$req = new LLMS_PayPal_Request( $this );

		$charge = 'initial' === $payment_type ? $order->get_initial_price( array(), 'float' ) : $order->get_price( 'total', array(), 'float' );


		if ( $charge <= 0 && ! $order->has_trial() ) {

			$this->log( $charge, 'PayPal `do_reference_transaction()` finished with errors', 'nothing to charge' );
			return;
		}

		// trial, record a free transaction
		if ( floatval( 0 ) === $charge ) {

			$txn = $order->record_transaction( array(
				'amount' => $charge,
				'completed_date' => current_time( 'mysql' ),
				'customer_id' => $order->get( 'gateway_customer_id' ),
				'source_description' => __( 'Free Trial', 'lifterlms' ),
				'status' => 'llms-txn-succeeded',
				'payment_type' => 'trial',
			) );

			$order->add_note( __( 'Free trial started', 'lifterlms-paypal' ) );

			return $txn;

		} else {

			$txn = $order->record_transaction( array(
				'amount' => $charge,
				'customer_id' => isset( $_POST['llms_paypal_payer_id'] ) ? sanitize_text_field( $_POST['llms_paypal_payer_id'] ) : $order->get( 'gateway_customer_id' ),
				'status' => 'llms-txn-pending',
				'payment_type' => ( $order->has_trial() && 'initial' === $payment_type ) ? 'trial' : 'recurring',
			) );

			$r = $req->do_reference_transaction( $order, $txn->get( 'id' ), $payment_type );

			if ( is_wp_error( $r ) ) {

				$txn->set( 'status', 'llms-txn-failed' );

				$this->log( $r, 'PayPal `do_reference_transaction()` finished with errors' );

				$order->add_note( __( '%s charge failed via PayPal!', 'lifterlms-paypal' ) );

				return $r;

			} else {

				$order->add_note( sprintf(
					__( '%s charge succeeded via PayPal! [Transaction ID: %s]', 'lifterlms-paypal' ),
					$payment_type,
					$r['TRANSACTIONID']
				) );

				$txn->set( 'completed_date', $r['ORDERTIME'] );
				$txn->set( 'gateway_fee_amount', $r['FEEAMT'] );
				$txn->set( 'gateway_source_id', $r['BILLINGAGREEMENTID'] );
				$txn->set( 'gateway_source_description', __( 'Billing Agreement', 'lifterlms-paypal' ) );
				$txn->set( 'gateway_transaction_id', $r['TRANSACTIONID'] );
				$txn->set( 'status', 'llms-txn-succeeded' );

				$this->log( $r, 'PayPal `do_reference_transaction()` finished' );

				return $txn;

			}

		}

	}

	/**
	 * Get invoice_prefix option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_invoice_prefix() {
		return $this->get_option( 'invoice_prefix' );
	}

	/**
	 * Get live_api_password option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_live_api_password() {
		return $this->get_option( 'live_api_password' );
	}

	/**
	 * Get live_api_signature option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_live_api_signature() {
		return $this->get_option( 'live_api_signature' );
	}

	/**
	 * Get live_api_username option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_live_api_username() {
		return $this->get_option( 'live_api_username' );
	}

	/**
	 * Get page_style option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_page_style() {
		return $this->get_option( 'page_style' );
	}

	/**
	 * Get test_api_password option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_test_api_password() {
		return $this->get_option( 'test_api_password' );
	}

	/**
	 * Get test_api_signature option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_test_api_signature() {
		return $this->get_option( 'test_api_signature' );
	}

	/**
	 * Get test_api_username option
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_test_api_username() {
		return $this->get_option( 'test_api_username' );
	}

	/**
	 * Called when the Update Payment Method form is submitted from a single order view on the student dashboard
	 *
	 * Gateways should do whatever the gateway needs to do to validate the new payment method and save it to the order
	 * so that future payments on the order will use this new source
	 *
	 * @param    obj     $order      Instance of the LLMS_Order
	 * @param    array   $form_data  Additional data passed from the submitted form (EG $_POST)
	 * @return   void
	 * @since    1.1.0
	 * @version  1.1.0
	 */
	public function handle_payment_source_switch( $order, $form_data = array() ) {

		$previous_gateway = $order->get( 'payment_gateway' );

		$this->log( 'PayPal `handle_payment_source_switch()` started', $order, $form_data );

		$validate = $this->validate_transaction( $order );
		if ( is_wp_error( $validate ) ) {
			return llms_add_notice( $validate->get_error_message(), 'error' );
		}

		$token = isset( $form_data['llms_paypal_token'] ) ? sanitize_text_field( $form_data['llms_paypal_token'] ) : false;

		// need to get a token, redirect to paypal for approval
		if ( ! $token ) {

			$req = new LLMS_PayPal_Request( $this );
			$r = $req->set_express_checkout( $order, 'switch' );

			if ( is_wp_error( $r ) ) {

				$this->log( $r, 'PayPal `handle_payment_source_switch()` finished with errors' );

				return llms_add_notice( $r->get_error_message(), 'error' );

			} else {

				$this->log( $r, 'PayPal `handle_payment_source_switch()` finished' );

				do_action( 'lifterlms_handle_payment_source_switch', $order );

				wp_redirect( $r );

				exit();

			}

		// have a token, update the order and maybe do a ref. transaction
		} else {

			$action = isset( $form_data['llms_switch_action'] ) ? sanitize_text_field( $form_data['llms_switch_action'] ) : false;

			// valid action
			if ( in_array( $action, array( 'pay', 'switch' ) ) ) {

				$order->set( 'payment_gateway', $this->get_id() );
				$order->set( 'gateway_customer_id', '' );
				$order->set( 'gateway_source_id', '' );
				$order->set( 'gateway_subscription_id', '' );

				$order->add_note( sprintf( __( 'Payment method switched to "%1$s"', 'lifterlms' ), $this->get_admin_title() ) );

				$this->confirm_pending_order( $order, $action );

				wp_safe_redirect( $order->get_view_link() );

			// shouldn't happen
			} else {

				return llms_add_notice( __( 'Invalid payment source switching action.', 'lifterlms' ), 'error' );

			}

		}

	}

	/**
	 * Handle a Pending Order
	 * Called by LLMS_Controller_Orders->create_pending_order() on checkout form submission
	 * All data will be validated before it's passed to this function
	 *
	 * @param   obj       $order   Instance LLMS_Order for the order being processed
	 * @param   obj       $plan    Instance LLMS_Access_Plan for the order being processed
	 * @param   obj       $person  Instance of LLMS_Student for the purchasing customer
	 * @param   obj|false $coupon  Instance of LLMS_Coupon applied to the order being processed, or false when none is being used
	 * @return  void
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	public function handle_pending_order( $order, $plan, $person, $coupon = false ) {

		$this->log( 'PayPal `handle_pending_order()` started', $order, $plan, $person, $coupon );

		$validate = $this->validate_transaction( $order, $plan );
		if ( is_wp_error( $validate ) ) {
			return llms_add_notice( $validate->get_error_message(), 'error' );
		}

		$req = new LLMS_PayPal_Request( $this );
		$r = $req->set_express_checkout( $order );

		if ( is_wp_error( $r ) ) {

			$this->log( $r, 'PayPal `handle_pending_order()` finished with errors' );

			return llms_add_notice( $r->get_error_message(), 'error' );

		} else {

			$this->log( $r, 'PayPal `handle_pending_order()` finished' );

			do_action( 'lifterlms_handle_pending_order_complete', $order );

			wp_redirect( $r );

			exit();

		}

	}

	/**
	 * Called by scheduled actions to charge an order for a scheduled recurring transaction
	 * This function must be defined by gateways which support recurring transactions
	 * @param    obj       $order   Instance LLMS_Order for the order being processed
	 * @return   void
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	public function handle_recurring_transaction( $order ) {

		$this->log( 'PayPal `handle_recurring_transaction()` started', $order );

		if ( 'yes' === $this->are_reference_transactions_enabled() ) {

			$ref = $this->do_reference_transaction( $order, 'recurring' );

			if ( is_wp_error( $ref ) ) {

				$this->log( 'PayPal `handle_recurring_transaction()` finished with errors', $ref );

			} else {

				$this->log( 'PayPal `handle_recurring_transaction()` finished', $ref );

			}

		} else {

			// update status
			$order->set_status( 'on-hold' );

			/**
			 * @hooked LLMS_Notification: manual_payment_due - 10
			 */
			do_action( 'llms_manual_payment_due', $order, $this );

		}

	}

	/**
	 * Called when refunding via a Gateway
	 * This function must be defined by gateways which support refunds
	 * This function is called by LLMS_Transaction->process_refund()
	 * @param    obj     $transaction  Instance of the LLMS_Transaction
	 * @param    float   $amount       Amount to refund
	 * @param    string  $note         Optional refund note to pass to the gateway
	 * @return   string|WP_Error       refund id on success, WP_Error otherwise
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function process_refund( $transaction, $amount = 0, $note = '' ) {

		$this->log( 'PayPal `process_refund()` started', $transaction, $amount, $note );

		if ( ! $transaction ) {
			return new WP_Error( 'error', __( 'Missing or invalid transaction.', 'lifterlms-paypal' ) );
		}

		if ( $transaction->get_date( 'date', 'U' ) < ( current_time( 'timestamp' ) - ( DAY_IN_SECONDS * self::REFUND_DAYS ) ) ) {
			return new WP_Error( 'error', __( 'PayPal cannot process a refund for this transaction because it is more than 60 days old.', 'lifterlms' ) );
		}

		$req = new LLMS_PayPal_Request( $this );
		$r = $req->refund_transaction( $amount, $transaction, $note );

		if ( is_wp_error( $r ) ) {

			$this->log( $r, 'PayPal `process_refund()` finished with errors' );

			return $r;

		} else {

			if ( isset( $r['REFUNDTRANSACTIONID'] ) ) {

				// record the fee refund
				if ( isset( $r['FEEREFUNDAMT'] ) ) {
					$fee = $transaction->get_price( 'gateway_fee_amount', array(), 'float' ) - $r['FEEREFUNDAMT'];
					$transaction->set( 'gateway_fee_amount', $fee );
				}

				$this->log( $r, 'PayPal `process_refund()` finished' );

				return $r['REFUNDTRANSACTIONID'];

			}

		}

		return new WP_Error( 'error', __( 'An unknown error was encountered while attempting to process the refund.', 'lifterlms-paypal' ) );

	}

	/**
	 * Output custom settings fields on the LifterLMS Gateways Screen
	 * @param    array     $fields      array of existing fields
	 * @param    string    $gateway_id  id of the gateway
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function settings_fields( $fields, $gateway_id ) {

		// don't add fields to other gateways!
		if ( $this->id !== $gateway_id ) {
			return $fields;
		}

		$fields[] = array(
			'type'  => 'custom-html',
			'value' => '
				<h4>' . __( 'PayPal Live API Credentials', 'lifterlms-paypal' ) . '</h4>
				<p>' . __( 'Enter your PayPal API credentials to process transactions via PayPal. Learn how to access your PayPal API Credentials <a href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/#creating-classic-api-credentials">here</a>.', 'lifterlms-paypal' ) . '</p>
			',
		);

		$live = array(
			'live_api_username' => __( 'API Username', 'lifterlms-paypal' ),
			'live_api_password' => __( 'API Password', 'lifterlms-paypal' ),
			'live_api_signature' => __( 'API Signature', 'lifterlms-paypal' ),
		);
		foreach( $live as $k => $v ) {
			$fields[] = array(
				'id'            => $this->get_option_name( $k ),
				'default'       => $this->{'get_' . $k}(),
				'title'         => $v,
				'type'          => 'text',
			);
		}

		$fields[] = array(
			'type'  => 'custom-html',
			'value' => '
				<h4>' . __( 'PayPal Sandbox API Credentials', 'lifterlms-paypal' ) . '</h4>
				<p>' . __( 'Enter your PayPal Sandbox API credentials to process transactions via PayPal is in Sandbox mode. Learn how to access your PayPal API Credentials <a href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/#creating-classic-api-credentials">here</a>.', 'lifterlms-paypal' ) . '</p>
			',
		);

		$test = array(
			'test_api_username' => __( 'API Username', 'lifterlms-paypal' ),
			'test_api_password' => __( 'API Password', 'lifterlms-paypal' ),
			'test_api_signature' => __( 'API Signature', 'lifterlms-paypal' ),
		);
		foreach( $test as $k => $v ) {
			$fields[] = array(
				'id'            => $this->get_option_name( $k ),
				'default'       => $this->{'get_' . $k}(),
				'title'         => $v,
				'type'          => 'text',
			);
		}

		$fields[] = array(
			'type'  => 'custom-html',
			'value' => '<h4>' . __( 'Additional Options', 'lifterlms-paypal' ) . '</h4>',
		);

		$fields[] = array(
			'id'            => $this->get_option_name( 'invoice_prefix' ),
			'default'       => apply_filters( 'llms_paypal_default_invoice_prefix', 'LLMS-' ),
			'desc' 		    => '<br>' . __( 'Specify a prefix to be added to invoices. If you use your PayPal account for multiple stores, ensure that this prefix is unique as PayPal will not allow duplicate invoice numbers.', 'lifterlms' ),
			'title'         => __( 'Invoice Prefix', 'lifterlms-paypal' ),
			'type'          => 'text',
		);

		$fields[] = array(
			'id'            => $this->get_option_name( 'page_style' ),
			'desc' 		    => '<br>' . sprintf( __( 'Optionally enter the name of a custom page style defined in your PayPal account. You can learn more about page styles <a href="%s" target="_blank">here</a>.', 'lifterlms' ), 'https://www.paypal.com/customize' ),
			'title'         => __( 'Page Style', 'lifterlms-paypal' ),
			'type'          => 'text',
		);

		// conditionally show this

		if ( 'yes' !== $this->are_reference_transactions_enabled() ) {

			$fields[] = array(
				'type'  => 'custom-html',
				'value' => '
					<h4>' . __( 'PayPal Reference Transactions', 'lifterls' ) . '</h4>
					<p class="description">' . sprintf( __( '<a href="%s" target="_blank">Reference Transactions</a> are required to process transactions for recurring access plans. Click the button below to check if they are enabled on your PayPal Account.', 'lifterlms-paypal' ), 'https://lifterlms.com/docs/lifterlms-paypal-reference-transactions' ) . '</p>
					<br><button class="llms-button-primary" name="' . $this->get_option_name( 'check_ref_trans' ) . '" type="submit">' . __( 'Check Now', 'lifterlms-paypal' ) . '</button>
				',
			);

		}

		$fields[] = array(
			'title'     => __( 'Activation Key', 'lifterlms-paypal' ),
			'desc' 		=> '<br>' . sprintf( __( 'Required for support and automated plugin updates. Located on your %sLifterLMS Account Settings page%s.', 'lifterlms-paypal' ), '<a href="https://lifterlms.com/my-account/" target="_blank">', '</a>' ),
			'id' 		=> 'lifterlms_paypal_activation_key',
			'type' 		=> 'llms_license_key',
			'default'	=> '',
			'extension' => LLMS_PAYPAL_PLUGIN_FILE,
		);

		if ( ! class_exists( 'LLMS_Helper' ) ) {

			$fields[] = array(
				'type' => 'custom-html',
				'value' => '<p>' . sprintf(
					__( 'Install the %s to start receiving automatic updates for this extension.', 'lifterlms-paypal' ),
					'<a href="https://lifterlms.com/docs/lifterlms-helper/" target="_blank">LifterLMS Helper</a>'
			 	) . '</p>',
			);

		}

		return $fields;

	}

	/**
	 * Determine if a transaction is valid based on order and plan submitted
	 * used for creating pending orders and switching payment sources on existing orders
	 * @param    obj        $order  LLMS_Order
	 * @param    obj        $plan   LLMS_Access_Plan, if not submitted will be retrieved from the $order
	 * @return   mixed              WP_Error when invalid, true if valid
	 * @since    1.1.0
	 * @version  1.1.0
	 */
	private function validate_transaction( $order, $plan = null ) {

		// do some gateway specific validation before proceeding
		$total = $order->get_price( 'total', array(), 'float' );
		if ( $total < self::MIN_AMOUNT ) {
			return new WP_Error( 'error', sprintf( __( 'PayPal cannot process transactions for less than %s', 'lifterlms-paypal' ), self::MIN_AMOUNT ) );
		} elseif ( $total > self::MAX_AMOUNT ) {
			return new WP_Error( 'error', sprintf( __( 'PayPal cannot process transactions for more than %s', 'lifterlms-paypal' ), self::MAX_AMOUNT ) );
		}

		if ( ! $plan ) {
			$plan = llms_get_post( $order->get( 'plan_id' ) );
		}

		// for recurring ensure recurring is enabled
		if ( $plan->is_recurring() && ! $this->supports( 'recurring_payments' ) ) {
			return new WP_Error( 'error', __( 'PayPal cannot process an order for a recurring access plan at this time.', 'lifterlms' ) );
		}

		return true;

	}

}
