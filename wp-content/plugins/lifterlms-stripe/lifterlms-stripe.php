<?php
/**
 * Plugin Name: LifterLMS Stripe Payment Gateway
 * Plugin URI: https://lifterlms.com/product/stripe-extension/
 * Description: Allow your students to purchase and subscribe to LifterLMS courses and memberships using Stripe.
 * Version: 4.3.2
 * Author: LifterLMS
 * Author URI: https://lifterlms.com
 * Text Domain: lifterlms-stripe
 * Domain Path: /i18n
 *
 * @package 	LifterLMS
 * @category 	Core
 * @author 		codeBOX
 */

/**
 * Restrict direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'LifterLMS_Stripe') ) :

final class LifterLMS_Stripe {

	/**
	 * Current version of the plugin
	 * @var string
	 */
	public $version = '4.3.2';

	/**
	 * Singleton instance of the class
	 * @var obj
	 * @since  1.0.0
	 * @version 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Instance of the LLMS_Stripe_Settings class
	 * @var obj
	 */
	public $settings = null;

	/**
	 * Singleton Instance of the LifterLMS_Stripe class
	 * @return obj    instance of the LifterLMS_Stripe class
	 * @since  1.0.0
	 * @version 1.0.0
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) )
			self::$_instance = new self();

		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * Add actions and filters to get things going
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 4.1.0
	 */
	private function __construct() {

		// define plugin constants
		$this->define_constants();

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		// get started
		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Call the Stripe API
	 * @param  string $resource   resource endpoint to request
	 * @param  array  $data       associative array of data to pass in the request body
	 * @return obj                Instance of LLMS_Stripe_API
	 * @see  LLMS_Stripe_API
	 */
	public function call_api( $resource, $data, $method = 'POST' ) {

		return new LLMS_Stripe_API( $resource, $data, $method );

	}

	/**
	 * Define all constants used by the plugin
	 * @return void
	 */
	private function define_constants() {

		if ( ! defined( 'LLMS_STRIPE_PLUGIN_FILE' ) ) {
			define( 'LLMS_STRIPE_PLUGIN_FILE', __FILE__ );
		}

		if ( ! defined( 'LLMS_STRIPE_PLUGIN_DIR' ) ) {
			define( 'LLMS_STRIPE_PLUGIN_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/' );
		}

		if ( ! defined( 'LLMS_STRIPE_VERSION' ) ) {
			define( 'LLMS_STRIPE_VERSION', $this->version );
		}

	}

	/**
	 * Include files and instantiate classes
	 * @return   void
	 * @since    1.0.0
	 * @version  4.3.0
	 */
	private function includes() {

		require_once LLMS_STRIPE_PLUGIN_DIR . 'includes/classes/llms.stripe.assets.php';
		require_once LLMS_STRIPE_PLUGIN_DIR . 'includes/classes/llms.gateway.stripe.php';
		require_once LLMS_STRIPE_PLUGIN_DIR . 'includes/classes/llms.stripe.api.php';
		require_once LLMS_STRIPE_PLUGIN_DIR . 'includes/classes/llms.stripe.customer.php';
		require_once LLMS_STRIPE_PLUGIN_DIR . 'includes/classes/llms.stripe.crons.php';

		if ( is_admin() ) {

			require_once LLMS_STRIPE_PLUGIN_DIR . 'includes/classes/llms.stripe.database.php';

		}

	}

	/**
	 * Include all required files and classes
	 * @return  void
	 * @since   1.0.0
	 * @version 4.3.0
	 */
	public function init() {

		// only load Stripe plugin if LifterLMS class exists.
		if ( function_exists( 'LLMS' ) && version_compare( '3.0.0-alpha', LLMS()->version, '<=' ) ) {

			add_filter( 'lifterlms_payment_gateways', array( $this, 'register_gateway' ), 10, 1 );

			// check for potential ssl issues
			add_action( 'admin_init', array( $this, 'ssl_notice' ) );

			$this->includes();

		}

	}

	/**
	 * Load Localization files
	 *
	 * The first loaded file takes priority
	 *
	 * Files can be found in the following order:
	 * 		WP_LANG_DIR/lifterlms/lifterlms-stripe-LOCALE.mo
	 * 		WP_LANG_DIR/plugins/lifterlms-stripe-LOCALE.mo
	 *
	 * @return   void
	 * @since    4.1.0
	 * @version  4.1.0
	 */
	public function load_textdomain() {

		// load locale
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms-stripe' );

		// load a lifterlms specific locale file if one exists
		load_textdomain( 'lifterlms-stripe', WP_LANG_DIR . '/lifterlms/lifterlms-stripe-' . $locale . '.mo' );

		// load localization files
		load_plugin_textdomain( 'lifterlms-stripe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Register the gateway with LifterLMS
	 * @param    array $gateways array of currently registered gateways
	 * @return   array
	 * @since    4.0.0
	 * @version  4.0.0
	 */
	public function register_gateway( $gateways ) {

		$gateways[] = 'LLMS_Payment_Gateway_Stripe';

		return $gateways;

	}

	/**
	 * Output a warning about stripe and ssl certs
	 * @return   void
	 * @since    4.0.0
	 * @version  4.3.0
	 */
	public function ssl_notice() {

		if ( ! class_exists( 'LLMS_Admin_Notices' ) ) {
			return;
		}

		$id = 'stripe-ssl-warning';

		if ( 'yes' === get_option( 'llms_gateway_stripe_enabled' ) && ( function_exists( 'llms_is_site_https' ) && ! llms_is_site_https() ) && 'no' === get_option( 'lifterlms_checkout_force_ssl' ) ) {

			$html = sprintf(
				__( 'LifterLMS Stripe is currently enabled but the %sForce SSL%s option is disabled. Your checkout may not be secure and Stripe will only work in test mode! %sLearn More%s.', 'lifterlms-stripe' ),
				'<a href="' . admin_url( 'admin.php?page=llms-settings&tab=checkout' ) . '">', '</a>',
				'<a href="https://lifterlms.com/docs/ssl-and-https/ " target="_blank">', '</a>'
			);

			LLMS_Admin_Notices::add_notice( $id, $html, array(
				'type' => 'error',
				'dismissible' => false,
				'remindable' => false,
			) );

		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {

			LLMS_Admin_Notices::delete_notice( $id );

		}

	}


}
endif;

/**
 * Main Stripe Instance
 * @since  1.0.0
 * @version 4.0.0
 */
function LLMS_Gateway_Stripe() {
	return LifterLMS_Stripe::instance();
}

return LLMS_Gateway_Stripe();
