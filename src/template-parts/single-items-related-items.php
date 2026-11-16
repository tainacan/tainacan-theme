<?php
/**
 * Related items section of a single Tainacan item.
 *
 * @package tainacan-interface
 */

$order_option = get_theme_mod( 'tainacan_single_item_related_items_order', 'title_asc' );

$order_option_split       = explode( '_', $order_option );
$order_by                 = $order_option_split[0] ? $order_option_split[0] : 'title';
$tainacan_interface_order = $order_option_split[1] ? $order_option_split[1] : 'asc';

if ( ! in_array( $order_by, array( 'title', 'date', 'modified' ), true ) ) {
	$order_by = 'title';
}

if ( ! in_array( $tainacan_interface_order, array( 'asc', 'desc' ), true ) ) {
	$tainacan_interface_order = 'asc';
}

$related_items_layout    = get_theme_mod( 'tainacan_single_item_related_items_layout', 'carousel' );
$is_gallery_layout       = strpos( $related_items_layout, 'gallery-' ) !== false;
$gallery_data_attributes = '';

if ( $is_gallery_layout && function_exists( 'tainacan_interface_get_item_gallery_data_attributes' ) ) {
	$gallery_data_attributes = tainacan_interface_get_item_gallery_data_attributes();
}

if ( function_exists( 'tainacan_the_related_items_carousel' ) && get_theme_mod( 'tainacan_single_item_enable_related_items_section', true ) && ! tainacan_interface_aside_takes_related() && tainacan_has_related_items() ) : ?>

<div class="mt-3 tainacan-single-post">

	<?php if ( '' !== (string) get_theme_mod( 'tainacan_single_item_related_items_section_label', __( 'Items related to this', 'tainacan-interface' ) ) ) : ?>
		<h2 class="title-content-items" id="single-item-related-items-label">
			<?php echo esc_html( get_theme_mod( 'tainacan_single_item_related_items_section_label', __( 'Items related to this', 'tainacan-interface' ) ) ); ?>
		</h2>
	<?php endif; ?>
	<section class="tainacan-content single-item-collection margin-two-column"<?php echo $is_gallery_layout ? tainacan_interface_get_item_gallery_data_attributes() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every attribute value is passed through esc_attr() inside the helper. ?>>
		<div class="single-item-collection--related-items justify-content-center">
			<div class="row">
			<?php
				$tainacan_view_mode = '';
			if ( strpos( $related_items_layout, 'tainacan-view-mode-' ) !== false ) {
				$tainacan_view_mode   = str_replace( 'tainacan-view-mode-', '', $related_items_layout );
				$related_items_layout = 'tainacan-view-modes';
			}

				$items_gallery_options = array();
			if ( $is_gallery_layout ) {

				$items_gallery_options = 'gallery-slider' === $related_items_layout ?
					array(
						'layoutElements'             => array(
							'main'       => true,
							'thumbnails' => true,
						),
						'mainSliderHeight'           => get_theme_mod( 'tainacan_single_item_related_items_gallery_max_height', 60 ),
						'thumbnailsCarouselItemSize' => get_theme_mod( 'tainacan_single_item_related_items_gallery_thumbnail_size', 136 ),
					) :
					array(
						'layoutElements'             => array(
							'main'       => false,
							'thumbnails' => true,
						),
						'mainSliderHeight'           => get_theme_mod( 'tainacan_single_item_related_items_gallery_max_height', 60 ),
						'thumbnailsCarouselItemSize' => get_theme_mod( 'tainacan_single_item_related_items_gallery_thumbnail_size', 136 ),
					);

				if ( function_exists( 'tainacan_interface_has_media_thumbs_layout' ) && tainacan_interface_has_media_thumbs_layout() ) {
					$thumbs_layout                                = tainacan_sanitize_media_thumbs_layout(
						get_theme_mod( 'tainacan_single_item_related_items_thumbs_layout', 'carousel' )
					);
					$items_gallery_options['thumbsLayout']        = $thumbs_layout;
					$items_gallery_options['hideImageThumbnails'] = 'list' === $thumbs_layout
						&& (bool) get_theme_mod( 'tainacan_single_item_related_items_hide_image_thumbnails', false );
				}

				if (
					'gallery-slider' === $related_items_layout
					&& function_exists( 'tainacan_interface_has_gallery_cover_mime_types' )
					&& tainacan_interface_has_gallery_cover_mime_types()
				) {
					$items_gallery_options['coverMimeTypesMain'] = (bool) get_theme_mod( 'tainacan_single_item_show_pdf_cover', false )
						? array( 'application/pdf' )
						: array();
				}

				$related_items_layout = 'gallery';
			}

				$related_items_options = array(
					'items_list_layout'      => $related_items_layout,
					'collection_heading_tag' => 'h3',
					'order'                  => $tainacan_interface_order,
					'orderby'                => $order_by,
					'dynamic_items_args'     => array(
						'max_columns_count'  => get_theme_mod( 'tainacan_single_item_related_items_max_columns_count', 4 ),
						'tainacan_view_mode' => $tainacan_view_mode,
					),
					'carousel_args'          => array(
						'max_items_per_screen' => get_theme_mod( 'tainacan_single_item_related_items_max_items_per_screen', 6 ),
					),
					'items_gallery_args'     => $items_gallery_options,
				);
				tainacan_the_related_items_carousel( $related_items_options );
				?>
			</div>
		<div>
	</section>
</div>

<?php endif; ?>