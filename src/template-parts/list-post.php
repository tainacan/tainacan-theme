<!-- <div class="row blog-post">
    <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
        <div class="col-lg-5 col-xl-4 mb-4">
            <div class="view overlay hm-white-slight z-depth-1-half">
                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(),'medium') ?>" class="img-fluid rounded" alt="">
            </div>
        </div>
    <?php endif; ?>
    
    <div class="<?php if ( has_post_thumbnail()) :?>col-lg-7 col-xl-7<?php else : ?>col-lg-12 col-xl-12<?php endif; ?> ml-xl-4 mb-4 blog-content">
        <h3 class="mb-3 dark-grey-text">
            <a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a>
        </h3>
        <div>
            <?php the_content(); ?>
        </div>
        <div>
            <p>by <a class="font-weight-bold dark-grey-text">Fabiano Alencar</a>, <?php the_time(); ?></p>
        </div>
        <a class="btn btn-primary btn-md waves-effect waves-light">Read more</a>
    </div>
</div> -->

<div class="row blog-post mb-4">
    <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
        <div class="col-xs-12 col-md-4 blog-thumbnail align-self-center text-center mb-4 mb-md-0">
            <a href="<?php the_permalink(); ?>"><img src="<?php echo get_the_post_thumbnail_url(get_the_ID(),'medium') ?>" class="img-fluid rounded" alt=""></a>
        </div>
    <?php endif; ?>
    <div class="col col-md-8 blog-content">
        <h3 class="mb-3">
            <a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a>
        </h3>
        <?php echo wp_trim_words( get_the_content(), 40, '...' ); ?>
        <br><br><p>by <a class="font-weight-bold dark-grey-text"><?php the_author_posts_link(); ?></a>, <?php the_time(); ?></p>
    </div>
</div>