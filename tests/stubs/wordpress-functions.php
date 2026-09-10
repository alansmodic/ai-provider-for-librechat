<?php
/**
 * Minimal WordPress function stubs for unit tests.
 *
 * @package AlanSmodic\AiProviderForLibreChat
 */

declare( strict_types=1 );

$GLOBALS['wp_options'] = array();
$GLOBALS['wp_filters'] = array();

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Retrieves an option value.
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	function get_option( $option, $default = false ) {
		return array_key_exists( $option, $GLOBALS['wp_options'] ) ? $GLOBALS['wp_options'][ $option ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Updates an option value.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return bool
	 */
	function update_option( $option, $value ): bool {
		$GLOBALS['wp_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Adds a filter callback.
	 *
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Callback.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted argument count.
	 * @return bool
	 */
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ): bool {
		$GLOBALS['wp_filters'][ $hook ][ (int) $priority ][] = array(
			'function'      => $callback,
			'accepted_args' => $accepted_args,
		);
		ksort( $GLOBALS['wp_filters'][ $hook ] );
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Applies attached filters.
	 *
	 * @param string $hook  Hook name.
	 * @param mixed  $value Value to filter.
	 * @param mixed  ...$args Additional arguments.
	 * @return mixed
	 */
	function apply_filters( $hook, $value, ...$args ) {
		if ( empty( $GLOBALS['wp_filters'][ $hook ] ) ) {
			return $value;
		}

		foreach ( $GLOBALS['wp_filters'][ $hook ] as $callbacks ) {
			foreach ( $callbacks as $cb ) {
				$params = array_merge( array( $value ), $args );
				$params = array_slice( $params, 0, (int) $cb['accepted_args'] );
				$value  = call_user_func_array( $cb['function'], $params );
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * Sanitizes a URL for use in a redirect or HTTP request.
	 *
	 * @param string            $url       URL.
	 * @param array<int,string> $protocols Allowed protocols.
	 * @return string
	 */
	function esc_url_raw( $url, $protocols = null ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}

		$protocols = is_array( $protocols ) ? $protocols : array( 'http', 'https' );
		$parts     = parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		if ( ! in_array( strtolower( (string) $parts['scheme'] ), $protocols, true ) ) {
			return '';
		}

		return $url;
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * Parses a URL.
	 *
	 * @param string $url       URL.
	 * @param int    $component Component.
	 * @return mixed
	 */
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	/**
	 * Removes trailing slashes.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	function untrailingslashit( $value ) {
		return rtrim( (string) $value, '/\\' );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Sanitizes a string from user input or from the database.
	 *
	 * @param string $str String.
	 * @return string
	 */
	function sanitize_text_field( $str ) {
		$str = wp_strip_all_tags( (string) $str );
		$str = preg_replace( '/[\r\n\t ]+/', ' ', $str );
		return trim( (string) $str );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	/**
	 * Strips all HTML tags.
	 *
	 * @param string $str String.
	 * @return string
	 */
	function wp_strip_all_tags( $str ) {
		return trim( strip_tags( (string) $str ) );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Retrieves a translated string.
	 *
	 * @param string $text   Text.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Escapes HTML.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Encodes data as JSON.
	 *
	 * @param mixed $data Data.
	 * @return string|false
	 */
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}
