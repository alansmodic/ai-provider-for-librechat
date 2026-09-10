<?php
/**
 * Tests for credential and URL resolution.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Tests\Unit\Support;

use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use AlanSmodic\AiProviderForLibreChat\Tests\TestCase;

/**
 * @covers \AlanSmodic\AiProviderForLibreChat\Support\Credentials
 */
class CredentialsTest extends TestCase {

	/**
	 * Tests that trailing slashes are stripped from valid http(s) URLs.
	 */
	public function test_sanitize_base_url_strips_trailing_slash(): void {
		$this->assertSame(
			'https://librechat.example.com',
			Credentials::sanitize_base_url( 'https://librechat.example.com/' )
		);
	}

	/**
	 * Tests that private-network hosts are kept (self-hosted LibreChat).
	 */
	public function test_sanitize_base_url_allows_private_hosts(): void {
		$this->assertSame(
			'http://192.168.1.10:3080',
			Credentials::sanitize_base_url( 'http://192.168.1.10:3080/' )
		);
		$this->assertSame(
			'http://localhost:3080',
			Credentials::sanitize_base_url( 'http://localhost:3080' )
		);
	}

	/**
	 * Tests that non-http(s) URLs and non-strings are rejected.
	 */
	public function test_sanitize_base_url_rejects_invalid_values(): void {
		$this->assertSame( '', Credentials::sanitize_base_url( 'javascript:alert(1)' ) );
		$this->assertSame( '', Credentials::sanitize_base_url( 'ftp://librechat.internal' ) );
		$this->assertSame( '', Credentials::sanitize_base_url( 'not-a-url' ) );
		$this->assertSame( '', Credentials::sanitize_base_url( array( 'https://x.example' ) ) );
		$this->assertSame( '', Credentials::sanitize_base_url( '' ) );
	}

	/**
	 * Tests that url() prefixes the Agents API path.
	 */
	public function test_url_appends_agents_api_path(): void {
		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://lc.example' );

		$this->assertSame(
			'https://lc.example/api/agents/v1/chat/completions',
			Credentials::url( 'chat/completions' )
		);
		$this->assertSame(
			'https://lc.example/api/agents/v1/models',
			Credentials::url( '/models' )
		);
	}

	/**
	 * Tests that url() ignores traversal and empty paths.
	 */
	public function test_url_blocks_path_traversal(): void {
		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://lc.example' );

		$this->assertSame(
			'https://lc.example/api/agents/v1/',
			Credentials::url( '../secret' )
		);
		$this->assertSame(
			'https://lc.example/api/agents/v1/',
			Credentials::url( '' )
		);
	}

	/**
	 * Tests that url() is empty when the instance URL is unset.
	 */
	public function test_url_is_empty_without_base_url(): void {
		$this->assertSame( '', Credentials::url( 'models' ) );
	}

	/**
	 * Tests that the API key prefers the environment variable over the option.
	 */
	public function test_api_key_prefers_env_over_option(): void {
		$this->set_env( 'LIBRECHAT_API_KEY', 'from-env' );
		update_option( Credentials::OPTION_NAME, 'from-db' );

		$this->assertSame( 'from-env', Credentials::api_key() );
	}

	/**
	 * Tests that the API key falls back to the stored option.
	 */
	public function test_api_key_falls_back_to_option(): void {
		update_option( Credentials::OPTION_NAME, ' from-db ' );

		$this->assertSame( 'from-db', Credentials::api_key() );
	}

	/**
	 * Tests that the base URL prefers the environment variable over the option.
	 */
	public function test_base_url_prefers_env_over_option(): void {
		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://from-env.example/' );
		update_option( Credentials::BASE_URL_OPTION, 'https://from-db.example' );

		$this->assertSame( 'https://from-env.example', Credentials::base_url() );
	}

	/**
	 * Tests that the base URL option is sanitized.
	 */
	public function test_base_url_falls_back_to_sanitized_option(): void {
		update_option( Credentials::BASE_URL_OPTION, 'https://from-db.example/' );

		$this->assertSame( 'https://from-db.example', Credentials::base_url() );
	}

	/**
	 * Tests that the URL is locked when supplied by the environment.
	 */
	public function test_base_url_is_locked_when_env_set(): void {
		$this->assertFalse( Credentials::base_url_is_locked() );

		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://lc.example' );
		$this->assertTrue( Credentials::base_url_is_locked() );
	}
}
