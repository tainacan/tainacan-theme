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
		$string = esc_html__( '&nbsp;by&nbsp;', 'tainacan-interface' ) . get_the_author_posts_link();
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
 * Whether the visitor may filter or sort a list by this metadata key: one of
 * the core title or description keys, or the ID of a metadatum the visitor may
 * read, outside a private metadata section, and whose parent (a compound
 * metadatum) passes the same test. Memoized per request.
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
		return true;
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
		// A repository metadatum has one section per collection: every one of them must be readable.
		$section_ids = array_map( 'intval', array_filter( (array) $metadatum->get_metadata_section_id(), 'is_numeric' ) );
		_prime_post_caches( $section_ids, false, false );
		foreach ( $section_ids as $section_id ) {
			$section = \Tainacan\Repositories\Metadata_Sections::get_instance()->fetch( $section_id ); // tnc-check-ignore TNC-P02: fetch by ID builds the entity from the post primed just above; no query per section.
			if ( $section instanceof \Tainacan\Entities\Metadata_Section && ! $section->can_read() ) {
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
	$fields = 'meta' === $kind ? array( 'key', 'value', 'compare', 'type' ) : array( 'taxonomy', 'terms', 'field', 'operator', 'include_children' );
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
 * on the arguments already mapped, exactly as they reach WP_Query: no status
 * (drafts), no author, meta clauses only on metadata the visitor may read and
 * tax clauses only on taxonomies they may read, and meta_value only with a
 * readable meta_key. Hooked only around the navigation call, so Tainacan's own
 * REST routes are left as they are.
 *
 * @param array $args Mapped WP_Query arguments.
 * @return array
 */
function tainacan_interface_public_navigation_args( $args ) {
	if ( ! is_array( $args ) ) {
		return $args;
	}

	unset( $args['post_status'], $args['author'], $args['author_name'] );

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
 * Whether the visitor may navigate the list unfiltered: they edit the
 * collection the list actually comes from (source_entity_id, which may differ
 * from the item's), or they manage Tainacan. A list of a term or of the whole
 * repository spans collections, so only managers pass there.
 *
 * @return bool
 */
function tainacan_interface_navigation_unfiltered() {
	if ( current_user_can( 'manage_tainacan' ) ) {
		return true;
	}
	if ( 'collection' !== tainacan_interface_query_arg( 'source_list' ) ) {
		return false;
	}

	$source = tainacan_interface_query_arg( 'source_entity_id' );
	if ( ctype_digit( $source ) && class_exists( '\Tainacan\Repositories\Collections' ) ) {
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $source );
	} else {
		$collection = function_exists( 'tainacan_get_collection' ) ? tainacan_get_collection() : null;
	}
	$caps = $collection instanceof \Tainacan\Entities\Collection ? $collection->get_items_capabilities() : null;

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
	}
	$memo = tainacan_get_adjacent_items();
	if ( $narrow ) {
		remove_filter( 'tainacan-api-prepare-items-args', 'tainacan_interface_public_navigation_args', PHP_INT_MAX );
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
 * ?ref= is accepted only when it points to this site: the original theme
 * printed it as it came, so a crafted link sent the "back to the list" button
 * to another site and could inject markup into the breadcrumb.
 *
 * @return string Unescaped URL; escape with esc_url() on output.
 */
function tainacan_get_source_item_list_url() {
	$base = tainacan_interface_query_url( 'ref' );

	if ( '' === $base ) {
		$base = function_exists( 'tainacan_get_the_collection_url' ) ? (string) tainacan_get_the_collection_url() : home_url( '/' );
	}

	$base = strtok( $base, '?#' );
	$args = tainacan_interface_list_query_args();

	return empty( $args ) ? $base : $base . '?' . http_build_query( $args );
}
