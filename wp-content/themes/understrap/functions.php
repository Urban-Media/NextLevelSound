<?php
// Must be included here to be able to use is_plugin_active()
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/**
 * Understrap functions and definitions
 *
 * @package understrap
 */

/**
 * Theme setup and custom theme supports.
 */
require get_template_directory() . '/inc/setup.php';

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Load functions to secure your WP install.
 */
// require get_template_directory() . '/inc/security.php';

/**
 * Enqueue scripts and styles.
 */
require get_template_directory() . '/inc/enqueue.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/pagination.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Custom Comments file.
 */
require get_template_directory() . '/inc/custom-comments.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load custom WordPress nav walker.
 */
require get_template_directory() . '/inc/bootstrap-wp-navwalker.php';

/**
 * Load WooCommerce functions.
 */
require get_template_directory() . '/inc/woocommerce.php';

/**
 * Load Editor functions.
 */
require get_template_directory() . '/inc/editor.php';

/*
 * Custom Next Level Sound stuff begins here
 */
 add_theme_support( 'post-thumbnails' );
 add_theme_support('category-thumbnails');

 function my_llms_theme_support(){
 	add_theme_support( 'lifterlms-sidebars' );
 }
 add_action( 'after_setup_theme', 'my_llms_theme_support' );

 /*
  * Load custom scripts
  */
function load_custom_scripts() {
    wp_register_script('matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array('jquery'), false);
    wp_register_script('courseOverview', get_template_directory_uri() . '/js/course_overview.js', array('jquery'), false);

    wp_enqueue_script('matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array('jquery'), false);

    // Only load courseOverview.js on course overview template pages
    if(is_page_template('page-templates/course-overview.php')) {
      wp_enqueue_script('courseOverview', get_template_directory_uri() . '/js/course_overview.js', array('jquery'), false);
      $translation_array = array( 'templateUrl' => get_stylesheet_directory_uri() );
      wp_localize_script( 'courseOverview', 'globalVars', $translation_array );
    }
}

add_action('wp_enqueue_scripts', 'load_custom_scripts');

/*
 * Redirect users to Dashboard on login
 */
/*add_filter( 'login_redirect', function( $url, $query, $user ) {
 	return home_url();
}, 10, 3 );*/

/*
 * Custom image thumbnail sizes
 */
add_image_size('mentors_thumbnail', 85, 85);
add_image_size('mentor_header_banner', 650, 425);
add_image_size('category_thumbnail', 180, 125);
add_image_size('primary_blog_post_thumbnail', 530, 350);
add_image_size('secondary_blog_post_thumbnail', 375, 185);
add_image_size('tertiary_blog_post_thumbnail', 250, 185);

/*
* Header menu nav walker
*/
register_nav_menus( array(
   'header-menu'       => 'Header Menu',
   'links-menu'       => 'Links Menu',
   'courses-menu'       => 'Courses Menu',
   'social-links-menu'       => 'Social Links Menu',
) );

class Social_Links_Navwalker extends Walker_Nav_Menu {
 public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
     $url = '';
     if( !empty( $item->url ) ) {
         $url = $item->url;
     }

     $socialImage = get_template_directory_uri() . "/img/" . strtolower($item->title) ."_icon.png";
     $output .= '<li class="social_link"><a href="' . $url . '"><i class="fa fa-'.strtolower($item->title).' fa-2x" aria-hidden="true"></i></span>';
 }

 public function end_el( &$output, $item, $depth = 0, $args = array() ) {
     $output .= '</a></li>';
 }
}

class Header_Menu_Navwalker extends Walker_Nav_Menu {
 public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
     $url = '';
     if( !empty( $item->url ) ) {
         $url = $item->url;
     }

     $loginExtraClass = ($item->title == "Login") ? 'login_nav nls_rounded_button' : '';
     if ($item->title == "Login" && is_user_logged_in()) {
       $item->title = "Logout";
       $url = wp_logout_url();
     } elseif ($item->title == "Login") {
       $url = wp_login_url(get_permalink(7));
     }

     // Add an class to highlight if menu item matches current page
     $isCurrentItem = '';
      if(array_search('current-menu-item', $item->classes) != 0)
      {
          $isCurrentItem = 'nls_active_menu_item';
      }

     $output .= '<li class="nav-item"><a class="nav-link '.$loginExtraClass.' '.$isCurrentItem.'" href="' . $url . '">' . $item->title;
 }

 public function end_el( &$output, $item, $depth = 0, $args = array() ) {
     $output .= '</a></li>';
 }
}

