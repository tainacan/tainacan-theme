<?php if ( have_posts() ) : ?>
	<div class="tainacan-list-post px-md-0 mt-5">
		<div class="tainacan-list-collection--container-grid justify-content-center">
			<?php while ( have_posts() ) : the_post(); ?>
				<a class="tainacan-list-collection--grid-link" href="<?php the_permalink(); ?>">
					<div class="tainacan-list-collection--grid">
						<p class="tainacan-list-collection--grid-title text-black text-left p-3 mb-0 text-truncate">
							<?php the_title(); ?>           
						</p>
						<?php if ( has_post_thumbnail() ) : 
							$thumbnail_id = get_post_thumbnail_id( $post->ID );
							$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true); ?>
							<img src="<?php the_post_thumbnail_url( 'tainacan-medium' ) ?>" class="img-fluid tainacan-list-collection--grid-img" alt="<?php echo esc_attr($alt); ?>">  
						<?php else : ?>
							<div class="image-placeholder">
								<h4 class="text-center">
									<?php echo esc_html( tainacan_get_initials( get_the_title() ) ); ?>
								</h4>
							</div>
						<?php endif; ?>
					</div>
				</a>
			<?php endwhile; ?>
		</div>
	</div>

	<?php echo tainacan_pagination( 3 ); ?>

<?php else : ?>
	<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>
