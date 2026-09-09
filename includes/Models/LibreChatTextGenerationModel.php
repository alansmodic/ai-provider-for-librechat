<?php
/**
 * LibreChat text generation model.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Models;

use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates text by invoking a LibreChat agent.
 *
 * LibreChat's Agents API implements the OpenAI Chat Completion format, where `model` is the agent
 * ID, so the SDK's OpenAI-compatible base class handles message mapping, multi-turn conversations
 * and token usage. Authentication is a standard bearer token, so the SDK's own
 * ApiKeyRequestAuthentication is used unchanged.
 *
 * @since 1.0.0
 */
class LibreChatTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected function createRequest( HttpMethodEnum $method, string $path, array $headers = array(), $data = null ): Request {
		return new Request(
			$method,
			Credentials::url( $path ),
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}
}
