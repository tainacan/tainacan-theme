<div class="media mb-4">
    <?php if ( has_post_thumbnail() && get_the_post_thumbnail_url(get_the_ID()) ) : ?>
        <a href="<?php the_permalink(); ?>">
            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(),'medium') ?>" alt="" class="img-fluid align-self-center mr-5">
        </a>
    <?php endif; ?>
    <div class="media-body align-self-center">
        <h5 class="mt-0 ">
            <a href="<?php the_permalink(); ?>" class="font-weight-bold"><?php the_title(); ?></a> 
            <time datetime="<?php the_time(); ?>" class="text-oslo-gray">
                <?php the_time('j \\d\\e F \\d\\e Y'); ?>
            </time> 
            <?php if(has_category()) : ?>
                <span class="text-oslo-gray px-2">/</span><span class="list-inline"><?php the_category(', '); ?></span>
            <?php endif; ?>
        </h5>
        <?php if(is_single()) : the_content(); else : the_excerpt(); endif; ?>
    </div>
</div>