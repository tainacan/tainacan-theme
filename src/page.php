<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>
	</header>
<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menubellowbanner' ); ?>

<div class="container-fluid mt-5 max-large">
	<div class="row">

		<div class="col-sm margin-one-column">
			<main role="main">
				<a href="#content" id="content" name="content" class="sr-only">Conteúdo</a>
				<?php get_template_part( 'template-parts/loop', 'singular' ); ?>
			</main>
		</div>
	</div><!-- /.row -->
</div>
<?php get_footer(); ?>
