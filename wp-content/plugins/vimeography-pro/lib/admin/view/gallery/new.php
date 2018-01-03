<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Gallery_New extends Vimeography_Gallery_New {
  /**
   * [__construct description]
   */
  public function __construct() {
    add_action('vimeography-pro/create-gallery', array( $this, 'create_vimeography_pro_gallery' ) );
    parent::__construct();
  }

  /**
   * [_create_vimeography_pro_gallery description]
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  public static function create_vimeography_pro_gallery($id) {
    global $wpdb;

    $row = $wpdb->insert(
      $wpdb->vimeography_pro_meta,
      array(
        'gallery_id' => $id,
        'per_page'   => 20,
        'sort'       => 'default',
        'direction'  => 'desc',
        'playlist'   => 0,
        'allow_downloads' => 0
      ),
      array('%d', '%d', '%s', '%s', '%d', '%d')
    );

    if ( ! $row ) {
      throw new Vimegraphy_Exception( __('Your Vimeography Pro gallery settings could not be saved.') );
    }

    return TRUE;
  }

}