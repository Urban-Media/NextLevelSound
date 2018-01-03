<?php

namespace TheLion\OutoftheBox;

class Thumbnail {

    /**
     *
     * @var App
     */
    private $_app;

    /**
     *
     * @var \Kunnu\Dropbox\Dropbox 
     */
    private $_client;

    /**
     *
     * @var Processor 
     */
    private $_processor;

    /**
     *
     * @var Entry
     */
    private $_entry;

    /**
     *
     * @var int 
     */
    private $_width;

    /**
     *
     * @var int 
     */
    private $_height;

    /**
     *
     * @var bool 
     */
    private $_crop = true;

    /**
     *
     * @var int 
     */
    private $_quality = '75';

    /**
     *
     * @var string 
     */
    private $_format = 'jpeg';

    /**
     *
     * @var string 
     */
    private $_thumbnail_name;

    /**
     *
     * @var string 
     */
    private $_location_thumbnails;

    /**
     *
     * @var string 
     */
    private $_location_thumbnails_url;

    /**
     *
     * @var string 
     */
    private $_image_data;

    /**
     *
     * @var bool 
     */
    private $_loading_thumb = false;

    public function __construct(Processor $_processor, Entry $entry, $width, $height, $crop = true, $quality = 75, $format = 'jpeg', $imagedata = null, $loading_thumb = false) {
        $this->_app = $_processor->get_app();
        $this->_client = $this->_app->get_client();
        $this->_processor = $_processor;
        $this->_entry = $entry;
        $this->_width = $width;
        $this->_height = $height;
        $this->_crop = $crop;
        $this->_quality = $quality;
        $this->_format = $format;
        $this->_location_thumbnails = OUTOFTHEBOX_CACHEDIR . 'thumbnails/';
        $this->_location_thumbnails_url = OUTOFTHEBOX_CACHEURL . 'thumbnails/';
        $this->_image_data = $imagedata;

        if ($this->_get_entry()->get_media('width') == null || $this->_get_entry()->get_media('height') == null) {
            $this->_crop = true;
        }

        if ($this->_crop) {
            $this->_width = max(array($this->_width, $this->_height));
            $this->_height = max(array($this->_width, $this->_height));
        }

        $this->_thumbnail_name = $this->_get_entry()->get_id() . '_' . $this->_width . '_' . $this->_height . '_c' . (($this->_crop) ? '1' : '0') . '_q' . $this->_quality . '.' . $this->_format;
    }

    public function get_url() {
        if ($this->does_thumbnail_exist()) {
            return $this->_get_location_thumbnail_url();
        }

        return $this->_build_thumbnail_url();
    }

    public function get_thumbnail_name() {
        return str_replace(':', '', $this->_thumbnail_name);
    }

    public function does_thumbnail_exist() {
        if (!file_exists($this->_get_location_thumbnail())) {
            return false;
        }

        if (filemtime($this->_get_location_thumbnail()) !== $this->_get_entry()->get_last_edited()) {
            return false;
        }

        if (filesize($this->_get_location_thumbnail()) < 1) {
            return false;
        }

        return $this->_get_location_thumbnail();
    }

    private function _build_thumbnail_url() {
        return admin_url('admin-ajax.php') . '?action=outofthebox-thumbnail&src=' . $this->_thumbnail_name;
    }

    public function build_thumbnail() {

        @set_time_limit(60); //Creating thumbnail can take a while

        /* First get the Image itself */
        if (empty($this->_image_data)) {
            try {
                $this->_image_data = $this->_get_client()->download($this->_get_entry()->get_path())->getContents();
            } catch (\Exception $ex) {
                /* TO DO LOG */
                die(__('Cannot get image'));
            }
        }

        /* Fall back in case the server can't handle the image itself */
        if (empty($this->_image_data)) {
            try {
                $this->_image_data = $this->_get_client()->getThumbnail($this->_get_entry()->get_path(), 'large')->getContents();
            } catch (\Exception $ex) {
                /* TO DO LOG */
                die(__('Cannot get thumbnail image'));
            }
        }

        return $this->_create_thumbnail();
    }

