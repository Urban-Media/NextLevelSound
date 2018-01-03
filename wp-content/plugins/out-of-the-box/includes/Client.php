<?php

namespace TheLion\OutoftheBox;

class Client {

    /**
     *
     * @var \TheLion\OutoftheBox\App
     */
    private $_app;

    /**
     *
     * @var \Kunnu\Dropbox\Dropbox 
     */
    private $_client;

    /**
     *
     * @var \TheLion\OutoftheBox\Processor
     */
    private $_processor;

    public function __construct(App $_app, Processor $_processor = null) {
        $this->_app = $_app;
        $this->_client = $_app->get_client();
        $this->_processor = $_processor;
    }

    public function get_account_info() {
        return $this->_client->getCurrentAccount();
    }

    public function get_account_space_info() {
        return $this->_client->getSpaceUsage();
    }

    public function get_entry($requested_path = null, $check_if_allowed = true) {
        if ($requested_path === null) {
            $requested_path = $this->get_processor()->get_requested_complete_path();
        }

        /* Clean path if needed */
        if (strpos($requested_path, '/') !== false) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        /* Get entry meta data (no meta data for root folder_ */
        if ($requested_path === '/' || $requested_path === '') {
            $entry = new Entry();
            $entry->set_id('Root');
            $entry->set_name('Root');
            $entry->set_path('');
            $entry->set_is_dir(true);
        } else {

            try {
                $api_entry = $this->_client->getMetadata($requested_path, array("include_media_info" => true));
                $entry = new Entry($api_entry);
            } catch (\Exception $ex) {
                /* TO DO LOG */
                return false;
            }
        }

        if ($check_if_allowed && !$this->get_processor()->_is_entry_authorized($entry)) {
            die('-1');
        }

        return $entry;
    }

    public function get_multiple_entries($entries = array()) {
        $dropbox_entries = array();
        foreach ($entries as $entry) {
            $dropbox_entry = $this->get_entry($entry, false);
            if (!empty($dropbox_entry)) {
                $dropbox_entries[] = $dropbox_entry;
            }
        }

        return $dropbox_entries;
    }

    /**
     * 
     * @param string $requested_path
     * @param bool $check_if_allowed
     * @return boolean|\TheLion\OutoftheBox\Entry
     */
    public function get_folder($requested_path = null, $check_if_allowed = true, $get_media_info = false, $recursive = false, $hierarchical = true) {
        if ($requested_path === null) {
            $requested_path = $this->get_processor()->get_requested_complete_path();
        }


        /* Clean path if needed */
        if (strpos($requested_path, '/') !== false) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        $folder = null;
        $children = array();

        /* Get folder children */
        try {
            $api_folders_contents = $this->_client->listFolder($requested_path, array("include_media_info" => $get_media_info, 'recursive' => $recursive));
            $api_entries = $api_folders_contents->getItems()->toArray();

            while ($api_folders_contents->hasMoreItems()) {
                $cursor = $api_folders_contents->getCursor();
                $api_folders_contents = $this->_client->listFolderContinue($cursor);
                $api_entries = array_merge($api_entries, $api_folders_contents->getItems()->toArray());
            }
        } catch (\Exception $ex) {
            /* TO DO LOG */
            die('-1');
        }


        if (count($api_entries) > 0) {
            foreach ($api_entries as $api_entry) {
                $entry = new Entry($api_entry);

                if ($check_if_allowed && $this->get_processor()->_is_entry_authorized($entry)) {
                    $relative_path = $this->get_processor()->get_relative_path($entry->get_path());
                    $entry->set_path($relative_path);
                    $relative_path_display = $this->get_processor()->get_relative_path($entry->get_path_display());
                    $entry->set_path_display($relative_path_display);
                    $children[$entry->get_id()] = $entry;
                } else {
                    
                }
            }
        }

        /* Sort contents */
        if (count($children) > 0) {
            $children = $this->get_processor()->sort_filelist($children);
        }

        /* Make a hierarchical structure if a recursive reponse is requested */
        if ($recursive && $hierarchical) {
            foreach ($children as $id => $child) {

                $relative_path = $this->get_processor()->get_relative_path($child->get_parent());
                $parent_id = Helpers::find_item_in_array_with_value($children, 'path', $relative_path);

                if ($parent_id === false || $parent_id === $child->get_id()) {
                    $child->flag = false;
                    continue;
                }

                $parent = $children[$parent_id];
                $parent_childs = $parent->get_children();
                $parent_childs[$child->get_id()] = $child;
                $parent->set_children($parent_childs);

                $child->flag = true;
            }

            foreach ($children as $id => $child) {
                if ($child->flag) {
                    unset($children[$id]);
                }
            }
        }

        /* Get folder meta data (no meta data for root folder_ */
        if ($requested_path === '' || !$recursive || !$hierarchical) {
            $folder_entry = new Entry();
            $folder_entry->set_path($requested_path);
            $folder_entry->set_is_dir(true);
            $folder_entry->set_children($children);
        } else {
            $folder_entry = reset($children);
        }

        return $folder_entry;
    }

