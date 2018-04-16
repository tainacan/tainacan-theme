<?php if(have_posts()): ?>
    <div class="tainacan-title">
        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
            <ul class="list-inline mb-1">
                <li class="list-inline-item text-midnight-blue font-weight-bold">
                    <?php 
                        if(is_home()) echo 'Blog'; 
                        elseif(is_search()){ 
                            _e('Search Results for', 'tainacan-theme'); 
                            echo ' ' . the_search_query();
                        }
                        elseif(is_archive()){
                            echo ' ' . get_the_archive_title();
                        }
                    ?>
                </li>
                <li class="list-inline-item float-right"><a href="javascript:history.go(-1)"><?php _e('Back'); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="mt-5 tainacan-list-post">
        <?php while(have_posts()): 
            the_post();
            //List Post
            
            get_template_part('template-parts/list-post');
            
			
        endwhile; ?>
    </div>
    <?php echo tainacan_pagination(3); ?>
<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>