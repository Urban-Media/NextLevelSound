<?php

class Vimeography_Core_Pro extends Vimeography_Core {

  /**
   * Page number of videos to retrieve from Vimeo
   *
   * @var integer
   */
  private $_page = 1;

  /**
   * Number of videos to fetch for each page.
   *
   * @var integer
   */
  private $_per_page = 50;

  /**
   * Method to sort by: date, likes, comments, plays, alphabetical, duration
   *
   * @var string
   */
  private $_sort = 'date';

  /**
   * Descending or ascending order
   *
   * @var string
   */
  private $_direction = 'desc';


  /**
   * [$_containing_uri description]
   * @var null
   */
  private $_containing_uri = NULL;


  /**
   * Instantiates the Vimeo library with the user's
   * stored access token and sets the Vimeo source type for the request.
   */
  public function __construct($settings) {
    parent::__construct($settings);

    if ( ( $this->_auth = get_option('vimeography_pro_access_token') ) === FALSE ) {
      throw new Vimeography_Exception(
        sprintf(
          __('Visit the <a href="%1$s" title="Vimeography Pro">Vimeography Pro</a> page to finish setting up Vimeography Pro.', 'vimeography-pro'),
          network_admin_url('admin.php?page=vimeography-pro')
        )
      );
    }

    $this->_auth = apply_filters('vimeography-pro/edit-access-token', $this->_auth);
    $this->_vimeo = new Vimeography_Vimeo(NULL, NULL, $this->_auth);
    $this->_vimeo->set_user_agent( sprintf( 'Vimeography loves you (%s)', home_url() ) );

    add_filter( 'vimeography.request.fields', array( $this, 'add_request_fields') );

    // Only perform if the query string does not exist in the source.
    $query = parse_url($settings['source'], PHP_URL_QUERY);

    if ( empty( $query ) ) {
      if ( isset( $settings['page'] ) ) {
        $this->_page      = $settings['page'];
      }

      // This must stay set in case the incoming request is an AJAX request for a
      // page that isn't in the current cache.
      if ( isset( $settings['remote_per_page'] ) ) {
        $this->_per_page      = $settings['remote_per_page'];
      }

      if ( isset( $settings['sort'] ) ) {
        $this->_sort      = $settings['sort'];
      }

      if ( isset( $settings['direction'] ) ) {
        $this->_direction = $settings['direction'];
      }

      if ( isset( $settings['containing_uri'] ) ) {
        $this->_containing_uri = $settings['containing_uri'];
      }

      $this->_params = $this->_add_request_params();
    }
  }

  /**
   * Get the videos paginated.
   *
   * Let's fetch the cache, and if the page is inside the cache, use the cache. If the page is out of the cache, fetch
   * a new page. If the cache has part of the page, ignore the cache, we needed to fetch the rest anyway.
   *
   * @param string $gallery_id            Pulls the correct cache file
   * @param number $page                  Which page to fetch
   * @param number $per_page              How many elements to return
   *
   * @return array
   */
  public function get_video_page($gallery_id, $page = 1, $per_page = 50, $limit = 25) {
    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    $cache = new Vimeography_Cache($gallery_id);

    if ( $cache->exists() ) {
      $data = $cache->get();

      $start = 1 + ($page * $per_page) - $per_page;
      $end   =      $page * $per_page;

      if ($limit != 0 AND $end > $limit) {
        $end = $limit;
      }

      // If we have the requested video range in the cache...
      if ( count( $data->video_set ) >= $end) {
        // return the video range...
        $data->video_set = array_slice($data->video_set, $start - 1, $per_page);

        // and cancel out the cached paging. We won't use it because
        // the per page from the initial request is always set to 50.
        $data->paging = new stdClass;

      } else {
        // Go get the requested range.
        $data = $this->fetch($last_modified = NULL);
      }
    } else {
      // Go get the requested range.
      $data = $this->fetch($last_modified = NULL);
    }

    // Return the results to be rendered in the AJAX class.
    return $data;
  }


  /**
   * Verifies that the endpoint is a valid Vimeo resource.
   *
   * @param  string $resource [description]
   * @return [type]           [description]
   */
  protected function _verify_vimeo_endpoint($resource) {
    return preg_match('#^/(users|channels|albums|groups|portfolios|categories|tags)/(.+)#', $resource);
  }

  /**
   * Adds the common request data to the current Vimeo request.
   *
   * The only Vimeo method that _does not_ require these parameters
   * is for a singular video request (vimeo.videos.getInfo)
   *
   * @return array The params of the current request
   */
  private function _add_request_params() {
    $params = array(
      'page'      => $this->_page,
      'per_page'  => $this->_per_page,
      'sort'      => $this->_sort,
    );

    // Direction is not a valid parameter
    // if the sort order is set to default
    if ( $params['sort'] !== 'default' ) {
      $params['direction'] = $this->_direction;
    }

    if ( $this->_containing_uri ) {
      $params['containing_uri'] = $this->_containing_uri;
    }

    return $params;
  }

  /**
   * Request additional fields from the Vimeo API.
   *
   * @param array $fields The fields requested by Vimeography Lite.
   */
  public function add_request_fields( $fields ) {
    $fields[] = 'download';
    return $fields;
  }
}