<?php
	$is_slideshow_available = false;
	if (function_exists('tainacan_is_view_mode_enabled'))
		$is_slideshow_available = tainacan_is_view_mode_enabled('slideshow');

	// Where the list comes from, as the Vue view modes send it: the item page keeps
	// "previous/next" inside the collection (and lets its editors navigate drafts)
	// only when source_list is "collection" with its id. Tainacan includes this
	// template inside the REST items route, in the scope of its get_items(): the
	// query has post_type as an array there, so tainacan_get_collection_id() is
	// false and the collection comes from the request. A term list keeps "term"
	// without an id, as before: with an id Tainacan would replace the whole
	// taxonomy filter of the list by that one term.
	$tainacan_interface_source_id = 0;
	if ( isset( $request ) && $request instanceof WP_REST_Request && ! empty( $request['collection_id'] ) ) {
		$tainacan_interface_source_id = absint( $request['collection_id'] );
	} elseif ( function_exists( 'tainacan_get_collection_id' ) && tainacan_get_collection_id() ) {
		$tainacan_interface_source_id = (int) tainacan_get_collection_id();
	}

	if ( ! function_exists( 'get_item_link_for_navigation' ) ) :
	function get_item_link_for_navigation($item_url, $index, $source_id = 0) {
		// Check if query parameters should be included based on the setting
		$enable_query_params = true; // Default to true to maintain current behavior
		if (class_exists('\Tainacan\Theme_Helper')) {
			$theme_helper = \Tainacan\Theme_Helper::get_instance();
			if (method_exists($theme_helper, 'get_enable_item_link_query_params')) {
				$enable_query_params = $theme_helper->get_enable_item_link_query_params();
			}
		}
		
		// Early return if query params are disabled
		if (!$enable_query_params) {
			return $item_url;
		}
		
		// Early return if we don't have the necessary pagination parameters
		if (!$_GET || !isset($_GET['paged']) || !isset($_GET['perpage'])) {
			return $item_url;
		}
		
		// Build query parameters for navigation
		$perpage = max(1, absint($_GET['perpage']));
		$paged = max(1, absint($_GET['paged']));
		$index = (int)$index;
		
		$query = '&pos=' . ( ($paged - 1) * $perpage + $index );
		$source_id = absint( $source_id );
		if ( $source_id > 0 ) {
			$query .= '&source_list=collection&source_entity_id=' . $source_id;
		} else {
			$query .= '&source_list=' . ( is_tax() ? 'term' : 'repository' );
		}

		return $item_url . '?' . $_SERVER['QUERY_STRING'] . $query;
	}
	endif;
?> 

<?php if ( have_posts() ) : ?>
	<div class="tainacan-grid-container">

		<?php $item_index = 0; while ( have_posts() ) : the_post(); ?>
			<div class="tainacan-grid-item">

				<div class="metadata-title">     
					<p><a href="<?php echo esc_url( get_item_link_for_navigation(get_permalink(), $item_index, $tainacan_interface_source_id) ); ?>"><?php the_title(); ?></a></p>
					<?php if ( $is_slideshow_available ) : ?>
						<a href="<?php echo esc_url('?' . $_SERVER['QUERY_STRING'] . '&slideshow-from=' . $item_index ); ?>" class="icon slideshow-icon">
							<i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"></i>
						</a>
					<?php endif; ?> 
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<a 
							href="<?php echo esc_url( get_item_link_for_navigation(get_permalink(), $item_index, $tainacan_interface_source_id) ); ?>"
							style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_the_post_thumbnail_url( null, 'tainacan-medium' ) ) ); ?>')"
							class="grid-item-thumbnail">
						<?php the_post_thumbnail( 'tainacan-medium' ); ?>
						<div class="skeleton"></div> 
					</a>
				<?php else : ?>
					<a 
							href="<?php echo esc_url( get_item_link_for_navigation(get_permalink(), $item_index, $tainacan_interface_source_id) ); ?>"
							style="background-image: url('<?php echo esc_url( tainacan_interface_css_url( get_template_directory_uri() . '/assets/images/thumbnail_placeholder.png' ) ); ?>')"
							class="grid-item-thumbnail">
						<?php echo '<img alt="', esc_attr_e('Thumbnail placeholder', 'tainacan-interface'), '" src="', esc_url(get_template_directory_uri()), '/assets/images/thumbnail_placeholder.png">'?>
						<div class="skeleton"></div> 
					</a>
				<?php endif; ?>  
			</div>	
		
		<?php $item_index++; endwhile; ?>
	
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
				<p><?php echo __( 'No item was found.','tainacan-interface' ); ?></p>
			</div>
		</section>
	</div>
<?php endif; ?>
