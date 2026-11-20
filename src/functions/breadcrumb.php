<?php
/**
 * The breadcrumb.
 *
 * Every crumb is escaped where it is built: the header still passes the
 * result through wp_kses_post(), but the function is public API and child
 * themes print it elsewhere.
 *
 * @package tainacan-interface
 */

/**
 * A crumb that links somewhere.
 *
 * @param string $url   URL.
 * @param string $label Plain text label.
 * @return string
 */
function tainacan_interface_crumb_link( $url, $label ) {
	if ( ! $url ) {
		return esc_html( wp_strip_all_tags( $label ) );
	}
	return '<a href="' . esc_url( $url ) . '">' . esc_html( wp_strip_all_tags( $label ) ) . '</a>';
}

/**
 * The current crumb.
 *
 * @param string $label Plain text label.
 * @return string
 */
function tainacan_interface_crumb_current( $label ) {
	return '<span class="current text-black"> ' . esc_html( wp_strip_all_tags( $label ) ) . '</span>';
}

/**
 * The breadcrumb function. Display parent and current page.
 *
 * @return string Breadcrumb HTML (escaped).
 */
function tainacan_interface_the_breadcrumb() {
	if ( is_home() || is_front_page() ) {
		return '';
	}

	$sep    = '&nbsp;&gt;&nbsp;';
	$crumbs = array( tainacan_interface_crumb_link( home_url( '/' ), __( 'Home', 'tainacan-interface' ) ) );
	$post   = get_post();

	if ( is_category() ) {
		$category = get_category( get_query_var( 'cat' ), false );
		if ( $category instanceof WP_Term && $category->parent ) {
			foreach ( array_reverse( get_ancestors( $category->term_id, 'category' ) ) as $ancestor ) {
				$crumbs[] = tainacan_interface_crumb_link( get_category_link( $ancestor ), get_cat_name( $ancestor ) );
			}
		}
		/* translators: %s: category name. */
		$crumbs[] = tainacan_interface_crumb_current( sprintf( __( 'Archive by category "%s"', 'tainacan-interface' ), single_cat_title( '', false ) ) );

	} elseif ( is_search() ) {
		/* translators: %s: search terms. */
		$crumbs[] = tainacan_interface_crumb_current( sprintf( __( 'Search results for "%s"', 'tainacan-interface' ), get_search_query( false ) ) );

	} elseif ( is_day() ) {
		$crumbs[] = tainacan_interface_crumb_link( get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) );
		$crumbs[] = tainacan_interface_crumb_link( get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ), get_the_time( 'F' ) );
		$crumbs[] = tainacan_interface_crumb_current( get_the_time( 'd' ) );

	} elseif ( is_month() ) {
		$crumbs[] = tainacan_interface_crumb_link( get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) );
		$crumbs[] = tainacan_interface_crumb_current( get_the_time( 'F' ) );

	} elseif ( is_year() ) {
		$crumbs[] = tainacan_interface_crumb_current( get_the_time( 'Y' ) );

	} elseif ( is_single() && ! is_attachment() ) {
		$type = get_post_type();
		if ( 'post' !== $type ) {
			$type_object = get_post_type_object( $type );
			if ( 'tainacan-collection' !== $type && 'tainacan-taxonomy' !== $type ) {
				$crumbs[] = tainacan_interface_crumb_link( get_post_type_archive_link( 'tainacan-collection' ), __( 'Collections', 'tainacan-interface' ) );
			}
			if ( $type_object ) {
				$crumbs[] = tainacan_interface_crumb_link( get_post_type_archive_link( $type ), $type_object->labels->singular_name );
			}
		} else {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$category = $categories[0];
				foreach ( array_reverse( get_ancestors( $category->term_id, 'category' ) ) as $ancestor ) {
					$crumbs[] = tainacan_interface_crumb_link( get_category_link( $ancestor ), get_cat_name( $ancestor ) );
				}
				$crumbs[] = tainacan_interface_crumb_link( get_category_link( $category ), $category->name );
			}
		}
		$crumbs[] = tainacan_interface_crumb_current( get_the_title() );

	} elseif ( ! is_single() && ! is_page() && 'post' !== get_post_type() && ! is_404() ) {
		$type_object = get_post_type_object( get_post_type() );
		if ( is_tax() ) {
			$term     = get_queried_object();
			$taxonomy = $term instanceof WP_Term ? get_taxonomy( $term->taxonomy ) : null;
			if ( $taxonomy ) {
				$crumbs[] = esc_html( wp_strip_all_tags( $taxonomy->labels->name ) );
				foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor ) {
					$parent   = get_term( $ancestor, $term->taxonomy );
					$link     = $parent instanceof WP_Term ? get_term_link( $parent ) : '';
					$crumbs[] = $parent instanceof WP_Term ? tainacan_interface_crumb_link( is_wp_error( $link ) ? '' : $link, $parent->name ) : '';
				}
				$crumbs[] = tainacan_interface_crumb_current( $term->name );
			}
		} elseif ( 'tainacan-collection' !== get_post_type() && 'tainacan-taxonomy' !== get_post_type() ) {
			$crumbs[] = tainacan_interface_crumb_link( get_post_type_archive_link( 'tainacan-collection' ), __( 'Collections', 'tainacan-interface' ) );
			$label    = '';
			if ( $type_object ) {
				$label = is_post_type_archive() ? $type_object->labels->singular_name : __( 'All items', 'tainacan-interface' );
			}
			$crumbs[] = tainacan_interface_crumb_current( $label );
		} elseif ( is_archive() && 'tainacan-collection' === get_post_type() ) {
			$crumbs[] = tainacan_interface_crumb_current( __( 'Collections', 'tainacan-interface' ) );
		} elseif ( is_archive() && 'tainacan-taxonomy' === get_post_type() ) {
			$crumbs[] = tainacan_interface_crumb_current( __( 'Taxonomies', 'tainacan-interface' ) );
		} elseif ( $type_object ) {
			$crumbs[] = tainacan_interface_crumb_current( $type_object->labels->singular_name );
		}
	} elseif ( is_attachment() && $post ) {
		$parent = get_post( $post->post_parent );
		if ( $parent ) {
			$categories = get_the_category( $parent->ID );
			if ( ! empty( $categories ) ) {
				$crumbs[] = tainacan_interface_crumb_link( get_category_link( $categories[0] ), $categories[0]->name );
				$crumbs[] = tainacan_interface_crumb_link( get_permalink( $parent ), get_the_title( $parent ) );
			}
		}
		$crumbs[] = tainacan_interface_crumb_current( get_the_title() );

	} elseif ( is_page() && $post ) {
		foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
			$crumbs[] = tainacan_interface_crumb_link( get_permalink( $ancestor ), get_the_title( $ancestor ) );
		}
		$crumbs[] = tainacan_interface_crumb_current( get_the_title() );

	} elseif ( is_tag() ) {
		/* translators: %s: tag name. */
		$crumbs[] = tainacan_interface_crumb_current( sprintf( __( 'Posts tagged "%s"', 'tainacan-interface' ), single_tag_title( '', false ) ) );

	} elseif ( is_author() ) {
		$author = get_queried_object();
		/* translators: %s: author name. */
		$crumbs[] = tainacan_interface_crumb_current( sprintf( __( 'Articles posted by %s', 'tainacan-interface' ), $author instanceof WP_User ? $author->display_name : '' ) );

	} elseif ( is_404() ) {
		$crumbs[] = tainacan_interface_crumb_current( __( 'Error 404', 'tainacan-interface' ) );
	}

	$html = implode( $sep, array_filter( $crumbs ) );

	$paged = absint( get_query_var( 'paged' ) );
	if ( $paged && ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) ) {
		/* translators: %d: page number. */
		$html .= esc_html( sprintf( __( ' (Page %d)', 'tainacan-interface' ), $paged ) );
	}

	$pagination = '';
	if ( defined( 'TAINACAN_VERSION' ) && $post && is_single() && get_theme_mod( 'tainacan_single_item_show_navigation_options', false ) && \Tainacan\Theme_Helper::get_instance()->is_post_an_item( $post ) ) {
		$adjacent = tainacan_get_adjacent_item_links();
		if ( '' !== $adjacent['previous'] || '' !== $adjacent['next'] ) {
			$pagination = '<div id="breadcrumb-single-item-pagination" class="ml-auto d-flex align-items-center">'
				. '<div class="pagination">' . $adjacent['previous'] . '</div>'
				. '<div class="pagination">' . $adjacent['next'] . '</div>'
				. '<div class="pagination"><a href="' . esc_url( tainacan_get_source_item_list_url() ) . '" aria-label="' . esc_attr__( 'Back to items list', 'tainacan-interface' ) . '"><i class="tainacan-icon tainacan-icon-viewtable tainacan-icon-1-25em"></i></a></div>'
				. '</div>';
		}
	}

	return tainacan_interface_modern_breadcrumb( $html, $pagination );
}
