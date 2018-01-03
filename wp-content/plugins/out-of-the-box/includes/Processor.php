<?php

namespace TheLion\OutoftheBox;

class Processor {

    /**
     *
     * @var \TheLion\OutoftheBox\Main 
     */
    private $_main;

    /**
     *
     * @var \TheLion\OutoftheBox\App 
     */
    private $_app;

    /**
     *
     * @var \TheLion\OutoftheBox\Client 
     */
    private $_client;

    /**
     *
     * @var \TheLion\OutoftheBox\User  
     */
    private $_user;

    /**
     *
     * @var \TheLion\OutoftheBox\UserFolders 
     */
    private $_userfolders;

    /**
     *
     * @var \TheLion\OutoftheBox\Cache 
     */
    private $_cache;
    public $options = array();
    protected $lists = array();
    protected $listtoken = '';
    protected $_requestedFile;
    protected $_requestedDir;
    protected $_requestedPath;
    protected $_requestedCompletePath;
    protected $_lastPath = '/';
    public $mobile = false;
    protected $_loadscripts = array('general' => false, 'files' => false, 'upload' => false, 'mediaplayer' => false, 'qtip' => false);

    /**
     * Construct the plugin object
     */
    public function __construct(Main $_main) {
        $this->_main = $_main;
        $this->settings = get_option('out_of_the_box_settings');
        $this->lists = get_option('out_of_the_box_lists', array());

        if (isset($_REQUEST['mobile']) && ($_REQUEST['mobile'] === 'true')) {
            $this->mobile = true;
        }

        /* If the user wants a hard refresh, set this globally */
        if (isset($_REQUEST['hardrefresh']) && $_REQUEST['hardrefresh'] === 'true' && (!defined('FORCE_REFRESH'))) {
            define('FORCE_REFRESH', true);
        }
    }

    public function start_process() {
        if (!isset($_REQUEST['action'])) {
            error_log('[Out-of-the-Box message]: ' . " Function start_process() requires an 'action' request");
            die();
        }

        $authorized = $this->_is_action_authorized();

        if (($authorized === true) && ($_REQUEST['action'] === 'outofthebox-revoke')) {
            if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                $this->get_app()->revoke_token();
            }
            die(1);
        }

        if ($_REQUEST['action'] === 'outofthebox-reset-cache') {
            if (Helpers::check_user_role($this->settings['permissions_edit_settings'])) {
                $this->reset_complete_cache();
            }
            die(1);
        }

        if ((!isset($_REQUEST['listtoken']))) {
            error_log('[Out-of-the-Box message]: ' . " Function start_process() requires a 'listtoken' request");
            die(1);
        }

