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
global $current_user;
if (post_password_required()) {
    return;
} ?>
<div id="comments" class="border-top border-jelly-bean" style="border-width: 2px !important;">
	<!--show the form-->
	<?php if('open' == $post->comment_status) : ?>
		<div id="respond" class="clearfix mt-5 <?php if(!wp_is_mobile()) { ?>margin-two-column<?php } ?>">  
			<?php if(get_option('comment_registration') && !is_user_logged_in()) : ?>
				<p>
				<?php // translators: placeholders are the opening and closing a (link) tags ?>
				<?php printf( __( 'You must be %1$slogged%2$s in to post a comment.', 'tainacan-theme'), "<a href='" . esc_url( home_url() ) . "/wp-login.php'>", "</a>" ); ?>
				</p>        
			<?php else : ?>      
				<div for="comment" class="d-flex mb-2">
					<span class="text-jelly-bean title-leave"><?php _e('Leave your comment', 'tainacan-theme'); ?></span>
					<?php 
						if(is_user_logged_in()) { ?>
							<span class="text-oslo-gray authenticated ml-sm-3 d-none d-sm-block align-self-center">
							<?php _e('Authenticated as', 'tainacan-theme'); echo ': '; 
								echo '<a href="'. get_author_posts_url($current_user->ID) .'">' . $current_user->display_name . '</a>'; 
							}
						?>
					</span>
				</div>

				<?php 
					$user_link = sprintf('<a href="%1$s" class="font-weight-light">%2$s</a>', get_author_posts_url($current_user->ID), $current_user->display_name);
					$comment_args = array(
						'logged_in_as' => '<span class="text-oslo-gray authenticated d-inline d-sm-none mb-4">'.
						// translators: placeholder is the user name with link to its page
						sprintf( __('Authenticated as %1$s', 'tainacan-theme'), $user_link)
						.'</span>',
						'title_reply' => '',
						'title_reply_before' => '',
						'title_reply_after' => '',
						'comment_field' => sprintf('<div class="form-row"><div class="col-3 col-md-1 align-self-center form-row--avartar"><img src="%1$s" class="img-fluid rounded-circle"></div>', get_avatar_url($current_user->ID, array('size'=>60))).'<div class="col-9 col-md-11 form-row--textarea"><textarea name="comment" id="comment" tabindex="1" required class="form-control mt-2 mt-sm-0" rows="2"></textarea></div></div>',
						'cancel_reply_before' => '',
						'cancel_reply_after' => '',
						'class_submit' => 'btn btn-info bg-jungle-green align-self-center mt-3 float-right ml-auto comment-submit-link',
						'label_submit' =>  __('Send', 'tainacan-theme'),
					);
					comment_form($comment_args); 
				?>
			<?php endif; ?>
		</div>
		<?php if (have_comments()) : ?>
			<div class="row <?php if(!wp_is_mobile()) { ?>margin-two-column<?php } ?>">
				<div class="col mt-4 list-comments pl-md-0">
					<?php wp_list_comments('type=comment&callback=tainacan_Comments_Callback'); ?>
				</div>
			</div>

			<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
				<nav id="comment-nav-below" class="navigation" role="navigation">
					<div class="float-left">
						<?php previous_comments_link(__('Previous Comments', 'tainacan-theme'), 0 ); ?>
					</div>
					<div class="float-right">
						<?php next_comments_link(__('Next Comments', 'tainacan-theme'), 0 ); ?>
					</div>
				</nav>
			<?php endif; // check for comment navigation ?>

			<?php elseif (!comments_open() && '0' != get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
				<p class="nocomments"><?php _e('Comments are closed.', 'tainacan-theme'); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>