    public function search($search_query) {
        $found_entries = array();

        /* Get requested path */
        $requested_path = $this->get_processor()->get_requested_complete_path();

        /* Set Search settings */
        $folder_to_search_in = ($this->get_processor()->get_shortcode_option('searchfrom') === 'parent') ? $requested_path : $this->get_processor()->get_root_folder();
        $search_for = ($this->get_processor()->get_shortcode_option('search_contents') === '1') ? 'filename_and_content' : 'filename';

        /* Get Results */
        try {
            $api_search_result = $this->_client->search($folder_to_search_in, $search_query, array('mode' => $search_for, 'max_results' => 1000));
            $api_entries = $api_search_result->getItems()->toArray();

            while ($api_search_result->hasMoreItems()) {
                $cursor = $api_search_result->getCursor();
                $api_search_result = $this->_client->search($folder_to_search_in, $search_query, array('mode' => $search_for, 'start' => $cursor, 'max_results' => 1000));
                $api_entries = array_merge($api_entries, $api_search_result->getItems()->toArray());
            }
        } catch (\Exception $ex) {
            /* TO DO LOG */
            die('-1');
        }

        /* Sort contents */
        if (count($api_entries) > 0) {
            foreach ($api_entries as $search_result) {
                $entry = new Entry($search_result->getMetadata());

                if ($this->get_processor()->_is_entry_authorized($entry)) {
                    $relative_path = $this->get_processor()->get_relative_path($entry->get_path());
                    $entry->set_path($relative_path);
                    $relative_path_display = $this->get_processor()->get_relative_path($entry->get_path_display());
                    $entry->set_path_display($relative_path_display);
                    $found_entries[$entry->get_id()] = $entry;
                }
            }
        }

        $folder = new Entry();
        $folder->set_path($this->get_processor()->get_relative_path($folder_to_search_in));
        $folder->set_is_dir(true);
        $folder->set_children($found_entries);
        return $folder;
    }

    public function get_folder_size($requested_path = null) {
        if ($requested_path === null) {
            $requested_path = $this->get_processor()->get_requested_complete_path();
        }


        /* Clean path if needed */
        if (strpos($requested_path, '/') !== false) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        $folder = null;
        $children = array();

        /* Get folder children */
        try {
            $api_folders_contents = $this->_client->listFolder($requested_path, array('recursive' => true));
            $api_entries = $api_folders_contents->getItems()->toArray();

            while ($api_folders_contents->hasMoreItems()) {
                $cursor = $api_folders_contents->getCursor();
                $api_folders_contents = $this->_client->listFolderContinue($cursor);
                $api_entries = array_merge($api_entries, $api_folders_contents->getItems()->toArray());
            }

            unset($api_folders_contents);
        } catch (\Exception $ex) {
            /* TO DO LOG */
            return null;
        }

        $total_size = 0;

        foreach ($api_entries as $api_entry) {
            $total_size += ($api_entry instanceof \Kunnu\Dropbox\Models\FolderMetadata) ? 0 : $api_entry->size;
        }

        unset($api_entries);
        return $total_size;
    }

