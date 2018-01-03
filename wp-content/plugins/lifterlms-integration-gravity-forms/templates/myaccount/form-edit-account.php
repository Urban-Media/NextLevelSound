<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Replace LifterLMS Registration Form
 * With a Gravity Form
 */
do_action( 'lifterlms_my_account_navigation' );

// ensure we have a form selected to override this template with
$form_id = get_option( 'lifterlms_gravityforms_edit_form', false );

// if we don't, remove the override directory, get the original template, and re-add the filter
if ( ! $form_id ) {

	$integrations = LLMS()->integrations()->get_available_integrations();
	$gforms = isset( $integrations['gravityforms'] ) ? $integrations['gravityforms'] : false;

	if ( $gforms ) {

		llms_print_notices();
		remove_filter( 'lifterlms_theme_override_directories', array( $gforms, 'add_override_directory' ), 10, 1 );
		llms_get_template( 'global/form-registration.php' );
		remove_filter( 'lifterlms_theme_override_directories', array( $gforms, 'add_override_directory' ), 10, 1 );

	}

	return;

}
// get the remaining options
else {

	$form_title = ( 'yes' === get_option( 'lifterlms_gravityforms_edit_form_show_title', 'no' ) ) ? true : false;
	$form_desc = ( 'yes' === get_option( 'lifterlms_gravityforms_edit_form_show_desc', 'no' ) ) ? true : false;
	$form_ajax = ( 'yes' === get_option( 'lifterlms_gravityforms_edit_form_usa_ajax', 'yes' ) ) ? true : false;

}
?>

<?php do_action( 'llms_gforms_before_edit_form' ); ?>

<?php echo do_shortcode( '[gravityform id="' . $form_id . '" title="'. $form_title .'" description="' . $form_desc . '" ajax="' . $form_ajax . '"]' ); ?>

<?php do_action( 'llms_gforms_after_edit_form' ); ?>
