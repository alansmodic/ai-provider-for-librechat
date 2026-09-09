<?php
/**
 * Plugin initializer class.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat;

use AlanSmodic\AiProviderForLibreChat\Provider\LibreChatProvider;
use AlanSmodic\AiProviderForLibreChat\Settings\LibreChatSettings;
use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin class.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		$settings = new LibreChatSettings();
		$settings->init();

		add_filter(
			'plugin_action_links_' . plugin_basename( AI_PROVIDER_FOR_LIBRECHAT_PLUGIN_FILE ),
			array( $this, 'plugin_action_links' )
		);

		if ( ! class_exists( AiClient::class ) ) {
			add_action( 'admin_notices', array( $this, 'missing_ai_client_notice' ) );
			return;
		}

		add_action( 'init', array( $this, 'register_provider' ), 5 );
		add_action( 'init', array( $this, 'register_authentication' ), 25 );
	}

	/**
	 * Displays an admin notice when the WordPress AI Client is unavailable.
	 *
	 * @since 1.0.1
	 */
	public function missing_ai_client_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p>
				<?php
				esc_html_e(
					'AI Provider for LibreChat requires the WordPress AI Client (WordPress 7.0 or later). The provider was not registered.',
					'ai-provider-for-librechat'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Registers the LibreChat provider with the AI Client.
	 *
	 * @since 1.0.0
	 */
	public function register_provider(): void {
		if ( ! class_exists( AiClient::class ) ) {
			return;
		}

		$registry = AiClient::defaultRegistry();

		if ( $registry->hasProvider( LibreChatProvider::class ) ) {
			return;
		}

		$registry->registerProvider( LibreChatProvider::class );
	}

	/**
	 * Registers bearer authentication when Core has not already done so.
	 *
	 * Core passes stored connector keys to the AI Client at priority 20, but skips keys supplied
	 * by an environment variable or constant. This fills that gap without overriding Core.
	 *
	 * @since 1.0.0
	 */
	public function register_authentication(): void {
		if ( ! class_exists( AiClient::class ) ) {
			return;
		}

		$api_key = Credentials::api_key();
		if ( '' === $api_key ) {
			return;
		}

		$registry = AiClient::defaultRegistry();

		if ( ! $registry->hasProvider( Credentials::PROVIDER_ID ) ) {
			return;
		}

		if ( null !== $registry->getProviderRequestAuthentication( Credentials::PROVIDER_ID ) ) {
			return;
		}

		$registry->setProviderRequestAuthentication(
			Credentials::PROVIDER_ID,
			new ApiKeyRequestAuthentication( $api_key )
		);
	}

	/**
	 * Adds a settings link to the plugin list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $links Existing action links.
	 * @return array<string> Modified action links.
	 */
	public function plugin_action_links( array $links ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'options-general.php?page=' . LibreChatSettings::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'ai-provider-for-librechat' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}
