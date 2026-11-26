<?php
/**
 * Template for the terms listing of a Tainacan taxonomy.
 *
 * @package tainacan-interface
 */

get_header();
?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

<main class="mt-5 max-large margin-one-column">
	<div class="row">
		<div class="col col-sm mx-sm-auto">
			<?php
			get_template_part(
				'template-parts/page-title',
				null,
				array(
					'kicker'      => _x( 'Taxonomy', 'kind of page, above its title', 'tainacan-interface' ),
					'title'       => get_the_title(),
					'description' => wp_strip_all_tags( (string) get_post_field( 'post_content', get_the_ID() ) ),
				)
			);
			?>
			<?php get_template_part( 'template-parts/terms-explorer' ); ?>
		</div>
	</div>
</main>

<?php get_footer(); ?>