    public function preview_entry() {
        /* Get file meta data */
        $entry = $this->get_entry();

        if ($entry === false) {
            die('-1');
        }

        if ($entry->get_can_preview_by_cloud() === false) {
            die('-1');
        }

        if ($this->get_processor()->get_user()->can_preview() === false) {
            die('-1');
        }

        /* Preview for Media files in HTML5 Player */
        if (in_array($entry->get_extension(), array('mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga'))) {
            if ($this->has_shared_link($entry)) {
                $temporarily_link = $this->get_shared_link($entry) . '?raw=1';
            } else {
                $temporarily_link = $this->get_temporarily_link($entry);
            }
            header('Location: ' . $temporarily_link);
            die();
        }

        /* Preview for Image files */
        if (in_array($entry->get_extension(), array('txt', 'pdf', 'jpg', 'jpeg', 'gif', 'png'))) {
            $shared_link = $this->get_shared_link($entry);
            header('Location: ' . $shared_link . '?raw=1');
            die();
        }

        /* Preview for PDF files */
        /* Preview for Excel files */
        if (in_array($entry->get_extension(), array('xls', 'xlsx', 'xlsm'))) {
            header('Content-Type: text/html');
        } else {
            header('Content-Disposition: inline; filename="' . $entry->get_basename() . '.pdf"');
            header('Content-Description: "' . $entry->get_basename() . '"');
            header('Content-Type: application/pdf');
        }

        try {
            $preview_file = $this->_client->preview($entry->get_path());
            echo $preview_file->getContents();
        } catch (\Exception $ex) {
            die('-1');
        }

        die();
    }

    public function download_entry() {
        /* Get file meta data */
        $entry = $this->get_entry();

        if ($entry === false) {
            die(-1);
        }

        /* TO DO Download notifications */
        if ($this->get_processor()->get_shortcode_option('notificationdownload') === '1') {
            $this->get_processor()->send_notification_email('download', array($entry));
        }

        /* Render file via browser */
        //if (in_array($entry->get_extension(), array('csv', 'html'))) {
        //    $download_file = $this->_client->download($entry->get_id());
        //    echo $download_file->getContents();
        //    die();
        //}

        $temporarily_link = $this->get_temporarily_link($entry);

        /* Download Hook */
        do_action('outofthebox_download', $entry, $temporarily_link);

        header('Location: ' . $temporarily_link);
        die();
    }

    public function stream_entry() {
        /* Get file meta data */
        $entry = $this->get_entry();

        if ($entry === false) {
            die(-1);
        }

        $extension = $entry->get_extension();
        $allowedextensions = array('mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga');

        if (empty($extension) || !in_array($extension, $allowedextensions)) {
            die();
        }

        $this->download_entry();
    }

    public function download_entries_as_zip() {

        if (isset($_REQUEST['files'])) {
            $requested_paths = $_REQUEST['files'];
        } else {
            $requested_paths = array($this->get_processor()->get_requested_complete_path());
        }

        /* Set Zip file name */
        $last_folder_path = $this->get_processor()->get_last_path();
        $zip_filename = '_zip_' . $this->get_processor()->get_relative_path($last_folder_path) . '_' . uniqid() . '.zip';

        $zip_filename = apply_filters('outofthebox_zip_filename', $zip_filename, $last_folder_path, $requested_paths);


        /* Load Zip Library */
        if (!function_exists('PHPZip\autoload')) {
            require_once "PHPZip/autoload.php";
        } else {
            die(-1);
        }

        /* Create Zip file */
        $zip = new \PHPZip\Zip\Stream\ZipStream(\TheLion\OutoftheBox\Helpers::filter_filename($zip_filename));

        /* Process all the files that need to be added to the zip file */
        $files_added_to_zip = array();
        foreach ($requested_paths as $requested_path) {
            if ($requested_path !== $this->get_processor()->get_requested_complete_path()) {
                $requested_path = $this->get_processor()->get_requested_complete_path() . '/' . rawurldecode($requested_path);
            }

            $entry = $this->get_entry($requested_path);

            if ($entry === false) {
                continue;
            }

            $entries_to_add = array();

            if ($entry->is_dir()) {
                $folder = $this->get_folder($entry->get_path(), true, false, true, false);

                if ($folder->has_children() === false) {
                    continue;
                }
                $entries_to_add = array_merge($entries_to_add, $folder->get_children());
            } else {
                $relative_path = $this->get_processor()->get_relative_path($entry->get_path());
                $entry->set_path($relative_path);
                $relative_path_display = $this->get_processor()->get_relative_path($entry->get_path_display());
                $entry->set_path_display($relative_path_display);
                $entries_to_add[] = $entry;
            }

            foreach ($entries_to_add as $entry_metadata) {
                $zip = $this->_add_entry_to_zip($zip, $entry_metadata);
                $files_added_to_zip[] = $entry;
            }
        }

        /* Close zip */
        $result = $zip->finalize();

        /* Send email if needed */
        if ($this->get_processor()->get_shortcode_option('notificationdownload') === '1') {
            $this->get_processor()->send_notification_email('download', $files_added_to_zip);
        }
    }

