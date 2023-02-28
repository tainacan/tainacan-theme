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
				<a href="<?php echo esc_url(get_author_posts_url( $comment->user_id )); ?>" class="text-black font-weight-bold bypostauthor">
					<?php wp_kses_post( comment_author( $comment->comment_ID ) ); ?>
				</a>
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
			<?php wp_kses_post( comment_text() ); ?>
			<?php wp_kses_post(
					comment_reply_link( array(
					'reply_text' => __( 'Reply', 'tainacan-interface' ),
					'depth'      => $depth,
					'max_depth'  => $args['max_depth'],
					'before'    => '<li class="ml-2 reply-link list-inline-item mr-3 mt-2">',
					'after'     => '</li>',
				))
			);
			wp_kses_post( edit_comment_link( __( 'Edit', 'tainacan-interface' ), '<li class="edit-link list-inline-item mr-3 mt-2">', '</li>' ) );
			?>
		</div>
	</div>
<?php }

function tainacan_wrap_comment( $content ) {
	return '<div class="comment-text">' . $content . '</div>';
}
add_filter( 'comment_text', 'tainacan_wrap_comment', 99 );


/**
 * Display date of post.
 */
if ( ! function_exists('tainacan_meta_date_author') ) {
	function tainacan_meta_date_author( $echo = true ) {
		$time = '<time class="entry-date published" datetime="%1$s">%2$s</time>';

		$time_string = sprintf( $time,
			esc_attr( get_the_date( 'c' ) ),
			get_the_date()
		);

		$string = $time_string;
		$string .= __( '&nbsp;by&nbsp;', 'tainacan-interface' );
		$string .= get_the_author_posts_link();

		$string = apply_filters( 'tainacan-meta-date-author', $string );

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
 * Retrieves an item adjacent link, either using WP strategy or Tainacan plugin tainacan_get_adjacent_items()
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
				'<a class="has-small-thumbnail" rel="prev" href="' . $previous_link_url . '">' . 
					'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp;<span>' . 
					$previous_title . '</span>' . 
					($previous_thumb !== false ? '<img src="' . $previous_thumb . '" alt="" />' : '') .
				'</a>';
			$next = $next_link_url === false ? '' :
				'<a class="has-small-thumbnail" rel="next" href="' . $next_link_url . '">' . 
					($next_thumb !== false ? '<img src="' . $next_thumb . '" alt="" />' : '') .
					'<span>' . $next_title . 
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
				'<a class="has-large-thumbnail" rel="prev" href="' . $previous_link_url . '">' .
					'<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-36px"></i>&nbsp;' .
					'<div><img src="' . $previous_thumb . '" alt=""><span>' . $previous_title . 
				'</span></div></a>';
			$next = $next_link_url === false ? '' :
				'<a class="has-large-thumbnail" rel="next" href="' . $next_link_url . '">' . 
					'<div><img src="' . $next_thumb . '" alt=""><span>' . $next_title . 
					'</span></div>&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-36px"></i>' .
				'</a>';
		break;
		
		default:
			$previous = $previous_link_url === false ? '' : '<a rel="prev" href="' . $previous_link_url . '"><i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp; <span>' . $previous_title . '</span></a>';
			$next = $next_link_url === false ? '' :'<a rel="next" href="' . $next_link_url . '"><span>' . $next_title . '</span> &nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i></a>';
	}

	return ['next' => $next, 'previous' => $previous];
}

/**
 * Retrieves the current items list source link
 */
function tainacan_get_source_item_list_url() {
	$args = $_GET;
	if (isset($args['ref'])) {
		$ref = $_GET['ref'];
		unset($args['pos']);
		unset($args['ref']);
		unset($args['source_list']);
		return $ref . '?' . http_build_query(array_merge($args));
	} else {
		unset($args['pos']);
		unset($args['ref']);
		unset($args['source_list']);
		return tainacan_the_collection_url() . '?' . http_build_query(array_merge($args));
	}
}