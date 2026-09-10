<?php
/**
 * LibreChat model metadata directory.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Metadata;

use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleModelMetadataDirectory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discovers the agents exposed by the configured LibreChat instance.
 *
 * LibreChat's Agents API presents each agent as a model, so `GET /models` returns the agents the
 * API key can reach rather than base models. Each entry therefore carries whatever system prompt,
 * tools, files and MCP servers were configured for it inside LibreChat.
 *
 * @since 1.0.0
 */
class LibreChatModelMetadataDirectory extends AbstractOpenAiCompatibleModelMetadataDirectory {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param HttpMethodEnum                     $method  The HTTP method.
	 * @param string                             $path    The API endpoint path, relative to the base URI.
	 * @param array<string, string|list<string>> $headers The request headers.
	 * @param string|array<string, mixed>|null   $data    The request data.
	 * @return Request The request object.
	 */
	protected function createRequest( HttpMethodEnum $method, string $path, array $headers = array(), $data = null ): Request {
		// Metadata directories carry no RequestOptions; those exist only on models.
		return new Request(
			$method,
			Credentials::url( $path ),
			$headers,
			$data
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param Response $response The HTTP response from GET /models.
	 * @return list<ModelMetadata> The agent metadata list.
	 *
	 * @throws ResponseException If the payload is missing the `data` list.
	 */
	protected function parseResponseToModelMetadataList( Response $response ): array {
		$data = $response->getData();

		if ( ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			throw ResponseException::fromMissingData( 'LibreChat', 'data' );
		}

		$models = array();
		foreach ( $data['data'] as $entry ) {
			$agent_id = '';
			$label    = '';

			if ( is_array( $entry ) ) {
				$agent_id = isset( $entry['id'] ) ? (string) $entry['id'] : '';
				// LibreChat may expose a friendlier agent name alongside the ID.
				foreach ( array( 'name', 'display_name' ) as $key ) {
					if ( isset( $entry[ $key ] ) && is_string( $entry[ $key ] ) && '' !== $entry[ $key ] ) {
						$label = sanitize_text_field( $entry[ $key ] );
						break;
					}
				}
			} elseif ( is_string( $entry ) ) {
				$agent_id = $entry;
			}

			$agent_id = sanitize_text_field( $agent_id );
			if ( '' === $agent_id ) {
				continue;
			}

			$models[] = $this->build_model_metadata( $agent_id, '' !== $label ? $label : $agent_id );
		}

		return $models;
	}

	/**
	 * Builds metadata for a single LibreChat agent.
	 *
	 * @since 1.0.0
	 *
	 * @param string $agent_id The agent ID, used as the model ID.
	 * @param string $label    Human readable agent name.
	 * @return ModelMetadata The model metadata.
	 */
	private function build_model_metadata( string $agent_id, string $label ): ModelMetadata {
		$text_only = array( array( ModalityEnum::text() ) );

		$options = array(
			new SupportedOption( OptionEnum::inputModalities(), $text_only ),
			new SupportedOption( OptionEnum::outputModalities(), $text_only ),
			new SupportedOption( OptionEnum::customOptions() ),
		);

		if ( function_exists( 'apply_filters' ) ) {
			/**
			 * Filters the options advertised for LibreChat agents.
			 *
			 * Agents own their own configuration inside LibreChat, so sampling options are not
			 * advertised by default. Use this filter to add options such as temperature or
			 * maxTokens once confirmed that your instance honors them per request.
			 *
			 * @since 1.0.0
			 *
			 * @param list<SupportedOption> $options  The supported options.
			 * @param string                $agent_id The agent ID.
			 */
			$filtered = apply_filters( 'ai_provider_for_librechat_supported_options', $options, $agent_id );

			if ( is_array( $filtered ) ) {
				$options = array();
				foreach ( $filtered as $option ) {
					if ( $option instanceof SupportedOption ) {
						$options[] = $option;
					}
				}
			}
		}

		return new ModelMetadata(
			$agent_id,
			$label,
			array(
				CapabilityEnum::textGeneration(),
				CapabilityEnum::chatHistory(),
			),
			$options
		);
	}
}
