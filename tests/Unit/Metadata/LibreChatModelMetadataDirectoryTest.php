<?php
/**
 * Tests for LibreChat agent discovery.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Tests\Unit\Metadata;

use AlanSmodic\AiProviderForLibreChat\Metadata\LibreChatModelMetadataDirectory;
use AlanSmodic\AiProviderForLibreChat\Tests\Mocks\MockHttpTransporter;
use AlanSmodic\AiProviderForLibreChat\Tests\TestCase;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

/**
 * @covers \AlanSmodic\AiProviderForLibreChat\Metadata\LibreChatModelMetadataDirectory
 */
class LibreChatModelMetadataDirectoryTest extends TestCase {

	/**
	 * Directory under test.
	 *
	 * @var LibreChatModelMetadataDirectory
	 */
	private $directory;

	/**
	 * Mock transporter.
	 *
	 * @var MockHttpTransporter
	 */
	private $transporter;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->set_env( 'LIBRECHAT_BASE_URL', 'https://lc.example' );
		$this->transporter = new MockHttpTransporter();
		$this->directory   = new LibreChatModelMetadataDirectory();
		$this->directory->setHttpTransporter( $this->transporter );
		$this->directory->setRequestAuthentication( new ApiKeyRequestAuthentication( 'test-key' ) );
		$this->directory->invalidateCaches();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function tearDown(): void {
		$this->directory->invalidateCaches();
		parent::tearDown();
	}

	/**
	 * Builds a 200 /models payload.
	 *
	 * @param mixed $data Value of the `data` key, or null to omit it.
	 * @return Response
	 */
	private function make_models_response( $data ): Response {
		$payload = null === $data
			? array( 'object' => 'list' )
			: array(
				'object' => 'list',
				'data'   => $data,
			);

		return new Response( 200, array(), (string) wp_json_encode( $payload ) );
	}

	/**
	 * Invokes the protected parser.
	 *
	 * @param Response $response Response.
	 * @return list<\WordPress\AiClient\Providers\Models\DTO\ModelMetadata>
	 */
	private function parse( Response $response ): array {
		$method = new \ReflectionMethod( LibreChatModelMetadataDirectory::class, 'parseResponseToModelMetadataList' );
		$method->setAccessible( true );

		/** @var list<\WordPress\AiClient\Providers\Models\DTO\ModelMetadata> */
		return $method->invoke( $this->directory, $response );
	}

	/**
	 * Tests that agents are parsed as text-generation models.
	 */
	public function test_parses_agents_as_text_models(): void {
		$models = $this->parse(
			$this->make_models_response(
				array(
					array(
						'id'   => 'agent-newsroom',
						'name' => 'Newsroom Style Editor',
					),
					'bare-id-only',
					array( 'id' => '' ),
				)
			)
		);

		$this->assertCount( 2, $models );
		$this->assertSame( 'agent-newsroom', $models[0]->getId() );
		$this->assertSame( 'Newsroom Style Editor', $models[0]->getName() );
		$this->assertSame( 'bare-id-only', $models[1]->getId() );
		$this->assertSame( 'bare-id-only', $models[1]->getName() );

		$has_text = false;
		foreach ( $models[0]->getSupportedCapabilities() as $capability ) {
			if ( $capability->isTextGeneration() ) {
				$has_text = true;
				break;
			}
		}
		$this->assertTrue( $has_text );
	}

	/**
	 * Tests that display_name is used when name is absent.
	 */
	public function test_uses_display_name_when_name_missing(): void {
		$models = $this->parse(
			$this->make_models_response(
				array(
					array(
						'id'           => 'agent-1',
						'display_name' => 'Friendly Agent',
					),
				)
			)
		);

		$this->assertSame( 'Friendly Agent', $models[0]->getName() );
	}

	/**
	 * Tests that a missing `data` key throws.
	 */
	public function test_missing_data_key_throws(): void {
		$this->expectException( ResponseException::class );
		$this->parse( $this->make_models_response( null ) );
	}

	/**
	 * Tests that junk filter values are dropped.
	 */
	public function test_supported_options_filter_drops_junk(): void {
		add_filter(
			'ai_provider_for_librechat_supported_options',
			static function ( $options ) {
				$options[] = 'not-an-option';
				$options[] = new SupportedOption( OptionEnum::temperature() );
				return $options;
			},
			10,
			2
		);

		$models = $this->parse(
			$this->make_models_response(
				array(
					array( 'id' => 'agent-1' ),
				)
			)
		);

		foreach ( $models[0]->getSupportedOptions() as $option ) {
			$this->assertInstanceOf( SupportedOption::class, $option );
		}

		$has_temperature = false;
		foreach ( $models[0]->getSupportedOptions() as $option ) {
			if ( $option->getName()->isTemperature() ) {
				$has_temperature = true;
				break;
			}
		}
		$this->assertTrue( $has_temperature );
	}

	/**
	 * Tests that listModelMetadata() hits the Agents API /models path.
	 */
	public function test_list_models_request_hits_agents_api(): void {
		$this->transporter->queue_response(
			$this->make_models_response(
				array(
					array(
						'id'   => 'agent-1',
						'name' => 'Agent One',
					),
				)
			)
		);

		$models = $this->directory->listModelMetadata();

		$this->assertCount( 1, $models );
		$this->assertSame( 'agent-1', $models[0]->getId() );

		$request = $this->transporter->get_last_request();
		$this->assertNotNull( $request );
		$this->assertTrue( $request->getMethod()->isGet() );
		$this->assertSame( 'https://lc.example/api/agents/v1/models', $request->getUri() );
	}

	/**
	 * Tests that a failed /models request surfaces an exception.
	 */
	public function test_failed_models_request_throws(): void {
		$this->transporter->set_response_to_return(
			new Response( 500, array(), '{"error":"Internal Server Error"}' )
		);

		$this->expectException( \Throwable::class );
		$this->directory->listModelMetadata();
	}
}
