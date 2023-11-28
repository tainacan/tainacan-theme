<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php if (!is_page_template( 'page-templates/landing.php' ) && !is_page_template( 'page-templates/landing-breadcrumb.php' ) ) : ?>
			<div class="tainacan-title">
				<div class="tainacan-title-page">
					<ul class="list-inline mb-1">
						<li class="list-inline-item text-midnight-blue font-weight-bold title-page">
							<h1><?php the_title(); ?></h1>
						</li>
						<li class="list-inline-item float-right title-back"><a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a></li>
					</ul>
				</div>
			</div>
			<div class="mt-3 tainacan-single-post">
				<?php get_template_part( 'template-parts/single-post' ); ?>
			</div>
		<?php else: ?>
			<div class="mt-0 tainacan-single-post">
				<?php get_template_part( 'template-parts/single-post' ); ?>
			</div>
		<?php endif; ?>
		<?php if ( ! is_singular( 'page' ) ) : ?>
			<div class="row py-4 justify-content-between">
				<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">&larr;</span>&nbsp;' . __( 'Previous', 'tainacan-interface' ) ); ?></span>
				<span class="nav-next"><?php next_post_link( '%link', __( 'Next', 'tainacan-interface' ) . '&nbsp;<span class="meta-nav">&rarr;</span>' ); ?></span>
			</div>
		<?php endif; ?>
	<?php endwhile; ?>
<?php else : ?>
	<?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>
