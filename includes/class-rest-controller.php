<?php
/**
 * REST route for the block editor.
 *
 * @package CasinoApiClient
 */

namespace CasinoApiClient;

defined( 'ABSPATH' ) || exit;

/**
 * Gives editors the list of casinos to choose from.
 */
final class Rest_Controller {

	private const ROUTE_NAMESPACE = 'casino-api-client/v1';

	/**
	 * Registers the route.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/casinos',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_items' ),
				'permission_callback' => array( self::class, 'can_edit_posts' ),
			)
		);
	}

	/**
	 * Limits the route to users who can place the block.
	 *
	 * @return bool
	 */
	public static function can_edit_posts() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Returns the identifier and name of each casino.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_items() {
		$items = array_map(
			static function ( $casino ) {
				return array(
					'id'   => $casino['id'],
					'name' => $casino['name'],
				);
			},
			Api_Client::get_casinos()
		);

		return rest_ensure_response( $items );
	}
}
