<?php
/**
 * Template for a single Tainacan item.
 *
 * @package tainacan-interface
 */

get_header();
?>

<main class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<?php if ( have_posts() ) : ?>

				<?php do_action( 'tainacan-interface-single-item-top' ); ?>

				<?php
				while ( have_posts() ) :
					the_post();

					// A password protected item shows the form instead of its metadata and files.
					if ( post_password_required() ) :
						// tnc-check-ignore TNC-S05: markup built by the core, which escapes each value.
						echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup built by the core, which escapes each value; there is no safe variant of it.
						continue;
					endif;
					?>
					<article role="article" id="post_<?php the_ID(); ?>" <?php post_class(); ?>>
						
						<?php
							get_template_part( 'template-parts/single-items-header' );
							do_action( 'tainacan-interface-single-item-after-title' );

							echo '<div class="single-item-data-section">';

						switch ( get_theme_mod( 'tainacan_single_item_layout_sections_order', 'document-attachments-metadata' ) ) {
							case 'document-attachments-metadata':
								get_template_part( 'template-parts/single-items-document' );
								do_action( 'tainacan-interface-single-item-after-document' );

								get_template_part( 'template-parts/single-items-attachments' );
								do_action( 'tainacan-interface-single-item-after-attachments' );

								tainacan_interface_the_item_metadata();
								break;

							case 'metadata-document-attachments':
								tainacan_interface_the_item_metadata();

								get_template_part( 'template-parts/single-items-document' );
								do_action( 'tainacan-interface-single-item-after-document' );

								get_template_part( 'template-parts/single-items-attachments' );
								do_action( 'tainacan-interface-single-item-after-attachments' );
								break;

							case 'document-metadata-attachments':
								get_template_part( 'template-parts/single-items-document' );
								do_action( 'tainacan-interface-single-item-after-document' );

								tainacan_interface_the_item_metadata();

								get_template_part( 'template-parts/single-items-attachments' );
								do_action( 'tainacan-interface-single-item-after-attachments' );
								break;

						}

							echo '</div>';
						?>

						<?php
						get_template_part( 'template-parts/single-items-related-items' );
								do_action( 'tainacan-interface-single-item-after-reated-items' );
						?>

						<?php get_template_part( 'template-parts/single-items-navigation' ); ?>

						<?php get_template_part( 'template-parts/single-items-comments' ); ?>

					</article>
				<?php endwhile; ?>

				<?php do_action( 'tainacan-interface-single-item-bottom' ); ?>

			<?php else : ?>
				<?php esc_html_e( 'Nothing found', 'tainacan-interface' ); ?>
			<?php endif; ?>
		</div>
	</div><!-- /.row -->
</main>

<?php get_footer(); ?>
