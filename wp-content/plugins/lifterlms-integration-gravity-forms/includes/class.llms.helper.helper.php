<?php
/**
 * Various Functions to help extensions communicate with the LifterLMS Helper
 *
 * @author LifterLMS
 * @since  1.0.0
 * @version 1.0.0
 */

// restrict direct access
if ( ! defined( 'ABSPATH' ) ) { exit; }

// only define the class if it's not already defined
if( ! class_exists( 'LLMS_Helper_Helper' ) ):

class LLMS_Helper_Helper {

	private $option_key = 'llms_helper_show_install_notice';
	private $nonce = 'lifterlms-helper-show-install-notice';
	private $wp_nonce;

	public function __construct() {

		// handle the display of the notice
		add_action( 'admin_init', array( $this, 'maybe_add_notice' ) );

		// add an ajax action to handle save the dismissal
		add_action( 'wp_ajax_llms_helper_helper_save_dismissal', array( $this, 'save_dismissal' ) );

	}




	/**
	 * Determine if the Helper is installed and activated
	 * @return boolean
	 */
	private function is_helper_active() {

		return defined( 'LLMS_HELPER_PLUGIN_FILE' );

	}



	/**
	 * Determine if the Helper is installed
	 * check if the notice has already been dismissed
	 * and add an action to output the notice
	 *
	 * @return void
	 */
	public function maybe_add_notice() {

		// only show notice if the helper is not active
		if( ! $this->is_helper_active() ) {

			// only show if the option is set to show
			if ( 'yes' === get_option( $this->option_key, 'yes' ) )  {

				$this->wp_nonce = wp_create_nonce( $this->nonce );

				// add the action to output the notice
				add_action( 'admin_notices', array( $this, 'output_notice' ) );
				add_action( 'admin_footer', array( $this, 'output_js' ) );

			}

		}

	}


	/**
	 * Output some Javascript that hooks into the dismiss button of the notice and saves the dismissal
	 * @return void
	 */
	public function output_js() {

		?>
			<script>
			( function( $ ) {

				$( document ).ready( function() {

					$( 'body' ).on( 'click', '.notice.is-dismissible.llms-save-dismissal button.notice-dismiss', function() {

						$.ajax( ajaxurl, {

							data: {
								action: 'llms_helper_helper_save_dismissal',
								nonce: '<?php echo $this->wp_nonce; ?>'
							}

						} );

					} );

				} );

			} )( jQuery );
			</script>

		<?php
	}


	/**
	 * Output the HTML of the admin notice
	 * @return void
	 */
	public function output_notice() {

		echo '<div class="notice notice-info is-dismissible llms-save-dismissal">
			<p>' .
				sprintf(
					'%s <a href="https://lifterlms.com/docs/lifterlms-helper/" target="_blank">LifterLMS Helper</a> %s',
					__( 'Install the', 'lifterlms-gravity-forms' ),
					__( 'to start receiving automatic updates for your premium LifterLMS extensions.', 'lifterlms-gravity-forms' )
			 	)
			. '</p>
		</div>';

	}


	/**
	 * Ajax function handler
	 * @return void
	 */
	public function save_dismissal() {

		check_ajax_referer( $this->nonce, 'nonce' );
		update_option( $this->option_key, 'no' );

	}


}

return new LLMS_Helper_Helper();

endif;