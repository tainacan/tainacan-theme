<div class="row blog-post mb-3">
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="col-xs-12 col-md-4 blog-thumbnail align-self-center text-center mb-4 mb-md-0">
            <a href="<?php the_permalink(); ?>"><img src="<?php echo get_the_post_thumbnail_url(get_the_ID(),'tainacan-interface-list-post') ?>" class="img-fluid" alt=""></a>
        </div>
    <?php endif; ?>
    <div class="col-xs-12 blog-content <?php if ( has_post_thumbnail() ) :?>col-md-8 blog-flex<?php else : ?>col-md-12<?php endif; ?> align-self-center">
        <h3 class="mb-3">
            <a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a>
        </h3>
        <?php echo '<p class="text-black">'.wp_trim_words( get_the_excerpt(), 28, '...').'</p>'; ?>
        <?php tainacan_meta_date_author(); ?> 
        
        <a href="<?php the_permalink(); ?>" class="readmore float-right screen-reader-text"><?php _e('Read more...', 'tainacan-interface'); ?></a>
    </div>
</div>

<hr class="border-mercury">