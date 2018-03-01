<?php
/**
 * Tema para exibir Comments.
 *
 * A área da página que contém os comentários atuais
 * E o formulário de comentário. A exibição real dos comentários é
 * Manipulado por um callback em tainacan_Comments_Callback() que é
 * Localizado no arquivo functions.php.
 *
 * @package WordPress
 * @subpackage Tainacan Theme
 */

if (post_password_required()) {
    return;
} ?>
<div class="row">
	<!-- Container -->
	<div class="col-xs-12 col-md-12" id="comments">
		<!--show the form-->
		<h2 class="title-amount text-uppercase"><i class="fa fa-comments-o" aria-hidden="true"></i> <?php comments_number(__('There are no comments', 'tainacan-theme'), __('1 Comment','tainacan-theme'), __('% Comments','tainacan-theme') );?></h2>
		<?php if('open' == $post->comment_status) : ?>
			<div id="respond" class="clearfix">        
			    <?php if(get_option('comment_registration') && !$user_ID) : ?>
					<p>
					<?php printf( __( 'You must be %slogged%s in to post a comment.', 'tainacan-theme'), "<a href='" . get_option('home') . "/wp-login.php'>", "</a>" ); ?>
					</p>        
			    <?php else : ?>
				<label for="comment" class="d-block"><?php _e('Leave your comment', 'tainacan-theme'); ?>:</label>
			    <form autocomplete="off" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="form-comment" class="form-inline px-sm-5">
					<div class="row mx-sm-auto">
						<div class="form-group">
							<?php comment_id_fields(); ?>
							<textarea name="comment" id="comment" tabindex="1" required class="form-control mr-sm-3" rows="2" placeholder="<?php _e('Enter your comment here.', 'tainacan-theme'); ?>"></textarea>
						</div>
						<button id="submit" class="btn btn-info bg-jungle-green align-self-center" type="submit" name="submit"><?php _e('Send', 'tainacan-theme') ?></button>
					</div>
					
					<?php cancel_comment_reply_link('Cancel'); ?>
					<?php do_action('comment_form', $post->ID); ?>
			    </form>
			    <?php endif; ?>
			</div>
		<?php endif; ?>

	    <?php if (have_comments()) : ?>

            <?php wp_list_comments(array('callback' => 'tainacan_Comments_Callback')); ?>

	        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
	            <nav id="comment-nav-below" class="navigation" role="navigation">
	                <div class="nav-previous">
	                    <?php previous_comments_link( _e('&larr; Newer', 'tainacan-theme')); ?>
	                </div>
	                <div class="nav-next">
	                    <?php next_comments_link(_e('Older &rarr;', 'tainacan-theme')); ?>
	                </div>
	            </nav>
	        <?php endif; // check for comment navigation ?>

	        <?php elseif (!comments_open() && '0' != get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
	            <p class="nocomments"><?php _e('Comments are closed.', 'tainacan-theme'); ?></p>
	    <?php endif; ?>
	</div>
</div>