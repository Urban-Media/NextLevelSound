<?php

namespace TheLion\OutoftheBox;

use \Kunnu\Dropbox\Dropbox;
use \Kunnu\Dropbox\DropboxApp;

class App {

    /**
     * 
     * @var string 
     */
    private $_access_token = null;

    /**
     *
     * @var bool 
     */
    private $_own_app = false;

    /**
     *
     * @var string 
     */
    private $_app_key = 'm3n3zyvyr59cdjb';

    /**
     *
     * @var string 
     */
    private $_app_secret = 'eu73x5upk7ehes4';

    /**
     *
     * @var string 
     */
    private $_identifier;

    /**
     * 
     * @var \Kunnu\Dropbox\Dropbox
     */
    private $_client = null;

    /**
     * 
     * @var \Kunnu\Dropbox\DropboxApp
     */
    private $_client_app = null;

    /**

     * We don't save your data or share it. 
     * This script just simply creates a redirect with your id and secret to Dropbox and returns the created token.
     * It is exactly the same script as the _authorizeApp.php file in the includes folder of the plugin, 
     * and is used for an easy and one-click authorization process that will always work!
     * 
     * @var string 
     */
    private $_redirect_uri = 'https://www.wpcloudplugins.com:443/out-of-the-box/_AuthorizeApp.php';

    /**
     *
     * @var \TheLion\OutoftheBox\Processor 
     */
    private $_processor;

    public function __construct(Processor $processor) {
        $this->_processor = $processor;
        require_once OUTOFTHEBOX_ROOTDIR . '/includes/dropbox-sdk/vendor/autoload.php';

        $own_key = $this->get_processor()->get_setting('dropbox_app_key');
        $own_secret = $this->get_processor()->get_setting('dropbox_app_secret');

        if (
                (!empty($own_key)) &&
                (!empty($own_secret))
        ) {
            $this->_app_key = $this->get_processor()->get_setting('dropbox_app_key');
            $this->_app_secret = $this->get_processor()->get_setting('dropbox_app_secret');
            $this->_own_app = true;
        }

        /* Set right redirect URL */
        $this->set_redirect_uri();

        /* Process codes/tokens if needed */
        $this->process_authorization();

        $this->_access_token = $this->get_processor()->get_setting('dropbox_app_token');
    }

    public function process_authorization() {

        if (isset($_REQUEST['action']) && $_REQUEST['action'] !== 'outofthebox_authorization') {
            return false;
        } elseif (!empty($_REQUEST['state'])) {
            $state = (strtr($_REQUEST['state'], '-_~', '+/='));

            $csrfToken = $state;
            $urlState = null;

            $splitPos = strpos($state, "|");

            if ($splitPos !== false) {
                $csrfToken = substr($state, 0, $splitPos);
                $urlState = substr($state, $splitPos + 1);
            }
            $redirectto = base64_decode($urlState);

            if (strpos($redirectto, 'outofthebox_authorization') === false) {
                return false;
            }
        } else {
            return false;
        }


        if (isset($_GET['code'])) {
            $access_token = $this->create_access_token();
            /** Echo To Popup */
            echo '<script type="text/javascript">window.opener.parent.location.href = "' . admin_url('admin.php?page=OutoftheBox_settings') . '"; window.close();</script>';
            die();
        } elseif (isset($_GET['_token'])) {
            $new_access_token = $_GET['_token'];
            $access_token = $this->set_access_token($new_access_token);

            /** Echo To Popup */
            echo '<script type="text/javascript">window.opener.parent.location.href = "' . admin_url('admin.php?page=OutoftheBox_settings') . '"; window.close();</script>';
            die();
        }



        return false;
    }

    public function can_do_own_auth() {
        $blog_url = parse_url(admin_url());
        return ($blog_url['scheme'] === 'https' || $blog_url['host'] === 'localhost');
    }

    public function has_plugin_own_app() {
        return $this->_own_app;
    }

    public function get_auth_url() {
        $auth_helper = $this->get_client()->getAuthHelper();
        $encodedredirect = strtr(base64_encode(admin_url('admin.php?page=OutoftheBox_settings&action=outofthebox_authorization')), '+/=', '-_~');

        return $auth_helper->getAuthUrl($this->get_redirect_uri(), array(), $encodedredirect);
    }

    public function start_client() {
        
    }

    public function create_access_token() {
        try {
            $code = $_REQUEST['code'];
            $state = $_REQUEST['state'];

            //Fetch the AccessToken
            $accessToken = $this->get_client()->getAuthHelper()->getAccessToken($code, $state, $this->get_redirect_uri());
            $this->set_access_token($accessToken->getToken());
        } catch (\Exception $ex) {
            return new \WP_Error('broke', __("error communicating with Dropbox API: ", 'outofthebox') . $ex->getMessage());
        }

        return true;
    }

    public function revoke_token() {
        try {
            $this->get_client()->getAuthHelper()->revokeAccessToken();
        } catch (\Exception $ex) {
            
        }

        $this->get_processor()->set_setting('userfolder_backend_auto_root', null);
        $this->set_access_token(null);
    }

    public function get_app_key() {
        return $this->_app_key;
    }

    public function get_app_secret() {
        return $this->_app_secret;
    }

    public function set_app_key($_app_key) {
        $this->_app_key = $_app_key;
    }

    public function set_app_secret($_app_secret) {
        $this->_app_secret = $_app_secret;
    }

    public function get_access_token() {
        return $this->_access_token;
    }

    public function set_access_token($_access_token) {
        $this->_access_token = $_access_token;
        $this->get_processor()->get_cache()->reset_cache();
        return $this->get_processor()->set_setting('dropbox_app_token', $_access_token);
    }

    public function has_access_token() {
        return (!empty($this->_access_token));
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Processor
     */
    public function get_processor() {
        return $this->_processor;
    }

    /**
     * 
     * @return \Kunnu\Dropbox\Dropbox
     */
    public function get_client() {
        if (empty($this->_client)) {
            $this->_client = new Dropbox($this->get_client_app(), array('persistent_data_store' => new \Kunnu\Dropbox\Store\DatabasePersistentDataStore()));
        }
        return $this->_client;
    }

    /**
     * 
     * @return \Kunnu\Dropbox\DropboxApp
     */
    public function get_client_app() {
        if (empty($this->_client_app)) {
            $this->_client_app = new DropboxApp($this->get_app_key(), $this->get_app_secret(), $this->get_access_token());
        }

        return $this->_client_app;
    }

    public function get_redirect_uri() {
        return $this->_redirect_uri;
    }

    public function set_redirect_uri() {
        if ($this->can_do_own_auth() && $this->has_plugin_own_app()) {
            $this->_redirect_uri = admin_url('admin.php?page=OutoftheBox_settings');
        }
    }

}
