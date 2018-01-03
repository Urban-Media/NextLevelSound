;( function( $ ) {

	// show a warning when a publishable key is added to the secret key fields
	$( '#llms_gateway_stripe_live_secret_key, #llms_gateway_stripe_test_secret_key' ).on( 'input', function() {

		var $err = $( this ).next( 'p.error' );

		if ( 0 === $( this ).val().indexOf( 'pk_' ) ) {

			if ( ! $err.length ) {
				$err = $( '<p class="error description" style="color:#e5554e;" />' );
				$( this ).parent().append( $err );
			}

			$err.text( LLMS.l10n.translate( 'This looks like a Publishable Key! Your Secret Key should go here.' ) );

		} else {
			$err.remove();
		}

	} );

	// show a warning when a secret key is added to the publishable key fields
	$( '#llms_gateway_stripe_live_publishable_key, #llms_gateway_stripe_test_publishable_key' ).on( 'input', function() {

		var $err = $( this ).next( 'p.error' );

		if ( 0 === $( this ).val().indexOf( 'sk_' ) ) {

			if ( ! $err.length ) {
				$err = $( '<p class="error description" style="color:#e5554e;" />' );
				$( this ).parent().append( $err );
			}

			$err.text( LLMS.l10n.translate( 'This looks like a Secret Key! Your Publishable Key should go here.' ) );

		} else {
			$err.remove();
		}

	} );

	// toggle visibility of test/live cred fields when enabling/disabling test mode checkbox
	$( '#llms_gateway_stripe_test_mode_enabled' ).on( 'change', function() {

		var $live = $( '#llms_gateway_stripe_live_heading, #llms_gateway_stripe_live_secret_key, #llms_gateway_stripe_live_publishable_key' ),
			$test = $( '#llms_gateway_stripe_test_heading, #llms_gateway_stripe_test_secret_key, #llms_gateway_stripe_test_publishable_key' ),
			$show, $hide;

		if ( $( this ).is( ':checked' ) ) {

			$show = $test;
			$hide = $live;

		} else {

			$show = $live;
			$hide = $test;

		}

		$show.each( function() {
			$( this ).closest( 'tr' ).show();
		} );

		$hide.each( function() {
			$( this ).closest( 'tr' ).hide();
		} );

	} ).trigger( 'change' );

} )( jQuery );
