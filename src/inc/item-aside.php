<?php
/**
 * Side column of the item page: ways to keep exploring, beside the metadata.
 *
 * The column holds "threads" of related items:
 * - relationships: the items that point to this one through a Tainacan
 *   relationship metadatum (the native related items) and the items this one
 *   points to;
 * - shared terms: items of the same collection with the same term, the rarest
 *   (most specific) term first;
 * - more from the collection: only when no other thread has anything, so the
 *   page never ends without a way on.
 *
 * Only published items of public collections are listed, so the result is the
 * same for every visitor and can be cached. Every thread is one query of IDs;
 * titles and thumbnails come from primed caches.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_RELATED_META = 'tainacan_interface_related';

/**
 * Whether the side column is on. Theme mod in the Customizer, plus a filter.
 *
 * @return bool
 */
function tainacan_interface_aside_enabled() {
	/**
	 * Turns the side column of the item page on or off.
	 *
	 * @param bool $enabled Value of the Customizer option.
	 */
	return (bool) apply_filters( 'tainacan_interface_item_aside_enabled', (bool) get_theme_mod( 'tainacan_interface_item_aside', true ) );
}

/**
 * Settings of the column, read and bounded once per request.
 *
 * @return array
 */
function tainacan_interface_aside_settings() {
	static $settings = null;
	if ( null !== $settings ) {
		return $settings;
	}

	$per      = absint( get_theme_mod( 'tainacan_interface_aside_per_thread', 6 ) );
	$settings = array(
		'side'          => 'left' === get_theme_mod( 'tainacan_interface_aside_side', 'right' ) ? 'left' : 'right',
		'title'         => (string) get_theme_mod( 'tainacan_interface_aside_title', __( 'Keep exploring', 'tainacan-interface' ) ),
		'relationships' => (bool) get_theme_mod( 'tainacan_interface_aside_relationships', true ),
		'terms'         => (bool) get_theme_mod( 'tainacan_interface_aside_terms', true ),
		'collection'    => (bool) get_theme_mod( 'tainacan_interface_aside_collection', true ),
		'order'         => 'terms' === get_theme_mod( 'tainacan_interface_aside_order', 'relationships' ) ? 'terms' : 'relationships',
		'per_thread'    => in_array( $per, array( 3, 6, 9 ), true ) ? $per : 6,
		'term_threads'  => min( 3, max( 1, absint( get_theme_mod( 'tainacan_interface_aside_term_threads', 2 ) ) ) ),
		'sticky'        => (bool) get_theme_mod( 'tainacan_interface_aside_sticky', true ),
		'hide_bottom'   => (bool) get_theme_mod( 'tainacan_interface_aside_hide_bottom', true ),
	);
	return $settings;
}

/* ---------- "Use to suggest related items" on the metadatum form ---------- */

/**
 * Registers the field on the metadatum form.
 */
function tainacan_interface_related_option_register_hook() {
	if ( function_exists( 'tainacan_register_admin_hook' ) ) {
		tainacan_register_admin_hook( 'metadatum', 'tainacan_interface_related_option_form', 'end-left' );
	}
}
add_action( 'tainacan-register-admin-hooks', 'tainacan_interface_related_option_register_hook' );

/**
 * Declares the meta, so the form is filled with the saved value.
 *
 * @param array $extra_meta Meta keys declared by others.
 * @return array
 */
function tainacan_interface_related_option_expose( $extra_meta ) {
	$extra_meta   = is_array( $extra_meta ) ? $extra_meta : array();
	$extra_meta[] = TAINACAN_INTERFACE_RELATED_META;
	return $extra_meta;
}
add_filter( 'tainacan-api-response-metadatum-meta', 'tainacan_interface_related_option_expose' );

/**
 * The form field (printed by Tainacan with v-html: no script).
 *
 * @return string
 */
