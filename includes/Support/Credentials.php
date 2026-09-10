<?php
/**
 * Credential and endpoint resolution.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the LibreChat API key and instance endpoint.
 *
 * @since 1.0.0
 */
class Credentials {

	/**
	 * Provider ID. Core derives the connector setting and constant names from this.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const PROVIDER_ID = 'librechat';

	/**
	 * Option name Core registers for this connector.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const OPTION_NAME = 'connectors_ai_librechat_api_key';

	/**
	 * Environment variable and PHP constant name for the API key.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const KEY_CONSTANT = 'LIBRECHAT_API_KEY';

	/**
	 * Environment variable and PHP constant name for the instance URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const BASE_URL_CONSTANT = 'LIBRECHAT_BASE_URL';

	/**
	 * Option name used when the instance URL is stored in the database.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const BASE_URL_OPTION = 'ai_provider_for_librechat_base_url';

	/**
	 * Path prefix for the LibreChat Agents API, appended to the instance URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const API_PATH = '/api/agents/v1/';

	/**
	 * Resolves the API key, matching Core's precedence: env var, constant, then option.
	 *
	 * Keys are generated from the LibreChat UI once `remoteAgents.use` and `remoteAgents.create`
	 * are enabled in librechat.yaml.
	 *
	 * @since 1.0.0
	 *
	 * @return string The API key, or an empty string when not configured.
	 */
	public static function api_key(): string {
		$env = getenv( self::KEY_CONSTANT );
		if ( is_string( $env ) && '' !== $env ) {
			return self::sanitize_api_key( $env );
		}

		if ( defined( self::KEY_CONSTANT ) ) {
			$constant = constant( self::KEY_CONSTANT );
			if ( is_string( $constant ) && '' !== $constant ) {
				return self::sanitize_api_key( $constant );
			}
		}

		if ( ! function_exists( 'get_option' ) ) {
			return '';
		}

		$option = get_option( self::OPTION_NAME, '' );

		return is_string( $option ) ? self::sanitize_api_key( $option ) : '';
	}

	/**
	 * Resolves the LibreChat instance URL.
	 *
	 * LibreChat is self-hosted, so there is no shared endpoint and deliberately no default:
	 * without an instance URL the provider reports itself unconfigured rather than guessing.
	 *
	 * @since 1.0.0
	 *
	 * @return string The instance URL without a trailing slash, or an empty string when unset.
	 */
	public static function base_url(): string {
		if ( defined( self::BASE_URL_CONSTANT ) ) {
			$constant = constant( self::BASE_URL_CONSTANT );
			if ( is_string( $constant ) && '' !== $constant ) {
				return self::sanitize_base_url( $constant );
			}
		}

		$env = getenv( self::BASE_URL_CONSTANT );
		if ( is_string( $env ) && '' !== $env ) {
			return self::sanitize_base_url( $env );
		}

		if ( ! function_exists( 'get_option' ) ) {
			return '';
		}

		$option = get_option( self::BASE_URL_OPTION, '' );

		return is_string( $option ) ? self::sanitize_base_url( $option ) : '';
	}

	/**
	 * Whether the instance URL is supplied by a constant or environment variable.
	 *
	 * @since 1.0.1
	 *
	 * @return bool True when settings UI should treat the URL as locked.
	 */
	public static function base_url_is_locked(): bool {
		if ( defined( self::BASE_URL_CONSTANT ) ) {
			$constant = constant( self::BASE_URL_CONSTANT );
			if ( is_string( $constant ) && '' !== $constant ) {
				return true;
			}
		}

		$env = getenv( self::BASE_URL_CONSTANT );

		return is_string( $env ) && '' !== $env;
	}

	/**
	 * Builds an absolute URL for a LibreChat Agents API path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path relative to the API root, such as `chat/completions`.
	 * @return string The absolute URL, or an empty string when the instance URL is unset.
	 */
	public static function url( string $path = '' ): string {
		$base_url = self::base_url();
		if ( '' === $base_url ) {
			return '';
		}

		$path = ltrim( $path, '/' );
		if ( '' === $path || false !== strpos( $path, '..' ) || false !== strpos( $path, "\0" ) ) {
			return $base_url . self::API_PATH;
		}

		return $base_url . self::API_PATH . $path;
	}

	/**
	 * Sanitizes an API key from env, constant, or stored option.
	 *
	 * @since 1.0.1
	 *
	 * @param string $key Raw API key.
	 * @return string Sanitized API key.
	 */
	public static function sanitize_api_key( string $key ): string {
		$key = trim( $key );

		if ( function_exists( 'sanitize_text_field' ) ) {
			return sanitize_text_field( $key );
		}

		return $key;
	}

	/**
	 * Sanitizes a LibreChat instance URL to http(s) only, without a trailing slash.
	 *
	 * Private-network hosts are allowed: self-hosted LibreChat is the intended use. VIP
	 * and `wp_http_validate_url()` would reject those destinations as SSRF protection.
	 *
	 * @since 1.0.1
	 *
	 * @param mixed $url Raw instance URL.
	 * @return string Sanitized URL, or an empty string when invalid.
	 */
	public static function sanitize_base_url( $url ): string {
		if ( ! is_string( $url ) ) {
			return '';
		}

		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		if ( function_exists( 'esc_url_raw' ) ) {
			$url = esc_url_raw( $url, array( 'http', 'https' ) );
		} elseif ( ! preg_match( '#^https?://#i', $url ) ) {
			return '';
		}

		if ( '' === $url ) {
			return '';
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['scheme'] ) ) {
			return '';
		}

		$scheme = strtolower( (string) $parts['scheme'] );
		if ( 'http' !== $scheme && 'https' !== $scheme ) {
			return '';
		}

		return untrailingslashit( $url );
	}
}
