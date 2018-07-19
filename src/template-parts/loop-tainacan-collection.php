<?php if(have_posts()): ?>
    <div class="tainacan-list-post container">
        <div class="row mx-auto">
            <?php while(have_posts()): the_post(); ?>
                <div class="col col-lg-4 tainacan-list-collection--card mt-5">
                    <a class="tainacan-list-collection--card-link mx-1" href="<?php the_permalink(); ?>">
                        <h5 class="tainacan-list-collection--title text-black"><?php the_title(); ?></h5>
                        <div class="media">
                            <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-card') ?>" class="tainacan-list-collection--card-img rounded-0 align-self-center mr-3" alt="">  
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
                            <div class="media-body text-oslo-gray">
                                <p><?php echo get_the_excerpt(); ?></p>
                                <p> 
                                    <?php _e('Create by: ');?> <?php the_author(); ?><br>
                                    <?php _e('Date: ');?> <?php tainacan_post_date(); ?>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php echo tainacan_pagination(3); ?>

<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>