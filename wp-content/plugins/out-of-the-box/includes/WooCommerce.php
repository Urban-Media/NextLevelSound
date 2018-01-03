<?php

namespace TheLion\OutoftheBox;

class WooCommerce extends \WC_Integration {

    /**
     *
     * @var \TheLion\OutoftheBox\Main 
     */
    private $_main;

    /**
     *
     * @var \TheLion\OutoftheBox\Processor 
     */
    private $_processor;
    private $_can_use_plugin = false;

    /**
     * Init and hook in the integration.
     */
    public function __construct() {
        global $OutoftheBox;
        $this->_main = $OutoftheBox;

        if (defined('DOING_AJAX')) {
            return false;
        }


        $this->id = 'outofthebox-woocommerce';
        $this->method_title = __('WooCommerce Dropbox', 'outofthebox');
        $this->method_description = __('Easily add downloadable products right from your Dropbox.', 'outofthebox') . ' '
                . sprintf(__('To be able to use this integration, you only need to link your Dropbox Account to the plugin on the %s.', 'outofthebox'), '<a href="' . admin_url('admin.php?page=OutoftheBox_settings#settings_advanced') . '">Out-of-the-Box settings page</a>');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        /* Actions */
        add_action('woocommerce_download_file_force', array(&$this, 'do_direct_download'), 1, 2);
        add_action('woocommerce_download_file_xsendfile', array(&$this, 'do_xsendfile_download'), 1, 2);
        add_action('woocommerce_download_file_redirect', array(&$this, 'do_redirect_download'), 1, 2);

        // Load custom scripts in the admin area
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
            add_action('edit_form_advanced', array(&$this, 'embed_out_of_the_box'), 1, 1);
        }
    }

    public function embed_out_of_the_box(\WP_Post $post) {
        if ($post->post_type !== 'product') {
            return;
        }
        ?>
        <div id='oftb-embedded' style='clear:both;display:none'>
          <?php
          $atts = array('mode' => 'files',
              'filelayout' => 'list',
              'filesize' => '0',
              'filedate' => '0',
              'addfolder' => '0',
              'showbreadcrumb' => '0',
              'showcolumnnames' => '0',
              'downloadrole' => 'none',
              'candownloadzip' => '0',
              'showsharelink' => '0',
              'mcepopup' => 'woocommerce');

          echo $this->get_processor()->create_from_shortcode($atts);
          ?>
        </div>
        <?php
    }

    /**
     * Load the Dropbox API and our own script
     */
    public function add_scripts() {

        $this->get_main()->load_styles();
        $this->get_main()->load_scripts();

        // register scripts/styles
        add_thickbox();
        wp_register_style('outofthebox-woocommerce', OUTOFTHEBOX_ROOTPATH . '/css/outofthebox-woocommerce.css');
        wp_register_script('outofthebox-woocommerce', OUTOFTHEBOX_ROOTPATH . '/includes/js/Woocommerce.js', array('jquery'), OUTOFTHEBOX_VERSION);

        // enqueue scripts/styles
        wp_enqueue_style('outofthebox-woocommerce');
        wp_enqueue_script('outofthebox-woocommerce');
        wp_enqueue_script('OutoftheBox');

        // register translations
        $translation_array = array(
            'choose_from_dropbox' => __('Choose from Dropbox', 'outofthebox'),
            'download_url' => admin_url('admin-ajax.php') . '?action=outofthebox-wc-direct-download&id=',
            'file_browser_url' => admin_url('admin-ajax.php') . '?action=outofthebox-getwoocommercepopup'
        );

        wp_localize_script('outofthebox-woocommerce', 'outofthebox_woocommerce_translation', $translation_array);
    }

    /**
     * 
     * @param string $file_path
     * @return \TheLion\OutoftheBox\CacheNode 
     */
    public function get_entry_for_download_by_url($file_path) {

        $download_url = parse_url($file_path);
        parse_str($download_url['query'], $download_url_query);
        $entry_id = $download_url_query['id'];
        
        $entry_path = urldecode(base64_decode($entry_id));

        if (empty($entry_path)){
            self::download_error(__('No valid file provided', 'woocommerce'));
        }
        
        $entry = $this->get_processor()->get_client()->get_entry($entry_path, false);

        if ($entry === false) {
            self::download_error(__('File not found', 'woocommerce'));
        }

        return $entry;
    }

    public function get_redirect_url_for_entry(Entry $entry) {

        $transient_url = self::get_download_url_transient($entry->get_id());
        if ($transient_url !== false) {
            return $transient_url;
        }

        $downloadlink = $this->get_processor()->get_client()->get_temporarily_link($entry);
        self::set_download_url_transient($entry->get_id(), $downloadlink);

        return $downloadlink;
    }

    public function do_direct_download($file_path, $filename) {

        if (strpos($file_path, 'outofthebox-wc-direct-download') === false) {
            return false; // Do nothing
        }

        $entry = $this->get_entry_for_download_by_url($file_path);
        $downloadlink = $this->get_redirect_url_for_entry($entry);
        $filename = $entry->get_name();
        /* Download the file */
        self::download_headers($downloadlink, $filename);

        if (!\WC_Download_Handler::readfile_chunked($downloadlink)) {
            $this->do_redirect_download($file_path, $filename);
        }

        exit;
    }

    public function do_xsendfile_download($file_path, $filename) {

        if (strpos($file_path, 'outofthebox-wc-direct-download') === false) {
            return false; // Do nothing
        }

        // Fallback
        $this->do_direct_download($file_path, $filename);
    }

    public function do_redirect_download($file_path, $filename) {

        if (strpos($file_path, 'outofthebox-wc-direct-download') === false) {
            return false; // Do nothing
        }

        $cached_entry = $this->get_entry_for_download_by_url($file_path);
        $downloadlink = $this->get_redirect_url_for_entry($cached_entry);

        /* Redirect to the file */
        header('Location: ' . $downloadlink);
        exit;
    }

    /**
     * Get content type of a download.
     * @param  string $file_path
     * @return string
     * @access private
     */
    private static function get_download_content_type($file_path) {
        $file_extension = strtolower(substr(strrchr($file_path, "."), 1));
        $ctype = "application/force-download";

        foreach (get_allowed_mime_types() as $mime => $type) {
            $mimes = explode('|', $mime);
            if (in_array($file_extension, $mimes)) {
                $ctype = $type;
                break;
            }
        }

        return $ctype;
    }

    /**
     * Set headers for the download.
     * @param  string $file_path
     * @param  string $filename
     * @access private
     */
    private static function download_headers($file_path, $filename) {
        self::check_server_config();
        self::clean_buffers();
        nocache_headers();

        header("X-Robots-Tag: noindex, nofollow", true);
        header("Content-Type: " . self::get_download_content_type($file_path));
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: binary");

        if ($size = @filesize($file_path)) {
            header("Content-Length: " . $size);
        }
    }

    /**
     * Check and set certain server config variables to ensure downloads work as intended.
     */
    private static function check_server_config() {
        wc_set_time_limit(0);
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 'Off');
        @session_write_close();
    }

    /**
     * Clean all output buffers.
     *
     * Can prevent errors, for example: transfer closed with 3 bytes remaining to read.
     *
     * @access private
     */
    private static function clean_buffers() {
        if (ob_get_level()) {
            $levels = ob_get_level();
            for ($i = 0; $i < $levels; $i++) {
                @ob_end_clean();
            }
        } else {
            @ob_end_clean();
        }
    }

    /**
     * Die with an error message if the download fails.
     * @param  string $message
     * @param  string  $title
     * @param  integer $status
     * @access private
     */
    private static function download_error($message, $title = '', $status = 404) {
        if (!strstr($message, '<a ')) {
            $message .= ' <a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="wc-forward">' . esc_html__('Go to shop', 'woocommerce') . '</a>';
        }
        wp_die($message, $title, array('response' => $status));
    }

    static public function get_download_url_transient($entry_id) {
        return get_transient('outofthebox_wc_download_' . $entry_id);
    }

    static public function set_download_url_transient($entry_id, $url) {
        /* Update progress */
        return set_transient('outofthebox_wc_download_' . $entry_id, $url, HOUR_IN_SECONDS);
    }

    public function get_processor() {
        if (empty($this->_processor)) {
            $this->_processor = new Processor($this->get_main());
        }

        return $this->_processor;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Main
     */
    public function get_main() {
        return $this->_main;
    }

}
