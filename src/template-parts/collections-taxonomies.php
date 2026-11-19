<?php
/**
 * Taxonomies of the site below the list of collections, each with a link to
 * its page of terms (option "Taxonomies below the collections").
 *
 * @package tainacan-interface
 */

if ( ! tainacan_interface_opt( 'list_taxonomies' ) ) {
	return;
}

$tainacan_interface_taxonomies = get_posts(
	array(
		'post_type'        => 'tainacan-taxonomy',
		'post_status'      => 'publish',
		'posts_per_page'   => 200,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	)
);
if ( empty( $tainacan_interface_taxonomies ) ) {
	return;
}

$tainacan_interface_tax_title = tainacan_interface_opt( 'list_taxonomies_heading' );
?>
<section class="tt2-taxonomies" aria-labelledby="tt2-taxonomies-title">
	<h2 id="tt2-taxonomies-title" class="tt2-taxonomies__title label"><?php echo esc_html( '' !== $tainacan_interface_tax_title ? $tainacan_interface_tax_title : __( 'Taxonomies', 'tainacan-interface' ) ); ?></h2>
	<ul class="tt2-taxonomies__list">
		<?php
		foreach ( $tainacan_interface_taxonomies as $tainacan_interface_tax ) :
			$tainacan_interface_terms = wp_count_terms(
				array(
					'taxonomy'   => 'tnc_tax_' . $tainacan_interface_tax->ID,
					'hide_empty' => false,
				)
			);
			$tainacan_interface_terms = is_wp_error( $tainacan_interface_terms ) ? 0 : (int) $tainacan_interface_terms;
			$tainacan_interface_desc  = wp_trim_words( wp_strip_all_tags( (string) $tainacan_interface_tax->post_content ), 18 );
			?>
			<li class="tt2-taxonomy">
				<a class="tt2-taxonomy__link" href="<?php echo esc_url( get_permalink( $tainacan_interface_tax ) ); ?>">
					<span class="tt2-taxonomy__icon" aria-hidden="true"></span>
					<span class="tt2-taxonomy__text">
						<span class="tt2-taxonomy__name"><?php echo esc_html( tainacan_interface_plain( get_the_title( $tainacan_interface_tax ) ) ); ?></span>
						<?php if ( '' !== $tainacan_interface_desc ) : ?>
							<span class="tt2-taxonomy__description"><?php echo esc_html( $tainacan_interface_desc ); ?></span>
						<?php endif; ?>
						<span class="tt2-taxonomy__count">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: number of terms. */
									_n( '%s term', '%s terms', $tainacan_interface_terms, 'tainacan-interface' ),
									number_format_i18n( $tainacan_interface_terms )
								)
							);
							?>
						</span>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
