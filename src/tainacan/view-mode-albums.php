<?php if (have_posts()):  ?>

    <div class="tainacan-albums-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col col-sm-12 col-md-6 col-lg-4 col-xl-3">
                <div class="tainacan-album">
                    <a href="<?php the_permalink(); ?>">              
                        <div class="cd" style="background-image: url('<?php the_post_thumbnail_url('medium'); ?>')"></div>                          
                        <?php if ( has_post_thumbnail() ): ?>
                            <?php the_post_thumbnail('tainacan-medium'); ?> 
                        <?php else: ?>
                            <?php echo '<img alt="Thumbnail placeholder" src="'.get_stylesheet_directory_uri().'/assets/images/thumbnail_placeholder.png">'?>
                        <?php endif; ?>  
                    </a>
                    <a href="<?php the_permalink(); ?>">
                        <p class="metadata-title"><?php the_title(); ?></p>
                    </a>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-albums-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
