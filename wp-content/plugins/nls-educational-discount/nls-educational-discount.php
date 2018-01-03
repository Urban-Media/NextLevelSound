<?php
/*
Plugin Name: NLS Educational Discount Widget Plugin
Plugin URI:
Description: Adds an educational discount widget to courses, sections and lessons
Version: 1.0
Author: Urban Media
Author URI: http://www.urbanmedia.co.uk
*/

/**
 * Register the widget
 */
add_action('widgets_init', create_function('', 'return register_widget("NLS_Edu_Discount");'));

/**
 * Class NLS_Resources
 */
class NLS_Edu_Discount extends WP_Widget
{
	/** Basic Widget Settings */
	const WIDGET_NAME = "NLS Educational Discounts";
	const WIDGET_DESCRIPTION = "Adds an educational discount widget for course/section/lesson sidebars";

	var $textdomain;
	var $fields;

	/**
	 * Construct the widget
	 */
	function __construct()
	{
		//We're going to use $this->textdomain as both the translation domain and the widget class name and ID
		$this->textdomain = strtolower(get_class($this));

		//Figure out your textdomain for translations via this handy debug print
		//var_dump($this->textdomain);

		//Add fields
		$this->add_field('title', 'Enter title', '', 'text');
		$this->add_field('page_id', 'Page ID', 'The ID of the Educational Discounts page', 'text');

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

		//if (!empty($title))
			//echo $args['before_title'] . $title . $args['after_title'];

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
			<div class="section_sideblock nls_course_nav grey_block">
        <div class="section_centre_circle">
          <img src="<?php echo get_template_directory_uri(); ?>/img/mortarboard.png" class="circle_icon">
        </div>
        <h2 class="sideblock_title text-center">
          Educational Discounts
        </h2>
        <a href="<?php echo get_permalink($page_id); ?>" class="sideblock_link">
          <div class="nls_rounded_button white_button black_text uppercase text-center sideblock_button">
            Find Out More
          </div>
        </a>
      </div>
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
