<?php
/**
 * Tainacan Interface - Dynamic Responsive CSS Generation
 *
 * Generates responsive CSS rules based on customizer settings,
 * supporting desktop, tablet, and mobile breakpoints.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output dynamic CSS for single item page
 */
function tainacan_output_dynamic_css() {
	$css = '';

	// Typography settings
	$meta_label_size = get_theme_mod( 'tainacan_typography_meta_label_size', '0.875rem' );
	$meta_label_weight = get_theme_mod( 'tainacan_typography_meta_label_weight', '600' );
	$meta_label_transform = get_theme_mod( 'tainacan_typography_meta_label_transform', 'uppercase' );
	$meta_value_size = get_theme_mod( 'tainacan_typography_meta_value_size', '1rem' );
	$meta_value_weight = get_theme_mod( 'tainacan_typography_meta_value_weight', '400' );
	$section_title_size = get_theme_mod( 'tainacan_typography_section_title_size', '1.25rem' );
	$section_title_weight = get_theme_mod( 'tainacan_typography_section_title_weight', '600' );
	$document_title_size = get_theme_mod( 'tainacan_typography_document_title_size', '1.125rem' );

	// Metadata columns
	$meta_cols_desktop = get_theme_mod( 'tainacan_single_item_metadata_columns_count_desktop', 3 );
	$meta_cols_tablet = get_theme_mod( 'tainacan_single_item_metadata_columns_count_tablet', 2 );
	$meta_cols_wide = get_theme_mod( 'tainacan_single_item_metadata_columns_count_wide', 3 );

	// Document settings
	$doc_max_height = get_theme_mod( 'tainacan_single_item_document_max_height', '60vh' );
	$attachment_thumb_size = get_theme_mod( 'tainacan_single_item_attachments_thumbnail_size', '136px' );

	// Gallery settings
	$gallery_sticky = get_theme_mod( 'tainacan_gallery_sticky', false );
	$gallery_width = get_theme_mod( 'tainacan_gallery_width', '50%' );

	// Base typography
	$css .= "/* Dynamic Typography */\n";
	$css .= ".tainacan-item-section--metadata .label, .tainacan-metadata-label {\n";
	$css .= "  font-size: " . tainacan_sanitize_css_unit( $meta_label_size ) . ";\n";
	$css .= "  font-weight: " . absint( $meta_label_weight ) . ";\n";
	$css .= "  text-transform: " . sanitize_text_field( $meta_label_transform ) . ";\n";
	$css .= "}\n";

	$css .= ".tainacan-item-section--metadata .value, .tainacan-metadata-value {\n";
	$css .= "  font-size: " . tainacan_sanitize_css_unit( $meta_value_size ) . ";\n";
	$css .= "  font-weight: " . absint( $meta_value_weight ) . ";\n";
	$css .= "}\n";

	$css .= ".tainacan-single-item-section-label, .tainacan-item-section-title {\n";
	$css .= "  font-size: " . tainacan_sanitize_css_unit( $section_title_size ) . ";\n";
	$css .= "  font-weight: " . absint( $section_title_weight ) . ";\n";
	$css .= "}\n";

	$css .= ".tainacan-item-section--document .section-label {\n";
	$css .= "  font-size: " . tainacan_sanitize_css_unit( $document_title_size ) . ";\n";
	$css .= "}\n";

	// Document dimensions
	$css .= ".tainacan-item-section--document .tainacan-document {\n";
	$css .= "  max-height: " . tainacan_sanitize_css_unit( $doc_max_height ) . ";\n";
	$css .= "}\n";

	$css .= ".tainacan-item-section--attachments .tainacan-media-component .swiper-slide img,\n";
	$css .= ".tainacan-item-section--attachments .attachment-without-document img {\n";
	$css .= "  width: " . tainacan_sanitize_css_unit( $attachment_thumb_size ) . ";\n";
	$css .= "  height: " . tainacan_sanitize_css_unit( $attachment_thumb_size ) . ";\n";
	$css .= "}\n";

	// Unified gallery sticky mode
	if ( $gallery_sticky ) {
		$css .= ".tainacan-item-gallery-unified {\n";
		$css .= "  position: sticky;\n";
		$css .= "  top: 100px;\n";
		$css .= "  align-self: flex-start;\n";
		$css .= "}\n";
	}

	// Gallery width
	$safe_width = tainacan_sanitize_css_unit( $gallery_width );
	if ( $safe_width ) {
		$css .= ".tainacan-item-layout--gallery .tainacan-item-gallery-unified {\n";
		$css .= "  flex: 0 0 " . $safe_width . ";\n";
		$css .= "  max-width: " . $safe_width . ";\n";
		$css .= "}\n";
	}

	// Desktop rules
	$css .= "\n/* Desktop */\n";
	$css .= "@media (min-width: 992px) {\n";
	$css .= "  .tainacan-item-section--metadata .metadata-list {\n";
	$css .= "    column-count: " . absint( $meta_cols_desktop ) . ";\n";
	$css .= "  }\n";
	$css .= "}\n";

	// Wide screen rules
	$css .= "\n/* Wide Screen */\n";
	$css .= "@media (min-width: 1400px) {\n";
	$css .= "  .tainacan-item-section--metadata .metadata-list {\n";
	$css .= "    column-count: " . absint( $meta_cols_wide ) . ";\n";
	$css .= "  }\n";
	$css .= "}\n";

	// Tablet rules
	$css .= "\n/* Tablet */\n";
	$css .= "@media (min-width: 768px) and (max-width: 991.98px) {\n";
	$css .= "  .tainacan-item-section--metadata .metadata-list {\n";
	$css .= "    column-count: " . absint( $meta_cols_tablet ) . ";\n";
	$css .= "  }\n";
	$css .= "  .tainacan-item-layout--gallery .tainacan-item-gallery-unified {\n";
	$css .= "    flex: 0 0 100%;\n";
	$css .= "    max-width: 100%;\n";
	$css .= "    position: relative;\n";
	$css .= "    top: auto;\n";
	$css .= "  }\n";
	$css .= "}\n";

	// Mobile rules
	$css .= "\n/* Mobile */\n";
	$css .= "@media (max-width: 767.98px) {\n";
	$css .= "  .tainacan-item-section--metadata .metadata-list {\n";
	$css .= "    column-count: 1;\n";
	$css .= "  }\n";
	$css .= "  .tainacan-item-layout--gallery {\n";
	$css .= "    flex-direction: column;\n";
	$css .= "  }\n";
	$css .= "  .tainacan-item-layout--gallery .tainacan-item-gallery-unified {\n";
	$css .= "    flex: 0 0 100%;\n";
	$css .= "    max-width: 100%;\n";
	$css .= "  }\n";
	$css .= "  .tainacan-item-gallery-unified {\n";
	$css .= "    position: relative !important;\n";
	$css .= "    top: auto !important;\n";
	$css .= "  }\n";
	$css .= "}\n";

	if ( ! empty( $css ) ) {
		echo '<style id="tainacan-dynamic-css">' . "\n" . $css . '</style>' . "\n";
	}
}
add_action( 'wp_head', 'tainacan_output_dynamic_css', 15 );

