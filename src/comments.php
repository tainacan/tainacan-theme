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
		<div id="respond" class="clearfix mt-3">  
			<?php if(get_option('comment_registration') && !is_user_logged_in()) : ?>
				<p>
				<?php printf( __( 'You must be %slogged%s in to post a comment.', 'tainacan-theme'), "<a href='" . get_option('home') . "/wp-login.php'>", "</a>" ); ?>
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

				<form autocomplete="off" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="form-comment" class="form-inline clearfix">
					<div class="form-row w-100 mx-0">
						<div class="<?php if(is_user_logged_in()) { ?>col col-md-1<?php }else{ ?>col<?php } ?> pl-0 pb-3 pb-md-0">
							<?php 
								comment_id_fields(); 
								$args = array('class' => 'img-fluid rounded-circle mr-sm-3', );
								echo get_avatar( $current_user->ID, 60, '', $current_user->display_name, $args );
							?>
							<span class="text-oslo-gray authenticated ml-3 <?php if(is_user_logged_in()) { ?>d-inline d-sm-none<?php } ?>">
								<?php 
									_e('Authenticated as', 'tainacan-theme'); echo ': '; 
									if(is_user_logged_in()) { 
										echo '<a href="'. get_author_posts_url($current_user->ID) .'" class="font-weight-light">' . $current_user->display_name . '</a>'; 
									}else{
										_e('Anonymous', 'tainacan-theme');
									}
								?>
							</span>
						</div>
						<div class="col-xs-11 col-md-11 pr-0">
							<?php if(!is_user_logged_in()) : ?>
								<div class="form-group form-inline mb-3">                            
									<label for="author" class="font-weight-bold mr-3"><?php _e('Name'); ?> </label>
									<input type="text" class="form-control" id="author" name="author" style="flex: 1 1 auto;">
								</div>
								<div class="form-group form-inline mb-3">                            
									<label for="auemailthor" class="font-weight-bold mr-3"><?php _e('E-mail'); ?> </label>
									<input type="email" class="form-control" id="email" name="email" style="flex: 1 1 auto;">
								</div>
								<div class="form-group form-inline mb-3">                            
									<label for="email" class="font-weight-bold mr-3"><?php _e('Website'); ?> </label>
									<input type="url" class="form-control" id="url" name="url" style="flex: 1 1 auto;">
								</div>
							<?php endif;?>
							<textarea name="comment" id="comment" tabindex="1" required class="form-control mt-2 mt-sm-0 w-100" rows="2"></textarea>
						</div>
					</div>
					<?php cancel_comment_reply_link(__( 'Cancel', 'tainacan-theme' )); ?>
					<button id="submit" class="btn btn-info bg-jungle-green align-self-center mt-3 float-right" type="submit" name="submit"><?php _e('Send', 'tainacan-theme') ?></button>
					<?php do_action('comment_form', $post->ID); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php if (have_comments()) : ?>
			<div class="row">
				<div class="col col-sm-10 mt-4 list-comments">
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