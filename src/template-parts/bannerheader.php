<div <?php if ( get_header_image() ) : ?>class="page-header header-filter clear-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else : ?>class="page-header header-filter clear-filter align-items-center" style="background-image: url('<?php echo esc_url( get_template_directory_uri() ) ?>/assets/images/capa.png')"<?php endif; ?>>
	<div class="container-fluid p-0 ph-title-description">
		<div class="bg-white-title title-header <?php if ( is_singular() || is_archive() || is_search() || is_home() ) { echo 'singular-title'; }?>">
			<h1 class="mb-0 text-truncate">
				<?php bloginfo( 'title' ) ?>
			</h1>
			<?php do_action( 'tainacan-interface-banner-header-description' ); ?>
		</div>
	</div>
</div>
