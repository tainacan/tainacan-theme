<?php if(have_posts()): ?>
    <div class="mt-5 tainacan-list-post">
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Miniature'); ?></th>
                    <th scope="col"><?php _e('Title'); ?></th>
                    <th scope="col"><?php _e('Author'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while(have_posts()): the_post(); ?>
                    
                    <tr class="tainacan-list-collection" data-href="<?php the_permalink(); ?>">
                        <td class="collection-miniature">
                            <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                                <a href="<?php the_permalink(); ?>" class="">
                                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-table') ?>" class="img-fluid rounded-circle" alt="">
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="collection-title"><a href="<?php the_permalink(); ?>" class=""><?php the_title(); ?></a></td>
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