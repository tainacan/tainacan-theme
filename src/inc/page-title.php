<?php
/**
 * The title at the top of the blog, posts, pages, searches and archives: a
 * line saying what kind of page it is, the title, and the count or the
 * description of the listing.
 *
 * @package tainacan-interface
 */

/**
 * What the title of the current page says. The title is HTML, ready for
 * wp_kses_post(); the other texts are plain.
 *
 * @return array{kicker: string, kicker_url: string, title: string, meta: string, description: string, back: string}
 */
function tainacan_interface_page_title_parts() {
	global $wp_query;

	// Where "Back" leads a visitor who came from outside: the blog, or the start page.
	$blog  = tainacan_interface_blog_url();
	$blog  = '' !== $blog ? $blog : home_url( '/' );
	$parts = array(
		'kicker'      => '',
		'kicker_url'  => '',
		'title'       => '',
		'meta'        => '',
		'description' => '',
		'back'        => home_url( '/' ),
	);
	if ( ! is_singular() ) {
		$count = $wp_query instanceof WP_Query ? (int) $wp_query->found_posts : 0;
		/* translators: %s: number of posts. */
		$posts = sprintf( _n( '%s post', '%s posts', $count, 'tainacan-interface' ), number_format_i18n( $count ) );
	}

	if ( is_singular() ) {
		$parts['title'] = get_the_title();
		$parent         = wp_get_post_parent_id( get_the_ID() );
		if ( is_singular( 'post' ) ) {
			$parts['back']       = $blog;
			$parts['kicker']     = _x( 'Blog', 'kind of page, above its title', 'tainacan-interface' );
			$parts['kicker_url'] = $blog;
			$category            = tainacan_interface_post_category();
			$category_url        = $category ? get_term_link( $category ) : '';
			if ( $category && is_string( $category_url ) ) {
				$parts['kicker']     = $category->name;
				$parts['kicker_url'] = $category_url;
			}
		} elseif ( $parent ) {
			$parts['kicker']     = get_the_title( $parent );
			$parts['kicker_url'] = (string) get_permalink( $parent );
			$parts['back']       = $parts['kicker_url'];
		} elseif ( is_front_page() ) {
			$parts['back'] = '';
		}
	} elseif ( is_home() ) {
		$parts['kicker'] = _x( 'Blog', 'kind of page, above its title', 'tainacan-interface' );
		$parts['title']  = get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'Blog Posts', 'tainacan-interface' );
		$parts['meta']   = $posts;
		// The blog on the start page has nowhere further back to go.
		$parts['back'] = is_front_page() ? '' : home_url( '/' );
	} elseif ( is_search() ) {
		$parts['kicker'] = _x( 'Search results', 'kind of page, above its title', 'tainacan-interface' );
		/* translators: %s: the words searched for. */
		$parts['title'] = sprintf( __( '&#8220;%s&#8221;', 'tainacan-interface' ), esc_html( get_search_query( false ) ) );
		/* translators: %s: number of results. */
		$parts['meta'] = sprintf( _n( '%s result', '%s results', $count, 'tainacan-interface' ), number_format_i18n( $count ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$taxonomy        = get_taxonomy( $term->taxonomy );
			$parts['kicker'] = $taxonomy ? $taxonomy->labels->singular_name : '';
		}
		$parts['title']       = single_term_title( '', false );
		$parts['meta']        = $posts;
		$parts['description'] = wp_strip_all_tags( term_description() );
		$parts['back']        = $blog;
	} elseif ( is_author() ) {
		$parts['kicker']      = _x( 'Author', 'kind of page, above its title', 'tainacan-interface' );
		$parts['title']       = esc_html( get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) ) );
		$parts['meta']        = $posts;
		$parts['description'] = wp_strip_all_tags( get_the_author_meta( 'description', (int) get_query_var( 'author' ) ) );
		$parts['back']        = $blog;
	} elseif ( is_date() ) {
		$parts['kicker'] = _x( 'Archive', 'kind of page, above its title', 'tainacan-interface' );
		$parts['title']  = esc_html( tainacan_interface_archive_date() );
		$parts['meta']   = $posts;
		$parts['back']   = $blog;
	} elseif ( is_archive() ) {
		$parts['title'] = post_type_archive_title( '', false );
		$parts['meta']  = $posts;
	}

	/**
	 * Filters what the title at the top of a page says.
	 *
	 * @param array $parts kicker, kicker_url, title (HTML), meta, description and back (the address of the
	 *                     back link for a visitor who came from outside; '' hides the button).
	 */
	return apply_filters( 'tainacan_interface_page_title_parts', $parts );
}

/**
 * The date a date archive asks for, from the address, not from its first post:
 * a page past the last one still has its title.
 *
 * @return string
 */
function tainacan_interface_archive_date() {
	$year  = (int) get_query_var( 'year' );
	$month = (int) get_query_var( 'monthnum' );
	$day   = (int) get_query_var( 'day' );
	$m     = preg_replace( '/\D/', '', (string) get_query_var( 'm' ) );
	if ( ! $year && strlen( $m ) >= 4 ) {
		$year  = (int) substr( $m, 0, 4 );
		$month = (int) substr( $m, 4, 2 );
		$day   = (int) substr( $m, 6, 2 );
	}
	if ( ! $year ) {
		return (string) get_the_date();
	}
	$utc  = new DateTimeZone( 'UTC' );
	$time = gmmktime( 0, 0, 0, max( 1, $month ), max( 1, $day ), $year );
	if ( $day ) {
		return (string) wp_date( get_option( 'date_format' ), $time, $utc );
	}
	if ( $month ) {
		return (string) wp_date( _x( 'F Y', 'monthly archives date format', 'tainacan-interface' ), $time, $utc );
	}
	return (string) $year;
}

/**
 * The category that names a post above its title: the first one that is not the
 * default category.
 *
 * @return WP_Term|null
 */
function tainacan_interface_post_category() {
	$default = (int) get_option( 'default_category' );
	foreach ( get_the_category() as $category ) {
		if ( (int) $category->term_id !== $default ) {
			return $category;
		}
	}
	return null;
}
