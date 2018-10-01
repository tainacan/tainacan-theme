<?php if ( ! is_404() ) : ?>
	<footer class="container-fluid p-4 p-sm-5 mt-5 tainacan-footer" style="padding-bottom: 0 !important;">
		<?php if ( is_active_sidebar( 'footer-1' ) ) { ?>
			<div class="row">
				<div class="col-12 col-lg">
					<ul class="p-4 d-lg-flex justify-content-md-center mb-md-0">
						<?php dynamic_sidebar( 'footer-1' ); ?>
					</ul>
				</div>
			</div>
		<?php } ?>
		<hr class="bg-scooter"/>
		<div class="row p-4 tainacan-footer-info">
			<div class="col text-white font-weight-normal">
				<p class="tainacan-footer-info--blog">
					<?php echo bloginfo( 'title' );
					if ( ! wp_is_mobile() ) {
						echo '<br>';
					} else {
						echo '</p><p>';
					}
					if ( get_option( 'blogaddress' ) ) {
						echo get_option( 'blogaddress', '' );
					} ?>
				</p>
				<p class="tainacan-footer-info--blog">
<?php if ( get_option( 'blogemail' ) ) {
	printf( __( 'E-mail: %s', 'tainacan-interface' ), get_option( 'blogemail', '' ) );
}
if ( get_option( 'blogemail' ) && get_option( 'blogphone' ) ) {
	if ( wp_is_mobile() ) :
		echo '<br>';
	else :
		echo ' - ';
	endif;
}
if ( get_option( 'blogphone' ) ) {
	printf( __( 'Telephone: %s', 'tainacan-interface' ), get_option( 'blogphone', '' ) );
} ?>
				</p>
			</div>
			<div class="col-auto pr-0 pr-md-3 d-none d-md-block align-self-md-top">
					<img src="<?php if ( get_theme_mod( 'footer_logo' ) ) { echo esc_attr(get_theme_mod( 'footer_logo' )); } ?>" class="tainacan-footer-info--logo" alt="">
			</div>
			<div class="col-12 tainacan-powered">
				<span>
					<?php if ( true == get_theme_mod( 'display_powered', false ) ) {
						printf( __( 'Proudly powered by %1$s and %2$s.', 'tainacan-interface' ), '<a href="https://wordpress.org/">Wordpress</a>', '<a href="http://tainacan.org/">Tainacan</a>' ); } ?>
				</span>
			</div>
		</div>
	</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>

</html>
