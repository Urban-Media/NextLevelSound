<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Rewrite {

  /**
   * [$_gallery_settings description]
   * @var array
   */
  private $_gallery_settings = array();


  public function __construct() {
    add_action('generate_rewrite_rules', array( $this, 'add_pagination_rewrite_rules' ) );
    add_filter('query_vars', array( $this, 'add_pagination_query_vars' ) );
    add_action('init', array( $this, 'flush_rewrite_rules' ) );

    // Check if the query vars exist in the request
    add_action('pre_get_posts', array( $this, 'check_query_vars' ) );
    add_filter('vimeography-pro/do-shortcode', array( $this, 'merge_gallery_settings' ), 99, 3 );
  }


  /**
   * Adds the rewrite rules for Vimeography's pagination convention. ([0-9]{4})
   * @param  [type] $wp_rewrite [description]
   * @return [type]             [description]
   */
  public function add_pagination_rewrite_rules($wp_rewrite) {
    $wp_rewrite->rules = array(
      '(.+?)/vimeography/(\d+)' => $wp_rewrite->index . '?pagename='. $wp_rewrite->preg_index( 1 ) .'&vimeography_id=' . $wp_rewrite->preg_index( 2 ),
      '(.+?)/vimeography/page/(\d+)' => $wp_rewrite->index . '?pagename='. $wp_rewrite->preg_index( 1 ) .'&vimeography_page=' . $wp_rewrite->preg_index( 2 ),
    ) + $wp_rewrite->rules;
  }


  /**
   * Adds the pagination query vars so they may be read in WordPress.
   * @param  [type] $qvars [description]
   * @return [type]        [description]
   */
  public function add_pagination_query_vars( $qvars ) {
    $qvars[] = 'vimeography_page';
    $qvars[] = 'vimeography_id';
    return $qvars;
  }


  /**
   * Flush rewrite rules on activation if our rules are not yet included/registered.
   * @return [type] [description]
   */
  public static function flush_rewrite_rules() {
    $rules = get_option( 'rewrite_rules' );

    if ( ! isset( $rules['(.+?)/vimeography/(\d+)'] ) ) {
      global $wp_rewrite;
      $wp_rewrite->flush_rules();
    }
  }


  /**
   * Detects if the Vimeography queryvars exist in the request
   * and if they do, ignore the standard cache and paginate off of those vars]
   *
   * @param  [type] $query [description]
   * @return [type]        [description]
   */
  public function check_query_vars($query) {
    // If this query is in the admin or isn't the main query, forget it.
    if ( is_admin() || ! $query->is_main_query() ) {
      return;
    }

    // If this is set, we should always ignore local cache
    if ( isset( $query->query_vars['vimeography_id'] ) ) {
      $this->_gallery_settings['containing_uri'] = '/videos/' . intval( $query->query_vars['vimeography_id'] );
      $this->_gallery_settings['cache'] = 0;
    }

    if ( isset( $query->query_vars['vimeography_page'] ) ) {
      $this->_gallery_settings['page'] = $query->query_vars['vimeography_page'];
      $this->_gallery_settings['cache'] = 0;
    }

    return $query;
  }


  /**
   * [vimeography_pro_shortcode_atts description]
   * @param  [type] $result   The shortcode_atts() merging of $pairs and $atts
   * @param  [type] $pairs    The default attributes defined for the shortcode
   * @param  [type] $atts     The attributes supplied by the user within the shortcode
   * @return array            The potentially modified $result array
   */
  public function merge_gallery_settings($result, $pairs, $atts) {
    return array_merge( $result, $this->_gallery_settings );
  }

  // public function merge_gallery_settings($gallery_settings) {
  //   return array_merge( $gallery_settings, $this->_gallery_settings );
  // }
}