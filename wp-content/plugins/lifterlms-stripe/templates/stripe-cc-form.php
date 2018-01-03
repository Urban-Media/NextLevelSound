<?php
/**
 * Checkout LLMS_Payment_Gateway
 * @since     1.0.0
 * @version  4.3.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

do_action( 'lifterlms_before_checkout_cc_form' );

if ( 'test' === $gateway->get_api_mode() ) {

	$notice = sprintf(
		__( 'LifterLMS Stripe is currently in test mode. You may use card number "4242424242424242" with an expiration date in the future and any CVC and Zip. For more information see the %1$sStripe Testing Documentation%2$s.', 'lifterlms-stripe' ),
		'<a href="https://stripe.com/docs/testing#cards" target="_blank">', '</a>'
	);

	llms_print_notice( $notice, 'debug' );

}

if ( $cards ) {

	echo '<div class="llms-form-field llms-cols-12 llms-cols-last"><label>' . __( 'Payment Method', 'lifterlms-stripe' ) . '</label></div><div class="clear"></div>';
	llms_form_field( array(
		'columns' => 12,
		'disabled' => $selected ? false : true,
		'id' => 'llms_stripe_saved_card_id',
		'last_column' => true,
		'required' => false,
		'type'  => 'select',
		'options' => $cards,
	) );

}

?>
<div class="llms-form-field llms-stripe-cc llms-cols-12 llms-cols-last">
	<!-- a Stripe Element will be inserted here. -->
	<div class="llms-stripe-card" id="llms-stripe-card-element"></div>
	<!-- Used to display form errors -->
	<div class="llms-stripe-card-errors" id="llms-stripe-card-errors" role="alert"></div>

	<input id="llms_stripe_token" name="llms_stripe_token" type="hidden" value="">
	<input id="llms_stripe_card_id" name="llms_stripe_card_id" type="hidden" value="">
</div>
<div class="clear"></div>

<?php
do_action( 'lifterlms_after_checkout_cc_form' );
