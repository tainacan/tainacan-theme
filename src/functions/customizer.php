<?php
/**
 * Customizer functionality
 *
 * @package tainacan-interface
 */

/**
 * Callback to Sanitize Checkboxes.
 *
 * @param mixed $checked Value sent by the control.
 * @return bool
 */
function tainacan_callback_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && (bool) $checked ) ? true : false );
}


/**
 * Number sanitization callback for the Customizer: a non-negative integer,
 * or empty to fall back to the default. The original saved these with
 * sanitize_text_field() and printed some of them inside style attributes.
 *
 * @param mixed $value Value sent by the Customizer.
 * @return int|string
 */
function tainacan_interface_sanitize_number( $value ) {
	return ( '' === $value || null === $value ) ? '' : absint( $value );
}

/**
 * Prints a style tag with CSS built by the theme from Customizer values.
 *
 * The values are validated when saved (absint, sanitize_hex_color, closed
 * lists) and checked again where the CSS is built; wp_strip_all_tags() removes
 * any tag, so no value can close the style element.
 *
 * @param string $id  Element id.
 * @param string $css CSS.
 */
function tainacan_interface_print_style( $id, $css ) {
	$css = trim( wp_strip_all_tags( (string) $css ) );
	if ( '' === $css ) {
		return;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS inside a style element: esc_html() would turn the > of child selectors and the quotes of content:"" into entities, which CSS does not decode; tags are stripped above.
	echo '<style id="' . esc_attr( $id ) . '">' . $css . '</style>'; // tnc-check-ignore TNC-S05: CSS in a style element, tags stripped, values validated on save.
}



/* Requires remaining customizer options */
require get_template_directory() . '/functions/customizer/header-image.php';
require get_template_directory() . '/functions/customizer/header-general.php';
require get_template_directory() . '/functions/customizer/header-search.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-general.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-metadata.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-document.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-related-items.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-search-area.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-filters-panel.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-pagination.php';
require get_template_directory() . '/functions/customizer/footer-info.php';
require get_template_directory() . '/functions/customizer/social-share.php';
require get_template_directory() . '/functions/customizer/posts-list.php';
require get_template_directory() . '/functions/customizer/tainacan-interface-features.php';