    public function _add_entry_to_zip(\PHPZip\Zip\Stream\ZipStream $zip, Entry $entry) {
        $path = $entry->get_path_display();

        if ($entry->is_dir()) {
            $zip->addDirectory(ltrim($path, '/'));
        } else {

            /* Download the File */
            /* Update the time_limit as this can take a while */
            @set_time_limit(60);

            /* Get file */
            $stream = fopen('php://temp', 'r+');
            /* @var $download_file \Kunnu\Dropbox\Models\File */
            $download_file = $this->_client->download($entry->get_id());
            fwrite($stream, $download_file->getContents());
            rewind($stream);

            /* Add file contents to zip */
            try {
                $zip->addLargeFile($stream, ltrim($path, '/'), $entry->get_last_edited(), $entry->get_description());
            } catch (\Exception $ex) {
                error_log($ex->getMessage());
                fclose($stream);
                /* To Do Log */
            }

            fclose($stream);
        }

        return $zip;
    }

    public function get_thumbnail(Entry $entry, $aslink = false, $width = null, $height = null, $crop = true) {

        $thumbnail = new \TheLion\OutoftheBox\Thumbnail($this->get_processor(), $entry, $width, $height, $crop);

        if ($aslink) {
            return $thumbnail->get_url();
        } else {
            header('Location: ' . $thumbnail->get_url());
        }
        die();
    }

    public function build_thumbnail() {
        $src = $_REQUEST['src'];
        preg_match_all('/(.+)_(\d+)_(\d+)_c(\d)_q(\d+)\.([a-z]+)/', $src, $attr, PREG_SET_ORDER);

        if (count($attr) !== 1 || count($attr[0]) !== 7) {
            die();
        }

        $entry_id = $attr[0][1];
        $width = $attr[0][2];
        $height = $attr[0][3];
        $crop = ($attr[0][4] == 1) ? true : false;
        $quality = $attr[0][5];
        $format = $attr[0][6];

        $entry = $this->get_entry($entry_id, false);

        if ($entry === false) {
            die(-1);
        }


        $thumbnail = new Thumbnail($this->get_processor(), $entry, $width, $height, $crop, $quality, $format);

        if ($thumbnail->does_thumbnail_exist() === false) {
            $thumbnail->build_thumbnail();
        }

        header('Location: ' . $thumbnail->get_url());

        die();
    }

    public function has_temporarily_link(Entry $entry) {
        $cached_entry = $this->get_cache()->is_cached($entry->get_id());

        if ($cached_entry !== false) {
            if ($temporarily_link = $cached_entry->get_temporarily_link()) {
                return true;
            }
        }

        return false;
    }

    public function get_temporarily_link(Entry $entry) {
        $cached_entry = $this->get_cache()->is_cached($entry->get_id());

        if ($cached_entry !== false) {
            if ($temporarily_link = $cached_entry->get_temporarily_link()) {
                return $temporarily_link;
            }
        }

        try {
            $temporarily_link = $this->_client->getTemporaryLink($entry->get_path());
            $cached_entry = $this->get_cache()->add_to_cache($entry);
            
            $max_cache_request = ((int) $this->get_processor()->get_setting('request_cache_max_age')) * 60;
            $expires = time() + (4 * 60 * 60) - $max_cache_request;
            
            $cached_entry->add_temporarily_link($temporarily_link->getLink(), $expires);
        } catch (\Exception $ex) {
            return false;
        }

        $this->get_cache()->set_updated();
        return $cached_entry->get_temporarily_link();
    }

    public function has_shared_link(Entry $entry, $visibility = 'public') {
        $cached_entry = $this->get_cache()->is_cached($entry->get_id());

        if ($cached_entry !== false) {
            if ($shared_link = $cached_entry->get_shared_link($visibility)) {
                return true;
            }
        }

        return false;
    }

