<?php
	$is_slideshow_available = false;
	if (function_exists('tainacan_is_view_mode_enabled'))
		$is_slideshow_available = tainacan_is_view_mode_enabled('slideshow');

	function get_item_link_for_navigation($item_url, $index) {
		if ( $_GET && isset($_GET['paged']) && isset($_GET['perpage']) ) {
			$query = '';
			$perpage = (int)$_GET['perpage'];
			$paged = (int)$_GET['paged'];
			$index = (int)$index;
			$query .= '&pos=' . ( ($paged - 1) * $perpage + $index );
			$query .= '&source_list=' . (is_tax() ? 'term' : ((!isset($collection_id) || empty($collection_id)) ? 'repository': 'collection') );
			return $item_url . '?' .  $_SERVER['QUERY_STRING'] . $query;
		}
		return $item_url;
	}
?> 

<?php if ( have_posts() ) : ?>
	<div class="tainacan-grid-container">

		<?php $item_index = 0; while ( have_posts() ) : the_post(); ?>
			<div class="tainacan-grid-item">

				<div class="metadata-title">     
					<p><a href="<?php echo get_item_link_for_navigation(get_permalink(), $item_index); ?>"><?php the_title(); ?></a></p>
					<?php if ( $is_slideshow_available ) : ?>
						<a href="<?php echo esc_url('?' . $_SERVER['QUERY_STRING'] . '&slideshow-from=' . $item_index ); ?>" class="icon slideshow-icon">
							<i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"></i>
						</a>
					<?php endif; ?> 
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<a 
							href="<?php echo get_item_link_for_navigation(get_permalink(), $item_index); ?>"
							style="background-image: url(<?php the_post_thumbnail_url( 'tainacan-medium' ) ?>)"
							class="grid-item-thumbnail">
						<?php the_post_thumbnail( 'tainacan-medium' ); ?>
						<div class="skeleton"></div> 
					</a>
				<?php else : ?>
					<a 
							href="<?php echo get_item_link_for_navigation(get_permalink(), $item_index); ?>"
							style="background-image: url(<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/thumbnail_placeholder.png'?>)"
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
