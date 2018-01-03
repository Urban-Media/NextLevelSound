<?php
/*
Plugin Name: Vimeography Pro
Plugin URI: http://vimeography.com/pro
Description: Vimeography Pro supercharges your Vimeography galleries by adding unlimited videos, custom sorting, Vimeo Pro support, download links, playlisting, hidden videos and more.
Version: 1.1.2
Requires: 3.8
Author: Dave Kiss
Author URI: http://www.davekiss.com/
Copyright: Dave Kiss
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists('json_decode') ) {
  wp_die('Vimeography PRO requires the JSON PHP extension.');
}

if ( ! defined('VIMEOGRAPHY_PRO_URL') ) {
  define( 'VIMEOGRAPHY_PRO_URL', plugin_dir_url(__FILE__) );
}

if ( ! defined('VIMEOGRAPHY_PRO_PATH') ) {
  define( 'VIMEOGRAPHY_PRO_PATH', plugin_dir_path(__FILE__) );
}

if ( ! defined('VIMEOGRAPHY_PRO_BASENAME') ) {
  define( 'VIMEOGRAPHY_PRO_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined('VIMEOGRAPHY_PRO_VERSION') ) {
  define( 'VIMEOGRAPHY_PRO_VERSION', '1.1.2');
}

if ( ! defined('VIMEOGRAPHY_PRO_CURRENT_PAGE') ) {
  define( 'VIMEOGRAPHY_PRO_CURRENT_PAGE', basename( $_SERVER['PHP_SELF'] ) );
}

class Vimeography_Pro {

  /**
   * @var Vimeography_Pro The one true Vimeography_Pro
   * @since 1.0
   */
  private static $instance;


  /**
   * Vimeography_Pro Database Object
   *
   * @var object
   * @since 1.0
   */
  public $database;


  public function __construct() {
    $this->_define_constants();
    $this->_includes();

    // Can be saved in public properties if need to access
    new Vimeography_Pro_Rewrite;
    new Vimeography_Pro_Loader;
    $this->database = new Vimeography_Pro_Database;
    new Vimeography_Pro_Ajax;
    new Vimeography_Pro_Shortcode;

    add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    add_action( 'plugins_loaded', array( $this, 'vimeography_pro_load_addon_plugin' ) );
  }


  /**
   * Main Vimeography_Pro Instance
   *
   * Ensures that only one instance of Vimeography_Pro exists in memory at any one
   * time. Also prevents needing to define globals all over the place.
   *
   * @since 1.0
   * @static
   * @static var array $instance
   * @uses Vimeography_Pro::setup_constants() Setup the constants needed
   * @uses Vimeography_Pro::includes()        Include the required files
   * @uses Vimeography_Pro::load_textdomain() load the language files
   * @see  Vimeography_Pro()
   * @return The one true Vimeography_Pro
   */
  public static function instance() {
    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Vimeography_Pro ) ) {
      self::$instance = new Vimeography_Pro;
    }

    return self::$instance;
  }


  /**
   * Localization
   * @return [type] [description]
   */
  public function load_textdomain() {
    load_plugin_textdomain('vimeography-pro', false, dirname( VIMEOGRAPHY_PRO_BASENAME ) . '/languages/');
  }


  /**
   * Setup plugin constants
   *
   * @access private
   * @since  1.0
   * @return void
   */
  private function _define_constants() {
    global $wpdb;

    if ( ! isset( $wpdb->vimeography_pro_meta ) ) {
      $wpdb->vimeography_pro_meta = $wpdb->prefix . 'vimeography_pro_meta';
    }
  }


  /**
   * Include required files
   *
   * @since  1.0
   * @return void
   */
  private function _includes() {
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/rewrite.php';
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/loader.php';
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/database.php';
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/shortcode.php';
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/ajax.php';
  }


  /**
   * Send the addons meta headers to the Vimeography updater
   * class as a registered addon.
   *
   * @return void
   */
  public function vimeography_pro_load_addon_plugin() {
    do_action('vimeography/load-addon-plugin', __FILE__);
  }

}

/**
 * The main function responsible for returning the one true Vimeography_Pro
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $pro = Vimeography_Pro(); ?>
 *
 * @since 1.0
 * @return object The one true Vimeography_Pro Instance
 */
function Vimeography_Pro() {
  if ( ! class_exists( 'Vimeography', false ) ) {
    return;
  }

  return Vimeography_Pro::instance();
}

// Get Vimeography Pro Running
add_action( 'plugins_loaded', 'Vimeography_Pro' );


/**
 * Helper function to handle DB creation depending on single or multisite installation
 *
 * @param  [type] $network_wide [description]
 * @return [type]               [description]
 */
function vimeography_pro_install($network_wide) {
  require_once VIMEOGRAPHY_PRO_PATH . 'lib/database.php';
  $db = new Vimeography_Pro_Database;

  if ( is_multisite() && $network_wide ) { // See if being activated on the entire network or one blog
    global $wpdb;

    // Get this so we can switch back to it later
    $original_blog_id = get_current_blog_id();

    // Get all blogs in the network and activate plugin on each one
    $blogs = $wpdb->get_results("
        SELECT blog_id
        FROM {$wpdb->blogs}
        WHERE site_id = '{$wpdb->siteid}'
        AND spam = '0'
        AND deleted = '0'
        AND archived = '0'
    ");

    foreach ( $blogs as $blog ) {
      switch_to_blog( $blog->blog_id );
      $db::vimeography_pro_update_tables();

      update_site_option('vimeography_pro_db_version', VIMEOGRAPHY_PRO_VERSION);
    }

    // Switch back to the current blog
    // @link http://wordpress.stackexchange.com/a/89114
    switch_to_blog( $original_blog_id );
    $GLOBALS['_wp_switched_stack'] = array();
    $GLOBALS['switched']           = FALSE;

  } else {
    // Running on a single blog
    $db::vimeography_pro_update_tables();
    update_site_option('vimeography_pro_db_version', VIMEOGRAPHY_PRO_VERSION);
  }
}


register_activation_hook( __FILE__, 'vimeography_pro_install' );
