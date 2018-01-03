<?php

namespace TheLion\OutoftheBox;

class CacheNode implements \Serializable {

    /**
     * ID of the Node = ID of the Cached Entry
     * @var string 
     */
    private $_id;
    private $_rev;
    private $_shared_links;
    private $_temporarily_link;

    function __construct($params = null) {
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }
    }

    public function serialize() {
        $data = array(
            '_id' => $this->_id,
            '_rev' => $this->_rev,
            '_shared_links' => $this->_shared_links,
            '_temporarily_link' => $this->_temporarily_link,
        );
        return serialize($data);
    }

    public function unserialize($data) {
        $values = unserialize($data);
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    public function get_id() {
        return $this->_id;
    }

    public function get_rev() {
        return $this->_rev;
    }

    public function set_rev($rev) {
        return $this->_rev = $rev;
    }

    public function add_temporarily_link($link, $expires = null) {
        
        if (empty($expires)){
            $expires = time() + (4 * 60 * 60);
        }
        
        $this->_temporarily_link = array(
            'url' => $link,
            'expires' => $expires
        );
    }

    public function get_temporarily_link() {
        if (!isset($this->_temporarily_link['url']) || empty($this->_temporarily_link['url'])) {
            return false;
        }

        if (!(empty($this->_temporarily_link['expires'])) && $this->_temporarily_link['expires'] < time() + 60) {
            return false;
        }

        return $this->_temporarily_link['url'];
    }

    /**
     * 
     * @param \Kunnu\Dropbox\Models\FileLinkMetaData|\Kunnu\Dropbox\Models\FolderLinkMetaData $shared_link_info
     */
    public function add_shared_link($shared_link_info) {
        $this->_shared_links[$shared_link_info->getLinkPermissions()->getResolvedVisibility()] = array(
            'url' => str_replace('?dl=0', '', $shared_link_info->getUrl()),
            //'permissions' => $shared_link_info->getLinkPermissions(),
            'expires' => $shared_link_info->getExpires()
        );

        return $this->get_shared_link($shared_link_info->getLinkPermissions()->getResolvedVisibility());
    }

    public function get_shared_link($visibility = 'public') {
        if (!isset($this->_shared_links[$visibility])) {
            return false;
        }

        if (!(empty($this->_shared_links[$visibility]['expires'])) && $this->_shared_links[$visibility]['expires'] < time() + 60) {
            return false;
        }

        return $this->_shared_links[$visibility]['url'];
    }

}
