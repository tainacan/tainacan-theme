<?php
$settings = tainacan_interface_get_item_gallery_settings();
global $post;

if ( tainacan_has_document() && ! $settings['is_gallery_mode'] ) : ?>
	<div class="mt-3 tainacan-single-post">
		<?php if ( get_theme_mod( 'tainacan_single_item_document_section_label', __( 'Document', 'tainacan-interface' ) ) != '' ) : ?>
			<h2 class="title-content-items" id="single-item-document-label">
				<?php echo esc_html( get_theme_mod( 'tainacan_single_item_document_section_label', __( 'Document', 'tainacan-interface' ) ) ); ?>
			</h2>
		<?php endif; ?>
		<section class="tainacan-content single-item-collection margin-two-column"<?php echo tainacan_interface_get_item_gallery_data_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?>>
			<div class="single-item-collection--document">
			<?php if ( function_exists( 'tainacan_the_item_gallery' ) ) {
				tainacan_the_item_gallery( tainacan_interface_get_item_gallery_args( 'document', $settings, $post->ID ) );
			} elseif ( function_exists( 'tainacan_get_the_media_component' ) ) {
				$media_items_main = array();

				$class_slide_metadata = '';
				if ( $settings['hide_file_name_main'] ) {
					$class_slide_metadata .= ' hide-name';
				}
				if ( $settings['hide_file_description_main'] ) {
					$class_slide_metadata .= ' hide-description';
				}
				if ( $settings['hide_file_caption_main'] ) {
					$class_slide_metadata .= ' hide-caption';
				}

				if ( tainacan_has_document() ) {
					$is_document_type_attachment = tainacan_get_the_document_type() === 'attachment';
					$media_items_main[] =
						tainacan_get_the_media_component_slide( array(
							'after_slide_metadata' => ( ( ! $settings['hide_download_button'] && tainacan_the_item_document_download_link() != '' ) ?
															( '<span class="tainacan-item-file-download">' . tainacan_the_item_document_download_link() . '</span>' )
													: '' ),
							'media_content' => tainacan_get_the_document(),
							'media_content_full' => $is_document_type_attachment ? tainacan_get_the_document( 0, 'full' ) : ( '<div class="attachment-without-image">' . tainacan_get_the_document( 0, 'full' ) . '</div>' ),
							'media_title' => $is_document_type_attachment ? get_the_title( tainacan_get_the_document_raw() ) : '',
							'media_description' => $is_document_type_attachment ? get_the_content( tainacan_get_the_document_raw() ) : '',
							'media_caption' => $is_document_type_attachment ? wp_get_attachment_caption( tainacan_get_the_document_raw() ) : '',
							'media_type' => tainacan_get_the_document_type(),
							'class_slide_metadata' => $class_slide_metadata,
						) );
				}

				tainacan_the_media_component(
					'tainacan-item-document_id-' . $post->ID,
					null,
					$media_items_main,
					array(
						'class_main_div' => '',
						'class_thumbs_div' => '',
						'swiper_thumbs_options' => '',
						'swiper_main_options' => array(
							'navigation' => array(
								'nextEl' => '.swiper-navigation-next_' . 'tainacan-item-document_id-' . $post->ID . '-main',
								'prevEl' => '.swiper-navigation-prev_' . 'tainacan-item-document_id-' . $post->ID . '-main',
								'preloadImages' => false,
								'lazy' => true,
							),
						),
						'disable_lightbox' => $settings['disable_gallery_lightbox'],
					)
				);

			} else {
				?>
				<div style="text-aling: center; max-width: 600px; margin: 2em auto; width: 100%; font-style: italic;">
					<p><?php __( 'It seems that you are using a legacy vesion of the Tainacan plugin. Please update in order to use the latest features for displaying item media.', 'tainacan-interface' ); ?></p>
				</div>
				<?php
			} ?>
			</div>
		</section>
	</div>

	<div class="my-5 border-bottom border-silver"></div>

<?php endif; ?>
