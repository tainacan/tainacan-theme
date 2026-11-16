<?php
/**
 * Functions of Tainacan Interface that this theme renamed or dropped, kept
 * for child themes and snippets that still call them. A call to a missing
 * function is a fatal error; these keep the old behaviour.
 *
 * The function get_item_link_for_navigation() is not here: it was declared inside
 * tainacan/view-mode-grid.php and rebuilt the item link from the raw query
 * string, which is where the ref redirect came from (see AGENTS.md).
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'custom_class' ) ) {
	/**
	 * Body classes of Tainacan Interface; tainacan_interface_body_class() adds them now.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	function custom_class( $classes ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- name kept from Tainacan Interface for child themes that call it.
		return tainacan_interface_body_class( $classes );
	}
}

if ( ! function_exists( 'tainacan_customizer_gutenberg_colors' ) ) {
	/**
	 * Accent color CSS for the block editor, as in Tainacan Interface.
	 *
	 * @return string
	 */
	function tainacan_customizer_gutenberg_colors() {
		$color = (string) tainacan_interface_opt( 'accent_color' );
		if ( ! $color ) {
			return '';
		}
		return '.has-accent-color { color: ' . $color . ' !important; }.has-accent-background-color { background-color: ' . $color . '; }';
	}
}

if ( ! function_exists( 'tainacan_interface_is_modern' ) ) {
	/**
	 * Up to 4.2 the theme had two designs. There is one now; child themes written
	 * for 4.0 to 4.2 may still ask.
	 *
	 * @deprecated 4.3.0
	 * @return bool Always true.
	 */
	function tainacan_interface_is_modern() {
		_deprecated_function( __FUNCTION__, '4.3.0' );
		return true;
	}
}

if ( ! function_exists( 'tainacan_get_color_scheme' ) ) {
	/**
	 * The color scheme of 2.x, now drawn from the colors of the design: background,
	 * page background, link and tooltip. Child themes of 2.x read it.
	 *
	 * @deprecated 4.3.0
	 * @return string[]
	 */
	function tainacan_get_color_scheme() {
		$accent = (string) tainacan_interface_opt( 'accent_color' );
		return array( (string) tainacan_interface_opt( 'page_background' ), (string) tainacan_interface_opt( 'surface_color' ), $accent, $accent );
	}
}
