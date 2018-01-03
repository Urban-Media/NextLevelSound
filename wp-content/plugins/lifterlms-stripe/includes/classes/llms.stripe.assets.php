<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handle registration, localization, and enqueues for scripts & styles
 * @since    4.0.0
 * @version  4.3.0
 */
class LLMS_Stripe_Assets {

	/**
	 * [$min description]
	 * @var  string
	 */
	private $min = '';

	/**
	 * Constructor
	 * @since    4.3.0
	 * @version  4.3.0
	 */
	public function __construct() {

		// if WP_DEBUG is enabled, load unminifed scripts
		$this->min = ( ! defined( 'WP_DEBUG' ) || false === WP_DEBUG ) ? '.min' : '';

		// Only enqueque assets if if stripe is actually enabled as a payment gateway
		if ( 'yes' === get_option( 'llms_gateway_stripe_enabled', 'no' ) ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );

	}

	/**
	 * Retrieve a filterable array of settings used to localize stripe.js & stripe elements
	 * @see      https://stripe.com/docs/stripe.js#stripe-elements
	 * @see      https://stripe.com/docs/stripe.js#element-options
	 * @return   array
	 * @since    4.3.0
	 * @version  4.3.0
	 */
	private function get_settings() {
		return apply_filters( 'llms_stripe_elements_settings', array(

			// https://stripe.com/docs/stripe.js#stripe-elements
			'elements' => array(
				'locale' => 'auto', // supported tags: ar, da, de, en, es, fi, fr, he, it, ja, no, nl, sv, zh.
			),

			// https://stripe.com/docs/stripe.js#element-options
			'card' => array(
				'style' => array(
					'base' => array(
						'fontSize' => '16px',
						'fontWeight' => 300,
					),
					'invalid' => array(
						'color' => '#e5554e',
					),
				),
			)

		) );
	}

	/**
	 * Register, enqueue, & localize scripts
	 * @return   void
	 * @since    4.0.0
	 * @version  4.3.0
	 */
	public function enqueue() {

		wp_register_script( 'stripe', 'https://js.stripe.com/v3/', false, 'v3', true );

		wp_register_script( 'lifterlms-stripe', plugins_url( 'assets/js/llms-stripe' . $this->min . '.js', LLMS_STRIPE_PLUGIN_FILE ), array( 'jquery', 'stripe', 'llms-form-checkout' ), LLMS_STRIPE_VERSION, true );

		if ( is_llms_checkout() || ( get_current_user_id() && is_llms_account_page() ) ) {

			wp_enqueue_style( 'lifterlms-stripe', plugins_url( 'assets/css/llms-stripe' . $this->min . '.css', LLMS_STRIPE_PLUGIN_FILE ), array(), LLMS_STRIPE_VERSION, 'screen' );

			wp_enqueue_script( 'lifterlms-stripe' );
			$stripe = LLMS()->payment_gateways()->get_gateway_by_id( 'stripe' );

			// localize the script
			wp_localize_script( 'lifterlms-stripe', 'llms_stripe', array(
				'publishable_key' => $stripe->get_publishable_key(),
				'settings' => $this->get_settings(),
			) );

		} else {

			// per stripe, Radar (https://stripe.com/docs/radar) works best when Stripe.js is on *all* pages of the site
			wp_enqueue_script( 'stripe' );

		}

	}

	/**
	 * Enqueue admin scripts
	 * @return   void
	 * @since    4.3.0
	 * @version  4.3.0
	 */
	public function enqueue_admin() {

		$screen = get_current_screen();

		if ( 'lifterlms_page_llms-settings' === $screen->id && isset( $_GET['tab'] ) && 'checkout' === $_GET['tab'] ) {
			wp_enqueue_script( 'lifterlms-stripe-admin', plugins_url( 'assets/js/llms-stripe-admin' . $this->min . '.js', LLMS_STRIPE_PLUGIN_FILE ), array( 'jquery' ), LLMS_STRIPE_VERSION, true );
		}



	}

}

return new LLMS_Stripe_Assets();
