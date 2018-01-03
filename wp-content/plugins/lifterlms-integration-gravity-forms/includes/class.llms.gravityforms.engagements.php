<?php
/**
 * LifterLMS engagements for custom Gform specifc actions
 *
 * @author Thomas Patrick Levy
 * @version  1.0.0
 * @since    1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_GravityForms_Engagements {

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  1.0.1 - llms core 3.0.5 engagement api updates
	 */
	public function __construct() {

		add_filter( 'lifterlms_engagement_actions', array( $this, 'register_engagement_action' ), 10, 1 );

		// accommodate llms 3.0.5 engagement updates
		add_filter( 'llms_engagement_controller_values_lesson', array( $this, 'register_engagement_action' ) );

		add_filter( 'lifterlms_engagement_triggers', array( $this, 'register_engagement_trigger' ), 10, 1 );
		add_filter( 'lifterlms_external_engagement_query_arguments', array( $this, 'engagment_query_args' ), 10, 3 );

		add_action( 'llms_metabox_after_save_lifterlms-engagement', array( $this, 'save_engagement' ) );

		if ( is_admin() ) {
			add_action( 'admin_footer', array( $this, 'admin_engagement_scripts' ) );
		}

	}

	/**
	 * Add some JS to the admin panel to handle user interaction with the engagment creation screen
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function admin_engagement_scripts() {
		global $post;

		if ( ! $post ) { return; }
		echo "
			<script type=\"text/javascript\">
			( function( $ ) {

				$( '#_llms_trigger_type' ).on( 'change', function() {

					var selected_val = $( '#_llms_trigger_type' ).find( 'option:selected' ).val();
					if ( 'llms_gform_lesson_form_completed' === selected_val ) {
						get_all_lessons();
					}

				} );

				// set a timeout b/c the core clears the value if none found...
				setTimeout( function() {
					$( '#_llms_trigger_type' ).trigger( 'change' );
					setTimeout( function() {
						$( '#trigger-select option[value=\"" . get_post_meta( $post->ID, '_llms_engagement_trigger_post', true ) . "\"]' ).attr( 'selected', 'selected' );
						$( '#trigger-select' ).trigger( 'chosen:updated' );
					}, 500 );
				}, 500 );

			} )( jQuery )
			</script>
		";
	}


	/**
	 * [engagment_query_args description]
	 * @param    [type]     $query_args   [description]
	 * @param    [type]     $action       [description]
	 * @param    [type]     $action_args  [description]
	 * @return   [type]                   [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function engagment_query_args( $query_args, $action, $action_args ) {

		// ensure we only do this for our gforms action
		if ( 'llms_gform_lesson_form_completed' !== $action ) {
			return $query_args;
		}

		$query_args['related_post_id'] = $action_args[2];
		$query_args['trigger_type'] = $action;
		$query_args['user_id'] = $action_args[0]['created_by'];

		return $query_args;

	}

	/**
	 * Register a custom engagment action with LifterLMS
	 * This list is watched by engagements class and adds this to the list of actions to "maybe_trigger_engagements"
	 * This also ensures that the "lesson" post selector displays when a gforms engagement is selected when creating an engagement
	 * @param    array     $engagements  list of actions that can trigger engagmenets
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function register_engagement_action( $engagements ) {
		$engagements[] = 'llms_gform_lesson_form_completed';
		return $engagements;
	}

	/**
	 * Register a custom engagment action with LifterLMS
	 * This will add the trigger to the list of possible triggers selected on the admin panel
	 * when creating an engagmenet
	 * @param    array     $engagements  list of actions that can trigger engagmenets
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function register_engagement_trigger( $engagements ) {
		$engagements['llms_gform_lesson_form_completed'] = __( 'Lesson Gravity Form Completed', 'lifterlms-gravity-forms' );
		return $engagements;
	}

	/**
	 * Saves the engagement on engagement metabox save
	 * Functions with the LLMS 3.0.5 engagements api
	 * @param    int     $post_id  WP Post ID of the Engagment
	 * @return   void
	 * @since    1.0.1
	 * @version  1.0.1
	 */
	public function save_engagement( $post_id ) {

		if ( isset( $_POST[ '_llms_trigger_type' ] ) && 'llms_gform_lesson_form_completed' === $_POST[ '_llms_trigger_type' ] ) {

			$val = isset( $_POST['_faux_engagement_trigger_post_lesson'] ) ? sanitize_text_field( $_POST['_faux_engagement_trigger_post_lesson'] ) : false;

			if ( $val ) {

				update_post_meta( $post_id, '_llms_engagement_trigger_post', $val );

			}

		}

	}


}
return new LLMS_GravityForms_Engagements();