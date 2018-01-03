<?php
/*
Plugin Name: NLS Course Work Plugin
Plugin URI:
Description: Adds course work download functionality to courses, sections and lessons
Version: 1.0
Author: Urban Media
Author URI: http://www.urbanmedia.co.uk
*/

/**
 * Register the widget
 */
add_action('widgets_init', create_function('', 'return register_widget("NLS_Course_Work");'));

/**
 * Class NLS_Course_Work
 */
class NLS_Course_Work extends WP_Widget
{
	/** Basic Widget Settings */
	const WIDGET_NAME = "NLS Course Work";
	const WIDGET_DESCRIPTION = "Adds course work download functionality for course/section/lesson sidebars";

	var $textdomain;
	var $fields;

	/**
	 * Construct the widget
	 */
	function __construct()
	{
    /*
     * Since this plugin revolves around getting Advanced Custom Field values
     * to display in widgets, ACF is a hard dependency and this plugin does
     * not work without it installed
     */
    if (!is_plugin_active('advanced-custom-fields/acf.php')) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
      wp_die( __( 'NLS Course Work requires Advanced Custom Fields to be installed and activated.', 'NLS_Course_Work' ) );
    }

		//We're going to use $this->textdomain as both the translation domain and the widget class name and ID
		$this->textdomain = strtolower(get_class($this));

		//Figure out your textdomain for translations via this handy debug print
		//var_dump($this->textdomain);

		//Add fields
		$this->add_field('title', 'Enter title', '', 'text');
		//$this->add_field('example_field', 'Example field', 'This is the default value', 'text');

		//Translations
		load_plugin_textdomain($this->textdomain, false, basename(dirname(__FILE__)) . '/languages' );

		//Init the widget
		parent::__construct($this->textdomain, __(self::WIDGET_NAME, $this->textdomain), array( 'description' => __(self::WIDGET_DESCRIPTION, $this->textdomain), 'classname' => $this->textdomain));
	}

	/**
	 * Widget frontend
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance)
	{
		$title = apply_filters('widget_title', $instance['title']);

		/* Before and after widget arguments are usually modified by themes */
		echo $args['before_widget'];

		if (!empty($title))
			echo $args['before_title'] . $title . $args['after_title'];

		/* Widget output here */
		$this->widget_output($args, $instance);

		/* After widget */
		echo $args['after_widget'];
	}

	/**
	 * This function will execute the widget frontend logic.
	 * Everything you want in the widget should be output here.
	 */
	private function widget_output($args, $instance)
	{
    global $post;
		extract($instance);
		?>
			<p>
        <ul class="nls_resources_list">
          <?php
          if (have_rows('course_work')) {
            while (have_rows('course_work')) { the_row();
                $resource = get_sub_field('download');
                $title = get_sub_field('title');
                ?>
                <li class="nls_resources_item">
                  <a href="<?php echo $resource; ?>" target="_blank">
                    <?php echo $title; ?>
                  </a>
                </li>
                <?php
            }
          }
          ?>
        </ul>
			</p>
		<?php
	}

	/**
	 * Widget backend
	 *
	 * @param array $instance
	 * @return string|void
	 */
	public function form( $instance )
	{
		/* Generate admin for fields */
		foreach($this->fields as $field_name => $field_data)
		{
			if($field_data['type'] === 'text'):
				?>
				<p>
					<label for="<?php echo $this->get_field_id($field_name); ?>"><?php _e($field_data['description'], $this->textdomain ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id($field_name); ?>" name="<?php echo $this->get_field_name($field_name); ?>" type="text" value="<?php echo esc_attr(isset($instance[$field_name]) ? $instance[$field_name] : $field_data['default_value']); ?>" />
				</p>
			<?php
			//elseif($field_data['type'] == 'textarea'):
			//You can implement more field types like this.
			else:
				echo __('Error - Field type not supported', $this->textdomain) . ': ' . $field_data['type'];
			endif;
		}
	}

	/**
	 * Adds a text field to the widget
	 *
	 * @param $field_name
	 * @param string $field_description
	 * @param string $field_default_value
	 * @param string $field_type
	 */
	private function add_field($field_name, $field_description = '', $field_default_value = '', $field_type = 'text')
	{
		if(!is_array($this->fields))
			$this->fields = array();

		$this->fields[$field_name] = array('name' => $field_name, 'description' => $field_description, 'default_value' => $field_default_value, 'type' => $field_type);
	}

	/**
	 * Updating widget by replacing the old instance with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update($new_instance, $old_instance)
	{
		return $new_instance;
	}
}
