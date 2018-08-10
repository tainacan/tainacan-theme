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
            <?php endif; ?>
            <div class="radial collection-header--share">
                <button class="mdi mdi-facebook" id="fa-1"></button>
                <button class="mdi mdi-twitter" id="fa-2"></button>
                <button class="mdi mdi-google-plus" id="fa-3"></button>
                <button class="fab collection-header--share-button">
                    <div class="mdi mdi-share-variant" id="collection-header--share-button-plus"></div>
                </button>
            </div> 
        </div>
        <div class="row t-collection--info max-large margin-one-column" style="overflow-x: inherit;">
            <div class="col-4 col-md-3 px-0 t-collection--col-3">
                
            </div>
            <div class="col-8 col-md-9 pl-0 t-collection--col-9" style="z-index: 2">
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