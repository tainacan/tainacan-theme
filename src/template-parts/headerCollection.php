<div <?php if ( get_header_image() ) : ?>class="page-header header-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else: ?>class="page-header header-filter page-collection" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
    <div class="container-fluid px-0 t-bg-collection" style="<!-- z-index: 0; -->">
        <div class="collection-header position-relative" style="">
            <?php if(has_post_thumbnail(tainacan_get_collection_id())) : ?>
                <img src="<?php echo get_the_post_thumbnail_url(tainacan_get_collection_id()); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute text-left" style="bottom: -6rem; margin-left: 4.16666666667%;">
            <?php else : ?>
                <div class="image-placeholder rounded-circle border border-white position-absolute">
                    <h4 class="text-center"  style="bottom: -6rem; margin-left: 4.16666666667%;">
                    <?php 
                        echo tainacan_get_initials(tainacan_get_the_collection_name());
                    ?>
                    </h4>
                </div>
            <?php endif; 
            global $wp; ?>
            <!-- <div class="collection-header--share">
                <?php if ( true == get_theme_mod( 'facebook_share', false ) ) : ?>
                    <button onclick="location.href='http://www.facebook.com/sharer.php?u=<?php echo home_url( $wp->request ); ?>'" target="_blank" class="mdi mdi-facebook" id="fa-1"></button>
                <?php endif; ?>
                <?php if ( true == get_theme_mod( 'twitter_share', false ) && get_option( 'twitter_user') ) : ?> 
                    <button onclick="location.href='http://twitter.com/share?url=<?php echo home_url( $wp->request ); ?>&amp;text=<?php echo tainacan_get_the_collection_name(); ?>&amp;via=<?php echo get_option( 'twitter_user', '' ) ?>'" target="_blank" class="mdi mdi-twitter" id="fa-2"></button>
                <?php endif; ?>
                <?php if ( true == get_theme_mod( 'google_share', false ) ) : ?> 
                    <button onclick="location.href='https://plus.google.com/share?url=<?php echo home_url( $wp->request ); ?>'" target="_blank" class="mdi mdi-google-plus" id="fa-3"></button>
                <?php endif; ?>
                <button class="collection-header--share-button">
                    <div class="mdi mdi-share-variant" id="collection-header--share-button-plus"></div>
                </button>
            </div>  -->
            <div class="collection-header--share">
                <div class="btn trigger">
                    <span class="mdi mdi-share-variant"></span>
                </div>
                <div class="icons">
                    <div class="rotater">
                        <?php if ( true == get_theme_mod( 'facebook_share', false ) ) : ?> 
                            <a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" target="_blank">
                                <div class="btn btn-icon">
                                    <i class="mdi mdi-facebook"></i>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="rotater">
                        <?php if ( true == get_theme_mod( 'google_share', false ) ) : ?> 
                            <a href="https://plus.google.com/share?url=<?php the_permalink(); ?>" target="_blank">
                                <div class="btn btn-icon">
                                    <i class="mdi mdi-google-plus"></i>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="rotater">
                        <?php if ( true == get_theme_mod( 'twitter_share', false ) && get_option( 'twitter_user') ) : ?> 
                            <a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?>&amp;via=<?php echo get_option( 'twitter_user', '' ) ?>" target="_blank">
                                <div class="btn btn-icon">
                                    <i class="mdi mdi-twitter"></i>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row t-collection--info max-large margin-one-column" style="overflow-x: inherit;">
            <div class="col-4 col-md-3 px-0 t-collection--col-3">
                
            </div>
            <div class="col-8 col-md-9 pl-0 t-collection--col-9 mt-md-3" style="z-index: 2">
                <h2 class="t-collection--info-title text-white">
                    <?php tainacan_the_collection_name(); ?>
                </h2>
                <div class="text-white t-collection--info-description-text dotmore">
                    <?php
                        tainacan_the_collection_description(); 
                    ?>
                    <a class="toggle" href="#"></a>
                </div>
            </div>
        </div>
    </div>
</div>