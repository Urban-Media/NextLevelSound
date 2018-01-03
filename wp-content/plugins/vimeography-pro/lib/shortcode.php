<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Shortcode {

  public function __construct() {
    // This filter is only supported by 3.6, so disable for now.
    // add_filter('shortcode_atts_vimeography', array($this, 'vimeography_pro_shortcode_atts'), 10, 3 );
    add_filter('vimeography-pro/do-shortcode', array($this, 'vimeography_pro_shortcode_atts'), 10, 3);
  }


  /**
   * [vimeography_pro_shortcode_atts description]
   * @param  [type] $result   The shortcode_atts() merging of $pairs and $atts
   * @param  [type] $pairs    The default attributes defined for the shortcode
   * @param  [type] $atts     The attributes supplied by the user within the shortcode
   * @return array            The potentially modified $result array
   */
  public function vimeography_pro_shortcode_atts($result, $pairs, $atts) {
    if ( isset( $atts['id'] ) AND intval( $atts['id'] ) != 0) {

      $pro_settings = self::_get_pro_settings( intval( $atts['id'] ) );

      $result['per_page']  = isset($atts['per_page'])  ? intval($atts['per_page']) : intval($pro_settings[0]->per_page);
      $result['sort']      = isset($atts['sort'])      ? $atts['sort']             : $pro_settings[0]->sort;
      $result['direction'] = isset($atts['direction']) ? $atts['direction']        : $pro_settings[0]->direction;
      $result['playlist']  = isset($atts['playlist'])  ? intval($atts['playlist']) : intval($pro_settings[0]->playlist);
      $result['allow_downloads'] = isset( $atts['allow_downloads'] ) ? intval( $atts['allow_downloads'] ) : intval( $pro_settings[0]->allow_downloads );
    }

    return $result;
  }

  /**
   * Retrieve the pro gallery settings for the given gallery ID,
   * or create defaults if they do not exist
   *
   * @since  1.0
   * @param  int $gallery_id The gallery id
   * @return mixed array on success, exception on error
   */
  private function _get_pro_settings($gallery_id) {
    global $wpdb;

    $pro_settings = $wpdb->get_results(
      '
      SELECT *
      FROM ' . $wpdb->vimeography_pro_meta . ' AS pro
      WHERE pro.gallery_id = ' . $gallery_id . '
      LIMIT 1;
      '
    );

    if ( empty( $pro_settings ) ) {
      $pro_settings = Vimeography_Pro()->database->add_default_settings($gallery_id);
    }

    if ( $pro_settings === FALSE ) {
      throw new Vimeography_Exception( __('The Vimeography gallery you are trying to load does not have pro settings.', 'vimeography-pro') );
    }

    return $pro_settings;
  }

}