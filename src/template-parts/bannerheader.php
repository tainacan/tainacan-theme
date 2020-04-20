<?php if ( false == get_theme_mod( 'tainacan_hide_header_banner', false ) ) : ?>
	<div <?php if ( get_header_image() ) : ?>class="page-header header-filter clear-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else : ?>class="page-header header-filter clear-filter align-items-center" style="background-image: url('<?php echo esc_url( get_template_directory_uri() ) ?>/assets/images/capa.png')"<?php endif; ?>>
		<div class="container-fluid p-0 ph-title-description">
			<?php if ( false == get_theme_mod( 'tainacan_hide_site_title_on_header_banner', false ) ) : ?>
				<div 
						style="background-color: rgba(255, 255, 255, <?php echo (get_theme_mod('tainacan_hide_header_box_opacity', 65)/100) ?>) !important;"
						class="title-header <?php if ( is_singular() || is_archive() || is_search() || is_home() ) { echo 'singular-title'; }?>">
					<h1 class="mb-0 text-truncate">
						<?php bloginfo( 'title' ) ?>
					</h1>
					<?php do_action( 'tainacan-interface-banner-header-description' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
