<div class="row blog-post mb-4">
    <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
        <div class="col-xs-12 col-md-4 blog-thumbnail align-self-center text-center mb-4 mb-md-0">
            <a href="<?php the_permalink(); ?>"><img src="<?php echo get_the_post_thumbnail_url(get_the_ID(),'medium') ?>" class="img-fluid rounded" alt=""></a>
        </div>
    <?php endif; ?>
    <div class="col-xs-12 <?php if ( has_post_thumbnail() ) :?>col-md-8<?php else : ?>col-md-12<?php endif; ?> blog-content">
        <h3 class="mb-3">
            <a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a>
        </h3>
        <?php echo '<p class="text-oslo-gray">'.wp_trim_words( get_the_content(), 35, '...' ).'</p>'; ?>
        <p>by <?php the_author_posts_link(); ?>, <?php the_time(); ?></p>
        <a href="<?php the_permalink(); ?>" class="btn btn-primary blog-read float-right">Read more</a>
    </div>
</div>

<hr class="mb-5">