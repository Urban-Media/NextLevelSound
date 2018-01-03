<?php
/**
 * Plugin Name: WP Discourse Widgets
 * Description: Discourse Widgets Plugin for WordPress - Modified by Roy @ UM
 * Version:     1.0.0
 * Author:      Vinkas with modifications by Roy @ Urban Media
 * Text Domain: wp-discourse-widgets
 * Domain Path: /languages
 * License:     MIT
 * Author URI:  http://vinkas.com
 * Plugin URI:  https://github.com/vinkas0/wp-discourse-widgets
 * GitHub Plugin URI: https://github.com/vinkas0/wp-discourse-widgets
 * License URI: https://github.com/vinkas0/wp-discourse-widgets/LICENSE.txt
 *
 * @category WordPress
 * @package Widgets
 * @subpackage Discourse
**/

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Vinoth Kannan <vinothkannan@vinkas.com>, Vinkas <http://vinkas.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
**/

require_once( __DIR__ . '/lib/discourse-widget.php' );
require_once( __DIR__ . '/lib/discourse/topics-widget.php' );

add_action( 'widgets_init', function(){
	register_widget( 'Vinkas\Widgets\Discourse\Topics_Widget' );
});
