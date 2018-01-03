<?php
/**
* LifterLMS Gravity Forms Integration Class
*
* @author Thomas Patrick Levy
* @version  1.0.0
* @since    1.0.3
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Integration_GravityForms {

	public $id = 'gravityforms';
	public $title = '';
	private $meta_key = 'llms_gform_present';

	/**
	 * Integration Constructor
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		$this->title = __( 'Gravity Forms', 'lifterlms-gravity-forms' );

		if ( $this->is_available() ) {

			add_action( 'save_post_lesson', array( $this, 'check_for_form' ), 10, 1 );
			add_action( 'lifterlms_single_lesson_after_summary', array( $this, 'maybe_remove_complete_action' ), 7 );
			add_action( 'gform_entry_created', array( $this, 'maybe_complete_lesson' ), 10, 2 );
			add_filter( 'gform_shortcode_form', array( $this, 'maybe_block_shortcode' ), 777, 3 );
			add_filter( 'gform_shortcode_conditional', array( $this, 'maybe_block_shortcode' ), 777, 3 );
			add_filter( 'gform_form_tag', array( $this, 'maybe_add_hidden_fields' ), 10, 2 );

			if ( $this->has_addon( 'user-registration' ) ) {

				add_filter( 'lifterlms_theme_override_directories', array( $this, 'add_override_directory' ), 10, 1 );
				add_action( 'gform_user_registered', array( $this, 'user_registered' ), 10, 4 );

			}

		}
	}

	/**
	 * Add template override directory
	 * @param    array     $dirs  array of directories lifterlms will look for templates in
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function add_override_directory( $dirs ) {
		array_unshift( $dirs, LLMS_GF_PLUGIN_TEMPLATES_DIR );
		return $dirs;
	}

	/**
	 * When an AJAX form is submitted, automatically redirect to the next lesson upon completion
	 * or reload the page, depending on autoadvance settings
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.3
	 */
	public function ajax_redirect_script() {

		$lesson_id = get_the_ID();

		// 2.x relies on a setting
		if ( version_compare( LLMS()->version, '3.0.0', '<' ) ) {
			$autoadvance = ( 'yes' === get_option( 'lifterlms_autoadvance', 'yes' ) );
		}
		// 3.x relies on a filter
		else {
			$autoadvance = apply_filters( 'lifterlms_autoadvance', true );
		}

		if ( $autoadvance ) {

			$lesson = new LLMS_Lesson( $lesson_id );
			$redirect_id = $lesson->get_next_lesson();

		} else {

			$redirect_id = $lesson_id;

		}

		$url = apply_filters( 'llms_gform_ajax_redirect_url', get_permalink( $redirect_id ), $lesson_id, $redirect_id );

		if ( $url ) {
			echo "
				<script type=\"text/javascript\">
					( function( $ ) {
						$( document ).bind( 'gform_confirmation_loaded', function( event, formId ) {
							setTimeout( function() {
								window.location = '" . $url . "';
							}, " . apply_filters( 'llms_gform_ajax_redirect_delay', 1000 ) . " );
						} );
					} )( jQuery );
				</script>
			";
		}

	}

	/**
	 * Called during save action of a lesson
	 * Stores a metavalue of 'yes' or 'no' depending on whether or not a gform shortcode
	 * is present in the lesson's content
	 *
	 * @param    int   $post_id  WP_Post ID of the lesson
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function check_for_form( $post_id ) {

		$val = 'no';

		if ( isset( $_POST['content'] ) ) {

			// check only this much of the shortcode, this will return true for alternate "gravityforms" shortcode
			$val = strpos( $_POST['content'], '[gravityform' ) !== false ? 'yes' : 'no';

		}

		/**
		 * Allow filtering of the stored value
		 * Perhaps someone is displaying the text [gravityform] for example purposes
		 * but they aren't actually embedding a form and they want the mark as complete button
		 * to still display... it could happen, right?
		 */
		update_post_meta( $post_id, $this->meta_key, apply_filters( 'llms_gform_present', $val, $post_id ) );

	}

	/**
	 * Determine if the integration is available for use
	 * @return   boolean
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function is_available() {
		if ( $this->is_installed() ) {
			return true;
		}
		return false;
	}

	/**
	 * Determine if Gravity Form addons are installed
	 * @param    string     $addon  addon slug -- this is our internal slug for the addon
	 * @return   boolean
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function has_addon( $addon ) {

		$r = false;

		switch( $addon ) {

			case 'user-registration':
				if ( class_exists( 'GFUser' ) ) {
					$r = true;
				}
			break;

		}

		return apply_filters( 'llms_gforms_has_addon', $r, $addon );

	}

	/**
	 * Determine if GravityForms is installed & activated
	 * @return   boolean
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function is_installed() {
		if ( class_exists( 'GFForms' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Add hidden fields used by our integration to identify various things needed by LifterLMS
	 *
	 * When on a Lesson, add the lesson ID so we can record lesson completion upon form submission
	 *
	 * If the form_id equals the option selected for user reg form, maybe add product id to hijack redirect to checkout
	 *
	 * @param    string     $html     html of the opening <form> tag for the gform
	 * @param    array     $form      Gform form array
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function maybe_add_hidden_fields( $html, $form ) {

		$post_id = get_the_ID();

		// we're on a lesson and we've recorded that this lesson has a gform
		if ( 'lesson' === get_post_type( $post_id ) && 'yes' === get_post_meta( $post_id, $this->meta_key, true ) ) {

			$html .= '<input type="hidden" name="llms_gform_lesson_id" value="' . $post_id . '">';

		}

		// user registration is enabled and this is the selected form and were in the midst of a checkout
		elseif ( $this->has_addon( 'user-registration' ) && $form['id'] == get_option( 'lifterlms_gravityforms_registration_form', false ) && get_query_var( 'product-id' ) ) {

			$html .= '<input type="hidden" name="llms_gform_product_id" value="' . get_query_var( 'product-id' ) . '">';

		}

		return $html;

	}

	/**
	 * Before returning gform shortcode content, check if we're on a lesson
	 * which is using a gform, if the lesson is already complete
	 * block the display of the form
	 *
	 * @param    string     $shortcode_string  gform html
	 * @param    array      $attributes        shortcode attributes array
	 * @param    string     $content           I don't know
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.3
	 */
	public function maybe_block_shortcode( $shortcode_string, $attributes, $content )  {

		$post_id = get_the_ID();

		if ( 'lesson' === get_post_type( $post_id ) && 'yes' === get_post_meta( $post_id, $this->meta_key, true ) ) {

			// shows the content if the shortcode string is a text confirmation message
			if ( false === strpos( $shortcode_string, '<form' ) ) {
				return $shortcode_string;
			}

			$lesson = new LLMS_Lesson( $post_id );
			if ( $lesson->is_complete() ) {

				return '';

			}

		}

		return $shortcode_string;

	}

	/**
	 * Called when a gravity form entry is recorded
	 * If the form is associated with a lesson and the lesson has a gform
	 * mark the lesson as completed
	 *
	 * @param    array     $lead  GForm entry array
	 * @param    array     $form  GForm form array
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.2
	 */
	public function maybe_complete_lesson( $lead, $form ) {

		$lesson_id = isset( $_POST['llms_gform_lesson_id'] ) ? $_POST['llms_gform_lesson_id'] : false;

		if ( $lesson_id ) {

			$lesson = new LLMS_Lesson( $lesson_id );

			// pre llms core 3.0
			if ( method_exists( $lesson, 'get_is_free' ) ) {

				if ( '1' !== $lesson->get_is_free() ) {

					$lesson->mark_complete( $lead['created_by'], true );

				}

			} else {

				if ( ! $lesson->is_free() ) {

					$lesson->mark_complete( $lead['created_by'], true );

				}

			}


			do_action( 'llms_gform_lesson_form_completed', $lead, $form, $lesson_id );

		}

	}

	/**
	 * Immediately before adding the mark as complete button
	 * check if there's a gform present and remove the button action if there is one
	 * additionally add javascript to the footer for ajax redirects
	 *
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function maybe_remove_complete_action() {

		$lesson = new LLMS_Lesson( get_the_ID() );

		if ( ! $lesson->is_complete() && 'yes' === get_post_meta( get_the_ID(), $this->meta_key, true ) ) {

			remove_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_complete_lesson_link', 10 );
			add_action( 'wp_footer', array( $this, 'ajax_redirect_script' ) );

		}

	}

	/**
	 * After Gforms registration, trigger the LifterLMS registration action for engagements
	 * @param    int     $user_id    user id of the newly registered user
	 * @param    array   $feed       feed data from gforms (settings)
	 * @param    array   $entry      form submission data
	 * @param    string  $user_pass  user's chosen or generated password (plaintext)
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function user_registered( $user_id, $feed, $entry, $user_pass ) {

		$user = get_user_by( 'id', $user_id );

		$data = array(
			'user_login' => $user->user_login,
			'user_pass'  => $user_pass,
			'user_email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
			'role'       => isset( $feed['meta']['role'] ) ? $feed['meta']['role'] : 'student',
		);

		do_action( 'lifterlms_created_person', $user_id, $data, false );

	}

}
