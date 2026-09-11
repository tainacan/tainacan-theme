<?php
/**
 * Item single gallery helpers (theme mods, gallery args, section attrs, media actions).
 */

if ( ! function_exists( 'tainacan_interface_has_media_item_actions' ) ) {
	/**
	 * Whether the current Tainacan ships the media item actions wrapper.
	 *
	 * @return bool
	 */
	function tainacan_interface_has_media_item_actions() {
		return function_exists( 'tainacan_get_the_media_item_expand_control' );
	}
}

if ( ! function_exists( 'tainacan_interface_get_item_gallery_settings' ) ) {
	/**
	 * Theme mods used by the item document / attachments gallery templates.
	 *
	 * @return array
	 */
	function tainacan_interface_get_item_gallery_settings() {
		return array(
			'is_gallery_mode'                => (bool) get_theme_mod( 'tainacan_single_item_gallery_mode', false ),
			'hide_file_name_main'            => (bool) get_theme_mod( 'tainacan_single_item_hide_files_name_main', true ),
			'hide_file_caption_main'         => (bool) get_theme_mod( 'tainacan_single_item_hide_files_caption_main', true ),
			'hide_file_description_main'     => (bool) get_theme_mod( 'tainacan_single_item_hide_files_description_main', true ),
			'hide_file_name'                 => (bool) get_theme_mod( 'tainacan_single_item_hide_files_name', false ),
			'hide_download_button'           => (bool) get_theme_mod( 'tainacan_single_item_hide_download_document', false ),
			'disable_gallery_lightbox'       => (bool) get_theme_mod( 'tainacan_single_item_disable_gallery_lightbox', false ),
			'hide_file_name_lightbox'        => (bool) get_theme_mod( 'tainacan_single_item_hide_files_name_lightbox', false ),
			'hide_file_caption_lightbox'     => (bool) get_theme_mod( 'tainacan_single_item_hide_files_caption_lightbox', false ),
			'hide_file_description_lightbox' => (bool) get_theme_mod( 'tainacan_single_item_hide_files_description_lightbox', false ),
			'has_light_dark_color_scheme'    => get_theme_mod( 'tainacan_single_item_gallery_color_scheme', 'dark' ) === 'light',
			'metadata_alignment'             => get_theme_mod( 'tainacan_single_item_gallery_metadata_alignment', 'center' ),
			'appearance'                     => get_theme_mod( 'tainacan_single_item_gallery_media_actions_appearance', 'icon' ),
			'behavior'                       => get_theme_mod( 'tainacan_single_item_gallery_media_actions_behavior', 'hover' ),
			'download_alignment'             => get_theme_mod( 'tainacan_single_item_gallery_download_alignment', 'center' ),
			'expand_alignment'               => get_theme_mod( 'tainacan_single_item_gallery_expand_alignment', 'center' ),
			'hide_expand'                    => (bool) get_theme_mod( 'tainacan_single_item_hide_expand_button', false ),
		);
	}
}

