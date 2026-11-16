<?php
/**
 * Design of version 4: the list of collections. Every collection is on the page
 * (the query has no page limit), so the field filters the cards in the browser.
 *
 * @package tainacan-interface
 */

$tainacan_interface_title = tainacan_interface_opt( 'list_heading' );
$tainacan_interface_intro = tainacan_interface_opt( 'list_intro' );
$tainacan_interface_style = tainacan_interface_opt( 'list_style' );
$tainacan_interface_image = tainacan_interface_opt( 'list_image' );
$tainacan_interface_cards = array();
$tainacan_interface_count = tainacan_interface_opt( 'list_count' ) || 'items' === tainacan_interface_opt( 'list_order' );

while ( have_posts() ) {
	the_post();
	$tainacan_interface_cards[] = array(
		'id'    => get_the_ID(),
		'count' => $tainacan_interface_count ? tainacan_interface_collection_count( get_the_ID() ) : 0,
	);
}
rewind_posts();
update_post_thumbnail_cache();

if ( 'items' === tainacan_interface_opt( 'list_order' ) ) {
	usort(
		$tainacan_interface_cards,
		function ( $a, $b ) {
			return $b['count'] - $a['count'];
		}
	);
}
?>
<main role="main" class="tt2-collections-page">
	<header class="tt2-collections-page__head">
		<div>
			<h1 class="tt2-collections-page__title"><?php echo esc_html( '' !== $tainacan_interface_title ? $tainacan_interface_title : __( 'Collections', 'tainacan-interface' ) ); ?></h1>
			<?php if ( '' !== $tainacan_interface_intro ) : ?>
				<p class="tt2-collections-page__intro"><?php echo esc_html( $tainacan_interface_intro ); ?></p>
			<?php endif; ?>
				<?php do_action( 'tainacan-interface-banner-header-description' ); ?>
		</div>
		<?php if ( tainacan_interface_opt( 'list_search' ) && count( $tainacan_interface_cards ) >= 7 ) : ?>
			<label class="tt2-collections-page__search">
				<span class="screen-reader-text"><?php esc_html_e( 'Find a collection', 'tainacan-interface' ); ?></span>
				<input type="search" data-tt2-collections-filter placeholder="<?php esc_attr_e( 'Find a collection', 'tainacan-interface' ); ?>">
			</label>
		<?php endif; ?>
	</header>

	<?php tainacan_interface_the_collections_menu(); ?>

	<?php if ( empty( $tainacan_interface_cards ) ) : ?>
		<p><?php esc_html_e( 'Nothing found', 'tainacan-interface' ); ?></p>
	<?php else : ?>
		<ul class="tt2-collections tt2-collections--<?php echo esc_attr( $tainacan_interface_style ); ?>">
			<?php
			foreach ( $tainacan_interface_cards as $tainacan_interface_card ) :
				$tainacan_interface_id      = (int) $tainacan_interface_card['id'];
				$tainacan_interface_name    = tainacan_interface_plain( get_the_title( $tainacan_interface_id ) );
				$tainacan_interface_figure  = '';
				$tainacan_interface_summary = tainacan_interface_opt( 'list_description' ) ? wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $tainacan_interface_id ) ), tainacan_interface_opt( 'list_words' ) ) : '';

				if ( 'mosaic' !== $tainacan_interface_image && has_post_thumbnail( $tainacan_interface_id ) ) {
					$tainacan_interface_figure = get_the_post_thumbnail(
						$tainacan_interface_id,
						'medium_large',
						array(
							'alt'     => '',
							'loading' => 'lazy',
							'sizes'   => '(max-width: 600px) 100vw, 400px',
						)
					);
				}
				if ( ! $tainacan_interface_figure && 'thumbnail' !== $tainacan_interface_image ) {
					$tainacan_interface_figure = tainacan_interface_mosaic_html( tainacan_interface_collection_images( $tainacan_interface_id, 4 ), 'tt2-collection__mosaic' );
				}
				?>
				<li class="tt2-collection" data-tt2-collection-name="<?php echo esc_attr( $tainacan_interface_name ); ?>">
					<a class="tt2-collection__link" href="<?php echo esc_url( get_permalink( $tainacan_interface_id ) ); ?>">
						<span class="tt2-collection__image">
							<?php if ( $tainacan_interface_figure ) : ?>
								<?php echo wp_kses( $tainacan_interface_figure, tainacan_interface_media_tags() ); ?>
							<?php else : ?>
								<?php echo wp_kses( tainacan_interface_art_html( $tainacan_interface_name ), tainacan_interface_media_tags() ); ?>
							<?php endif; ?>
						</span>
						<span class="tt2-collection__text">
							<span class="tt2-collection__name"><?php echo esc_html( $tainacan_interface_name ); ?></span>
							<?php if ( '' !== $tainacan_interface_summary ) : ?>
								<span class="tt2-collection__description"><?php echo esc_html( $tainacan_interface_summary ); ?></span>
							<?php endif; ?>
							<?php if ( tainacan_interface_opt( 'list_count' ) ) : ?>
								<span class="tt2-collection__count">
									<?php
									$tainacan_interface_items = (int) $tainacan_interface_card['count'];
									echo esc_html(
										sprintf(
											/* translators: %s: number of items. */
											_n( '%s item', '%s items', $tainacan_interface_items, 'tainacan-interface' ),
											number_format_i18n( $tainacan_interface_items )
										)
									);
									?>
								</span>
							<?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="tt2-collections-page__none" data-tt2-collections-none hidden></p>
	<?php endif; ?>
</main>
