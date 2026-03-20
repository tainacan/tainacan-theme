<?php

/**
 * Show list of comments.
 * callback: tainacan_comments_callback
 * param: @comment, @args, @depth
 *
 * Fixed in 2.9.0: Corrected comment_author() double output bug,
 * fixed wp_kses_post on echo-functions, added proper escaping.
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
				<?php
					$comment_author_name = get_comment_author( $comment->comment_ID );
					if ( $comment_author_name ) : ?>
					<a href="<?php echo esc_url( get_author_posts_url( $comment->user_id ) ); ?>" class="text-black font-weight-bold bypostauthor">
						<?php echo esc_html( $comment_author_name ); ?>
					</a>
				<?php endif; ?>
				<p class="comment-time text-oslo-gray my-sm-1">
					<?php
						printf(
							/* translators: 1: Comment date, 2: comment time. Example: April 21st at 15:25*/
							esc_html__( '%1$s at %2$s.', 'tainacan-interface' ),
							esc_html( get_comment_date() ),
							esc_html( get_comment_time() )
						);
					?>
				</p>
			</h5>
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation">
					<?php esc_html_e( 'Your comment is awaiting moderation.','tainacan-interface' ); ?>
				</p>
			<?php endif; ?>
			<?php
				$comment_text = get_comment_text( $comment );
				if ( $comment_text ) {
					echo wp_kses_post( $comment_text );

					echo wp_kses_post(
						get_comment_reply_link( array(
							'reply_text' => __( 'Reply', 'tainacan-interface' ),
							'depth'      => $depth,
							'max_depth'  => $args['max_depth'],
							'before'    => '<li class="ml-2 reply-link list-inline-item mr-3 mt-2">',
							'after'     => '</li>',
						), $comment )
					);

					$edit_link = get_edit_comment_link( $comment );
					if ( $edit_link ) {
						echo '<li class="edit-link list-inline-item mr-3 mt-2"><a class="comment-edit-link" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'tainacan-interface' ) . '</a></li>';
					}
				}
			?>
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
			esc_html( get_the_date() )
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
	$formatted_link = '<a rel="nofollow" class="btn btn-info text-haiti bg-white border-oslo-gray align-self-center mt-3 ml-auto mr-1" id="cancel-comment-reply-link" href="' . esc_url( $link ) . '" style="display:none;">' . esc_html( $text ) . '</a>';
	return $formatted_link;
}
add_filter( 'cancel_comment_reply_link', 'tainacan_filter_cancel_comment_reply_link', 10, 3 );

/**
 * Retrieves an item adjacent link, either using WP strategy or Tainacan plugin tainacan_get_adjacent_items()
 *
 * Fixed in 2.9.0: Added esc_url() and esc_html() to all output.
 *
 * @param string $thumbnail: accepts 'small' and 'large', defaults to null
 */
function tainacan_get_adjacent_item_links($thumbnail = null) {

	if (function_exists('tainacan_get_adjacent_items') && isset($_GET['pos'])) {
		$adjacent_items = tainacan_get_adjacent_items();

		if (isset($adjacent_items['next'])) {
			$next_link_url = $adjacent_items['next']['url'];
			$next_title = $adjacent_items['next']['title'];
		} else {
			$next_link_url = false;
			$next_title = '';
		}
		if (isset($adjacent_items['previous'])) {
			$previous_link_url = $adjacent_items['previous']['url'];
			$previous_title = $adjacent_items['previous']['title'];
		} else {
			$previous_link_url = false;
			$previous_title = '';
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
			$previous_thumb = false;
			$next_thumb = false;

			if (function_exists('tainacan_get_adjacent_items') && isset($_GET['pos'])) {
				if (isset($adjacent_items['next']) && $adjacent_items['next']) {
					$next_thumb = isset($adjacent_items['next']['thumbnail']['tainacan-small'][0]) ? $adjacent_items['next']['thumbnail']['tainacan-small'][0] : false;
				}
				if (isset($adjacent_items['previous']) && $adjacent_items['previous']) {
					$previous_thumb = isset($adjacent_items['previous']['thumbnail']['tainacan-small'][0]) ? $adjacent_items['previous']['thumbnail']['tainacan-small'][0] : false;
				}
			} else {
				$previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-small' );
				$next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-small' );
			}

			// Creates the links with proper escaping
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
			$previous_thumb = '';
			$next_thumb = '';

			if (function_exists('tainacan_get_adjacent_items') && isset($_GET['pos'])) {
				if (isset($adjacent_items['next']) && $adjacent_items['next']) {
					$next_thumb = isset($adjacent_items['next']['thumbnail']['tainacan-medium'][0]) ? $adjacent_items['next']['thumbnail']['tainacan-medium'][0] : '';
				}
				if (isset($adjacent_items['previous']) && $adjacent_items['previous']) {
					$previous_thumb = isset($adjacent_items['previous']['thumbnail']['tainacan-medium'][0]) ? $adjacent_items['previous']['thumbnail']['tainacan-medium'][0] : '';
				}
			} else {
				//Get the thumnail url of the previous and next post
				$previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-medium' );
				$next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-medium' );
			}

			// Creates the links with proper escaping
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
 *
 * Fixed in 2.9.0: Added sanitization and validation to prevent open redirect.
 */
function tainacan_get_source_item_list_url() {
	// Sanitize all GET parameters
	$args = array_map( 'sanitize_text_field', wp_unslash( $_GET ) );

	if ( isset( $args['ref'] ) ) {
		$ref = $args['ref'];
		unset( $args['pos'] );
		unset( $args['ref'] );
		unset( $args['source_list'] );

		// Validate that ref is a safe redirect URL (internal only)
		$ref = wp_validate_redirect( $ref, home_url() );

		$query = http_build_query( $args );
		return esc_url( $ref . ( $query ? '?' . $query : '' ) );
	} else {
		unset( $args['pos'] );
		unset( $args['ref'] );
		unset( $args['source_list'] );

		$base_url = function_exists( 'tainacan_the_collection_url' ) ? tainacan_the_collection_url() : home_url();
		$query = http_build_query( $args );
		return esc_url( $base_url . ( $query ? '?' . $query : '' ) );
	}
}
