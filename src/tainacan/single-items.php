<?php get_header(); ?>

<main class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<?php if ( have_posts() ) : ?>

				<?php do_action( 'tainacan-interface-single-item-top' ); ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
						
						<?php
							get_template_part( 'template-parts/single-items-header' );
							do_action( 'tainacan-interface-single-item-after-title' );

							echo '<div class="single-item-data-section">';

							switch (get_theme_mod( 'tainacan_single_item_layout_sections_order', 'document-attachments-metadata')) {
								case 'document-attachments-metadata':
									get_template_part( 'template-parts/single-items-document' );
									do_action( 'tainacan-interface-single-item-after-document' );  
					
									get_template_part( 'template-parts/single-items-attachments' );
									do_action( 'tainacan-interface-single-item-after-attachments' );
									
									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );
								break;

								case 'metadata-document-attachments':
									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );

									get_template_part( 'template-parts/single-items-document' );
									do_action( 'tainacan-interface-single-item-after-document' );  
					
									get_template_part( 'template-parts/single-items-attachments' );
									do_action( 'tainacan-interface-single-item-after-attachments' );
								break;

								case 'document-metadata-attachments':
									get_template_part( 'template-parts/single-items-document' );
									do_action( 'tainacan-interface-single-item-after-document' );

									get_template_part( 'template-parts/single-items-metadata' );
									do_action( 'tainacan-interface-single-item-after-metadata' );  
					
									get_template_part( 'template-parts/single-items-attachments' );
									do_action( 'tainacan-interface-single-item-after-attachments' );
								break;
									
							}
							echo '</div>';
						?>
						
						<?php if (get_theme_mod('tainacan_single_item_show_next_previous_links', false)): ?>
							<div id="item-single-navigation" class="d-flex margin-pagination justify-content-between mt-0">
								<div class="pagination">
									<?php previous_post_link('<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-1-25em"></i>&nbsp; %link'); ?>
								</div>
								<div class="pagination">
									<?php next_post_link('%link &nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em"></i>'); ?>
								</div> 
							</div>
						<?php endif; ?>

						<?php get_template_part( 'template-parts/single-items-comments' ); ?>

					</article>
				<?php endwhile; ?>

				<?php do_action( 'tainacan-interface-single-item-bottom' ); ?>

			<?php else : ?>
				<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
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
