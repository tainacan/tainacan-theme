<?php
/**
 * Listing loop with its title.
 *
 * @package tainacan-interface
 */

?>
<div class="tainacan-title">
	<div class="tainacan-title-page">
		<ul class="list-inline mb-1 d-flex">
			<li class="list-inline-item text-midnight-blue font-weight-bold title-page">
				<h1>
				<?php
				if ( is_home() ) {
					if ( get_option( 'page_for_posts' ) ) :
						echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
					else :
						esc_html_e( 'Blog Posts', 'tainacan-interface' );
					endif;
				} elseif ( is_singular() ) {
					echo esc_html( get_bloginfo( 'name' ) );
				} elseif ( is_search() ) {
					esc_html_e( 'Search Results for', 'tainacan-interface' );
					echo ' "' . esc_html( get_search_query( false ) ) . '"';
				} elseif ( is_tag() || is_category() || is_tax() ) {
					single_term_title();
				} elseif ( is_archive() ) {
					if ( have_posts() ) {
						if ( is_day() ) :
							/* translators: %s: date. */
							printf( esc_html__( 'Daily Archives: %s', 'tainacan-interface' ), esc_html( get_the_date() ) );
						elseif ( is_month() ) :
							/* translators: %s: month and year. */
							printf( esc_html__( 'Monthly Archives: %s', 'tainacan-interface' ), esc_html( get_the_date( _x( 'F Y', 'monthly archives date format', 'tainacan-interface' ) ) ) );
						elseif ( is_year() ) :
							/* translators: %s: year. */
							printf( esc_html__( 'Yearly Archives: %s', 'tainacan-interface' ), esc_html( get_the_date( _x( 'Y', 'yearly archives date format', 'tainacan-interface' ) ) ) );
						elseif ( is_author() ) :
							echo esc_html( get_the_author() );
						elseif ( 'tainacan-collection' === get_post_type() ) :
							tainacan_the_collection_name();
						else :
							echo wp_kses_post( get_the_archive_title() );
						endif;
					}
				}
				?>
				</h1>
			</li>
			<li class="list-inline-item float-right title-back align-self-end ml-auto"><a href="javascript:history.go(-1)"><?php esc_html_e( 'Back', 'tainacan-interface' ); ?></a></li>
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

	<?php tainacan_pagination(); ?>
	
<?php else : ?>
	<?php esc_html_e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>
