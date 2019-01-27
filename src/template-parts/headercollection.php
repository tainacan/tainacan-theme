<?php
echo '<style>
nav.menu-belowheader #menubelowHeader ul.dropdown-menu {
	min-width: 10rem !important;
}';

$background_color = esc_attr( get_post_meta( tainacan_get_collection_id(), 'tainacan_theme_collection_background_color', true ) );
$text_color = esc_attr( get_post_meta( tainacan_get_collection_id(), 'tainacan_theme_collection_color', true ) );
if ( $background_color ) {
	echo ".t-bg-collection {
		background-color: $background_color !important;
	}";
	echo ".t-bg-collection h2, .t-bg-collection .t-collection--info-description-text {
		color: $text_color !important;
	}";

	echo ".t-bg-collection a {
		color: $text_color !important;
		opacity: 0.6;
	}";
}

echo '</style>';
?>

<div <?php if ( get_header_image() ) : ?>class="page-header header-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else : ?>class="page-header header-filter page-collection" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
	<div class="container-fluid px-0 t-bg-collection" style="<!-- z-index: 0; -->">
		<div class="collection-header position-relative max-large" style="">
			<?php do_action( 'tainacan-interface-collection-header' ); ?>
			<?php if ( has_post_thumbnail( tainacan_get_collection_id() ) ) : ?>
				<img src="<?php echo get_the_post_thumbnail_url( tainacan_get_collection_id() ); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute text-left">
			<?php else : ?>
				<div class="image-placeholder rounded-circle border border-white position-absolute">
					<h4 class="text-center">
					<?php
						echo tainacan_get_initials( tainacan_get_the_collection_name() );
					?>
					</h4>
				</div>
			<?php endif;
			global $wp; ?>
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
		</div>
		<div class="row t-collection--info max-large margin-one-column" style="overflow-x: inherit;">
			<div class="col-4 col-md-3 px-0 t-collection--col-3">
				
			</div>
			<div class="col-8 col-md-9 pl-0 t-collection--col-9 mt-md-3" style="z-index: 2">
				<h2 class="t-collection--info-title text-white">
					<?php tainacan_the_collection_name(); ?>
				</h2>
				<?php $tainacan_collection_description = tainacan_get_the_collection_description(); ?>
				<?php if ( ! empty( $tainacan_collection_description ) || has_action( 'tainacan-interface-collection-description' ) ) : ?>
					<div class="text-white t-collection--info-description-text tainacan-interface-truncate">
						<?php tainacan_the_collection_description(); ?>
						<?php do_action( 'tainacan-interface-collection-description' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php do_action( 'tainacan-interface-collection-header-bottom' ); ?>
		</div>
	</div>
</div>
