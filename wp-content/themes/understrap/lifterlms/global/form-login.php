<?php
/**
 * LifterLMS Login Form
 * @since    3.0.0
 * @version  3.0.4 - added layout options
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $redirect ) ) {
	$redirect = get_permalink();
}

if ( ! isset( $layout ) ) {
	$layout = apply_filters( 'llms_login_form_layout', 'columns' );
}

if ( is_user_logged_in() ) { return; }
?>
<?php if ( ! empty( $message ) ) : ?>
	<?php llms_print_notice( $message, 'notice' ); ?>
<?php endif; ?>

<?php llms_print_notices(); ?>

<form action="" class="llms-login" method="POST">
	<div class="container section_bottom_block nls_course_nav">
		<div class="row section_bottom_block_header white_text uppercase">
	<!--<div class="col-12 llms-person-login-form-wrapper nls_course_nav">-->
			<div class="col-12">
				<h4 class="llms-form-heading uppercase white_text text-center section_bottom_block_header "><?php _e( 'Login', 'lifterlms' ); ?></h4>
			</div>
		</div>
		<div class="row">
			<div class="col-12 grey_block section_bottom_block" style="margin-top: 0px !important;">

					<div class="llms-form-fields">

						<?php do_action( 'lifterlms_login_form_start' ); ?>

						<?php foreach ( LLMS_Person_Handler::get_login_fields( $layout ) as $field ) : ?>
							<?php llms_form_field( $field ); ?>
						<?php endforeach; ?>

						<?php wp_nonce_field( 'llms_login_user' ); ?>
						<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
						<input type="hidden" name="action" value="llms_login_user" />

						<?php do_action( 'lifterlms_login_form_end' ); ?>

					</div>

			</div>

		</div>
	</div>
</form>
