<?php

namespace TheLion\OutoftheBox;

/*
  Plugin Name: WP Cloud Plugin Out-of-the-Box (Dropbox)
  Plugin URI: https://www.wpcloudplugins.com/plugins/out-of-the-box-wordpress-plugin-for-dropbox/
  Description: Say hello to the most popular WordPress Dropbox plugin! Start using the Cloud even more efficiently by integrating it on your website.
  Version: 1.10.1
  Author: WP Cloud Plugins
  Author URI: https://www.wpcloudplugins.com
  Text Domain: outofthebox
 */

/* * ***********SYSTEM SETTINGS****************** */
define('OUTOFTHEBOX_VERSION', '1.10.1');
define('OUTOFTHEBOX_ROOTPATH', plugins_url('', __FILE__));
define('OUTOFTHEBOX_ROOTDIR', __DIR__);
define('OUTOFTHEBOX_CACHEDIR', WP_CONTENT_DIR . '/out-of-the-box-cache/');
define('OUTOFTHEBOX_CACHEURL', WP_CONTENT_URL . '/out-of-the-box-cache/');
define('OUTOFTHEBOX_SLUG', dirname(plugin_basename(__FILE__)) . '/out-of-the-box.php');


require_once 'includes/Autoload.php';

class Main {

    public $settings = false;

