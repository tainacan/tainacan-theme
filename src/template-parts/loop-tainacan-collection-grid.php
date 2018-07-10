<?php if(have_posts()): ?>
    <div class="tainacan-list-post text-center container px-md-0">
        <div class="row mx-auto">
            <?php while(have_posts()): the_post(); ?>
                <a class="tainacan-list-collection--grid-link mt-5 mr-3" href="<?php the_permalink(); ?>">
                    <div class="col tainacan-list-collection--grid px-0">
                        <p class="tainacan-list-collection--grid-title text-black text-left">
                            <?php the_title(); ?>           
                        </p>
                        <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-grid') ?>" class="img-fluid tainacan-list-collection--grid-img" alt="">  
                        <?php endif; ?>
                    </div> 
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <?php echo tainacan_pagination(3); ?>

<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>