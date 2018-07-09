<?php if(have_posts()): ?>
    <div class="tainacan-list-post">
        <div class="row justify-content-center">
            <?php while(have_posts()): the_post(); ?>
            <a class="tainacan-list-collection--card-link mt-5" href="<?php the_permalink(); ?>">
                <div class="card tainacan-list-collection--card border-0 mx-1">
                    <div class="card-header text-black border-0">
                        <?php the_title(); ?>
                    </div>
                    <div class="row ">
                        <div class="col col-md-6">
                            <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-card') ?>" class="card-img-top-left tainacan-list-collection--card-img rounded-0" alt="">  
                            <?php endif; ?>
                        </div>
                        <div class="col col-md-6 px-3">
                            <div class="card-block p-0">
                                <p class="card-text text-oslo-gray"><?php echo get_the_excerpt(); ?></p>
                                <p class="card-text text-oslo-gray"> 
                                    <?php _e('Create by: ');?> <?php the_author(); ?><br>
                                    <?php _e('Date: ');?> <?php tainacan_post_date(); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>

    <?php echo tainacan_pagination(3); ?>

<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>