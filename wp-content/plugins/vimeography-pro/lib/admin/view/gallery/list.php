<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_Gallery_List extends Vimeography_Gallery_List {

  /**
   * [__construct description]
   */
  public function __construct() {
    add_action('vimeography-pro/duplicate-gallery', array($this, 'duplicate_pro_gallery'), 10, 2);
    add_action('vimeography-pro/delete-gallery', array($this, 'delete_pro_gallery'));
    parent::__construct();

    add_action('toplevel_page_vimeography-edit-galleries', array($this, 'render_tools') );
    add_action('vimeography_action_export_galleries', array($this, 'export_galleries') );
    add_action('vimeography_action_import_galleries', array($this, 'import_galleries') );
  }


  /**
   * Output Vimeography Pro tools.
   *
   * @param  [type] $a [description]
   * @param  [type] $r [description]
   * @return [type]    [description]
   */
  public function render_tools() {
  ?>
    <style>
      #vimeography-pro-tools {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        box-sizing: border-box;
        padding: 1em;
      }

      #vimeography-pro-tools h4 {
        margin-bottom: 2em;
      }
    </style>
    <div id="vimeography-pro-tools">
      <h4>Extra Tools</h4>
      <?php $this->render_export_button(); ?>
      <?php $this->render_import_form(); ?>
    </div>
  <?php
  }


  /**
   * Outputs an export button if there are galleries that exist.
   *
   * @return string
   */
  public function render_export_button() {
    global $wpdb;

    $result = $wpdb->get_results("
      SELECT
        gallery.id,
        gallery.title,
        gallery.date_created,
        meta.gallery_id,
        meta.source_url,
        meta.resource_uri,
        meta.featured_video,
        meta.video_limit,
        meta.gallery_width,
        meta.cache_timeout,
        meta.theme_name,
        pro.per_page,
        pro.sort,
        pro.playlist,
        pro.allow_downloads,
        pro.direction,
        pro.gallery_id
      FROM $wpdb->vimeography_gallery gallery
      JOIN $wpdb->vimeography_gallery_meta meta
      ON gallery.id = meta.gallery_id
      JOIN $wpdb->vimeography_pro_meta pro
      ON gallery.id = pro.gallery_id
      ORDER BY gallery.id;
    ");

    if ( empty( $result ) ) {
      return;
    }

    $url = add_query_arg( 'vimeography-action', 'export_galleries', menu_page_url('vimeography-edit-galleries', false) );

    ?>
      <style>
      .vimeography-tools-export {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        flex-wrap: wrap;
        margin: 0 0 4em;
      }

      .vimeography-tools-export h5 {
        margin: 0;
        padding: 0;
        width: 200px;
      }

      .vimeography-tools-export label {
        margin-bottom: 1.5em;
      }

      </style>
      <div class="vimeography-tools-export">
        <h5>Export Galleries</h5>
        <div>
          <label for="vimeography_preserve_gallery_ids">
            <input type="checkbox" name="vimeography_preserve_gallery_ids" id="vimeography_preserve_gallery_ids" value="1" style="margin-right: 2px;">
            <span style="transform: translateY(2px); display: inline-block;">Preserve Gallery IDs</span>
          </label>
          <a id="vimeography_export_galleries" class="button" href="<?php esc_attr_e( $url ); ?>">Click to Export Galleries</a>
        </div>
      </div>
      <script>
        jQuery(function($) {
          $('#vimeography_preserve_gallery_ids').change(function(){
            var vimeography_export = document.querySelector('#vimeography_export_galleries');

            if (this.checked) {
              vimeography_export.href += '&preserve_gallery_ids=1';
            } else {
              vimeography_export.href = vimeography_export.href.replace('&preserve_gallery_ids=1','');
            }
          });
        });
      </script>
    <?php
  }


  /**
   * Outputs an import form for importing galleries to a site.
   *
   * @return string
   */
  public function render_import_form() {
    $url = add_query_arg( 'vimeography-action', 'import_galleries', menu_page_url('vimeography-edit-galleries', false) );

    ?>
      <style>
      .vimeography-tools-import {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        flex-wrap: wrap;
        margin: 0 0 2em;
      }

      .vimeography-tools-import h5 {
        width: 200px;
      }

      </style>
      <div class="vimeography-tools-import">
        <h5>Import Galleries</h5>
        <form method="post" action="<?php esc_attr_e( $url ); ?>" enctype="multipart/form-data">
          <input type="file" id="vimeography-import" name="vimeography_gallery_manifest" onchange="this.form.submit()">
        </form>
      </div>
    <?php
  }


  /**
   * Exports all of the current site's Vimeography galleries
   * to a portable JSON file.
   *
   * @return void
   */
  public function export_galleries() {
    global $wpdb;

    $result = $wpdb->get_results("
      SELECT
        gallery.id,
        gallery.title,
        gallery.date_created,
        meta.gallery_id,
        meta.source_url,
        meta.resource_uri,
        meta.featured_video,
        meta.video_limit,
        meta.gallery_width,
        meta.cache_timeout,
        meta.theme_name,
        pro.per_page,
        pro.sort,
        pro.playlist,
        pro.allow_downloads,
        pro.direction,
        pro.gallery_id
      FROM $wpdb->vimeography_gallery gallery
      JOIN $wpdb->vimeography_gallery_meta meta
      ON gallery.id = meta.gallery_id
      JOIN $wpdb->vimeography_pro_meta pro
      ON gallery.id = pro.gallery_id
      ORDER BY gallery.id;
    ");

    if ( empty( $result ) ) {
      return;
    }

    if ( empty( $_GET['preserve_gallery_ids'] ) ) {
      foreach($result as $index => $gallery) {
        unset( $gallery->id );
        unset( $gallery->gallery_id );
        $result[$index] = $gallery;
      }
    }

    $json = json_encode($result);
    $name = sanitize_title( get_bloginfo('name') );
    $date = current_time( 'm.d.Y.His' );
    $filename = sprintf('%1$s-vimeography-galleries.%2$s.json', $name, $date);

    header('Content-disposition: attachment; filename=' . $filename);
    header('Content-type: application/json');
    echo $json;
    exit;
  }


  /**
   * Parse the imported JSON file into the Vimeography Database.
   *
   * @return [type] [description]
   */
  public function import_galleries($request) {
    if ( ! current_user_can('manage_options') ) {
      $this->messages[] = array(
        'type' => 'error',
        'heading' => __('Gallery Import Failed.', 'vimeography'),
        'message' => __('Only site administrators can perform this action.', 'vimeography')
      );

      return;
    }

    if ( $_FILES['vimeography_gallery_manifest']['error'] === UPLOAD_ERR_OK
          && is_uploaded_file($_FILES['vimeography_gallery_manifest']['tmp_name'] ) ) {

      $manifest = file_get_contents( $_FILES['vimeography_gallery_manifest']['tmp_name'] );
      $galleries = json_decode( $manifest );

      if ( empty( $galleries ) || $galleries === false || $galleries === null ) {
        return;
      }

      $count = 0;

      global $wpdb;

      foreach ( $galleries as $gallery ) {
        $wpdb->query('START TRANSACTION');

        $args = array(
          'title'        => $gallery->title,
          'date_created' => $gallery->date_created,
          'is_active'    => 1
        );

        if ( ! empty( $gallery->id ) ) {
          $args['id'] = $gallery->id;
        }

        $result = $wpdb->insert( $wpdb->vimeography_gallery, $args );

        if (!$result) {
          $wpdb->query('ROLLBACK');
          $this->messages[] = array(
            'type' => 'error',
            'heading' => __('Gallery Import Failed.', 'vimeography'),
            'message' => sprintf( __('The "%1$s" gallery could not be imported.', 'vimeography'), $gallery->title )
          );
          continue;
        }

        $gallery_id = $wpdb->insert_id;

        $result = $wpdb->insert( $wpdb->vimeography_gallery_meta, array(
                                'gallery_id'     => $gallery_id,
                                'source_url'     => $gallery->source_url,
                                'resource_uri'   => $gallery->resource_uri,
                                'featured_video' => $gallery->featured_video,
                                'gallery_width'  => $gallery->gallery_width,
                                'video_limit'    => $gallery->video_limit,
                                'cache_timeout'  => $gallery->cache_timeout,
                                'theme_name'     => $gallery->theme_name ) );

        if (!$result) {
          $wpdb->query('ROLLBACK');
          $this->messages[] = array(
            'type' => 'error',
            'heading' => __('Gallery Import Failed.', 'vimeography'),
            'message' => sprintf( __('Gallery #%1$d meta could not be imported.', 'vimeography'), $gallery_id )
          );
          continue;
        }

        $result = $wpdb->insert(
          $wpdb->vimeography_pro_meta,
          array(
            'gallery_id' => $gallery_id,
            'per_page'   => $gallery->per_page,
            'sort'       => $gallery->sort,
            'direction'  => $gallery->direction,
            'playlist'   => $gallery->playlist,
            'allow_downloads' => $gallery->allow_downloads
          ),
          array('%d', '%d', '%s', '%s', '%d', '%d')
        );

        if (!$result) {
          $wpdb->query('ROLLBACK');
          $this->messages[] = array(
            'type' => 'error',
            'heading' => __('Gallery Import Failed.', 'vimeography'),
            'message' => sprintf( __('Gallery #%1$d pro meta could not be imported.', 'vimeography'), $gallery_id )
          );
          continue;
        }

        $wpdb->query('COMMIT');
        $count++;
      }

      do_action('vimeography/reload-galleries');

      if ( $count > 0 ) {
        $this->messages[] = array(
          'type' => 'updated',
          'heading' => sprintf( __('%1$d Galleries Imported.', 'vimeography'), $count ),
          'message' => __('Now that was easy, wasn\'t it?', 'vimeography')
        );
      }
    }
  }


  /**
   * Creates a copy of the given gallery id settings.
   *
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  public function duplicate_pro_gallery($old_id, $new_id) {
    try {
      global $wpdb;

      $duplicate = $wpdb->get_results('
        SELECT *
        FROM ' . $wpdb->vimeography_pro_meta . ' AS pro
        WHERE pro.gallery_id = ' . $old_id . '
        LIMIT 1;'
      );

      $result = $wpdb->insert(
        $wpdb->vimeography_pro_meta,
        array(
          'gallery_id' => $new_id,
          'per_page'   => $duplicate[0]->per_page,
          'sort'       => $duplicate[0]->sort,
          'direction'  => $duplicate[0]->direction,
          'playlist'   => $duplicate[0]->playlist,
          'allow_downloads'   => $duplicate[0]->allow_downloads
        )
      );

      if ( $result === FALSE ) {
        throw new Exception( __('Your gallery could not be duplicated.', 'vimeography-pro') );
      }
    } catch (Exception $e) {
      $this->messages[] = array('type' => 'error', 'heading' => __('Ruh Roh.'), 'message' => $e->getMessage());
    }
  }

  /**
   * Deletes the gallery of the given id in the database.
   *
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  public function delete_pro_gallery($id) {
    try {
      global $wpdb;
      $result = $wpdb->query('
        DELETE pro
        FROM ' . $wpdb->vimeography_pro_meta . ' pro
        WHERE pro.gallery_id = ' . $id . ';'
      );

      if ( $result === FALSE ) {
        throw new Exception( __('Your gallery could not be deleted.', 'vimeography-pro') );
      }

    } catch (Exception $e) {
      $this->messages[] = array('type' => 'error', 'heading' => __('Ruh Roh.'), 'message' => $e->getMessage());
    }
  }

}