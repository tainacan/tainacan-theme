<?php
/**
 * Article markup for a single post or page.
 *
 * @package tainacan-interface
 */

?>
<article role="article" id="post_<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( ! is_page_template( 'page-templates/landing.php' ) && ! is_page_template( 'page-templates/landing-breadcrumb.php' ) ) : ?>
		<header class="mb-4 tainacan-content">
			<div class="header-meta text-muted mb-5 d-flex">
				<?php if ( ! is_singular( 'page' ) && tainacan_interface_post_shows( 'meta' ) ) { ?>
					<?php tainacan_meta_date_author(); ?>
				<?php } ?>
				<?php if ( tainacan_interface_post_shows( 'share' ) ) : ?>
					<div class="btn-group ml-auto" role="group">
						<?php tainacan_interface_the_share_links( get_permalink(), get_the_title(), 'images', 'share-link' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
			if ( ! (bool) get_theme_mod( 'tainacan_featured_image_on_header_banner', false ) && tainacan_interface_post_shows( 'featured' ) ) {
				the_post_thumbnail();
			}
			?>
		</header>
	<?php endif; ?>
	<section class="tainacan-content margin-two-column">
		<?php
			the_content();
		?>
		<div class="row margin-one-column">
			<?php
			// Previous/next post navigation.
				wp_link_pages(
					array(
						'before' => '<div class="page-link"><span>' . esc_html__( 'Pages:', 'tainacan-interface' ) . '</span>',
						'after'  => '</div>',
					)
				);
				?>
		</div>
	</section>
	<?php if ( ! is_singular( 'page' ) && tainacan_interface_post_shows( 'terms' ) ) { ?>
	<footer class="mt-5 border-top pt-3">
		<p>
			<?php
			esc_html_e( 'Category: ', 'tainacan-interface' );
			the_category( ' <span>|</span> ' )
			?>
			-
			<?php
			if ( has_tag() ) {
				the_tags( 'Tags: ', ' <span>|</span> ' );
				?>
				-
				<?php
			}
			esc_html_e( 'Comments: ', 'tainacan-interface' );
			?>
			<?php comments_popup_link( __( 'None', 'tainacan-interface' ), '1', '%' ); ?>
		</p>
	</footer>
	<?php } ?>
</article>
<?php if ( comments_open() || get_comments_number() ) : ?>
	<div class="row">
		<!-- Container -->
		<div class="col mt-3 mx-auto">
			<?php comments_template(); ?>
		</div>
	</div>
<?php endif; ?>