        $this->listtoken = $_REQUEST['listtoken'];
        if (!isset($this->lists[$this->listtoken])) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            error_log('[Out-of-the-Box message]: ' . " Function start_process() hasn't received a valid listtoken (" . $this->listtoken . ") on: $url \nLists:\n" . var_export(array_keys($this->lists), true));
            die();
        }

        $this->options = $this->lists[$this->listtoken];

        if (is_wp_error($authorized)) {
            error_log('[Out-of-the-Box message]: ' . " Function start_process() isn't authorized");

            if ($this->options['debug'] === '1') {
                die($authorized->get_error_message());
            } else {
                die();
            }
        }

        /* Set rootFolder */
        if ($this->options['user_upload_folders'] === 'manual') {
            $this->_rootFolder = $this->get_user_folders()->get_manually_linked_folder_for_user();
        } else if (($this->options['user_upload_folders'] === 'auto') && !\TheLion\OutoftheBox\Helpers::check_user_role($this->options['view_user_folders_role'])) {
            $this->_rootFolder = $this->get_user_folders()->get_auto_linked_folder_for_user();
        } else if (($this->options['user_upload_folders'] === 'auto')) {
            $this->_rootFolder = str_replace('/%user_folder%', '', $this->options['root']);
        } else {
            $this->_rootFolder = $this->options['root'];
        }

        $this->_rootFolder = html_entity_decode($this->_rootFolder);
        $this->_rootFolder = str_replace('//', '/', $this->_rootFolder);

        if ($this->get_user()->can_view() === false) {
            error_log('[Out-of-the-Box message]: ' . " Function start_process() discovered that an user didn't have the permission to view the plugin");

            die();
        }

        if (isset($_REQUEST['lastpath'])) {
            $this->_lastPath = stripslashes(rawurldecode($_REQUEST['lastpath']));
        }

        if (isset($_REQUEST['OutoftheBoxpath']) && $_REQUEST['OutoftheBoxpath'] != '') {
            $path = stripslashes(rawurldecode($_REQUEST['OutoftheBoxpath']));
            $this->_set_requested_path($path);
        } else {
            $this->_set_requested_path();
        }

        $this->set_last_path($this->get_requested_path());


        /* Check if the request is cached */
        if (defined('FORCE_REFRESH')) {
            $cached_request = new CacheRequest($this);
            $cached_request->clear_local_cache_for_shortcode();
        }

        if (in_array($_REQUEST['action'], array('outofthebox-get-filelist', 'outofthebox-get-gallery', 'outofthebox-get-playlist'))) {

            /* And Set GZIP compression if possible */
            $this->_set_gzip_compression();

            $cached_request = new CacheRequest($this);
            if ($cached_request->is_cached()) {
                echo $cached_request->get_cached_response();
                die();
            }
        }


        switch ($_REQUEST['action']) {
            case 'outofthebox-get-filelist':
                $filebrowser = new Filebrowser($this);

                if (isset($_REQUEST['query']) && $this->options['search'] === '1') { // Search files
                    $filelist = $filebrowser->search_files();
                } else {
                    $filelist = $filebrowser->get_files_list(); // Read folder
                }

                break;

            case 'outofthebox-preview':
                $preview = $this->get_client()->preview_entry();
                break;

            case 'outofthebox-download':
                if ($this->get_user()->can_download() === false) {
                    die();
                }

                $file = $this->get_client()->download_entry();
                die();
                break;


            case 'outofthebox-create-zip':
                if ($this->get_user()->can_download() === false) {
                    die();
                }

                $file = $this->get_client()->download_entries_as_zip();
                die();
                break;


            case 'outofthebox-create-link':
            case 'outofthebox-embedded':
                $link = array();

                if (isset($_REQUEST['entries'])) {
                    $links = array('links');
                    foreach ($_REQUEST['entries'] as $entry_id) {
                        $entry = $this->get_requested_complete_path() . '/' . stripslashes(rawurldecode($entry_id));
                        $link['links'][] = $this->get_client()->get_shared_link_for_output($entry);
                    }
                } else {
                    $link = $this->get_client()->get_shared_link_for_output();
                }
                echo json_encode($link);
                die();
                break;


            case 'outofthebox-get-gallery':
                if (is_wp_error($authorized)) {
// No valid token is set
                    echo json_encode(array('lastpath' => $this->_lastPath, 'folder' => '', 'html' => ''));
                    die();
                }

                $gallery = new Gallery($this);

                if (isset($_REQUEST['query']) && $this->options['search'] === '1') { // Search files
                    $imagelist = $gallery->search_image_files();
                } else {
                    $imagelist = $gallery->get_images_list(); // Read folder
                }

                die();
                break;

            case 'outofthebox-upload-file':
                $user_can_upload = $this->get_user()->can_upload();

                if (is_wp_error($authorized) || $user_can_upload === false) {
                    die();
                }

                $upload_processor = new Upload($this);
                switch ($_REQUEST['type']) {
                    case 'do-upload':
                        $upload = $upload_processor->do_upload();
                        break;
                    case 'get-status':
                        $status = $upload_processor->get_upload_status();
                        break;
                    case 'upload-postprocess':
                        $status = $upload_processor->upload_post_process();
                        break;
                }

                die();
                break;

            case 'outofthebox-delete-entry':
            case 'outofthebox-delete-entries':
//Check if user is allowed to delete entry
                $user_can_delete = $this->get_user()->can_delete_files() || $this->get_user()->can_delete_folders();

                if (is_wp_error($authorized) || $user_can_delete === false || !isset($_REQUEST['entries'])) {
                    echo json_encode(array('result' => '-1', 'msg' => __('Failed to delete entry', 'outofthebox')));
                    die();
                }

                $entries_to_delete = array();
                foreach ($_REQUEST['entries'] as $requested_path) {
                    $entry_path = str_replace('//', '/', $this->get_requested_complete_path() . '/' . rawurldecode($requested_path));
                    $entries_to_delete[] = $entry_path;
                }

                $entries = $this->get_client()->delete_entries($entries_to_delete);

                foreach ($entries as $entry) {
                    if ($entry === false) {
                        echo json_encode(array('result' => '-1', 'msg' => __('Not all entries could be deleted', 'outofthebox')));
                        die();
                    }
                }
                echo json_encode(array('result' => '1', 'msg' => __('Entry was deleted', 'outofthebox')));
                die();
                break;

            case 'outofthebox-rename-entry':
                /* Check if user is allowed to rename entry */
                $user_can_rename = $this->get_user()->can_rename_files() || $this->get_user()->can_rename_folders();

                if (is_wp_error($authorized) || $user_can_rename === false) {
                    echo json_encode(array('result' => '-1', 'msg' => __('Failed to rename entry', 'outofthebox')));
                    die();
                }

                /* Strip unsafe characters */
                $newname = rawurldecode($_REQUEST['newname']);
                $new_filename = Helpers::filter_filename($newname, false);

                $file = $this->get_client()->rename_entry($new_filename);

                if (is_wp_error($file)) {
                    echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
                } else {
                    echo json_encode(array('result' => '1', 'msg' => __('Entry was renamed', 'outofthebox')));
                }

                die();
                break;

            case 'outofthebox-move-entry':
                /* Check if user is allowed to move entry */
                $user_can_move = $this->get_user()->can_move();

                if (is_wp_error($authorized) || $user_can_move === false) {
                    echo json_encode(array('result' => '-1', 'msg' => __('Failed to move entry', 'outofthebox')));
                    die();
                }

                $file = $this->get_client()->move_entry(rawurldecode($this->_rootFolder . $_REQUEST['target']));

                if (is_wp_error($file)) {
                    echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
                } else {
                    echo json_encode(array('result' => '1', 'msg' => __('Entry was moved', 'outofthebox')));
                }

                die();
                break;


            case 'outofthebox-add-folder':

//Check if user is allowed to add folder
                $user_can_addfolder = $this->get_user()->can_add_folders();

                if (is_wp_error($authorized) || $user_can_addfolder === false) {
                    echo json_encode(array('result' => '-1', 'msg' => __('Failed to add folder', 'outofthebox')));
                    die();
                }

//Strip unsafe characters
                $newfolder = rawurldecode($_REQUEST['newfolder']);
                $new_foldername = Helpers::filter_filename($newfolder, false);

                $file = $this->get_client()->add_folder($new_foldername);

                $this->set_last_path($this->_requestedPath . '/' . $file->get_name());

                if (is_wp_error($file)) {
                    echo json_encode(array('result' => '-1', 'msg' => $file->get_error_message()));
                } else {
                    echo json_encode(array('result' => '1', 'msg' => __('Folder', 'outofthebox') . ' ' . $newfolder . ' ' . __('was added', 'outofthebox'), 'lastpath' => rawurlencode($this->_lastPath)));
                }
                die();
                break;

            case 'outofthebox-get-playlist':
                if (is_wp_error($authorized)) {
                    die();
                }

                $mediaplayer = new Mediaplayer($this);
                $playlist = $mediaplayer->get_media_list();

                break;

            case 'outofthebox-stream':
                $file = $this->get_client()->stream_entry();
                break;

            default:
                error_log('[Out-of-the-Box message]: ' . sprintf('No valid AJAX call: %s', $_REQUEST['action']));
                die();
        }
    }

    public function create_from_shortcode($atts) {


        $atts = (is_string($atts)) ? array() : $atts;
        $atts = $this->remove_deprecated_options($atts);

        //Create a unique identifier
        $this->listtoken = md5(OUTOFTHEBOX_VERSION . serialize($atts));

//Read shortcode
        extract(shortcode_atts(array(
            'dir' => '/',
            'startpath' => false,
            'mode' => 'files',
            'userfolders' => '0',
            'usertemplatedir' => '',
            'viewuserfoldersrole' => 'administrator',
            'maxuserfoldersize' => '-1',
            'ext' => '*',
            'showfiles' => '1',
            'showfolders' => '1',
            'maxfiles' => '-1',
            'filesize' => '1',
            'filedate' => '1',
            'showcolumnnames' => '1',
            'showext' => '1',
            'showroot' => '0',
            'sortfield' => 'name',
            'sortorder' => 'asc',
            'showbreadcrumb' => '1',
            'candownloadzip' => '0',
            'canpopout' => '0',
            'showsharelink' => '0',
            'showrefreshbutton' => '1',
            'roottext' => __('Start', 'outofthebox'),
            'search' => '1',
            'searchfrom' => 'parent',
            'searchcontents' => '0',
            'include' => '*',
            'exclude' => '*',
            'maxwidth' => '100%',
            'maxheight' => '',
            'viewrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'previewrole' => 'all',
            'downloadrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'previewinline' => '1',
            'forcedownload' => '0',
            'maximages' => '25',
            'crop' => '0',
            'quality' => '90',
            'slideshow' => '0',
            'pausetime' => '5000',
            'targetheight' => '150',
            'mediaextensions' => '',
            'autoplay' => '0',
            'hideplaylist' => '0',
            'linktomedia' => '0',
            'linktoshop' => '',
            'notificationupload' => '0',
            'notificationdownload' => '0',
            'notificationdeletion' => '0',
            'notificationemail' => '%admin_email%',
            'upload' => '0',
            'overwrite' => '0',
            'uploadext' => '.',
            'uploadrole' => 'administrator|editor|author|contributor|subscriber',
            'maxfilesize' => '0',
            'maxnumberofuploads' => '-1',
            'delete' => '0',
            'deletefilesrole' => 'administrator|editor',
            'deletefoldersrole' => 'administrator|editor',
            'rename' => '0',
            'renamefilesrole' => 'administrator|editor',
            'renamefoldersrole' => 'administrator|editor',
            'move' => '0',
            'moverole' => 'administrator|editor',
            'addfolder' => '0',
            'addfolderrole' => 'administrator|editor',
            'mcepopup' => '0',
            'debug' => '0',
            'demo' => '0'
                        ), $atts));

        if (!isset($this->lists[$this->listtoken])) {

            $authorized = $this->_is_action_authorized();

            if (is_wp_error($authorized)) {
                if ($debug === '1') {
                    return "<div id='message' class='error'><p>" . $authorized->get_error_message() . "</p></div>";
                }
                return '<i>>>> ' . __('ERROR: Contact the Administrator to see this content', 'outofthebox') . ' <<<</i>';
            }

            $this->lists[$this->listtoken] = array();

//Set Session Data
            switch ($mode) {
                case 'audio':
                case 'video':
                    $mediaextensions = explode('|', $mediaextensions);
                    break;
                case 'gallery':
                    $ext = ($ext == '*') ? 'gif|jpg|jpeg|png|bmp' : $ext;
                    $uploadext = ($uploadext == '.') ? 'gif|jpg|jpeg|png|bmp' : $uploadext;
                default:
                    $mediaextensions = '';
                    break;
            }

            //Force $candownloadzip = 0 if we can't use ZipArchive
            if (!class_exists('ZipArchive')) {
                $candownloadzip = '0';
            }

            $dir = rtrim($dir, "/");
            $dir = ($dir == '') ? '/' : $dir;
            if (substr($dir, 0, 1) !== '/') {
                $dir = '/' . $dir;
            }

            // Explode roles
            $viewrole = explode('|', $viewrole);
            $previewrole = explode('|', $previewrole);
            $downloadrole = explode('|', $downloadrole);
            $uploadrole = explode('|', $uploadrole);
            $deletefilesrole = explode('|', $deletefilesrole);
            $deletefoldersrole = explode('|', $deletefoldersrole);
            $renamefilesrole = explode('|', $renamefilesrole);
            $renamefoldersrole = explode('|', $renamefoldersrole);
            $moverole = explode('|', $moverole);
            $addfolderrole = explode('|', $addfolderrole);
            $viewuserfoldersrole = explode('|', $viewuserfoldersrole);

            $this->options = array(
                'root' => htmlspecialchars_decode($dir),
                'startpath' => $startpath,
                'mode' => $mode,
                'user_upload_folders' => $userfolders,
                'user_template_dir' => htmlspecialchars_decode($usertemplatedir),
                'view_user_folders_role' => $viewuserfoldersrole,
                'max_user_folder_size' => $maxuserfoldersize,
                'media_extensions' => $mediaextensions,
                'autoplay' => $autoplay,
                'hideplaylist' => $hideplaylist,
                'linktomedia' => $linktomedia,
                'linktoshop' => $linktoshop,
                'ext' => explode('|', strtolower($ext)),
                'show_files' => $showfiles,
                'show_folders' => $showfolders,
                'max_files' => $maxfiles,
                'show_filesize' => $filesize,
                'show_filedate' => $filedate,
                'show_columnnames' => $showcolumnnames,
                'show_ext' => $showext,
                'show_root' => $showroot,
                'sort_field' => $sortfield,
                'sort_order' => $sortorder,
                'show_breadcrumb' => $showbreadcrumb,
                'can_download_zip' => $candownloadzip,
                'canpopout' => $canpopout,
                'show_sharelink' => $showsharelink,
                'show_refreshbutton' => $showrefreshbutton,
                'root_text' => $roottext,
                'search' => $search,
                'searchfrom' => $searchfrom,
                'search_contents' => $searchcontents,
                'include' => explode('|', strtolower(htmlspecialchars_decode($include))),
                'exclude' => explode('|', strtolower(htmlspecialchars_decode($exclude))),
                'maxwidth' => $maxwidth,
                'maxheight' => $maxheight,
                'view_role' => $viewrole,
                'preview_role' => $previewrole,
                'download_role' => $downloadrole,
                'previewinline' => $previewinline,
                'forcedownload' => $forcedownload,
                'maximages' => $maximages,
                'notificationupload' => $notificationupload,
                'notificationdownload' => $notificationdownload,
                'notificationdeletion' => $notificationdeletion,
                'notificationemail' => $notificationemail,
                'upload' => $upload,
                'overwrite' => $overwrite,
                'upload_ext' => strtolower($uploadext),
                'upload_role' => $uploadrole,
                'maxfilesize' => $maxfilesize,
                'maxnumberofuploads' => $maxnumberofuploads,
                'delete' => $delete,
                'deletefiles_role' => $deletefilesrole,
                'deletefolders_role' => $deletefoldersrole,
                'rename' => $rename,
                'renamefiles_role' => $renamefilesrole,
                'renamefolders_role' => $renamefoldersrole,
                'move' => $move,
                'move_role' => $moverole,
                'addfolder' => $addfolder,
                'addfolder_role' => $addfolderrole,
                'crop' => $crop,
                'quality' => $quality,
                'targetheight' => $targetheight,
                'slideshow' => $slideshow,
                'pausetime' => $pausetime,
                'mcepopup' => $mcepopup,
                'debug' => $debug,
                'demo' => $demo,
                'expire' => strtotime('+1 weeks'),
                'listtoken' => $this->listtoken);

            $this->update_lists();

            //Create userfolders if needed

            if (($this->options['user_upload_folders'] === 'auto')) {
                if ($this->settings['userfolder_onfirstvisit'] === 'Yes') {

                    $allusers = array();
                    $roles = array_diff($this->options['view_role'], $this->options['view_user_folders_role']);

                    foreach ($roles as $role) {
                        $users_query = new \WP_User_Query(array(
                            'fields' => 'all_with_meta',
                            'role' => $role,
                            'orderby' => 'display_name'
                        ));
                        $results = $users_query->get_results();
                        if ($results) {
                            $allusers = array_merge($allusers, $results);
                        }
                    }
                    $userfolder = $this->get_user_folders()->create_user_folders($allusers);
                }
            }
        } else {
            $this->options = $this->lists[$this->listtoken];
            $this->update_lists();
        }

        ob_start();
        $this->render_template();

        return ob_get_clean();
    }

    public function render_template() {

        if ($this->get_user()->can_view() === false) {
            return;
        }


// Render the  template

        $rootfolder = ''; //(($this->options['user_upload_folders'] !== '0') && !\TheLion\OutoftheBox\Helpers::check_user_role($this->options['view_user_folders_role'])) ? '' : $this->options['root'];

        if ($this->options['user_upload_folders'] === 'manual') {
            $userfolder = get_user_option('out_of_the_box_linkedto');
            if (is_array($userfolder) && isset($userfolder['foldertext'])) {
                $rootfolder = $userfolder['folderid'];
            } else {
                $defaultuserfolder = get_site_option('out_of_the_box_guestlinkedto');
                if (is_array($defaultuserfolder) && isset($defaultuserfolder['folderid'])) {
                    $rootfolder = $defaultuserfolder['folderid'];
                } else {
                    include(sprintf("%s/templates/noaccess.php", OUTOFTHEBOX_ROOTDIR));
                    return;
                }
            }
        }

        $rootfolder = ($this->options['startpath'] !== false) ? $this->options['startpath'] : $rootfolder;

        echo "<div id='OutoftheBox' style='display:none'>";
        echo "<noscript><div class='OutoftheBox-nojsmessage'>" . __('To view the Dropbox folders, you need to have JavaScript enabled in your browser', 'outofthebox') . ".<br/>";
        echo "<a href='http://www.enable-javascript.com/' target='_blank'>" . __('To do so, please follow these instructions', 'outofthebox') . "</a>.</div></noscript>";

        switch ($this->options['mode']) {
            case 'files':

                $this->load_scripts('files');

                echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox files oftb-list jsdisabled' data-list='files' data-token='$this->listtoken'  data-path='" . rawurlencode($rootfolder) . "' data-org-path='" . rawurlencode($this->_lastPath) . "' data-sort='" . $this->options['sort_field'] . ":" . $this->options['sort_order'] . "' data-deeplink='" . ((!empty($_REQUEST['file'])) ? $_REQUEST['file'] : '') . "' data-layout='list'>";

                if ($this->get_shortcode_option('mcepopup') === 'linkto' || $this->get_shortcode_option('mcepopup') === 'linktobackendglobal') {
                    $button_text = __('Use the Root Folder of your Account', 'outofthebox');
                    echo '<div data-url="' . urlencode('/') . '" data-name="/">';
                    echo '<div class="entry_linkto entry_linkto_root">';
                    echo '<span><input class="button-secondary" type="submit" title="' . $button_text . '" value="' . $button_text . '"></span>';
                    echo "</div>";
                    echo "</div>";
                }

                if ($this->options['mcepopup'] === 'shortcode') {
                    echo "<div class='selected-folder'><strong>" . __('Selected folder', 'outofthebox') . ": </strong><span class='current-folder-raw'></span></div>";
                }

                include(sprintf("%s/templates/frontend.php", OUTOFTHEBOX_ROOTDIR));
                $this->render_uploadform();

                echo "</div>";
                break;

            case 'upload':

                echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox upload jsdisabled'  data-token='$this->listtoken'  data-path='" . rawurlencode($rootfolder) . "' data-org-path='" . rawurlencode($this->_lastPath) . "'>";
                $this->render_uploadform();
                echo "</div>";
                break;

            case 'gallery':

                $this->load_scripts('files');

                $nextimages = '';
                if (($this->options['maximages'] !== '0')) {
                    $nextimages = "data-loadimages='" . $this->options['maximages'] . "'";
                }

                echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox gridgallery jsdisabled' data-list='gallery' data-token='$this->listtoken' data-org-path='" . rawurlencode($this->_lastPath) . "' data-sort='" . $this->options['sort_field'] . ":" . $this->options['sort_order'] . "'  data-targetheight='" . $this->options['targetheight'] . "' data-deeplink='" . ((!empty($_REQUEST['image'])) ? $_REQUEST['image'] : '') . "' data-slideshow='" . $this->options['slideshow'] . "' data-pausetime='" . $this->options['pausetime'] . "' $nextimages>";
                include(sprintf("%s/templates/gallery.php", OUTOFTHEBOX_ROOTDIR));
                $this->render_uploadform();
                echo "</div>";
                break;

            case 'video':
            case 'audio':
                $skin = $this->settings['mediaplayer_skin'];
                $mp4key = array_search('mp4', $this->options['media_extensions']);
                if ($mp4key !== false) {
                    unset($this->options['media_extensions'][$mp4key]);
                    if ($this->options['mode'] === 'video') {
                        if (!in_array('m4v', $this->options['media_extensions'])) {
                            $this->options['media_extensions'][] = 'm4v';
                        }
                    } else {
                        if (!in_array('m4a', $this->options['media_extensions'])) {
                            $this->options['media_extensions'][] = 'm4a';
                        }
                    }
                }

                $oggkey = array_search('ogg', $this->options['media_extensions']);
                if ($oggkey !== false) {
                    unset($this->options['media_extensions'][$oggkey]);
                    if ($this->options['mode'] === 'video') {
                        if (!in_array('ogv', $this->options['media_extensions'])) {
                            $this->options['media_extensions'][] = 'ogv';
                        }
                    } else {
                        if (!in_array('oga', $this->options['media_extensions'])) {
                            $this->options['media_extensions'][] = 'oga';
                        }
                    }
                }

                $this->load_scripts('mediaplayer');

                $extensions = join(',', $this->options['media_extensions']);
                $coverclass = 'nocover';
                if ($this->options['mode'] === 'audio' && $this->options['covers'] === '1') {
                    $coverclass = 'cover';
                }

                if ($extensions !== '') {
                    echo "<div id='OutoftheBox-$this->listtoken' class='OutoftheBox media " . $this->options['mode'] . " $coverclass jsdisabled' data-list='media' data-token='$this->listtoken' data-extensions='" . $extensions . "' data-path='$this->_lastPath' data-sort='" . $this->options['sort_field'] . ":" . $this->options['sort_order'] . "' data-deeplink='' data-autoplay='" . $this->options['autoplay'] . "'>";
                    include(sprintf("%s/skins/%s/player.php", OUTOFTHEBOX_ROOTDIR, $skin));
                    echo "</div>";
                } else {
                    echo '<strong>Out-of-the-Box:</strong>' . __('Please update your mediaplayer shortcode', 'outofthebox');
                }

                break;
        }
        echo "</div>";

        $this->load_scripts('general');
    }

    public function render_uploadform() {
        $user_can_upload = $this->get_user()->can_upload();

        if ($user_can_upload === false) {
            return;
        }

        $post_max_size_bytes = min(\TheLion\OutoftheBox\Helpers::return_bytes(ini_get('post_max_size')), \TheLion\OutoftheBox\Helpers::return_bytes(ini_get('upload_max_filesize')));
        $max_file_size = ($this->options['maxfilesize'] !== '0') ? Helpers::return_bytes($this->options['maxfilesize']) : ($post_max_size_bytes);
        $post_max_size_str = \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($max_file_size);
        $acceptfiletypes = '.(' . $this->options['upload_ext'] . ')$';
        $max_number_of_uploads = $this->options['maxnumberofuploads'];

        $this->load_scripts('upload');
        include(sprintf("%s/templates/uploadform.php", OUTOFTHEBOX_ROOTDIR));
    }

    public function create_thumbnail() {
        $this->get_client()->build_thumbnail();
        die();
    }

    public function get_last_path() {
        return $this->_lastPath;
    }

    public function set_last_path($last_path) {
        $this->_lastPath = $last_path;
        if ($this->_lastPath === '') {
            $this->_lastPath = '/';
        }
        $this->_set_requested_path();
        return $this->_lastPath;
    }

    public function get_requested_path() {
        return $this->_requestedPath;
    }

    private function _set_requested_path($path = '') {

        if ($path === '') {
            if ($this->_lastPath !== '') {
                $path = $this->_lastPath;
            } else {
                $path = '/';
            }
        }

        $path = \TheLion\OutoftheBox\Helpers::clean_folder_path($path);
        $path_parts = \TheLion\OutoftheBox\Helpers::get_pathinfo($path);

        $this->_requestedDir = '';
        $this->_requestedFile = '';

        if (isset($path_parts['extension'])) {
//it's a file
            $this->_requestedFile = $path_parts['basename'];
            $this->_requestedDir = str_replace('\\', '/', $path_parts['dirname']);
            $requestedDir = ($this->_requestedDir === '/') ? '/' : $this->_requestedDir . '/';
            $this->_requestedPath = $requestedDir . $this->_requestedFile;
        } else {
//it's a dir
            $this->_requestedDir = str_replace('\\', '/', $path);
            $this->_requestedFile = '';
            $this->_requestedPath = $this->_requestedDir;
        }

        $requestedCompletePath = $this->_rootFolder;
        if ($this->_rootFolder !== $this->_requestedPath) {
            $requestedCompletePath = html_entity_decode($this->_rootFolder . $this->_requestedPath);
        }

        $this->_requestedCompletePath = str_replace('//', '/', $requestedCompletePath);
    }

    public function get_requested_complete_path() {
        return $this->_requestedCompletePath;
    }

    public function get_root_folder() {
        return $this->_rootFolder;
    }

    public function get_relative_path($full_path, $from_path = null) {
        if (empty($from_path)) {

            if ($this->get_root_folder() === '' || $this->get_root_folder() === '/') {
                return $full_path;
            }

            $from_path = $this->get_root_folder();
        }

        $from_path_arr = explode('/', $from_path);
        $full_path_arr = explode('/', $full_path);
        $difference = (count($full_path_arr) - count($from_path_arr));

        if ($difference < 1) {
            return '/';
        }

        if ($difference === 1) {
            return '/' . end($full_path_arr);
        }

        return '/' . implode('/', array_slice($full_path_arr, -($difference)));
    }

    public function get_listtoken() {
        return $this->listtoken;
    }

    protected function load_scripts($template) {
        if ($this->_loadscripts[$template] === true) {
            return false;
        }

        switch ($template) {
            case 'general':
                wp_enqueue_style('OutoftheBox');
                wp_enqueue_script('OutoftheBox');
                break;
            case 'files':
                wp_enqueue_style('qtip');

                if ($this->get_user()->can_move()) {
                    wp_enqueue_script('jquery-ui-droppable');
                    wp_enqueue_script('jquery-ui-draggable');
                }

                wp_enqueue_script('jquery-effects-core');
                wp_enqueue_script('jquery-effects-fade');
                wp_enqueue_style('ilightbox');
                wp_enqueue_style('ilightbox-skin-outofthebox');

                break;

            case 'mediaplayer':
                wp_enqueue_style('OutoftheBox.Media');
                wp_enqueue_script('jQuery.jplayer');
                wp_enqueue_script('jQuery.jplayer.playlist');
                wp_enqueue_script('OutoftheBox.Media');
                break;
            case 'upload':
                //wp_enqueue_style('OutoftheBox-fileupload-jquery-ui');
                wp_enqueue_script('jquery-ui-droppable');
                wp_enqueue_script('jquery-ui-button');
                wp_enqueue_script('jquery-ui-progressbar');
                wp_enqueue_script('jQuery.iframe-transport');
                wp_enqueue_script('jQuery.fileupload');
                wp_enqueue_script('jQuery.fileupload-process');
                break;
        }

        $this->_loadscripts[$template] = true;
    }

    protected function remove_deprecated_options($options = array()) {
        /* Deprecated Shuffle */
        if (isset($options['shuffle'])) {
            unset($options['shuffle']);
            $options['sortfield'] = 'shuffle';
        }
        /* Changed Userfolders */
        if (isset($options['userfolders']) && $options['userfolders'] === '1') {
            $options['userfolders'] = 'auto';
        }

        if (isset($options['partiallastrow'])) {
            unset($options['partiallastrow']);
        }

        if (isset($options['maxfiles']) && empty($options['maxfiles'])) {
            unset($options['maxfiles']);
        }

        /* Convert bytes in version before 1.8 to MB */
        if (isset($options['maxfilesize']) && !empty($options['maxfilesize']) && ctype_digit($options['maxfilesize'])) {
            $options['maxfilesize'] = Helpers::bytes_to_size_1024($options['maxfilesize']);
        }

        if (isset($options['forcedownload']) && $options['forcedownload'] === 1 && !isset($options['previewrole'])) {
            $options['previewrole'] = 'none';
        }

        return $options;
    }

    protected function update_lists() {
        $this->lists[$this->listtoken] = $this->options;
        $this->_clean_lists();
        update_option('out_of_the_box_lists', $this->lists);
    }

    public function sort_filelist($foldercontents) {
        if (count($foldercontents) > 0) {
// Sort Filelist, folders first
            $sort = array();

            $sort_field = 'name';
            $sort_order = SORT_ASC;

            if (isset($_REQUEST['sort'])) {
                $sort_options = explode(':', $_REQUEST['sort']);

                if ($sort_options[0] === 'shuffle') {
                    shuffle($foldercontents);
                    return $foldercontents;
                }

                if (count($sort_options) === 2) {
                    $sort_field = $sort_options[0];

                    switch ($sort_options[1]) {
                        case 'asc':
                            $sort_order = SORT_ASC;
                            break;
                        case 'desc':
                            $sort_order = SORT_DESC;
                            break;
                    }
                }
            }

            $multisort_flag = SORT_REGULAR;
            if ($sort_field === 'name') {
                //$multisort_flag = SORT_NATURAL;
            }

            foreach ($foldercontents as $k => $v) {
                $sort['is_dir'][$k] = $v->is_dir();

                switch ($sort_field) {
                    case 'modified':
                        $sort['sort'][$k] = $v->get_last_edited();
                        break;
                    case 'size':
                        $sort['sort'][$k] = $v->get_size();
                        break;
                    case 'name':
                    default:
                        $sort['sort'][$k] = $v->get_name();
                        break;
                }
            }

            /* Sort by dir desc and then by name asc */
            array_multisort($sort['is_dir'], SORT_DESC, SORT_REGULAR, $sort['sort'], $sort_order, $multisort_flag, $foldercontents, SORT_ASC, $multisort_flag);
        }
        return $foldercontents;
    }

    public function send_notification_email($emailtype = false, $entries = array()) {

        if ($emailtype === false) {
            return;
        }

        /* Current site url */
        $currenturl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        /* Root Folder of the current shortcode */
        $rootfolder = $this->get_client()->get_entry($this->get_root_folder(), false);

        /* Vistor name and email */
        $visitor = __('A guest', 'outofthebox');
        $visitor_email = '';
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $visitor = $current_user->display_name;
            $visitor_email = $current_user->user_email;
        }

        /* User IP */
        $ip = Helpers::get_user_ip();

        /* Geo location if required */
        $location = Helpers::get_user_location($ip);

        /* Create FileList */
        $_filelisttemplate = trim($this->settings['filelist_template']);
        $filelist = '';
        foreach ($entries as $entry) {

            $url = ($this->get_client()->has_shared_link($entry) ) ? $this->get_client()->get_shared_link($entry) . '?dl=0' : admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($entry->get_path()) . "&lastpath=" . rawurlencode($this->_lastPath) . "&listtoken=" . $this->listtoken;
            $filename = $entry->get_name();
            $filepath = $entry->get_path_display();

            $fileline = strtr($_filelisttemplate, array(
                "%filename%" => $filename,
                "%filesize%" => Helpers::bytes_to_size_1024($entry->get_size()),
                "%fileurl%" => $url,
                "%filepath%" => $filepath,
                "%filesafepath%" => $entry->get_path_display()
            ));
            $filelist .= $fileline;
        }

        /* Create Message */
        switch ($emailtype) {
            case 'download':
                if (count($entries) === 1) {
                    $subject = trim($this->settings['download_template_subject']);
                } else {
                    $subject = trim($this->settings['download_template_subject_zip']);
                }
                $message = trim($this->settings['download_template']);
                break;
            case 'upload':
                $subject = trim($this->settings['upload_template_subject']);
                $message = trim($this->settings['upload_template']);
                break;
            case 'deletion':
            case 'deletion_multiple':
                $subject = trim($this->settings['delete_template_subject']);
                $message = trim($this->settings['delete_template']);
                break;
        }


        /* Replace filters */
        $recipients = strtr(trim($this->options['notificationemail']), array(
            "%admin_email%" => get_site_option('admin_email'),
            "%user_email%" => $visitor_email
        ));

        $subject = strtr($subject, array(
            "%sitename%" => get_bloginfo(),
            "%number_of_files%" => count($entries),
            "%visitor%" => $visitor,
            "%user_email%" => $visitor_email,
            "%ip%" => $ip,
            "%location%" => $location,
            "%filename%" => $filename,
            "%filepath%" => $filepath,
            '%folder%' => $rootfolder->get_name()
        ));

        $message = strtr($message, array(
            "%visitor%" => $visitor,
            "%currenturl%" => $currenturl,
            "%filelist%" => $filelist,
            "%ip%" => $ip,
            "%location%" => $location
        ));

        $recipients = explode(',', $recipients);


        /* Create Notifaction variable for hook */
        $notification = array(
            'type' => $emailtype,
            'recipients' => $recipients,
            'subject' => $subject,
            'message' => $message,
            'files' => $entries
        );

        /* Executes hook */
        $notification = apply_filters('outofthebox_notification', $notification);

        /* Send mail */
        try {
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $htmlmessage = nl2br($notification['message']);

            foreach ($notification['recipients'] as $recipient) {
                $result = wp_mail($recipient, $notification['subject'], $htmlmessage, $headers);
            }
        } catch (\Exception $ex) {
            
        }
    }

    private function _clean_lists() {
        $now = time();
        foreach ($this->lists as $token => $list) {

            if (!isset($list['expire']) || ($list['expire']) < $now) {
                unset($this->lists[$token]);
            }
        }
    }

    protected function _is_action_authorized($hook = false) {
        $allow_nonce_verification = apply_filters("out_of_the_box_allow_nonce_verification", true);

        if ($allow_nonce_verification && isset($_REQUEST['action']) && ($hook === false) && is_user_logged_in()) {

            $is_authorized = false;
            switch ($_REQUEST['action']) {

                case 'outofthebox-get-filelist':
                case 'outofthebox-get-gallery':
                case 'outofthebox-get-playlist':
                case 'outofthebox-rename-entry':
                case 'outofthebox-move-entry':
                case 'outofthebox-upload-file':
                case 'outofthebox-add-folder':
                case 'outofthebox-create-zip':
                    $is_authorized = check_ajax_referer($_REQUEST['action'], false, false);
                    break;

                case 'outofthebox-delete-entry':
                case 'outofthebox-delete-entries':
                    $is_authorized = check_ajax_referer('outofthebox-delete-entry', false, false);
                    break;

                case 'outofthebox-create-link':
                    $is_authorized = check_ajax_referer('outofthebox-create-link', false, false);
                    break;
                case 'outofthebox-embedded':
                case 'outofthebox-download':
                case 'outofthebox-stream':
                case 'outofthebox-getpopup':
                case 'outofthebox-thumbnail':
                case 'outofthebox-preview':
                    $is_authorized = true;
                    break;

                case 'outofthebox-reset-cache':
                case 'outofthebox-revoke':
                    $is_authorized = check_ajax_referer('outofthebox-admin-action', false, false);
                    break;

                case 'edit': // Required for integration one Page/Post pages
                    $is_authorized = true;
                    break;
                case 'editpost': // Required for Yoast SEO Link Watcher trying to build the shortcode
                case 'wpseo_filter_shortcodes':
                    return false;
                default:
                    error_log('[Out-of-the-Box message]: ' . " Function _is_action_authorized() didn't receive a valid action: " . $_REQUEST['action']);
                    die();
            }

            if ($is_authorized === false) {
                error_log('[Out-of-the-Box message]: ' . " Function _is_action_authorized() didn't receive a valid nonce");
                die();
            }
        }

        if (!$this->get_app()->has_access_token()) {
            error_log('[Out-of-the-Box message]: ' . " Function _is_action_authorized() discovered that the plugin doesn't have an access token");
            return new \WP_Error('broke', '<strong>' . __("Out-of-the-Box needs your help!", 'outofthebox') . '</strong> ' . __('Authorize the plugin.', 'outofthebox') . '.');
        }


        $this->get_client();

        return true;
    }

    /*
     * Check if $entry is allowed
     */

    public function _is_entry_authorized(Entry $entry) {
        /* Return in case a direct call is being made, and no shortcode is involved */
        if (empty($this->options)) {
            return true;
        }

        if (strtolower($entry->get_path()) === strtolower($this->_rootFolder)) {
            return true;
        }

        /* skip entry if its a file, and we dont want to show files */
        if (($entry->is_file()) && ($this->get_shortcode_option('show_files') === '0')) {
            return false;
        }

        /* Skip entry if its a folder, and we dont want to show folders */
        if (($entry->is_dir()) && ($this->get_shortcode_option('show_folders') === '0')) {
            return false;
        }

        /* Only keep files with the right extension */
        if ($entry->is_file() === true && (!in_array($entry->get_extension(), $this->get_shortcode_option('ext'))) && $this->options['ext'][0] != '*') {
            return false;
        }

        $_path = str_ireplace($this->_rootFolder . '/', '', $entry->get_path());
        $_path = strtolower($_path);
        $subs = array_filter(explode('/', $_path));

        if ($this->options['exclude'][0] != '*') {
            if (count($subs) === 1) {
                $found = false;

                foreach ($subs as $sub) {
                    if (in_array($sub, $this->options['exclude'])) {
                        $found = true;
                        continue;
                    }
                }
                if ($found) {
                    return false;
                }
            } elseif (count($subs) > 1) {
                $found = false;

                foreach ($subs as $sub) {
                    if (in_array($sub, $this->options['exclude'])) {
                        $found = true;
                        continue;
                    }
                }
                if ($found) {
                    return false;
                }
            }
        }

        /* only allow included folders and files */

        if ($this->options['include'][0] != '*') {
            if (count($subs) === 1) {
                $found = false;

                foreach ($subs as $sub) {
                    if (in_array($sub, $this->options['include'])) {
                        $found = true;
                        continue;
                    }
                }
                if (!$found) {
                    return false;
                }
            } elseif (count($subs) > 1) {
                $found = false;

                foreach ($subs as $sub) {
                    if (in_array($sub, $this->options['include'])) {
                        $found = true;
                        continue;
                    }
                }
                if (!$found) {
                    return false;
                }
            }
        }
        //if ($this->options['include'][0] != '*') {
        //  foreach ($this->options['include'] as $includedentry) {
        //    if (stripos($entry, '/' . $includedentry) === false) {
        //      return false;
        //    }
        //  }
        //}
        return true;
    }

    /*
     * Check if $extensions array has $entry
     */

    public function _is_extension_authorized($entry, $extensions, $prefix = '.') {
        if ($extensions[0] != '*') {

            $pathinfo = \TheLion\OutoftheBox\Helpers::get_pathinfo($entry);
            if (!isset($pathinfo['extension'])) {
                return true;
            }

            foreach ($extensions as $allowedextensions) {
                if (stripos($entry, $prefix . $allowedextensions) !== false) {
                    return true;
                }
            }
        } else {
            return true;
        }
        return false;
    }

    public function is_mobile() {
        return $this->mobile;
    }

    public function get_setting($key) {
        return $this->settings[$key];
    }

    public function set_setting($key, $value) {
        $this->settings[$key] = $value;
        $success = update_option('out_of_the_box_settings', $this->settings);
        $this->settings = get_option('out_of_the_box_settings');
        return $success;
    }

    public function get_shortcode() {
        return $this->options;
    }

    public function get_shortcode_option($key) {
        return $this->options[$key];
    }

    /**
     * Function that enables gzip compression when is needed and when is possible
     */
    private function _set_gzip_compression() {
        /* Compress file list if possible */
        if ($this->get_setting('gzipcompression') === 'Yes') {
            $zlib = (ini_get('zlib.output_compression') == '' || !ini_get('zlib.output_compression')) && (ini_get('output_handler') != 'ob_gzhandler');
            if ($zlib === true) {
                if (extension_loaded('zlib')) {
                    if (!in_array('ob_gzhandler', ob_list_handlers())) {
                        ob_start('ob_gzhandler');
                    }
                }
            }
        }
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
     * @return \TheLion\OutoftheBox\App
     */
    public function get_app() {
        if (empty($this->_app)) {
            $this->_app = new \TheLion\OutoftheBox\App($this);
        }

        return $this->_app;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Client
     */
    public function get_client() {
        if (empty($this->_client)) {
            $this->_client = new \TheLion\OutoftheBox\Client($this->get_app(), $this);
        }

        return $this->_client;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Cache
     */
    public function get_cache() {
        if (empty($this->_cache)) {
            $this->_cache = new \TheLion\OutoftheBox\Cache($this);
        }

        return $this->_cache;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\User
     */
    public function get_user() {
        if (empty($this->_user)) {
            $this->_user = new \TheLion\OutoftheBox\User($this);
        }

        return $this->_user;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\UserFolders
     */
    public function get_user_folders() {
        if (empty($this->_userfolders)) {
            $this->_userfolders = new \TheLion\OutoftheBox\UserFolders($this);
        }

        return $this->_userfolders;
    }

    public function reset_complete_cache() {
        update_option('out_of_the_box_lists', array());

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {

            if ($path->isDir()) {
                continue;
            }
            if ($path->getFilename() === '.htaccess') {
                continue;
            }

            try {
                unlink($path->getPathname());
            } catch (\Exception $ex) {
                continue;
            }
        }
        return true;
    }

}
