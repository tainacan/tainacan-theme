<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

<main role="main" class="mt-5 max-large margin-one-column">
	<div class="row py-4 justify-content-between">
		<div class="col-12 <?php if ( is_active_sidebar( 'tainacan-sidebar-right' ) ) { ?>col-lg-8 pr-lg-0<?php } ?>">
			<?php get_template_part( 'template-parts/loop' ); ?>
		</div>
		<?php if ( is_active_sidebar( 'tainacan-sidebar-right' ) ) { get_sidebar(); } ?>
	</div>
</main>
<?php get_footer();
