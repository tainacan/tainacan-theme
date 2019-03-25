<?php if ( have_posts() ) : ?>
	<div class="mt-5 tainacan-list-post table-responsive">
		<table class="table table-hover">
			<thead>
				<tr>
					<th scope="col"></th>
					<th scope="col"><?php _e( 'Title', 'tainacan-interface' ); ?></th>
					<th scope="col"><?php _e( 'Description', 'tainacan-interface' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php while ( have_posts() ) : the_post(); ?>
					<tr class="tainacan-list-collection" onclick="location.href='<?php the_permalink(); ?>'">
						<td class="collection-miniature">
							<?php if ( has_post_thumbnail() ) : 
								the_post_thumbnail('tainacan-small', array('class' => 'img-fluid')); ?>
							<?php else : ?>
								<div class="image-placeholder">
									<h4>
									<?php echo esc_html( tainacan_get_initials( get_the_title(), true ) ); ?>
									</h4>
								</div>
							<?php endif; ?>
						</td>
						<td class="collection-title text-black"><?php the_title(); ?></td>
						<td class="collection-description text-oslo-gray"><?php the_excerpt(); ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>

	<?php echo tainacan_pagination(); ?>

<?php else : ?>
	<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>
