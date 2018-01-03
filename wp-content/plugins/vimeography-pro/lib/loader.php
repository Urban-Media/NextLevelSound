<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Loader {

  public function __construct() {
    add_action('vimeography/load_pro', array($this, 'vimeography_load_pro_core'));
    add_action('vimeography/load_pro', array($this, 'vimeography_load_pro_renderer'));

    add_action('vimeography-pro/load-new', array($this, 'vimeography_pro_load_new'));
    add_action('vimeography-pro/load-editor', array($this, 'vimeography_pro_load_editor'));
    add_action('vimeography-pro/load-list', array($this, 'vimeography_pro_load_list'));
    add_filter('vimeography-pro/load-edit-partials', array($this, 'vimeography_pro_load_edit_partials'));
  }


  /**
   * [vimeography_load_pro_core description]
   * @return [type] [description]
   */
  public function vimeography_load_pro_core() {
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/core/pro.php';
  }

  /**
   * [vimeography_load_pro_renderer description]
   * @return [type] [description]
   */
  public function vimeography_load_pro_renderer() {
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/renderer.php';
  }

  /**
   * [vimeography_load_pro_renderer description]
   * @return [type] [description]
   */
  public function vimeography_pro_load_new() {
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/admin/view/gallery/new.php';
  }

  /**
   * [vimeography_load_pro_renderer description]
   * @return [type] [description]
   */
  public function vimeography_pro_load_editor() {
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/admin/view/gallery/edit.php';
  }

  /**
   * [vimeography_load_pro_list description]
   * @return [type] [description]
   */
  public function vimeography_pro_load_list() {
    require_once VIMEOGRAPHY_PRO_PATH . 'lib/admin/view/gallery/list.php';
  }

  /**
   * [vimeography_pro_load_edit_partials description]
   * @param  [type] $partialsLoader [description]
   * @return [type]                 [description]
   */
  public function vimeography_pro_load_edit_partials($partialsLoader) {
    return $partialsLoader->addLoader(new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PRO_PATH . 'lib/admin/templates/gallery/edit/partials'));
  }

}