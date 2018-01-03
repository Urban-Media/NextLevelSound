<?php
	function implode_attributes($array) {
		$html	= array();
		
		foreach($array AS $name => $value) {
			$html[] = sprintf('%s="%s"', $name, $value);
		}
		
		return $html;
	}
	
	function has_category_thumbnail($category_id = null) {
		global $cat;
		global $wpdb;
		
		if($category_id != null) {
			$cat = $category_id;	
		}
		
		$result = $wpdb->get_row($wpdb->prepare('SELECT `term_thumbnail` FROM `' . $wpdb->prefix . 'term_taxonomy` WHERE `term_id`=%d LIMIT 1', $cat));
		return $result->term_thumbnail != null ? true : false;
	}
	
	function the_category_thumbnail($category_id = null, $sizes = array()) {		
		print get_the_category_thumbnail($category_id, $sizes);
	}
	
	function get_the_category_thumbnail($category_id = null, $sizes = array()) {
		global $cat;
		global $wpdb;
		
		if($category_id != null) {
			$cat = $category_id;	
		}
		
		$attributes		= array();
		$result			= $wpdb->get_row($wpdb->prepare('SELECT `term_thumbnail` FROM `' . $wpdb->prefix . 'term_taxonomy` WHERE `term_id`=%d LIMIT 1', $cat));
		$data			= json_decode($result->term_thumbnail);
		
		if(count($sizes) == 2) {
			$attributes['width']	= $sizes[0];
			$attributes['height']	= $sizes[1];
		}
		
		if(!empty($data->alt)) {
			$attributes['alt']		= $data->alt;
		} else {
			$attributes['alt']		= '';
		}
		
		if(!empty($data->title)) {
			$attributes['title']	= $data->title;
		}
		
		return sprintf('<img src="%s"%s />', $data->url, implode(' ', implode_attributes($attributes)));
	}
	
	function get_the_category_data($category_id = null) {
		global $cat;
		global $wpdb;
		
		if($category_id != null) {
			$cat = $category_id;	
		}
		
		$result			= $wpdb->get_row($wpdb->prepare('SELECT `term_thumbnail` FROM `' . $wpdb->prefix . 'term_taxonomy` WHERE `term_id`=%d LIMIT 1', $cat));
		$data			= json_decode($result->term_thumbnail);
		
		return $data;
	}
?>