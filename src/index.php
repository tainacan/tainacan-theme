<?php
/**
 * Fallback template for listings.
 *
 * @package tainacan-interface
 */

get_header();
?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

<main role="main" class="mt-5 max-large margin-one-column">
	<div class="row justify-content-between">
		<div class="col-12 
		<?php
		if ( tainacan_interface_has_sidebar() ) {
			?>
			col-lg-8 pr-lg-0<?php } ?>">
			<?php get_template_part( 'template-parts/loop' ); ?>
		</div>
		<?php
		if ( tainacan_interface_has_sidebar() ) {
			get_sidebar(); }
		?>
	</div>
</main>
<?php
get_footer();
