<?php get_header();?>

	<div class="page-header page-404 header-filter clear-filter" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/404.png')">
		<div class="container align-self-center">
			<div class="row">
				<div class="col-md-12 mx-auto">
					<div class="brand mb-5">
						<h1>404</h1>
						<h3><?php _e( 'The page you are looking for does not exist!', 'tainacan-interface' );?></h3>
						<h3>̄\_(ツ)_/ ̄</h3>
					</div>
				</div>
				<div class="col-md-12 mx-auto text-center">
					<a href="<?php echo home_url(); ?>" class="btn btn-primary bg-jungle-green py-0"><?php _e( 'Go to the first page', 'tainacan-interface' );?></a>
				</div>
			</div>
		</div><!-- end .container -->
	</div><!-- end .page-header -->
<?php get_footer(); ?>
