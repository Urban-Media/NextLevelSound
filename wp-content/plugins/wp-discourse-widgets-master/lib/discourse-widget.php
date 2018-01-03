<?php

namespace Vinkas\Widgets;

class Discourse_Widget extends \WP_Widget {

  protected $cacheTime = 10; // Time in minutes between updates.

  public function __construct( $id_base, $name, $description, $control_options = array() ) {
    $name = 'Discourse ' . $name;
    $widget_ops = array(
			'classname' => 'widget_discourse_' . $id_base,
			'description' => $description,
		);
    $id_base = 'discourse-' . $id_base;
    parent::__construct( $id_base, $name, $widget_ops, $control_options );
	}

}