    public function get_shared_link(Entry $entry, $visibility = 'public') {

        $cached_entry = $this->get_cache()->is_cached($entry->get_id());

        if ($cached_entry !== false) {
            if ($shared_link = $cached_entry->get_shared_link($visibility)) {
                return $shared_link;
            }
        }

        return $shared_link = $this->create_shared_link($entry, $visibility);
    }

    public function create_shared_link(Entry $entry, $visibility) {
        $cached_entry = $this->get_cache()->add_to_cache($entry);
        $shared_link = false;
        try {
            $shared_link_info = $this->_client->createSharedLinkWithSettings($entry->get_path(), array('requested_visibility' => $visibility));
            $this->get_cache()->set_updated();
            $shared_link = $cached_entry->add_shared_link($shared_link_info);
        } catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $ex) {

            if ($ex->getError() === 'shared_link_already_exists' || (strpos($ex->getErrorSummary(), 'shared_link_already_exists') !== false)) {
                /* Get existing shared link */
                $shared_links = $this->_client->listSharedLinks($entry->get_path());
                $shared_links->getItems()->each(function ($shared_link_info, $key) use ($cached_entry) {
                    $cached_entry->add_shared_link($shared_link_info);
                });

                $this->get_cache()->set_updated();
                $shared_link = $cached_entry->get_shared_link($visibility);

                if (empty($shared_link)) {
                    die(sprintf(__('The sharing permissions on this file is preventing you from accessing a %s shared link. Please contact the administrator to change the Dropbox sharing settings for this document.'), $visibility));
                }
            } else {
                die($ex->getErrorSummary());
                return false;
            }
        }

