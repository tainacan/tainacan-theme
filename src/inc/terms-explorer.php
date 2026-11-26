<?php
/**
 * Terms explorer: the page of a Tainacan taxonomy shows its whole hierarchy
 * in two views (tree, columns), with an A–Z index, pages of 100 terms per
 * level and a panel for the chosen term.
 * Replaces the 2.x views (cards, thumbnails, table), which listed one level
 * at a time and hid the context of each term.
 *
 * The page carries the structure of the taxonomy (id, parent, name, count,
 * description, image, link); the items of a term come from the Tainacan REST
 * when the term is opened, with the permissions of whoever is looking.
 *
 * @package tainacan-interface
 */

/**
 * Views of the terms page. The first one is the default.
 *
 * @return string[]
 */
function tainacan_interface_terms_view_modes() {
	return array( 'tree', 'columns' );
}

/**
 * View mode of the terms page, from the closed list. Links of 2.x
 * (cards, grid, table) fall back to the default.
 *
 * @return string
 */
function tainacan_interface_terms_view_mode() {
	$modes = tainacan_interface_terms_view_modes();
	$mode  = (string) get_query_var( 'tainacan_terms_viewmode', '' );
	return in_array( $mode, $modes, true ) ? $mode : $modes[0];
}

/**
 * Is this request the page of a Tainacan taxonomy?
 *
 * @return bool
 */
function tainacan_interface_is_taxonomy_page() {
	return is_singular( 'tainacan-taxonomy' );
}

/**
 * Structure of a taxonomy for the explorer, cached until any term changes.
 *
 * @param int $taxonomy_post_id ID of the tainacan-taxonomy post.
 * @return array{terms: array<int, array<string, mixed>>, total: int}
 */
function tainacan_interface_terms_explorer_data( $taxonomy_post_id ) {
	$taxonomy_post_id = absint( $taxonomy_post_id );
	$taxonomy         = 'tnc_tax_' . $taxonomy_post_id;
	$cache_key        = 'tt2_terms_' . $taxonomy_post_id . '_' . md5( wp_cache_get_last_changed( 'terms' ) . TAINACAN_INTERFACE_VERSION );

	$cached = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$terms = get_terms(
		array(
			'taxonomy'               => $taxonomy,
			'hide_empty'             => false,
			'orderby'                => 'name',
			'order'                  => 'ASC',
			'update_term_meta_cache' => true,
		)
	);

	$data = array(
		'terms' => array(),
		'total' => 0,
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $data;
	}

	foreach ( $terms as $term ) {
		$image_id = absint( get_term_meta( $term->term_id, 'header_image_id', true ) );
		$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'tainacan-medium' ) : '';
		$link     = get_term_link( $term );

		$data['terms'][] = array(
			'id' => (int) $term->term_id,
			'p'  => (int) $term->parent,
			'n'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'c'  => (int) $term->count,
			'd'  => wp_trim_words( wp_strip_all_tags( $term->description ), 60, '…' ),
			'i'  => $image ? esc_url_raw( $image ) : '',
			'u'  => is_wp_error( $link ) ? '' : esc_url_raw( $link ),
		);
	}
	$data['total'] = count( $data['terms'] );

	set_transient( $cache_key, $data, DAY_IN_SECONDS );
	return $data;
}

/**
 * Server-side nested list of the terms: what the visitor sees without
 * JavaScript and what search engines read. The explorer replaces it.
 *
 * @param array<int, array<string, mixed>> $terms  Terms from tainacan_interface_terms_explorer_data().
 * @param int                              $parent_id Parent term ID.
 * @param int                              $depth  Current depth.
 */
