<?php if (have_posts()):  ?>

    <div class="tainacan-gallery-container">

        <div class="row no-gutters">
        <?php while (have_posts()): the_post(); ?>
            <div class="col col-md-12 col-lg-6 col-xl-4">
                <div class="tainacan-gallery">                  
                    <div class="media">
                        
                        <?php if ( tainacan_current_view_displays('thumbnail') ): ?>
                            <a href="<?php the_permalink(); ?>">
                                <div class="frame">
                                    <div class="mat">
                                        <div class="art">
                                            <?php if ( has_post_thumbnail() ): ?>
                                                <?php the_post_thumbnail('tainacan-medium-full'); ?> 
                                            <?php else: ?>
                                                <?php echo '<img alt="Thumbnail placeholder" src="'.get_stylesheet_directory_uri().'/assets/images/thumbnail_placeholder.png">'?>
                                            <?php endif; ?>  
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endif; ?>

                        <div class="media-body pl-3">
                            <div class="list-metadata ">
                                <?php if ( tainacan_current_view_displays('title') ): ?>
                                    <p class="metadata-title">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php tainacan_the_metadata(array('metadata__in' => $view_mode_displayed_metadata['meta'], 'exclude_title' => true, 'before_title' => '<h3 class="metadata-label">', 'before_value' => '<p class="metadata-value">')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            
        <?php endwhile; ?>
        </div>
    </div>

<?php else: ?>
    <div class="tainacan-gallery-container">
        Nenhum item encontrado
    </div>
<?php endif; ?>
