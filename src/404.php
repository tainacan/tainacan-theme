<?php get_header();?>
	</header>
	<main role="main" class="page-header page-404 header-filter clear-filter" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/404.png')">
		<div class="container align-self-center">
			<div class="row">
				<div class="col-md-12 mx-auto">
					<a href="#content" id="content" name="content" class="sr-only">Conteúdo</a>
					<div class="brand mb-5">
						<h2>404</h2>
						<h3><?php _e( 'The page you are looking for does not exist!', 'tainacan-interface' );?></h3>
						<h3>̄\_(ツ)_/ ̄</h3>
					</div>
				</div>
				<div class="col-md-12 mx-auto text-center">
					<a href="<?php echo home_url(); ?>" class="btn btn-primary bg-jungle-green py-0"><?php _e( 'Go to first page', 'tainacan-interface' );?></a>
				</div>
			</div>
		</div><!-- end .container -->
	</main><!-- end .page-header -->
<?php get_footer(); ?>
