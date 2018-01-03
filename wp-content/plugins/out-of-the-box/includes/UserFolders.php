<?php

namespace TheLion\OutoftheBox;

class UserFolders {

    /**
     *
     * @var \TheLion\OutoftheBox\Client 
     */
    private $_client;

    /**
     *
     * @var \TheLion\OutoftheBox\Processor 
     */
    private $_processor;

    /**
     *
     * @var string 
     */
    private $_user_name_template;
    private $_user_folder_name;

    public function __construct(\TheLion\OutoftheBox\Processor $_processor = null) {
        $this->_client = $_processor->get_client();
        $this->_processor = $_processor;
        $this->_user_name_template = $this->get_processor()->get_setting('userfolder_name');
    }

    public function get_auto_linked_folder_name_for_user() {
        $shortcode = $this->get_processor()->get_shortcode();
        if (!isset($shortcode['user_upload_folders']) || $shortcode['user_upload_folders'] !== 'auto') {
            return false;
        }

        if (!empty($this->_user_folder_name)) {
            return $this->_user_folder_name;
        }

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $userfoldername = $this->get_user_name_template($current_user);
        } else {
            $userfoldername = $this->get_guest_user_name();
        }

        $this->_user_folder_name = $userfoldername;

        return $userfoldername;
    }

    public function get_auto_linked_folder_for_user() {

        /* Add folder if needed */
        $result = $this->create_user_folder($this->get_auto_linked_folder_name_for_user(), $this->get_processor()->get_shortcode(), 5000000);

        if ($result === false) {
            die();
        }

        return $result->get_path();
    }

    public function get_manually_linked_folder_for_user() {
        $userfolder = get_user_option('out_of_the_box_linkedto');
        if (is_array($userfolder) && isset($userfolder['foldertext'])) {
            return $userfolder['folderid'];
        } else {
            $defaultuserfolder = get_site_option('out_of_the_box_guestlinkedto');
            if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
                return $defaultuserfolder['folderid'];
            } else {
                die(-1);
            }
        }
    }

    public function manually_link_folder($user_id, $linkedto) {

        if ($user_id === 'GUEST') {
            $result = update_site_option('out_of_the_box_guestlinkedto', $linkedto);
        } else {
            $result = update_user_option($user_id, 'out_of_the_box_linkedto', $linkedto, false);
        }

        if ($result !== false) {
            die('1');
        }
    }

    public function manually_unlink_folder($user_id) {

        if ($user_id === 'GUEST') {
            $result = delete_site_option('out_of_the_box_guestlinkedto');
        } else {
            $result = delete_user_option($user_id, 'out_of_the_box_linkedto', false);
        }

        if ($result !== false) {
            die('1');
        }
    }

    public function create_user_folder($userfoldername, $shortcode, $mswaitaftercreation = 0) {

        if (strpos($shortcode['root'], '%user_folder%') !== false) {
            $userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $userfoldername, $shortcode['root']));
        } else {
            $userfolder_path = Helpers::clean_folder_path($shortcode['root'] . '/' . $userfoldername);
        }

        try {
            $api_entry = $this->get_client()->get_library()->getMetadata($userfolder_path);
            return new Entry($api_entry);
        } catch (\Exception $ex) {
            /* Folder doesn't exists, so continue */
        }

        $user_template_path = $shortcode['user_template_dir'];

        try {
            if (empty($user_template_path)) {
                $api_entry_new = $this->get_client()->get_library()->createFolder($userfolder_path);
            } else {
                $api_entry_new = $this->get_client()->get_library()->copy($user_template_path, $userfolder_path);

                /* New Meta data isn't fully available directly after copy command */
                usleep($mswaitaftercreation);
            }
        } catch (\Exception $ex) {
            return false;
        }

        return new Entry($api_entry_new);
    }

    public function create_user_folders_for_shortcodes($user_id) {
        $new_user = get_user_by('id', $user_id);
        $new_userfoldersname = $this->get_user_name_template($new_user);

        $outoftheboxlists = get_option('out_of_the_box_lists', array());

        foreach ($outoftheboxlists as $list) {

            if (!isset($list['user_upload_folders']) || $list['user_upload_folders'] !== 'auto') {
                continue;
            }

            $this->create_user_folder($new_userfoldersname, $list);
        }
    }

    public function create_user_folders($users = array()) {

        if (count($users) === 0) {
            return;
        }

        foreach ($users as $user) {
            $userfoldersname = $this->get_user_name_template($user);
            $this->create_user_folder($userfoldersname, $this->get_processor()->get_shortcode());
        }
    }

    public function remove_user_folder($user_id) {

        $deleted_user = get_user_by('id', $user_id);
        $userfoldername = $this->get_user_name_template($deleted_user);

        $outoftheboxlists = get_option('out_of_the_box_lists', array());

        foreach ($outoftheboxlists as $list) {

            if (!isset($list['user_upload_folders']) || $list['user_upload_folders'] !== 'auto') {
                continue;
            }
            if (strpos($list['root'], '%user_folder%') !== false) {
                $userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $userfoldername, $list['root']));
            } else {
                $userfolder_path = Helpers::clean_folder_path($list['root'] . '/' . $userfoldername);
            }

            try {
                $api_entry_deleted = $this->get_client()->get_library()->delete($userfolder_path);
            } catch (\Exception $ex) {
                return false;
            }
        }
        return true;
    }

    public function update_user_folder($user_id, $old_user) {


        $updated_user = get_user_by('id', $user_id);
        $new_userfoldersname = $this->get_user_name_template($updated_user);

        $old_userfoldersname = $this->get_user_name_template($old_user);

        if ($new_userfoldersname === $old_userfoldersname) {
            return false;
        }

        $outoftheboxlists = get_option('out_of_the_box_lists', array());

        foreach ($outoftheboxlists as $list) {

            if (!isset($list['user_upload_folders']) || $list['user_upload_folders'] !== 'auto') {
                continue;
            }

            if (strpos($list['root'], '%user_folder%') !== false) {
                $new_userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $new_userfoldersname, $list['root']));
                $old_userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $old_userfoldersname, $list['root']));
            } else {
                $new_userfolder_path = Helpers::clean_folder_path($list['root'] . '/' . $new_userfoldersname);
                $old_userfolder_path = Helpers::clean_folder_path($list['root'] . '/' . $old_userfoldersname);
            }

            try {
                $api_entry_move = $this->get_client()->get_library()->move($old_userfolder_path, $new_userfolder_path);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return true;
    }

    public function get_user_name_template($user_data) {

        return strtr($this->_user_name_template, array(
            "%user_login%" => isset($user_data->user_login) ? $user_data->user_login : '',
            "%user_email%" => isset($user_data->user_email) ? $user_data->user_email : '',
            "%user_firstname%" => isset($user_data->user_firstname) ? $user_data->user_firstname : '',
            "%user_lastname%" => isset($user_data->user_lastname) ? $user_data->user_lastname : '',
            "%display_name%" => isset($user_data->display_name) ? $user_data->display_name : '',
            "%ID%" => isset($user_data->ID) ? $user_data->ID : '',
            "%user_role%" => isset($user_data->roles) ? implode(',', $user_data->roles) : '',
            "%jjjj-mm-dd%" => date('Y-m-d')
        ));
    }

    public function get_guest_user_name() {
        $username = $this->get_guest_id();

        $current_user = new \stdClass();
        $current_user->user_login = md5($username);
        $current_user->display_name = $username;
        $current_user->ID = $username;
        $current_user->user_role = __('Guest', 'outofthebox');

        return __('Guests', 'outofthebox') . ' - ' . $this->get_user_name_template($current_user);
    }

    public function get_guest_id() {
        $id = uniqid();
        if (!isset($_COOKIE['OftB-ID'])) {
            $expire = time() + 60 * 60 * 24 * 7;
            @setcookie('OftB-ID', $id, $expire, '/');
        } else {
            $id = $_COOKIE['OftB-ID'];
        }

        return $id;
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
     * @return \TheLion\OutoftheBox\Client
     */
    public function get_client() {
        return $this->get_processor()->get_client();
    }

}
