<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Gallery_Edit extends Vimeography_Gallery_Edit {
  /**
   * [__construct description]
   */
  public function __construct() {
    parent::__construct();

    add_filter('vimeography/gallery-settings', array( $this, 'vimeography_pro_add_gallery_settings') );

    if ( isset( $_POST['vimeography_pro_settings'] ) ) {
      self::validate_vimeography_pro_settings($this->_gallery_id, $_POST['vimeography_pro_settings']);
    }
  }

  /**
   * [pro_nonce description]
   * @return [type] [description]
   */
  public static function pro_nonce() {
    return wp_nonce_field('vimeography-pro-action','vimeography-pro-verification');
  }

  /**
   * [vimeography_pro_add_gallery_settings description]
   * @param  [type] $gallery [description]
   * @return [type]          [description]
   */
  public function vimeography_pro_add_gallery_settings($gallery) {
    global $wpdb;

    $results = $wpdb->get_results('
      SELECT per_page, sort, direction, playlist, allow_downloads FROM ' . $wpdb->vimeography_pro_meta . ' pro
      WHERE pro.gallery_id = ' . $this->_gallery_id . '
      LIMIT 1;
    ');

    if ( ! $results ) {
      $results = Vimeography_Pro()->database->add_default_settings($this->_gallery_id);
    }

    // Still no love?
    if ( ! $results ) {
      $this->messages[] = array('type' => 'error', 'heading' => 'Uh oh.', 'message' => __('Your Vimeography Pro settings are missing for this gallery.', 'vimeography-pro') );
    } else {
      // merge pro settings with current gallery settings
      $gallery[0]->per_page  = $results[0]->per_page;
      $gallery[0]->sort      = $results[0]->sort;
      $gallery[0]->direction = $results[0]->direction === 'asc' ? TRUE : FALSE;
      $gallery[0]->playlist  = intval($results[0]->playlist) == 1 ? TRUE : FALSE;
      $gallery[0]->allow_downloads = intval($results[0]->allow_downloads) == 1 ? TRUE : FALSE;
    }

    return $gallery;
  }

  /**
   * [selected_sort description]
   * @return [type] [description]
   */
  public function selected_sort() {
    return array(
      $this->_gallery[0]->sort => TRUE,
    );
  }

  /**
   * [validate_vimeography_pro_settings description]
   * @param  [type] $id    [description]
   * @param  [type] $input [description]
   * @return [type]        [description]
   */
  private function validate_vimeography_pro_settings($id, $input) {
    if ( check_admin_referer('vimeography-pro-action','vimeography-pro-verification') ) {
      try {
        global $wpdb;

        $input['videos_per_page'] = intval( $input['videos_per_page'] ) <= 50 ? intval( $input['videos_per_page'] ) : 50;
        $input['enable_playlist'] = isset( $input['enable_playlist'] ) ? 1 : 0;
        $input['allow_downloads'] = isset( $input['allow_downloads'] ) ? 1 : 0;
        $input['direction']       = isset( $input['reverse_order'] ) ? 'asc' : 'desc';

        $result = $wpdb->update(
          $wpdb->vimeography_pro_meta,
          array(
            'per_page'  => $input['videos_per_page'],
            'sort'      => $input['sort'],
            'direction' => $input['direction'],
            'playlist'  => $input['enable_playlist'],
            'allow_downloads' => $input['allow_downloads']
          ),
          array( 'gallery_id' => $id ),
          array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%d'
          ),
          array('%d')
        );

        if ( $result === FALSE ) {
          throw new Exception('Your Vimeography Pro settings could not be updated.');
          //$wpdb->print_error();
        }

        if ( $this->_cache->exists() ) {
          $this->_cache->delete();
        }

        $this->messages[] = array('type' => 'success', 'heading' => __('Settings updated.'), 'message' => __('Nice work. You are pretty good at this.'));
      } catch (Exception $e) {
        $this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
      }
    }
  }

}
