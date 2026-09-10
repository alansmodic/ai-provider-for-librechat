<?php
/**
 * Tests for the LibreChat provider factory.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Tests\Unit\Provider;

use AlanSmodic\AiProviderForLibreChat\Metadata\LibreChatModelMetadataDirectory;
use AlanSmodic\AiProviderForLibreChat\Models\LibreChatTextGenerationModel;
use AlanSmodic\AiProviderForLibreChat\Provider\LibreChatProvider;
use AlanSmodic\AiProviderForLibreChat\Provider\LibreChatProviderAvailability;
use AlanSmodic\AiProviderForLibreChat\Tests\TestCase;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\AbstractProvider;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;

/**
 * @covers \AlanSmodic\AiProviderForLibreChat\Provider\LibreChatProvider
 */
class LibreChatProviderTest extends TestCase {

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->clear_provider_caches();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function tearDown(): void {
		$this->clear_provider_caches();
		parent::tearDown();
	}

	/**
	 * Clears static caches on AbstractProvider.
	 */
	private function clear_provider_caches(): void {
		$reflection = new \ReflectionClass( AbstractProvider::class );
		foreach ( array( 'metadataCache', 'availabilityCache', 'modelMetadataDirectoryCache' ) as $prop_name ) {
			if ( ! $reflection->hasProperty( $prop_name ) ) {
				continue;
			}
			$prop = $reflection->getProperty( $prop_name );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );
		}
	}

	/**
	 * Invokes the protected createModel() factory.
	 *
	 * @param ModelMetadata $model_metadata Model metadata.
	 * @return ModelInterface
	 */
	private function invoke_create_model( ModelMetadata $model_metadata ): ModelInterface {
		$method = new \ReflectionMethod( LibreChatProvider::class, 'createModel' );
		$method->setAccessible( true );

		/** @var ModelInterface */
		return $method->invoke( null, $model_metadata, LibreChatProvider::metadata() );
	}

	/**
	 * Tests provider metadata identity and type.
	 */
	public function test_metadata_is_self_hosted_server_provider(): void {
		$metadata = LibreChatProvider::metadata();

		$this->assertSame( 'librechat', $metadata->getId() );
		$this->assertSame( 'LibreChat', $metadata->getName() );
		$this->assertTrue( $metadata->getType()->isServer() );

		$auth_method = $metadata->getAuthenticationMethod();
		$this->assertNotNull( $auth_method );
		$this->assertTrue( $auth_method->isApiKey() );
	}

	/**
	 * Tests that availability() returns the LibreChat availability class.
	 */
	public function test_availability_returns_librechat_availability(): void {
		$this->assertInstanceOf(
			LibreChatProviderAvailability::class,
			LibreChatProvider::availability()
		);
	}

	/**
	 * Tests that modelMetadataDirectory() returns the LibreChat directory class.
	 */
	public function test_model_metadata_directory_returns_correct_type(): void {
		$this->assertInstanceOf(
			LibreChatModelMetadataDirectory::class,
			LibreChatProvider::modelMetadataDirectory()
		);
	}

	/**
	 * Tests that createModel() returns a text generation model for agents.
	 */
	public function test_create_model_returns_text_generation_for_agents(): void {
		$meta = new ModelMetadata(
			'agent-newsroom',
			'Newsroom Style Editor',
			array( CapabilityEnum::textGeneration() ),
			array()
		);

		$model = $this->invoke_create_model( $meta );
		$this->assertInstanceOf( LibreChatTextGenerationModel::class, $model );
	}

	/**
	 * Tests that createModel() throws without text generation.
	 */
	public function test_create_model_throws_without_text_generation(): void {
		$meta = new ModelMetadata(
			'agent-x',
			'Agent X',
			array( CapabilityEnum::chatHistory() ),
			array()
		);

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Unsupported LibreChat agent capabilities for: agent-x' );
		$this->invoke_create_model( $meta );
	}
}
