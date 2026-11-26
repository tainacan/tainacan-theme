<?php

/**
 * Show list of comments.
 * callback: tainacan_comments_callback
 * param: @comment, @args, @depth
 */
function tainacan_comments_callback( $comment, $args, $depth ) {
	//get comment to determine type
	global $post;
	$class_has = 'has-children media comment-' . $comment->comment_ID; ?>
	<div <?php comment_class( $args['has_children'] ? $class_has : 'parent media' ); ?> id="comment-<?php comment_ID() ?>">
		<?php
			$arg = array(
				'class' => 'img-fluid rounded-circle mr-sm-3',
			);
			echo wp_kses_post( get_avatar( $comment, 60, '', '', $arg ) );
		?>
		<div class="media-body">
			<h5 class="media-heading comment-author vcard ml-2">
				<?php if ( comment_author( $comment->comment_ID ) ) : ?>
					<a href="<?php echo esc_url(get_author_posts_url( $comment->user_id )); ?>" class="text-black font-weight-bold bypostauthor">
						<?php echo wp_kses_post( comment_author( $comment->comment_ID ) ); ?>
					</a>
				<?php endif; ?>
				<p class="comment-time text-oslo-gray my-sm-1">
					<?php
						printf(
							/* translators: 1: Comment date, 2: comment time. Example: April 21st at 15:25*/
							__( '%1$s at %2$s.', 'tainacan-interface' ),
							get_comment_date(),
							get_comment_time()
						);
					?>
				</p>
			</h5>
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation">
					<?php _e( 'Your comment is awaiting moderation.','tainacan-interface' ); ?>
				</p>
			<?php endif; ?>
			<?php if ( comment_text() ) {
				wp_kses_post( comment_text() );
			
				wp_kses_post(
					comment_reply_link( array(
						'reply_text' => __( 'Reply', 'tainacan-interface' ),
						'depth'      => $depth,
						'max_depth'  => $args['max_depth'],
						'before'    => '<li class="ml-2 reply-link list-inline-item mr-3 mt-2">',
						'after'     => '</li>',
					))
				);
				wp_kses_post( edit_comment_link( __( 'Edit', 'tainacan-interface' ), '<li class="edit-link list-inline-item mr-3 mt-2">', '</li>' ) );
			} ?>
		</div>
	</div>
<?php }

function tainacan_wrap_comment( $content ) {
	return '<div class="comment-text">' . $content . '</div>';
}
add_filter( 'comment_text', 'tainacan_wrap_comment', 99 );


/**
 * Display date and author of post.
 */
if ( ! function_exists('tainacan_meta_date_author') ) {
	function tainacan_meta_date_author( $echo = true ) {
		$string = tainacan_meta_date( false );
		$string .= tainacan_meta_author( false );

		$string = apply_filters( 'tainacan-meta-date-author', $string );

		if ( $echo ) {
			echo wp_kses_post($string);
		} else {
			return $string;
		}
	}
}

/**
 * Display date of post.
 */
if ( ! function_exists('tainacan_meta_date') ) {
	function tainacan_meta_date( $echo = true ) {
		$time = '<time class="entry-date published" datetime="%1$s">%2$s</time>';

		$time_string = sprintf( $time,
			esc_attr( get_the_date( 'c' ) ),
			get_the_date()
		);

		$string = $time_string;
		
		$string = apply_filters( 'tainacan-meta-date', $string );

		if ( $echo ) {
			echo wp_kses_post($string);
		} else {
			return $string;
		}
	}
}

/**
 * Display author of post.
 */
if ( ! function_exists('tainacan_meta_author') ) {
	function tainacan_meta_author( $echo = true ) {
		$string = __( '&nbsp;by&nbsp;', 'tainacan-interface' );
		$string .= get_the_author_posts_link();

		$string = apply_filters( 'tainacan-meta-author', $string );

		if ( $echo ) {
			echo wp_kses_post($string);
		} else {
			return $string;
		}
	}
}

// define the cancel_comment_reply_link callback
function tainacan_filter_cancel_comment_reply_link( $formatted_link, $link, $text ) {
	// make filter magic happen here...
	$formatted_link = '<a rel="nofollow" class="btn btn-info text-haiti bg-white border-oslo-gray align-self-center mt-3 ml-auto mr-1" id="cancel-comment-reply-link" href="' . $link . '" style="display:none;">' . $text . '</a>';
	return $formatted_link;
}
add_filter( 'cancel_comment_reply_link', 'tainacan_filter_cancel_comment_reply_link', 10, 3 );

