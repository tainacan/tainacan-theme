<article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
	<?php if ( get_the_title()  != '') : ?>
		<header class="mb-4">
			<div class="header-meta text-muted mb-5 d-flex">
				<?php if ( ! is_singular( 'page' ) ) { ?>
					<?php tainacan_meta_date_author(); ?>
				<?php } ?>
				<div class="btn-group ml-auto" role="group">
					<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
						<a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" class="" target="_blank">
							<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/facebook-circle.png'; ?>" alt="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>">
						</a>
					<?php endif; ?>
					<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) ) : ?> 
						<?php $twitter_option = get_theme_mod( 'tainacan_twitter_user' ); ?>
						<?php $via = ! empty( $twitter_option ) ? '&amp;via=' . esc_attr( get_theme_mod( 'tainacan_twitter_user' ) ) : ''; ?>
						<a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?><?php echo $via; ?>" target="_blank" class="">
							<img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/twitter-circle.png'; ?>" alt="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>">
						</a>
					<?php endif; ?>
				</div>
			</div>
			<?php the_post_thumbnail(); ?>
		</header>
	<?php endif; ?>
	<section class="tainacan-content text-black margin-two-column">
		<?php
			the_content();
		?>
		<div class="row margin-one-column">
			<?php // Previous/next post navigation.
				wp_link_pages(
					array(
						'before' => '<div class="page-link"><span>' . __( 'Pages:', 'tainacan-interface' ) . '</span>',
						'after'  => '</div>',
					)
				); ?>
		</div>
	</section>
	<?php if ( ! is_singular( 'page' ) ) { ?>
	<footer class="mt-5 border-top pt-3">
		<p>
			<?php _e( 'Category: ', 'tainacan-interface' );
			the_category( ' <span>|</span> ' ) ?> -
			<?php if ( has_tag() ) {
				the_tags( 'Tags: ', ' <span>|</span> ' ); ?> -
			<?php }
			_e( 'Comments: ', 'tainacan-interface' ); ?>
			<?php comments_popup_link( __( 'None', 'tainacan-interface' ), '1', '%' ); ?>
		</p>
	</footer>
	<?php } ?>
</article>
<div class="row">
	<!-- Container -->
	<div class="col mt-3 mx-auto">
		<?php
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif; ?>
	</div>
</div>
