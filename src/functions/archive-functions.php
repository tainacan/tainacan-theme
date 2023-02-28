<?php

function tainacan_interface_extra_viewmodes( $public_query_vars ) {
	$public_query_vars[] = 'tainacan_collections_viewmode';
	$public_query_vars[] = 'tainacan_terms_viewmode';
	return $public_query_vars;
}
add_filter( 'query_vars', 'tainacan_interface_extra_viewmodes' );

function tainacan_active( $selected, $current = true, $echo = true ) {
	$return = $selected == $current ? 'active' : '';

	if ( $echo ) {
		echo $return;
	}

	return $return;
}

function tainacan_theme_collection_title( $title ) {
	if ( is_post_type_archive( 'tainacan-collection' ) ) {
		return __( 'Collections', 'tainacan-interface' );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'tainacan_theme_collection_title' );

function tainacan_theme_collection_query( $query ) {
	if ( $query->is_main_query() && $query->is_post_type_archive( 'tainacan-collection' ) ) {
		$query->set( 'posts_per_page', 12 );
	}
}
add_action( 'pre_get_posts', 'tainacan_theme_collection_query' );

/**
 * Displays pagination using Tainacan Interface style.
 */
if ( ! function_exists( 'tainacan_pagination' ) ) :
	function tainacan_pagination() {
		global $wp_query;
		$cur_posts = min( (int) $wp_query->get( 'posts_per_page' ), $wp_query->found_posts );
		$to_paged = max( (int) $wp_query->get( 'paged' ), 1 );
		$count_max = ( $to_paged - 1 ) * $cur_posts; ?>
		<div class="d-flex margin-pagination justify-content-between border-top pt-2">
			<div class="col-sm-3 d-none d-lg-block pl-0 view-items">
				<?php //translators: Example - Viewing results: 1 to 12 of 345 ?>
				<?php printf( __('Viewing results: %1$d to %2$d of %3$d', 'tainacan-interface'), $count_max + 1, $count_max + $wp_query->post_count, $wp_query->found_posts ); ?>
			</div>
			<div class="col-sm-5 pr-md-0 justify-content-md-end">
				<?php the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => sprintf(
							'%s',
							'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-1-25em"></i>'
						),
						'next_text' => sprintf(
							' %s',
							'<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em"></i>'
						),
						'screen_reader_text' => ' '
					)
				); ?>
			</div>
		</div>
	<?php }
endif;

/**
 * Displays pagination for terms list using Tainacan Interface style.
 */
if ( ! function_exists( 'tainacan_pagination_terms' ) ) :
	function tainacan_pagination_terms($total_terms) {
		$current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();
        $current_total_terms = $total_terms;
        $current_first_term_index = max(($current_args['termspaged'] - 1) * $current_args['perpage'] + 1, 1);
        $current_last_term_index = min($current_args['termspaged'] * $current_args['perpage'], $current_total_terms);
        ?>
		<div class="d-flex margin-pagination justify-content-between border-top pt-2">
            <div class="col-sm-3 d-none d-lg-block pl-0 view-items">
                <?php //translators: Example - Viewing results: 1 to 12 of 345 ?>
                <?php printf( __('Viewing results: %1$d to %2$d of %3$d', 'tainacan-interface'), $current_first_term_index, $current_last_term_index, $current_total_terms ); ?>
            </div>
            <div class="col-sm-5 pr-md-0 justify-content-md-end">
                <?php
                    tainacan_the_taxonomies_pagination(
                        $current_total_terms,
                        array(
                            'paginate_links_extra_args' => array(
                                'mid_size'  => 2,
                                'prev_text' => sprintf(
                                    '%s',
                                    '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-1-25em"></i>'
                                ),
                                'next_text' => sprintf(
                                    ' %s',
                                    '<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em"></i>'
                                ),
                                'screen_reader_text' => ' '
                            )
                        )
                    );
                ?>
             </div>
        </div>
	<?php }
endif;