<?php
/**
 * PHPUnit bootstrap.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

define( 'ABSPATH', '/tmp/wordpress/' );
define( 'AI_PROVIDER_FOR_LIBRECHAT_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'AI_PROVIDER_FOR_LIBRECHAT_PLUGIN_FILE', dirname( __DIR__ ) . '/plugin.php' );

require_once __DIR__ . '/stubs/wordpress-functions.php';

$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( ! is_readable( $autoload ) ) {
	fwrite( STDERR, "Composer autoloader not found. Run composer install.\n" );
	exit( 1 );
}

require_once $autoload;
