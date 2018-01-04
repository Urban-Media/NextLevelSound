<?php

namespace Vinkas\Widgets\Discourse;

use Vinkas\Widgets\Discourse_Widget;

class Topics_Widget extends Discourse_Widget {

  /**
  * Sets up the widgets name etc
  */
  public function __construct() {
    parent::__construct( 'topics', 'Topics', 'Discourse topics widget' );
  }

  /**
  * Outputs the content of the widget
  *
  * @param array $args
  * @param array $instance
  */
  public function widget( $args, $instance ) {
    extract($args);
    $url = ! empty( $instance['url'] ) ? $instance['url'] : '';
    $filter = ! empty( $instance['filter'] ) ? $instance['filter'] : 'latest';
    $count = ! empty( $instance['count'] ) ? $instance['count'] : '10';

    while ( stristr($url, 'http') != $url )
    $url = substr($url, 1);

    if ( empty($url) )
    return;

    if(false === ($data = get_transient($this->id) ) ) {

      $args = array(
        'timeout'     => 200,
        'redirection' => 5,
        'headers'     => array(),
        'cookies'     => array(),
        'body'        => null,
        'sslverify'   => false,
        'stream'      => false,
        'filename'    => null,
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '',
        'after_title'   => ''
      );

      $json = wp_remote_get($url . '/' . $filter . '.json', $args);

      if ( is_wp_error($json) ) {
        echo $json->get_error_message();
        return;
      }

      $data = json_decode( wp_remote_retrieve_body($json) );
      set_transient($this->id, $data, 60 * $this->cacheTime);
    }

    $topics = $data->topic_list->topics;
    if(count($topics) == 0) {
      return;
    }

    ?>

    <div class="section_sideblock nls_course_nav grey_block">
      <div class="section_centre_circle circle_icon">
        <img src="<?php echo get_template_directory_uri(); ?>/img/speech.png">
      </div>
      <h2 class="sideblock_title text-center black_text forum_title">
        <?php
        echo apply_filters( 'widget_title', $instance['title'] );
        ?>
      </h2>

      <?php

      echo "<ul class='discourse_topic_list'>";
      $i = 0;
      foreach ($topics as $topic) { //var_dump($topic);
        if($i >= $count) {
          break;
        }
        if($topic->pinned_globally == false) {
          $topic_url = $url . "/t/" . $topic->slug . "/" . $topic->id;
          echo '<li class="discourse_topic"><a class="greyer_text forum_topic" target="_blank" rel="noopener" href="' . $topic_url . '">' . $topic->title . '</a> <span class="discourse_topic_author section_subtitle">@' . $topic->last_poster_username . '</span><hr class="nls_discourse_hr"></li>';
          $i += 1;
        }
      }
      echo "</ul>";
      ?>
      <a href="https://mixmasterforum.com/" target="_blank" rel="noopener" class="sideblock_link">
        <div class="nls_rounded_button white_button black_text uppercase text-center sideblock_button">
          View Forum
        </div>
      </a>
      <?php
      echo $args['after_widget'];
      ?>

    </div>
    <?php
    /*
    <div class="section_sideblock nls_course_nav orange_block">
      <div class="section_centre_circle">
        <img src="<?php echo get_template_directory_uri(); ?>/img/open-book.png" class="circle_icon">
      </div>
      <h2 class="sideblock_title text-center white_text">
        Download Course Workbook
      </h2>
      <a href="<?php echo 'hi'; ?>" class="sideblock_link">
        <div class="nls_rounded_button white_button orange_text uppercase text-center sideblock_button">
          Download
        </div>
      </a>
    </div>
    */

  }

  /**
  * Outputs the options form on admin
  *
  * @param array $instance The widget options
  */
  public function form( $instance ) {
    if( $instance) {
      $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Latest Topics', 'wp-discourse-widgets' );
      $url = $instance['url'];
      $count = $instance['count'];
      $filter = $instance['filter'];
    } else {
      $title = __( 'Latest Topics', 'wp-discourse-widgets' ); $url = ''; $count = 10; $filter = 'latest';
    }
    ?>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>"><?php _e( esc_attr( 'Discourse URL:' ) ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>">
    </p>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">How many topics would you like to display?</label>
      <select id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>">
        <?php
        for ($i = 1; $i <= 30; $i++) {
          $selected = ($count == $i ? ' selected' : '');
          echo '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
        }
        ?>
      </select>
    </p>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>">Filter topics by:</label>
      <select id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ); ?>">
        <option value="latest"<?php echo $filter == "latest" ? " selected" : "" ?>>Latest</option>
        <option value="new"<?php echo $filter == "new" ? " selected" : "" ?>>New</option>
        <option value="top"<?php echo $filter == "top" ? " selected" : "" ?>>Top</option>
      </select>
    </p>
    <?php
  }

  /**
  * Processing widget options on save
  *
  * @param array $new_instance The new options
  * @param array $old_instance The previous options
  */
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['url'] = ( ! empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';
    $instance['count'] = ( ! empty( $new_instance['count'] ) ) ? $new_instance['count'] : 10;
    $instance['filter'] = ( ! empty( $new_instance['filter'] ) ) ? $new_instance['filter'] : 'latest';

    if($old_instance['url'] != $new_instance['url'] || $old_instance['filter'] != $new_instance['filter']) {
      delete_transient( $this->id );
    }

    return $instance;
  }

}
