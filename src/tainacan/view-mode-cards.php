<?php if (have_posts()):  ?>

    <div class="tainacan-cards-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col col-md-12 col-lg-6 col-xl-4">
                <div class="tainacan-card">
                
                    <p class="metadata-title"><?php the_title(); ?></p>

                    <div class="media">
                        <?php the_post_thumbnail('thumbnail', array('class' => 'mr-4')); ?>

                        <div class="list-metadata media-body">
                            <?php foreach ($displayed_metadata as $meta_id): ?>
                                     
                                <?php tainacan_the_metadata($meta_id, array('before_title' => '<h3 class="metadata-label">', 'before_value' => '<p class="metadata-value">')); ?>

                            <?php endforeach; ?>
                        </div>
                    </div>
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
