<?php global $wp; ?>
<div class="collection-header--share">
	<div class="btn trigger">
		<span class="tainacan-icon tainacan-icon-share"></span>
	</div>
	<div class="icons">
		<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
			<div class="rotater">
				<a href="http://www.facebook.com/sharer.php?u=<?php echo esc_url( home_url( $wp->request ) ); ?>" target="_blank" title="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>">
					<div class="btn btn-icon">
						<i class="tainacan-icon tainacan-icon-facebook"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) && get_theme_mod( 'tainacan_twitter_user' ) ) : ?>
			<div class="rotater">
				<a href="http://twitter.com/share?url=<?php echo esc_url( home_url( $wp->request ) ); ?>&amp;text=<?php the_title_attribute(); ?>&amp;via=<?php echo esc_attr( get_theme_mod( 'tainacan_twitter_user', '' ) ); ?>" target="_blank" title="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>">
					<div class="btn btn-icon">
						<i class="tainacan-icon tainacan-icon-twitter"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( true == get_theme_mod( 'tainacan_whatsapp_share', false ) ) : ?>
			<div class="rotater">
				<a href="https://api.whatsapp.com/send?1=pt_BR&text=<?php echo esc_url( home_url( $wp->request ) ); ?>" target="_blank" title="<?php esc_attr_e('Share this on WhatsApp', 'tainacan-interface') ?>">
					<div class="btn btn-icon">
						<i class="tainacan-icon tainacan-icon-whatsapp"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( true == get_theme_mod( 'tainacan_telegram_share', false ) ) : ?>
			<div class="rotater">
				<a href="https://t.me/share/url?url=<?php echo esc_url( home_url( $wp->request ) ); ?>" target="_blank" title="<?php esc_attr_e('Share this on Telegram', 'tainacan-interface') ?>">
					<div class="btn btn-icon">
						<i class="tainacan-icon tainacan-icon-telegram"></i>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<div class="rotater">
			<button onclick="copyTextToClipboard('<?php echo esc_url( home_url( $wp->request ) ); ?>')" title="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>" class="tainacan-copy-link-button">
				<div class="btn btn-icon">
					<i class="tainacan-icon tainacan-icon-url"></i>
				</div>
			</button>
		</div>
	</div>
</div>
