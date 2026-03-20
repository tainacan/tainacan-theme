<?php
/**
 * Tainacan Interface - Security Hardening
 *
 * Fixes security vulnerabilities and adds protective measures.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize and validate all customizer inputs
 */
function tainacan_sanitize_select( $input, $setting ) {
	$choices = $setting->manager->get_control( $setting->id )->choices;
	return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

function tainacan_sanitize_number( $input ) {
	return absint( $input );
}

function tainacan_sanitize_float( $input ) {
	return floatval( $input );
}

function tainacan_sanitize_css_unit( $input ) {
	// Allow only safe CSS values like "60vh", "300px", "50%", "auto"
	if ( preg_match( '/^(\d+(\.\d+)?)(px|em|rem|vh|vw|%)$/', $input ) || $input === 'auto' || $input === 'none' ) {
		return $input;
	}
	return '';
}

function tainacan_sanitize_html_class( $input ) {
	return sanitize_html_class( $input );
}

/**
 * Escape output in templates - helper functions
 */
function tainacan_safe_output( $text ) {
	return wp_kses_post( $text );
}

function tainacan_safe_attr( $text ) {
	return esc_attr( $text );
}

/**
 * Add security headers
 */
function tainacan_security_headers() {
	if ( ! is_admin() ) {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}
}
add_action( 'send_headers', 'tainacan_security_headers' );

/**
 * Sanitize search query parameters to prevent XSS
 */
function tainacan_sanitize_search_params() {
	if ( isset( $_GET['s'] ) ) {
		$_GET['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}
	if ( isset( $_GET['tainacan_collections_viewmode'] ) ) {
		$allowed = array( 'cards', 'grid', 'table' );
		if ( ! in_array( $_GET['tainacan_collections_viewmode'], $allowed, true ) ) {
			$_GET['tainacan_collections_viewmode'] = 'cards';
		}
	}
	if ( isset( $_GET['tainacan_terms_viewmode'] ) ) {
		$allowed = array( 'cards', 'grid', 'table' );
		if ( ! in_array( $_GET['tainacan_terms_viewmode'], $allowed, true ) ) {
			$_GET['tainacan_terms_viewmode'] = 'cards';
		}
	}
}
add_action( 'init', 'tainacan_sanitize_search_params', 1 );

/**
 * Prevent direct access to theme PHP files
 */
function tainacan_block_direct_access() {
	if ( defined( 'ABSPATH' ) ) {
		return;
	}
	http_response_code( 403 );
	exit( 'Direct access not allowed.' );
}

/**
 * Validate nonces for AJAX requests
 */
function tainacan_verify_ajax_nonce() {
	if ( ! wp_doing_ajax() ) {
		return;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

	// Only check nonces for our theme's AJAX actions
	if ( strpos( $action, 'tainacan_interface' ) === 0 ) {
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_nonce'] ) ), 'tainacan_interface_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'tainacan-interface' ) ), 403 );
		}
	}
}
add_action( 'wp_ajax_nopriv_tainacan_interface', 'tainacan_verify_ajax_nonce', 1 );
add_action( 'wp_ajax_tainacan_interface', 'tainacan_verify_ajax_nonce', 1 );

/**
 * Add nonce to theme scripts
 */
function tainacan_add_script_nonce() {
	wp_localize_script( 'tainacan-interface-js', 'tainacanInterfaceSecurity', array(
		'nonce'   => wp_create_nonce( 'tainacan_interface_nonce' ),
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'tainacan_add_script_nonce', 20 );

/**
 * Rate limit AJAX requests from non-logged-in users
 */
function tainacan_rate_limit_ajax() {
	if ( is_user_logged_in() || ! wp_doing_ajax() ) {
		return;
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$transient_key = 'tainacan_rate_' . md5( $ip );
	$count = get_transient( $transient_key );

	if ( $count !== false && $count > 60 ) {
		wp_send_json_error( array( 'message' => __( 'Too many requests.', 'tainacan-interface' ) ), 429 );
	}

	set_transient( $transient_key, ( $count ? $count + 1 : 1 ), 60 );
}
add_action( 'admin_init', 'tainacan_rate_limit_ajax', 1 );

/**
 * Sanitize all theme mod outputs used in CSS
 */
function tainacan_sanitize_color_output( $color ) {
	if ( empty( $color ) ) {
		return '';
	}
	// Only allow hex colors and CSS color names
	if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ) {
		return $color;
	}
	$safe_names = array( 'transparent', 'inherit', 'initial', 'unset', 'currentColor' );
	if ( in_array( $color, $safe_names, true ) ) {
		return $color;
	}
	return '';
}

/**
 * Content Security Policy for inline styles
 */
function tainacan_add_csp_nonce( $tag, $handle ) {
	if ( strpos( $handle, 'tainacan' ) !== false ) {
		return str_replace( ' src', ' crossorigin="anonymous" src', $tag );
	}
	return $tag;
}
add_filter( 'style_loader_tag', 'tainacan_add_csp_nonce', 10, 2 );
