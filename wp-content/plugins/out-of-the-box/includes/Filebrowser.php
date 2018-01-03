<?php

namespace TheLion\OutoftheBox;

class Filebrowser {

    /**
     *
     * @var \TheLion\OutoftheBox\Processor 
     */
    private $_processor;
    private $_search = false;

    public function __construct(Processor $_processor) {
        $this->_processor = $_processor;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Processor 
     */
    public function get_processor() {
        return $this->_processor;
    }

    public function get_files_list() {

        $this->_folder = $this->get_processor()->get_client()->get_folder();

        if (($this->_folder !== false)) {
            $this->renderFilelist();
        }
    }

    public function search_files() {
        $this->_search = true;
        $input = mb_strtolower($_REQUEST['query'], 'UTF-8');
        $this->_folder = $this->get_processor()->get_client()->search($input);

        if (($this->_folder !== false)) {
            $this->renderFilelist();
        }
    }

    public function renderFilelist() {

        /* Create HTML Filelist */
        $filelist_html = "";

        // Add 'back to Previous folder' if needed
        if (
                ($this->_search === false) &&
                ($this->_folder->get_path() !== '') &&
                (strtolower($this->_folder->get_path()) !== strtolower($this->get_processor()->get_root_folder()))
        ) {


            $foldername = basename($this->_folder->get_path());
            $location = str_replace('\\', '/', (dirname($this->get_processor()->get_requested_path())));

            $parent_folder_entry = new Entry();
            $parent_folder_entry->set_id('Previous Folder');
            $parent_folder_entry->set_name(__('Previous folder', 'outofthebox'));
            $parent_folder_entry->set_path($location);
            $parent_folder_entry->set_path_display($location);
            $parent_folder_entry->set_is_dir(true);
            $parent_folder_entry->set_parent_folder(true);
            $parent_folder_entry->set_icon(OUTOFTHEBOX_ROOTPATH . '/css/icons/32x32/folder-grey.png');

            $filelist_html .= $this->renderDir($parent_folder_entry);
        }


        /* Limit the number of files if needed */
        if ($this->get_processor()->get_shortcode_option('max_files') !== '-1' && $this->_folder->has_children()) {
            $children = $this->_folder->get_children();
            $children_sliced = array_slice($children, 0, (int) $this->get_processor()->get_shortcode_option('max_files'));
            $this->_folder->set_children($children_sliced);
        }

        if ($this->_folder->has_children()) {

            $hasfilesorfolders = false;

            foreach ($this->_folder->get_children() as $item) {
                /* Render folder div */
                if ($item->is_dir()) {
                    $filelist_html .= $this->renderDir($item);


                    if (!$item->is_parent_folder()) {
                        $hasfilesorfolders = true;
                    }
                }
            }
        }

        $filelist_html .= $this->renderNewFolder();

        if ($this->_folder->has_children()) {
            foreach ($this->_folder->get_children() as $item) {
                /* Render files div */
                if ($item->is_file()) {
                    $filelist_html .= $this->renderFile($item);
                    $hasfilesorfolders = true;
                }
            }

            if ($hasfilesorfolders === false) {
                if ($this->get_processor()->get_shortcode_option('show_files') === '1') {
                    $filelist_html .= $this->renderNoResults();
                }
            }
        } else {
            if ($this->get_processor()->get_shortcode_option('show_files') === '1' || $this->_search === true) {
                $filelist_html .= $this->renderNoResults();
            }
        }

        /* Create HTML Filelist title */
        $spacer = ' &raquo; ';

        $breadcrumbelements = array_filter(explode('/', $this->get_processor()->get_requested_path()));

        $location = '';
        foreach ($breadcrumbelements as &$element) {
            $location .= '/' . $element;
            $class = 'folder';
            if (basename($this->get_processor()->get_requested_path()) == $element) {
                $class .= ' current_folder';
            }
            $element = "<a href='javascript:void(0)' class='" . $class . "' data-url='" . rawurlencode($location) . "'>" . $element . "</a>";
        }

        if (($this->get_processor()->get_shortcode_option('show_root') === '1') && ($this->get_processor()->get_root_folder() != '/')) {
            $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . ltrim($this->get_processor()->get_root_folder(), '/') . "</a>";
            array_unshift($breadcrumbelements, $startelement);
        } else {
            $userfolder_name = $this->get_processor()->get_user_folders()->get_auto_linked_folder_name_for_user();
            $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . $this->get_processor()->get_shortcode_option('root_text') . "</a>";

            array_unshift($breadcrumbelements, $startelement);
        }

        $filepath = implode($spacer, $breadcrumbelements);

        $raw_path = '';
        if (
                (\TheLion\OutoftheBox\Helpers::check_user_role($this->get_processor()->get_setting('permissions_add_shortcodes'))) ||
                (\TheLion\OutoftheBox\Helpers::check_user_role($this->get_processor()->get_setting('permissions_add_links'))) ||
                (\TheLion\OutoftheBox\Helpers::check_user_role($this->get_processor()->get_setting('permissions_add_embedded')))
        ) {
            $raw_path = ($this->_folder->get_path() !== null) ? $this->_folder->get_path() : '';
        }


        if ($this->_search === true) {
            $expires = 0;
        } else {
            $expires = time() + 60 * 5;
        }

        $response = json_encode(array(
            'lastpath' => rawurlencode($this->get_processor()->get_last_path()),
            'rawpath' => $raw_path,
            'breadcrumb' => $filepath,
            'html' => $filelist_html,
            'expires' => $expires));


        $cached_request = new CacheRequest($this->get_processor());
        $cached_request->add_cached_response($response);

        echo $response;
        die();
    }

    public function renderNoResults() {

        $html = '<div class="entry folder">
<div class="entry_icon">
<img src="' . OUTOFTHEBOX_ROOTPATH . '/css/clouds/cloud_status_16.png" ></div>
<div class="entry_name"><a class="entry_link">' . __('No files or folders found', 'outofthebox') . '</a></div></div>
';

        return $html;
    }

    public function renderDir(Entry $item) {
        $return = '';

        $classmoveable = ($this->get_processor()->get_user()->can_move()) ? 'moveable' : '';
        $style = ($item->is_parent_folder()) ? ' previous ' : '';

        $return .= "<div class='entry folder $classmoveable $style' data-url='" . rawurlencode($item->get_path_display()) . "' data-name=\"" . $item->get_basename() . "\">\n";
        $return .= "<div class='entry_icon' data-url='" . rawurlencode($item->get_path_display()) . "'><img src='" . $item->get_icon() . "'/></div>\n";

        if ($item->is_parent_folder() === false) {

            if ($this->get_processor()->get_shortcode_option('mcepopup') === 'linkto' || $this->get_processor()->get_shortcode_option('mcepopup') === 'linktobackendglobal') {
                $return .= "<div class='entry_linkto'>\n";
                $return .= "<span>" . "<input class='button-secondary' type='submit' title='" . __('Select folder', 'outofthebox') . "' value='" . __('Select folder', 'outofthebox') . "'>" . '</span>';
                $return .= "</div>";
            }

            if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_folders()) {
                $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item->get_name()) . "'/></div>";
            }

            if ($this->get_processor()->get_shortcode_option('mcepopup') === 'links') {
                $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item->get_name()) . "'/></div>";
            }

