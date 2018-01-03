<?php

namespace TheLion\OutoftheBox;

class Admin {

    /**
     *
     * @var \TheLion\OutoftheBox\Main 
     */
    private $_main;
    private $settings_key = 'out_of_the_box_settings';
    private $plugin_options_key = 'OutoftheBox_settings';
    private $plugin_network_options_key = 'OutoftheBox_network_settings';
    private $plugin_id = 5529125;
    private $settingspage;
    private $filebrowserpage;
    private $userpage;
    public $settings;

    /**
     * Construct the plugin object
     */
    public function __construct(\TheLion\OutoftheBox\Main $main) {

        $this->_main = $main;

        /* Check if plugin can be used */
        if ($main->can_run_plugin() === false) {
            add_action('admin_notices', array(&$this, 'get_admin_notice'));
            return;
        }

        /* Init */
        add_action('init', array(&$this, 'load_settings'));
        add_action('admin_init', array(&$this, 'RegisterSettings'));
        add_action('admin_init', array(&$this, 'check_for_updates'));
        add_action('admin_enqueue_scripts', array(&$this, 'load_admin'));

        /* add TinyMCE button */
        /* Depends on the theme were to load.... */
        add_action('init', array(&$this, 'load_shortcode_buttons'));
        add_action('admin_head', array(&$this, 'load_shortcode_buttons'));

        /* Add menu's */
        add_action('admin_menu', array(&$this, 'add_admin_menu'));
        add_action('network_admin_menu', array(&$this, 'add_admin_network_menu'));

        /* Network save settings call */
        add_action('network_admin_edit_' . $this->plugin_network_options_key, array($this, 'save_settings_network'));

        /* Save settings call */
        add_filter('pre_update_option_' . $this->settings_key, array($this, 'save_settings'), 10, 2);

        /* Notices */
        add_action('admin_notices', array(&$this, 'get_admin_notice_not_authorized'));
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Main
     */
    public function get_main() {
        return $this->_main;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Processor
     */
    public function get_processor() {
        if (empty($this->_processor)) {
            $this->_processor = new \TheLion\OutoftheBox\Processor($this->get_main());
        }

        return $this->_processor;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\App
     */
    public function get_app() {
        if (empty($this->_app)) {
            $this->_app = new \TheLion\OutoftheBox\App($this->get_processor());
            $this->_app->start_client();
        }

        return $this->_app;
    }

    public function load_admin($hook) {

        if ($hook == $this->filebrowserpage || $hook == $this->userpage || $hook == $this->settingspage) {
            $this->get_main()->load_scripts();
            $this->get_main()->load_styles();

            wp_enqueue_script('jquery-effects-fade');
            wp_enqueue_script('OutoftheBox.Libraries');

            wp_enqueue_style('qtip');
            wp_enqueue_style('OutoftheBox.tinymce');
            wp_enqueue_style('Awesome-Font-css');
        }

        if ($hook == $this->settingspage) {
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('OutoftheBox.tinymce');
            ;
        }

        if ($hook == $this->userpage) {
            wp_enqueue_style('OutoftheBox');
            add_thickbox();
        }
    }

    /**
     * add a menu
     */
    public function add_admin_menu() {
        /* Add a page to manage this plugin's settings */
        $menuadded = false;

        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            add_menu_page('Out-of-the-Box', 'Out-of-the-Box', 'read', $this->plugin_options_key, array(&$this, 'load_settings_page'), OUTOFTHEBOX_ROOTPATH . '/css/images/dropbox_logo_small.png');
            $menuadded = true;
            $this->settingspage = add_submenu_page($this->plugin_options_key, 'Out-of-the-Box - ' . __('Settings'), __('Settings'), 'read', $this->plugin_options_key, array(&$this, 'load_settings_page'));
        }
        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_link_users'])) {
            if (!$menuadded) {
                $this->userpage = add_menu_page('Out-of-the-Box', 'Out-of-the-Box', 'read', $this->plugin_options_key, array(&$this, 'load_linkusers_page'), OUTOFTHEBOX_ROOTPATH . '/css/images/dropbox_logo_small.png');
                $this->userpage = add_submenu_page($this->plugin_options_key, __('Private Folders', 'outofthebox'), __('Private Folders', 'outofthebox'), 'read', $this->plugin_options_key, array(&$this, 'load_linkusers_page'));
                $menuadded = true;
            } else {
                $this->userpage = add_submenu_page($this->plugin_options_key, __('Private Folders', 'outofthebox'), __('Private Folders', 'outofthebox'), 'read', $this->plugin_options_key . '_linkusers', array(&$this, 'load_linkusers_page'));
            }
        }
        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_see_filebrowser'])) {
            if (!$menuadded) {
                $this->filebrowserpage = add_menu_page('Out-of-the-Box', 'Out-of-the-Box', 'read', $this->plugin_options_key, array(&$this, 'load_filebrowser_page'), OUTOFTHEBOX_ROOTPATH . '/css/images/dropbox_logo_small.png');
                $this->filebrowserpage = add_submenu_page($this->plugin_options_key, __('File browser', 'outofthebox'), __('File browser', 'outofthebox'), 'read', $this->plugin_options_key, array(&$this, 'load_filebrowser_page'));
            } else {
                $this->filebrowserpage = add_submenu_page($this->plugin_options_key, __('File browser', 'outofthebox'), __('File browser', 'outofthebox'), 'read', $this->plugin_options_key . '_filebrowser', array(&$this, 'load_filebrowser_page'));
            }
        }
    }

