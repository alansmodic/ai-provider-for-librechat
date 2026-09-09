<?php
/**
 * LibreChat provider.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Provider;

use AlanSmodic\AiProviderForLibreChat\Metadata\LibreChatModelMetadataDirectory;
use AlanSmodic\AiProviderForLibreChat\Models\LibreChatTextGenerationModel;
use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for the LibreChat provider.
 *
 * @since 1.0.0
 */
class LibreChatProvider extends AbstractApiProvider {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function baseUrl(): string {
		return Credentials::base_url();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderMetadata(): ProviderMetadata {
		$provider_meta = array(
			Credentials::PROVIDER_ID,
			'LibreChat',
			// Self-hosted, but reached over HTTP like any remote API.
			ProviderTypeEnum::cloud(),
			'https://www.librechat.ai/docs/features/agents_api',
			RequestAuthenticationMethod::apiKey(),
		);

		// Provider description support was added in AI Client 1.2.0.
		if ( version_compare( AiClient::VERSION, '1.2.0', '>=' ) ) {
			$provider_meta[] = function_exists( '__' )
				? __( 'Agents from a self-hosted LibreChat instance, including any models it fronts.', 'ai-provider-for-librechat' )
				: 'Agents from a self-hosted LibreChat instance, including any models it fronts.';
		}

		// Provider logo path support was added in AI Client 1.3.0.
		if ( version_compare( AiClient::VERSION, '1.3.0', '>=' ) ) {
			$provider_meta[] = defined( 'AI_PROVIDER_FOR_LIBRECHAT_PLUGIN_DIR' )
				? AI_PROVIDER_FOR_LIBRECHAT_PLUGIN_DIR . 'includes/Provider/logo.svg'
				: dirname( __DIR__, 2 ) . '/includes/Provider/logo.svg';
		}

		return new ProviderMetadata( ...$provider_meta );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new LibreChatProviderAvailability();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new LibreChatModelMetadataDirectory();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createModel(
		ModelMetadata $model_metadata,
		ProviderMetadata $provider_metadata
	): ModelInterface {
		foreach ( $model_metadata->getSupportedCapabilities() as $capability ) {
			if ( $capability->isTextGeneration() ) {
				return new LibreChatTextGenerationModel( $model_metadata, $provider_metadata );
			}
		}

		throw new RuntimeException(
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message, not output.
			'Unsupported LibreChat agent capabilities for: ' . $model_metadata->getId()
		);
	}
}
