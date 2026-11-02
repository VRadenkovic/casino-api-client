<?php
/**
 * Reads casino data from the configured API.
 *
 * @package CasinoApiClient
 */

namespace CasinoApiClient;

defined( 'ABSPATH' ) || exit;

/**
 * Fetches and caches the casino list.
 */
final class Api_Client {

	public const CACHE_KEY = 'casino_api_client_casinos';

	private const CACHE_TTL = 6 * HOUR_IN_SECONDS;

	private const RETRY_TTL = 5 * MINUTE_IN_SECONDS;

	private const REQUEST_TIMEOUT = 5;

	private const REQUIRED_FIELDS = array(
		'id',
		'name',
		'logo_url',
		'average_rtp',
		'biggest_win_month',
		'payment_delay_hours',
		'monthly_withdrawal_limit',
		'validated_withdrawals_value',
		'monthly_withdrawals_number',
	);

	/**
	 * Returns every casino the API provides.
	 *
	 * @return array[] Casinos, empty when the API is not configured or not available.
	 */
	public static function get_casinos() {
		$casinos = get_transient( self::CACHE_KEY );

		if ( is_array( $casinos ) ) {
			return $casinos;
		}

		$casinos = self::request_casinos();

		if ( null === $casinos ) {
			// A short empty entry keeps pages fast while the API is down.
			set_transient( self::CACHE_KEY, array(), self::RETRY_TTL );
			return array();
		}

		set_transient( self::CACHE_KEY, $casinos, self::CACHE_TTL );

		return $casinos;
	}

	/**
	 * Returns one casino.
	 *
	 * @param string $id Casino identifier.
	 * @return array|null The casino, or null when the API does not provide it.
	 */
	public static function get_casino( $id ) {
		foreach ( self::get_casinos() as $casino ) {
			if ( $casino['id'] === $id ) {
				return $casino;
			}
		}

		return null;
	}

	/**
	 * Drops the cached list, so that the next read asks the API.
	 *
	 * @return void
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Requests the list from the API.
	 *
	 * @return array[]|null Casinos with all required fields, or null on failure.
	 */
	private static function request_casinos() {
		$settings = Settings::get();

		if ( '' === $settings['base_url'] ) {
			return null;
		}

		$response = wp_remote_get(
			untrailingslashit( $settings['base_url'] ) . '/casinos',
			array(
				'timeout' => self::REQUEST_TIMEOUT,
				'headers' => array(
					'Accept'        => 'application/json',
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- HTTP Basic Authentication.
					'Authorization' => 'Basic ' . base64_encode( $settings['username'] . ':' . $settings['password'] ),
				),
			)
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$casinos = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $casinos ) ) {
			return null;
		}

		return array_values( array_filter( $casinos, array( self::class, 'has_required_fields' ) ) );
	}

	/**
	 * Tells whether an API row can be displayed.
	 *
	 * @param mixed $casino API row.
	 * @return bool
	 */
	private static function has_required_fields( $casino ) {
		return is_array( $casino ) && ! array_diff( self::REQUIRED_FIELDS, array_keys( $casino ) );
	}
}
