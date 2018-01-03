<?php

namespace TheLion\OutoftheBox;

class Mediaplayer {

    /**
     *
     * @var \TheLion\OutoftheBox\Processor 
     */
    private $_processor;

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

    public function get_media_list() {

        $this->_folder = $this->get_processor()->get_client()->get_folder(null, true, false, true, false);

        if (($this->_folder !== false)) {
            //Create Gallery array
            $this->mediaarray = $this->createMediaArray();

            if (count($this->mediaarray) > 0) {
                $response = json_encode($this->mediaarray);

                $cached_request = new CacheRequest($this->get_processor());
                $cached_request->add_cached_response($response);

                echo $response;
            }
        }

        die();
    }

    public function createMediaArray() {
        $playlist = array();

        //Create Filelist array
        if ($this->_folder->has_children()) {

            $files = array();

            foreach ($this->_folder->get_children() as $child) {

                if (($child->is_dir())) {
                    continue;
                }


                $allowedextensions = array('mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga');
                if (empty($child->extension) || !in_array($child->extension, $allowedextensions)) {
                    continue;
                }

                // combine same files with different extensions
                if (!isset($files[$child->get_basename()])) {

                    $song_name = ltrim(str_replace($child->get_name(), $child->get_basename(), $child->get_path_display()), '/');

                    $files[$child->get_basename()] = array(
                        'item' => $child,
                        'title' => $song_name,
                        'name' => $child->get_basename(),
                        'artist' => '',
                        'path' => $child->get_path(),
                        'file' => $child->get_path_display(),
                        'poster' => OUTOFTHEBOX_ROOTPATH . '/css/images/play.png',
                        'extensions' => array(),
                        'download' => false,
                        'linktoshop' => ($this->get_processor()->get_shortcode_option('linktoshop') !== '') ? $this->get_processor()->get_shortcode_option('linktoshop') : false
                    );
                }

                array_push($files[$child->get_basename()]['extensions'], $child->get_extension());
            }


            foreach ($files as $file) {
                $song = $file;

                foreach ($song['extensions'] as $song_extension) {
                    //Can play mp4 but need to give m4v or m4a
                    if ($song_extension === 'mp4') {
                        $song_extension = ($this->get_processor()->get_shortcode_option('mode') === 'audio') ? 'm4a' : 'm4v';
                    }
                    if ($song_extension === 'ogg') {
                        $song_extension = ($this->get_processor()->get_shortcode_option('mode') === 'audio') ? 'oga' : 'ogv';
                    }

                    $url = admin_url('admin-ajax.php') . "?action=outofthebox-stream&OutoftheBoxpath=" . rawurlencode($song['path']) . "&lastpath=" . rawurlencode($this->get_processor()->get_last_path()) . "&listtoken=" . $this->get_processor()->get_listtoken();
                    if ($this->get_processor()->get_client()->has_temporarily_link($song['item'])) {
                        $url = $this->get_processor()->get_client()->get_temporarily_link($song['item']);
                    } elseif ($this->get_processor()->get_client()->has_shared_link($song['item'])) {
                        $url = $this->get_processor()->get_client()->get_shared_link($song['item']);
                        $url .= '?raw=1';
                    }

                    $song[$song_extension] = $url;
                    if ($this->get_processor()->get_shortcode_option('linktomedia') === '1') {
                        $song['download'] = str_replace('outofthebox-stream', 'outofthebox-download', $song[$song_extension]);
                    }
                }

                unset($song['item']);
                unset($song['path']);
                array_push($playlist, $song);
            }
        }

        return $playlist;
    }

}
