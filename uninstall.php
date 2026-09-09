<?php
/**
 * Uninstall handler.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ai_provider_for_librechat_base_url' );
