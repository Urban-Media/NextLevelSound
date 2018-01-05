<?php

namespace TheLion\OutoftheBox;

class Gallery {

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

    public function get_images_list() {
        $recursive = ($this->get_processor()->get_shortcode_option('folderthumbs') === '1');
        $this->_folder = $this->get_processor()->get_client()->get_folder(null, true, true, $recursive);

        if (($this->_folder !== false)) {
            $this->renderImagesList();
        }
    }

    public function search_image_files() {
        $this->_search = true;
        $input = mb_strtolower($_REQUEST['query'], 'UTF-8');
        $this->_folder = $this->get_processor()->get_client()->search($input);

        if (($this->_folder !== false)) {
            $this->renderImagesList();
        }
    }

    public function renderImagesList() {

        /* Create HTML Filelist */
        $imageslist_html = "";

        // Add 'back to Previous folder' if needed
        if (($this->_search === false) && (strtolower($this->_folder->get_path()) !== strtolower($this->get_processor()->get_root_folder()))) {
            $foldername = basename($this->_folder->get_path());
            $location = str_replace('\\', '/', (dirname($this->get_processor()->get_requested_path())));

            $parent_folder_entry = new Entry();
            $parent_folder_entry->set_id('Previous Folder');
            $parent_folder_entry->set_name(__('Previous folder', 'outofthebox'));
            $parent_folder_entry->set_path($location);
            $parent_folder_entry->set_path_display($location);
            $parent_folder_entry->set_is_dir(true);
            $parent_folder_entry->set_parent_folder(true);
            $parent_folder_entry->set_icon(OUTOFTHEBOX_ROOTPATH . '/css/icons/128x128/folder-grey.png');
        }


        if ($this->get_processor()->get_shortcode_option('max_files') !== '-1' && $this->_folder->has_children()) {
            $children = $this->_folder->get_children();
            $children_sliced = array_slice($children, 0, (int) $this->get_processor()->get_shortcode_option('max_files'));
            $this->_folder->set_children($children_sliced);
        }

        if ($this->_folder->has_children()) {
            $hasfilesorfolders = false;

            $imageslist_html = "<div class='images image-collage'>";
            foreach ($this->_folder->get_children() as $item) {
                /* Render folder div */
                if ($item->is_dir()) {
                    $imageslist_html .= $this->renderDir($item);


                    if (!$item->is_parent_folder()) {
                        $hasfilesorfolders = true;
                    }
                }
            }
        }

        $imageslist_html .= $this->renderNewFolder();

        if ($this->_folder->has_children()) {
            $i = 0;
            foreach ($this->_folder->get_children() as $item) {

                /* Render file div */
                if ($item->is_file()) {
                    $hidden = (($this->get_processor()->get_shortcode_option('maximages') !== '0') && ($i >= $this->get_processor()->get_shortcode_option('maximages')));
                    $imageslist_html .= $this->renderFile($item, $hidden);
                    $hasfilesorfolders = true;
                    $i++;
                }
            }

            $imageslist_html .= "</div>";
        } else {
            if ($this->_search === true) {
                $imageslist_html .= '<div class="no_results">' . __('No files or folders found', 'outofthebox') . '</div>';
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
            if ($userfolder_name !== false) {
                $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . $userfolder_name . "</a>";
            } else {
                $startelement = "<a href='javascript:void(0)' class='folder' data-url='" . rawurlencode('/') . "'>" . $this->get_processor()->get_shortcode_option('root_text') . "</a>";
            }


            array_unshift($breadcrumbelements, $startelement);
        }

        $filepath = implode($spacer, $breadcrumbelements);

        if ($this->_search === true) {
            $expires = 0;
        } else {
            $expires = time() + 60 * 5;
        }

        $response = json_encode(array(
            'lastpath' => rawurlencode($this->get_processor()->get_last_path()),
            'breadcrumb' => $filepath,
            'html' => $imageslist_html,
            'expires' => $expires));

        $cached_request = new CacheRequest($this->get_processor());
        $cached_request->add_cached_response($response);

        echo $response;

        die();
    }

    public function renderDir(Entry $item) {
        $return = "";

        $target_height = $this->get_processor()->get_shortcode_option('targetheight');

        if ($item->is_parent_folder()) {
            $return .= "<div class='image-container image-folder' data-url='" . rawurlencode($item->get_path_display()) . "' data-name='" . $item->get_basename() . "'>";
        } else {
            $classmoveable = ($this->get_processor()->get_user()->can_move()) ? 'moveable' : '';
            $return .= "<div class='image-container image-folder entry $classmoveable' data-url='" . rawurlencode($item->get_path_display()) . "' data-name='" . $item->get_basename() . "'>";

            $return .= "<div class='entry_edit'>";
            $return .= $this->renderEditItem($item);

            if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_folders()) {
                $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item->get_basename()) . "'/></div>";
            }
            $return .= "</div>";
        }
        $return .= "<a title='" . $item->get_name() . "'>";
        $return .= "<div class='preloading'></div>";
        $return .= "<img class='preloading image-folder-img' src='" . OUTOFTHEBOX_ROOTPATH . "/css/images/transparant.png' data-src='" . plugins_url('css/images/folder.png', dirname(__FILE__)) . "' width='$target_height' height='$target_height' style='width:{$target_height}px !important;height:{$target_height}px !important; '/>";

