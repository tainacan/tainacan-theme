<?php if (have_posts()):  ?>

    <div class="tainacan-grid-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col">
                <a class="tainacan-grid" href="<?php the_permalink(); ?>">
                    <?php if ( tainacan_current_view_displays('title') ): ?>
                        <p class="metadata-title">     
                            <?php the_title(); ?>           
                        </p>
                    <?php endif; ?>
                    <?php if ( tainacan_current_view_displays('thumbnail') ): ?>
                            <?php the_post_thumbnail('medium'); ?>   
                    <?php endif; ?>
                </a>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-grid-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
