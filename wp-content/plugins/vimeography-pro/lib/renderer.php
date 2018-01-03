<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Renderer extends Vimeography_Renderer {

  public function __construct($settings, $gallery_id) {
    parent::__construct($settings, $gallery_id);
  }

  /**
   * Renders the data inside of the theme's Mustache template.
   * We did it!
   *
   * @param  array $data [description]
   * @return string       [description]
   */
  public function render($result) {

    foreach ($this->_settings as $member => $value) {
      $this->_view->{$member} = $value;
    }

    if ( isset( $this->_settings['playlist'] ) ) {
      if ( $this->_settings['playlist'] == 1 ) {
        $this->_view->playlist = sprintf(
          'vimeography.utilities.enable_playlist(%d);',
          $this->_gallery_id
        );
      } else {
        unset( $this->_view->playlist );
      }
    }

    if ( isset( $this->_settings['allow_downloads'] ) ) {
      if ( $this->_settings['allow_downloads'] == 0 ) {
        unset( $this->_view->allow_downloads );
      }
    }

    if ( isset( $this->_settings['width'] ) ) {
      $this->_view->gallery_width = $this->_settings['width'];
    }

    $this->_view->pagination_nonce = wp_create_nonce('vimeography_pro_ajax_pagination_nonce');
    $this->_view->ajax_url = admin_url( 'admin-ajax.php' );

    // Set remaining view variables and render away
    $this->_view->data     = array_slice($result->video_set, 0, $this->_view->per_page);

    if (! empty( $this->_view->data[0] ) ) {
      $this->_view->featured = clone $this->_view->data[0];
    }

    if (isset($this->_view->limit) AND $this->_view->limit != 0 AND $this->_view->limit < $result->total) {
      $this->_view->total = $this->_view->limit;
    } else {
      $this->_view->total  = $result->total;
    }

    $this->_view->page     = $result->page;
    $this->_view->paging   = $result->paging;

    // Set theme javascript variables
    $this->set_theme_vars();

    return $this->_template->render($this->_view);
  }


  /**
   * [set_theme_vars description]
   */
  public function set_theme_vars() {

    $theme_name = strtolower( $this->_active_theme['name'] );

    $data = array(
      'gallery_id' => $this->_view->gallery_id
    );

    if ( isset( $this->_view->paging ) ) {
      $data['pagination'] = array(
        'request' => array(
          '_ajax_nonce' => $this->_view->pagination_nonce,
          '_ajax_url'   => $this->_view->ajax_url,
          'theme'       => $theme_name,
          'gallery_id'  => $this->_view->gallery_id,
          'per_page'    => $this->_view->per_page,
          'limit'       => (int) $this->_view->limit,
          'sort'        => $this->_view->sort,
          'direction'   => $this->_view->direction
        ),
        'endpoint'      => $this->_view->source,
        'per_page'      => $this->_view->per_page,
        'current_page'  => $this->_view->page,
        'total'         => (int) $this->_view->total,
      );
    }

    if ( isset( $this->_view->playlist ) ) {
      $data['playlist'] = TRUE;
    }

    if ( isset( $this->_view->containing_uri ) ) {
      $data['containing_uri'] = $this->_view->containing_uri;
    }

    $reshuffled_data = array(
      'l10n_print_after' => sprintf('vimeography.galleries.%1$s["%2$s"] = %3$s',
        $theme_name,
        $this->_view->gallery_id,
        json_encode( $data )
      )
    );

    wp_localize_script("vimeography-{$theme_name}",
      "vimeography = window.vimeography || {};
      window.vimeography.galleries = window.vimeography.galleries || {};
      window.vimeography.galleries.{$theme_name} = window.vimeography.galleries.{$theme_name} || {};
      vimeography.unused",
    $reshuffled_data);
  }

}