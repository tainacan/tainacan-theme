<?php if (have_posts()):  ?>

    <div class="tainacan-mosaic-container card-columns">

        <?php while (have_posts()): the_post(); ?>
        
                <div class="tainacan-mosaic">
                    <?php the_post_thumbnail('full'); ?>
                    <p class="metadata-title"><?php the_title(); ?></p>
                </div>      
                
        <?php endwhile; ?>

    </div>

<?php else: ?>
    <div class="tainacan-cards-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
