<?php

namespace TheLion\OutoftheBox;

interface EntryInterface {

    function convert_api_entry($api_entry);

    function to_array();

    function get_id();

    function set_id($id);

    function get_name();

    function set_name($name);

    function get_basename();

    function set_basename($basename);

    function get_path();

    function set_path($path);

    function get_path_display();

    function set_path_display($path_display);

    function get_parent();

    function set_parent($parent);

    function get_children();

    function set_children($children);

    function get_extension();

    function set_extension($extension);

    function get_mimetype();

    function set_mimetype($mimetype);

    function get_is_dir();

    function set_is_dir($is_dir);

    function get_size();

    function set_size($size);

    function get_description();

    function set_description($description);

    function get_last_edited();

    function get_last_edited_str();

    function set_last_edited($last_edited);

    function get_trashed();

    function set_trashed($trashed);

    function get_preview_link();

    function set_preview_link($preview_link);

    function get_download_link();

    function set_download_link($download_link);

    function get_direct_download_link();

    function set_direct_download_link($direct_download_link);

    function get_save_as();

    function set_save_as($save_as);

    function get_can_preview_by_cloud();

    function set_can_preview_by_cloud($can_preview_by_cloud);

    function get_can_edit_by_cloud();

    function set_can_edit_by_cloud($can_edit_by_cloud);

    function get_permissions();

    function set_permissions($permissions);

    function get_thumbnail($key);

    function set_thumbnail($key, $url);

    function has_own_thumbnail();

    function set_has_own_thumbnail($value = true);

    function get_icon();

    function set_icon($icon);

    function get_media();

    function set_media($media);

    function get_additional_data();

    function set_additional_data($additional_data);

    function get_default_icon();
}

abstract class EntryAbstract implements EntryInterface {

    public $id;
    public $name;
    public $basename;
    public $rev;
    public $path;
    public $path_display;
    public $children;
    public $parent;
    public $extension;
    public $mimetype;
    public $is_dir = false;
    public $size;
    public $description;
    public $last_edited;
    public $trashed = false;
    public $preview_link;
    public $download_link;
    public $direct_download_link;
    public $shared_links;
    public $save_as = array();
    public $can_preview_by_cloud = false;
    public $can_edit_by_cloud = false;
    public $permissions = array(
        'canpreview' => false,
        'candelete' => false,
        'canadd' => false,
        'canrename' => false
    );
    public $thumbnails = array(
    );
    public $has_own_thumbnail = false;
    public $icon = false;
    public $backup_icon;
    public $media;
    public $additional_data = array();
    /* Parent folder, only used for displaying the Previous Folder entry */
    public $pf = false;

    public function __construct($api_entry = null) {
        if ($api_entry !== null) {
            $this->convert_api_entry($api_entry);
        }

        $this->backup_icon = $this->get_default_icon();
    }

    public abstract function convert_api_entry($entry);

    public function to_array() {

        $entry = (array) $this;

        /* Remove Unused data */
        unset($entry['parent']);
        //unset($entry['trashed']);
        unset($entry['mimetype']);
        unset($entry['direct_download_link']);
        unset($entry['additional_data']);

        /* Update id to make sure that it can be used in DOM if needed */
        $entry['id'] = urlencode($entry['id']);

        /* Update size */
        $entry['size'] = ($entry['size'] > 0) ? $entry['size'] : '';

        /* Add datetime string for browser that doen't support toLocaleDateString */
        $entry['last_edited_str'] = get_date_from_gmt(date('Y-m-d H:i:s', $entry['last_edited']), get_option('date_format') . ' ' . get_option('time_format'));
    }

    public function get_id() {
        return $this->id;
    }

    public function set_id($id) {
        return $this->id = $id;
    }

    public function get_name() {
        return $this->name;
    }

    public function set_name($name) {
        return $this->name = $name;
    }

    public function get_basename() {
        return $this->basename;
    }

    public function set_basename($basename) {
        return $this->basename = $basename;
    }

    public function get_rev() {
        return $this->rev;
    }

    public function set_rev($rev) {
        return $this->rev = $rev;
    }

    public function get_path() {
        return $this->path;
    }

    public function set_path($path) {
        return $this->path = $path;
    }

    public function get_path_display() {
        return $this->path_display;
    }

