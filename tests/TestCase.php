<?php
/**
 * Shared PHPUnit test case.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Resets env vars, options, and filters between tests.
 *
 * @since 1.0.1
 */
abstract class TestCase extends PHPUnitTestCase {

	/**
	 * Original LIBRECHAT_API_KEY env value.
	 *
	 * @var string|false
	 */
	private $original_api_key;

	/**
	 * Original LIBRECHAT_BASE_URL env value.
	 *
	 * @var string|false
	 */
	private $original_base_url;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->original_api_key  = getenv( 'LIBRECHAT_API_KEY' );
		$this->original_base_url = getenv( 'LIBRECHAT_BASE_URL' );

		$GLOBALS['wp_options'] = array();
		$GLOBALS['wp_filters'] = array();

		$this->clear_env( 'LIBRECHAT_API_KEY' );
		$this->clear_env( 'LIBRECHAT_BASE_URL' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function tearDown(): void {
		$this->restore_env( 'LIBRECHAT_API_KEY', $this->original_api_key );
		$this->restore_env( 'LIBRECHAT_BASE_URL', $this->original_base_url );

		$GLOBALS['wp_options'] = array();
		$GLOBALS['wp_filters'] = array();

		parent::tearDown();
	}

	/**
	 * Unsets an environment variable.
	 *
	 * @param string $name Variable name.
	 */
	protected function clear_env( string $name ): void {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- Test isolation.
		putenv( $name );
	}

	/**
	 * Sets an environment variable.
	 *
	 * @param string $name  Variable name.
	 * @param string $value Value.
	 */
	protected function set_env( string $name, string $value ): void {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- Test isolation.
		putenv( $name . '=' . $value );
	}

	/**
	 * Restores an environment variable to its pre-test value.
	 *
	 * @param string       $name  Variable name.
	 * @param string|false $value Original value, or false when unset.
	 */
	protected function restore_env( string $name, $value ): void {
		if ( false === $value ) {
			$this->clear_env( $name );
			return;
		}

		$this->set_env( $name, $value );
	}
}
