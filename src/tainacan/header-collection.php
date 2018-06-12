<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerHeader' ); ?>

<?php 
/**
 * This template adss Collections details to the header
 * 
 * It will only work with tainacan plugin enabled.
 * 
 */
?>

    <!-- <div class="t-collection--background-title"></div> -->
    <div class="container-fluid">
        <div class="row t-collection--info ml-sm-5">
            <div class="col pr-0">
                <img src="<?php echo get_the_post_thumbnail_url(tainacan_get_collection_id()); ?>" class="t-collection--info-img rounded-circle img-fluid d-none d-md-block border border-white">
            </div>
            <div class="col-10 pl-0">
                <h2 class="mt-3 mt-md-0 t-collection--info-title text-white"><?php tainacan_the_collection_name(); ?></h2>
                <div class="d-flex justify-content-between container ml-0 pl-0 position-absolute" style="top: 15px">
                    <div><?php tainacan_the_collection_description(); ?></div>
                    <div>
                        <a href="#" class="">Icon 1</a> <a href="#">Icon 2</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menuBellowBanner' ); ?>