function tainacan_interface_related_option_form() {
	ob_start();
	?>
	<div class="field">
		<label class="label" for="<?php echo esc_attr( TAINACAN_INTERFACE_RELATED_META ); ?>"><?php esc_html_e( 'Use to suggest related items', 'tainacan-interface' ); ?></label>
		<div class="control is-clearfix">
			<select id="<?php echo esc_attr( TAINACAN_INTERFACE_RELATED_META ); ?>" name="<?php echo esc_attr( TAINACAN_INTERFACE_RELATED_META ); ?>">
				<option value="no"><?php esc_html_e( 'No', 'tainacan-interface' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Yes', 'tainacan-interface' ); ?></option>
			</select>
		</div>
		<p class="help"><?php esc_html_e( 'Tainacan Interface, taxonomy metadata only: the side column of the item page suggests items with the same term of the taxonomies marked here. When no taxonomy of a collection is marked, all of them are used.', 'tainacan-interface' ); ?></p>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Saves the option with the metadatum.
 *
 * @param \Tainacan\Entities\Metadatum $metadatum Metadatum being saved.
 */
function tainacan_interface_related_option_save( $metadatum ) {
	if ( ! function_exists( 'tainacan_get_api_postdata' ) || ! $metadatum instanceof \Tainacan\Entities\Metadatum || ! $metadatum->can_edit() ) {
		return;
	}

	$posted = tainacan_get_api_postdata();
	if ( ! is_object( $posted ) || ! isset( $posted->{TAINACAN_INTERFACE_RELATED_META} ) ) {
		return;
	}

	update_post_meta( (int) $metadatum->get_id(), TAINACAN_INTERFACE_RELATED_META, 'yes' === $posted->{TAINACAN_INTERFACE_RELATED_META} ? 'yes' : 'no' );
}
add_action( 'tainacan-insert-tainacan-metadatum', 'tainacan_interface_related_option_save' );

/* ---------- threads ---------- */

/**
 * Published items of public collections, in the given order, without the
 * excluded ones.
 *
 * @param int[] $ids     Item post IDs.
 * @param array $exclude IDs already shown, keyed by ID.
 * @return int[]
 */
function tainacan_interface_aside_public_ids( $ids, $exclude ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	if ( empty( $ids ) ) {
		return array();
	}
	_prime_post_caches( $ids, false, false );

	$public     = array();
	$collection = array();
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( isset( $exclude[ $id ] ) || ! $post || 'publish' !== $post->post_status || '' !== $post->post_password || 1 !== preg_match( '/^tnc_col_(\d+)_item$/', (string) $post->post_type, $match ) ) {
			continue;
		}
		$cid = (int) $match[1];
		if ( ! isset( $collection[ $cid ] ) ) {
			$collection[ $cid ] = 'publish' === get_post_status( $cid );
		}
		if ( $collection[ $cid ] ) {
			$public[] = $id;
		}
	}
	return $public;
}

/**
 * Relationship threads: who points to the item (the native related items,
 * as configured in each relationship metadatum) and whom the item points to.
 *
 * @param \Tainacan\Entities\Item $item    Item.
 * @param int                     $per     Items per thread.
 * @param array                   $exclude IDs already shown, keyed by ID (updated).
 * @return array
 */
function tainacan_interface_aside_relationship_threads( $item, $per, &$exclude ) {
	$threads = array();

	// Points to the item. Published items only, so the total is what "See all" lists.
	$groups = $item->get_related_items(
		array(
			'posts_per_page' => $per + 2,
			'post_status'    => 'publish',
		)
	);
	foreach ( is_array( $groups ) ? $groups : array() as $group ) {
		if ( empty( $group['items'] ) || ! is_array( $group['items'] ) || 'publish' !== get_post_status( (int) $group['collection_id'] ) ) {
			continue;
		}
		$ids = tainacan_interface_aside_public_ids( wp_list_pluck( $group['items'], 'id' ), $exclude );
		if ( empty( $ids ) ) {
			continue;
		}
		$ids = array_slice( $ids, 0, $per );
		foreach ( $ids as $id ) {
			$exclude[ $id ] = true;
		}
		$threads[] = array(
			'kind'  => 'relationship',
			'label' => (int) $group['collection_id'] === (int) $item->get_collection_id()
				/* translators: %s: name of the relationship metadatum, e.g. "See also". */
				? sprintf( __( 'Refer to this item in “%s”', 'tainacan-interface' ), (string) $group['metadata_name'] )
				/* translators: 1: name of the relationship metadatum, 2: name of the collection that holds it. */
				: sprintf( __( 'Refer to this item in “%1$s” (%2$s)', 'tainacan-interface' ), (string) $group['metadata_name'], (string) $group['collection_name'] ),
			'total' => isset( $group['total_items'] ) ? (int) $group['total_items'] : count( $ids ),
			'ids'   => $ids,
			'url'   => add_query_arg(
				array(
					'metaquery' => array(
						array(
							'key'   => (int) $group['metadata_id'],
							'value' => (int) $item->get_id(),
						),
					),
				),
				(string) $group['collection_url']
			),
		);
	}

	// The item points to.
	foreach ( tainacan_interface_aside_metadata( $item->get_collection(), 'Relationship' ) as $metadatum ) {
		$ids = tainacan_interface_aside_public_ids( get_post_meta( (int) $item->get_id(), (string) $metadatum->get_id(), false ), $exclude );
		if ( empty( $ids ) ) {
			continue;
		}
		$total = count( $ids );
		$ids   = array_slice( $ids, 0, $per );
		foreach ( $ids as $id ) {
			$exclude[ $id ] = true;
		}
		$threads[] = array(
			'kind'  => 'relationship',
			'label' => (string) $metadatum->get_name(),
			'total' => $total,
			'ids'   => $ids,
			'url'   => '',
		);
	}

	return $threads;
}

/**
 * Published relationship or taxonomy metadata of a collection. One fetch for
 * both kinds, memoized per request.
 *
 * @param mixed  $collection Collection entity.
 * @param string $kind       Relationship|Taxonomy.
 * @return \Tainacan\Entities\Metadatum[]
 */
function tainacan_interface_aside_metadata( $collection, $kind ) {
	static $memo = array();
	if ( ! $collection instanceof \Tainacan\Entities\Collection ) {
		return array();
	}
	$cid = (int) $collection->get_id();
	if ( ! isset( $memo[ $cid ] ) ) {
		$memo[ $cid ] = array(
			'Relationship' => array(),
			'Taxonomy'     => array(),
		);
		$found        = \Tainacan\Repositories\Metadata::get_instance()->fetch_by_collection(
			$collection,
			array(
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Tainacan stores the metadatum type as post meta and the repository has no other filter for it; the metadata of one collection are a handful of posts.
					array(
						'key'     => 'metadata_type',
						'value'   => array( 'Tainacan\Metadata_Types\Relationship', 'Tainacan\Metadata_Types\Taxonomy' ),
						'compare' => 'IN',
					),
				),
			),
			'OBJECT'
		);
		foreach ( (array) $found as $metadatum ) {
			if ( $metadatum instanceof \Tainacan\Entities\Metadatum && 'publish' === $metadatum->get_status() ) {
				$type                    = 'Tainacan\Metadata_Types\Taxonomy' === $metadatum->get_metadata_type() ? 'Taxonomy' : 'Relationship';
				$memo[ $cid ][ $type ][] = $metadatum;
			}
		}
	}
	return isset( $memo[ $cid ][ $kind ] ) ? $memo[ $cid ][ $kind ] : array();
}