class Links_Menu_Navwalker extends Walker_Nav_Menu {
 public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
     $url = '';
     if( !empty( $item->url ) ) {
         $url = $item->url;
     }

     // Switch Login/Logout name and link depending on if user logged in
     if ($item->title == "Sign In" && is_user_logged_in()) {
       $item->title = "Logout";
       $url = wp_logout_url();
     } elseif ($item->title == "Sign In" || $item->title == "Login") {
       $url = wp_login_url(get_permalink(7));
     }

     $classes = empty( $item->classes ) ? array() : (array) $item->classes;
     $classes[] = 'menu-item-' . $item->ID;

     $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
     $class_names = $class_names ? esc_attr( $class_names ) : '';

     $output .= '<li id="menu-item-'.$item->ID.'" class="'. $class_names .'"><a href="' . $url . '">' . $item->title;
 }

 public function end_el( &$output, $item, $depth = 0, $args = array() ) {
     $output .= '</a></li>';
 }
}

add_action( 'widgets_init', 'upsell_sidebar' );
function upsell_sidebar() {
    register_sidebar( array(
        'name' => __( 'Upsell Sidebar', 'understrap' ),
        'id' => 'upsell',
        'description' => __( 'Widgets in this area will be shown on the registration sign-up page.', 'understrap' ),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
	      'after_widget'  => '</li>',
	      'before_title'  => '<h2 class="widgettitle">',
	      'after_title'   => '</h2>',
    ) );
}

// Remove the 'Read More' button for the_excerpt()
function new_excerpt_more($more) {
    global $post;
	  return '';
}
add_filter('excerpt_more', 'new_excerpt_more');


/*
 * Custom shortcodes
 */


/*
 * End custom shortcodes
 */


/*
 * Begin LifterLMS modifications
 */

// Override 'sections' post-type so we can view and interact with them
if (is_plugin_active('lifterlms/lifterlms.php')) {
  add_filter( 'register_post_type_args', 'change_capabilities_of_section_posttype' , 10, 2 );

  function change_capabilities_of_section_posttype( $args, $post_type ){

   // Do not filter any other post type
   if ( 'section' !== $post_type ) {

       // Give other post_types their original arguments
       return $args;

   }

   // Change the capability_type of the "section" post_type
   $args['public'] = true;
   $args['show_ui'] = true;
   $args['publicly_queryable'] = true;
   $args['exclude_from_search'] = false;
   $args['rewrite'] = true;
   $args['query_var'] = true;

    return $args;

  }
}

// LifterLMS includes course and lesson sidebars by default but we need to add
// a sidebar for sections also

$sectionSidebarArgs = array(
	'name'          => __( 'Section Sidebar', 'nextlevelsounds' ),
	'id'            => 'llms_section_widgets_side',
	'description'   => 'Widgets in this area will be shown on LifterLMS sections.',
  'class'         => '',
	'before_widget' => '<li id="%1$s" class="widget %2$s">',
	'after_widget'  => '</li>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>' );

register_sidebar( $sectionSidebarArgs );

// Courses have a load of info hooked onto them by default that we don't want
// Let's remove them here
add_action( 'after_setup_theme', 'remove_lms_course_data' );
function remove_lms_course_data(){
  // Hook - priority
  $hooksToRemove = array(
    'lifterlms_template_single_meta_wrapper_start' => 5,
    'lifterlms_template_single_length'             => 10,
    'lifterlms_template_single_difficulty'         => 20,
    'lifterlms_template_single_course_tracks'      => 25,
    'lifterlms_template_single_course_categories'  => 30,
    'lifterlms_template_single_course_tags'        => 35,
    'lifterlms_template_course_author'             => 40,
    'lifterlms_template_single_meta_wrapper_end'   => 50
  );

  foreach($hooksToRemove as $key => $val) {
	   remove_action( 'lifterlms_single_course_after_summary', $key, $val );
  }
}

add_action( 'after_setup_theme', 'remove_lms_course_catalogue_author');
function remove_lms_course_catalogue_author() {
  remove_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_author', 10);
}

add_action( 'after_setup_theme', 'remove_lms_achievements');
function remove_lms_achievements() {
  remove_action('lifterlms_student_dashboard_index', 'lifterlms_template_student_dashboard_my_achievements', 20);
}

/*
 * End LifterLMS modifications
 */
