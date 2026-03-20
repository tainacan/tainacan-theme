<?php get_header(); ?>

<main id="main-content" class="mt-5 max-large margin-one-column" role="main">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<?php if ( have_posts() ) : ?>

				<?php do_action( 'tainacan-interface-single-item-top' ); ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>

						<?php
							get_template_part( 'template-parts/single-items-header' );
							do_action( 'tainacan-interface-single-item-after-title' );

							// Determine the layout type (supports 6 layout modes)
							$collection_id = 0;
							if ( function_exists( 'tainacan_get_collection_id' ) ) {
								$collection_id = tainacan_get_collection_id();
							}

							$layout_type = function_exists( 'tainacan_get_collection_layout_type' )
								? tainacan_get_collection_layout_type( $collection_id )
								: 'type-dam';

							// Backwards compatibility with old setting
							if ( ! $layout_type || ! in_array( $layout_type, array( 'type-dam', 'type-dma', 'type-mda', 'type-gm', 'type-gtm', 'type-mg' ), true ) ) {
								$old_order = get_theme_mod( 'tainacan_single_item_layout_sections_order', 'document-attachments-metadata' );
								$map = array(
									'document-attachments-metadata' => 'type-dam',
									'metadata-document-attachments' => 'type-mda',
									'document-metadata-attachments' => 'type-dma',
								);
								$layout_type = isset( $map[ $old_order ] ) ? $map[ $old_order ] : 'type-dam';
							}

							$is_gallery_layout = in_array( $layout_type, array( 'type-gm', 'type-gtm', 'type-mg' ), true );
							$gallery_class = $is_gallery_layout
								? ( $layout_type === 'type-gtm' ? 'tainacan-item-layout--gallery-top' : 'tainacan-item-layout--gallery' )
								: '';

							echo '<div class="single-item-data-section ' . esc_attr( $gallery_class ) . '" data-layout="' . esc_attr( $layout_type ) . '">';

							switch ( $layout_type ) {

								// Classic layouts: separate document, attachments, metadata
								case 'type-dam':
									get_template_part( 'template-parts/single-items-document' );
									do_action( 'tainacan-interface-single-item-after-document' );

									get_template_part( 'template-parts/single-items-attachments' );
									do_action( 'tainacan-interface-single-item-after-attachments' );

									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );
									break;

								case 'type-dma':
									get_template_part( 'template-parts/single-items-document' );
									do_action( 'tainacan-interface-single-item-after-document' );

									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );

									get_template_part( 'template-parts/single-items-attachments' );
									do_action( 'tainacan-interface-single-item-after-attachments' );
									break;

								case 'type-mda':
									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );

									get_template_part( 'template-parts/single-items-document' );
									do_action( 'tainacan-interface-single-item-after-document' );

									get_template_part( 'template-parts/single-items-attachments' );
									do_action( 'tainacan-interface-single-item-after-attachments' );
									break;

								// Gallery layouts: unified document + attachments
								case 'type-gm':
									// Gallery (sidebar left) → Metadata
									get_template_part( 'template-parts/single-items-gallery-unified' );
									echo '<div class="tainacan-item-metadata-area">';
										get_template_part( 'template-parts/single-items-metadata' );
										do_action( 'tainacan-interface-single-item-after-metadata' );
									echo '</div>';
									break;

								case 'type-gtm':
									// Gallery (top full width) → Metadata
									get_template_part( 'template-parts/single-items-gallery-unified' );
									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );
									break;

								case 'type-mg':
									// Metadata → Gallery (sidebar right)
									echo '<div class="tainacan-item-metadata-area">';
										get_template_part( 'template-parts/single-items-metadata' );
										do_action( 'tainacan-interface-single-item-after-metadata' );
									echo '</div>';
									get_template_part( 'template-parts/single-items-gallery-unified' );
									break;
							}

							echo '</div>';
						?>

						<?php get_template_part( 'template-parts/single-items-related-items' );
							  do_action( 'tainacan-interface-single-item-after-related-items' );
							  // Keep old typo hook for backwards compatibility
							  do_action( 'tainacan-interface-single-item-after-reated-items' ); ?>

						<?php get_template_part( 'template-parts/single-items-navigation' ); ?>

						<?php get_template_part( 'template-parts/single-items-comments' ); ?>

					</article>
				<?php endwhile; ?>

				<?php do_action( 'tainacan-interface-single-item-bottom' ); ?>

			<?php else : ?>
				<?php esc_html_e( 'Nothing found', 'tainacan-interface' ); ?>
			<?php endif; ?>
		</div>
	</div><!-- /.row -->
</main>

<?php get_footer(); ?>

<script>
	jQuery('#topNavbar').addClass('b-bottom-top');
	jQuery('nav.menu-belowheader').removeClass('border-bottom');
	jQuery('nav.menu-belowheader .max-large').addClass('b-bottom-bellow');
</script>
