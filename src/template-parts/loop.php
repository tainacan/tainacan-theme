<?php if ( have_posts() ) : ?>
	<div class="tainacan-title">
		<div class="border-bottom border-jelly-bean tainacan-title-page">
			<ul class="list-inline mb-1 d-flex">
				<li class="list-inline-item text-midnight-blue font-weight-bold title-page">
					<?php if ( is_home() ) {
						if ( get_option( 'page_for_posts' ) ) :
							echo get_the_title( get_option( 'page_for_posts' ) );
						else :
							_e( 'Blog Posts', 'tainacan-interface' );
						endif;
} elseif ( is_search() ) {
	_e( 'Search Results for', 'tainacan-interface' );
	echo ' "' , get_search_query(), '"';
} elseif ( is_archive() ) {
	echo ' ' . get_the_archive_title();
} ?>
				</li>
				<li class="list-inline-item float-right title-back align-self-end ml-auto"><a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a></li>
			</ul>
		</div>
	</div>

	<div class="mt-5 tainacan-list-post margin-md-two-column">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/list-post' ); ?>
		<?php endwhile; ?>
	</div>

	<?php echo tainacan_pagination( 3 ); ?>
	
<?php else : ?>
	<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>
