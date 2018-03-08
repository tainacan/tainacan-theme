<div class="card w-100">
    <h5 class="card-header"><?php the_title(); ?></h5>
    <div class="card-body">
        <p class="card-text"><?php if(is_single()) : the_content(); else : the_excerpt(); endif; ?></p>
        <a href="<?php the_permalink(); ?>" class="btn btn-primary">View more</a>
    </div>
</div>