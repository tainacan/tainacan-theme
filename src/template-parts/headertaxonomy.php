<?php echo '<style>
	nav.menu-belowheader #menubelowHeader ul.dropdown-menu {
		min-width: 10rem !important;
	}
</style>';

$current_wpterm = tainacan_get_term();

$current_taxonomy = get_taxonomy( $current_wpterm->taxonomy );
$current_term = \Tainacan\Repositories\Terms::get_instance()->fetch($current_wpterm->term_id, $current_wpterm->taxonomy);

$image =  $current_term->get_header_image_id();
$src = wp_get_attachment_image_src($image, 'full');
?>

<div class="page-header-taxonomy <?php if(!$src) : echo 'page-header-taxonomy--height-auto'; endif; ?>">

	<div class="container-fluid mt-0 mb-0 p-0 d-flex flex-wrap <?php if ($src) { echo 'max-large margin-one-column'; } ?>">
		<?php do_action( 'tainacan-interface-taxonomy-header' ); ?>

		<?php if($src) : ?>
		<!--<div class="col-12 col-sm-4 p-0 page-header-image" style="background-image: url('<?php echo $src[0]; ?>');"></div>-->
		<img class="page-header-image" src="<?php echo $src[0]; ?>" alt="<?php tainacan_the_term_name(); ?>">
		<?php endif; ?>
		<div class="col-12 col-sm p-0 page-header-content">
			<div class="page-header-content-meta <?php if (!$src) { echo 'max-large'; } ?>">
				<div class="page-header-content-title d-inline-flex border-bottom">
					<h2 class="page-header-title">
						<?php echo $current_taxonomy->labels->name . ':'; ?>
						<span style="font-weight: 500;">
							<?php tainacan_the_term_name(); ?>
						</span>
					</h2>
					<a class="page-header-back ml-auto" href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a>
				</div>
				<div class="page-header-hightlights d-flex flex-wrap">
					<div class="col-12 col-xl-10 col-lg-9 page-header-description tainacan-interface-truncate-term">
						<?php tainacan_the_term_description(); ?>
						<?php do_action( 'tainacan-interface-term-description' ); ?>
					</div>
					<?php do_action( 'tainacan-interface-taxonomy-description' ); ?>
					<div class="col-12 col-xl-2 col-lg-3 d-flex flex-wrap page-header-share">
						<div class="page-header-icons">
							<p class="share-title"><?php _e('Share', 'tainacan-interface'); ?></p>
							<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
								<a href="http://www.facebook.com/sharer.php?u=<?php echo get_term_link((int) $current_term->get_id()); ?>" title="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>" class="share-link" target="_blank">
									<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/facebook-circle.png'; ?>" alt="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
							<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) ) : ?> 
								<?php $twitter_option = get_option( 'tainacan_twitter_user' ); ?>
								<?php $via = ! empty( $twitter_option ) ? '&amp;via=' . esc_attr( get_option( 'tainacan_twitter_user' ) ) : ''; ?>
								<a href="http://twitter.com/share?url=<?php echo get_term_link((int) $current_term->get_id()); ?>&amp;text=<?php echo $current_taxonomy->labels->name . ':' . $current_term->get_name(); ?><?php echo $via; ?>" target="_blank" title="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>" class="share-link">
									<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/twitter-circle.png'; ?>" alt="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
							<?php if ( true == get_theme_mod( 'tainacan_whatsapp_share', false ) ) : ?>
								<a href="https://api.whatsapp.com/send?1=pt_BR&text=<?php echo get_term_link((int) $current_term->get_id()); ?>" class="share-link" target="_blank" title="<?php esc_attr_e('Share this on WhatsApp', 'tainacan-interface') ?>">
									<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/whatsapp-circle.png'; ?>" alt="<?php esc_attr_e('Share this on WhatsApp', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
							<?php if ( true == get_theme_mod( 'tainacan_telegram_share', false ) ) : ?>
								<a href="https://t.me/share/url?url=<?php echo get_term_link((int) $current_term->get_id()); ?>" class="share-link" target="_blank" title="<?php esc_attr_e('Share this on Telegram', 'tainacan-interface') ?>">
									<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/telegram-circle.png'; ?>" alt="<?php esc_attr_e('Share this on Telegram', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
							<button id="tainacan-interface-sharer" data-link="<?php echo get_term_link((int) $current_term->get_id()); ?>" title="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>" class="share-link tainacan-copy-link-button">
								<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/url-circle.png'; ?>" alt="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>" class="share-images">
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'tainacan-interface-taxonomy-header-bottom' ); ?>
	</div>
</div>