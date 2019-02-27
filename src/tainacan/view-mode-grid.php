<?php if ( have_posts() ) : ?>

	<div class="tainacan-grid-container">

		<?php while ( have_posts() ) : the_post(); ?>
			
			<a class="tainacan-grid-item" href="<?php the_permalink(); ?>">
				<p class="metadata-title">     
					<?php the_title(); ?>           
				</p>
				<?php if ( has_post_thumbnail() ) : ?>
					<div 
							style="background-image: url(<?php the_post_thumbnail_url( 'tainacan-medium' ) ?>)"
							class="grid-item-thumbnail">
						<?php the_post_thumbnail( 'tainacan-medium' ); ?>
						<div class="skeleton"></div> 
					</div>
				<?php else : ?>
					<div 
							style="background-image: url(<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/thumbnail_placeholder.png'?>)"
							class="grid-item-thumbnail">
						<?php echo '<img alt="', esc_attr_e('Thumbnail placeholder', 'tainacan-interface'), '" src="', get_template_directory_uri(), '/assets/images/thumbnail_placeholder.png">'?>
						<div class="skeleton"></div> 
					</div>
				<?php endif; ?>  
			</a>	
		
		<?php endwhile; ?>
	
	</div>

<?php else : ?>
	<div class="tainacan-grid-container">
		<section class="section">
			<div class="content has-text-gray4 has-text-centered">
				<p>
					<span class="icon is-large">
						<i class="mdi mdi-48px mdi-file-multiple"></i>
					</span>
				</p>
				<p><?php echo __( 'No item was found.','tainacan-interface' ); ?></p>
			</div>
		</section>
	</div>
<?php endif; ?>
