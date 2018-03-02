<?php

/**
 * Show list of comments.
 * callback: tainacan_Comments_Callback
 * param: @comment, @args, @depth
 */
if (!function_exists('tainacan_Comments_Callback')) :
    function tainacan_Comments_Callback($comment, $args, $depth) {
        global $current_user;
        $GLOBALS['comment'] = $comment;
        
        $user_id = $comment->user_id;
        $get_user = get_userdata($user_id);
        ?>
        <div class="media mt-4" id="comment-<?php comment_ID(); ?>">
            <?php $args = array('class' => 'img-fluid rounded-circle mr-sm-3', ); ?>
            <a href="<?php echo get_author_posts_url($comment->user_id); ?>"><?php echo get_avatar( $comment, 60, '', '', $args ); ?></a>
            <div class="media-body">
                <h5 class="mt-0">
                    <?php        
                        if ($get_user && $user_id) {
                            $user=get_userdata($user_id);
                            echo '<a href="'.get_author_posts_url($user_id).'" class="text-black font-weight-bold">'.$user->display_name.'</a>';
                        } else {
                            comment_author_link();
                        }
                        printf('<p class="comment-time text-oslo-gray my-sm-1">%s às %s.</p>', get_comment_date(), get_comment_time()); 
                    ?>
                </h5>
                <?php comment_text(); ?>
                <p>
                    <?php comment_reply_link(); ?>
                </p>
            </div>
        </div>
    <?php }
endif;