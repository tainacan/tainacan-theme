<?php if(have_posts()): ?>
    <div class="mt-5 tainacan-list-post table-responsive">
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col"><?php _e('Title', 'tainacan-theme'); ?></th>
                    <th scope="col"><?php _e('Description', 'tainacan-theme'); ?></th>
                    <!-- <th scope="col"><?php //_e('Date'); ?></th>
                    <th scope="col"><?php //_e('Author'); ?></th> -->
                </tr>
            </thead>
            <tbody>
                <?php while(have_posts()): the_post(); ?>
                    <tr class="tainacan-list-collection" onclick="location.href='<?php the_permalink(); ?>'">
                        <td class="collection-miniature">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'tainacan-small') ?>" class="img-fluid rounded-circle" alt="">
                            <?php else : ?>
                                <div class="image-placeholder">
                                    <h4>
                                    <?php echo tainacan_get_initials(get_the_title(), true); ?>
                                    </h4>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="collection-title text-black"><?php the_title(); ?></td>
                        <td class="collection-description text-oslo-gray"><?php the_excerpt(); ?></td>
                        <!-- <td class="collection-date text-oslo-gray"><?php //echo get_the_date(); ?></td>
                        <td class="collection-create-by text-oslo-gray"><?php //_e('Created by', 'tainacan-theme'); ?> <?php the_author_posts_link(); ?></td> -->
                    </tr>
                <?php endwhile; ?>
        
            </tbody>
        </table>
        
    </div>

    <?php echo tainacan_pagination(3); ?>

<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>