    public function add_admin_network_menu() {
        add_menu_page('Out-of-the-Box', 'Out-of-the-Box', 'manage_options', $this->plugin_network_options_key, array(&$this, 'load_settings_network_page'), OUTOFTHEBOX_ROOTPATH . '/css/images/dropbox_logo_small.png');
    }

    public function RegisterSettings() {
        register_setting($this->settings_key, $this->settings_key);
    }

    function load_settings() {
        $this->settings = (array) get_option($this->settings_key);

        $updated = false;
        if (!isset($this->settings['dropbox_app_key'])) {
            $this->settings['dropbox_app_key'] = '';
            $this->settings['dropbox_app_secret'] = '';
            $updated = true;
        }

        if ($updated) {
            update_option($this->settings_key, $this->settings);
        }
    }

    public function load_settings_page() {
        if (!\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'outofthebox'));
        }

        include(sprintf("%s/templates/admin.php", OUTOFTHEBOX_ROOTDIR));
    }

    public function load_settings_network_page() {
        $outofthebox_purchaseid = get_site_option('outofthebox_purchaseid');
        ?>
        <div class="wrap">
          <div class='left' style="min-width:400px; max-width:650px; padding: 0 20px 0 0; float:left">
            <?php if ($_GET['updated']) { ?>
                <div id="message" class="updated"><p><?php _e('Saved!', 'outofthebox'); ?></p></div>
            <?php } ?>
            <form action="<?php echo network_admin_url('edit.php?action=' . $this->plugin_network_options_key); ?>" method="post">
              <?php
              echo __('If you would like to receive updates, please insert your Purchase code', 'outofthebox') . '. ' .
              '<a href="http://support.envato.com/index.php?/Knowledgebase/Article/View/506/54/where-can-i-find-my-purchase-code">' .
              __('Where do I find the purchase code?', 'outofthebox') . '</a>.';
              ?>
              <table class="form-table">
                <tbody>
                  <tr valign="top">
                    <th scope="row"><?php _e('Purchase Code', 'outofthebox'); ?></th>
                    <td><input type="text" name="outofthebox_purchaseid" id="outofthebox_purchaseid" value="<?php echo $outofthebox_purchaseid; ?>" placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX" maxlength="37" style="width:90%"/></td>
                  </tr>
                </tbody>
              </table>
              <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
            </form>
          </div>
        </div>
        <?php
    }

    public function save_settings($new_settings, $old_settings) {

        foreach ($new_settings as $setting_key => &$value) {
            if ($value === 'on') {
                $value = 'Yes';
            }

            if ($setting_key === 'dropbox_app_own' && $value === 'No') {
                $new_settings['dropbox_app_key'] = '';
                $new_settings['dropbox_app_secret'] = '';
            }
        }

        return $new_settings;
    }

    public function save_settings_network() {
        if (current_user_can('manage_network_options')) {
            update_site_option('outofthebox_purchaseid', $_POST['outofthebox_purchaseid']);
        }

        wp_redirect(
                add_query_arg(
                        array('page' => $this->plugin_network_options_key, 'updated' => 'true'), network_admin_url('admin.php')
                )
        );
        exit;
    }

    function load_filebrowser_page() {

        if (!\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_see_filebrowser'])) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'outofthebox'));
        }

        include(sprintf("%s/templates/admin_filebrowser.php", OUTOFTHEBOX_ROOTDIR));
    }

    function load_linkusers_page() {

        if (!\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_link_users'])) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'outofthebox'));
        }
        $linkusers = new LinkUsers($this->get_main());
        $linkusers->render();
    }

    public function get_plugin_activated_box() {
        $purchasecode = $this->settings['purcasecode'];


        /* Check if Auto-update is being activated */
        if (isset($_REQUEST['purchase_code']) && isset($_REQUEST['plugin_id']) && ((int) $_REQUEST['plugin_id'] === $this->plugin_id)) {
            $purchasecode = $this->settings['purcasecode'] = sanitize_key($_REQUEST['purchase_code']);
            update_option($this->settings_key, $this->settings);
        }


        $box_class = 'oftb-updated';
        $box_text = __('The plugin is <strong>Activated</strong> and the <strong>Auto-Updater</strong> enabled', 'outofthebox') . ". " . __('Your purchasecode', 'outofthebox') . ":<br/><code style='user-select: initial;'>" . esc_attr($this->settings['purcasecode']) . '</code>';
        if (empty($purchasecode)) {
            $box_class = 'oftb-error';
            $box_text = __('The plugin is <strong>Not Activated</strong> and the <strong>Auto-Updater</strong> disabled', 'outofthebox') . ". " . __('Please activate your copy in order to have direct access to the latest updates and to get support', 'outofthebox') . ". ";
            $box_text .= "</p><p><input id='updater_button' type='button' value='Activate' class='simple-button blue' />";
        } else {
            $box_text .= "</p><p><input id='check_updates_button' type='button' class='simple-button blue' value='" . __('Check for Updates', 'outofthebox') . "' />";
        }

        return "<div id='message' class='$box_class'><p>$box_text</p></div>";
    }

    public function get_plugin_authorization_box() {

        $revokebutton = "<div id='revokeDropbox_button' type='button' class='simple-button blue'/>" . __('Revoke authorization', 'outofthebox') . "&nbsp;<div class='oftb-spinner'></div></div>";

        try {
            $app = $this->get_app();
        } catch (\Exception $ex) {
            error_log('[Out-of-the-Box message]: ' . sprintf('Out-of-the-Box has encountered an error: %s', $ex->getMessage()));

            $box_class = 'oftb-error';
            $box_text = '<strong>' . __('Out-of-the-Box has encountered an error', 'outofthebox') . "</strong> ";
            $box_text .= '<p><em>' . __('Error Details', 'outofthebox') . ": " . $ex->getMessage() . '</em></p>';
            return "<div id = 'message' class = '$box_class'><p>$box_text</p><p>$revokebutton</p></div>";
        }

        $authorizebutton = "<div id='authorizeDropbox_button' type='button' class='simple-button blue' data-url='{$app->get_auth_url()}'>" . __('(Re) Authorize the Plugin!', 'outofthebox') . "&nbsp;<div class='oftb-spinner'></div></div>";

        $box_redirect_msg = '';

        if ($app->has_access_token()) {
            try {

                $client = $this->get_processor()->get_client();

                $account = $client->get_account_info();
                $account_name = $account->getDisplayName();
                $account_email = $account->getEmail();
                $account_type = $account->getAccountType();
                $account_verified = $account->emailIsVerified();
                $account_verified_text = ($account_verified) ? '' : "<p>" . sprintf(__('Your Dropbox account is not verified. Please take a look at %sthis Dropbox Article%s for more information how to verify your account', 'outofthebox'), '<a href="https://www.dropbox.com/help/sign-in/verify-email" target="blank">', '</a>') . "</p>";

                $account_space = $client->get_account_space_info();
                $account_space_quota_used = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($account_space['used']);
                $account_space_quota_total = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($account_space['allocation']['allocated']);

                $box_class = ($account_verified) ? 'oftb-updated' : 'oftb-error';
                $box_text = __('Out-of-the-Box is succesfully authorized and linked with Dropbox account:', 'outofthebox') . "<br/><span data-type='$account_type'><strong>$account_name ($account_email - $account_space_quota_used/$account_space_quota_total)</strong></span> $account_verified_text";
                $box_buttons = $revokebutton;
            } catch (\Exception $ex) {
                error_log('[Out-of-the-Box message]: ' . sprintf('Out-of-the-Box has encountered an error: %s', $ex->getMessage()));


                $box_class = 'oftb-error';
                $box_text = __('Out-of-the-Box has encountered an error', 'outofthebox') . ": " . $ex->getMessage();
                if ($app->has_plugin_own_app()) {
                    $box_text .= '<p>' . __('Please fall back to the default App by clearing the KEY and Secret on the Advanced settings tab', 'outofthebox') . '.</p>';
                }

                $box_text .= '<p><em>' . __('Error Details', 'outofthebox') . ": " . $ex->getMessage() . '</em></p>';
                $box_buttons = $revokebutton . $authorizebutton;
            }
        } else {

            $box_class = 'oftb-error';
            $box_text = __("Plugin isn't linked to your Dropbox... Please Authorize!", 'outofthebox');
            $box_buttons = $authorizebutton;
        }

        return "<div id = 'message' class = '$box_class'><p>$box_text</p><p>$box_redirect_msg</p><p>$box_buttons</p></div>";
    }

    public function get_plugin_reset_box() {
        $box_text = __('Out-of-the-Box uses a cache to improve performance', 'outofthebox') . ". " . __('If the plugin somehow is causing issues, try to reset the cache first', 'outofthebox') . ".<br/>";

        $box_button = "<div id='resetDropbox_button' type='button' class='simple-button blue'/>" . __('Reset Cache', 'outofthebox') . "&nbsp;<div class='oftb-spinner'></div></div>";
        return "<div id='message'><p>$box_text</p><p>$box_button</p> </div>";
    }

    public function get_admin_notice() {

        if (version_compare(PHP_VERSION, '5.5.0') < 0) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>' . __('Out-of-the-Box <u>requires at least PHP 5.5</u> to be able to use the Dropbox API Library', 'outofthebox') . '. ' .
            __('You are using PHP version:', 'outofthebox') . ' <u><a href="https://secure.php.net/releases/#' . phpversion() . '" target="_blank" >' . phpversion() . '</a></u>.' .
            '<ul><li><strong>Please update your PHP version</strong> on your server to a recent PHP version of at least version 5.5, and if possible to version 7. Contact your webhost if you need help to update your PHP version.</li></ul>' .
            '</p></div>';
        } elseif (!function_exists('curl_reset')) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>' . __("You don't have the cURL PHP extension installed (couldn't find function \"curl_reset\"), please enable or install this extension", 'outofthebox') . '. ' .
            '</p></div>';
        } elseif (!file_exists(OUTOFTHEBOX_CACHEDIR) || !is_writable(OUTOFTHEBOX_CACHEDIR) || !file_exists(OUTOFTHEBOX_CACHEDIR . '/.htaccess')) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>' . sprintf(__('Cannot create the cache directory %s, or it is not writable', 'outofthebox'), '<code>' . OUTOFTHEBOX_CACHEDIR . '</code>') . '. ' .
            sprintf(__('Please check if the directory exists on your server and has %s writing permissions %s', 'outofthebox'), '<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">', '</a>') . '</p></div>';
        }
    }

    public function get_admin_notice_not_authorized() {
        global $pagenow;
        if ($pagenow == 'index.php' || $pagenow == 'plugins.php') {
            if (current_user_can('manage_options') || current_user_can('edit_theme_options')) {

                $processor = new Processor($this->get_main());
                $app = $processor->get_app();

                if ($app->has_access_token() === false) {
                    $location = get_admin_url(null, 'admin.php?page=OutoftheBox_settings');
                    echo '<div id="message" class="error"><p><strong>Out-of-the-Box: </strong>' . __('The plugin isn\'t autorized to use your Dropbox', 'outofthebox') . '. ' .
                    "<a href='$location' class='button-primary'>" . __('Authorize the plugin!', 'outofthebox') . '</a></p></div>';
                }
            }
        }
    }

    public function check_for_updates() {
        /* Updater */
        $purchasecode = false;

        $plugin = dirname(plugin_basename(__FILE__)) . '/out-of-the-box.php';
        if (is_multisite() && is_plugin_active_for_network($plugin)) {
            $purchasecode = get_site_option('outofthebox_purchaseid');
        } else {
            $purchasecode = $this->settings['purcasecode'];
        }

        if (!empty($purchasecode)) {
            require_once 'plugin-update-checker/plugin-update-checker.php';
            $updatechecker = \Puc_v4_Factory::buildUpdateChecker('https://www.wpcloudplugins.com/updates/?action=get_metadata&slug=out-of-the-box&purchase_code=' . $purchasecode . '&plugin_id=' . $this->plugin_id, plugin_dir_path(__DIR__) . '/out-of-the-box.php');
        }
    }

    public function get_system_information() {
        $check = array();

        array_push($check, array('success' => true, 'warning' => false, 'value' => __('WordPress version', 'outofthebox'), 'description' => get_bloginfo('version')));
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('Plugin version', 'outofthebox'), 'description' => OUTOFTHEBOX_VERSION));
        array_push($check, array('success' => true, 'warning' => false, 'value' => __('Memory limit', 'outofthebox'), 'description' => (ini_get('memory_limit'))));

        if (version_compare(PHP_VERSION, '5.5.0') < 0) {
            array_push($check, array('success' => false, 'warning' => false, 'value' => __('PHP version', 'outofthebox'), 'description' => phpversion() . ' ' . __('You need at least PHP 5.5 if you want to use Out-of-the-Box', 'outofthebox')));
        } else {
            array_push($check, array('success' => true, 'warning' => false, 'value' => __('PHP version', 'outofthebox'), 'description' => phpversion()));
        }

        //Check if we can use CURL
        if (function_exists('curl_init')) {
            array_push($check, array('success' => true, 'warning' => false, 'value' => __('cURL PHP extension', 'outofthebox'), 'description' => __('You have the cURL PHP extension installed', 'outofthebox')));
        } else {
            array_push($check, array('success' => false, 'warning' => false, 'value' => __('cURL PHP extension', 'outofthebox'), 'description' => __("You don't have the cURL PHP extension installed (couldn't find function \"curl_init\"), please enable or install this extension", 'outofthebox')));
        }

        //Check if cache dir is writeable
        if (!file_exists(OUTOFTHEBOX_CACHEDIR)) {
            @mkdir(OUTOFTHEBOX_CACHEDIR, 0755);
        }

        if (!is_writable(OUTOFTHEBOX_CACHEDIR)) {
            @chmod(OUTOFTHEBOX_CACHEDIR, 0755);

            if (!is_writable(OUTOFTHEBOX_CACHEDIR)) {
                array_push($check, array('success' => false, 'warning' => false, 'value' => __('Is CACHE directory writable?', 'outofthebox'), 'description' => __('CACHE directory', 'outofthebox') . ' ' . OUTOFTHEBOX_CACHEDIR . __('isn\'t writable. The plugin will load very slowly.', 'outofthebox') . ' ' . __('Make sure CACHE directory is writable', 'outofthebox')));
            } else {
                array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE directory writable?', 'outofthebox'), 'description' => __('CACHE directory is now writable', 'outofthebox')));
            }
        } else {
            array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE directory writable?', 'outofthebox'), 'description' => __('CACHE directory is writable', 'outofthebox')));
        }

        //Check if cache index-file is writeable
        if (!is_readable(OUTOFTHEBOX_CACHEDIR . 'index')) {
            @file_put_contents(OUTOFTHEBOX_CACHEDIR . 'index', json_encode(array()));

            if (!is_readable(OUTOFTHEBOX_CACHEDIR . 'index')) {
                array_push($check, array('success' => false, 'warning' => false, 'value' => __('Is CACHE-index file writable?', 'outofthebox'), 'description' => __('-index file', 'outofthebox') . ' ' . OUTOFTHEBOX_CACHEDIR . 'index' . '\' ' . __('isn\'t writable. The plugin will load very slowly.', 'outofthebox') . ' ' . __('Make sure CACHE-index file is writable', 'outofthebox')));
            } else {
                array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE-index file writable?', 'outofthebox'), 'description' => __('CACHE-index file is now writable', 'outofthebox')));
            }
        } else {
            array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is CACHE-index file writable?', 'outofthebox'), 'description' => __('CACHE-index file is writable', 'outofthebox')));
        }

        //Check if cache dir is writeable
        if (!file_exists(OUTOFTHEBOX_CACHEDIR . 'thumbnails')) {
            mkdir(OUTOFTHEBOX_CACHEDIR . 'thumbnails', 0755);
        }

        if (!is_writable(OUTOFTHEBOX_CACHEDIR . 'thumbnails')) {
            @chmod(OUTOFTHEBOX_CACHEDIR . 'thumbnails', 0755);

            if (!is_writable(OUTOFTHEBOX_CACHEDIR . 'thumbnails')) {
                array_push($check, array('success' => false, 'warning' => false, 'value' => __('Is THUMBNAIL directory writable?', 'outofthebox'), 'description' => __('THUMBNAIL directory', 'outofthebox') . ' ' . OUTOFTHEBOX_CACHEDIR . 'thumbnails ' . __('isn\'t writable. The gallery will load very slowly.', 'outofthebox') . ' ' . __('Make sure THUMBNAIL directory is writable', 'outofthebox')));
            } else {
                array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is THUMBNAIL directory writable?', 'outofthebox'), 'description' => __('THUMBNAIL directory is now writable', 'outofthebox')));
            }
        } else {
            array_push($check, array('success' => true, 'warning' => false, 'value' => __('Is THUMBNAIL directory writable?', 'outofthebox'), 'description' => __('THUMBNAIL directory is writable', 'outofthebox')));
        }

        // Supported images
        $mime_types = array('image/jpeg', 'image/png', 'image/bmp', 'image/gif');
        $supported = '';
        $success = true;

        foreach ($mime_types as $mime_type) {
            $arg = array('mime_type' => $mime_type, 'methods' => array('resize', 'save'));
            $img_editor_test = false;

            if (function_exists('wp_image_editor_supports')) {
                $img_editor_test = wp_image_editor_supports($arg);
            }

            if ($img_editor_test === true) {
                $success = false;
            }

            $supported .= $mime_type . ': ' . (($img_editor_test === true) ? 'Yes' : 'No') . '<br/>';
        }

        array_push($check, array('success' => $success, 'warning' => true, 'value' => __('Can resize the following images', 'outofthebox'), 'description' => $supported . '<br/>' . __("If your server doesn't support resizing an image type, we try to use Dropbox own thumbnails", 'outofthebox')));

        //Check if we can use ZIP class
        if (class_exists('ZipArchive')) {
            $message = __("You can use the ZIP function", 'outofthebox');
            array_push($check, array('success' => true, 'warning' => false, 'value' => __('Download files as ZIP', 'outofthebox'), 'description' => $message));
        } else {
            $message = __("You cannot download files as ZIP", 'outofthebox');
            array_push($check, array('success' => true, 'warning' => true, 'value' => __('Download files as ZIP', 'outofthebox'), 'description' => $message));
        }

        if (!extension_loaded('mbstring')) {
            array_push($check, array('success' => false, 'warning' => false, 'value' => __('mbstring extension enabled?', 'outofthebox'), 'description' => __('The required mbstring extension is not enabled on your server. Please enable this extension.', 'outofthebox')));
        }

        /* Check if Gravity Forms is installed and can be used */
        if (class_exists("GFForms")) {
            $is_correct_version = false;
            if (class_exists('GFCommon')) {
                $is_correct_version = version_compare(\GFCommon::$version, '1.9', '>=');
            }
            if ($is_correct_version) {
                $message = __("You can use Out-of-the-Box in Gravity Forms (" . \GFCommon::$version . ")", 'outofthebox');
                array_push($check, array('success' => true, 'warning' => false, 'value' => __('Gravity Forms integration', 'outofthebox'), 'description' => $message));
            } else {
                $message = __("You have Gravity Forms (" . \GFCommon::$version . ") installed, but versions before 1.9 are not supported. Please update Gravity Forms if you want to use this plugin in combination with Gravity Forms", 'outofthebox');
                array_push($check, array('success' => false, 'warning' => true, 'value' => __('Gravity Forms integration', 'outofthebox'), 'description' => $message));
            }
        }

        if (class_exists("WC_Integration")) {

            global $woocommerce;
            $is_correct_version = (is_object($woocommerce) ? version_compare($woocommerce->version, '3.0', '>=') : false);

            if ($is_correct_version) {
                $message = __("You can use Out-of-the-Box in WooCommerce (" . $woocommerce->version . ") for your Digital Products ", 'outofthebox') . '<br/><br/> ';
                array_push($check, array('success' => true, 'warning' => false, 'value' => __('WooCommerce Digital Products', 'outofthebox'), 'description' => $message));
            } else {
                $message = __("You have WooCommerce (" . $woocommerce->version . ") installed, but versions before 3.0 are not supported. Please update WooCommerce if you want to use this plugin in combination with WooCommerce", 'outofthebox');
                array_push($check, array('success' => false, 'warning' => true, 'value' => __('WooCommerce Digital Products', 'outofthebox'), 'description' => $message));
            }
        }

        // Create Table
        $html = '<table border="0" cellspacing="0" cellpadding="0">';

        foreach ($check as $row) {

            $color = ($row['success']) ? 'green' : 'red';
            $color = ($row['warning']) ? 'orange' : $color;

            $html .= '<tr style="vertical-align:top;"><td width="200" style="padding: 5px; color:' . $color . '"><strong>' . $row['value'] . '</strong></td><td style="padding: 5px;">' . $row['description'] . '</td></tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /*
     * Add MCE buttons and script
     */

    public function load_shortcode_buttons() {


        /* Abort early if the user will never see TinyMCE */
        if (
                !(\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) &&
                !(\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_links'])) &&
                !(\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_embedded']))
        ) {
            return;
        }

        if (get_user_option('rich_editing') !== 'true')
            return;

        //Add a callback to regiser our tinymce plugin
        add_filter("mce_external_plugins", array(&$this, "register_tinymce_plugin"));

        // Add a callback to add our button to the TinyMCE toolbar
        add_filter('mce_buttons', array(&$this, 'register_tinymce_plugin_buttons'));

        /* Add custom CSs for placeholders */
        add_editor_style(OUTOFTHEBOX_ROOTPATH . '/css/outofthebox_tinymce_editor.css');
    }

    //This callback registers our plug-in
    function register_tinymce_plugin($plugin_array) {
        $plugin_array['outofthebox'] = OUTOFTHEBOX_ROOTPATH . "/includes/js/Tinymce.js";
        return $plugin_array;
    }

    //This callback adds our button to the toolbar
    function register_tinymce_plugin_buttons($buttons) {
        //Add the button ID to the $button array

        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_shortcodes'])) {
            $buttons[] = "outofthebox";
        }
        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_links'])) {
            $buttons[] = "outofthebox_links";
        }
        if (\TheLion\OutoftheBox\Helpers::check_user_role($this->settings['permissions_add_embedded'])) {
            $buttons[] = "outofthebox_embedded";
        }

        return $buttons;
    }

}