    public function set_path_display($path_display) {
        return $this->path_display = $path_display;
    }

    /**
     * 
     * @return \TheLion\OutoftheBox\Entry[]
     */
    public function get_children() {
        return $this->children;
    }

    public function set_children($children) {
        return $this->children = $children;
    }

    public function has_children() {
        return (count($this->children) > 0);
    }

    public function get_parent() {
        return $this->parent;
    }

    public function set_parent($parent) {
        return $this->parent = $parent;
    }

    public function has_parent() {
        return ($this->parent !== '' && $this->parent !== '/');
    }

    public function get_extension() {
        return $this->extension;
    }

    public function set_extension($extension) {
        return $this->extension = $extension;
    }

    public function get_mimetype() {
        return $this->mimetype;
    }

    public function set_mimetype($mimetype) {
        return $this->mimetype = $mimetype;
    }

    public function get_is_dir() {
        return $this->is_dir;
    }

    public function is_dir() {
        return $this->is_dir;
    }

    public function is_file() {
        return !$this->is_dir;
    }

    public function set_is_dir($is_dir) {
        return $this->is_dir = (bool) $is_dir;
    }

    public function get_size() {
        return $this->size;
    }

    public function set_size($size) {
        return $this->size = (int) $size;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_description($description) {
        return $this->description = $description;
    }

    public function get_last_edited() {
        return $this->last_edited;
    }

    public function get_last_edited_str() {
        /* Add datetime string for browser that doen't support toLocaleDateString */
        $last_edited = $this->get_last_edited();
        if (empty($last_edited)) {
            return '';
        }
        return get_date_from_gmt(date('Y-m-d H:i:s', $last_edited), get_option('date_format') . ' ' . get_option('time_format'));
    }

    public function set_last_edited($last_edited) {
        return $this->last_edited = $last_edited;
    }

    public function get_trashed() {
        return $this->trashed;
    }

    public function set_trashed($trashed = true) {
        return $this->trashed = $trashed;
    }

    public function get_preview_link() {
        return $this->preview_link;
    }

    public function set_preview_link($preview_link) {
        return $this->preview_link = $preview_link;
    }

    public function get_download_link() {
        return $this->download_link;
    }

    public function set_download_link($download_link) {
        return $this->download_link = $download_link;
    }

    public function get_direct_download_link() {
        return $this->direct_download_link;
    }

    public function set_direct_download_link($direct_download_link) {
        return $this->direct_download_link = $direct_download_link;
    }

    public function get_shared_links() {
        return $this->shared_links;
    }

    public function set_shared_link_by_visibility($url, $visibility = 'public', $expires = false, $shortened = false) {
        return $this->shared_links[$visibility] = array(
            'url' => $url,
            'shortened' => $shortened,
            'expires' => $expires
        );
    }

    public function get_shared_link_by_visibility($visibility = 'public', $shortened = false) {
        if (!isset($this->shared_links[$visibility])) {
            return null;
        }

        if (
                !empty($this->shared_links[$visibility]['expires']) &&
                $this->shared_links[$visibility]['expires'] < time()
        ) {
            return null;
        }

        if ($shortened !== false) {
            if (empty($this->shared_links[$visibility]['shortend'])) {
                return null;
            }

            return $this->shared_links[$visibility]['shortend'];
        }

        return $this->shared_links[$visibility]['url'];
    }

    public function get_save_as() {
        return $this->save_as;
    }

    public function set_save_as($save_as) {
        return $this->save_as = $save_as;
    }

    public function get_can_preview_by_cloud() {
        return $this->can_preview_by_cloud;
    }

    public function set_can_preview_by_cloud($can_preview_by_cloud) {
        return $this->can_preview_by_cloud = $can_preview_by_cloud;
    }

    public function get_can_edit_by_cloud() {
        return $this->can_edit_by_cloud;
    }

    public function set_can_edit_by_cloud($can_edit_by_cloud) {
        return $this->can_edit_by_cloud = $can_edit_by_cloud;
    }

    public function get_permissions() {
        return $this->permissions;
    }

    public function set_permissions($permissions) {
        return $this->permissions = $permissions;
    }

    public function get_thumbnail($key) {
        if (!isset($this->thumbnails[$key])) {
            return null;
        }

        return $this->thumbnails[$key];
    }

    public function set_thumbnail($key, $url) {
        return $this->thumbnails[$key] = $url;
    }

    public function has_own_thumbnail() {
        return $this->has_own_thumbnail;
    }

    public function set_has_own_thumbnail($value = true) {
        return $this->has_own_thumbnail = $value;
    }

    public function get_icon() {
        return $this->icon;
    }

    public function set_icon($icon) {
        return $this->icon = $icon;
    }

    public function get_media($key = null) {
        if (empty($key)) {
            return $this->media;
        }

        if (!isset($this->media[$key])) {
            return null;
        }

        return $this->media[$key];
    }

    public function set_media($media) {
        return $this->media = $media;
    }

    public function get_additional_data() {
        return $this->additional_data;
    }

    public function get_additional_data_by_key($key) {
        if (isset($this->additional_data[$key])) {
            return $this->additional_data;
        }

        return null;
    }

    public function set_additional_data($additional_data) {
        return $this->additional_data = $additional_data;
    }

    public function set_additional_data_by_key($key, $data) {
        return $this->additional_data[$key] = $data;
    }

    public function is_parent_folder() {
        return $this->pf;
    }

    public function set_parent_folder($value) {
        return $this->pf = (bool) $value;
    }

    public function get_default_icon() {
        $icon_location = realpath(__DIR__ . '/../../css/icons/mimetype') . '/96x96/' . strtoupper($this->get_extension()) . '.png';
        if ($this->is_dir() || $this->is_parent_folder()) {
            $icon = 'FOLDER.png';
        } elseif (file_exists($icon_location)) {
            $icon = strtoupper($this->get_extension()) . '.png';
        } else {
            $icon = 'HLP.png';
        }
    }

}

class Entry extends EntryAbstract {

