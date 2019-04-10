<?php global $wp; ?>
<div class="collection-header--share">
	<div class="btn trigger">
		<span class="tainacan-icon tainacan-icon-share"></span>
	</div>
	<div class="icons">
		<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
			<div class="rotater">
				<a href="http://www.facebook.com/sharer.php?u=<?php echo esc_url( home_url( $wp->request ) ); ?>" target="_blank">
					<div class="btn btn-icon">
						<i class="mdi mdi-facebook"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) && get_theme_mod( 'tainacan_twitter_user' ) ) : ?>
			<div class="rotater">
				<a href="http://twitter.com/share?url=<?php echo esc_url( home_url( $wp->request ) ); ?>&amp;text=<?php the_title_attribute(); ?>&amp;via=<?php echo esc_attr( get_theme_mod( 'tainacan_twitter_user', '' ) ); ?>" target="_blank">
					<div class="btn btn-icon">
						<i class="mdi mdi-twitter"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
