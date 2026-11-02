<?php
/**
 * Removes the stored credentials and cached data on uninstall.
 *
 * @package CasinoApiClient
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'casino_api_client_settings' );
delete_transient( 'casino_api_client_casinos' );
