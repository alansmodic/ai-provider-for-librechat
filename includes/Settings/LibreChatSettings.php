<?php
/**
 * LibreChat instance URL settings.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.1
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Settings;

use AlanSmodic\AiProviderForLibreChat\Support\Credentials;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page under Settings > LibreChat for the instance URL.
 *
 * The API key is stored by Core on Settings > Connectors. This screen only
 * collects the self-hosted instance URL, with sanitization via the Settings API.
 *
 * @since 1.0.1
 */
class LibreChatSettings {

	/**
	 * Settings API option group.
	 *
	 * @since 1.0.1
	 * @var string
	 */
	private const OPTION_GROUP = 'ai_provider_for_librechat_settings';

	/**
	 * Settings page slug.
	 *
	 * @since 1.0.1
	 * @var string
	 */
	public const PAGE_SLUG = 'ai-provider-for-librechat';

	/**
	 * Settings section ID.
	 *
	 * @since 1.0.1
	 * @var string
	 */
	private const SECTION_ID = 'ai_provider_for_librechat_main';

	/**
	 * Initializes the settings.
	 *
	 * @since 1.0.1
	 */
	public function init(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
	}

	/**
	 * Registers the setting and settings fields.
	 *
	 * @since 1.0.1
	 */
	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			Credentials::BASE_URL_OPTION,
			array(
				'type'              => 'string',
				'description'       => __( 'Base URL of the self-hosted LibreChat instance.', 'ai-provider-for-librechat' ),
				'sanitize_callback' => array( Credentials::class, 'sanitize_base_url' ),
				'default'           => '',
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			self::SECTION_ID,
			'',
			array( $this, 'render_section' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			Credentials::BASE_URL_OPTION,
			__( 'Instance URL', 'ai-provider-for-librechat' ),
			array( $this, 'render_base_url_field' ),
			self::PAGE_SLUG,
			self::SECTION_ID,
			array( 'label_for' => Credentials::BASE_URL_OPTION )
		);
	}

	/**
	 * Registers the settings screen.
	 *
	 * @since 1.0.1
	 */
	public function register_settings_screen(): void {
		add_options_page(
			__( 'LibreChat Settings', 'ai-provider-for-librechat' ),
			__( 'LibreChat', 'ai-provider-for-librechat' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_screen' )
		);
	}

	/**
	 * Renders the settings screen.
	 *
	 * @since 1.0.1
	 */
	public function render_screen(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the settings section introduction.
	 *
	 * @since 1.0.1
	 */
	public function render_section(): void {
		$connectors_url = admin_url( 'options-connectors.php' );
		?>
		<p>
			<?php
			printf(
				/* translators: 1: opening anchor tag, 2: closing anchor tag, 3: wrapped constant name */
				esc_html__( 'Set the URL of your self-hosted LibreChat instance. Store the API key on the %1$sSettings > Connectors%2$s screen, or as the %3$s environment variable / PHP constant.', 'ai-provider-for-librechat' ),
				'<a href="' . esc_url( $connectors_url ) . '">',
				'</a>',
				'<code>' . esc_html( Credentials::KEY_CONSTANT ) . '</code>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Renders the instance URL field.
	 *
	 * @since 1.0.1
	 */
	public function render_base_url_field(): void {
		$locked = Credentials::base_url_is_locked();
		$value  = Credentials::base_url();
		?>
		<input
			type="url"
			id="<?php echo esc_attr( Credentials::BASE_URL_OPTION ); ?>"
			name="<?php echo esc_attr( Credentials::BASE_URL_OPTION ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
			placeholder="https://librechat.example.com"
			<?php wp_readonly( $locked ); ?>
		/>
		<p class="description">
			<?php
			if ( $locked ) {
				printf(
					/* translators: %s: PHP constant / environment variable name */
					esc_html__( 'This value is locked by the %s constant or environment variable.', 'ai-provider-for-librechat' ),
					'<code>' . esc_html( Credentials::BASE_URL_CONSTANT ) . '</code>'
				);
			} else {
				printf(
					/* translators: %s: PHP constant / environment variable name */
					esc_html__( 'HTTPS is recommended. You can also set %s in wp-config.php or the environment; those sources override this field.', 'ai-provider-for-librechat' ),
					'<code>' . esc_html( Credentials::BASE_URL_CONSTANT ) . '</code>'
				);
			}
			?>
		</p>
		<?php
	}
}
