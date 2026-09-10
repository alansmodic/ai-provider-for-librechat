<?php
/**
 * Mock HTTP transporter for tests.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Tests\Mocks;

use WordPress\AiClient\Providers\Http\Contracts\HttpTransporterInterface;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\RequestOptions;
use WordPress\AiClient\Providers\Http\DTO\Response;

/**
 * Queues canned responses for SDK HTTP calls.
 *
 * @since 1.0.1
 */
class MockHttpTransporter implements HttpTransporterInterface {

	/**
	 * The last request that was sent.
	 *
	 * @var Request|null
	 */
	private $last_request = null;

	/**
	 * Fallback response when the queue is empty.
	 *
	 * @var Response|null
	 */
	private $response_to_return = null;

	/**
	 * FIFO queue of responses.
	 *
	 * @var list<Response>
	 */
	private $responses_queue = array();

	/**
	 * {@inheritDoc}
	 */
	public function send( Request $request, ?RequestOptions $options = null ): Response {
		unset( $options );
		$this->last_request = $request;

		if ( array() !== $this->responses_queue ) {
			return array_shift( $this->responses_queue );
		}

		return null !== $this->response_to_return
			? $this->response_to_return
			: new Response( 200, array(), '{"object":"list","data":[]}' );
	}

	/**
	 * Returns the last request that was sent.
	 *
	 * @return Request|null
	 */
	public function get_last_request() {
		return $this->last_request;
	}

	/**
	 * Sets the fallback response.
	 *
	 * @param Response $response Response.
	 */
	public function set_response_to_return( Response $response ): void {
		$this->response_to_return = $response;
	}

	/**
	 * Adds a response to the FIFO queue.
	 *
	 * @param Response $response Response.
	 */
	public function queue_response( Response $response ): void {
		$this->responses_queue[] = $response;
	}
}
