<?php

class Vimeography_Pro_Ajax extends Vimeography_Pro
{
  /**
   * [__construct description]
   */
  public function __construct() {
    // fixes "trying to get property of non-object in wp-includes/functions.php on line 4037" error
    // on AJAX page request
    remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );
    add_action( 'wp_ajax_vimeography_pro_ajax_paginate', array( $this, 'vimeography_pro_ajax_paginate' ) );
    add_action( 'wp_ajax_nopriv_vimeography_pro_ajax_paginate', array( $this, 'vimeography_pro_ajax_paginate' ) );
  }

  /**
   * Ajax method to return the pagination elements
   *
   * The required vars are:
   * - $_POST['gallery_id] = the gallery id
   * - $_POST['get_page]   = which page to fetch
   * - $_POST['per_page]   = how many items each page has
   *
   * @todo We need to standarize gallery ID should be the auto incrementative ID from the dB, token is the md5hash
   * @todo the settings is hardcoded for now, but should fetch the data from the DB
   */
  public function vimeography_pro_ajax_paginate() {
    require_once VIMEOGRAPHY_PATH . 'lib/core.php';
    require_once VIMEOGRAPHY_PATH . 'lib/renderer.php';
    do_action('vimeography/load_pro');

    check_ajax_referer( 'vimeography_pro_ajax_pagination_nonce' );

    $gallery_id = $_POST['gallery_id'];

    $settings    = array(
      'partial'  => 'videos',
      'theme'    => $_POST['theme'],
      'source'   => $_POST['endpoint'],
      'page'     => $_POST['page'],
      'per_page' => $_POST['per_page'],
      'remote_per_page' => $_POST['per_page'],
      'limit'    => $_POST['limit'],
      'sort'     => $_POST['sort'],
      'direction'=> $_POST['direction']
    );

    $vimeography = new Vimeography_Core_Pro($settings);
    $renderer    = new Vimeography_Pro_Renderer($settings, $gallery_id);
    $renderer->load_theme();

    $response    = $vimeography->get_video_page($gallery_id, $page = $settings['page'], $per_page = $settings['remote_per_page'], $limit = $settings['limit']);
    $html        = $renderer->render($response);

    $json = array(
        'result'   => 'success',
        'page'     => (int) $settings['page'],
        'per_page' => (int) $settings['remote_per_page'],
        'paging'   => $response->paging,
        'html'     => $html,
    );
    echo json_encode($json);
    die;
  }

  /**
   * [vimeography_ajax_get_cached_videos description]
   * @return [type] [description]
   */
  public function vimeography_ajax_get_cached_videos() {
    // This will automatically die; if it fails
    check_ajax_referer('vimeography-get-cached-videos');

    $gallery_id = intval( $_POST['gallery_id'] );

    $data = $this->get_vimeography_cache($gallery_id);
    $videos = json_decode( $data[0] );

    if ( isset( $data[1] ) ) {
      // featured video option is set
      $featured = json_decode( $data[1] );
      array_unshift( $videos, $featured[0] );
    }

    echo json_encode($videos);
    die;
  }
}