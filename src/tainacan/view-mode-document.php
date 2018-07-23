<?php if (have_posts()):  ?>

    <div class="tainacan-document-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col col-md-12 col-lg-6 col-xl-4">
                <div class="tainacan-document">
                
                    <?php if ( tainacan_current_view_displays('title') ): ?>
                        <p class="metadata-title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <div class="media">
                        <div class="paper mr-4">
                            <?php if ( tainacan_current_view_displays('thumbnail') ): ?>
                                <a href="<?php the_permalink(); ?>">
                                <?php if ( has_post_thumbnail() ): ?>
                                    <?php the_post_thumbnail('tainacan-medium-full'); ?> 
                                <?php else: ?>
                                    <?php echo '<img alt="Thumbnail placeholder" src="'.get_stylesheet_directory_uri().'/assets/images/thumbnail_placeholder.png">'?>
                                <?php endif; ?>  
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="list-metadata media-body">
                            <?php tainacan_the_metadata(array('metadata__in' => $view_mode_displayed_metadata['meta'], 'exclude_title' => true, 'before_title' => '<h3 class="metadata-label">', 'before_value' => '<p class="metadata-value">')); ?>
                        </div>
                    </div>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-document-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