/**
 * Output archive page dynamic CSS
 */
function tainacan_output_archive_dynamic_css() {
	if ( ! is_post_type_archive() && ! is_tax() ) {
		return;
	}

	$css = '';

	// Archive color palette
	$archive_bg = get_theme_mod( 'tainacan_archive_background_color', '' );
	$archive_text = get_theme_mod( 'tainacan_archive_text_color', '' );
	$archive_heading = get_theme_mod( 'tainacan_archive_heading_color', '' );
	$archive_label = get_theme_mod( 'tainacan_archive_label_color', '' );

	if ( $archive_bg ) {
		$css .= ".tainacan-items-list-container { background-color: " . tainacan_sanitize_color_output( $archive_bg ) . "; }\n";
	}
	if ( $archive_text ) {
		$css .= ".tainacan-items-list-container { color: " . tainacan_sanitize_color_output( $archive_text ) . "; }\n";
	}
	if ( $archive_heading ) {
		$css .= ".tainacan-items-list-container h1, .tainacan-items-list-container h2, .tainacan-items-list-container h3 { color: " . tainacan_sanitize_color_output( $archive_heading ) . "; }\n";
	}
	if ( $archive_label ) {
		$css .= ".tainacan-items-list-container .label, .tainacan-items-list-container .filter-label { color: " . tainacan_sanitize_color_output( $archive_label ) . "; }\n";
	}

	if ( ! empty( $css ) ) {
		echo '<style id="tainacan-archive-dynamic-css">' . "\n" . $css . '</style>' . "\n";
	}
}
add_action( 'wp_head', 'tainacan_output_archive_dynamic_css', 16 );
