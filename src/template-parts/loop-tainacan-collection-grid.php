<?php if(have_posts()): ?>
    <div class="tainacan-list-post text-center container px-md-0 mt-5">
        <div class="tainacan-list-collection--container-grid justify-content-center">
            <?php while(have_posts()): the_post(); ?>
                <a class="tainacan-list-collection--grid-link" href="<?php the_permalink(); ?>">
                    <div class="tainacan-list-collection--grid">
                        <p class="tainacan-list-collection--grid-title text-black text-left p-3 mb-0">
                            <?php the_title(); ?>           
                        </p>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'tainacan-medium') ?>" class="img-fluid tainacan-list-collection--grid-img" alt="">  
                        <?php else : ?>
                            <div class="image-placeholder">
                                <h4>
                                    <?php echo tainacan_get_initials(get_the_title()); ?>
                                </h4>
                            </div>
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