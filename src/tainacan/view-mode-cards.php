

<?php var_dump($request['fetch_only']); if (have_posts()):  ?>

    <div class="list-container">
        

        <?php while (have_posts()): the_post(); ?>

            <div class="tainacan-list">
            
                <p class="field-main-content"><?php the_title(); ?></p>

                <div>
                
                    <div class="list-image">
                        <?php the_post_thumbnail('thumbnail'); ?>
                    </div>

                    <div class="list-metadata">
                        <?php foreach ($request['fetch_only']['meta'] as $meta_id): ?>
                            
                            <span>
                                <p>
                                    <?php tainacan_the_metadata((int) $meta_id); ?>
                                </p>
                            </span>

                        <?php endforeach; ?>
                    </div>
                
                </div>

            </div>

        <?php endwhile; ?>
        
        
    
    </div>

<?php else: ?>
    Nenhum item encontrado
<?php endif; ?>
