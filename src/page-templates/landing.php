<?php /* Template Name: Landing */ ?>

<?php get_header(); ?>

<div class="max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<div id="content" role="main">
				<?php get_template_part( 'template-parts/loop', 'singular' ); ?>
			</div><!-- /#content -->
		</div>
	</div><!-- /.row -->
</div>
<?php get_footer(); ?>
