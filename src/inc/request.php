<?php
/**
 * Request input, in one place.
 *
 * Templates never read a superglobal: they ask here. Every value that comes
 * from the visitor's request is unslashed and sanitized before it leaves this
 * file, and a URL that came from the URL is accepted only when it points to
 * this site.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A query-string value from the current request, as plain text.
 *
 * @param string $key     Query argument name.
 * @param string $fallback Value when the argument is missing.
 * @return string
 */
function tainacan_interface_query_arg( $key, $fallback = '' ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation state (search, list position), nothing is changed.
	if ( ! isset( $_GET[ $key ] ) || ! is_scalar( $_GET[ $key ] ) ) { // tnc-check-ignore TNC-S02: presence and type check before the sanitized read below.
		return $fallback;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation state, as above.
	return sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
}

/**
 * Whether a query-string flag is present and truthy (?onlyposts=1).
 *
 * @param string $key Query argument name.
 * @return bool
 */
function tainacan_interface_query_flag( $key ) {
	$value = tainacan_interface_query_arg( $key );
	return '' !== $value && '0' !== $value && 'false' !== $value;
}

/**
 * The URL, if it is an http(s) address on this site; empty string otherwise.
 *
 * Used for addresses that arrive in the request (the ?ref= of the item page,
 * the list address the drawer receives) and go back out in an href or to
 * url_to_postid(). Only control characters are stripped: a Tainacan list
 * address is percent-encoded and carries JSON, and both sanitize_text_field()
 * (drops %XX) and esc_url_raw() (drops quotes) would destroy it. The caller
 * still escapes with esc_url() at the point of output.
 *
 * @param string $url Candidate URL.
 * @return string
 */
function tainacan_interface_internal_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	$url = preg_replace( '/[\x00-\x1F\x7F]/', '', $url );
	if ( null === $url || '' === $url ) {
		return '';
	}

	if ( '/' === $url[0] && ( ! isset( $url[1] ) || '/' !== $url[1] ) ) {
		$url = home_url( $url );
	}

	if ( ! wp_http_validate_url( $url ) ) {
		return '';
	}

	$host = wp_parse_url( $url, PHP_URL_HOST );
	$home = wp_parse_url( home_url(), PHP_URL_HOST );

	if ( ! is_string( $host ) || ! is_string( $home ) || strtolower( $host ) !== strtolower( $home ) ) {
		return '';
	}

	return $url;
}

/**
 * A query-string value that carries an address, if it points to this site.
 *
 * Read without sanitize_text_field(), which would drop the percent-encoded
 * octets of an accented slug; tainacan_interface_internal_url() does the validation.
 *
 * @param string $key Query argument name.
 * @return string Unescaped URL or ''.
 */
function tainacan_interface_query_url( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: an address to link back to.
	if ( ! isset( $_GET[ $key ] ) || ! is_string( $_GET[ $key ] ) ) { // tnc-check-ignore TNC-S02: presence and type check before the validated read below.
		return '';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated by tainacan_interface_internal_url(): http(s), this host, no control characters.
	return tainacan_interface_internal_url( wp_unslash( $_GET[ $key ] ) ); // tnc-check-ignore TNC-S02: tainacan_interface_internal_url() is the sanitizer for addresses (see its docblock).
}

/**
 * Query arguments of the current request that describe a Tainacan list
 * position (filters, search, order), without the item-navigation keys.
 *
 * Values are kept as the visitor sent them, with control characters removed,
 * because the receiving list parses its own JSON parameters; they are only
 * ever used to rebuild a URL that is escaped on output.
 *
 * @return array<string, mixed>
 */
function tainacan_interface_list_query_args() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: rebuilds the "back to the list" link.
	$args = wp_unslash( $_GET ); // tnc-check-ignore TNC-S02: every value is stripped of control characters below and only rebuilds a URL escaped on output.
	if ( ! is_array( $args ) ) {
		return array();
	}

	unset( $args['pos'], $args['ref'], $args['source_list'] );

	array_walk_recursive(
		$args,
		static function ( &$value ) {
			$value = is_scalar( $value ) ? preg_replace( '/[\x00-\x1F\x7F]/', '', (string) $value ) : '';
		}
	);

	return $args;
}
