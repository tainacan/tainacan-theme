<?php
/**
 * Site footer.
 *
 * @package tainacan-interface
 */

if ( ! is_404() ) :
	if ( ! get_theme_mod( 'tainacan_use_block_template_parts_on_footer', false ) ) :
		$tainacan_interface_address = get_theme_mod( 'tainacan_blogaddress', '' );
		$tainacan_interface_email   = sanitize_email( (string) get_theme_mod( 'tainacan_blogemail', '' ) );
		$tainacan_interface_phone   = get_theme_mod( 'tainacan_blogphone', '' );
		?>
		<footer class="container-fluid p-4 p-sm-5 mt-5 tainacan-footer <?php echo esc_attr( 'tainacan-footer-' . get_theme_mod( 'tainacan_footer_color', 'dark' ) ); ?>" style="padding-bottom: 0 !important;">
			<?php if ( is_active_sidebar( 'tainacan-sidebar-footer' ) ) : ?>
				<div class="row tainacan-footer-widgets-area">
					<ul class="col-12 col-lg pt-3 pb-3 pl-0 pr-0 d-lg-flex flex-wrap justify-content-xs-center mb-md-0">
						<?php dynamic_sidebar( 'tainacan-sidebar-footer' ); ?>
					</ul>
				</div>
			<?php endif; ?>
			<hr class="tainacan-footer-area-separator"/>
			<div class="row pt-3 pb-4 pl-0 pr-0 tainacan-footer-info">
				<div class="col text-white font-weight-normal">
					<p class="tainacan-footer-info--blog">
						<?php
						echo esc_html( get_bloginfo( 'name' ) );
						echo wp_is_mobile() ? '</p><p>' : '<br>';
						if ( $tainacan_interface_address ) {
							echo esc_html( $tainacan_interface_address );
						}
						if ( $tainacan_interface_email ) {
							printf(
								/* translators: %s: e-mail address. */
								esc_html__( 'E-mail: %s', 'tainacan-interface' ),
								esc_html( $tainacan_interface_email )
							);
						}
						if ( $tainacan_interface_email && $tainacan_interface_phone ) {
							echo wp_is_mobile() ? '<br>' : ' - ';
						}
						if ( $tainacan_interface_phone ) {
							printf(
								/* translators: %s: telephone number. */
								esc_html__( 'Telephone: %s', 'tainacan-interface' ),
								esc_html( $tainacan_interface_phone )
							);
						}
						?>
					</p>
				</div>
				<?php if ( get_theme_mod( 'tainacan_display_footer_logo', true ) ) : ?>
					<div class="col-auto pr-0 pr-md-3 d-none d-md-block align-self-md-top">
						<?php
						$tainacan_interface_logo = get_theme_mod( 'tainacan_footer_logo' );
						if ( ! $tainacan_interface_logo ) {
							$tainacan_interface_logo = get_template_directory_uri() . ( 'light' === get_theme_mod( 'tainacan_footer_color', 'dark' ) ? '/assets/images/logo.svg' : '/assets/images/logo-footer.svg' );
						}
						?>
						<a href="<?php echo esc_url( get_theme_mod( 'tainacan_footer_logo_link', 'https://tainacan.org' ) ); ?>">
							<img src="<?php echo esc_url( $tainacan_interface_logo ); ?>" class="tainacan-footer-info--logo" alt="<?php esc_attr_e( 'Footer logo', 'tainacan-interface' ); ?>">
						</a>
					</div>
				<?php endif; ?>
				<div class="col-12 tainacan-powered">
					<span>
						<?php
						if ( get_theme_mod( 'tainacan_display_powered', false ) ) {
							printf(
								/* translators: 1: WordPress; 2: Tainacan */
								esc_html__( 'Proudly powered by %1$s and %2$s.', 'tainacan-interface' ),
								'<a href="https://wordpress.org/">WordPress</a>',
								'<a href="https://tainacan.org/">Tainacan</a>'
							);
						}
						?>
					</span>
				</div>
			</div>
		</footer>
		<?php
	else :
		block_template_part( 'footer' );
	endif;
endif;
wp_footer();
?>
</body>

</html>
