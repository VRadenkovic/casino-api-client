<?php
/**
 * Plugin Name:       Casino API Client
 * Plugin URI:        https://github.com/VRadenkovic/casino-api-client
 * Description:       Gutenberg block that displays casino cards from a REST API.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            Vladimir Radenković
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       casino-api-client
 *
 * @package CasinoApiClient
 */

namespace CasinoApiClient;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-api-client.php';
require_once __DIR__ . '/includes/class-rest-controller.php';
require_once __DIR__ . '/includes/class-settings.php';

Settings::init();

add_action( 'rest_api_init', array( Rest_Controller::class, 'register_routes' ) );

add_action(
	'init',
	static function () {
		register_block_type( __DIR__ . '/build/casino-card' );
	}
);
