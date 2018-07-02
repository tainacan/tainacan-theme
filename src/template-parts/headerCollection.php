<?php 
    /* if(wp_is_mobile())
        echo tainacan_get_form_search(); */
?>

<div <?php if ( get_header_image() ) : ?>class="page-header header-filter clear-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else: ?>class="page-header header-filter clear-filter page-collection" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
    <div class="container-fluid px-0 bg-jelly-bean t-bg-collection">
        <div class="row t-collection--info margin-one-column">
            <div class="col-4 col-md-3 px-0 t-collection--col-3">
                <img src="<?php echo get_the_post_thumbnail_url(tainacan_get_collection_id()); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute">
            </div>
            <div class="col-8 col-md-9 pl-0 t-collection--col-9">
                <h2 class="mt-3 mt-md-0 t-collection--info-title text-white position-absolute"><?php tainacan_the_collection_name(); ?></h2>
            </div>
        </div>
        <div class="row t-collection--info-description ml-0 pl-0 mt-3 text-white margin-one-column">
            <p class="t-collection--info-description-text"><?php tainacan_the_collection_description(); ?></p>
        </div>
    </div>
</div>