/**
 * LifterLMS Stripe JS payment gateway
 * @since    4.0.0
 * @version  4.3.2
 */
( function( $ ) {

	var llms_stripe = window.llms_stripe || {},
		stripe = Stripe( llms_stripe.publishable_key ),
		elements = stripe.elements( llms_stripe.settings.elements ),
		card = elements.create( 'card', llms_stripe.settings.card );

	/**
	 * Bind DOM events
	 * @return   void
	 * @since    4.0.0
	 * @version  4.3.2
	 */
	llms_stripe.bind = function() {

		var self = this

		// add token creation & cc validation to core before submit validations
		window.llms.checkout.add_before_submit_event( {
			data: self,
			handler: self.submit,
		} );

		if ( $( '#llms-stripe-card-element' ).length ) {

			self.mount_card();

		}

		// saved card dropdown interactions
		$( '#llms_stripe_saved_card_id' ).on( 'change', function() {

			var card_id = $( this ).val(),
				$card_id = $( '#llms_stripe_card_id' );

			// remove the existing card ID (if there is one) & mount the Stripe Element
			if ( 'create-new' === card_id ) {

				$card_id.val( '' );
				card.mount( '#llms-stripe-card-element' );
				self.toggle_submit( false );

			// unmount the element and set the card ID to be the selected card ID
			} else {

				$card_id.val( card_id );
				card.unmount();
				self.toggle_submit( true );

			}

		} );

		$( '#llms_billing_zip' ).on( 'blur', function() {

			card.update( {
				value: {
					postalCode: $( '#llms_billing_zip' ).val(),
				}
			} );

		} ).trigger( 'blur' );

		// trigger saved card field change on pageload
		$( '#llms_stripe_saved_card_id' ).trigger( 'change' );

		// when stripe is selected we should trigger a saved card change
		$( '.llms-payment-gateways' ).on( 'llms-gateway-selected', function( e, data ) {
			if ( 'stripe' === data.id ) {

				if ( card._complete ) {
					self.toggle_submit( true );
				} else {
					self.toggle_submit( false );
				}

				$( '#llms_stripe_saved_card_id' ).trigger( 'change' );

				if ( 'create-new' !== $( '#llms_stripe_saved_card_id' ).val() ) {
					card.focus();
				}

			}
		} );

		// if ( self.is_selected ) {
		// 	self.toggle_submit( false );
		// }

	};

	/**
	 * Mount the card
	 * @return   void
	 * @since    4.3.2
	 * @version  4.3.2
	 */
	llms_stripe.mount_card = function() {

		// Add an instance of the card UI component into the `card-element` <div>
		card.mount( '#llms-stripe-card-element' );

		// do stuff when the card is updated
		card.addEventListener( 'change', function( event ) {

			// show error messages when an error exists
			if ( event.error ) {

				llms_stripe.set_error( event.error.message );

			// clear error messages
			} else {

				llms_stripe.set_error( '' );

			}

			// enable the checkout button
			if ( event.complete ) {
				llms_stripe.toggle_submit( true );
			} else {
				llms_stripe.toggle_submit( false );
			}

		} );

	}

	/**
	 * Enable/Disable the checkout/update submit button
	 * @param    bool       toggle  if truthie enables
	 *                              if falsie, disables
	 * @return   void
	 * @since    4.3.0
	 * @version  4.3.0
	 */
	llms_stripe.toggle_submit = function( toggle ) {

		var $btns = $( '#llms_create_pending_order, #llms_save_payment_method' );

		if ( toggle ) {
			$btns.removeAttr( 'disabled' );
		} else {
			$btns.attr( 'disabled', 'disabled' );
		}

	};

	/**
	 * Retrieve a token using Stripe.js (ASYNC)
	 * @param    function  callback  callback function which 2 params
	 *                               success object
	 *                               error object
	 * @return   callback
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	llms_stripe.get_token = function( callback ) {

		var self = this;

		stripe.createToken( card ).then( function( result ) {

			// show error
			if ( result.error ) {

				callback( null, result.error );

			// add the token to the dom
			} else {

				callback( result.token );

			}

		} );

	};

	/**
	 * Determine if Stripe is selected on the list of available payment methods
	 * @return Boolean
	 * @since  2.0.0
	 * @version 4.0.0
	 */
	llms_stripe.is_selected = function() {

		// check the payment method radio element to see if it's checked
		return $( '#llms_payment_gateway_stripe' ).is( ':checked' );

	};

	/**
	 * Add a Stripe Eleents error message
	 * @param    string   msg  error message
	 * @since    4.3.0
	 * @version  4.3.0
	 */
	llms_stripe.set_error = function( msg ) {

		var el = document.getElementById( 'llms-stripe-card-errors' );
		el.textContent = msg;

	};

	/**
	 * Handle checkout submission to retrieve a token when Stripe is the selected gateway
	 * @return  void
	 * @since   4.0.0
	 * @version 4.3.0
	 */
	llms_stripe.submit = function( self, callback ) {

		var $saved = $( '#llms_stripe_saved_card_id' ),
			$form = $( this ),
			response = true;

		// don't proceed unless stripe is selected
		if( ! self.is_selected() ) {
			callback( response );
			return;
		}

		// skip if we're using a saved Card ID
		if ( $saved.length && 'create-new' !== $saved.val() ) {
			callback( response );
			return;
		}

		// get a token
		llms_stripe.get_token( function( token, err ) {

			// error
			if ( ! token ) {

				response = err.message;

			// success
			} else if ( token ) {

				$( '#llms_stripe_token' ).val( token.id );
				$( '#llms_stripe_card_id' ).val( token.card.id );

			}

			callback( response );

		} );

	};

	// add this object to the core checkout class for future binds
	window.llms.checkout.add_gateway( llms_stripe );

	llms_stripe.bind();

} )( jQuery );

