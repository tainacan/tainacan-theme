<?php if(have_posts()): ?>
    <div class="mt-5 tainacan-list-post table-responsive">
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Miniature'); ?></th>
                    <th scope="col"><?php _e('Title'); ?></th>
                    <th scope="col"><?php _e('Description'); ?></th>
                    <th scope="col"><?php _e('Date'); ?></th>
                    <th scope="col"><?php _e('Author'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while(have_posts()): the_post(); ?>
                    <tr class="tainacan-list-collection" onclick="location.href='<?php the_permalink(); ?>'">
                        <td class="collection-miniature">
                            <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-table') ?>" class="img-fluid rounded-circle" alt="">
                            <?php else : ?>
                                <div class="image-placeholder">
                                    <h4>
                                    <?php 
                                        $get_title =  get_the_title(); 
                                        $ltr_group = substr($get_title, 0, 1);
                                        echo $ltr_group;
                                    ?>
                                    </h4>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="collection-title"><?php the_title(); ?></td>
                        <td class="collection-description text-oslo-gray"><?php the_excerpt(); ?></td>
                        <td class="collection-date text-oslo-gray"><?php echo get_the_date(); ?></td>
                        <td class="collection-create-by text-oslo-gray"><?php _e('Created by', 'tainacan-theme'); ?> <?php the_author_posts_link(); ?></td>
                    </tr>
                <?php endwhile; ?>
        
            </tbody>
        </table>
        
    </div>

    <?php echo tainacan_pagination(3); ?>

<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>