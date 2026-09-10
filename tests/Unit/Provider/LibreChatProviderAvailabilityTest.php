<?php
/**
 * Tests for provider availability.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Tests\Unit\Provider;

use AlanSmodic\AiProviderForLibreChat\Provider\LibreChatProviderAvailability;
use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use AlanSmodic\AiProviderForLibreChat\Tests\TestCase;

/**
 * @covers \AlanSmodic\AiProviderForLibreChat\Provider\LibreChatProviderAvailability
 */
class LibreChatProviderAvailabilityTest extends TestCase {

	/**
	 * Availability under test.
	 *
	 * @var LibreChatProviderAvailability
	 */
	private $availability;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->availability = new LibreChatProviderAvailability();
	}

	/**
	 * Tests that the provider is unconfigured with neither key nor URL.
	 */
	public function test_is_not_configured_without_credentials(): void {
		$this->assertFalse( $this->availability->isConfigured() );
	}

	/**
	 * Tests that a key alone is not enough.
	 */
	public function test_is_not_configured_with_only_api_key(): void {
		$this->set_env( 'LIBRECHAT_API_KEY', 'secret' );
		$this->assertFalse( $this->availability->isConfigured() );
	}

	/**
	 * Tests that a URL alone is not enough.
	 */
	public function test_is_not_configured_with_only_base_url(): void {
		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://lc.example' );
		$this->assertFalse( $this->availability->isConfigured() );
	}

	/**
	 * Tests that both a key and URL are required.
	 */
	public function test_is_configured_with_key_and_url(): void {
		$this->set_env( 'LIBRECHAT_API_KEY', 'secret' );
		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://lc.example' );

		$this->assertTrue( $this->availability->isConfigured() );
		$this->assertSame( 'secret', Credentials::api_key() );
	}
}
