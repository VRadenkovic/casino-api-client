<?php
/**
 * Connection settings for the casino API.
 *
 * @package CasinoApiClient
 */

namespace CasinoApiClient;

defined( 'ABSPATH' ) || exit;

/**
 * Stores the API connection and renders its settings page.
 */
final class Settings {

	public const OPTION = 'casino_api_client_settings';

	private const PAGE = 'casino-api-client';

	private const DEFAULTS = array(
		'base_url' => '',
		'username' => '',
		'password' => '',
	);

	/**
	 * Hooks the settings into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( self::class, 'register_fields' ) );
		add_action( 'admin_menu', array( self::class, 'add_page' ) );
		add_action( 'add_option_' . self::OPTION, array( Api_Client::class, 'flush_cache' ) );
		add_action( 'update_option_' . self::OPTION, array( Api_Client::class, 'flush_cache' ) );
	}

	/**
	 * Returns the stored connection.
	 *
	 * @return array{base_url: string, username: string, password: string}
	 */
	public static function get() {
		return wp_parse_args( get_option( self::OPTION, array() ), self::DEFAULTS );
	}

	/**
	 * Cleans submitted values.
	 *
	 * @param mixed $input Submitted settings.
	 * @return array{base_url: string, username: string, password: string}
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$password = isset( $input['password'] ) ? (string) $input['password'] : '';

		return array(
			'base_url' => esc_url_raw( trim( $input['base_url'] ?? '' ), array( 'http', 'https' ) ),
			'username' => sanitize_text_field( $input['username'] ?? '' ),
			// The password is never printed back, so an empty field means no change.
			'password' => '' === $password ? self::get()['password'] : $password,
		);
	}

	/**
	 * Registers the option and its fields.
	 *
	 * @return void
	 */
	public static function register_fields() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => self::DEFAULTS,
			)
		);

		add_settings_section( 'connection', '', '__return_empty_string', self::PAGE );

		$fields = array(
			'base_url' => array(
				'label'       => __( 'API base URL', 'casino-api-client' ),
				'type'        => 'url',
				'description' => __( 'The plugin requests /casinos from this address.', 'casino-api-client' ),
			),
			'username' => array(
				'label'       => __( 'Username', 'casino-api-client' ),
				'type'        => 'text',
				'description' => '',
			),
			'password' => array(
				'label'       => __( 'Password', 'casino-api-client' ),
				'type'        => 'password',
				'description' => __( 'Leave empty to keep the saved password.', 'casino-api-client' ),
			),
		);

		foreach ( $fields as $name => $field ) {
			add_settings_field(
				$name,
				$field['label'],
				array( self::class, 'render_field' ),
				self::PAGE,
				'connection',
				array(
					'label_for'   => self::PAGE . '-' . $name,
					'name'        => $name,
					'type'        => $field['type'],
					'description' => $field['description'],
				)
			);
		}
	}

	/**
	 * Adds the page under Settings.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_options_page(
			__( 'Casino API Client', 'casino-api-client' ),
			__( 'Casino API Client', 'casino-api-client' ),
			'manage_options',
			self::PAGE,
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Renders one field.
	 *
	 * @param array $field Field name, type, description and input id.
	 * @return void
	 */
	public static function render_field( $field ) {
		$is_password = 'password' === $field['type'];
		$value       = $is_password ? '' : self::get()[ $field['name'] ];

		printf(
			'<input id="%1$s" class="regular-text" type="%2$s" name="%3$s" value="%4$s" autocomplete="%5$s" />',
			esc_attr( $field['label_for'] ),
			esc_attr( $field['type'] ),
			esc_attr( self::OPTION . '[' . $field['name'] . ']' ),
			esc_attr( $value ),
			$is_password ? 'new-password' : 'off'
		);

		if ( '' !== $field['description'] ) {
			printf( '<p class="description">%s</p>', esc_html( $field['description'] ) );
		}
	}

	/**
	 * Renders the page.
	 *
	 * @return void
	 */
	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