/**
 * Shared-term threads: the item's terms of the chosen taxonomies, rarest
 * first, skipping terms only the item has.
 *
 * @param \Tainacan\Entities\Item $item    Item.
 * @param int                     $per     Items per thread.
 * @param int                     $max     Maximum number of threads.
 * @param array                   $exclude IDs already shown, keyed by ID (updated).
 * @return array
 */
function tainacan_interface_aside_term_threads( $item, $per, $max, &$exclude ) {
	$collection = $item->get_collection();
	if ( ! $collection instanceof \Tainacan\Entities\Collection ) {
		return array();
	}

	$all    = array();
	$marked = array();
	foreach ( tainacan_interface_aside_metadata( $collection, 'Taxonomy' ) as $metadatum ) {
		$options  = $metadatum->get_metadata_type_options();
		$taxonomy = ! empty( $options['taxonomy_id'] ) ? 'tnc_tax_' . absint( $options['taxonomy_id'] ) : '';
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) || 'publish' !== get_post_status( absint( $options['taxonomy_id'] ) ) ) {
			continue;
		}
		$all[] = $taxonomy;
		if ( 'yes' === get_post_meta( (int) $metadatum->get_id(), TAINACAN_INTERFACE_RELATED_META, true ) ) {
			$marked[] = $taxonomy;
		}
	}
	$taxonomies = array_values( array_unique( ! empty( $marked ) ? $marked : $all ) );
	if ( empty( $taxonomies ) ) {
		return array();
	}

	$terms = wp_get_object_terms( (int) $item->get_id(), $taxonomies );
	if ( ! is_array( $terms ) ) {
		return array();
	}
	// Rarest first; a term with one item is the item itself.
	$terms = array_filter(
		$terms,
		function ( $term ) {
			return $term->count > 1;
		}
	);
	usort(
		$terms,
		function ( $a, $b ) {
			return $a->count - $b->count;
		}
	);

	// A term can still come up empty (its other items unpublished): try a few more than needed, never all.
	$threads = array();
	foreach ( array_slice( $terms, 0, $max + 2 ) as $term ) {
		if ( count( $threads ) >= $max ) {
			break;
		}
		$query = new WP_Query( // tnc-check-ignore TNC-P02: at most term_threads + 2 (5) queries of IDs, one per shared term; each needs its own LIMIT, and the result is cached.
			array(
				'post_type'              => $collection->get_db_identifier(),
				'post_status'            => 'publish',
				'has_password'           => false,
				'posts_per_page'         => $per,
				'post__not_in'           => array_keys( $exclude ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- at most a few dozen IDs already on the page; filtering them out in PHP would need a larger LIMIT and could still come up short.
				'fields'                 => 'ids',
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- the shared term is the whole point of the thread; term_relationships is indexed by term.
					array(
						'taxonomy'         => $term->taxonomy,
						'terms'            => array( (int) $term->term_id ),
						'include_children' => false,
					),
				),
			)
		);
		$ids   = array_map( 'intval', $query->posts );
		if ( empty( $ids ) ) {
			continue;
		}
		foreach ( $ids as $id ) {
			$exclude[ $id ] = true;
		}
		$threads[] = array(
			'kind'  => 'term',
			/* translators: %s: term name, e.g. "Rio de Janeiro". */
			'label' => sprintf( __( 'Also “%s”', 'tainacan-interface' ), $term->name ),
			// Counted by the query, not term->count: that one counts drafts and items of other collections.
			'total' => (int) $query->found_posts,
			'ids'   => $ids,
			'url'   => add_query_arg(
				array(
					'taxquery' => array(
						array(
							'taxonomy' => $term->taxonomy,
							'terms'    => array( (int) $term->term_id ),
							'compare'  => 'IN',
						),
					),
				),
				$collection->get_url()
			),
		);
	}
	return $threads;
}