function tainacan_interface_terms_fallback_list( $terms, $parent_id = 0, $depth = 0 ) {
	static $by_parent = null;
	if ( 0 === $depth ) {
		$by_parent = array();
		foreach ( $terms as $term ) {
			$by_parent[ $term['p'] ][] = $term;
		}
	}
	if ( empty( $by_parent[ $parent_id ] ) || $depth > 12 ) {
		return;
	}
	echo '<ul>';
	foreach ( $by_parent[ $parent_id ] as $term ) {
		echo '<li>';
		if ( $term['u'] ) {
			printf( '<a href="%1$s">%2$s</a>', esc_url( $term['u'] ), esc_html( $term['n'] ) );
		} else {
			echo esc_html( $term['n'] );
		}
		tainacan_interface_terms_fallback_list( $terms, $term['id'], $depth + 1 );
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Both forms of a plural string, translated, for the script (which picks
 * one by the number).
 *
 * @param array $nooped Result of _n_noop().
 * @return array{singular: string, plural: string}
 */
function tainacan_interface_terms_plural( $nooped ) {
	return array(
		'singular' => translate_nooped_plural( $nooped, 1, 'tainacan-interface' ),
		'plural'   => translate_nooped_plural( $nooped, 2, 'tainacan-interface' ),
	);
}

/**
 * Loads the explorer only on the page of a taxonomy.
 */
function tainacan_interface_terms_explorer_enqueue() {
	if ( ! tainacan_interface_is_taxonomy_page() ) {
		return;
	}
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'tainacan-interface-terms-explorer', $uri . '/assets/css/terms-explorer.css', array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script( 'tainacan-interface-terms-explorer', $uri . '/assets/js/terms-explorer.js', array(), TAINACAN_INTERFACE_VERSION, true );
	wp_localize_script(
		'tainacan-interface-terms-explorer',
		'tainacanInterfaceTerms',
		array(
			'items'    => esc_url_raw( rest_url( 'tainacan/v2/items/' ) ),
			'nonce'    => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
			'taxonomy' => 'tnc_tax_' . absint( get_queried_object_id() ),
			// Items without thumbnail get the theme's "no image" picture of their document type, as in the lists.
			'ph'       => function_exists( 'tainacan_interface_placeholder_on' ) && tainacan_interface_placeholder_on()
				? array_merge( tainacan_interface_placeholder_rules(), array( 'url' => tainacan_interface_placeholder_url( '__KIND__', 'm' ) ) )
				: null,
			'i18n'     => array(
				'tree'        => __( 'Tree', 'tainacan-interface' ),
				'columns'     => __( 'Columns', 'tainacan-interface' ),
				'az'          => __( 'Terms by initial letter', 'tainacan-interface' ),
				'all'         => __( 'All', 'tainacan-interface' ),
				'pages'       => __( 'Pages of terms', 'tainacan-interface' ),
				'prev'        => __( 'Previous page', 'tainacan-interface' ),
				'next'        => __( 'Next page', 'tainacan-interface' ),
				/* translators: 1: first term shown, 2: last term shown, 3: number of terms. */
				'range'       => __( '%1$d–%2$d of %3$d', 'tainacan-interface' ),
				/* translators: %d: number of terms that the button shows. */
				'showMore'    => __( 'Show %d more', 'tainacan-interface' ),
				/* translators: %d: number of terms not shown yet. */
				'remaining'   => tainacan_interface_terms_plural( _n_noop( '%d left', '%d left', 'tainacan-interface' ) ),
				'views'       => __( 'Ways of seeing the taxonomy', 'tainacan-interface' ),
				'search'      => __( 'Find a term', 'tainacan-interface' ),
				'sortName'    => __( 'Alphabetical', 'tainacan-interface' ),
				'sortCount'   => __( 'Most items first', 'tainacan-interface' ),
				'sort'        => __( 'Order', 'tainacan-interface' ),
				'expandAll'   => __( 'Expand all', 'tainacan-interface' ),
				'collapseAll' => __( 'Collapse all', 'tainacan-interface' ),
				/* translators: %d: number of terms. */
				'terms'       => tainacan_interface_terms_plural( _n_noop( '%d term', '%d terms', 'tainacan-interface' ) ),
				/* translators: %d: number of items. */
				'items'       => tainacan_interface_terms_plural( _n_noop( '%d item', '%d items', 'tainacan-interface' ) ),
				/* translators: %d: number of direct children. */
				'children'    => tainacan_interface_terms_plural( _n_noop( '%d child term', '%d child terms', 'tainacan-interface' ) ),
				/* translators: %d: number of terms in the whole branch. */
				'below'       => tainacan_interface_terms_plural( _n_noop( '%d term in the branch', '%d terms in the branch', 'tainacan-interface' ) ),
				/* translators: %d: number of levels. */
				'levels'      => tainacan_interface_terms_plural( _n_noop( '%d level', '%d levels', 'tainacan-interface' ) ),
				/* translators: %d: number of terms matching the search. */
				'found'       => tainacan_interface_terms_plural( _n_noop( '%d term found', '%d terms found', 'tainacan-interface' ) ),
				'nothing'     => __( 'No term matches this search.', 'tainacan-interface' ),
				'root'        => __( 'All terms', 'tainacan-interface' ),
				'seeItems'    => __( 'See all items', 'tainacan-interface' ),
				'subterms'    => __( 'Terms below', 'tainacan-interface' ),
				'siblings'    => __( 'Same level', 'tainacan-interface' ),
				'itemsTitle'  => __( 'Items with this term', 'tainacan-interface' ),
				'noItems'     => __( 'No item uses this term yet.', 'tainacan-interface' ),
				'loading'     => __( 'Loading…', 'tainacan-interface' ),
				'error'       => __( 'The items could not be loaded.', 'tainacan-interface' ),
				'close'       => __( 'Close', 'tainacan-interface' ),
				'pick'        => __( 'Choose a term to see its details, its place in the hierarchy and its items.', 'tainacan-interface' ),
				'path'        => __( 'Place in the hierarchy', 'tainacan-interface' ),
				'copy'        => __( 'Copy link', 'tainacan-interface' ),
				'copied'      => __( 'Link copied', 'tainacan-interface' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_terms_explorer_enqueue', 25 );
