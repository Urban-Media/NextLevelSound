<?php
/**
 * Plugin Name: LifterLMS Gravity Forms
 * Plugin URI: https://lifterlms.com/
 * Description: Integrate your Gravity Forms into LifterLMS lessons
 * Version: 1.0.3
 * Author: Thomas Patrick Levy
 * Author URI: https://lifterlms.com
 * Text Domain: lifterlms-gravity-forms
 * Domain Path: /languages
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 4.2
 * Tested up to: 4.5.3
 *
 * @package 		LifterLMS Gravity Forms
 * @category 	Core
 * @author 		LifterLMS
 */
final class LifterLMS_GravityForms {

	public $version = '1.0.3';
	protected static $_instance = null;

	/**
	 * Main Instance of LifterLMS_GravityForms
	 * Ensures only one instance of LifterLMS_GravityForms is loaded or can be loaded.
	 * @since  1.0.0
	 * @version  1.0.0
	 * @see LLMS_GravityForms()
	 * @return LifterLMS_GravityForms - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 * @since  1.0.0
	 * @version  1.0.0
	 * @return  void
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_GF_PLUGIN_FILE' ) ) {
			define( 'LLMS_GF_PLUGIN_FILE', __FILE__ );
		}
		if ( ! defined( 'LLMS_GF_PLUGIN_DIR' ) ) {
			define( 'LLMS_GF_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
		}
		if ( ! defined( 'LLMS_GF_PLUGIN_TEMPLATES_DIR' ) ) {
			define( 'LLMS_GF_PLUGIN_TEMPLATES_DIR', LLMS_GF_PLUGIN_DIR . 'templates/' );
		}

		add_action( 'plugins_loaded', array( $this, 'init' ), 10 );

	}

	/**
	 * Initialize, require, add hooks & filters
	 * @since  1.0.0
	 * @version  1.0
	 * @return  void
	 */
	public function init() {

		if ( function_exists( 'LLMS' ) ) {

			// gforms related classes
			require_once 'includes/class.llms.integration.gravityforms.php';
			require_once 'includes/class.llms.gravityforms.engagements.php';

			// settings
			require_once 'includes/class.llms.settings.gravityforms.php';

			// helper related functions
			require_once 'includes/class.llms.helper.helper.php';


			// register the integration
			add_filter( 'lifterlms_integrations', array( $this, 'register_integration' ), 10, 1 );

		}

	}

	/**
	 * Register the integration with LifterLMS
	 * @param    array     $integrations  array of LifterLMS Integration Classes
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function register_integration( $integrations ) {
		$integrations[] = 'LLMS_Integration_GravityForms';
		return $integrations;
	}

}
/**
 * Returns the main instance of LLMS
 * @since  1.0.0
 * @version  1.0.0
 * @return LifterLMS
 */
function LLMS_GravityForms() {
	return LifterLMS_GravityForms::instance();
}
return LLMS_GravityForms();