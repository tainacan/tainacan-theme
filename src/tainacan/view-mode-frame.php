<?php if (have_posts()):  ?>

    <div class="tainacan-frame-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col">
                <div class="tainacan-frame">
                    <div class="frame">
                        <div class="mat">
                            <div class="art">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <p class="metadata-title"><?php the_title(); ?></p>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-cards-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
