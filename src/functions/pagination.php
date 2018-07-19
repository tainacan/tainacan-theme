<?php

if ( ! function_exists( 'pagination_bst4' ) ) {
	/**
	 * Tainacan pagination
	 * @param $col to number of grid collum
	 */
	function tainacan_pagination($col) {
 
		if( is_singular() )
			return;
	 
		global $wp_query;
	 
		/** Stop execution if there's only 1 page */
		if( $wp_query->max_num_pages <= 1 )
			return;
	 
		$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
		$max   = intval( $wp_query->max_num_pages );

		$cur_posts = min( ( int ) $wp_query->get( 'posts_per_page' ), $wp_query->found_posts );
		$to_paged = max( ( int ) $wp_query->get( 'paged' ), 1 );
		$count_max = ( $to_paged - 1 ) * $cur_posts;
		/** Add current page to the array */
		if ( $paged >= 1 )
			$links[] = $paged;
	 
		/** Add the pages around the current page to the array */
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}
	 
		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}
		
		echo '<div class="d-flex margin-pagination justify-content-between border-top pt-2">';
			printf('<div class="col-sm-3 d-none d-lg-block pl-0 view-items">Viewing Items: %d to %d from %d</div>', $count_max + 1, $count_max + $wp_query->post_count, $wp_query->found_posts);
			echo '<div class="col-sm-5 pr-md-0">';
				echo '<ul class="pagination justify-content-center justify-content-md-end">' . "\n";
	 
					/** Previous Post Link */
					if ( get_previous_posts_link() )
						printf( '<li style="padding-right: 5px">%s</li>' . "\n", get_previous_posts_link('<i class="mdi mdi-menu-left"></i>') );
				
					/** Link to first page, plus ellipses if necessary */
					if ( ! in_array( 1, $links ) ) {
						$class = 1 == $paged ? ' class="active"' : '';
				
						printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );
				
						if ( ! in_array( 2, $links ) )
							echo '<li>…</li>';
					}
				
					/** Link to current page, plus 2 pages in either direction if necessary */
					sort( $links );
					foreach ( (array) $links as $link ) {
						$class = $paged == $link ? ' class="active"' : '';
						printf( '<li%s><a href="%s" class="p-2">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
					}
				
					/** Link to last page, plus ellipses if necessary */
					if ( ! in_array( $max, $links ) ) {
						if ( ! in_array( $max - 1, $links ) )
							echo '<li>…</li>' . "\n";
				
						$class = $paged == $max ? ' class="active"' : '';
						printf( '<li%s><a href="%s" class="p-2">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
					}
				
					/** Next Post Link */
					if ( get_next_posts_link() )
						printf( '<li>%s</li>' . "\n", get_next_posts_link('<i class="mdi mdi-menu-right"></i>') );
		
				echo '</ul>';
			echo '</div>' . "\n";
		echo '</div>';
	}
}