<?php
$attachments = tainacan_get_the_attachments();
$settings = tainacan_interface_get_item_gallery_settings();
global $post;

if ( ! empty( $attachments ) || ( $settings['is_gallery_mode'] && tainacan_has_document() ) ) {
	?>

	<div class="mt-3 tainacan-single-post">
		<?php if ( ! $settings['is_gallery_mode'] && get_theme_mod( 'tainacan_single_item_attachments_section_label', __( 'Attachments', 'tainacan-interface' ) ) != '' ) : ?>
			<h2 class="title-content-items" id="single-item-attachments-label">
				<?php echo esc_html( get_theme_mod( 'tainacan_single_item_attachments_section_label', __( 'Attachments', 'tainacan-interface' ) ) ); ?>
			</h2>
		<?php endif; ?>
		<?php if ( $settings['is_gallery_mode'] && get_theme_mod( 'tainacan_single_item_documents_section_label', __( 'Documents', 'tainacan-interface' ) ) != '' ) : ?>
			<h2 class="title-content-items" id="single-item-documents-label">
				<?php echo esc_html( get_theme_mod( 'tainacan_single_item_documents_section_label', __( 'Documents', 'tainacan-interface' ) ) ); ?>
			</h2>
		<?php endif; ?>

		<section
				style="<?php echo ( ! $settings['is_gallery_mode'] ? 'min-height: 10vh;' : '' ); ?>"
				class="tainacan-content single-item-collection margin-two-column"<?php echo tainacan_interface_get_item_gallery_data_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?>>
			<?php tainacan_the_item_gallery( tainacan_interface_get_item_gallery_args( 'attachments', $settings, $post->ID ) ); ?>
		</section>

	</div>

	<div class="my-5 border-bottom border-silver"></div>
	<?php
}
