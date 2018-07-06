<?php if(have_posts()): ?>
    <div class="tainacan-list-post">
        <div class="row">
            <?php while(have_posts()): the_post(); ?>
                <div class="col col-md-3 mt-5">
                    <a class="tainacan-list-collection--grid" href="<?php the_permalink(); ?>">
                        <p class="tainacan-list-collection--grid-title text-black">     
                            <?php the_title(); ?>           
                        </p>
                        <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'collection-list-grid') ?>" class="img-fluid tainacan-list-collection--grid-img" alt="">  
                        <?php endif; ?>
                    </a>
                </div> 
            <?php endwhile; ?>
        </div>
    </div>

    <?php echo tainacan_pagination(3); ?>

<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>