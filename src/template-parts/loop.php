<?php
/**
 * Listing loop with its title.
 *
 * @package tainacan-interface
 */

?>
<?php get_template_part( 'template-parts/page-title' ); ?>

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
