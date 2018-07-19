<?php if (have_posts()):  ?>

    <div class="tainacan-mosaic-container card-columns">

        <?php while (have_posts()): the_post(); ?>
        
                <div class="tainacan-mosaic">
                    <a href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ): ?>
                            <?php the_post_thumbnail('large'); ?> 
                        <?php else: ?>
                            <?php echo '<img alt="Thumbnail placeholder" src="'.get_stylesheet_directory_uri().'/assets/images/thumbnail_placeholder.png">'?>
                        <?php endif; ?>  
                    </a>    
                    <a href="<?php the_permalink(); ?>">
                        <p class="metadata-title"><?php the_title(); ?></p>
                    </a>
                </div>      

        <?php endwhile; ?>

    </div>

<?php else: ?>
    <div class="tainacan-mosaic-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
