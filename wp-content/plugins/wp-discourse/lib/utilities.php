<?php
/**
 * Static utility functions used throughout the plugin.
 *
 * @package WPDiscourse
 */

namespace WPDiscourse\Utilities;

/**
 * Class Utilities
 *
 * @package WPDiscourse
 */
class Utilities {

	/**
	 * Returns a single array of options from a given array of arrays.
	 *
	 * @return array
	 */
	public static function get_options() {
		static $options = array();

		if ( empty( $options ) ) {
			$discourse_option_groups = get_option( 'discourse_option_groups' );
			if ( $discourse_option_groups ) {
				foreach ( $discourse_option_groups as $option_name ) {
					if ( get_option( $option_name ) ) {
						$option  = get_option( $option_name );
						$options = array_merge( $options, $option );
					}
				}

				$multisite_configuration_enabled = get_site_option( 'wpdc_multisite_configuration' );
				if ( 1 === intval( $multisite_configuration_enabled ) ) {
					$site_options = get_site_option( 'wpdc_site_options' );
					foreach ( $site_options as $key => $value ) {
						$options[ $key ] = $value;
					}
				}
			}
		}

		return apply_filters( 'wpdc_utilities_options_array', $options );
	}

	/**
	 * Checks the connection status to Discourse.
	 *
	 * @return int|\WP_Error
	 */
	public static function check_connection_status() {
		$options      = self::get_options();
		$url          = ! empty( $options['url'] ) ? $options['url'] : null;
		$api_key      = ! empty( $options['api-key'] ) ? $options['api-key'] : null;
		$api_username = ! empty( $options['publish-username'] ) ? $options['publish-username'] : null;

		if ( ! ( $url && $api_key && $api_username ) ) {

			return 0;
		}

		$url = add_query_arg(
			array(
				'api_key'      => $api_key,
				'api_username' => $api_username,
			), $url . '/users/' . $api_username . '.json'
		);

		$url      = esc_url_raw( $url );
		$response = wp_remote_get( $url );

		return self::validate( $response );
	}

	/**
	 * Validates the response from `wp_remote_get` or `wp_remote_post`.
	 *
	 * @param array $response The response from `wp_remote_get` or `wp_remote_post`.
	 *
	 * @return int
	 */
	public static function validate( $response ) {
		if ( empty( $response ) ) {

			return 0;
		} elseif ( is_wp_error( $response ) ) {

			return 0;

			// There is a response from the server, but it's not what we're looking for.
		} elseif ( intval( wp_remote_retrieve_response_code( $response ) ) !== 200 ) {

			return 0;
		} else {
			// Valid response.
			return 1;
		}
	}

	/**
	 * Gets the Discourse categories.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_discourse_categories() {
		$options      = self::get_options();
		$force_update = false;

		$categories = get_option( 'wpdc_discourse_categories' );

		if ( ! empty( $options['publish-category-update'] ) || ! $categories ) {
			$force_update = true;
		}

		if ( $force_update ) {
			$base_url     = ! empty( $options['url'] ) ? $options['url'] : null;
			$api_key      = ! empty( $options['api-key'] ) ? $options['api-key'] : null;
			$api_username = ! empty( $options['publish-username'] ) ? $options['publish-username'] : null;

			if ( ! ( $base_url && $api_key && $api_username ) ) {

				return new \WP_Error( 'discourse_configuration_error', 'The Discourse connection options have not been configured.' );
			}

			$site_url = esc_url_raw( "{$base_url}/site.json" );
			$site_url = add_query_arg(
				array(
					'api_key'      => $api_key,
					'api_username' => $api_username,
				), $site_url
			);

			$remote = wp_remote_get( $site_url );

			if ( ! self::validate( $remote ) ) {

				return new \WP_Error( 'connection_not_established', 'There was an error establishing a connection with Discourse' );
			}

			$remote = json_decode( wp_remote_retrieve_body( $remote ), true );
			if ( array_key_exists( 'categories', $remote ) ) {
				$categories = $remote['categories'];
				if ( empty( $options['display-subcategories'] ) ) {
					foreach ( $categories as $category => $values ) {
						if ( array_key_exists( 'parent_category_id', $values ) ) {
							unset( $categories[ $category ] );
						}
					}
				}
				update_option( 'wpdc_discourse_categories', $categories );
			} else {

				return new \WP_Error( 'key_not_found', 'The categories key was not found in the response from Discourse.' );
			}
		}// End if().

		return $categories;
	}

	/**
	 * Tries to find a WordPress post that's associated with a Discourse topic_id.
	 *
	 * @param int $topic_id The topic_id to lookup.
	 *
	 * @return null|string
	 */
	public static function get_post_id_by_topic_id( $topic_id ) {
		global $wpdb;

		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'discourse_topic_id' AND meta_value = %d", $topic_id ) );

		return $post_id;
	}

