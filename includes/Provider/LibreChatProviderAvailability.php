<?php
/**
 * LibreChat provider availability.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace AlanSmodic\AiProviderForLibreChat\Provider;

use AlanSmodic\AiProviderForLibreChat\Support\Credentials;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports whether the LibreChat provider is configured.
 *
 * Both an instance URL and an API key are required, since LibreChat is self-hosted and has no
 * shared endpoint. A network probe is avoided so admin screens stay fast and instances on private
 * networks do not stall page loads.
 *
 * @since 1.0.0
 */
class LibreChatProviderAvailability implements ProviderAvailabilityInterface {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	public function isConfigured(): bool {
		return '' !== Credentials::api_key() && '' !== Credentials::base_url();
	}
}
