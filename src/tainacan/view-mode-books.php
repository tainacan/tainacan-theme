<?php if (have_posts()):  ?>

    <div class="tainacan-books-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col col-sm-12 col-md-6 col-lg-4 col-xl-3">
                <div class="tainacan-book">
                    <?php if ( tainacan_current_view_displays('thumbnail') ): ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ): ?>
                                <?php the_post_thumbnail('medium_large'); ?> 
                            <?php else: ?>
                                <?php echo '<img alt="Thumbnail placeholder" src="'.get_stylesheet_directory_uri().'/assets/images/thumbnail_placeholder.png">'?>
                            <?php endif; ?>  
                        </a>
                    <?php endif; ?>
                    <?php if ( tainacan_current_view_displays('title') ): ?>
                        <a href="<?php the_permalink(); ?>">
                            <p class="metadata-title"><?php the_title(); ?></p>
                        </a>
                    <?php endif; ?>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-books-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
