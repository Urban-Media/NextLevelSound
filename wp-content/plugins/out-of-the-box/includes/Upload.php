<?php

namespace TheLion\OutoftheBox;

class Upload {

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
     * @var UploadHandler 
     */
    private $_upload_handler;

    public function __construct(\TheLion\OutoftheBox\Processor $_processor = null) {
        $this->_client = $_processor->get_client();
        $this->_processor = $_processor;

        /* Upload File to server */
        if (!class_exists('UploadHandler')) {
            require('jquery-file-upload/server/UploadHandler.php');
        }
    }

    public function do_upload() {

        if ($this->get_processor()->get_shortcode_option('demo') === '1') {
            /* TO DO LOG + FAIL ERROR */
            die(-1);
        }

        $shortcode_max_file_size = $this->get_processor()->get_shortcode_option('maxfilesize');
        $accept_file_types = '/.(' . $this->get_processor()->get_shortcode_option('upload_ext') . ')$/i';
        $post_max_size_bytes = min(Helpers::return_bytes(ini_get('post_max_size')), Helpers::return_bytes(ini_get('upload_max_filesize')));
        $max_file_size = ($shortcode_max_file_size !== '0') ? Helpers::return_bytes($shortcode_max_file_size) : $post_max_size_bytes;

        $options = array(
            'access_control_allow_methods' => array('POST', 'PUT'),
            'accept_file_types' => $accept_file_types,
            'inline_file_types' => '/\.____$/i',
            'orient_image' => false,
            'image_versions' => array(),
            'max_file_size' => $max_file_size,
            'print_response' => false
        );

        $error_messages = array(
            1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'outofthebox'),
            2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'outofthebox'),
            3 => __('The uploaded file was only partially uploaded', 'outofthebox'),
            4 => __('No file was uploaded', 'outofthebox'),
            6 => __('Missing a temporary folder', 'outofthebox'),
            7 => __('Failed to write file to disk', 'outofthebox'),
            8 => __('A PHP extension stopped the file upload', 'outofthebox'),
            'post_max_size' => __('The uploaded file exceeds the post_max_size directive in php.ini', 'outofthebox'),
            'max_file_size' => __('File is too big', 'outofthebox'),
            'min_file_size' => __('File is too small', 'outofthebox'),
            'accept_file_types' => __('Filetype not allowed', 'outofthebox'),
            'max_number_of_files' => __('Maximum number of files exceeded', 'outofthebox'),
            'max_width' => __('Image exceeds maximum width', 'outofthebox'),
            'min_width' => __('Image requires a minimum width', 'outofthebox'),
            'max_height' => __('Image exceeds maximum height', 'outofthebox'),
            'min_height' => __('Image requires a minimum height', 'outofthebox')
        );

        $this->upload_handler = new \UploadHandler($options, false, $error_messages);
        $response = $this->upload_handler->post(false);
        $hash = $_REQUEST['hash'];

        /* Upload files to Dropbox */
        foreach ($response['files'] as &$file) {
            /* Set return Object */
            $file->listtoken = $this->get_processor()->get_listtoken();
            $file->hash = $hash;

            if (!isset($file->error)) {
                $return = array('file' => $file, 'status' => array('bytes_down_so_far' => 0, 'total_bytes_down_expected' => $file->size, 'percentage' => 0, 'progress' => 'starting'));
                self::set_upload_progress($hash, $return);


                /** Check if the user hasn't reached its usage limit */
                $max_user_folder_size = $this->get_processor()->get_shortcode_option('max_user_folder_size');
                if ($this->get_processor()->get_shortcode_option('user_upload_folders') !== '0' && $max_user_folder_size !== '-1') {
                    $disk_usage_after_upload = $this->get_client()->get_folder_size() + $file->size;
                    $max_allowed_bytes = Helpers::return_bytes($max_user_folder_size);
                    if ($disk_usage_after_upload > $max_allowed_bytes) {
                        $return['status']['progress'] = 'failed';
                        $file->error = __('You have reached your usage limit of', 'outofthebox') . ' ' . Helpers::bytes_to_size_1024($max_allowed_bytes);
                        self::set_upload_progress($hash, $return);
                        echo json_encode($return);
                        die();
                    }
                }


                /* Check if file already exists */
                $filename = apply_filters('outofthebox_upload_file_name', $file->name);
                $new_file_path = Helpers::clean_folder_path($this->get_processor()->get_requested_complete_path() . '/' . $filename);
                $new_file_path = apply_filters('outofthebox_upload_file_path', $new_file_path);

                $entry_if_exists = $this->get_client()->get_entry($new_file_path);

                $file_rev = false;
                if (!empty($entry_if_exists)) {
                    $file_rev = $entry_if_exists->get_rev();
                }

                /* Add or update file? */
                $params = array('mode' => 'add', 'autorename' => true);

                if ($this->get_processor()->get_shortcode_option('overwrite') === '1' && !empty($file_rev)) {
                    $params = array('mode' => 'overwrite', 'autorename' => false);
                };

                /* Write file */
                $temp_file_path = $file->tmp_path;
                try {
                    $entry = $this->do_upload_to_dropbox($temp_file_path, $new_file_path, $params);
                    $file->completepath = $this->get_processor()->get_relative_path($entry->get_path_display());
                    $file->fileid = $entry->get_id();
                    $file->filesize = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($entry->get_size());
                    $file->link = false; // Currently no direct link available
                } catch (\Exception $ex) {
                    error_log($ex->getMessage());
                    $file->error = __('Not uploaded to Dropbox', 'outofthebox') . $ex->getMessage();
                }

                $return['status']['progress'] = 'finished';
                $return['status']['percentage'] = '100';

                $cached_request = new CacheRequest($this->get_processor());
                $cached_request->clear_local_cache_for_shortcode();
            } else {
                error_log($file->error);
                $return['status']['progress'] = 'failed';
                $file->error = __('Uploading failed', 'outofthebox');
            }
        }

        $return['file'] = $file;
        self::set_upload_progress($hash, $return);

        /* Create response */
        echo json_encode($return);
        die();
    }

    public function do_upload_to_dropbox($temp_file_path, $new_file_path, $params = array()) {
        return $this->get_client()->upload_file($temp_file_path, $new_file_path, $params);
    }

    static public function get_upload_progress($file_hash) {
        return get_transient('outofthebox_upload_' . substr($file_hash, 0, 40));
    }

    static public function set_upload_progress($file_hash, $status) {
        /* Update progress */
        return set_transient('outofthebox_upload_' . substr($file_hash, 0, 40), $status, HOUR_IN_SECONDS);
    }

    public function get_upload_status() {
        $hash = $_REQUEST['hash'];

        /* Try to get the upload status of the file */
        for ($_try = 1; $_try < 6; $_try++) {
            $result = self::get_upload_progress($hash);

            if ($result !== false) {

                if ($result['status']['progress'] === 'failed' || $result['status']['progress'] === 'finished') {
                    delete_transient('outofthebox_upload_' . substr($hash, 0, 40));
                }

                break;
            }

            /* Wait a moment, perhaps the upload still needs to start */
            usleep(500000 * $_try);
        }

        if ($result === false) {
            $result = array('file' => false, 'status' => array('bytes_down_so_far' => 0, 'total_bytes_down_expected' => 0, 'percentage' => 0, 'progress' => 'failed'));
        }

        echo json_encode($result);
        die();
    }

    public function upload_post_process() {
        if ((!isset($_REQUEST['files'])) || count($_REQUEST['files']) === 0) {
            echo json_encode(array('result' => 0));
            die();
        }

        $uploaded_files = $_REQUEST['files'];
        $_uploaded_entries = array();

        foreach ($uploaded_files as $file_id) {

            try {
                $api_entry = $this->get_client()->get_library()->getMetadata($file_id);
                $entry = new Entry($api_entry);
            } catch (\Exception $ex) {
                continue;
            }

            if ($entry === false) {
                continue;
            }

            /* Upload Hook */
            do_action('outofthebox_upload', $entry);
            $_uploaded_entries[] = $entry;
        }

        /* Send email if needed */
        if (count($_uploaded_entries) > 0) {
            if ($this->get_processor()->get_shortcode_option('notificationupload') === '1') {
                $this->get_processor()->send_notification_email('upload', $_uploaded_entries);
            }
        }

        /* Return information of the files */
        $files = array();
        foreach ($_uploaded_entries as $entry) {

            $relative_path_display = $this->get_processor()->get_relative_path($entry->get_path_display());
            $entry->set_path_display($relative_path_display);

            $link = ($this->get_client()->has_shared_link($entry) ) ? $this->get_client()->get_shared_link($entry) . '?dl=0' : admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($entry->get_path_display()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken();

            $file = array();
            $file['name'] = $entry->get_name();
            $file['completepath'] = $entry->get_path_display();
            $file['fileid'] = $entry->get_id();
            $file['filesize'] = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($entry->get_size());
            $file['link'] = $link;

            $files[$file['fileid']] = $file;
        }

        echo json_encode(array('result' => 1, 'files' => $files));
    }

    public function get_processor() {
        return $this->_processor;
    }

    public function get_client() {
        return $this->_client;
    }

}