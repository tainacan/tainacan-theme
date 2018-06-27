<?php if(have_posts()): ?>
    <?php if(!is_archive('tainacan-collection')) : ?>
        <div class="tainacan-title">
            <div class="border-bottom border-jelly-bean tainacan-title-page">
                <ul class="list-inline mb-1">
                    <li class="list-inline-item text-midnight-blue font-weight-bold title-page">
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
                    <li class="list-inline-item float-right title-back"><a href="javascript:history.go(-1)"><?php _e('Back'); ?></a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <div class="mt-5 tainacan-list-post">
        <?php if(is_archive('tainacan-collection')) : ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col"><?php _e('Miniature'); ?></th>
                        <th scope="col"><?php _e('Title'); ?></th>
                        <th scope="col"><?php _e('Author'); ?></th>
                        <th scope="col">#</th>
                    </tr>
                </thead>
                <tbody>
        <?php endif; ?>
        <?php while(have_posts()): 
            the_post();
            //List Post
            if(is_archive('tainacan-collection')) :
                get_template_part('template-parts/list-post', 'tainacan-collection');
            else :
                get_template_part('template-parts/list-post');
            endif;
        endwhile; ?>
        <?php if(is_archive('tainacan-collection')) : ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php echo tainacan_pagination(3); ?>
<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>