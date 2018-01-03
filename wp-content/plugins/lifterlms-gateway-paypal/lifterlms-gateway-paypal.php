<?php
/**
* Plugin Name: LifterLMS PayPal Gateway
* Plugin URI: https://lifterlms.com/
* Description: Sell LifterLMS courses and memberships using PayPal Express Checkout
* Version: 1.1.2
* Author: Thomas Patrick Levy
* Author URI: https://lifterlms.com
* Text Domain: lifterlms-paypal
* Domain Path: /i18n
* License:     GPLv2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Requires at least: 4.2
* Tested up to: 4.5.3
*
* @package 		LifterLMS PayPal
* @category 	Core
* @author 		LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Restrict direct access

if ( ! class_exists( 'LifterLMS_PayPal') ) :

final class LifterLMS_PayPal {

	/**
	 * Plugin Version
	 */
	public $version = '1.1.2';

	/**
	 * Singleton class instance
	 * @var  obj
	 * @since  1.0.0
	 * @version  1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Instance of LifterLMS_PayPal
	 * Ensures only one instance of LifterLMS_PayPal is loaded or can be loaded.
	 * @see LLMS_Gateway_PayPal()
	 * @return LifterLMS_PayPal - Main instance
	 * @since  1.0.0
	 * @version  1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 * @return   void
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	private function __construct() {

		$this->define_constants();

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		add_action( 'plugins_loaded', array( $this, 'init' ), 10 );

	}

	/**
	 * Define plugin constants
	 * @return   void
	 * @since    1.0.0
	 * @version  1.1.0
	 */
	private function define_constants() {

		// LLMS PayPal Version
		if ( ! defined( 'LLMS_PAYPAL_VERSION' ) ) {
			define( 'LLMS_PAYPAL_VERSION', $this->version );
		}

		// LLMS PayPal Plugin File
		if ( ! defined( 'LLMS_PAYPAL_PLUGIN_FILE' ) ) {
			define( 'LLMS_PAYPAL_PLUGIN_FILE', __FILE__ );
		}

		// LLMS PayPal Plugin Directory
		if ( ! defined( 'LLMS_PAYPAL_PLUGIN_DIR' ) ) {
			define( 'LLMS_PAYPAL_PLUGIN_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/');
		}

	}

	/**
	 * Initialize, require, add hooks & filters
	 * @return  void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function init() {

		// can only function with LifterLMS 3.0.0 or later
		if ( function_exists( 'LLMS' ) && version_compare( '3.0.0-alpha', LLMS()->version, '<=' ) ) {

			add_action( 'lifterlms_settings_save_checkout', array( $this, 'maybe_check_reference_transactions' ) );
			add_filter( 'lifterlms_payment_gateways', array( $this, 'register_gateway' ), 10, 1 );

			require_once 'includes/class.llms.payment.gateway.paypal.php';
			require_once 'includes/class.llms.paypal.request.php';

		}

	}

	/**
	 * Load Localization files
	 *
	 * The first loaded file takes priority
	 *
	 * Files can be found in the following order:
	 * 		WP_LANG_DIR/lifterlms/lifterlms-paypal-LOCALE.mo
	 * 		WP_LANG_DIR/plugins/lifterlms-paypal-LOCALE.mo
	 *
	 * @return   void
	 * @since    1.1.0
	 * @version  1.1.0
	 */
	public function load_textdomain() {

		// load locale
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms-paypal' );

		// load a lifterlms specific locale file if one exists
		load_textdomain( 'lifterlms-paypal', WP_LANG_DIR . '/lifterlms/lifterlms-paypal-' . $locale . '.mo' );

		// load localization files
		load_plugin_textdomain( 'lifterlms-paypal', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n' );

	}

	/**
	 * When saving the Checkout tab, check reference transactions if the check button was clicked
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function maybe_check_reference_transactions() {

		$gateways = LLMS()->payment_gateways();
		$g = $gateways->get_gateway_by_id( 'paypal' );

		$check = false;

		// if live creds have changed we should check ref transactions on the new creds
		if ( isset( $_POST[ $g->get_option_name( 'live_api_username' ) ] ) && $g->get_live_api_username() !== $_POST[ $g->get_option_name( 'live_api_username' ) ] ) {

			$check = true;

		} elseif ( isset( $_POST['llms_gateway_paypal_check_ref_trans'] ) ) {

			$check = true;

		}

		// checkem
		if ( $check ) {

			// wait until after settings are saved so that the check will always be run with the credentials that we're just submitted
			add_action( 'lifterlms_settings_saved', array( $g, 'check_reference_transactions' ) );

		}

	}

	/**
	 * Register the gateway with LifterLMS
	 * @param   array $gateways array of currently registered gateways
	 * @return  array
	 * @since  1.0.0
	 * @version  1.0.0
	 */
	public function register_gateway( $gateways ) {

		$gateways[] = 'LLMS_Payment_Gateway_PayPal';

		return $gateways;

	}

}

endif;

/**
 * Returns the main instance of LifterLMS_PayPal
 * @return LifterLMS
 * @since  1.0.0
 * @version  1.0.0
 */
function LLMS_Gateway_PayPal() {
	return LifterLMS_PayPal::instance();
}
return LLMS_Gateway_PayPal();
