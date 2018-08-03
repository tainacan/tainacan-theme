<?php if(have_posts()): ?>
    <div class="tainacan-title">
        <div class="border-bottom border-jelly-bean tainacan-title-page">
            <ul class="list-inline mb-1 d-flex">
                <li class="list-inline-item text-midnight-blue font-weight-bold title-page">
                    <?php 
                        if(is_home()) _e('Posts of blog', 'tainacan-theme'); 
                        elseif(is_search()){ 
                            _e('Search Results for', 'tainacan-theme');
                            echo ' "' , get_search_query(), '"';
                        }
                        elseif(is_archive()){
                            echo ' ' . get_the_archive_title();
                        }
                    ?>
                </li>
                <li class="list-inline-item float-right title-back align-self-end ml-auto"><a href="javascript:history.go(-1)"><?php _e('Back'); ?></a></li>
            </ul>
        </div>
    </div>
    
    <div class="mt-5 tainacan-list-post margin-md-two-column">
        
        <?php while(have_posts()): ?>
            <?php the_post(); ?>
            
            <?php get_template_part('template-parts/list-post'); ?>
            
        <?php endwhile; ?>
        
    </div>

    <?php echo tainacan_pagination(3); ?>
    
<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>