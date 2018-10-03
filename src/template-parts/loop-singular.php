<?php if(have_posts()): ?>
	<?php while(have_posts()): the_post(); ?>
	    <div class="tainacan-title">
	        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
	            <div class="list-inline mb-1">
	                <h3 class="list-inline-item text-midnight-blue font-weight-bold title-page">
	                    <?php the_title(); ?>
	                </h3>
	                <a href="javascript:history.go(-1)" class="list-inline-item float-right title-back"><?php _e('Back', 'tainacan-theme'); ?></a>
	            </div>
	        </div>
	    </div>
	    <div class="mt-3 tainacan-single-post">
	        <?php get_template_part('template-parts/single-post'); ?>
	    </div>
	<?php endwhile; ?>
<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>