/**
 * "More from this collection": the latest items, for when nothing else shows.
 *
 * @param \Tainacan\Entities\Item $item    Item.
 * @param int                     $per     Items per thread.
 * @param array                   $exclude IDs already shown, keyed by ID.
 * @return array
 */
function tainacan_interface_aside_collection_thread( $item, $per, $exclude ) {
	$collection = $item->get_collection();
	if ( ! $collection instanceof \Tainacan\Entities\Collection ) {
		return array();
	}
	$query = new WP_Query(
		array(
			'post_type'              => $collection->get_db_identifier(),
			'post_status'            => 'publish',
			'has_password'           => false,
			'posts_per_page'         => $per,
			'post__not_in'           => array_keys( $exclude ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- only the item itself here.
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
		)
	);
	if ( empty( $query->posts ) ) {
		return array();
	}
	return array(
		array(
			'kind'  => 'collection',
			'label' => __( 'More from this collection', 'tainacan-interface' ),
			'total' => 0,
			'ids'   => array_map( 'intval', $query->posts ),
			'url'   => $collection->get_url(),
		),
	);
}

/**
 * Threads of an item. Cached per item until any post or term changes, but only
 * when built for an anonymous visitor: a logged-in user may read private
 * metadata sections, and what they see must not be served to the public.
 *
 * @param int $item_id Item post ID.
 * @return array
 */
function tainacan_interface_aside_threads( $item_id ) {
	$item = tainacan_interface_readable_item( (int) $item_id );
	if ( ! $item ) {
		return array();
	}

	$s    = tainacan_interface_aside_settings();
	$key  = 'threads:' . (int) $item_id . ':' . md5( wp_json_encode( $s ) . get_locale() ) . ':' . wp_cache_get_last_changed( 'posts' ) . ':' . wp_cache_get_last_changed( 'terms' );
	$anon = ! is_user_logged_in();
	$hit  = $anon ? wp_cache_get( $key, 'tainacan_interface_aside' ) : false;
	if ( is_array( $hit ) ) {
		return $hit;
	}

	$exclude = array( (int) $item_id => true );
	$rel     = $s['relationships'] ? tainacan_interface_aside_relationship_threads( $item, $s['per_thread'], $exclude ) : array();
	$terms   = $s['terms'] ? tainacan_interface_aside_term_threads( $item, $s['per_thread'], $s['term_threads'], $exclude ) : array();
	$threads = 'terms' === $s['order'] ? array_merge( $terms, $rel ) : array_merge( $rel, $terms );
	if ( empty( $threads ) && $s['collection'] ) {
		$threads = tainacan_interface_aside_collection_thread( $item, $s['per_thread'], $exclude );
	}

	/**
	 * Threads of the side column. Each thread: kind, label, total, ids, url.
	 * The IDs must be readable by anyone: the result is cached for every visitor.
	 *
	 * @param array                   $threads Threads.
	 * @param \Tainacan\Entities\Item $item    Item.
	 */
	$threads = (array) apply_filters( 'tainacan_interface_item_aside_threads', $threads, $item );

	if ( $anon ) {
		wp_cache_set( $key, $threads, 'tainacan_interface_aside', 12 * HOUR_IN_SECONDS );
	}
	return $threads;
}

/* ---------- output ---------- */

/**
 * Prints the metadata section, inside the grid with the side column when
 * the column has threads. Replaces the plain template part in the item page.
 */
function tainacan_interface_the_item_metadata() {
	$threads = tainacan_interface_aside_enabled() && tainacan_interface_is_item_page() ? tainacan_interface_aside_threads( get_the_ID() ) : array();

	if ( empty( $threads ) ) {
		if ( tainacan_interface_is_modern() ) {
			echo '<div class="tt2-item-body tt2-item-body--alone"><div class="tt2-item-body__main">';
			get_template_part( 'template-parts/single-items-metadata' );
			tainacan_interface_the_item_citation();
			echo '</div></div>';
		} else {
			get_template_part( 'template-parts/single-items-metadata' );
		}
		do_action( 'tainacan-interface-single-item-after-metadata' );
		return;
	}

	$s = tainacan_interface_aside_settings();
	tainacan_interface_aside_shown( in_array( 'relationship', wp_list_pluck( $threads, 'kind' ), true ) );
	printf( '<div class="%s">', esc_attr( 'tt2-item-body tt2-item-body--' . $s['side'] . ( $s['sticky'] ? ' is-sticky' : '' ) ) );
	echo '<div class="tt2-item-body__main">';
	get_template_part( 'template-parts/single-items-metadata' );
	tainacan_interface_the_item_citation();
	echo '</div>';
	tainacan_interface_the_item_aside( $threads, $s );
	echo '</div>';
	// Outside the grid: what plugins print here keeps the full width it had.
	do_action( 'tainacan-interface-single-item-after-metadata' );
}

/**
 * Whether the column printed relationship threads on this page. Set by the
 * column, read by the bottom related items section, which comes later.
 *
 * @param bool|null $set Value to record; null to read.
 * @return bool
 */
function tainacan_interface_aside_shown( $set = null ) {
	static $shown = false;
	if ( null !== $set ) {
		$shown = (bool) $set;
	}
	return $shown;
}

/**
 * Prints the side column.
 *
 * @param array $threads Threads.
 * @param array $s       Settings.
 */
function tainacan_interface_the_item_aside( $threads, $s ) {
	$ids = array();
	foreach ( $threads as $thread ) {
		$ids = array_merge( $ids, (array) $thread['ids'] );
	}
	// Posts and their meta in one query each, then all the thumbnails in one more.
	_prime_post_caches( $ids, false, true );
	_prime_post_caches( array_filter( array_map( 'get_post_thumbnail_id', $ids ) ), false, true );

	$heading = '' !== trim( $s['title'] ) ? 'tt2-aside-title' : '';
	?>
	<aside class="tt2-aside"<?php echo '' !== $heading ? ' aria-labelledby="tt2-aside-title"' : ' aria-label="' . esc_attr__( 'Related items', 'tainacan-interface' ) . '"'; ?>>
		<?php if ( '' !== $heading ) : ?>
			<h2 class="tt2-aside__title label" id="tt2-aside-title"><?php echo esc_html( $s['title'] ); ?></h2>
		<?php endif; ?>
		<?php foreach ( $threads as $thread ) : ?>
			<section class="tt2-aside__thread tt2-aside__thread--<?php echo esc_attr( sanitize_html_class( (string) $thread['kind'] ) ); ?>">
				<h3 class="tt2-aside__label label">
					<?php echo esc_html( (string) $thread['label'] ); ?>
					<?php if ( ! empty( $thread['total'] ) ) : ?>
						<small>
							<?php
							$thread_total = (int) $thread['total'];
							/* translators: %d: number of items. */
							echo esc_html( sprintf( _n( '%d item', '%d items', $thread_total, 'tainacan-interface' ), $thread_total ) );
							?>
						</small>
					<?php endif; ?>
				</h3>
				<ul class="tt2-aside__grid">
					<?php foreach ( (array) $thread['ids'] as $id ) : ?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
								<?php if ( has_post_thumbnail( $id ) ) : ?>
									<?php
									echo get_the_post_thumbnail(
										$id,
										'thumbnail',
										array(
											'alt'      => '',
											'loading'  => 'lazy',
											'decoding' => 'async',
										)
									);
									?>
								<?php else : ?>
									<span class="tt2-aside__noimg" aria-hidden="true"></span>
								<?php endif; ?>
								<span class="tt2-aside__name"><?php echo esc_html( get_the_title( $id ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( ! empty( $thread['url'] ) ) : ?>
					<p class="tt2-aside__more"><a href="<?php echo esc_url( (string) $thread['url'] ); ?>"><?php esc_html_e( 'See all', 'tainacan-interface' ); ?></a></p>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</aside>
	<?php
}

/**
 * Whether the native "Items related to this" section at the bottom of the
 * page should stay out: only when the column did show relationship threads.
 *
 * @return bool
 */
function tainacan_interface_aside_takes_related() {
	return tainacan_interface_aside_enabled() && tainacan_interface_aside_settings()['hide_bottom'] && tainacan_interface_aside_shown();
}

/**
 * Style, only on item pages.
 */
function tainacan_interface_aside_enqueue() {
	if ( tainacan_interface_aside_enabled() && tainacan_interface_is_item_page() ) {
		wp_enqueue_style( 'tainacan-interface-item-aside', get_template_directory_uri() . '/assets/css/item-aside.css', array(), TAINACAN_INTERFACE_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_aside_enqueue' );