/**
 * The collection of the list the visitor came from, when that list is one
 * collection (source_list=collection): source_entity_id, or else the
 * collection of the item, read as Theme_Helper::get_adjacent_items() reads
 * them (is_numeric, then absint), so both look at the same collection. Null
 * for a list of a term or of the whole repository, which spans collections.
 * Memoized per request.
 *
 * @return \Tainacan\Entities\Collection|null
 */
function tainacan_interface_navigation_collection() {
	static $memo = false;

	if ( false !== $memo ) {
		return $memo;
	}
	$memo        = null;
	$source_list = isset( $_GET['source_list'] ) && is_string( $_GET['source_list'] ) ? sanitize_key( wp_unslash( $_GET['source_list'] ) ) : '';
	if ( 'collection' !== $source_list ) {
		return $memo;
	}

	$source = isset( $_GET['source_entity_id'] ) && is_scalar( $_GET['source_entity_id'] ) ? wp_unslash( $_GET['source_entity_id'] ) : '';
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
			$section = \Tainacan\Repositories\Metadata_Sections::get_instance()->fetch( $section_id );
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
		// One query for all the collection posts; fetch() below reads them from the cache.
		_prime_post_caches( $ids, false, false );
		foreach ( $ids as $id ) {
			$collections[] = $repository->fetch( $id );
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
 * ask, read the way WP_Meta_Query and WP_Tax_Query read it. Clauses are
 * rebuilt from their known fields, so compare_key (which matches keys by LIKE)
 * never gets through, and a meta clause with no key is dropped.
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
 * Narrows the item query that Tainacan builds for the item navigation.
 *
 * Tainacan turns the whole query string into WP_Query arguments
 * (prepare_filters) without asking whether the visitor may see what is being
 * filtered: ?pos=0&status=draft shows the title and thumbnail of a draft, and
 * the presence of a "next" item answers whether an item has a given value in
 * a field the visitor cannot read. This runs on the mapped arguments: no
 * author, meta clauses only on readable metadata, tax clauses only on
 * readable taxonomies, meta_value only with a readable meta_key. The status
 * asked stays, so an author can walk the list of their own drafts: the flag
 * set here makes tainacan_interface_navigation_where() keep, in SQL, only the
 * items the visitor may see.
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
		$args['meta_query'] = tainacan_interface_public_query( $args['meta_query'], 'meta' );
	}
	if ( isset( $args['tax_query'] ) ) {
		$args['tax_query'] = tainacan_interface_public_query( $args['tax_query'], 'tax' );
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
 * Whether the visitor may navigate the list unfiltered: they manage Tainacan,
 * or they edit the items of others in the collection the list comes from
 * (source_entity_id, which may differ from the item's). Term and repository
 * lists span collections, so only managers pass there. Everyone else
 * navigates the narrowed list, where their own drafts still count. An
 * anonymous visitor always navigates the narrowed list, whatever capabilities
 * a site may grant it.
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
 * The previous and next items of the list the visitor came from, computed
 * once per request, with the query narrowed by
 * tainacan_interface_public_navigation_args() unless the visitor may see
 * everything.
 *
 * @return array|false { next, previous } as Tainacan returns them, or false.
 */
function tainacan_interface_adjacent_items() {
	static $memo = null;

	if ( null !== $memo ) {
		return $memo;
	}

	$memo = false;
	if ( ! function_exists( 'tainacan_get_adjacent_items' ) || ! isset( $_GET['pos'] ) ) {
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
 * The URL, if it is an http(s) address on this site; empty string otherwise.
 * Only control characters are stripped, because a Tainacan list address is
 * percent-encoded and carries JSON. The caller escapes with esc_url().
 *
 * @param string $url Candidate URL.
 * @return string
 */
function tainacan_interface_internal_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	$url = preg_replace( '/[\x00-\x1F\x7F]/', '', $url );
	if ( null === $url || '' === $url ) {
		return '';
	}

	if ( '/' === $url[0] && ( ! isset( $url[1] ) || '/' !== $url[1] ) ) {
		// A root-relative address (Tainacan's $route.href) already carries the install path.
		$home_parts = wp_parse_url( home_url() );
		$url        = $home_parts['scheme'] . '://' . $home_parts['host'] . ( isset( $home_parts['port'] ) ? ':' . $home_parts['port'] : '' ) . $url;
	}

	// Parsed and compared, never resolved: wp_http_validate_url() looks up the
	// host of any address that is not this site's, so a ?ref= to another host
	// made the server run a DNS query while rendering the page.
	$parts = wp_parse_url( $url );
	$home  = wp_parse_url( home_url() );
	if ( ! is_array( $parts ) || ! is_array( $home ) || ! isset( $parts['scheme'], $parts['host'], $home['host'] ) ) {
		return '';
	}
	if ( ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
		return '';
	}
	$port      = isset( $parts['port'] ) ? (int) $parts['port'] : 0;
	$home_port = isset( $home['port'] ) ? (int) $home['port'] : 0;
	if ( strtolower( $parts['host'] ) !== strtolower( $home['host'] ) || $port !== $home_port ) {
		return '';
	}

	return $url;
}

/**
 * An address for a CSS url('…') in a style attribute, before esc_url().
 *
 * The function esc_url() keeps the single quote (as &#039;, which the HTML
 * parser turns back into a quote inside the attribute) and the parentheses,
 * and either one ends the CSS string or the url(). They are percent-encoded
 * here, with the double quote and the backslash, which leaves the address
 * the same.
 *
 * @param string $url Address.
 * @return string Unescaped address; escape with esc_url() on output.
 */
function tainacan_interface_css_url( $url ) {
	return str_replace( array( "'", '"', '(', ')', '\\' ), array( '%27', '%22', '%28', '%29', '%5C' ), (string) $url );
}

/**
 * Retrieves an item adjacent link, either using WP strategy or Tainacan plugin tainacan_get_adjacent_items()
 * 
 * @param string $thumbnail: accepts 'small' and 'large', defaults to null
 */
function tainacan_get_adjacent_item_links($thumbnail = null) {

	if (function_exists('tainacan_get_adjacent_items') && isset($_GET['pos'])) {
		$adjacent_items = tainacan_interface_adjacent_items();

		if (isset($adjacent_items['next'])) {
			$next_link_url = $adjacent_items['next']['url'];
			$next_title = $adjacent_items['next']['title'];
		} else {
			$next_link_url = false;
		}
		if (isset($adjacent_items['previous'])) {
			$previous_link_url = $adjacent_items['previous']['url'];
			$previous_title = $adjacent_items['previous']['title'];
		} else {
			$previous_link_url = false;
		}

	} else {
		//Get the links to the Previous and Next Post
		$previous_link_url = get_permalink( get_previous_post() );
		$next_link_url = get_permalink( get_next_post() );

		//Get the title of the previous post and next post
		$previous_title = get_the_title( get_previous_post() );
		$next_title = get_the_title( get_next_post() );
	}

	$previous = '';
	$next = '';

	switch ($thumbnail) {

		case 'small':
			//Get the thumnail url of the previous and next post
			if (function_exists('tainacan_get_adjacent_items') && isset($_GET['pos'])) {
				if ($adjacent_items['next']) {
					$next_thumb = isset($adjacent_items['next']['thumbnail']['tainacan-small'][0]) ? $adjacent_items['next']['thumbnail']['tainacan-small'][0] : false;
				}
				if ($adjacent_items['previous']) {
					$previous_thumb = isset($adjacent_items['previous']['thumbnail']['tainacan-small'][0]) ? $adjacent_items['previous']['thumbnail']['tainacan-small'][0] : false;
				}
			} else {
				$previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-small' );
				$next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-small' );
			}

			// Creates the links
			$previous = $previous_link_url === false ? '' :
				'<a class="has-small-thumbnail" rel="prev" href="' . esc_url( $previous_link_url ) . '">' . 
					'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp;<span>' . 
					esc_html( $previous_title ) . '</span>' . 
					($previous_thumb !== false ? '<img src="' . esc_url( $previous_thumb ) . '" alt="" />' : '') .
				'</a>';
			$next = $next_link_url === false ? '' :
				'<a class="has-small-thumbnail" rel="next" href="' . esc_url( $next_link_url ) . '">' . 
					($next_thumb !== false ? '<img src="' . esc_url( $next_thumb ) . '" alt="" />' : '') .
					'<span>' . esc_html( $next_title ) . 
					'</span>&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>' .
				'</a>';
		break;

		case 'large':

			if (function_exists('tainacan_get_adjacent_items') && isset($_GET['pos'])) {
				if ($adjacent_items['next']) {
					$next_thumb = $adjacent_items['next']['thumbnail']['tainacan-medium'][0];
				}
				if ($adjacent_items['previous']) {
					$previous_thumb = $adjacent_items['previous']['thumbnail']['tainacan-medium'][0];
				}
			} else {
				//Get the thumnail url of the previous and next post
				$previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-medium' );
				$next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-medium' );
			}
			
			// Creates the links
			$previous = $previous_link_url === false ? '' :
				'<a class="has-large-thumbnail" rel="prev" href="' . esc_url( $previous_link_url ) . '">' .
					'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-36px"></i>&nbsp;' .
					'<div><img src="' . esc_url( $previous_thumb ) . '" alt=""><span>' . esc_html( $previous_title ) . 
				'</span></div></a>';
			$next = $next_link_url === false ? '' :
				'<a class="has-large-thumbnail" rel="next" href="' . esc_url( $next_link_url ) . '">' . 
					'<div><img src="' . esc_url( $next_thumb ) . '" alt=""><span>' . esc_html( $next_title ) . 
					'</span></div>&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-36px"></i>' .
				'</a>';
		break;
		
		default:
			$previous = $previous_link_url === false ? '' : '<a rel="prev" href="' . esc_url( $previous_link_url ) . '"><i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp; <span>' . esc_html( $previous_title ) . '</span></a>';
			$next = $next_link_url === false ? '' :'<a rel="next" href="' . esc_url( $next_link_url ) . '"><span>' . esc_html( $next_title ) . '</span> &nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i></a>';
	}

	return ['next' => $next, 'previous' => $previous];
}

/**
 * The address of the list the visitor came from, for the "back to the list"
 * link.
 *
 * The address of the list is ?ref=, which the Tainacan lists send with the
 * item link, when it points to this site. Its own query is the state of the
 * list (the Vue lists put every filter, the search, the order and the page in
 * it) and is kept as it is: the arguments of the item address are not merged
 * into it, because with plain permalinks they also name the item (?p=,
 * ?tnc_col_1_item=) and the list's own &paged=2 would be replaced by the
 * item's. Without ?ref= (the grid of the theme sends none), the link goes to
 * the collection, with the list arguments that came with the item link.
 *
 * Either way, what names the item or the navigation is left out (a ?p= would
 * send the visitor back to the item), and the filters follow the rule of the
 * navigation: unless the visitor may see the whole list, no status, no
 * author, and only filters and an order on metadata and taxonomies they may
 * read.
 *
 * @return string Unescaped URL; escape with esc_url() on output.
 */
function tainacan_get_source_item_list_url() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only; validated by tainacan_interface_internal_url(): http(s), this host and port, no control characters.
	$ref  = isset( $_GET['ref'] ) && is_string( $_GET['ref'] ) ? tainacan_interface_internal_url( wp_unslash( $_GET['ref'] ) ) : '';
	$base = '' !== $ref ? $ref : ( function_exists( 'tainacan_get_the_collection_url' ) ? (string) tainacan_get_the_collection_url() : home_url( '/' ) );

	$base = explode( '#', $base, 2 )[0];
	$path = explode( '?', $base, 2 )[0];
	$args = array();
	wp_parse_str( (string) wp_parse_url( $base, PHP_URL_QUERY ), $args );
	if ( '' === $ref ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only; control characters stripped below, the values only rebuild a URL escaped on output (the list parses its own JSON parameters, so they are not sanitized as text).
		$current = wp_unslash( $_GET );
		if ( is_array( $current ) ) {
			array_walk_recursive(
				$current,
				static function ( &$value ) {
					$value = is_scalar( $value ) ? preg_replace( '/[\x00-\x1F\x7F]/', '', (string) $value ) : '';
				}
			);
			$args = array_merge( $args, $current );
		}
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
 * author, metakey) narrowed by the rule of the navigation, unless the visitor
 * may see the whole list. The list itself is rendered by Tainacan, which does
 * not apply this rule, so the theme does not link to it with a status or
 * author filter, or with a filter or order on a metadatum or taxonomy the
 * visitor may not read.
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