        return $shared_link;
    }

    public function get_embedded_link(Entry $entry) {

        $shared_link = $this->get_shared_link($entry) . '?raw=1';
        /* Embed PDF files directly */

        if (
                in_array($entry->get_extension(), array('pdf', 'jpg', 'jpeg', 'png', 'gif'))
        ) {
            return $shared_link;
        }

        /* Otherwise, embed via Google */
        return 'https://docs.google.com/viewer?embedded=true&url=' . rawurlencode($shared_link);
    }

    public function get_shared_link_for_output($entry_path = null) {

        $entry = $this->get_entry($entry_path);

        if ($entry === false) {
            die(-1);
        }

        $shared_link = $this->get_shared_link($entry) . '?dl=1';

        $resultdata = array(
            'name' => $entry->get_name(),
            'extension' => $entry->get_extension(),
            'link' => $this->shorten_url($shared_link),
            'embeddedlink' => $this->get_embedded_link($entry),
            'size' => Helpers::bytes_to_size_1024($entry->get_size()),
            'error' => false
        );

        return $resultdata;
    }

    public function shorten_url($url) {
        if (($this->get_processor()->get_setting('shortlinks') === 'Bitly')) {
            require_once 'bitly/bitly.php';

            $this->bitly = new \Bitly($this->get_processor()->get_setting('bitly_login'), $this->get_processor()->get_setting('bitly_apikey'));
            try {
                $response = $this->bitly->shorten($url);
                $url = $response['url'];
            } catch (Exception $ex) {
                return $url;
            }
        }

        return $url;
    }

    public function add_folder($name_of_folder_to_create, $target_folder_path = null) {

        if ($this->get_processor()->get_shortcode_option('demo') === '1') {
            /* TO DO LOG + FAIL ERROR */
            die(-1);
        }

        if ($target_folder_path === null) {
            $target_folder_path = $this->get_processor()->get_requested_complete_path();
        }

        $target_entry = $this->get_entry($target_folder_path);

        /* Set new entry path */
        $new_folder_path = \TheLion\OutoftheBox\Helpers::clean_folder_path($target_entry->get_path() . '/' . $name_of_folder_to_create);

        try {
            $api_entry_new = $this->_client->createFolder($new_folder_path);
            $cached_request = new CacheRequest($this->get_processor());
            $cached_request->clear_local_cache_for_shortcode();

            return new Entry($api_entry_new);
        } catch (\Exception $ex) {
            /* TO DO LOG + FAIL ERROR */
            die('-1');
        }

        return false;
    }

    public function rename_entry($new_name, $target_entry_path = null) {
        if ($target_entry_path === null) {
            $target_entry_path = $this->get_processor()->get_requested_complete_path();
        }

        $target_entry = $this->get_entry($target_entry_path);

        if (
                $target_entry->is_file() && $this->get_processor()->get_user()->can_rename_files() === false) {
            /* TO DO LOG + FAIL ERROR */
            die(-1);
        }

        if (
                $target_entry->is_dir() && $this->get_processor()->get_user()->can_rename_folders() === false) {
            /* TO DO LOG + FAIL ERROR */
            die(-1);
        }

        if ($this->get_processor()->get_shortcode_option('demo') === '1') {
            /* TO DO LOG + FAIL ERROR */
            die(-1);
        }

        /* Set new entry path */
        $new_entry_path = \TheLion\OutoftheBox\Helpers::clean_folder_path($target_entry->get_parent() . '/' . $new_name);

        try {
            $api_entry = $this->_client->move($target_entry->get_path(), $new_entry_path);

            $cached_request = new CacheRequest($this->get_processor());
            $cached_request->clear_local_cache_for_shortcode();

            return new Entry($api_entry);
        } catch (\Exception $ex) {
            /* TO DO LOG + FAIL */
            die(-1);
        }
    }

    public function move_entry($target_entry_path, $current_entry_path = null, $copy = false) {


        if ($this->get_processor()->get_shortcode_option('demo') === '1') {
            /* TO DO LOG + FAIL ERROR */
            die(-1);
        }

        if ($current_entry_path === null) {
            $current_entry_path = $this->get_processor()->get_requested_complete_path();
        }

        $target_cached_entry = $this->get_entry($target_entry_path);
        $current_cached_entry = $this->get_entry($current_entry_path);

        if ($target_cached_entry === false || $current_cached_entry === false) {
            die(-1);
        }

        /* Set new entry path */
        $new_entry_path = \TheLion\OutoftheBox\Helpers::clean_folder_path($target_cached_entry->get_path() . '/' . $current_cached_entry->get_name());

        try {
            $api_entry = $this->_client->move($current_cached_entry->get_path(), $new_entry_path);

            $cached_request = new CacheRequest($this->get_processor());
            $cached_request->clear_local_cache_for_shortcode();

            return new Entry($api_entry);
        } catch (\Exception $ex) {
            /* TO DO LOG + FAIL */
            die(-1);
        }
    }

    public function delete_entries($entries_to_delete = array()) {

        $deleted_entries = array();

        foreach ($entries_to_delete as $target_entry_path) {
            $target_entry = $this->get_entry($target_entry_path);

            if ($target_entry === false) {
                continue;
            }

            if ($target_entry->is_file() && $this->get_processor()->get_user()->can_delete_files() === false) {
                /* TO DO LOG + FAIL ERROR */
                $deleted_entries[$target_entry->get_id()] = false;
                continue;
            }

            if ($target_entry->is_dir() && $this->get_processor()->get_user()->can_delete_folders() === false) {
                /* TO DO LOG + FAIL ERROR */
                $deleted_entries[$target_entry->get_id()] = false;
                continue;
            }

            if ($this->get_processor()->get_shortcode_option('demo') === '1') {
                $deleted_entries[$target_entry->get_id()] = false;
                continue;
            }

            try {
                $api_entry = $this->_client->delete($target_entry->get_path());
                $deleted_entries[$target_entry->get_id()] = new Entry($api_entry);
            } catch (\Exception $ex) {
                $deleted_entries[$target_entry->get_id()] = false;
            }
        }


        if ($this->get_processor()->get_shortcode_option('notificationdeletion') === '1') {
            /* TO DO NOTIFICATION */
            $this->get_processor()->send_notification_email('deletion', $deleted_entries);
        }

        return $deleted_entries;
    }

    public function upload_file($temp_file_path, $new_file_path, $params) {
        $api_entry = $this->_client->upload($temp_file_path, $new_file_path, $params);
        return new Entry($api_entry);
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
     * @return \TheLion\OutoftheBox\Tree
     */
    public function get_cache() {
        return $this->get_processor()->get_cache();
    }

    /**
     * 
     * @return \Kunnu\Dropbox\Dropbox 
     */
    public function get_library() {
        return $this->_client;
    }

}
