<?php
/**
 * Archive helpers: view modes of the collections and terms lists, sorting,
 * pagination.
 *
 * @package tainacan-interface
 */

/**
 * View mode of the collections or terms list, from the closed list. The value
 * comes from the URL and goes to get_template_part().
 *
 * @param string $query_var tainacan_collections_viewmode or tainacan_terms_viewmode.
 * @return string cards|grid|table
 */
function tainacan_interface_list_view_mode( $query_var ) {
	$mode = get_query_var( $query_var, 'cards' );
	return in_array( $mode, array( 'cards', 'grid', 'table' ), true ) ? $mode : 'cards';
}

/**
 * Normalizes the public query vars of the Tainacan terms list before anyone
 * reads them. ?termspaged=abc reached arithmetic in the theme and in the
 * plugin and ended in a TypeError (HTTP 500) on PHP 8.
 *
 * @param array $query_vars Parsed query vars.
 * @return array
 */
function tainacan_interface_sanitize_terms_query_vars( $query_vars ) {
	if ( isset( $query_vars['termspaged'] ) ) {
		$query_vars['termspaged'] = max( 1, absint( $query_vars['termspaged'] ) );
	}
	if ( isset( $query_vars['perpage'] ) ) {
		$query_vars['perpage'] = min( 96, max( 1, absint( $query_vars['perpage'] ) ) );
	}
	if ( isset( $query_vars['termsparent'] ) ) {
		$query_vars['termsparent'] = absint( $query_vars['termsparent'] );
	}
	if ( isset( $query_vars['search'] ) && is_string( $query_vars['search'] ) ) {
		$query_vars['search'] = sanitize_text_field( wp_unslash( $query_vars['search'] ) );
	}
	return $query_vars;
}
add_filter( 'request', 'tainacan_interface_sanitize_terms_query_vars' );

/**
 * Registers the view mode and collection taxonomy query vars.
 *
 * @param string[] $public_query_vars Public query vars.
 * @return string[]
 */
function tainacan_interface_extra_viewmodes( $public_query_vars ) {
	$public_query_vars[]   = 'tainacan_collections_viewmode';
	$public_query_vars[]   = 'tainacan_terms_viewmode';
	$collection_taxonomies = get_object_taxonomies( 'tainacan-collection', 'names' );
	foreach ( $collection_taxonomies as $taxonomy ) {
		$public_query_vars[] = $taxonomy;
	}
	return $public_query_vars;
}
add_filter( 'query_vars', 'tainacan_interface_extra_viewmodes' );

/**
 * Returns (and by default prints) the 'active' CSS class when two values match.
 *
 * @param mixed $selected Value currently selected.
 * @param mixed $current  Value of the option being rendered.
 * @param bool  $echo     Whether to print the class.
 * @return string 'active' or ''.
 */
function tainacan_active( $selected, $current = true, $echo = true ) {
	// Booleans keep the truthiness comparison of the original; anything else is compared as text.
	if ( is_bool( $selected ) || is_bool( $current ) ) {
		$tainacan_interface_matches = (bool) $selected === (bool) $current;
	} else {
		$tainacan_interface_matches = (string) $selected === (string) $current;
	}

	$return = $tainacan_interface_matches ? 'active' : '';

	if ( $echo ) {
		echo esc_attr( $return );
	}

	return $return;
}

/**
 * Archive title of the collections archive.
 *
 * @param string $title Archive title.
 * @return string
 */
