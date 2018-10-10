<?php global $wp; ?>
<div class="collection-header--share" style="margin-right: 4.16666666667%;">
	<div class="btn trigger">
		<span class="mdi mdi-share-variant"></span>
	</div>
	<div class="icons">
		<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
			<div class="rotater">
				<a href="http://www.facebook.com/sharer.php?u=<?php echo home_url( $wp->request ); ?>" target="_blank">
					<div class="btn btn-icon">
						<i class="mdi mdi-facebook"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( true == get_theme_mod( 'tainacan_google_share', true ) ) : ?> 
			<div class="rotater">
				<a href="https://plus.google.com/share?url=<?php echo home_url( $wp->request ); ?>" target="_blank">
					<div class="btn btn-icon">
						<i class="mdi mdi-google-plus"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) && get_option( 'tainacan_twitter_user' ) ) : ?>
			<div class="rotater">
				<a href="http://twitter.com/share?url=<?php echo home_url( $wp->request ); ?>&amp;text=<?php the_title_attribute(); ?>&amp;via=<?php echo esc_attr( get_option( 'tainacan_twitter_user', '' ) ); ?>" target="_blank">
					<div class="btn btn-icon">
						<i class="mdi mdi-twitter"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
