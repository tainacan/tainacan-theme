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
 * Whether the visitor may filter a list by this metadata key: the core title
 * or description keys, or the ID of a metadatum the visitor may read, outside
 * a private metadata section, and whose parent (a compound metadatum) passes
 * the same test.
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
			$section = \Tainacan\Repositories\Metadata_Sections::get_instance()->fetch( $section_id );
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
 * ask, read the way WP_Meta_Query and WP_Tax_Query read it. Clauses are
 * rebuilt from their known fields, so compare_key (which matches keys by LIKE)
 * never gets through, and a meta clause with no key is dropped.
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
 * Narrows the item query that Tainacan builds for the item navigation.
 *
 * Tainacan turns the whole query string into WP_Query arguments
 * (prepare_filters) without asking whether the visitor may see what is being
 * filtered: ?pos=0&status=draft shows the title and thumbnail of a draft, and
 * the presence of a "next" item answers whether an item has a given value in
 * a field the visitor cannot read. This runs on the mapped arguments: no
 * status, no author, meta clauses only on readable metadata, tax clauses only
 * on readable taxonomies, meta_value only with a readable meta_key.
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
 * Whether the visitor may navigate the list unfiltered: they manage Tainacan,
 * or they edit the collection the list comes from (source_entity_id, which may
 * differ from the item's). Term and repository lists span collections, so
 * only managers pass there.
 *
 * @return bool
 */
function tainacan_interface_navigation_unfiltered() {
	if ( current_user_can( 'manage_tainacan' ) ) {
		return true;
	}
	$source_list = isset( $_GET['source_list'] ) && is_string( $_GET['source_list'] ) ? sanitize_key( wp_unslash( $_GET['source_list'] ) ) : '';
	if ( 'collection' !== $source_list ) {
		return false;
	}

	// Read as Theme_Helper::get_adjacent_items() reads it (is_numeric, then absint), so both look at the same collection.
	$source = isset( $_GET['source_entity_id'] ) && is_scalar( $_GET['source_entity_id'] ) ? wp_unslash( $_GET['source_entity_id'] ) : '';
	if ( is_numeric( $source ) && class_exists( '\Tainacan\Repositories\Collections' ) ) {
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( absint( $source ) );
	} else {
		$collection = function_exists( 'tainacan_get_collection' ) ? tainacan_get_collection() : null;
	}
	$caps = $collection instanceof \Tainacan\Entities\Collection ? $collection->get_items_capabilities() : null;

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
	}
	$memo = tainacan_get_adjacent_items();
	if ( $narrow ) {
		remove_filter( 'tainacan-api-prepare-items-args', 'tainacan_interface_public_navigation_args', PHP_INT_MAX );
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

	if ( ! wp_http_validate_url( $url ) ) {
		return '';
	}

	$host = wp_parse_url( $url, PHP_URL_HOST );
	$home = wp_parse_url( home_url(), PHP_URL_HOST );

	if ( ! is_string( $host ) || ! is_string( $home ) || strtolower( $host ) !== strtolower( $home ) ) {
		return '';
	}

	return $url;
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
 * Retrieves the current items list source link
 */
function tainacan_get_source_item_list_url() {
	$args = wp_unslash( $_GET );
	unset($args['pos']);
	unset($args['ref']);
	unset($args['source_list']);

	$ref = isset($_GET['ref']) ? tainacan_interface_internal_url( wp_unslash( $_GET['ref'] ) ) : '';
	if ($ref !== '') {
		// Query arguments of the list address itself (plain permalinks route by them) come first.
		$ref_query = wp_parse_url( $ref, PHP_URL_QUERY );
		$ref_args  = array();
		if ( is_string( $ref_query ) ) {
			wp_parse_str( $ref_query, $ref_args );
		}
		$args = array_merge( $ref_args, $args );
		$base = strtok($ref, '?#');
	} else {
		$base = function_exists('tainacan_get_the_collection_url') ? tainacan_get_the_collection_url() : home_url( '/' );
	}
	return $base . '?' . http_build_query($args);
}