    /**
     * 
     * @param \Kunnu\Dropbox\Models\FolderMetadata|\Kunnu\Dropbox\Models\FileMetadata $api_entry
     */
    public function convert_api_entry($api_entry) {

        /* Normal Meta Data */
        $this->set_id($api_entry->id);
        $this->set_rev($api_entry->rev);
        $this->set_name($api_entry->name);

        if ($api_entry instanceof \Kunnu\Dropbox\Models\FolderMetadata) {
            $this->set_is_dir(true);
        }

        $pathinfo = \TheLion\OutoftheBox\Helpers::get_pathinfo($api_entry->path_lower);
        if ($this->is_file() && isset($pathinfo['extension'])) {
            $this->set_extension(strtolower($pathinfo['extension']));
        }
        $this->set_mimetype_from_extension();

        if ($this->is_file()) {
            $this->set_basename(str_replace('.' . $this->get_extension(), '', $this->get_name()));
        } else {
            $this->set_basename($this->get_name());
        }

        $this->set_size(($this->is_dir()) ? 0 : $api_entry->size);

        if ($this->is_file() && is_string($api_entry->server_modified)) {
            $dtime = \DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $api_entry->server_modified, new \DateTimeZone('UTC'));
            $this->set_last_edited($dtime->getTimestamp());
        }

        $this->set_path($api_entry->path_lower);
        $this->set_path_display($api_entry->path_display);

        if ($api_entry->path_lower !== '') {
            $this->set_parent($pathinfo['dirname']);
        }

        /* Can File be previewed via Dropbox? 
         * https://www.dropbox.com/developers/core/docs#thumbnails
         */
        $previewsupport = array('pdf', 'txt', 'doc', 'docx', 'docm', 'ppt', 'pps', 'ppsx', 'ppsm', 'pptx', 'pptm', 'xls', 'xlsx', 'xlsm', 'rtf', 'jpg', 'jpeg', 'gif', 'png', 'mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga');
        $openwithdropbox = (in_array($this->get_extension(), $previewsupport));
        if ($openwithdropbox) {
            $this->set_can_preview_by_cloud(true);
        }

        /* Set the permissions */
        $permissions = array(
            'canpreview' => $openwithdropbox,
            'candownload' => true,
            'candelete' => true,
            'canadd' => true,
            'canrename' => true,
        );
        $this->set_permissions($permissions);

        /* Icon */
        $default_icon = $this->get_default_icon();
        $this->set_icon($default_icon);

