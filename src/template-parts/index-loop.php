
<div class="<?php if(!is_single()) : echo 'card-columns'; endif; ?> mt-3">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <div class="col-sm">
            <div class="card w-100">
                <h5 class="card-header"><?php the_title(); ?></h5>
                <div class="card-body">
                    <p class="card-text"><?php if(is_single()) : the_content(); else : the_excerpt(); endif; ?></p>
                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">View more</a>
                </div>
            </div>
        </div>

    <?php endwhile; else: ?>
    <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
    <?php endif; ?>
</div>

<nav class="mx-auto">
    <?php echo pagination_bst4(); ?>
</nav>