if ( ! function_exists( 'tainacan_interface_get_item_gallery_args' ) ) {
	/**
	 * Args for tainacan_the_item_gallery() from theme mods.
	 *
	 * @param string     $context  'document' or 'attachments'.
	 * @param array|null $settings Optional settings bag.
	 * @param int        $post_id  Item post ID.
	 * @return array
	 */
	function tainacan_interface_get_item_gallery_args( $context, $settings = null, $post_id = 0 ) {
		if ( $settings === null ) {
			$settings = tainacan_interface_get_item_gallery_settings();
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$shared = array(
			'hideFileNameMain'              => $settings['hide_file_name_main'],
			'hideFileCaptionMain'           => $settings['hide_file_caption_main'],
			'hideFileDescriptionMain'       => $settings['hide_file_description_main'],
			'hideFileNameThumbnails'        => $settings['hide_file_name'],
			'hideFileCaptionThumbnails'     => true,
			'hideFileDescriptionThumbnails' => true,
			'showDownloadButtonMain'        => ! $settings['hide_download_button'],
			'showArrowsAsSVG'               => false,
			'hideFileNameLightbox'          => $settings['hide_file_name_lightbox'],
			'hideFileCaptionLightbox'       => $settings['hide_file_caption_lightbox'],
			'hideFileDescriptionLightbox'   => $settings['hide_file_description_lightbox'],
			'lightboxHasLightBackground'    => $settings['has_light_dark_color_scheme'],
		);

		if ( $context === 'document' ) {
			return array_merge(
				$shared,
				array(
					'blockId'             => 'tainacan-item-document_id-' . $post_id,
					'layoutElements'      => array( 'main' => true, 'thumbnails' => false ),
					'mediaSources'        => array( 'document' => true, 'attachments' => false, 'metadata' => false ),
					'openLightboxOnClick' => ! $settings['disable_gallery_lightbox'],
				)
			);
		}

		return array_merge(
			$shared,
			array(
				'blockId'             => 'tainacan-item-attachments_id-' . $post_id,
				'layoutElements'      => array( 'main' => $settings['is_gallery_mode'], 'thumbnails' => true ),
				'mediaSources'        => array( 'document' => $settings['is_gallery_mode'], 'attachments' => true, 'metadata' => false ),
				'openLightboxOnClick' => $settings['is_gallery_mode'] ? ! $settings['disable_gallery_lightbox'] : true,
			)
		);
	}
}

if ( ! function_exists( 'tainacan_interface_get_item_gallery_data_attributes' ) ) {
	/**
	 * HTML attribute string for the gallery section (data-gallery-*).
	 *
	 * @param array|null $settings Optional settings from tainacan_interface_get_item_gallery_settings().
	 * @return string Space-prefixed attribute string, or empty when unsupported.
	 */
	function tainacan_interface_get_item_gallery_data_attributes( $settings = null ) {
		if ( ! tainacan_interface_has_media_item_actions() ) {
			return '';
		}

		if ( $settings === null ) {
			$settings = tainacan_interface_get_item_gallery_settings();
		}

		$allowed_align = array( 'left', 'center', 'right' );
		$allowed_appearance = array( 'icon', 'button', 'link' );
		$allowed_behavior = array( 'hover', 'always' );

		$metadata_alignment = in_array( $settings['metadata_alignment'], $allowed_align, true ) ? $settings['metadata_alignment'] : 'center';
		$appearance = in_array( $settings['appearance'], $allowed_appearance, true ) ? $settings['appearance'] : 'icon';
		$behavior = in_array( $settings['behavior'], $allowed_behavior, true ) ? $settings['behavior'] : 'hover';
		$download_alignment = in_array( $settings['download_alignment'], $allowed_align, true ) ? $settings['download_alignment'] : 'center';
		$expand_alignment = in_array( $settings['expand_alignment'], $allowed_align, true ) ? $settings['expand_alignment'] : 'center';

		return sprintf(
			' data-gallery-metadata-align="%1$s" data-gallery-actions-appearance="%2$s" data-gallery-actions-behavior="%3$s" data-gallery-download-align="%4$s" data-gallery-expand-align="%5$s"',
			esc_attr( $metadata_alignment ),
			esc_attr( $appearance ),
			esc_attr( $behavior ),
			esc_attr( $download_alignment ),
			esc_attr( $expand_alignment )
		);
	}
}

if ( ! function_exists( 'tainacan_interface_add_media_action_button_classes' ) ) {
	/**
	 * Add theme button classes to an action control wrapper and <a>.
	 *
	 * Uses WP_HTML_Tag_Processor so existing class attributes from Tainacan are merged.
	 *
	 * @param string $html Action control HTML.
	 * @return string
	 */
	function tainacan_interface_add_media_action_button_classes( $html ) {
		if ( $html === '' || strpos( $html, '<a' ) === false ) {
			return $html;
		}

		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $html;
		}

		$processor = new WP_HTML_Tag_Processor( $html );

		if ( $processor->next_tag( 'span' ) ) {
			$processor->add_class( 'wp-block-button' );
		}

		if ( ! $processor->next_tag( 'a' ) ) {
			return $processor->get_updated_html();
		}

		$processor->add_class( 'wp-block-button__link' );
		$processor->add_class( 'wp-element-button' );
		$processor->add_class( 'btn' );
		$processor->add_class( 'btn-jelly-bean' );

		return $processor->get_updated_html();
	}
}

/**
 * Hide Expand and optionally style action links as theme buttons.
 */
function tainacan_interface_register_item_gallery_filters() {
	if ( ! tainacan_interface_has_media_item_actions() ) {
		return;
	}

	add_filter(
		'tainacan_get_the_media_item_expand_control',
		function( $html ) {
			$settings = tainacan_interface_get_item_gallery_settings();

			if ( $settings['hide_expand'] ) {
				return '';
			}

			if ( $settings['appearance'] === 'button' ) {
				$html = tainacan_interface_add_media_action_button_classes( $html );
			}

			return $html;
		}
	);

	$add_download_button_classes = function( $html ) {
		if ( $html === '' ) {
			return $html;
		}

		$settings = tainacan_interface_get_item_gallery_settings();

		if ( $settings['appearance'] !== 'button' ) {
			return $html;
		}

		return tainacan_interface_add_media_action_button_classes( $html );
	};

	add_filter( 'tainacan_get_the_item_document_download_link', $add_download_button_classes, 10, 1 );
	add_filter( 'tainacan_get_the_item_attachment_download_link', $add_download_button_classes, 10, 1 );
}
add_action( 'init', 'tainacan_interface_register_item_gallery_filters' );
