<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

<main id="tainacan-site-content" role="main" class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<?php get_template_part( 'template-parts/loop', 'singular' ); ?>
		</div>
	</div><!-- /.row -->
</main>
<?php get_footer(); ?>