        /* Thumbnail */
        $can_always_create_thumbnail_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $can_always_create_thumbnail = (in_array($this->get_extension(), $can_always_create_thumbnail_extensions));

        $mediadata = array();

        if (
                $can_always_create_thumbnail ||
                ($this->is_file() && isset($api_entry->media_info) && $api_entry->getMediaInfo() !== null)
        ) {
            /* Set dimensions is avaiable */
            $mediadata['width'] = null;
            $mediadata['height'] = null;

            $this->set_has_own_thumbnail(true);
        }

        if ($this->is_file()) {

            $has_media_info = ($api_entry->getMediaInfo() instanceof \Kunnu\Dropbox\Models\MediaInfo);
            $has_media_data = false;
            if ($has_media_info) {
                $has_media_data = ($api_entry->getMediaInfo()->getMediaMetadata() instanceof \Kunnu\Dropbox\Models\MediaMetadata);
            }

            if ($has_media_info && $has_media_data) {
                $dimensions = $api_entry->getMediaInfo()->getMediaMetadata()->getDimensions();
                if (!empty($dimensions)) {
                    $mediadata['width'] = $dimensions['width'];
                    $mediadata['height'] = $dimensions['height'];
                }

                if ($api_entry->getMediaInfo()->getMediaMetadata() instanceof \Kunnu\Dropbox\Models\VideoMetadata) {
                    $mediadata['duration'] = $api_entry->getMediaInfo()->getMediaMetadata()->getDuration();
                    $this->set_has_own_thumbnail(false);
                }
            }
        }

        $this->set_media($mediadata);

        $additional_data = array();
        $this->set_additional_data($additional_data);
    }

    public function set_mimetype_from_extension() {
        if ($this->is_dir()) {
            return null;
        }

        if (empty($this->extension)) {
            return null;
        }
        include_once 'mime-types/mime-types.php';
        $mimetype = getMimeType($this->get_extension());
        $this->set_mimetype($mimetype);
    }

    public function get_default_icon() {

        $icon = 'unknown';
        $mimetype = $this->get_mimetype();
        if ($this->is_dir()) {
            $icon = 'folder';
        } else if (strpos($mimetype, 'word') !== false) {
            $icon = 'application-msword';
        } else if (strpos($mimetype, 'excel') !== false || strpos($mimetype, 'spreadsheet') !== false) {
            $icon = 'application-vnd.ms-excel';
        } else if (strpos($mimetype, 'powerpoint') !== false || strpos($mimetype, 'presentation') !== false) {
            $icon = 'application-vnd.ms-powerpoint';
        } else if (strpos($mimetype, 'access') !== false || strpos($mimetype, 'mdb') !== false) {
            $icon = 'application-vnd.ms-access';
        } else if (strpos($mimetype, 'image') !== false) {
            $icon = 'image-x-generic';
        } else if (strpos($mimetype, 'audio') !== false) {
            $icon = 'audio-x-generic';
        } else if (strpos($mimetype, 'video') !== false) {
            $icon = 'video-x-generic';
        } else if (strpos($mimetype, 'pdf') !== false) {
            $icon = 'application-pdf';
        } else if (strpos($mimetype, 'zip') !== false ||
                strpos($mimetype, 'archive') !== false ||
                strpos($mimetype, 'tar') !== false ||
                strpos($mimetype, 'compressed') !== false
        ) {
            $icon = 'application-zip';
        } else if (strpos($mimetype, 'html') !== false) {
            $icon = 'text-xml';
        } else if (strpos($mimetype, 'application/exe') !== false ||
                strpos($mimetype, 'application/x-msdownload') !== false ||
                strpos($mimetype, 'application/x-exe') !== false ||
                strpos($mimetype, 'application/x-winexe') !== false ||
                strpos($mimetype, 'application/msdos-windows') !== false ||
                strpos($mimetype, 'application/x-executable') !== false
        ) {
            $icon = 'application-x-executable';
        } else if (strpos($mimetype, 'text') !== false) {
            $icon = 'text-x-generic';
        }

        return OUTOFTHEBOX_ROOTPATH . '/css/icons/32x32/' . $icon . '.png';
    }

    public function get_icon_large() {
        return str_replace('32x32', '128x128', $this->get_icon());
    }

}