function tainacan_theme_collection_title( $title ) {
	if ( is_post_type_archive( 'tainacan-collection' ) ) {
		return __( 'Collections', 'tainacan-interface' );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'tainacan_theme_collection_title' );

/**
 * Archive title of the taxonomies archive.
 *
 * @param string $title Archive title.
 * @return string
 */
function tainacan_theme_taxonomy_title( $title ) {
	if ( is_post_type_archive( 'tainacan-taxononomy' ) ) {
		return __( 'Taxonomies', 'tainacan-interface' );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'tainacan_theme_taxonomy_title' );

/**
 * Sets the page size and the taxonomy filter of the collections archive query.
 *
 * @param WP_Query $query The query being prepared.
 */
function tainacan_theme_collection_query( $query ) {
	if ( $query->is_main_query() && $query->is_post_type_archive( 'tainacan-collection' ) ) {
		$query->set( 'posts_per_page', 12 );

		// The design of version 4 lists every collection, in the chosen order.
		if ( tainacan_interface_is_modern() ) {
			$query->set( 'posts_per_page', -1 );
			$query->set( 'no_found_rows', true );
			$query->set( 'update_post_term_cache', false );
			switch ( tainacan_interface_opt( 'list_order' ) ) {
				case 'title':
					$query->set( 'orderby', 'title' );
					$query->set( 'order', 'ASC' );
					break;
				case 'date':
					$query->set( 'orderby', 'date' );
					$query->set( 'order', 'DESC' );
					break;
				default:
					$query->set(
						'orderby',
						array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
						)
					);
			}
		}

		// Handle taxonomy filtering using WordPress standard approach.
		$tax_query             = array();
		$collection_taxonomies = get_object_taxonomies( 'tainacan-collection', 'names' );

		foreach ( $collection_taxonomies as $taxonomy ) {
			if ( '' !== tainacan_interface_query_arg( $taxonomy ) ) {
				$term_slug = sanitize_title( tainacan_interface_query_arg( $taxonomy ) );
				if ( ! empty( $term_slug ) ) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'operator' => 'IN',
						'terms'    => array( $term_slug ),
					);
				}
			}
		}

		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$query->set( 'tax_query', $tax_query );
			$query->set( 'post_type', 'tainacan-collection' ); // Ensure post type is set.
		}
	}
}
add_action( 'pre_get_posts', 'tainacan_theme_collection_query' );

if ( ! function_exists( 'tainacan_pagination' ) ) :
	/**
	 * Displays pagination using Tainacan Interface style.
	 */
	function tainacan_pagination() {
		global $wp_query;
		$cur_posts = min( (int) $wp_query->get( 'posts_per_page' ), $wp_query->found_posts );
		$to_paged  = max( (int) $wp_query->get( 'paged' ), 1 );
		$count_max = ( $to_paged - 1 ) * $cur_posts; ?>
		<div class="d-flex margin-pagination justify-content-between border-top pt-2">
			<div class="col-sm-3 d-none d-lg-block pl-0 view-items">
				<?php // translators: Example - Viewing results: 1 to 12 of 345. ?>
				<?php
				/* translators: 1: first result shown, 2: last result shown, 3: number of results. */
				printf( esc_html__( 'Viewing results: %1$d to %2$d of %3$d', 'tainacan-interface' ), (int) $count_max + 1, (int) $count_max + (int) $wp_query->post_count, (int) $wp_query->found_posts );
				?>
			</div>
			<div class="col-sm-5 pr-md-0 justify-content-md-end">
				<?php
				the_posts_pagination(
					array(
						'mid_size'           => 2,
						'prev_text'          => sprintf(
							'%s',
							'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-1-25em"></i>'
						),
						'next_text'          => sprintf(
							' %s',
							'<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em"></i>'
						),
						'screen_reader_text' => ' ',
					)
				);
				?>
			</div>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'tainacan_pagination_terms' ) ) :
	/**
	 * Displays pagination for terms list using Tainacan Interface style.
	 *
	 * @param int $total_terms Total of terms in the listing.
	 */
	function tainacan_pagination_terms( $total_terms ) {
		$current_args             = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();
		$current_total_terms      = $total_terms;
		$current_first_term_index = max( ( absint( $current_args['termspaged'] ) - 1 ) * absint( $current_args['perpage'] ) + 1, 1 );
		$current_last_term_index  = min( absint( $current_args['termspaged'] ) * absint( $current_args['perpage'] ), (int) $current_total_terms );
		?>
		<div class="d-flex margin-pagination justify-content-between border-top pt-2">
			<div class="col-sm-3 d-none d-lg-block pl-0 view-items">
				<?php // translators: Example - Viewing results: 1 to 12 of 345. ?>
				<?php
				/* translators: 1: first result shown, 2: last result shown, 3: number of results. */
				printf( esc_html__( 'Viewing results: %1$d to %2$d of %3$d', 'tainacan-interface' ), (int) $current_first_term_index, (int) $current_last_term_index, (int) $current_total_terms );
				?>
			</div>
			<div class="col-sm-5 pr-md-0 justify-content-md-end">
				<?php
					tainacan_the_taxonomies_pagination(
						$current_total_terms,
						array(
							'paginate_links_extra_args' => array(
								'mid_size'           => 2,
								'prev_text'          => sprintf(
									'%s',
									'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-1-25em"></i>'
								),
								'next_text'          => sprintf(
									' %s',
									'<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em"></i>'
								),
								'screen_reader_text' => ' ',
							),
						)
					);
				?>
			</div>
		</div>
		<?php
	}
endif;