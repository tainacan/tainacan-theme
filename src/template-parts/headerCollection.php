<div <?php if ( get_header_image() ) : ?>class="page-header header-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else: ?>class="page-header header-filter page-collection" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
    <div class="container-fluid px-0 t-bg-collection" style="<!-- z-index: 0; -->">
        <div class="row t-collection--info max-large margin-one-column" style="overflow-x: inherit;">
            <div class="col-4 col-md-3 px-0 t-collection--col-3">
                <?php if(has_post_thumbnail(tainacan_get_collection_id())) : ?>
                    <img src="<?php echo get_the_post_thumbnail_url(tainacan_get_collection_id()); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute">
                <?php else : ?>
                    <div class="image-placeholder rounded-circle border border-white position-absolute">
                        <h4 class="text-center">
                        <?php 
                            echo tainacan_get_initials(tainacan_get_the_collection_name());
                        ?>
                        </h4>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-8 col-md-9 pl-0 t-collection--col-9">
                <h2 class="t-collection--info-title text-white">
                    <?php tainacan_the_collection_name(); ?>
                </h2>
                <p class="text-white t-collection--info-description-text">
                    <?php 
                        if(!wp_is_mobile()){
                            $words = 420;
                        }else {
                            $words = 80;
                        }
                        wp_trim_words( tainacan_the_collection_description(), $words, '...' ) ; 
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>