    /**
     * Construct the plugin object
     */
    public function __construct() {

        $this->load_default_values();

        add_action('init', array(&$this, 'init'));

        if (is_admin() && (!defined('DOING_AJAX') ||
                (isset($_REQUEST['action']) && ($_REQUEST['action'] === 'update-plugin')))) {
            $admin = new \TheLion\OutoftheBox\Admin($this);
        }

        add_action('wp_head', array(&$this, 'load_IE_styles'));

        $priority = add_filter('out-of-the-box_enqueue_priority', 10);
        add_action('wp_enqueue_scripts', array(&$this, 'load_scripts'), $priority);
        add_action('wp_enqueue_scripts', array(&$this, 'load_styles'));

        add_action('plugins_loaded', array(&$this, 'load_gravity_forms_addon'), 100);
        add_filter('woocommerce_integrations', array(&$this, 'load_woocommerce_addon'), 10);

        /* Shortcodes */
        add_shortcode('outofthebox', array(&$this, 'create_template'));

        /* After the Shortcode hook to make sure that the raw shortcode will not become visible when plugin isn't meeting the requirements */
        if ($this->can_run_plugin() === false) {
            return false;
        }

        /* Add user folder if needed */
        if (isset($this->settings['userfolder_oncreation']) && $this->settings['userfolder_oncreation'] === 'Yes') {
            add_action('user_register', array(&$this, 'user_folder_create'));
        }
        if (isset($this->settings['userfolder_update']) && $this->settings['userfolder_update'] === 'Yes') {
            add_action('profile_update', array(&$this, 'user_folder_update'), 100, 2);
        }
        if (isset($this->settings['userfolder_remove']) && $this->settings['userfolder_remove'] === 'Yes') {
            add_action('delete_user', array(&$this, 'user_folder_delete'));
        }

        add_action('wp_footer', array(&$this, 'load_custom_css'), 100);
        add_action('admin_footer', array(&$this, 'load_custom_css'), 100);

        /* Ajax calls */
        add_action('wp_ajax_nopriv_outofthebox-get-filelist', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-get-filelist', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-search', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-search', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-get-gallery', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-get-gallery', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-upload-file', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-upload-file', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-delete-entry', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-delete-entry', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-delete-entries', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-delete-entries', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-rename-entry', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-rename-entry', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-move-entry', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-move-entry', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-add-folder', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-add-folder', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-get-playlist', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-get-playlist', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-create-zip', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-create-zip', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-thumbnail', array(&$this, 'create_thumbnail'));
        add_action('wp_ajax_outofthebox-thumbnail', array(&$this, 'create_thumbnail'));

        add_action('wp_ajax_nopriv_outofthebox-create-link', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-create-link', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-embedded', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-embedded', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-download', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-download', array(&$this, 'start_process'));

        add_action('wp_ajax_nopriv_outofthebox-stream', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-stream', array(&$this, 'start_process'));


        add_action('wp_ajax_nopriv_outofthebox-preview', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-preview', array(&$this, 'start_process'));

        add_action('wp_ajax_outofthebox-reset-cache', array(&$this, 'start_process'));
        add_action('wp_ajax_outofthebox-revoke', array(&$this, 'start_process'));

        add_action('wp_ajax_outofthebox-getpopup', array(&$this, 'get_popup'));

        add_action('wp_ajax_outofthebox-linkusertofolder', array(&$this, 'user_folder_link'));
        add_action('wp_ajax_outofthebox-unlinkusertofolder', array(&$this, 'user_folder_unlink'));
        add_action('wp_ajax_outofthebox-rating-asked', array(&$this, 'rating_asked'));

        /* add settings link on plugin page */
        add_filter('plugin_row_meta', array(&$this, 'add_settings_link'), 99, 2);
    }

    public function init() {
        /* Localize */
        $i18n_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
        load_plugin_textdomain('outofthebox', false, $i18n_dir);
    }

    public function can_run_plugin() {
        if ((version_compare(PHP_VERSION, '5.5.0') < 0) || (!function_exists('curl_reset'))) {
            return false;
        }

        /* Check Cache Folder */
        if (!file_exists(OUTOFTHEBOX_CACHEDIR)) {
            @mkdir(OUTOFTHEBOX_CACHEDIR, 0755);
        }


        if (!is_writable(OUTOFTHEBOX_CACHEDIR)) {
            @chmod(OUTOFTHEBOX_CACHEDIR, 0755);

            if (!is_writable(OUTOFTHEBOX_CACHEDIR)) {
                return false;
            }
        }

        if (!file_exists(OUTOFTHEBOX_CACHEDIR . '.htaccess')) {
            return copy(OUTOFTHEBOX_ROOTDIR . '/cache/.htaccess', OUTOFTHEBOX_CACHEDIR . '.htaccess');
        }

        return true;
    }

    public function load_default_values() {

        $this->settings = get_option('out_of_the_box_settings', array(
            'purcasecode' => '',
            'dropbox_app_key' => '',
            'dropbox_app_secret' => '',
            'dropbox_app_token' => '',
            'shortlinks' => 'Dropbox',
            'bitly_login' => '',
            'bitly_apikey' => '',
            'google_analytics' => 'No',
            'lightbox_skin' => 'metro-black',
            'lightbox_path' => 'horizontal',
            'lightbox_rightclick' => 'No',
            'lightbox_showcaption' => 'click',
            'mediaplayer_skin' => 'default',
            'userfolder_name' => '%user_login% (%user_email%)',
            'userfolder_oncreation' => 'Yes',
            'userfolder_onfirstvisit' => 'No',
            'userfolder_update' => 'Yes',
            'userfolder_remove' => 'Yes',
            'userfolder_backend' => 'No',
            'userfolder_backend_auto_root' => '',
            'download_template_subject' => '',
            'download_template_subject_zip' => '',
            'download_template' => '',
            'upload_template_subject' => '',
            'upload_template' => '',
            'delete_template_subject' => '',
            'delete_template' => '',
            'filelist_template' => '',
            'permissions_edit_settings' => array('administrator'),
            'permissions_link_users' => array('administrator', 'editor'),
            'permissions_see_filebrowser' => array('administrator'),
            'permissions_add_shortcodes' => array('administrator', 'editor', 'author', 'contributor'),
            'permissions_add_links' => array('administrator', 'editor', 'author', 'contributor'),
            'permissions_add_embedded' => array('administrator', 'editor', 'author', 'contributor'),
            'custom_css' => '',
            'loaders' => array(),
            'colors' => array(),
            'gzipcompression' => '',
            'request_cache_max_age' => ''
        ));

        if ($this->settings === false) {
            return;
        }
        /* Remove 'advancedsettings' option of versions before 1.6.2 */
        $advancedsettings = get_option('out_of_the_box_advancedsettings');
        if ($advancedsettings !== false && $this->settings !== false) {
            $this->settings = array_merge($this->settings, $advancedsettings);
            delete_option('out_of_the_box_advancedsettings');
            $this->settings = get_option('out_of_the_box_settings');
        }

        $updated = false;
        /* Set default values */
        if (empty($this->settings['google_analytics'])) {
            $this->settings['google_analytics'] = 'No';
            $updated = true;
        }

        if (empty($this->settings['download_template_subject'])) {
            $this->settings['download_template_subject'] = '%sitename% | %visitor% downloaded %filepath%';
            $updated = true;
        }

        if (empty($this->settings['download_template_subject_zip'])) {
            $this->settings['download_template_subject_zip'] = '%sitename% | %visitor% downloaded %number_of_files% file(s) from %folder%';
            $updated = true;
        }

        if (empty($this->settings['download_template'])) {
            $this->settings['download_template'] = 'Hi!

%visitor% has downloaded the following files from your site: 

<ul>%filelist%</ul>';
            $updated = true;
        }

        if (empty($this->settings['upload_template_subject'])) {
            $this->settings['upload_template_subject'] = '%sitename% | %visitor% uploaded (%number_of_files%) file(s) to %folder%';
            $updated = true;
        }

        if (empty($this->settings['upload_template'])) {
            $this->settings['upload_template'] = 'Hi!

%visitor% has uploaded the following file(s) to your Dropbox:

<ul>%filelist%</ul>';
            $updated = true;
        }

        if (empty($this->settings['delete_template_subject'])) {
            $this->settings['delete_template_subject'] = '%sitename% | %visitor% deleted (%number_of_files%) file(s) from %folder%';
            $updated = true;
        }

        if (empty($this->settings['delete_template'])) {
            $this->settings['delete_template'] = 'Hi!

%visitor% has deleted the following file(s) on your Dropbox:

<ul>%filelist%</ul>';
            $updated = true;
        }

        if (empty($this->settings['filelist_template'])) {
            $this->settings['filelist_template'] = '<li><a href="%fileurl%" title-"%filename%">%filesafepath%<a/> (%filesize%)</li>';
            $updated = true;
        }

        if (empty($this->settings['mediaplayer_skin'])) {
            $this->settings['mediaplayer_skin'] = 'default';
            $updated = true;
        }

        if (empty($this->settings['lightbox_skin'])) {
            $this->settings['lightbox_skin'] = 'metro-black';
            $updated = true;
        }
        if (empty($this->settings['lightbox_path'])) {
            $this->settings['lightbox_path'] = 'horizontal';
            $updated = true;
        }


        if (empty($this->settings['permissions_edit_settings'])) {
            $this->settings['permissions_edit_settings'] = array('administrator');
            $updated = true;
        }
        if (empty($this->settings['permissions_link_users'])) {
            $this->settings['permissions_link_users'] = array('administrator', 'editor');
            $updated = true;
        }
        if (empty($this->settings['permissions_see_filebrowser'])) {
            $this->settings['permissions_see_filebrowser'] = array('administrator');
            $updated = true;
        }
        if (empty($this->settings['permissions_add_shortcodes'])) {
            $this->settings['permissions_add_shortcodes'] = array('administrator', 'editor', 'author', 'contributor');
            $updated = true;
        }
        if (empty($this->settings['permissions_add_links'])) {
            $this->settings['permissions_add_links'] = array('administrator', 'editor', 'author', 'contributor');
            $updated = true;
        }
        if (empty($this->settings['permissions_add_embedded'])) {
            $this->settings['permissions_add_embedded'] = array('administrator', 'editor', 'author', 'contributor');
            $updated = true;
        }

        if (empty($this->settings['gzipcompression'])) {
            $this->settings['gzipcompression'] = 'No';
            $updated = true;
        }

        if (empty($this->settings['request_cache_max_age'])) {
            $this->settings['request_cache_max_age'] = 30; // in minutes
            $updated = true;
        }

        if (empty($this->settings['userfolder_backend'])) {
            $this->settings['userfolder_backend'] = 'No';
            $updated = true;
        }

        if (empty($this->settings['userfolder_backend_auto_root'])) {
            $this->settings['userfolder_backend_auto_root'] = '';
            $updated = true;
        }

        if (empty($this->settings['colors'])) {
            $this->settings['colors'] = array(
                'style' => 'light',
                'background' => '#f2f2f2',
                'accent' => '#29ADE2',
                'black' => '#222',
                'dark1' => '#666',
                'dark2' => '#999',
                'white' => '#fff',
                'light1' => '#fcfcfc',
                'light2' => '#e8e8e8',
            );
            $updated = true;
        }

        if (empty($this->settings['loaders'])) {
            $this->settings['loaders'] = array(
                'style' => 'spinner',
                'loading' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_loading.gif',
                'no_results' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_no_results.png',
                'error' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_error.png',
                'upload' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_upload.gif',
                'protected' => OUTOFTHEBOX_ROOTPATH . '/css/images/loader_protected.png',
            );
            $updated = true;
        }

        if (empty($this->settings['lightbox_rightclick'])) {
            $this->settings['lightbox_rightclick'] = 'No';
            $updated = true;
        }

        if (empty($this->settings['lightbox_showcaption'])) {
            $this->settings['lightbox_showcaption'] = 'click';
            $updated = true;
        }

        if ($updated) {
            update_option('out_of_the_box_settings', $this->settings);
        }
    }

    public function add_settings_link($links, $file) {
        $plugin = plugin_basename(__FILE__);

        /* create link */
        if ($file == $plugin && !is_network_admin()) {
            return array_merge(
                    $links, array(sprintf('<a href="https://www.wpcloudplugins.com/updates" target="_blank">%s</a>', __('Download latest package', 'outofthebox'))), array(sprintf('<a href="options-general.php?page=%s">%s</a>', 'OutoftheBox_settings', __('Settings', 'outofthebox'))), array(sprintf('<a href="' . plugins_url('_documentation/index.html', __FILE__) . '" target="_blank">%s</a>', __('Documentation', 'outofthebox'))), array(sprintf('<a href="https://florisdeleeuwnl.zendesk.com/hc/en-us" target="_blank">%s</a>', __('Support', 'outofthebox')))
            );
        }

        return $links;
    }

    public function load_scripts() {

        $skin = $this->settings['mediaplayer_skin'];
        if ((!file_exists(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/Media.js")) ||
                (!file_exists(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/css/style.css")) ||
                (!file_exists(OUTOFTHEBOX_ROOTDIR . "/skins/$skin/player.php"))) {
            $skin = 'default';
        }

        wp_register_style('OutoftheBox.Media', plugins_url("/skins/$skin/css/style.css", __FILE__), false);
        wp_register_script('jQuery.jplayer', plugins_url("/skins/$skin/jquery.jplayer/jplayer.playlist.min.js", __FILE__), array('jquery'));
        wp_register_script('jQuery.jplayer.playlist', plugins_url("/skins/$skin/jquery.jplayer/jquery.jplayer.min.js", __FILE__), array('jquery'));

        /* load in footer */
        wp_register_script('OutoftheBox.Media', plugins_url("/skins/$skin/Media.js", __FILE__), array('jquery'), false, true);
        wp_register_script('jQuery.iframe-transport', plugins_url('includes/jquery-file-upload/js/jquery.iframe-transport.js', __FILE__), array('jquery'), false, true);
        wp_register_script('jQuery.fileupload', plugins_url('includes/jquery-file-upload/js/jquery.fileupload.js', __FILE__), array('jquery'), false, true);
        wp_register_script('jQuery.fileupload-process', plugins_url('includes/jquery-file-upload/js/jquery.fileupload-process.js', __FILE__), array('jquery'), false, true);

        wp_register_script('OutoftheBox.Libraries', plugins_url('includes/js/library.js', __FILE__), array('jquery'), OUTOFTHEBOX_VERSION, true);
        wp_register_script('OutoftheBox', plugins_url('includes/js/Main.min.js', __FILE__), array('jquery', 'OutoftheBox.Libraries'), OUTOFTHEBOX_VERSION, true);

        wp_register_script('OutoftheBox.tinymce', plugins_url('includes/js/Tinymce_popup.js', __FILE__), array('jquery'), OUTOFTHEBOX_VERSION, true);

        $post_max_size_bytes = min(\TheLion\OutoftheBox\Helpers::return_bytes(ini_get('post_max_size')), \TheLion\OutoftheBox\Helpers::return_bytes(ini_get('upload_max_filesize')));

        $localize = array(
            'plugin_ver' => OUTOFTHEBOX_VERSION,
            'plugin_url' => plugins_url('', __FILE__),
            'ajax_url' => admin_url('admin-ajax.php'),
            'js_url' => plugins_url('/skins/' . $this->settings['mediaplayer_skin'] . '/jquery.jplayer', __FILE__),
            'cookie_path' => COOKIEPATH,
            'cookie_domain' => COOKIE_DOMAIN,
            'is_mobile' => wp_is_mobile(),
            'lightbox_skin' => $this->settings['lightbox_skin'],
            'lightbox_path' => $this->settings['lightbox_path'],
            'lightbox_rightclick' => $this->settings['lightbox_rightclick'],
            'lightbox_showcaption' => $this->settings['lightbox_showcaption'],
            'post_max_size' => $post_max_size_bytes,
            'google_analytics' => (($this->settings['google_analytics'] === 'Yes') ? 1 : 0),
            'refresh_nonce' => wp_create_nonce("outofthebox-get-filelist"),
            'gallery_nonce' => wp_create_nonce("outofthebox-get-gallery"),
            'upload_nonce' => wp_create_nonce("outofthebox-upload-file"),
            'delete_nonce' => wp_create_nonce("outofthebox-delete-entry"),
            'rename_nonce' => wp_create_nonce("outofthebox-rename-entry"),
            'move_nonce' => wp_create_nonce("outofthebox-move-entry"),
            'addfolder_nonce' => wp_create_nonce("outofthebox-add-folder"),
            'getplaylist_nonce' => wp_create_nonce("outofthebox-get-playlist"),
            'createzip_nonce' => wp_create_nonce("outofthebox-create-zip"),
            'createlink_nonce' => wp_create_nonce("outofthebox-create-link"),
            'str_loading' => __('Hang on. Waiting for the files...', 'outofthebox'),
            'str_processing' => __('Processing...', 'outofthebox'),
            'str_success' => __('Success', 'outofthebox'),
            'str_error' => __('Error', 'outofthebox'),
            'str_inqueue' => __('In queue', 'outofthebox'),
            'str_uploading_local' => __('Uploading to Server', 'outofthebox'),
            'str_uploading_cloud' => __('Uploading to Cloud', 'outofthebox'),
            'str_error_title' => __('Error', 'outofthebox'),
            'str_close_title' => __('Close', 'outofthebox'),
            'str_start_title' => __('Start', 'outofthebox'),
            'str_cancel_title' => __('Cancel', 'outofthebox'),
            'str_delete_title' => __('Delete', 'outofthebox'),
            'str_zip_title' => __('Create zip file', 'outofthebox'),
            'str_copy_to_clipboard_title' => __('Copy to clipboard', 'outofthebox'),
            'str_delete' => __('Do you really want to delete:', 'outofthebox'),
            'str_delete_multiple' => __('Do you really want to delete these files?', 'outofthebox'),
            'str_rename_failed' => __("That doesn't work. Are there any illegal characters (<>:\"/\|?*) in the filename?", 'outofthebox'),
            'str_rename_title' => __('Rename', 'outofthebox'),
            'str_rename' => __('Rename to:', 'outofthebox'),
            'str_no_filelist' => __("Can't receive filelist", 'outofthebox'),
            'str_addfolder_title' => __('Add folder', 'outofthebox'),
            'str_addfolder' => __('New folder', 'outofthebox'),
            'str_zip_nofiles' => __('No files found or selected', 'outofthebox'),
            'str_zip_createzip' => __('Creating zip file', 'outofthebox'),
            'str_share_link' => __('Share file', 'outofthebox'),
            'str_create_shared_link' => __('Creating shared link...', 'outofthebox'),
            'str_previous_title' => __('Previous', 'outofthebox'),
            'str_next_title' => __('Next', 'outofthebox'),
            'str_xhrError_title' => __('This content failed to load', 'outofthebox'),
            'str_imgError_title' => __('This image failed to load', 'outofthebox'),
            'str_startslideshow' => __('Start slideshow', 'outofthebox'),
            'str_stopslideshow' => __('Stop slideshow', 'outofthebox'),
            'str_nolink' => __('Not yet linked to a folder', 'outofthebox'),
            'maxNumberOfFiles' => __('Maximum number of files exceeded', 'outofthebox'),
            'acceptFileTypes' => __('File type not allowed', 'outofthebox'),
            'maxFileSize' => __('File is too large', 'outofthebox'),
            'minFileSize' => __('File is too small', 'outofthebox'),
            'str_iframe_loggedin' => "<div class='empty_iframe'><h1>" . __('Still Waiting?', 'outofthebox') . "</h1><span>" . __("If the document doesn't open, you are probably trying to access a protected file which requires you to be logged in on Dropbox.", 'outofthebox') . " <strong>" . __('Try to open the file in a new window.', 'outofthebox') . "</strong></span></div>"
        );

        wp_localize_script('OutoftheBox', 'OutoftheBox_vars', $localize);
    }

    public function load_styles() {
        $is_rtl_css = (is_rtl() ? '-rtl' : '');

        //wp_register_style('OutoftheBox-fileupload-jquery-ui', plugins_url('includes/jquery-file-upload/css', __FILE__) . '/jquery.fileupload-ui.css');

        $skin = $this->settings['lightbox_skin'];
        wp_register_style('ilightbox', plugins_url('includes/iLightBox/css/ilightbox.css', __FILE__), false);
        wp_register_style('ilightbox-skin-outofthebox', plugins_url('includes/iLightBox/' . $skin . '-skin/skin.css', __FILE__), false);
        wp_register_style('qtip', plugins_url('includes/jquery-qTip/jquery.qtip.min.css', __FILE__), null, false);
        wp_register_style('Awesome-Font-css', plugins_url('includes/font-awesome/css/font-awesome.min.css', __FILE__), false);
        wp_register_style('OutoftheBox', plugins_url("css/main$is_rtl_css.css", __FILE__), array('Awesome-Font-css'), OUTOFTHEBOX_VERSION);
        wp_register_style('OutoftheBox.tinymce', plugins_url("css/tinymce$is_rtl_css.css", __FILE__), null, OUTOFTHEBOX_VERSION);
    }

    public function load_IE_styles() {

        echo "<!--[if IE]>\n";
        echo "<link rel='stylesheet' type='text/css' href='" . plugins_url('css/skin-ie.css', __FILE__) . "' />\n";
        echo "<![endif]-->\n";
    }

    public function load_gravity_forms_addon() {
        require_once 'includes/GravityForms.php';
    }

    public function load_woocommerce_addon($integrations) {
        global $woocommerce;

        if (is_object($woocommerce) && version_compare($woocommerce->version, '3.0', '>=')) {
            $integrations[] = __NAMESPACE__ . '\WooCommerce';
        }

        return $integrations;
    }

    public function start_process() {

        if (!isset($_REQUEST['action'])) {
            return false;
        }

        switch ($_REQUEST['action']) {
            case 'outofthebox-get-filelist':
            case 'outofthebox-download':
            case 'outofthebox-stream':
            case 'outofthebox-preview':
            case 'outofthebox-create-zip':
            case 'outofthebox-create-link':
            case 'outofthebox-embedded':
            case 'outofthebox-reset-cache':
            case 'outofthebox-revoke':
            case 'outofthebox-get-gallery':
            case 'outofthebox-upload-file':
            case 'outofthebox-delete-entry':
            case 'outofthebox-delete-entries':
            case 'outofthebox-rename-entry':
            case 'outofthebox-move-entry':
            case 'outofthebox-add-folder':
            case 'outofthebox-get-playlist':
                require_once(ABSPATH . 'wp-includes/pluggable.php');
                $processor = new Processor($this);
                $processor->start_process();
                break;
        }
    }

    public function load_custom_css() {
        $css_html = '<!-- Custom OutoftheBox CSS Styles -->' . "\n";
        $css_html .= '<style type="text/css" media="screen">' . "\n";
        $css = '';

        if (!empty($this->settings['custom_css'])) {
            $css .= $this->settings['custom_css'] . "\n";
        }

        if ($this->settings['loaders']['style'] === 'custom') {
            $css .= "#OutoftheBox .loading{  background-image: url(" . $this->settings['loaders']['loading'] . ");}" . "\n";
            $css .= "#OutoftheBox .loading.upload{    background-image: url(" . $this->settings['loaders']['upload'] . ");}" . "\n";
            $css .= "#OutoftheBox .loading.error{  background-image: url(" . $this->settings['loaders']['error'] . ");}" . "\n";
        }

        $css .= $this->get_color_css();

        $css_html .= \TheLion\OutoftheBox\Helpers::compress_css($css);
        $css_html .= '</style>' . "\n";

        echo $css_html;
    }

    public function get_color_css() {
        $css = file_get_contents(OUTOFTHEBOX_ROOTDIR . '/css/skin.' . $this->settings['colors']['style'] . '.min.css');
        return preg_replace_callback('/%(.*)%/iU', array(&$this, 'fill_placeholder_styles'), $css);
    }

    public function fill_placeholder_styles($matches) {
        if (isset($this->settings['colors'][$matches[1]])) {
            return $this->settings['colors'][$matches[1]];
        }
        return 'initial';
    }

    public function create_template($atts = array()) {

        if (is_feed()) {
            return __('Please browse to the page to see this content', 'outofthebox') . '.';
        }

        if ($this->can_run_plugin() === false) {
            return '<i>>>> ' . __('ERROR: Contact the Administrator to see this content', 'outofthebox') . ' <<<</i>';
        }

        $processor = new Processor($this);
        return $processor->create_from_shortcode($atts);
    }

    public function create_thumbnail() {
        $processor = new Processor($this);
        return $processor->create_thumbnail();
    }

    public function get_popup() {
        include OUTOFTHEBOX_ROOTDIR . '/templates/tinymce_popup.php';
        die();
    }

    public function ask_for_review($force = false) {

        $rating_asked = get_option('out_of_the_box_rating_asked', false);
        if ($rating_asked == true) {
            return;
        }
        $counter = get_option('out_of_the_box_shortcode_opened', 0);
        if ($counter < 10) {
            return;
        }
        ?>

        <div class="enjoying-container lets-ask">
          <div class="enjoying-text"><?php _e('Enjoying Out-of-the-Box?', 'outofthebox'); ?></div>
          <div class="enjoying-buttons">
            <a class="enjoying-button" id="enjoying-button-lets-ask-no"><?php _e('Not really', 'outofthebox'); ?></a>
            <a class="enjoying-button default"  id="enjoying-button-lets-ask-yes"><?php _e('Yes!', 'outofthebox'); ?></a>
          </div>
        </div>

        <div class="enjoying-container go-for-it" style="display:none">
          <div class="enjoying-text"><?php _e('Great! How about a review, then?', 'outofthebox'); ?></div>
          <div class="enjoying-buttons">
            <a class="enjoying-button" id="enjoying-button-go-for-it-no"><?php _e('No, thanks', 'outofthebox'); ?></a>
            <a class="enjoying-button default" id="enjoying-button-go-for-it-yes" href="https://codecanyon.net/item/outofthebox-dropbox-plugin-for-wordpress-/reviews/5529125?ref=_DeLeeuw_" target="_blank"><?php _e('Ok, sure!', 'outofthebox'); ?></a>
          </div>
        </div>

        <div class="enjoying-container mwah" style="display:none">
          <div class="enjoying-text"><?php _e('Would you mind giving us some feedback?', 'outofthebox'); ?></div>
          <div class="enjoying-buttons">
            <a class="enjoying-button" id="enjoying-button-mwah-no"><?php _e('No, thanks', 'outofthebox'); ?></a>
            <a class="enjoying-button default" id="enjoying-button-mwah-yes" href="https://docs.google.com/forms/d/e/1FAIpQLSct8a8d-_7iSgcvdqeFoSSV055M5NiUOgt598B95YZIaw7LhA/viewform?usp=pp_url&entry.83709281=Out-of-the-Box+(Dropbox)&entry.450972953&entry.1149244898" target="_blank"><?php _e('Ok, sure!', 'outofthebox'); ?></a>
          </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
              $('#enjoying-button-lets-ask-no').click(function () {
                $('.enjoying-container.lets-ask').fadeOut('fast', function () {
                  $('.enjoying-container.mwah').fadeIn();
                })
              });

              $('#enjoying-button-lets-ask-yes').click(function () {
                $('.enjoying-container.lets-ask').fadeOut('fast', function () {
                  $('.enjoying-container.go-for-it').fadeIn();
                })
              });

              $('#enjoying-button-mwah-no, #enjoying-button-go-for-it-no').click(function () {
                $('.enjoying-container').fadeOut('fast', function () {
                  $(this).remove();
                });
              });

              $('#enjoying-button-go-for-it-yes').click(function () {
                $('.enjoying-container').fadeOut('fast', function () {
                  $(this).remove();
                });
              });

              $('#enjoying-button-mwah-yes').click(function () {
                $('.enjoying-container').fadeOut('fast', function () {
                  $(this).remove();
                });
              });

              $('#enjoying-button-mwah-no, #enjoying-button-go-for-it-no, #enjoying-button-go-for-it-yes, #enjoying-button-mwah-yes').click(function () {
                $.ajax({type: "POST",
                  url: '<?php echo admin_url('admin-ajax.php'); ?>',
                  data: {
                    action: 'outofthebox-rating-asked',
                  }
                });
              });
            })
        </script>
        <?php
    }

    public function rating_asked() {
        update_option('out_of_the_box_rating_asked', true);
    }

    public function user_folder_link() {
        check_ajax_referer('outofthebox-create-link');

        $processor = new Processor($this);
        $userfolders = new UserFolders($processor);

        $linkedto = array('folderid' => rawurldecode($_REQUEST['id']), 'foldertext' => rawurldecode($_REQUEST['id']));
        $userid = $_REQUEST['userid'];

        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_link_users'])) {
            $userfolders->manually_link_folder($userid, $linkedto);
        };
    }

    public function user_folder_unlink() {
        check_ajax_referer('outofthebox-create-link');

        $processor = new Processor($this);
        $userfolders = new UserFolders($processor);

        $userid = $_REQUEST['userid'];

        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_link_users'])) {
            $userfolders->manually_unlink_folder($userid);
        }
    }

    public function user_folder_create($user_id) {
        $processor = new Processor($this);
        $userfolders = new UserFolders($processor);
        $userfolders->create_user_folders_for_shortcodes($user_id);
    }

    public function user_folder_update($user_id, $old_user_data = false) {
        $processor = new Processor($this);
        $userfolders = new UserFolders($processor);
        $userfolders->update_user_folder($user_id, $old_user_data);
    }

    public function user_folder_delete($user_id) {
        $processor = new Processor($this);
        $userfolders = new UserFolders($processor);
        $userfolders->remove_user_folder($user_id);
    }

}

/* Installation and uninstallation hooks */
register_activation_hook(__FILE__, __NAMESPACE__ . '\OutoftheBox_Network_Activate');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\OutoftheBox_Network_Deactivate');

$OutoftheBox = new \TheLion\OutoftheBox\Main();

/**
 * Activate the plugin on network
 */
function OutoftheBox_Network_Activate($network_wide) {
    if (is_multisite() && $network_wide) { // See if being activated on the entire network or one blog
        global $wpdb;

        /* Get this so we can switch back to it later */
        $current_blog = $wpdb->blogid;
        /* For storing the list of activated blogs */
        $activated = array();

        /* Get all blogs in the network and activate plugin on each one */
        $sql = "SELECT blog_id FROM %d";
        $blog_ids = $wpdb->get_col($wpdb->prepare($sql, $wpdb->blogs));
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            OutoftheBox_Activate(); // The normal activation function
            $activated[] = $blog_id;
        }

        /* Switch back to the current blog */
        switch_to_blog($current_blog);

        /* Store the array for a later function */
        update_site_option('out_of_the_box_activated', $activated);
    } else { // Running on a single blog
        OutoftheBox_Activate(); // The normal activation function
    }
}

/**
 * Activate the plugin
 */
function OutoftheBox_Activate() {
    add_option('out_of_the_box_settings', array(
        'dropbox_app_key' => '',
        'dropbox_app_secret' => '',
        'dropbox_app_token' => '',
        'purcasecode' => '',
        'custom_css' => '',
        'shortlinks' => 'Dropbox',
        'google_analytics' => 'No',
        'lightbox_skin' => 'metro-black',
        'lightbox_path' => 'horizontal',
        'mediaplayer_skin' => 'default',
        'bitly_login' => '',
        'bitly_apikey' => '',
        'userfolder_name' => '%user_login% (%user_email%)',
        'userfolder_oncreation' => 'Yes',
        'userfolder_onfirstvisit' => 'No',
        'userfolder_update' => 'Yes',
        'userfolder_remove' => 'Yes',
        'userfolder_backend' => 'No',
        'userfolder_backend_auto_root' => '',
        'download_template_subject' => '',
        'download_template_subject_zip' => '',
        'download_template' => '',
        'upload_template_subject' => '',
        'upload_template' => '',
        'delete_template_subject' => '',
        'delete_template' => '',
        'filelist_template' => '',
        'gzipcompression' => '',
        'request_cache_max_age' => '')
    );

    update_option('out_of_the_box_lists', array());
    @unlink(OUTOFTHEBOX_CACHEDIR . '/index');
}

/**
 * Deactivate the plugin on network
 */
function OutoftheBox_Network_Deactivate($network_wide) {
    if (is_multisite() && $network_wide) { // See if being activated on the entire network or one blog
        global $wpdb;

        // Get this so we can switch back to it later
        $current_blog = $wpdb->blogid;

        // If the option does not exist, plugin was not set to be network active
        if (get_site_option('out_of_the_box_activated') === false) {
            return false;
        }

        // Get all blogs in the network
        $activated = get_site_option('out_of_the_box_activated');

        $sql = "SELECT blog_id FROM %d";
        $blog_ids = $wpdb->get_col($wpdb->prepare($sql, $wpdb->blogs));
        foreach ($blog_ids as $blog_id) {
            if (!in_array($blog_id, $activated)) { // Plugin is not activated on that blog
                switch_to_blog($blog_id);
                OutoftheBox_Deactivate();
            }
        }

        // Switch back to the current blog
        switch_to_blog($current_blog);

        // Store the array for a later function
        update_site_option('out_of_the_box_activated', $activated);
    } else { // Running on a single blog
        OutoftheBox_Deactivate();
    }
}

/**
 * Deactivate the plugin
 */
function OutoftheBox_Deactivate() {
    update_option('out_of_the_box_lists', array());

    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {

        if ($path->getFilename() === '.htaccess') {
            continue;
        }
    }
}

/**
 * Deactivate the plugin on network
 */
function OutoftheBox_Network_Uninstall($network_wide) {
    if (is_multisite() && $network_wide) { // See if being activated on the entire network or one blog
        global $wpdb;

        // Get this so we can switch back to it later
        $current_blog = $wpdb->blogid;

        // If the option does not exist, plugin was not set to be network active
        if (get_site_option('out_of_the_box_activated') === false) {
            return false;
        }

        // Get all blogs in the network
        $activated = get_site_option('out_of_the_box_activated');

        $sql = "SELECT blog_id FROM %d";
        $blog_ids = $wpdb->get_col($wpdb->prepare($sql, $wpdb->blogs));
        foreach ($blog_ids as $blog_id) {
            if (!in_array($blog_id, $activated)) { // Plugin is not activated on that blog
                switch_to_blog($blog_id);
                OutoftheBox_Uninstall();
            }
        }

        // Switch back to the current blog
        switch_to_blog($current_blog);

        // Store the array for a later function
        update_site_option('out_of_the_box_activated', $activated);
    } else { // Running on a single blog
        OutoftheBox_Uninstall();
    }
}

/**
 * Deactivate the plugin
 */
function OutoftheBox_Uninstall() {
    //delete_option('out_of_the_box_settings');
    delete_option('out_of_the_box_lists');
    delete_option('out_of_the_box_activated');
    delete_site_option('out_of_the_box_guestlinkedto');

    $cachefiles = @scandir(OUTOFTHEBOX_CACHEDIR);

    if ($cachefiles !== FALSE) {
        $cachefiles = array_diff($cachefiles, array('..', '.', '.htaccess'));
        foreach ($cachefiles as $cachefile) {
            @unlink(OUTOFTHEBOX_CACHEDIR . $cachefile);
        }
    }
}
