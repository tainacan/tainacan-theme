<?php
/**
 * Template Part: Unified Gallery (Document + Attachments)
 *
 * Renders document and attachments as a single gallery component
 * for the gallery layout types (gm, gtm, mg).
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gallery_color = get_theme_mod( 'tainacan_gallery_color_scheme', 'light' );
$gallery_sticky = get_theme_mod( 'tainacan_gallery_sticky', false );
$gallery_columns = get_theme_mod( 'tainacan_gallery_thumbnail_columns', 5 );
$lightbox_enabled = get_theme_mod( 'tainacan_lightbox_enabled', true );
$show_filename = get_theme_mod( 'tainacan_lightbox_show_filename', false );
$show_caption = get_theme_mod( 'tainacan_lightbox_show_caption', true );
$show_description = get_theme_mod( 'tainacan_lightbox_show_description', false );
$show_download = get_theme_mod( 'tainacan_lightbox_show_download', true );

$gallery_mode = get_theme_mod( 'tainacan_single_item_gallery_mode', false );

$sticky_class = $gallery_sticky ? 'tainacan-gallery-sticky' : '';
$color_class = 'gallery-scheme-' . esc_attr( $gallery_color );

?>
<div class="tainacan-item-gallery-unified <?php echo esc_attr( $sticky_class . ' ' . $color_class ); ?>"
	 data-columns="<?php echo esc_attr( $gallery_columns ); ?>"
	 data-lightbox="<?php echo $lightbox_enabled ? 'true' : 'false'; ?>">

	<?php
	$section_label = get_theme_mod( 'tainacan_single_item_documents_section_label', '' );
	if ( ! empty( $section_label ) ) : ?>
		<h2 class="tainacan-single-item-section-label">
			<?php echo esc_html( $section_label ); ?>
			<?php
			if ( function_exists( 'tainacan_help_button' ) ) {
				echo tainacan_help_button( 'item-gallery' );
			}
			?>
		</h2>
	<?php endif; ?>

	<?php
	/**
	 * Use the Tainacan gallery function if available (combines document + attachments)
	 */
	if ( function_exists( 'tainacan_the_item_gallery' ) ) {

		$args = array(
			'blockId'                    => 'tainacan-item-gallery-unified-' . get_the_ID(),
			'layoutElements'             => array(
				'main'       => true,
				'thumbnails' => true,
			),
			'mediaSources'               => array(
				'document'    => true,
				'attachments' => true,
			),
			'hideFileNameMain'           => get_theme_mod( 'tainacan_single_item_hide_files_name_main', true ),
			'hideFileCaptionMain'        => get_theme_mod( 'tainacan_single_item_hide_files_caption_main', false ),
			'hideFileDescriptionMain'    => get_theme_mod( 'tainacan_single_item_hide_files_description_main', true ),
			'hideFileNameThumbnails'     => get_theme_mod( 'tainacan_single_item_hide_files_name', true ),
			'hideFileNameLightbox'       => ! $show_filename,
			'hideFileCaptionLightbox'    => ! $show_caption,
			'hideFileDescriptionLightbox' => ! $show_description,
			'openLightboxOnClick'        => $lightbox_enabled,
			'lightboxHasLightBackground' => ( $gallery_color === 'light' ),
			'showDownloadButtonMain'     => $show_download,
		);

		tainacan_the_item_gallery( $args );

	} elseif ( function_exists( 'tainacan_get_the_media_component' ) ) {

		// Fallback for older Tainacan versions
		$hide_file_name = get_theme_mod( 'tainacan_single_item_hide_files_name', true );
		$max_height = get_theme_mod( 'tainacan_single_item_document_max_height', '60vh' );

		echo '<div class="tainacan-media-component-gallery" style="max-height:' . esc_attr( $max_height ) . ';">';
		echo tainacan_get_the_media_component(
			'',
			'',
			'tainacan-item-file__document',
			'tainacan-item-file__media',
			( $hide_file_name ? '' : '%file_name%' )
		);
		echo '</div>';

	} else {
		?>
		<div class="tainacan-gallery-fallback">
			<p class="alert alert-info">
				<?php esc_html_e( 'Gallery mode requires Tainacan plugin version 0.18 or higher.', 'tainacan-interface' ); ?>
			</p>
		</div>
		<?php
	}
	?>
</div>
