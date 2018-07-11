<?php if (have_posts()):  ?>

    <div class="tainacan-polaroid-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col">
                <div class="tainacan-polaroid">
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
                        <p class="metadata-title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>    
                        </p>
                    <?php endif; ?>
                    <div class="list-metadata">
                        <?php tainacan_the_metadata(array('metadata__in' => $displayed_metadata, 'exclude_title' => true, 'before_title' => '<h3 class="metadata-label">', 'before_value' => '<p class="metadata-value">')); ?>
                    </div>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-grid-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
