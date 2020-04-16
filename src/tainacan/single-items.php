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

							get_template_part( 'template-parts/single-items-document' );
							do_action( 'tainacan-interface-single-item-after-document' );  
			
							get_template_part( 'template-parts/single-items-attachments' );
							do_action( 'tainacan-interface-single-item-after-attachments' );
							
							get_template_part( 'template-parts/single-items-metadata' );
							do_action( 'tainacan-interface-single-item-after-metadata' );
				
							get_template_part( 'template-parts/single-items-comments' );
						?>

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
