<?php get_header(); ?>

<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menubellowbanner' ); ?>

<main class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<?php if ( have_posts() ) : ?>
				<?php do_action( 'tainacan-interface-single-item-top' ); ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<div class="tainacan-title">
						<div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
							<ul class="list-inline mb-1">
								<li class="list-inline-item text-midnight-blue font-weight-bold title-page">
									<?php the_title(); ?> 
								</li>
								<li class="list-inline-item float-right title-back">
									<a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a>
								</li>
							</ul>
						</div>
					</div>
					
					<?php do_action( 'tainacan-interface-single-item-after-title' ); ?>
					
					<div class="mt-3 tainacan-single-post collection-single-item">
						<article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
							<header class="mb-4">
								<div class="header-meta text-muted mb-5">
									<span class="time"><?php tainacan_meta_date_author(); ?></span>
									<?php if(function_exists('tainacan_the_item_edit_link')) {
										echo '<span class="tainacan-edit-item-collection">';
											tainacan_the_item_edit_link(null, ' - ');
										echo '</span>';
									} ?>
								</div>
							</header>
							<?php if ( tainacan_has_document() ) : ?>
								<h1 class="title-content-items"><?php _e( 'Document', 'tainacan-interface' ); ?></h1>
								<section class="tainacan-content single-item-collection margin-two-column">
									<div class="single-item-collection--document">
										<?php tainacan_the_document(); ?>
									</div>
								</section>
							<?php endif; ?>
						</article>
					</div>

					<?php do_action( 'tainacan-interface-single-item-after-document' ); ?>

					<?php if ( tainacan_has_document() ) : ?>
						<div class="tainacan-title my-5">
							<div class="border-bottom border-silver tainacan-title-page" style="border-width: 1px !important;">
							</div>
						</div>
					<?php endif; ?>

					<?php
						$attachment = array_values(
							get_children(
								array(
									'post_parent' => $post->ID,
									'post_type' => 'attachment',
									'post_mime_type' => 'image',
									'order' => 'ASC',
									'numberposts'  => -1,
								)
							)
						);
					?>

					<?php if ( ! empty( $attachment ) ) : ?>

						<div class="mt-3 tainacan-single-post">
							<article role="article">
								<h1 class="title-content-items"><?php _e( 'Attachments', 'tainacan-interface' ); ?></h1>
								<section class="tainacan-content single-item-collection margin-two-column">
									<div class="single-item-collection--attachments">
										<?php foreach ( $attachment as $attachment ) { ?>
											<div class="single-item-collection--attachments-img">
												<a href="<?php echo $attachment->guid; ?>" data-toggle="lightbox" data-gallery="example-gallery">
													<?php
														echo wp_get_attachment_image( $attachment->ID, 'tainacan-interface-item-attachments' );
													?>
												</a>
											</div>
										<?php }
										?>
									</div>
								</section>
							</article>
						</div>

						<div class="tainacan-title my-5">
							<div class="border-bottom border-silver tainacan-title-page" style="border-width: 1px !important;">
							</div>
						</div>

					<?php endif; ?>

					<?php do_action( 'tainacan-interface-single-item-after-attachments' ); ?>

					<div class="mt-3 tainacan-single-post">
						<article role="article">
							<!-- <h1 class="title-content-items"><?php _e( 'Information', 'tainacan-interface' ); ?></h1> -->
							<section class="tainacan-content single-item-collection margin-two-column">
								<div class="single-item-collection--information justify-content-center">
									<div class="row">
										<div class="col s-item-collection--metadata">
											<div class="card border-0">
												<div class="card-body bg-white border-0 pl-0 pt-0 pb-1">
													<h3><?php _e( 'Thumbnail', 'tainacan-interface' ); ?></h3>
													<img src="<?php echo get_the_post_thumbnail_url( get_the_ID(), 'tainacan-medium-full' ) ?>" class="item-card--thumbnail mt-2">
												</div>
											</div>
											<div class="card border-0 my-3">
												<div class="card-body bg-white border-0 pl-0 pt-0 pb-1">
													<h3><?php _e( 'Share', 'tainacan-interface' ); ?></h3>
													<div class="btn-group" role="group">
														<?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
															<a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" class="item-card-link--sharing" target="_blank">
																<img src="<?php echo get_template_directory_uri() . '/assets/images/facebook-circle.png'; ?>" alt="">
															</a>
														<?php endif; ?>
														<?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) ) : ?> 
															<?php
															$twitter_option = get_option( 'tainacan_twitter_user' );
															$via = ! empty( $twitter_option ) ? '&amp;via=' . esc_attr( get_option( 'tainacan_twitter_user' ) ) : '';
															?>
															<a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?><?php echo $via; ?>" target="_blank" class="item-card-link--sharing">
																<img src="<?php echo get_template_directory_uri() . '/assets/images/twitter-circle.png'; ?>" alt="">
															</a>
														<?php endif; ?>
														<?php if ( true == get_theme_mod( 'tainacan_google_share', true ) ) : ?> 
															<a href="https://plus.google.com/share?url=<?php the_permalink(); ?>" target="_blank" class="item-card-link--sharing">
																<img src="<?php echo get_template_directory_uri() . '/assets/images/google-plus-circle.png'; ?>" alt="">
															</a>
														<?php endif; ?>
													</div>
												</div>
											</div>
											<?php do_action( 'tainacan-interface-single-item-metadata-begin' ); ?>
											<?php
												$args = array(
													'before_title' => '<div><h3>',
													'after_title' => '</h3>',
													'before_value' => '<p>',
													'after_value' => '</p></div>',
												);
												//$field = null;
												tainacan_the_metadata( $args );
											?>
											<?php do_action( 'tainacan-interface-single-item-metadata-end' ); ?>
										</div>
									</div>
								</div>
							</section>
						</article>
					</div>

					<?php do_action( 'tainacan-interface-single-item-after-metadata' ); ?>

					<div class="tainacan-title my-5">
						<div class="border-bottom border-silver tainacan-title-page" style="border-width: 1px !important;">
						</div>
					</div>
					<div class="mt-3 tainacan-single-post">
						<div class="row">
							<!-- Container -->
							<div class="col mt-3 mx-auto">
								<?php
								if ( comments_open() || get_comments_number() ) :
									comments_template();
								endif; ?>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
				<?php do_action( 'tainacan-interface-single-item-bottom' ); ?>
			<?php else : ?>
				<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
			<?php endif; ?>
		</div>
	</div><!-- /.row -->
</main>
<?php get_footer(); ?>
<script>
	jQuery('#topNavbar').addClass('b-bottom-top');
	jQuery('nav.menu-belowheader').removeClass('border-bottom');
	jQuery('nav.menu-belowheader .max-large').addClass('b-bottom-bellow');
</script>
