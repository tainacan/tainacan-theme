<?php
/**
 * "Thumbnail" view mode of the Tainacan lists, registered by the theme.
 *
 * Rendered inside the Tainacan REST request that fetches the list, so the
 * query string is the list's own (filters, search, page). The links carry the
 * list position for the item page's previous/next, built with add_query_arg()
 * from the sanitized arguments; the original glued $_SERVER['QUERY_STRING']
 * into the href as it came.
 *
 * @package tainacan-interface
 */

$tainacan_interface_slideshow = function_exists( 'tainacan_is_view_mode_enabled' ) && tainacan_is_view_mode_enabled( 'slideshow' );
$tainacan_interface_list_args = tainacan_interface_list_query_args();
$tainacan_interface_paged     = max( 1, absint( tainacan_interface_query_arg( 'paged', '1' ) ) );
$tainacan_interface_perpage   = absint( tainacan_interface_query_arg( 'perpage', '0' ) );
$tainacan_interface_with_pos  = $tainacan_interface_perpage > 0 && '' !== tainacan_interface_query_arg( 'paged' );
// Where the list comes from, as the Vue view modes send it: the item page only keeps
// "previous/next" inside the collection when source_list is "collection" with its id.
// Tainacan includes this template inside the REST items route, in the scope of its
// get_items(): the query has post_type as an array there, so is_post_type_archive()
// and tainacan_get_collection_id() are false; the collection comes from the request.
// A term list keeps "term" without an id, as 2.x sent: with an id Tainacan would
// replace the whole taxonomy filter of the list by that one term.
$tainacan_interface_source_id = 0;
if ( isset( $request ) && $request instanceof WP_REST_Request && ! empty( $request['collection_id'] ) ) {
	$tainacan_interface_source_id = absint( $request['collection_id'] );
} elseif ( tainacan_get_collection_id() ) {
	$tainacan_interface_source_id = (int) tainacan_get_collection_id();
}
$tainacan_interface_source = $tainacan_interface_source_id > 0 ? 'collection' : ( is_tax() ? 'term' : 'repository' );
if ( 'collection' === $tainacan_interface_source ) {
	$tainacan_interface_list_args['source_entity_id'] = $tainacan_interface_source_id;
}

if ( class_exists( '\Tainacan\Theme_Helper' ) && method_exists( '\Tainacan\Theme_Helper', 'get_enable_item_link_query_params' ) ) {
	$tainacan_interface_with_pos = $tainacan_interface_with_pos && \Tainacan\Theme_Helper::get_instance()->get_enable_item_link_query_params();
}

if ( have_posts() ) :
	?>
	<div class="tainacan-grid-container">

		<?php
		$tainacan_interface_index = 0;
		while ( have_posts() ) :
			the_post();
			$tainacan_interface_link = get_permalink();
			if ( $tainacan_interface_with_pos ) {
				$tainacan_interface_link = add_query_arg(
					urlencode_deep(
						array_merge(
							$tainacan_interface_list_args,
							array(
								'pos'         => ( $tainacan_interface_paged - 1 ) * $tainacan_interface_perpage + $tainacan_interface_index,
								'source_list' => $tainacan_interface_source,
							)
						)
					),
					$tainacan_interface_link
				);
			}
			?>
			<div class="tainacan-grid-item" data-tainacan-item-id="<?php the_ID(); ?>">

				<div class="metadata-title">
					<p><a href="<?php echo esc_url( $tainacan_interface_link ); ?>"><?php the_title(); ?></a></p>
					<?php if ( $tainacan_interface_slideshow ) : ?>
						<a href="<?php echo esc_url( add_query_arg( urlencode_deep( array_merge( $tainacan_interface_list_args, array( 'slideshow-from' => $tainacan_interface_index ) ) ), '' ) ); ?>" class="icon slideshow-icon" aria-label="<?php esc_attr_e( 'Slideshow', 'tainacan-interface' ); ?>">
							<i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"></i>
						</a>
					<?php endif; ?>
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php echo esc_url( $tainacan_interface_link ); ?>" style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_the_post_thumbnail_url( null, 'tainacan-medium' ) ) ); ?>')" class="grid-item-thumbnail">
						<?php the_post_thumbnail( 'tainacan-medium' ); ?>
						<div class="skeleton"></div>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $tainacan_interface_link ); ?>" style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png' ) ); ?>')" class="grid-item-thumbnail">
						<img alt="<?php esc_attr_e( 'Thumbnail placeholder', 'tainacan-interface' ); ?>" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png' ); ?>">
						<div class="skeleton"></div>
					</a>
				<?php endif; ?>
			</div>
			<?php
			++$tainacan_interface_index;
		endwhile;
		?>

	</div>

<?php else : ?>
	<div class="tainacan-grid-container">
		<section class="section">
			<div class="content has-text-gray4 has-text-centered">
				<p>
					<span class="icon is-large">
						<i class="tainacan-icon tainacan-icon-48px tainacan-icon-items"></i>
					</span>
				</p>
				<p><?php esc_html_e( 'No item was found.', 'tainacan-interface' ); ?></p>
			</div>
		</section>
	</div>
<?php endif; ?>
