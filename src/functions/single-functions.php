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
			echo get_avatar( $comment, 60, '', '', $arg );
		?>
		<div class="media-body">
			<h5 class="media-heading comment-author vcard ml-2">
				<a href="<?php echo get_author_posts_url( $comment->user_id ); ?>" class="text-black font-weight-bold bypostauthor">
					<?php comment_author( $comment->comment_ID ); ?>
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
			<?php comment_text(); ?>
			<?php comment_reply_link( array(
				'reply_text' => __( 'Reply', 'tainacan-interface' ),
				'depth'      => $depth,
				'max_depth'  => $args['max_depth'],
				'before'    => '<li class="ml-2 reply-link list-inline-item mr-3 mt-2">',
				'after'     => '</li>',
			));
			edit_comment_link( __( 'Edit', 'tainacan-interface' ), '<li class="edit-link list-inline-item mr-3 mt-2">', '</li>' );
			?>
			<?php if ( $args['has_children'] ) : ?>
				<p>
					<!-- <a href="#comments" class="hideChild-comments"><i class="material-icons align-top text-jelly-bean">arrow_drop_up</i><?php _e( 'Hide reply', 'tainacan-interface' ); ?></a> -->
				</p>
			<?php endif; ?>
		</div>
	</div>
<?php }

function tainacan_wrap_comment( $content ) {
	return '<div class="comment-text">' . $content . '</div>';
}
add_filter( 'comment_text', 'tainacan_wrap_comment', 99 );


if ( ! function_exists( 'tainacan_pagination' ) ) :
	function tainacan_pagination() {
		global $wp_query;
		$cur_posts = min( (int) $wp_query->get( 'posts_per_page' ), $wp_query->found_posts );
		$to_paged = max( (int) $wp_query->get( 'paged' ), 1 );
		$count_max = ( $to_paged - 1 ) * $cur_posts; ?>
		<div class="d-flex margin-pagination justify-content-between border-top pt-2">
			<div class="col-sm-3 d-none d-lg-block pl-0 view-items">
				<?php printf( __('Viewing Items: %d to %d from %d', 'tainacan-interface'), $count_max + 1, $count_max + $wp_query->post_count, $wp_query->found_posts ); ?>
			</div>
			<div class="col-sm-5 pr-md-0 justify-content-md-end">
				<?php the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => sprintf(
							'%s',
							'<i class="mdi mdi-menu-left"></i>'
						),
						'next_text' => sprintf(
							' %s',
							'<i class="mdi mdi-menu-right"></i>'
						),
						'screen_reader_text' => ' '
					)
				); ?>
			</div>
		</div>
	<?php }
endif;

/**
 * Display date of post.
 */
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
		echo $string;
	} else {
		return $string;
	}
}

// define the cancel_comment_reply_link callback
function tainacan_filter_cancel_comment_reply_link( $formatted_link, $link, $text ) {
	// make filter magic happen here...
	$formatted_link = '<a rel="nofollow" class="btn btn-info text-haiti bg-white border-oslo-gray align-self-center mt-3 ml-auto mr-1" id="cancel-comment-reply-link" href="' . $link . '" style="display:none;">' . $text . '</a>';
	return $formatted_link;
}
add_filter( 'cancel_comment_reply_link', 'tainacan_filter_cancel_comment_reply_link', 10, 3 );