        if ($this->get_processor()->get_shortcode_option('folderthumbs') === '1') {
            $i = 1;
            if ($item->has_children()) {
                foreach ($item->get_children() as $folder_child) {
                    if ($i > 3) {
                        break;
                    }

                    if ($folder_child->has_own_thumbnail() === false) {
                        continue;
                    }

                    $thumbnail_url = $this->get_processor()->get_client()->get_thumbnail($folder_child, true, $target_height * 1.5, $target_height * 1.5, true);
                    $return .= "<div class='folder-thumb thumb$i' style='width:" . $target_height . "px;height:" . $target_height . "px;background-image: url(" . $thumbnail_url . ")'></div>";

                    $i++;
                }
            }
        }



        $return .= "<div class='folder-text'>" . $item->get_name() . "</div></a>";

        $return .= "</div>\n";

        return $return;
    }

    public function renderFile(Entry $item, $hidden = false) {

        $hidden_class = ($hidden) ? 'hidden-for-gallery' : '';
        $target_height = $this->get_processor()->get_shortcode_option('targetheight');
        /* Search API call doesn't return image sizes... grrr, so in that case crop the image) */
        $thumbnail_url = $this->get_processor()->get_client()->get_thumbnail($item, true, 0, $target_height * 1.5, ($this->_search) ? true : false);

        if ((!empty($_REQUEST['deeplink'])) && (md5($item->get_id()) === $_REQUEST['deeplink'])) {
            $class .= ' deeplink';
        }

        $classmoveable = ($this->get_processor()->get_user()->can_move()) ? 'moveable' : '';
        $return = "<div class='image-container $hidden_class entry $classmoveable' data-url='" . rawurlencode($item->get_path_display()) . "' data-name='" . $item->get_name() . "'>";

        $return .= "<div class='entry_edit'>";
        $return .= $this->renderEditItem($item);

        if ($this->get_processor()->get_user()->can_download_zip() || $this->get_processor()->get_user()->can_delete_files()) {
            $return .= "<div class='entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='" . rawurlencode($item->get_name()) . "'/></div>";
        }

        $return .= "</div>";

        $thumbnail = 'data-options="thumbnail: \'' . $thumbnail_url . '\'"';
        $class = 'ilightbox-group';
        $target = '';

        $url = admin_url('admin-ajax.php') . "?action=outofthebox-preview&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken();
        if ($this->get_processor()->get_client()->has_shared_link($item)) {
            $url = $this->get_processor()->get_client()->get_shared_link($item);
            $url = $url . '?raw=1';
        } elseif ($this->get_processor()->get_client()->has_temporarily_link($item)) {
            $url = $this->get_processor()->get_client()->get_temporarily_link($item);
        }

        /* If previewinline attribute is set, open image in new window */
        if ($this->get_processor()->get_shortcode_option('previewinline') === '0') {
            $url = str_replace('?dl=1', '?raw=1', $url);
            $class = '';
            $target = ' target="_blank" ';
        }

        $download_url = admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken() . "&dl=1";
        $caption = ($this->get_processor()->get_user()->can_download()) ? '<a href="' . $download_url . '" title="' . __('Download file', 'outofthebox') . '"><i class="fa fa-arrow-circle-down" aria-hidden="true"></i></a>&nbsp' : '';
        $caption .= htmlspecialchars($item->get_name(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES);

        $return .= "<a href='" . $url . "' title='" . htmlspecialchars($item->get_name(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES) . "' $target class='$class' data-type='image' data-caption='$caption' $thumbnail rel='ilightbox[" . $this->get_processor()->get_listtoken() . "]'><span class='image-rollover'></span>";

        /* Search API call doesn't return image sizes... grrr, so in that case crop the image) */
        $height = $target_height;
        $width = ($this->_search === false && ($item->get_media('width') > 0) && ($item->get_media('height') > 0)) ? round(($target_height / $item->get_media('height')) * $item->get_media('width')) : $target_height;
        $return .= "<div class='preloading'></div>";
        $return .= "<img class='preloading $hidden_class' src='" . OUTOFTHEBOX_ROOTPATH . "/css/images/transparant.png' data-src='" . $thumbnail_url . "' width='$width' height='$height' style='width:{$width}px !important;height:{$height}px !important; '/>";

        $text = '';
        if ($this->get_processor()->get_shortcode_option('show_filenames') === '1') {
            $text = $item->get_basename();

            $text = apply_filters('outofthebox_gallery_entry_text', $text, $item, $this);
            $return .= "<div class='entry-text'>" . $text . "</div>";
        }

        $return .= "</a>";

        $return .= "</div>\n";
        return $return;
    }

    public function renderEditItem(Entry $item) {
        $html = '';

        $usercanrename = ($item->is_dir()) ? $this->get_processor()->get_user()->can_rename_folders() : $this->get_processor()->get_user()->can_rename_files();
        $usercandelete = ($item->is_dir()) ? $this->get_processor()->get_user()->can_delete_folders() : $this->get_processor()->get_user()->can_delete_files();


        /* Download */
        if (($item->is_file()) && ($this->get_processor()->get_user()->can_download())) {
            $html .= "<li><a href='" . admin_url('admin-ajax.php') . "?action=outofthebox-download&OutoftheBoxpath=" . rawurlencode($item->get_path()) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken() . "&dl=1' class='entry_action_download' title='" . __('Download file', 'outofthebox') . "'><i class='fa fa-cloud-download fa-lg'></i>&nbsp;" . __('Download file', 'outofthebox') . "</a></li>";
        }

        /* Shortlink */
        if ($item->is_file() && ($this->get_processor()->get_user()->can_share())) {
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
            return "<a class='entry_edit_menu'><i class='fa fa-chevron-circle-down fa-lg'></i></a><div id='menu-" . $item->get_id() . "' class='oftb-dropdown-menu'><ul data-path='" . rawurlencode($item->get_path_display()) . "' data-name='" . $item->get_basename() . "'>" . $html . "</ul></div>\n";
        }

        return $html;
    }

    public function renderNewFolder() {
        $html = '';
        if ($this->_search === false) {

            if ($this->get_processor()->get_user()->can_add_folders()) {
                $height = $this->get_processor()->get_shortcode_option('targetheight');
                $html .= "<div class='image-container image-folder image-add-folder grey newfolder'>";
                $html .= "<a title='" . __('Add folder', 'outofthebox') . "'><div class='folder-text'>" . __('Add folder', 'outofthebox') . "</div>";
                $html .= "<img class='preloading' src='" . OUTOFTHEBOX_ROOTPATH . "/css/images/transparant.png' data-src='" . plugins_url('css/images/folder.png', dirname(__FILE__)) . "' width='$height' height='$height' style='width:" . $height . "px;height:" . $height . "px;'/>";
                $html .= "</a>";
                $html .= "</div>\n";
            }
        }
        return $html;
    }

}
