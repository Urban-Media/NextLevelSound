<?php
/**
 * Manage settings forms on the LifterLMS Integrations Settings Page
 *
 * @package 	LifterLMS Gravity Forms
 * @category 	Core
 * @author 		Thomas Patrick Levy
 * @since       1.0.0
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Integrations_GravityForms {

	public function __construct() {

		// Add filter to when settings page is loaded
		add_filter( 'lifterlms_integrations_settings', array( $this, 'integration_settings' ), 10, 1 );

	}

	/**
	 * This function adds the appropriate content to the
	 * array that makes up the settings page. It takes in
	 * the content passed to it via the filter and then adds
	 * the mailchimp info to it.
	 *
	 * @param  array $content Content that is contained on the integrations page of LifterLMS
	 * @return array          The updated content array
	 */
	public function integration_settings( $content ) {

		$integrations = LLMS()->integrations()->get_available_integrations();
		$gforms = isset( $integrations['gravityforms'] ) ? $integrations['gravityforms'] : false;

		// locate active gravity forms to use in various settings
		$form_options = array();
		if ( $gforms && $gforms->has_addon( 'user-registration' ) ) {

			$form_options[0] = __( '-- Select a Gravity Form --', 'lifterlms-gravity-forms' );

			foreach( GFAPI::get_forms( true ) as $form ) {

				$form_options[ $form['id'] ] = $form['title'];

			}

			asort( $form_options );

		}




		$content[] = array(
			'type' => 'sectionstart',
			'id' => 'lifterlms_gravityforms_options',
			'class' =>'top'
		);

		$content[] = array(
			'title' => __( 'Gravity Forms Settings', 'lifterlms-gravity-forms' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'lifterlms_gravityforms_options'
		);

		// only display these options if the user registration is enabled
		if ( count( $form_options ) ) {


			/**
			 * User registration form fields
			 */
			$content[] = array(
				'desc' 		=> '<br>' . sprintf( __( 'This Gravity Form will automatically replace LifterLMS registration forms. This form must be associated with a <a href="%s" target="_blank">Gravity Forms User Registration Feed</a>.', 'lifterlms-gravity-forms' ), 'https://www.gravityhelp.com/documentation/article/user-registration-add-on/' ),
				'default'	=> 0,
				'id' 		=> 'lifterlms_gravityforms_registration_form',
				'options'   => $form_options,
				'title'     => __( 'Registration Form', 'lifterlms-gravity-forms' ),
				'type' 		=> 'select',
			);

			$content[] = array(
				'checkboxgroup' => 'start',
				'desc' 		=> __( 'Show title', 'lifterlms-gravity-forms' ),
				'default'	=> 'no',
				'id' 		=> 'lifterlms_gravityforms_registration_form_show_title',
				'type' 		=> 'checkbox',
				'title'     => __( 'Registration Form Settings', 'lifterlms-gravity-forms' ),
			);

			$content[] = array(
				'checkboxgroup' => 'middle',
				'desc' 		=> __( 'Show description', 'lifterlms-gravity-forms' ),
				'default'	=> 'no',
				'id' 		=> 'lifterlms_gravityforms_registration_form_show_desc',
				'type' 		=> 'checkbox',
			);

			$content[] = array(
				'checkboxgroup' => 'end',
				'desc' 		=> __( 'Use AJAX submission', 'lifterlms-gravity-forms' ),
				'default'	=> 'yes',
				'id' 		=> 'lifterlms_gravityforms_registration_form_usa_ajax',
				'type' 		=> 'checkbox',
			);


			/**
			 * User edit form screens
			 */
			$content[] = array(
				'desc' 		=> '<br>' . sprintf( __( 'This Gravity Form will automatically replace the LifterLMS user edit account form. This form must be associated with a <a href="%s" target="_blank">Gravity Forms User Registration Feed</a>.', 'lifterlms-gravity-forms' ), 'https://www.gravityhelp.com/documentation/article/user-registration-add-on/' ),
				'default'	=> 0,
				'id' 		=> 'lifterlms_gravityforms_edit_form',
				'options'   => $form_options,
				'title'     => __( 'Edit Account Form', 'lifterlms-gravity-forms' ),
				'type' 		=> 'select',
			);

			$content[] = array(
				'checkboxgroup' => 'start',
				'desc' 		=> __( 'Show title', 'lifterlms-gravity-forms' ),
				'default'	=> 'no',
				'id' 		=> 'lifterlms_gravityforms_edit_form_show_title',
				'type' 		=> 'checkbox',
				'title'     => __( 'Edit Account Form Settings', 'lifterlms-gravity-forms' ),
			);

			$content[] = array(
				'checkboxgroup' => 'middle',
				'desc' 		=> __( 'Show description', 'lifterlms-gravity-forms' ),
				'default'	=> 'no',
				'id' 		=> 'lifterlms_gravityforms_edit_form_show_desc',
				'type' 		=> 'checkbox',
			);

			$content[] = array(
				'checkboxgroup' => 'end',
				'desc' 		=> __( 'Use AJAX submission', 'lifterlms-gravity-forms' ),
				'default'	=> 'yes',
				'id' 		=> 'lifterlms_gravityforms_edit_form_usa_ajax',
				'type' 		=> 'checkbox',
			);

		}


		$content[] = array(
			'title'     => __( 'Activation Key', 'lifterlms-gravity-forms' ),
			'desc' 		=> __( 'Required for support and automated plugin updates. Located on your <a href="https://lifterlms.com/my-account/" target="_blank">LifterLMS Account Settings page</a>.', 'lifterlms-gravity-forms' ),
			'id' 		=> 'lifterlms_gravityforms_activation_key',
			'type' 		=> 'llms_license_key',
			'default'	=> '',
			'extension' => LLMS_GF_PLUGIN_FILE,
		);

		if ( ! class_exists( 'LLMS_Helper' ) ) {

			$content[] = array(
				'type' => 'custom-html',
				'value' => '<p>' . sprintf(
					__( 'Install the %s to start receiving automatic updates for this extension.', 'lifterlms-gravity-forms' ),
					'<a href="https://lifterlms.com/docs/lifterlms-helper/" target="_blank">LifterLMS Helper</a>'
			 	) . '</p>',
			);
		}

		$content[] = array(
			'type' => 'sectionend',
			'id' => 'lifterlms_gravityforms_options'
		);

		return $content;

	}

}

return new LLMS_Settings_Integrations_GravityForms();
