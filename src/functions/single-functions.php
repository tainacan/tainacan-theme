<?php

/**
 * Show list of comments.
 * callback: tainacan_Comments_Callback
 * param: @comment, @args, @depth
 */
function tainacan_Comments_Callback($comment, $args, $depth) {
    //get comment to determine type
    $GLOBALS['comment'] = $comment;
    global $post; 
    //var_dump($args);die;
    $class_has = "has-children media comment-". $comment->comment_ID; ?>
    <div <?php comment_class( $args['has_children'] ? $class_has : 'parent media' ); ?> id="comment-<?php comment_ID() ?>">
        <?php 
            $arg = array('class' => 'img-fluid rounded-circle mr-sm-3', );
            echo get_avatar($comment, 60, '', '', $arg);
        ?>
        <div class="media-body">
            <h5 class="media-heading comment-author vcard ml-2">
                <a href="<?php echo get_author_posts_url($comment->user_id); ?>" class="text-black font-weight-bold">
                    <?php comment_author( $comment->comment_ID ); ?>
                </a>
                <?php 
                    printf('<p class="comment-time text-oslo-gray my-sm-1">%s às %s.</p>', get_comment_date(), get_comment_time()); 
                ?>
            </h5>
            <?php if ('0' == $comment->comment_approved) : ?>
                <p class="comment-awaiting-moderation">
                    <?php _e('Your comment is awaiting moderation.','tainacan-theme'); ?>
                </p>
            <?php endif; ?>
            <?php comment_text(); ?>
            <?php comment_reply_link( array(
                        'reply_text' => __('Reply'),
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                        'before'    => '<li class="ml-2 reply-link list-inline-item mr-3 mt-2">',
                        'after'     => '</li>'
                    )
                ); 
                edit_comment_link( __( 'Edit' ), '<li class="edit-link list-inline-item mr-3 mt-2">', '</li>' );
            ?>
            <?php if($args['has_children']) : ?>
                <p>
                    <!-- <a href="#comments" class="hideChild-comments"><i class="material-icons align-top text-jelly-bean">arrow_drop_up</i><?php _e('Hide reply', 'tainacan-theme'); ?></a> -->
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php }

function wrap_Comment($content){
    return "<div class='comment-text'>". $content ."</div>";
}
add_filter( 'comment_text', 'wrap_Comment', 99);

/**
 * Display date of post.
 */
//if ( ! function_exists( 'tainacan_post_date' ) ) {
	function tainacan_post_date() {
		//if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
			if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time> <time class="updated" datetime="%3$s">(updated %4$s)</time>';
			}
			$time_string = sprintf( $time_string,
				esc_attr( get_the_date( 'c' ) ),
				get_the_date(),
				esc_attr( get_the_modified_date( 'c' ) ),
				get_the_modified_date()
			);
			echo $time_string;
		//}
	}
//}

// define the cancel_comment_reply_link callback 
function filter_cancel_comment_reply_link( $formatted_link, $link, $text ) { 
    // make filter magic happen here... 
    $formatted_link = '<a rel="nofollow" class="btn btn-info bg-white border-oslo-gray align-self-center mt-3 ml-1" id="cancel-comment-reply-link" href="'.$link.'" style="display:none;">'.$text.'</a>';
    return $formatted_link; 
}
add_filter( 'cancel_comment_reply_link', 'filter_cancel_comment_reply_link', 10, 3 ); 