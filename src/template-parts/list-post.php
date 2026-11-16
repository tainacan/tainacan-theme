<?php
/**
 * Card for one post in a listing.
 *
 * @package tainacan-interface
 */

$tainacan_interface_show_type = defined( 'TAINACAN_VERSION' ) && ! tainacan_interface_query_flag( 'onlyposts' ) && ! tainacan_interface_query_flag( 'onlypages' ) && is_search();
if ( $tainacan_interface_show_type ) {
	$tainacan_interface_type_object = get_post_type_object( get_post_type() );
	$tainacan_interface_type_label  = $tainacan_interface_type_object ? $tainacan_interface_type_object->labels->singular_name : '';
	$tainacan_interface_type_link   = get_post_type_archive_link( get_post_type() );
}
?>
<div <?php post_class( 'row blog-post mb-4' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="col-xs-12 col-md-4 blog-thumbnail align-self-center text-center mb-4 mb-md-0">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'medium_large', array( 'class' => 'img-fluid' ) ); ?>
			</a>
		</div>
	<?php endif; ?>
	<div class="col-xs-12 blog-content 
	<?php
	if ( has_post_thumbnail() ) :
		?>
col-md-8 blog-flex
		<?php
else :
	?>
		col-md-12<?php endif; ?> align-self-center">
		<?php if ( $tainacan_interface_show_type ) : ?>
			<div class="title-area">
				<h3 class="mb-3">
					<a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a>
				</h3>

				<h4><a href="<?php echo esc_url( (string) $tainacan_interface_type_link ); ?>"><?php echo esc_html( $tainacan_interface_type_label ); ?></a></h4>
			</div>
		<?php else : ?>
			<h3 class="mb-3">
				<a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a>
			</h3>
		<?php endif; ?>
		<?php $tainacan_interface_words = (int) tainacan_interface_opt( 'blog_excerpt' ); ?>
		<?php if ( $tainacan_interface_words > 0 ) : ?>
			<p class="text-black"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $tainacan_interface_words, '...' ) ); ?></p>
		<?php endif; ?>
		<?php
			// Check customizer options for hiding author and date.
			$hide_author = get_theme_mod( 'tainacan_hide_author_in_posts_listing', false );
			$hide_date   = get_theme_mod( 'tainacan_hide_date_in_posts_listing', false );

			// If both are hidden, don't display anything.
		if ( ! $hide_author && ! $hide_date ) {
			tainacan_meta_date_author();
		} else {
			if ( ! $hide_date ) {
				tainacan_meta_date();
			}

			if ( ! $hide_author ) {
				tainacan_meta_author();
			}
		}
		?>
		<a href="<?php the_permalink(); ?>" class="readmore float-right screen-reader-text"><?php esc_html_e( 'Read more...', 'tainacan-interface' ); ?></a>
	</div>
</div>

<hr class="border-mercury">