	/**
	 * Check if an user is linked to a discourse instance
	 *
	 * @return boolean
	 */
	public static function user_is_linked_to_sso() {
		$user = wp_get_current_user();

		if ( ! $user ) {
			return false;
		}

		return get_user_meta( $user->ID, 'discourse_sso_user_id', true );
	}

	/**
	 * Get a Discourse user object.
	 *
	 * @param int  $user_id The WordPress user_id.
	 * @param bool $match_by_email Whether or not to attempt to get the user by their email address.
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public static function get_discourse_user( $user_id, $match_by_email = false ) {
		$api_credentials = self::get_api_credentials();
		if ( is_wp_error( $api_credentials ) ) {

			return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
		}

		$external_user_url = "{$api_credentials['url']}/users/by-external/{$user_id}.json";
		$external_user_url = esc_url_raw(
			add_query_arg(
				array(
					'api_key'      => $api_credentials['api_key'],
					'api_username' => $api_credentials['api_username'],
				), $external_user_url
			)
		);

		$response = wp_remote_get( $external_user_url );

		if ( self::validate( $response ) ) {

			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $body->user ) ) {

				return $body->user;
			}
		}

		if ( $match_by_email ) {
			$user = get_user_by( 'id', $user_id );

			if ( ! empty( $user ) && ! is_wp_error( $user ) ) {

				return self::get_discourse_user_by_email( $user->user_email );
			} else {

				return new \WP_Error( 'wpdc_param_error', 'There is no WordPress user with the supplied id.' );
			}
		}

		return new \WP_Error( 'wpdc_response_error', 'The Discourse user could not be retrieved.' );
	}

	/**
	 * Gets a Discourse user by their email address.
	 *
	 * @param string $email The email address to search for.
	 *
	 * @return object \WP_Error
	 */
	public static function get_discourse_user_by_email( $email ) {
		$api_credentials = self::get_api_credentials();
		if ( is_wp_error( $api_credentials ) ) {

			return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
		}

		$users_url = "{$api_credentials['url']}/admin/users/list/all.json";
		$users_url = esc_url_raw(
			add_query_arg(
				array(
					'email'        => rawurlencode_deep( $email ),
					'filter'       => rawurlencode_deep( $email ),
					'api_key'      => $api_credentials['api_key'],
					'api_username' => $api_credentials['api_username'],
				), $users_url
			)
		);

		$response = wp_remote_get( $users_url );
		if ( self::validate( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			// The reqest returns a valid response even if the user isn't found, so check for empty.
			if ( ! empty( $body ) && ! empty( $body[0] ) ) {

				return $body[0];
			} else {

				// A valid response was returned, but the user wasn't found.
				return new \WP_Error( 'wpdc_response_error', 'The user could not be retrieved by their email address.' );
			}
		} else {

			return new \WP_Error( 'wpdc_response_error', 'An invalid response was returned when trying to find the user by email address.' );
		}
	}

	/**
	 * Creates a Discourse user through the API.
	 *
	 * @param \WP_User $user The WordPress user.
	 * @param bool     $require_activation Whether or not to require an activation email to be sent.
	 *
	 * @return int|\WP_Error
	 */
	public static function create_discourse_user( $user, $require_activation = true ) {
		// Keeps track of the number of attempts to create a unique username. Set to a max of 3.
		static $username_instance = 0;

		$api_credentials = self::get_api_credentials();
		if ( is_wp_error( $api_credentials ) ) {

			return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
		}

		if ( empty( $user ) || empty( $user->ID ) || is_wp_error( $user ) ) {

			return new \WP_Error( 'wpdc_user_not_set_error', 'The Discourse user you are attempting to create does not exist on WordPress.' );
		}

		$user_id = $user->ID;

		$require_activation = apply_filters( 'wpdc_auto_create_user_require_activation', $require_activation, $user );
		$create_user_url    = esc_url_raw( "{$api_credentials['url']}/users" );
		$username           = 0 === $username_instance ? $user->user_login : $user->user_login . $username_instance;
		$name               = $user->display_name;
		$email              = $user->user_email;
		$password           = wp_generate_password( 20 );
		$response           = wp_remote_post(
			$create_user_url, array(
				'method' => 'POST',
				'body'   => array(
					'api_key'      => $api_credentials['api_key'],
					'api_username' => $api_credentials['api_username'],
					'name'         => $name,
					'email'        => $email,
					'password'     => $password,
					'username'     => $username,
					'active'       => $require_activation ? 'false' : 'true',
					'approved'     => 'true',
				),
			)
		);

		if ( ! self::validate( $response ) ) {

			return new \WP_Error( 'wpdc_response_error', 'An error was returned from Discourse when attempting to create a user.' );
		}

		$user_data = json_decode( wp_remote_retrieve_body( $response ) );

		// The username is taken on Discourse.
		if ( ! empty( $user_data->errors ) && ! empty( $user_data->errors->username ) && $username_instance < 2 ) {
			$username_instance ++;
			self::create_discourse_user( $user, $require_activation );

			return new \WP_Error( 'wpdc_username_taken_error', 'The Discourse username is already taken. Attempting to create another user.' );
		}

		if ( isset( $user_data->user_id ) ) {
			update_user_meta( $user_id, 'discourse_sso_user_id', $user_data->user_id );
			update_user_meta( $user_id, 'discourse_username', $username );

			return $user_data->user_id;
		}

		return new \WP_Error( 'wpdc_response_error', 'The Discourse user could not be created.' );
	}

	/**
	 * Gets the Discourse groups and saves the non-automatic groups in a transient.
	 *
	 * The transient has an expiry time of 10 minutes.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_discourse_groups() {
		$api_credentials = self::get_api_credentials();
		if ( is_wp_error( $api_credentials ) ) {

			return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
		}

		$groups_url = "{$api_credentials['url']}/groups.json";
		$groups_url = esc_url_raw(
			add_query_arg(
				array(
					'api_key'      => $api_credentials['api_key'],
					'api_username' => $api_credentials['api_username'],
				), $groups_url
			)
		);

		$response = wp_remote_get( $groups_url );

		if ( ! self::validate( $response ) ) {

			return new \WP_Error( 'wpdc_response_error', 'An invalid response was returned from Discourse when retrieving Discourse groups data' );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! empty( $response->groups ) ) {
			$groups               = $response->groups;
			$non_automatic_groups = [];

			foreach ( $groups as $group ) {
				if ( empty( $group->automatic ) ) {
					$non_automatic_groups[] = $group;
				}
			}
			set_transient( 'wpdc_non_automatic_groups', $non_automatic_groups, 10 * MINUTE_IN_SECONDS );

			return $non_automatic_groups;
		}

		return new \WP_Error( 'wpdc_response_error', 'No groups were returned from Discourse.' );
	}

	/**
	 * Adds a WordPress user to a Discourse group.
	 *
	 * @param int    $user_id The user id.
	 * @param string $group_name The Discourse group to add the user to.
	 * @param bool   $force_update Whether or not to force an update of the Discourse group transient.
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public static function add_user_to_discourse_group( $user_id, $group_name, $force_update = false ) {
		$discourse_id = self::get_discourse_id( $user_id );
		$group_id     = self::get_discourse_group_id_from_name( $group_name, $force_update );

		if ( is_wp_error( $discourse_id ) || is_wp_error( $group_id ) ) {

			return new \WP_Error( 'wpdc_groups_error', 'Cannot add user to group. An error was returned when retrieving either the discourse_id or discourse_group_id.' );
		}

		if ( $discourse_id && $group_id ) {
			$api_credentials = self::get_api_credentials();
			if ( is_wp_error( $api_credentials ) ) {

				return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
			}

			$group_url = esc_url_raw( "{$api_credentials['url']}/admin/groups/{$group_id}/members.json" );

			$response = wp_remote_post(
				$group_url, array(
					'method' => 'PUT',
					'body'   => array(
						'user_ids'     => $discourse_id,
						'api_key'      => $api_credentials['api_key'],
						'api_username' => $api_credentials['api_username'],
					),
				)
			);

			$response = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $response->errors ) ) {

				return new \WP_Error( 'wpdc_groups_error', $response->errors );
			} else {

				return $response;
			}
		}

		return new \WP_Error( 'wpdc_groups_error', 'The user could not be added to the group.' );
	}

	/**
	 * Removes a WordPress user from a Discourse group.
	 *
	 * @param int    $user_id The WordPress user id.
	 * @param string $group_name The Discourse group name.
	 * @param bool   $force_update Whether or not to force an update of the Discourse group data transient.
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public static function remove_user_from_discourse_group( $user_id, $group_name, $force_update = false ) {
		$discourse_id = self::get_discourse_id( $user_id );
		$group_id     = self::get_discourse_group_id_from_name( $group_name, $force_update );

		if ( is_wp_error( $discourse_id ) || is_wp_error( $group_id ) ) {

			return new \WP_Error( 'wpdc_groups_error', 'Cannot remove user from group. An error was returned when retrieving either the discourse_id or discourse_group_id.' );
		}

		if ( $discourse_id && $group_id ) {
			$api_credentials = self::get_api_credentials();
			if ( is_wp_error( $api_credentials ) ) {

				return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
			}

			$group_url = esc_url_raw( "{$api_credentials['url']}/admin/groups/{$group_id}/members.json" );

			$response = wp_remote_post(
				$group_url, array(
					'method' => 'DELETE',
					'body'   => array(
						'user_id'      => $discourse_id,
						'api_key'      => $api_credentials['api_key'],
						'api_username' => $api_credentials['api_username'],
					),
				)
			);

			$response = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $response->errors ) ) {

				return new \WP_Error( 'wpdc_groups_error', $response->errors );
			} else {

				return $response;
			}
		}

		return new \WP_Error( 'wpdc_groups_error', 'The user could not be added to the group.' );
	}

	/**
	 * Get the Discourse about.json route.
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public static function get_discourse_stats() {
		$api_credentials = self::get_api_credentials();
		if ( is_wp_error( $api_credentials ) ) {

			return new \WP_Error( 'wpdc_get_user_error', 'The WP Discourse plugin is not properly configured.' );
		}

		$about_url = esc_url_raw( "{$api_credentials['url']}/about.json" );
		$about_url = add_query_arg(
			array(
				'api_key'      => $api_credentials['api_key'],
				'api_username' => $api_credentials['api_username'],
			), $about_url
		);

		$response = wp_remote_get( $about_url );

		if ( ! self::validate( $response ) ) {

			return new \WP_Error( 'wpdc_response_error', 'The Discourse stats could not be returned.' );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		return $response;
	}

	/**
	 * Verify that the request originated from a Discourse webhook and the the secret keys match.
	 *
	 * @param \WP_REST_Request $data The WP_REST_Request object.
	 *
	 * @return \WP_Error|\WP_REST_Request
	 */
	public static function verify_discourse_webhook_request( $data ) {
		$options = self::get_options();
		// The X-Discourse-Event-Signature consists of 'sha256=' . hamc of raw payload.
		// It is generated by computing `hash_hmac( 'sha256', $payload, $secret )`.
		$sig = substr( $data->get_header( 'X-Discourse-Event-Signature' ), 7 );
		if ( $sig ) {
			$payload = $data->get_body();
			// Key used for verifying the request - a matching key needs to be set on the Discourse webhook.
			$secret = ! empty( $options['webhook-secret'] ) ? $options['webhook-secret'] : '';

			if ( ! $secret ) {

				return new \WP_Error( 'discourse_webhook_configuration_error', 'The webhook secret key has not been set.' );
			}

			if ( hash_hmac( 'sha256', $payload, $secret ) === $sig ) {

				return $data;
			} else {

				return new \WP_Error( 'discourse_webhook_authentication_error', 'Discourse Webhook Request Error: signatures did not match.' );
			}
		}

		return new \WP_Error( 'discourse_webhook_authentication_error', 'Discourse Webhook Request Error: the X-Discourse-Event-Signature was not set for the request.' );
	}

	/**
	 * Gets the Discourse group_id from the group name.
	 *
	 * @param string $group_name The Discourse group name.
	 * @param bool   $force_update Whether or not to force an update of the groups data transient.
	 *
	 * @return \WP_Error
	 */
	protected static function get_discourse_group_id_from_name( $group_name, $force_update = false ) {
		$discourse_groups = get_transient( 'wpdc_non_automatic_groups' );
		if ( empty( $discourse_groups ) || $force_update ) {
			$discourse_groups = self::get_discourse_groups();

			if ( empty( $discourse_groups ) || is_wp_error( $discourse_groups ) ) {

				return new \WP_Error( 'wpdc_response_error', 'The groups data could not be returned from Discourse.' );
			}
		}

		foreach ( $discourse_groups as $group ) {
			if ( $group->name === $group_name ) {

				return $group->id;
			}
		}

		return new \WP_Error( 'wpdc_group_not_found', 'A Discourse group was not found with the name you provided.' );
	}

	/**
	 * Gets the Discourse user id from the WordPress user id.
	 *
	 * @param int $user_id The WordPress user id.
	 *
	 * @return int|mixed|\WP_Error
	 */
	protected static function get_discourse_id( $user_id ) {
		$discourse_id = get_user_meta( $user_id, 'discourse_sso_user_id', true );
		if ( empty( $discourse_id ) ) {

			$discourse_user = self::get_discourse_user( $user_id, true );
			if ( ! empty( $discourse_user ) && ! is_wp_error( $discourse_user ) ) {

				$discourse_id = $discourse_user->id;
				update_user_meta( $user_id, 'discourse_sso_user_id', $discourse_id );
			} else {

				$require_activation = ( 1 === get_user_meta( $user_id, 'discourse_email_not_verified', true ) );
				$user               = get_user_by( 'id', $user_id );
				if ( empty( $user ) || is_wp_error( $user ) ) {

					return new \WP_Error( 'wpdc_user_does_not_exist_error', 'The user you are trying to add to a Discourse group does not exist on WordPress.' );
				}

				$discourse_id = self::create_discourse_user( $user, $require_activation );

				if ( empty( $discourse_id ) || is_wp_error( $discourse_id ) ) {

					return new \WP_Error( 'wpdc_response_error', 'Unable to create a Discourse user to add to the group.' );
				}
			}
		}

		return $discourse_id;
	}

	/**
	 * Gets the Discourse API credentials from the options array.
	 *
	 * @return array|\WP_Error
	 */
	protected static function get_api_credentials() {
		$options      = self::get_options();
		$url          = ! empty( $options['url'] ) ? $options['url'] : null;
		$api_key      = ! empty( $options['api-key'] ) ? $options['api-key'] : null;
		$api_username = ! empty( $options['publish-username'] ) ? $options['publish-username'] : null;

		if ( ! ( $url && $api_key && $api_username ) ) {

			return new \WP_Error( 'wpdc_configuration_error', 'The Discourse configuration options have not been set.' );
		}

		return array(
			'url'          => $url,
			'api_key'      => $api_key,
			'api_username' => $api_username,
		);
	}
}
