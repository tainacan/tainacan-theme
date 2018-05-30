<?php if (have_posts()):  ?>
    <div class="tainacan-masonry-container card-columns p-3">
        <?php while (have_posts()): the_post(); ?>
                <div class="card">
                    <?php the_post_thumbnail('medium'); ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                    </div>
                </div>      
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="tainacan-masonry-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
