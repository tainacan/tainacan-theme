<?php
/**
 * Single post and single item helpers: comments, post meta line, item
 * navigation and the "back to the list" link.
 *
 * @package tainacan-interface
 */

/**
 * Show list of comments.
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    wp_list_comments() arguments.
 * @param int        $depth   Depth.
 */
function tainacan_comments_callback( $comment, $args, $depth ) {
	$class_has = 'has-children media comment-' . $comment->comment_ID;
	?>
	<div <?php comment_class( $args['has_children'] ? $class_has : 'parent media' ); ?> id="comment-<?php comment_ID(); ?>">
		<?php
		echo wp_kses_post( get_avatar( $comment, 60, '', '', array( 'class' => 'img-fluid rounded-circle mr-sm-3' ) ) );
		?>
		<div class="media-body">
			<h5 class="media-heading comment-author vcard ml-2">
				<?php if ( $comment->user_id ) : ?>
					<a href="<?php echo esc_url( get_author_posts_url( $comment->user_id ) ); ?>" class="text-black font-weight-bold bypostauthor">
						<?php echo esc_html( get_comment_author( $comment ) ); ?>
					</a>
				<?php else : ?>
					<span class="text-black font-weight-bold"><?php echo esc_html( get_comment_author( $comment ) ); ?></span>
				<?php endif; ?>
				<p class="comment-time text-oslo-gray my-sm-1">
					<?php
					printf(
						/* translators: 1: Comment date, 2: comment time. Example: April 21st at 15:25*/
						esc_html__( '%1$s at %2$s.', 'tainacan-interface' ),
						esc_html( get_comment_date( '', $comment ) ),
						esc_html( get_comment_time() )
					);
					?>
				</p>
			</h5>
			<?php if ( '0' === (string) $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation">
					<?php esc_html_e( 'Your comment is awaiting moderation.', 'tainacan-interface' ); ?>
				</p>
			<?php endif; ?>
			<?php
			comment_text( $comment );
			comment_reply_link(
				array_merge(
					$args,
					array(
						'reply_text' => __( 'Reply', 'tainacan-interface' ),
						'depth'      => $depth,
						'max_depth'  => $args['max_depth'],
						'before'     => '<li class="ml-2 reply-link list-inline-item mr-3 mt-2">',
						'after'      => '</li>',
					)
				)
			);
			edit_comment_link( __( 'Edit', 'tainacan-interface' ), '<li class="edit-link list-inline-item mr-3 mt-2">', '</li>' );
			?>
		</div>
	</div>
	<?php
}

/**
 * Wraps the comment text.
 *
 * @param string $content Comment text (already filtered by WordPress).
 * @return string
 */
function tainacan_wrap_comment( $content ) {
	return '<div class="comment-text">' . $content . '</div>';
}
add_filter( 'comment_text', 'tainacan_wrap_comment', 99 );

if ( ! function_exists( 'tainacan_meta_date_author' ) ) {
	/**
	 * Display date and author of post.
	 *
	 * @param bool $echo Print (true) or return.
	 * @return string|void
	 */
	function tainacan_meta_date_author( $echo = true ) {
		$string = tainacan_meta_date( false ) . tainacan_meta_author( false );
		$string = apply_filters( 'tainacan-meta-date-author', $string );

		if ( ! $echo ) {
			return $string;
		}
		echo wp_kses_post( $string );
	}
}

if ( ! function_exists( 'tainacan_meta_date' ) ) {
	/**
	 * Display date of post.
	 *
	 * @param bool $echo Print (true) or return.
	 * @return string|void
	 */
	function tainacan_meta_date( $echo = true ) {
		$string = sprintf(
			'<time class="entry-date published" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);
		$string = apply_filters( 'tainacan-meta-date', $string );

		if ( ! $echo ) {
			return $string;
		}
		echo wp_kses_post( $string );
	}
}

if ( ! function_exists( 'tainacan_meta_author' ) ) {
	/**
	 * Display author of post.
	 *
	 * @param bool $echo Print (true) or return.
	 * @return string|void
	 */
	function tainacan_meta_author( $echo = true ) {
		// A post without an author (made by a script, or whose author was removed) shows the date alone.
		$link   = get_the_author_posts_link();
		$string = '' === trim( wp_strip_all_tags( $link ) ) ? '' : esc_html__( '&nbsp;by&nbsp;', 'tainacan-interface' ) . $link;
		$string = apply_filters( 'tainacan-meta-author', $string );

		if ( ! $echo ) {
			return $string;
		}
		echo wp_kses_post( $string );
	}
}

/**
 * Cancel comment reply link with the theme classes.
 *
 * @param string $formatted_link Link HTML.
 * @param string $link           Link URL.
 * @param string $text           Link text.
 * @return string
 */
function tainacan_filter_cancel_comment_reply_link( $formatted_link, $link, $text ) {
	return '<a rel="nofollow" class="btn btn-info text-haiti bg-white border-oslo-gray align-self-center mt-3 ml-auto mr-1" id="cancel-comment-reply-link" href="' . esc_url( $link ) . '" style="display:none;">' . esc_html( $text ) . '</a>';
}
add_filter( 'cancel_comment_reply_link', 'tainacan_filter_cancel_comment_reply_link', 10, 3 );

/**
 * The collection of the list the visitor came from, when that list is one
 * collection (source_list=collection): source_entity_id, or else the
 * collection of the item, read the way Tainacan reads them in
 * get_adjacent_items(). Null for a list of a term or of the whole repository,
 * which spans collections. Memoized per request.
 *
 * @return \Tainacan\Entities\Collection|null
 */
function tainacan_interface_navigation_collection() {
	static $memo = false;

	if ( false !== $memo ) {
		return $memo;
	}
	$memo = null;
	if ( 'collection' !== tainacan_interface_query_arg( 'source_list' ) ) {
		return $memo;
	}

	$source = tainacan_interface_query_arg( 'source_entity_id' );
	if ( is_numeric( $source ) && class_exists( '\Tainacan\Repositories\Collections' ) ) {
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( absint( $source ) );
	} else {
		$collection = function_exists( 'tainacan_get_collection' ) ? tainacan_get_collection() : null;
	}
	$memo = $collection instanceof \Tainacan\Entities\Collection ? $collection : null;
	return $memo;
}

/**
 * Whether the visitor may filter or sort a list by this metadata key: the ID
 * of a metadatum the visitor may read, outside a private metadata section of
 * the list's collection, and whose parent (a compound metadatum) passes the
 * same test; or one of the core keys, when the metadata they stand for pass
 * it. Memoized per request.
 *
 * @param mixed $key Meta key.
 * @return bool
 */
function tainacan_interface_queryable_metadatum( $key ) {
	static $memo = array();

	if ( ! is_scalar( $key ) ) {
		return false;
	}
	$key = (string) $key;
	if ( 'tainacan_core_title' === $key || 'tainacan_core_description' === $key ) {
		return tainacan_interface_queryable_core_key( $key );
	}
	if ( ! ctype_digit( $key ) || ! class_exists( '\Tainacan\Repositories\Metadata' ) ) {
		return false;
	}
	if ( array_key_exists( $key, $memo ) ) {
		return $memo[ $key ];
	}
	$memo[ $key ] = false; // A parent loop ends here.

	$metadatum = \Tainacan\Entities\Metadatum::get_post_type() === get_post_type( (int) $key ) ? \Tainacan\Repositories\Metadata::get_instance()->fetch( (int) $key ) : null;
	$ok        = $metadatum instanceof \Tainacan\Entities\Metadatum && $metadatum->can_read();

	if ( $ok && class_exists( '\Tainacan\Repositories\Metadata_Sections' ) ) {
		// A repository metadatum has one section per collection. A list of one
		// collection only asks about the items of that collection, so only its
		// section counts there; a list of a term or of the repository spans
		// collections, so every section must be readable.
		$collection  = tainacan_interface_navigation_collection();
		$section_ids = array_map( 'intval', array_filter( (array) $metadatum->get_metadata_section_id(), 'is_numeric' ) );
		_prime_post_caches( $section_ids, false, false );
		foreach ( $section_ids as $section_id ) {
			$section = \Tainacan\Repositories\Metadata_Sections::get_instance()->fetch( $section_id ); // tnc-check-ignore TNC-P02: fetch by ID builds the entity from the post primed just above; no query per section.
			if ( ! $section instanceof \Tainacan\Entities\Metadata_Section ) {
				continue;
			}
			if ( $collection && (int) $section->get_collection_id() !== (int) $collection->get_id() ) {
				continue;
			}
			if ( ! $section->can_read() ) {
				$ok = false;
				break;
			}
		}
	}
	if ( $ok && (int) $metadatum->get_parent() > 0 ) {
		$ok = tainacan_interface_queryable_metadatum( (int) $metadatum->get_parent() );
	}

	$memo[ $key ] = $ok;
	return $ok;
}

/**
 * Whether the visitor may filter or sort a list by tainacan_core_title or
 * tainacan_core_description. Tainacan replaces these keys only later, inside
 * Items::fetch() (parse_core_metadata_for_advanced_search()), by the title or
 * description metadatum of each collection of the list, with the same compare
 * and value. So the key passes only when each of those metadata passes the
 * test of a metadatum ID: a private description, or one in a private section,
 * cannot be asked through its alias. The collections are the ones Tainacan
 * expands: the list's collection, or, for a list of a term or of the
 * repository, every collection whose item post type is registered, as
 * Items::fetch() gathers them when no collection is given (all of them, not a
 * page, and whether or not the visitor may read the collection). Memoized per
 * request.
 *
 * @param string $key tainacan_core_title or tainacan_core_description.
 * @return bool
 */
function tainacan_interface_queryable_core_key( $key ) {
	static $memo = array();

	if ( isset( $memo[ $key ] ) ) {
		return $memo[ $key ];
	}

	$collection  = tainacan_interface_navigation_collection();
	$collections = $collection ? array( $collection ) : array();
	if ( ! $collection && class_exists( '\Tainacan\Repositories\Collections' ) ) {
		$repository = \Tainacan\Repositories\Collections::get_instance();
		$ids        = array();
		foreach ( get_post_types() as $post_type ) {
			if ( 0 === strpos( $post_type, \Tainacan\Entities\Collection::$db_identifier_prefix ) ) {
				$ids[] = (int) $repository->get_id_by_db_identifier( $post_type );
			}
		}
		$ids = array_filter( $ids );
		_prime_post_caches( $ids, false, false );
		foreach ( $ids as $id ) {
			$collections[] = $repository->fetch( $id ); // tnc-check-ignore TNC-P02: fetch by ID builds the entity from the post primed just above; no query per collection.
		}
	}

	$ok = true;
	foreach ( $collections as $entity ) {
		if ( ! $entity instanceof \Tainacan\Entities\Collection ) {
			continue;
		}
		$metadatum = 'tainacan_core_title' === $key ? $entity->get_core_title_metadatum() : $entity->get_core_description_metadatum();
		if ( $metadatum instanceof \Tainacan\Entities\Metadatum && ! tainacan_interface_queryable_metadatum( $metadatum->get_id() ) ) {
			$ok = false;
			break;
		}
	}

	$memo[ $key ] = $ok;
	return $ok;
}

/**
 * Whether the visitor may filter a list by this taxonomy: a Tainacan taxonomy
 * whose entity they may read.
 *
 * @param mixed $taxonomy Taxonomy slug (tnc_tax_<id>).
 * @return bool
 */
function tainacan_interface_queryable_taxonomy( $taxonomy ) {
	if ( ! is_string( $taxonomy ) || 1 !== preg_match( '/^tnc_tax_(\d+)$/', $taxonomy, $match ) || ! class_exists( '\Tainacan\Repositories\Taxonomies' ) ) {
		return false;
	}
	if ( \Tainacan\Entities\Taxonomy::get_post_type() !== get_post_type( (int) $match[1] ) ) {
		return false;
	}
	$entity = \Tainacan\Repositories\Taxonomies::get_instance()->fetch( (int) $match[1] );
	return $entity instanceof \Tainacan\Entities\Taxonomy && $entity->can_read();
}

/**
 * A WP_Query meta_query or tax_query with only the clauses the visitor may
 * ask. Read the way WP_Meta_Query and WP_Tax_Query read it: a non-array entry
 * is ignored, an array with a clause field is a clause, any other array is a
 * nested group. Clauses are rebuilt from their known fields, so a field such
 * as compare_key (which matches keys by LIKE) never gets through, and a meta
 * clause with no key (the value under any key) is dropped.
 *
 * @param mixed  $query meta_query or tax_query.
 * @param string $kind  'meta' or 'tax'.
 * @return array
 */
function tainacan_interface_public_query( $query, $kind ) {
	$fields = 'meta' === $kind ? array( 'key', 'value', 'compare', 'type' ) : array( 'taxonomy', 'terms', 'field', 'operator', 'compare', 'include_children' );
	$out    = array();

	foreach ( is_array( $query ) ? $query : array() as $index => $clause ) {
		if ( 'relation' === $index ) {
			$out[ $index ] = $clause;
			continue;
		}
		if ( ! is_array( $clause ) ) {
			continue;
		}
		$is_clause = 'meta' === $kind
			? isset( $clause['key'] ) || isset( $clause['value'] ) || isset( $clause['compare'] ) || isset( $clause['type'] ) || isset( $clause['compare_key'] ) || isset( $clause['type_key'] )
			: isset( $clause['taxonomy'] ) || isset( $clause['terms'] );
		if ( $is_clause ) {
			$allowed = 'meta' === $kind
				? isset( $clause['key'] ) && tainacan_interface_queryable_metadatum( $clause['key'] )
				: isset( $clause['taxonomy'] ) && tainacan_interface_queryable_taxonomy( $clause['taxonomy'] );
			if ( $allowed ) {
				$out[ $index ] = array_intersect_key( $clause, array_flip( $fields ) );
			}
			continue;
		}
		$group = tainacan_interface_public_query( $clause, $kind );
		if ( array_diff_key( $group, array( 'relation' => true ) ) ) {
			$out[ $index ] = $group;
		}
	}
	return $out;
}

/**
 * Narrows the item query that Tainacan builds for the navigation.
 *
 * Tainacan turns the whole query string into WP_Query arguments
 * (prepare_filters) without asking whether the visitor may see what is being
 * filtered, and the presence of a "next" item answers the question. This runs
 * on the arguments already mapped, exactly as they reach WP_Query: no
 * author, meta clauses only on metadata the visitor may read and tax clauses
 * only on taxonomies they may read, and meta_value only with a readable
 * meta_key.
 * The status asked stays, so an author can walk the list of their own drafts:
 * the flag set here makes tainacan_interface_navigation_where() keep, in SQL,
 * only the items the visitor may see. Hooked only around the navigation call,
 * so Tainacan's own REST routes are left as they are.
 *
 * @param array $args Mapped WP_Query arguments.
 * @return array
 */
function tainacan_interface_public_navigation_args( $args ) {
	if ( ! is_array( $args ) ) {
		return $args;
	}

	unset( $args['author'], $args['author_name'] );
	$args['tainacan_interface_visible_only'] = true;

	if ( isset( $args['meta_query'] ) ) {
		$args['meta_query'] = tainacan_interface_public_query( $args['meta_query'], 'meta' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- narrows the meta query Tainacan already built from the list filters; no clause is added.
	}
	if ( isset( $args['tax_query'] ) ) {
		$args['tax_query'] = tainacan_interface_public_query( $args['tax_query'], 'tax' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- narrows the tax query Tainacan already built from the list filters; no clause is added.
	}
	if ( ! isset( $args['meta_key'] ) || ! tainacan_interface_queryable_metadatum( $args['meta_key'] ) ) {
		unset( $args['meta_key'], $args['meta_value'], $args['meta_value_num'], $args['meta_compare'] );
	}

	return $args;
}

/**
 * The items the visitor may see, added to the WHERE of the navigation query
 * flagged by tainacan_interface_public_navigation_args(): items in a public
 * status (published), the visitor's own drafts, pending and private items
 * (never their trash or auto-drafts), and private
 * items when a logged-in visitor may read the private items of the list's
 * collection. An anonymous visitor gets only public items, whoever the author
 * of an item is.
 *
 * @param string   $where WHERE clause.
 * @param WP_Query $query The query.
 * @return string
 */
function tainacan_interface_navigation_where( $where, $query ) {
	global $wpdb;

	if ( ! $query instanceof WP_Query || ! $query->get( 'tainacan_interface_visible_only' ) ) {
		return $where;
	}

	$statuses   = array_values( get_post_stati( array( 'public' => true ) ) );
	$collection = tainacan_interface_navigation_collection();
	$caps       = $collection ? $collection->get_items_capabilities() : null;
	if ( $caps && ! empty( $caps->read_private_posts ) && is_user_logged_in() && current_user_can( $caps->read_private_posts ) ) {
		$statuses[] = 'private';
	}

	$visible = $wpdb->prepare( "{$wpdb->posts}.post_status IN (" . implode( ', ', array_fill( 0, count( $statuses ), '%s' ) ) . ')', $statuses );
	$user    = get_current_user_id();
	if ( $user > 0 ) {
		// Only the author's work in progress: never trash or auto-draft, even with status=any or status=trash.
		$own     = array( 'draft', 'pending', 'private' );
		$visible = '(' . $visible . $wpdb->prepare( " OR ({$wpdb->posts}.post_author = %d AND {$wpdb->posts}.post_status IN (" . implode( ', ', array_fill( 0, count( $own ), '%s' ) ) . '))', array_merge( array( $user ), $own ) ) . ')';
	}

	return $where . ' AND ' . $visible;
}

/**
 * Whether the visitor may navigate the list unfiltered: they edit the items of
 * others in the collection the list actually comes from (source_entity_id,
 * which may differ from the item's), or they manage Tainacan. A list of a
 * term or of the whole repository spans collections, so only managers pass
 * there. Everyone else navigates the narrowed list, where their own drafts
 * still count. An anonymous visitor always navigates the narrowed list,
 * whatever capabilities a site may grant it.
 *
 * @return bool
 */
function tainacan_interface_navigation_unfiltered() {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	if ( current_user_can( 'manage_tainacan' ) ) {
		return true;
	}

	$collection = tainacan_interface_navigation_collection();
	$caps       = $collection ? $collection->get_items_capabilities() : null;

	return $caps && ! empty( $caps->edit_others_posts ) && current_user_can( $caps->edit_others_posts );
}

/**
 * The previous and next items of the list the visitor came from.
 *
 * Computed once per request (the header, the breadcrumb and the navigation
 * part all ask). Tainacan builds the query from the whole query string and
 * turns ?status= into post_status, which WP_Query does not check against the
 * visitor: ?pos=0&status=draft would show the title and thumbnail of a draft
 * to anyone. Unless the visitor may see everything, the query is narrowed by
 * tainacan_interface_public_navigation_args() for the duration of the call.
 *
 * @return array|false { next, previous } as Tainacan returns them, or false.
 */
function tainacan_interface_adjacent_items() {
	static $memo = null;

	if ( null !== $memo ) {
		return $memo;
	}

	$memo = false;
	if ( ! function_exists( 'tainacan_get_adjacent_items' ) || ! ctype_digit( tainacan_interface_query_arg( 'pos' ) ) ) {
		return $memo;
	}

	$narrow = ! tainacan_interface_navigation_unfiltered();
	if ( $narrow ) {
		add_filter( 'tainacan-api-prepare-items-args', 'tainacan_interface_public_navigation_args', PHP_INT_MAX );
		add_filter( 'posts_where', 'tainacan_interface_navigation_where', PHP_INT_MAX, 2 );
	}
	$memo = tainacan_get_adjacent_items();
	if ( $narrow ) {
		remove_filter( 'tainacan-api-prepare-items-args', 'tainacan_interface_public_navigation_args', PHP_INT_MAX );
		remove_filter( 'posts_where', 'tainacan_interface_navigation_where', PHP_INT_MAX );
	}

	return $memo;
}

/**
 * Retrieves an item adjacent link, either using WP strategy or Tainacan plugin tainacan_get_adjacent_items().
 *
 * Every piece is escaped where the HTML is built: the result goes to echo in
 * three templates.
 *
 * @param string|null $thumbnail Accepts 'small' and 'large', defaults to null.
 * @return array{next: string, previous: string}
 */
function tainacan_get_adjacent_item_links( $thumbnail = null ) {
	$size     = 'large' === $thumbnail ? 'tainacan-medium' : 'tainacan-small';
	$adjacent = tainacan_interface_adjacent_items();
	$links    = array();

	foreach ( array( 'previous', 'next' ) as $side ) {
		$links[ $side ] = array(
			'url'   => '',
			'title' => '',
			'thumb' => '',
		);

		if ( is_array( $adjacent ) ) {
			if ( empty( $adjacent[ $side ] ) ) {
				continue;
			}
			$entry          = $adjacent[ $side ];
			$links[ $side ] = array(
				'url'   => isset( $entry['url'] ) ? $entry['url'] : '',
				'title' => isset( $entry['title'] ) ? $entry['title'] : '',
				'thumb' => isset( $entry['thumbnail'][ $size ][0] ) ? $entry['thumbnail'][ $size ][0] : '',
			);
		} else {
			$post = 'previous' === $side ? get_previous_post() : get_next_post();
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$links[ $side ] = array(
				'url'   => get_permalink( $post ),
				'title' => get_the_title( $post ),
				'thumb' => (string) get_the_post_thumbnail_url( $post, $size ),
			);
		}
	}

	$out = array(
		'next'     => '',
		'previous' => '',
	);

	foreach ( $links as $side => $link ) {
		if ( '' === $link['url'] ) {
			continue;
		}

		$rel   = 'previous' === $side ? 'prev' : 'next';
		$arrow = '<i class="tainacan-icon tainacan-icon-arrow' . ( 'previous' === $side ? 'left' : 'right' ) . ' tainacan-icon-' . ( 'large' === $thumbnail ? '36px' : '30px' ) . '"></i>';
		$title = '<span>' . esc_html( wp_strip_all_tags( $link['title'] ) ) . '</span>';
		$img   = '' !== $link['thumb'] ? '<img src="' . esc_url( $link['thumb'] ) . '" alt="" />' : '';
		$href  = esc_url( $link['url'] );

		if ( 'small' === $thumbnail ) {
			$inner = 'previous' === $side ? $arrow . '&nbsp;' . $title . $img : $img . $title . '&nbsp;' . $arrow;
			$class = ' class="has-small-thumbnail"';
		} elseif ( 'large' === $thumbnail ) {
			$box   = '<div>' . $img . $title . '</div>';
			$inner = 'previous' === $side ? $arrow . '&nbsp;' . $box : $box . '&nbsp;' . $arrow;
			$class = ' class="has-large-thumbnail"';
		} else {
			$inner = 'previous' === $side ? $arrow . '&nbsp; ' . $title : $title . ' &nbsp;' . $arrow;
			$class = '';
		}

		$out[ $side ] = '<a' . $class . ' rel="' . $rel . '" href="' . $href . '">' . $inner . '</a>';
	}

	return $out;
}

/**
 * Retrieves the current items list source link.
 *
 * The address of the list is ?ref=, which the Tainacan lists send with the
 * item link, when it points to this site (the original theme printed it as it
 * came, so a crafted link sent the "back to the list" button to another site
 * and could inject markup into the breadcrumb). Its own query is the state of
 * the list and is kept: with plain permalinks it is also what names the list
 * (?post_type=tnc_col_1_item). Without ?ref= (the grid of the theme sends
 * none), the link goes to the collection, with the list arguments that came
 * with the item link.
 *
 * Either way, what names the item or the navigation is left out (a ?p= would
 * send the visitor back to the item), and the filters follow the rule of the
 * navigation: unless the visitor may see the whole list, no status, and only
 * filters and an order on metadata and taxonomies they may read.
 *
 * @return string Unescaped URL; escape with esc_url() on output.
 */
function tainacan_get_source_item_list_url() {
	$ref  = tainacan_interface_query_url( 'ref' );
	$base = '' !== $ref ? $ref : ( function_exists( 'tainacan_get_the_collection_url' ) ? (string) tainacan_get_the_collection_url() : home_url( '/' ) );

	$base = explode( '#', $base, 2 )[0];
	$path = explode( '?', $base, 2 )[0];
	$args = array();
	wp_parse_str( (string) wp_parse_url( $base, PHP_URL_QUERY ), $args );
	if ( '' === $ref ) {
		$args = array_merge( $args, tainacan_interface_list_query_args() );
	}

	$args = tainacan_interface_public_list_args( tainacan_interface_list_args_without_item( $args ) );

	return empty( $args ) ? $path : $path . '?' . http_build_query( $args );
}

/**
 * List arguments without the ones that name the item being viewed or the
 * navigation: the position and source of the list, the post ID and slug
 * variables (and the query variable of the item's post type, which names it
 * with plain permalinks), preview and page of the post.
 *
 * @param array $args Query arguments.
 * @return array
 */
function tainacan_interface_list_args_without_item( $args ) {
	$keys = array( 'pos', 'ref', 'source_list', 'source_entity_id', 'p', 'page_id', 'name', 'pagename', 'attachment', 'attachment_id', 'preview', 'preview_id', 'preview_nonce', 'page', 'cpage' );
	$type = get_post_type_object( (string) get_post_type( get_queried_object_id() ) );
	if ( $type && is_string( $type->query_var ) && '' !== $type->query_var ) {
		$keys[] = $type->query_var;
	}
	return array_diff_key( $args, array_flip( $keys ) );
}

/**
 * List arguments (as a Tainacan list sends them: status, metaquery, taxquery,
 * author, metakey) narrowed by the rule of the navigation, unless the visitor may see
 * the whole list. The list itself is rendered by Tainacan, which does not
 * apply this rule, so the theme does not link to it with a status filter or a
 * filter or order on a metadatum or taxonomy the visitor may not read.
 *
 * @param array $args Query arguments.
 * @return array
 */
function tainacan_interface_public_list_args( $args ) {
	if ( tainacan_interface_navigation_unfiltered() ) {
		return $args;
	}

	unset( $args['status'], $args['post_status'], $args['author'], $args['author_name'] );
	foreach ( array(
		'metaquery' => 'meta',
		'taxquery'  => 'tax',
	) as $name => $kind ) {
		if ( ! isset( $args[ $name ] ) ) {
			continue;
		}
		$args[ $name ] = tainacan_interface_public_query( $args[ $name ], $kind );
		if ( ! array_diff_key( $args[ $name ], array( 'relation' => true ) ) ) {
			unset( $args[ $name ] );
		}
	}
	// Same rule as meta_key in the navigation: Tainacan copies metavalue to
	// meta_value even with no key, and WP_Meta_Query then matches the value in
	// any field, private ones included. So the value and the compare go with
	// the key, and only a readable key keeps them.
	if ( ! isset( $args['metakey'] ) || ! tainacan_interface_queryable_metadatum( $args['metakey'] ) ) {
		unset( $args['metakey'], $args['metavalue'], $args['metavaluenum'], $args['metacompare'] );
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array( 'meta_value', 'meta_value_num' ), true ) ) {
			unset( $args['orderby'] );
		}
	}
	return $args;
}

/**
 * Order of the document, the attachments and the record on the item page,
 * from the "Layout" setting (site or collection). Since 4.9 the layout holds
 * the order too; 4.9.0 converted the earlier separate "order" setting.
 *
 * @return string One of document-attachments-metadata, document-metadata-attachments, metadata-document-attachments.
 */
function tainacan_interface_item_sections_order() {
	$orders = array(
		'stacked'        => 'document-attachments-metadata',
		'stacked-split'  => 'document-metadata-attachments',
		'stacked-record' => 'metadata-document-attachments',
		'media-left'     => 'document-attachments-metadata',
		'media-right'    => 'metadata-document-attachments',
	);
	$layout = (string) tainacan_interface_opt( 'item_layout' );
	return isset( $orders[ $layout ] ) ? $orders[ $layout ] : $orders['stacked'];
}