    private function _create_thumbnail() {
        /* Create the requested thumbnail */
        try {
            $php_thumb = $this->_load_phpthumb_object();
            $php_thumb->GenerateThumbnail();
            $php_thumb->CalculateThumbnailDimensions();
            $php_thumb->SetCacheFilename();
            $is_thumbnail_created = $php_thumb->RenderToFile($this->_get_location_thumbnail());
            unset($php_thumb);

            /* Set the modification date of the thumbnail to that of the entry
             * so we can check if a new thumbnail should be loaded */
            touch($this->_get_location_thumbnail(), $this->_get_entry()->get_last_edited());

            /* Create small thumbnail for showing until the height/size is known */
            /* if (!$this->_is_loading_thumb()) {
              $small_image = new Thumbnail($this->_processor, $this->_client, $this->_entry, null, null, true, $this->_image_data, true);
              return $small_image->get_url();
              } */

            return $is_thumbnail_created;
        } catch (\Exception $ex) {
            /* TO DO LOG */
            die(__('Cannot generate thumbnail image'));
        }
    }

    /**
     * 
     * @return phpThumb
     */
    private function _load_phpthumb_object() {
        if (!class_exists('phpthumb')) {
            try {
                require_once('phpThumb/phpthumb.class.php');
            } catch (\Exception $ex) {
                /* TO DO LOG */
                die("Can't load PHPTHUMB Library");
            }
        }

        $this->_create_thumbnail_dir();

        $php_thumb = new \phpthumb();
        $php_thumb->resetObject();
        $php_thumb->setParameter('config_temp_directory', $this->_get_location_thumbnails());
        $php_thumb->setParameter('config_cache_directory', $this->_get_location_thumbnails());
        $php_thumb->setParameter('config_output_format', $this->get_format());
        $php_thumb->setParameter('q', $this->get_quality());
        if ($this->get_width() !== 0) {
            $php_thumb->setParameter('w', $this->get_width());
        }
        if ($this->get_height() !== 0) {
            $php_thumb->setParameter('h', $this->get_height());
        }
        $php_thumb->setParameter('zc', $this->get_crop());
        $php_thumb->setParameter('f', $this->get_format());
        $php_thumb->setParameter('bg', 'FFFFFF|0');
        $php_thumb->setParameter('ar', true);
        $php_thumb->setParameter('aoe', false);

        $max_file_size = ($this->get_width() * $this->get_height()) / 5;
        //$php_thumb->setParameter('maxb', $max_file_size);

        $php_thumb->setSourceData($this->_get_image_data());

        return $php_thumb;
    }

    private function _create_thumbnail_dir() {

        if (!file_exists($this->_get_location_thumbnails())) {
            @mkdir($this->_get_location_thumbnails(), 0755);
        } else {
            return true;
        }

        if (!is_writable($this->_get_location_thumbnails())) {
            @chmod($this->_get_location_thumbnails(), 0755);
        } else {
            return true;
        }

        return is_writable($this->_get_location_thumbnails());
    }

    /**
     * 
     * @return \Kunnu\Dropbox\Dropbox
     */
    private function _get_client() {
        return $this->_client;
    }

    /**
     * 
     * @return Cache\Node
     */
    private function _get_entry() {
        return $this->_entry;
    }

    public function get_width() {
        return $this->_width;
    }

    public function get_height() {
        return $this->_height;
    }

    public function get_crop() {
        return $this->_crop;
    }

    public function get_quality() {
        return $this->_quality;
    }

    public function get_format() {
        return $this->_format;
    }

    public function set_width($_width) {
        $this->_width = (int) $_width;
    }

    public function set_height($_height) {
        $this->_height = (int) $_height;
    }

    public function set_crop($_crop = false) {
        $this->_crop = (bool) $_crop;
    }

    public function set_quality($_quality) {
        $this->_quality = (int) $_quality;
    }

    public function set_format($_format) {
        $this->_format = $_format;
    }

    private function _get_location_thumbnail() {
        return $this->_location_thumbnails . $this->get_thumbnail_name();
    }

    private function _get_location_thumbnail_url() {
        return $this->_location_thumbnails_url . $this->get_thumbnail_name();
    }

    private function _get_location_thumbnails() {
        return $this->_location_thumbnails;
    }

    private function _get_location_thumbnails_url() {
        return $this->_location_thumbnails_url;
    }

    private function _get_image_data() {
        return $this->_image_data;
    }

    private function _is_loading_thumb() {
        return $this->_loading_thumb;
    }

}
