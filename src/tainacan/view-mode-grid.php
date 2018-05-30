<?php if (have_posts()):  ?>

    <div class="tainacan-grid-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col col-md-12 col-lg-6 col-xl-4">
                <div class="tainacan-grid">
                    <?php the_post_thumbnail('medium'); ?>
                    <p class="metadata-title"><?php the_title(); ?></p>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    Nenhum item encontrado
<?php endif; ?>
