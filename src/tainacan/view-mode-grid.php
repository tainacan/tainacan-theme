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
                        <?php if ( has_post_thumbnail() ): ?>
                            <?php the_post_thumbnail('tainacan-medium'); ?> 
                        <?php else: ?>
                            <?php echo '<img alt="Thumbnail placeholder" src="'.get_stylesheet_directory_uri().'/assets/images/thumbnail_placeholder.png">'?>
                        <?php endif; ?>  
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
