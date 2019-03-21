<?php echo '<style>
	nav.menu-belowheader #menubelowHeader ul.dropdown-menu {
		min-width: 10rem !important;
	}
</style>';

$term = tainacan_get_term();
$taxonomy = get_taxonomy( $term->taxonomy );
$current_term = \Tainacan\Repositories\Terms::get_instance()->fetch($term->term_id, $term->taxonomy);
$image =  $current_term->get_header_image_id();
$src = wp_get_attachment_image_src($image, 'full');
?>

<div class="page-header-taxonomy <?php if(!$src) : echo 'page-header-taxonomy--height-auto'; endif; ?>">

	<div class="container-fluid mt-0 mb-0 p-0 d-flex flex-wrap <?php if ($src) { echo 'max-large margin-one-column'; } ?>">
		<?php do_action( 'tainacan-interface-taxonomy-header' ); ?>

		<?php if($src) : ?>
		<div class="col-12 col-sm-4 p-0 page-header-image" style="background-image: url('<?php echo $src[0]; ?>');"></div>
		<?php endif; ?>
		<div class="col-12 col-sm p-0 page-header-content">
			<div class="page-header-content-meta <?php if (!$src) { echo 'max-large'; } ?>">
				<div class="page-header-content-title d-inline-flex border-bottom">
					<h2 class="page-header-title">
						<?php echo $taxonomy->labels->name . ':'; ?>
						<span style="font-weight: 500;">
							<?php tainacan_the_term_name(); ?>
						</span>
					</h2>
					<a class="page-header-back ml-auto" href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a>
				</div>
				<?php $tainacan_term_description = tainacan_get_the_term_description(); ?>
				<div class="page-header-hightlights d-flex flex-wrap">
					<div class="col-12 col-lg-10 p-0 page-header-description">
						<?php echo $tainacan_term_description; ?>
					</div>
					<?php do_action( 'tainacan-interface-taxonoy-description' ); ?>
					<div class="col-12 col-lg-2 d-flex flex-wrap page-header-share">
						<div class="page-header-icons">
							<p class="share-title"><?php _e('Share', 'tainacan-interface'); ?></p>
							<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
								<a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" class="share-link" target="_blank">
									<img src="<?php echo get_template_directory_uri() . '/assets/images/facebook-circle.png'; ?>" alt="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
							<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) ) : ?> 
								<?php $twitter_option = get_option( 'tainacan_twitter_user' ); ?>
								<?php $via = ! empty( $twitter_option ) ? '&amp;via=' . esc_attr( get_option( 'tainacan_twitter_user' ) ) : ''; ?>
								<a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?><?php echo $via; ?>" target="_blank" class="share-link">
									<img src="<?php echo get_template_directory_uri() . '/assets/images/twitter-circle.png'; ?>" alt="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
							<?php if ( true == get_theme_mod( 'tainacan_google_share', true ) ) : ?> 
								<a href="https://plus.google.com/share?url=<?php the_permalink(); ?>" target="_blank" class="share-link">
									<img src="<?php echo get_template_directory_uri() . '/assets/images/google-plus-circle.png'; ?>" alt="<?php esc_attr_e('Share this on google plus', 'tainacan-interface') ?>" class="share-images">
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'tainacan-interface-taxonomy-header-bottom' ); ?>
	</div>
</div>