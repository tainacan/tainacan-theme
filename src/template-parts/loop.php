<div class="tainacan-title">
	<div class="tainacan-title-page">
		<ul class="list-inline mb-1 d-flex">
			<li class="list-inline-item text-midnight-blue font-weight-bold title-page">
				<h1>
				<?php if ( is_home() ) {
					if ( get_option( 'page_for_posts' ) ) :
						echo get_the_title( get_option( 'page_for_posts' ) );
					else :
						_e( 'Blog Posts', 'tainacan-interface' );
					endif;
				} elseif ( is_singular() ) {
					bloginfo( 'title' );
				} elseif ( is_search() ) {
					_e( 'Search Results for', 'tainacan-interface' );
					echo ' "' , get_search_query(), '"';
				} elseif ( is_tag() || is_category() || is_tax() ) {
					single_term_title();
				} elseif ( is_archive() ) {
					if ( have_posts() ) {
						if ( is_day() ) :
							printf( __( 'Daily Archives: %s', 'tainacan-interface' ), get_the_date() );
						elseif ( is_month() ) :
							printf( __( 'Monthly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'tainacan-interface' ) ) );
						elseif ( is_year() ) :
							printf( __( 'Yearly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'Y', 'yearly archives date format', 'tainacan-interface' ) ) );
						elseif ( is_author() ) :
							echo get_the_author();
						elseif ( 'tainacan-collection' == get_post_type() ) :
							echo tainacan_the_collection_name();
						else :
							echo get_the_archive_title();
						endif;
					}
				} ?>
				</h1>
			</li>
			<li class="list-inline-item float-right title-back align-self-end ml-auto"><a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a></li>
		</ul>
	</div>
</div>

<?php if ( have_posts() ) : ?>

	<div class="mt-5 tainacan-list-post margin-md-two-column">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/list-post' ); ?>
		<?php endwhile; ?>
	</div>

	<?php echo tainacan_pagination(); ?>
	
<?php else : ?>
	<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>
