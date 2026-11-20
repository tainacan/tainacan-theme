<?php
/**
 * Banner shown at the top of the listing and single templates.
 *
 * @package tainacan-interface
 */

if ( 'none' !== tainacan_interface_opt( 'hero_style' ) ) :
	if ( ! is_singular() || ! (bool) get_theme_mod( 'tainacan_featured_image_on_header_banner', false ) || ! has_post_thumbnail() ) :
		?>
		<div
		<?php
		if ( get_header_image() ) :
			?>
			class="page-header header-filter clear-filter page-height" style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_header_image() ) ); ?>')"
			<?php
else :
	?>
			class="page-header header-filter clear-filter align-items-center" style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_template_directory_uri() . '/assets/images/capa.png' ) ); ?>')"<?php endif; ?>>
	<?php else : ?>
		<div class="page-header header-filter clear-filter page-height" style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_the_post_thumbnail_url() ) ); ?>')">
	<?php endif; ?>
		<div class="container-fluid p-0 ph-title-description">
			<?php if ( ! (bool) get_theme_mod( 'tainacan_hide_site_title_on_header_banner', false ) ) : ?>
				<div 
						class="title-header 
						<?php
						if ( is_singular() || is_archive() || is_search() || is_home() ) {
							echo 'singular-title'; }
						?>
						">
					<h1 class="mb-0">
						<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
					</h1>
					<?php do_action( 'tainacan-interface-banner-header-description' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
