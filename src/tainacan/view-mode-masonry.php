<?php if (have_posts()):  ?>
    <div class="tainacan-masonry-container card-columns p-3">
        <?php while (have_posts()): the_post(); ?>
                <div class="card">
                    <?php 
                        if(has_post_thumbnail()): 
                            the_post_thumbnail('large'); 
                        endif;
                    ?>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h5>
                    </div>
                </div>      
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="tainacan-masonry-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