            $return .= "<div class='entry_edit'>";
            $return .= $this->renderEditItem($item);
            $return .= "</div>";

            $return .= "<div class='entry_name'><a class='entry_link' title='{$item->get_basename()}'><span>" . $item->get_basename() . "</span></a></div>";
        } else {
            $return .= "<div class='entry_name'><a class='entry_link' title='{$item->get_basename()}'><span>" . $item->get_name() . "</span></a></div>";
        }

        $return .= "</div>\n";
        return $return;
    }

    public function renderFile(Entry $item) {
        $return = '';
        $classmoveable = ($this->get_processor()->get_user()->can_move()) ? 'moveable' : '';

        $return .= "<div class='entry file $classmoveable' data-url='" . rawurlencode($item->get_path_display()) . "' data-name=\"" . $item->get_name() . "\">\n";
        $return .= "<div class='entry_icon'><img src='" . $item->get_icon() . "'/></div>";

        $link = $this->renderFileNameLink($item);
        $title = $link['filename'] . ((($this->get_processor()->get_shortcode_option('show_filesize') === '1') && ($item->get_size() > 0)) ? ' (' . \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($item->get_size()) . ')' : '&nbsp;');

        if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_files()) {
            $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item->get_name()) . "'/></div>";
        }

        if (in_array($this->get_processor()->get_shortcode_option('mcepopup'), array('links', 'embedded'))) {
            $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item->get_name()) . "'/></div>";
        }

        $return .= "<div class='entry_edit_placheholder'><div class='entry_edit'>";
        $return .= $this->renderEditItem($item);
        $return .= "</div></div>";


        $download_url = admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken() . "&dl=1";
        $caption = ($this->get_processor()->get_user()->can_download()) ? '<a href="' . $download_url . '" title="' . __('Download file', 'outofthebox') . '"><i class="fa fa-arrow-circle-down" aria-hidden="true"></i></a>&nbsp' : '';
        $caption .= $link['filename'];

        $add_caption = true;
        if (in_array($item->get_extension(), array('mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga'))) {
            /* Don't overlap the player controls with the caption */
            $add_caption = false;
        }

        $return .= "<a {$link['url']} {$link['target']} class='{$link['class']}' title='$title' {$link['lightbox']} {$link['onclick']} data-filename='{$link['filename']}' " . (($add_caption) ? "data-caption='$caption'" : '') . ">";

        if ($this->get_processor()->get_shortcode_option('show_filesize') === '1') {
            $return .= "<div class='entry_size'>" . \TheLion\OutoftheBox\Helpers::bytes_to_size_1024($item->get_size()) . "</div>";
        }

        if ($this->get_processor()->get_shortcode_option('show_filedate') === '1') {
            $return .= "<div class='entry_lastedit'>" . $item->get_last_edited_str() . "</div>";
        }


        $return .= "<div class='entry_name'><span>" . $link['filename'];

        if ($this->_search === true) {
            $return .= "<div class='entry_foundpath'>" . $item->get_path() . "</div>";
        }

        $return .= "</span></div>";
        $return .= "</a>";

        $return .= $link['lightbox_inline'];
        $return .= "</div>\n";

        return $return;
    }

    public function renderFileNameLink(Entry $item) {
        $class = '';
        $url = '';
        $target = '';
        $onclick = '';
        $lightbox = '';
        $lightbox_inline = '';
        $datatype = 'iframe';
        $filename = ($this->get_processor()->get_shortcode_option('show_ext') === '1') ? $item->get_name() : $item->get_basename();

        /* Check if user is allowed to preview the file */
        if (($this->get_processor()->get_user()->can_preview()) && ($this->get_processor()->get_shortcode_option('mcepopup') === '0') && $this->get_processor()->get_user()->can_view() && $item->get_can_preview_by_cloud()) {


            $url = admin_url('admin-ajax.php') . "?action=outofthebox-preview&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken();

            /* Display direct links for image and media files */
            if (in_array($item->get_extension(), array('jpg', 'jpeg', 'gif', 'png'))) {
                $datatype = 'image';
                if ($this->get_processor()->get_client()->has_temporarily_link($item)) {
                    $url = $this->get_processor()->get_client()->get_temporarily_link($item);
                } elseif ($this->get_processor()->get_client()->has_shared_link($item)) {
                    $url = $this->get_processor()->get_client()->get_shared_link($item) . '?raw=1';
                }
            } else if (in_array($item->get_extension(), array('mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga'))) {
                $datatype = 'inline';
                if ($this->get_processor()->get_client()->has_temporarily_link($item)) {
                    $url = $this->get_processor()->get_client()->get_temporarily_link($item);
                }
            }

            /* Check if we need to preview inline */
            if ($this->get_processor()->get_shortcode_option('previewinline') === '1') {
                $class = 'entry_link ilightbox-group';
                $onclick = "sendGooglePageView('Preview', '{$item->get_name()}');";

                /* Lightbox Settings */
                $lightbox = "rel='ilightbox[" . $this->get_processor()->get_listtoken() . "]' ";
                $lightbox .= 'data-type="' . $datatype . '"';

                switch ($datatype) {
                    case 'image':
                        $lightbox .= ' data-options="thumbnail: \'' . $this->get_processor()->get_client()->get_thumbnail($item, true, 128, 128, false) . '\'"';
                        break;
                    case 'inline':
                        $id = 'ilightbox_' . $this->get_processor()->get_listtoken() . '_' . md5($item->get_id());
                        $html5_element = (strpos($item->get_mimetype(), 'video') === false) ? 'audio' : 'video';
                        $icon = str_replace('32x32', '128x128', $item->get_icon());
                        $icon_256 = str_replace('32x32', '256x256', $item->get_icon());
                        $lightbox .= ' data-options="mousewheel: false, width: \'85%\', height: \'85%\', thumbnail: \'' . $icon . '\'"';
                        $thumbnail = ($html5_element === 'audio') ? '<div class="html5_player_thumbnail"><img src="' . $icon_256 . '"/><h3>' . $item->get_basename() . '</h3></div>' : '';
                        $lightbox_inline = '<div id="' . $id . '" class="html5_player" style="display:none;"><div class="html5_player_container"><div style="width:100%"><' . $html5_element . ' controls controlsList="nodownload" preload="metadata"  poster="' . $icon_256 . '"> <source data-src="' . $url . '" type="' . $item->get_mimetype() . '">' . __('Your browser does not support HTML5. You can only download this file', 'outofthebox') . '</' . $html5_element . '></div>' . $thumbnail . '</div></div>';
                        $url = '#' . $id;
                        break;
                    case 'iframe':
                        $lightbox .= ' data-options="mousewheel: false, width: \'85%\', height: \'80%\', thumbnail: \'' . str_replace('32x32', '128x128', $item->get_icon()) . '\'"';
                    default:
                        break;
                }
            } else {
                $class = 'entry_action_external_view';
                $target = '_blank';
                $onclick = "sendGooglePageView('Preview  (new window)', '{$item->get_name()}');";
            }
        } else if (($this->get_processor()->get_shortcode_option('mcepopup') === '0') && $this->get_processor()->get_user()->can_download()) {
            /* Check if user is allowed to download file */

            $url = admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken();
            $class = 'entry_action_download';
        }

        if ($this->get_processor()->get_shortcode_option('mcepopup') === 'woocommerce') {
            $class = 'entry_woocommerce_link';
        }


        if ($this->get_processor()->is_mobile() && $datatype === 'iframe') {
            $lightbox = '';
            $class = 'entry_action_external_view';
            $target = '_blank';
            $onclick = "sendGooglePageView('Preview  (new window)', '{$item->get_name()}');";
        }

        if (!empty($url)) {
            $url = "href='" . $url . "'";
        };
        if (!empty($target)) {
            $target = "target='" . $target . "'";
        };
        if (!empty($onclick)) {
            $onclick = 'onclick="' . $onclick . '"';
        };

        return array('filename' => htmlspecialchars($filename, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES), 'class' => $class, 'url' => $url, 'lightbox' => $lightbox, 'lightbox_inline' => $lightbox_inline, 'target' => $target, 'onclick' => $onclick);
    }

    public function renderEditItem(Entry $item) {
        $html = '';

        $usercanrename = ($item->is_dir()) ? $this->get_processor()->get_user()->can_rename_folders() : $this->get_processor()->get_user()->can_rename_files();
        $usercandelete = ($item->is_dir()) ? $this->get_processor()->get_user()->can_delete_folders() : $this->get_processor()->get_user()->can_delete_files();

        $filename = $item->get_name();
        $filename .= (($this->get_processor()->get_shortcode_option('show_ext') === '1' && !empty($item->extension)) ? '.' . $item->get_extension() : '');

        /* View */
        $previewurl = admin_url('admin-ajax.php') . "?action=outofthebox-preview&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken();
        $onclick = "sendGooglePageView('Preview', '" . $item->get_name() . "');";

        if ($this->get_processor()->get_user()->can_preview() && $this->get_processor()->get_shortcode_option('forcedownload') !== '1' && ($item->is_file())) {

            if ($item->get_can_preview_by_cloud() && $this->get_processor()->get_shortcode_option('previewinline') === '1') {
                $html .= "<li><a class='entry_action_view' title='" . __('Preview', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview', 'outofthebox') . "</a></li>";
                $html .= "<li><a href='$previewurl' target='_blank' class='entry_action_external_view' onclick=\"$onclick\" title='" . __('Preview (new window)', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview (new window)', 'outofthebox') . "</a></li>";
            } else if ($item->get_can_preview_by_cloud()) {

                if ($this->get_processor()->get_shortcode_option('previewinline') === '1') {
                    $html .= "<li><a class='entry_action_view' title='" . __('Preview', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview', 'outofthebox') . "</a></li>";
                }
                $html .= "<li><a href='$previewurl' target='_blank' class='entry_action_external_view' onclick=\"$onclick\" title='" . __('Preview (new window)', 'outofthebox') . "'><i class='fa fa-desktop fa-lg'></i>&nbsp;" . __('Preview (new window)', 'outofthebox') . "</a></li>";
            }
        }

        /* Download */
        if (($item->is_file()) && ($this->get_processor()->get_user()->can_download())) {
            $html .= "<li><a href='" . admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken() . "&dl=1' data-filename='" . $filename . "' class='entry_action_download' title='" . __('Download file', 'outofthebox') . "'><i class='fa fa-cloud-download fa-lg'></i>&nbsp;" . __('Download file', 'outofthebox') . "</a></li>";
        }

        /* Shortlink */
        if (($this->get_processor()->get_user()->can_share())) {
            $html .= "<li><a class='entry_action_shortlink' title='" . __('Sharing link', 'outofthebox') . "'><i class='fa fa-group fa-lg'></i>&nbsp;" . __('Sharing link', 'outofthebox') . "</a></li>";
        }

        /* Rename */
        if ($usercanrename) {
            $html .= "<li><a class='entry_action_rename' title='" . __('Rename', 'outofthebox') . "'><i class='fa fa-tag fa-lg'></i>&nbsp;" . __('Rename', 'outofthebox') . "</a></li>";
        }

        /* Delete */
        if ($usercandelete) {
            $html .= "<li><a class='entry_action_delete' title='" . __('Delete', 'outofthebox') . "'><i class='fa fa-times-circle fa-lg'></i>&nbsp;" . __('Delete', 'outofthebox') . "</a></li>";
        }

        if ($html !== '') {
            return "<a class='entry_edit_menu'><i class='fa fa-chevron-circle-down fa-lg'></i></a><div id='menu-" . $item->get_id() . "' class='oftb-dropdown-menu'><ul data-path='" . rawurlencode($item->get_path_display()) . "' data-name=\"" . $item->get_basename() . "\">" . $html . "</ul></div>\n";
        }

        return $html;
    }

    public function renderNewFolder() {
        $html = '';
        if ($this->_search === false) {

            if ($this->get_processor()->get_user()->can_add_folders()) {
                $html .= "<div class='entry folder newfolder'>";
                $html .= "<div class='entry_icon'><img src='" . OUTOFTHEBOX_ROOTPATH . "/css/icons/32x32/folder-new.png'/></div>";
                $html .= "<div class='entry_name'>" . __('Add folder', 'outofthebox') . "</div>";
                $html .= "<div class='entry_description'>" . __('Add a new folder in this directory', 'outofthebox') . "</div>";
                $html .= "</div>";
            }
        }
        return $html;
    }

}
