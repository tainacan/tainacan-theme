<?php

/**
 * Show list of comments.
 * callback: tainacan_comments_callback
 * param: @comment, @args, @depth
 */
function tainacan_comments_callback( $comment, $args, $depth ) {
	//get comment to determine type
	global $post;
	//var_dump($args);die;
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
			<?php printf( '<div class="col-sm-3 d-none d-lg-block pl-0 view-items">Viewing Items: %d to %d from %d</div>', $count_max + 1, $count_max + $wp_query->post_count, $wp_query->found_posts ); ?>
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

function tainacan_social_meta() {

	if ( is_single() || is_tax() || is_archive() ) {

		$logo = get_template_directory_uri() . '/assets/images/social-logo.png';
		$excerpt = get_bloginfo( 'description' );
		$url_src = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		global $wp;
			
		if ( is_post_type_archive() ) {
			$collection_id = tainacan_get_collection_id();
			if ($collection_id) {
				$title = tainacan_get_the_collection_name();
				$img_info = ( has_post_thumbnail( tainacan_get_collection_id() ) ) ? wp_get_attachment_image_src( get_post_thumbnail_id( tainacan_get_collection_id() ), 'full' ) : $logo;
				$url_src = home_url( $wp->request );
				$excerpt = tainacan_get_the_collection_description();
			}
		} elseif ( is_singular() ) {
			global $post;

			if ( !is_object($post) ) { return; }

			$title = get_the_title();
			$img_info = ( has_post_thumbnail( $post->ID ) ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ) : $logo;
			$url_src = get_permalink();
			$content = wp_trim_words( $post->post_content, 28, '[...]' );
			if ( $content ) {
				$excerpt = strip_tags( $content );
				$excerpt = str_replace( '', "'", $excerpt );
			} 
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			$tainacan_term = tainacan_get_term();
			
			$title = $term->name;
			$excerpt = $term->description;
			
			$url_src = get_term_link($term->term_id, $term->taxonomy);

			if ($tainacan_term) {
				$_term = new \Tainacan\Entities\Term( $tainacan_term );
				$img_id = $_term->get_header_image_id();
				if ($img_id) {
					$img_info = wp_get_attachment_image_src( $img_id, 'full' );
				}
			}
			
		} else {
			
			if ( is_day() ) :
				$title =  sprintf( __( 'Daily Archives: %s', 'tainacan-interface' ), get_the_date() );
			elseif ( is_month() ) :
				$title =  sprintf( __( 'Monthly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'tainacan-interface' ) ) );
			elseif ( is_year() ) :
				$title =  sprintf( __( 'Yearly Archives: %s', 'tainacan-interface' ), get_the_date( _x( 'Y', 'yearly archives date format', 'tainacan-interface' ) ) );
			elseif ( is_author() ) :
				$title = get_the_author();
			else :
				$title = get_the_archive_title();
			endif;
			
		}

		$image = array(
		   'url' => ( ! empty( $img_info[0] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) ? $img_info[0] : $logo,
		   'width' => ( ! empty( $img_info[1] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) ? $img_info[1] : 200,
		   'height' => ( ! empty( $img_info[2] ) && $img_info[1] >= 200 && $img_info[2] >= 200 ) ? $img_info[2] : 200,
		);

		?>
		<meta property="og:type" content="article"/>
		<meta property="og:title" content="<?php echo $title; ?>"/>
		<meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
		<meta property="og:description" content="<?php echo $excerpt; ?>"/>
		<meta property="og:url" content="<?php echo $url_src; ?>"/>
		<meta property="og:image" content="<?php echo $image['url']; ?>"/>
		<meta property="og:image:width" content="<?php echo $image['width']; ?>"/>
		<meta property="og:image:height" content="<?php echo $image['height']; ?>"/>


	<?php } else { return; } // End if().
}

add_action( 'wp_head', 'tainacan_social_meta', 5 );
