<?php
	/*
		Plugin Name:		Category Thumbnails
		Plugin URI:			http://hovida-design.de/wordpress-plugin-category-thumbnails/
		Donation Link:		https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H56DKPMQE49NJ
		Author:				Adrian Preuss
		Author URI:			mailto:support@hovida-design.de?Subject=WordPress%20category-thumbnails
		Version:			1.0.6
		Text Domain:		category-thumbnails
		Domain Path:		/languages
		Description:		This Plugin provide functions like post-thumbnails for categories and (own) custom taxonomys. Please visit the Author-URL for Documentation.
	*/

	require_once('public_api.php');

	class Category_Thumbnails {
		protected $slug			= 'category-thumbnails';
		protected $name			= '';
		protected $active		= false;
		protected $url_donation	= 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H56DKPMQE49NJ';
		protected $url_help		= 'http://hovida-design.de/wordpress-plugin-category-thumbnails/';
		protected $actions		= array(
			'admin_init'					=> 'admin_init',
			'admin_print_scripts'			=> 'admin_script',
			'admin_print_styles'			=> 'admin_style',
			'admin_notices'					=> 'admin_notices'
		);

		protected $exclude_types	= array(
			'post'
		);

		protected $exclude_terms	= array(
			'post_tag', 'nav_menu', 'link_category', 'post_format'
		);

		function __construct() {
			define('PCT_I18N',																	$this->slug);
			$this->name																			= __('Category Thumbnails', PCT_I18N);
			load_plugin_textdomain($this->slug, false,											dirname(plugin_basename(__FILE__)) . '/languages/');
			register_activation_hook(__FILE__,													array($this, 'install'));
			register_deactivation_hook(__FILE__,												array($this, 'uninstall'));
			add_action('init',																	array($this, 'init'), 10000);
			add_filter(sprintf('plugin_action_links_%s/%s.php', $this->slug, $this->slug),		array($this, 'admin_plugin_links'));
		}

		public function install() {
			global $wpdb;

			$wpdb->query(sprintf('ALTER TABLE `%sterm_taxonomy` DROP `term_thumbnail`', $wpdb->prefix));
			$wpdb->query(sprintf('ALTER TABLE `%sterm_taxonomy` ADD `term_thumbnail` TEXT AFTER `count`', $wpdb->prefix));
		}

		public function uninstall() {
			global $wpdb;

			$wpdb->query(sprintf('ALTER TABLE `%sterm_taxonomy` DROP `term_thumbnail`', $wpdb->prefix));
		}

		public function admin_init() {
			global $pagenow;

			/* DEPRECATED START */
			if(in_array($pagenow, array('media-upload.php', 'async-upload.php'))) {
				add_filter('gettext', array($this, 'thickbox_button'), 20, 3);
			}
			/* DEPRECATED END */
		}

		public function admin_plugin_links($links) {
			return array_merge($links, array(
				sprintf('<a href="%s" target="_blank">%s</a>', $this->url_help, __('Help', PCT_I18N)),
				sprintf('<a href="%s" target="_blank">%s</a>', $this->url_donation, __('Donate', PCT_I18N)),
				sprintf('<a href="%s?TB_iframe=true&width=600&height=550" class="thickbox" title="Category Thumbnails « WordPress Codex">%s</a>', plugins_url('/docs/index.html' , __FILE__), __('Documentation', PCT_I18N))
			));
		}

		/* DEPRECATED START */
		function thickbox_button($translated_text, $text, $domain) {
			if($text == 'Insert into Post' || $text == __('Insert into Post')) {
				if(isset($_GET['referer']) && preg_match(sprintf('/%s/', $this->slug), $_GET['referer'])) {
					return __('Insert into Category', PCT_I18N);
				}
			}

			return $translated_text;
		}
		/* DEPRECATED END */

		public function admin_notices() {
			if($this->active == false) {
				printf('<div class="error"><p><span class="pct_error">%s:</span> %s</p></div>', __('WARNING', PCT_I18N), sprintf(__('Your active Theme "%s" don\'t Support %s. Please add %s to your %s', PCT_I18N), sprintf('<strong>%s</strong>', wp_get_theme()), sprintf('<strong>%s</strong>', $this->name), sprintf('<code>add_theme_support(\'%s\');</code>', $this->slug), '<strong>functions.php</strong>'));
			}
		}

		private function add_action($name, $value) {
			$this->actions[$name] = $value;
		}

		public function init() {
			global $wp_taxonomies;

			if(current_theme_supports($this->slug)) {
				$this->active = true;
			} else {
				$this->active = false;
			}

			if(count($wp_taxonomies) > 0) {
				foreach($wp_taxonomies AS $name => $data) {
					if(!in_array($name, $this->exclude_terms)) {
						if(count($data->object_type) > 0) {
							foreach($data->object_type AS $index => $type) {
								if(!in_array($type, $this->exclude_types)) {
									$this->add_action(sprintf('%s_edit_form_fields', $type),	'edit');
									$this->add_action(sprintf('%s_add_form_fields', $type),		'create');
									$this->add_action(sprintf('edited_%s', $type),				'save');
									$this->add_action(sprintf('created_%s', $type),				'save');
								}
							}

							$this->add_action(sprintf('%s_add_form_fields', $name),		'create');
							$this->add_action(sprintf('%s_edit_form_fields', $name),	'edit');
							$this->add_action(sprintf('edited_%s', $name),				'save');
							$this->add_action(sprintf('created_%s', $name),				'save');

							add_filter(sprintf('manage_edit-%s_columns', $name),		array($this, 'table_header'));
							add_filter(sprintf('manage_%s_custom_column', $name),		array($this, 'table_body'), 10, 3);
						}
					}
				}
			}

			if(count($this->actions) > 0) {
				foreach($this->actions AS $hook => $method) {
					add_action($hook, array($this, $method));
				}
			}
		}

		public static function admin_script() {
			wp_enqueue_script('thickbox');
			#wp_enqueue_style('thickbox');

			if(function_exists('wp_enqueue_media')){
				wp_enqueue_media();
			} else {
				wp_enqueue_script('media-upload');
			}

			wp_enqueue_script('js-pct-base64', plugins_url('/js/base64.js' , __FILE__), array('jquery'));
			wp_enqueue_script('js-pct-media', plugins_url('/js/media-upload.js' , __FILE__), array('jquery'));
		}

		public static function admin_style() {
			wp_enqueue_style('thickbox');
			wp_enqueue_style('css-pct-style', plugins_url('/css/style.css' , __FILE__));
		}

		public function table_header($columns) {
			return array_merge(array(
				'cb'		=> $columns['cb'],
				$this->slug => sprintf('<a href="edit-tags.php?taxonomy=%s&orderby=name&order=asc"><span>%s</span><span class="sorting-indicator"></span></a>', $_GET['taxonomy'], __('Thumbnail', PCT_I18N))
			), $columns);
		}

		public function table_body($deprecated, $column_name, $term_id) {
			global $wpdb;

			if($column_name == $this->slug) {
				if(has_category_thumbnail($term_id)) {
					$category_thumbnail = get_the_category_data($term_id, array(50, 50));
					printf('<div class="pct_image" style="background-image: url(%s);"></div>', $category_thumbnail->url);
				}
			}
		}

		public function getTemplate($category = null) {
			ob_start();
			require_once('admin_template.php');
			return ob_get_clean();
		}

		public function create($category) {
			if($this->active == false) {
				return;
			}

			printf('<div class="form-field"><label for="tag-thumb">%s</label>%s</div>', __('Thumbnail', PCT_I18N), $this->getTemplate($category));
		}

		public function edit($category) {
			if($this->active == false) {
				return;
			}

			printf('<tr class="form-field"><th scope="row" valign="top"><label for="parent">%s</label></th><td>%s</td></tr>', __('Thumbnail', PCT_I18N), $this->getTemplate($category));
		}

		public function save($category) {
			global $wpdb;

			if($this->active == false) {
				return;
			}

			$data = isset($_POST['category-thumbnail']) ? $_POST['category-thumbnail'] : 'none';

			if($data == 'none' || empty($data) || $data == 'NULL') {
				$data = NULL;
			}

			if($data !== NULL) {
				$data = base64_decode($data);
			}

			$wpdb->update($wpdb->prefix . 'term_taxonomy', array(
				'term_thumbnail' => $data
			), array(
				'term_id' => $category
			));
		}
	}

	new Category_Thumbnails();
?>
