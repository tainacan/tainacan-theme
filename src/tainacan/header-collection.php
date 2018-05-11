<?php 
/**
 * This template adss Collections details to the header
 * 
 * It will only work with tainacan plugin enabled.
 * 
 */
?>

<div class="t-collection--background-title p-5"></div>
<div class="container-fluid">
    <div class="row t-collection--info ml-sm-5">
        <div class="col pr-0">
            <img src="<?php echo get_the_post_thumbnail_url(tainacan_get_collection_id()); ?>" class="t-collection--info-img rounded-circle img-fluid d-none d-md-block">
        </div>
        <div class="col-10 pl-0">
<<<<<<< HEAD:src/header-homecollection.php
            <h2 class="mt-3 mt-md-0 t-collection--info-title text-white">Title</h2>
=======
            <h2 class="mt-3 mt-md-0 t-collection--info-title"><?php tainacan_the_collection_name(); ?></h2>
>>>>>>> 5fb08a2e2c93cdef01cc4473e7383f87eeb1dca3:src/tainacan/header-collection.php
            <div class="d-flex justify-content-between container ml-0 pl-0">
                <div><?php tainacan_the_collection_description(); ?></div>
                <div>
                    <a href="#" class="">Icon 1</a> <a href="#">Icon 2</a>
                </div>
            </div>
        </div>
    </div>
</div>

    <?php
    $bread = "<ol class='breadcrumb pb-0 mb-1' style='background: transparent'>";
    $bread .= "<li class='breadcrumb-item'><a href='#'>Home</a></li>";
    $bread .= "<li class='breadcrumb-item'><a href='#'>Site</a></li>";
    $bread .= "<li class='breadcrumb-item active' aria-current='page'>Blog</li>";
    $bread .= "</ol>";
    if(has_nav_menu('navMenubelowHeader')) : ?>
        <nav class="navbar navbar-expand-md navbar-light bg-white px-1 border-bottom" role="navigation">
            <div class="container-fluid max-large">
                <?php /* if(wp_is_mobile()) echo $bread; */ ?>
                <a class="navbar-brand" href="#"></a>
                <!-- Brand and toggle get grouped for better mobile display -->
                <button class="navbar-toggler text-heavy-metal border-0 px-2 pt-2" type="button" data-toggle="collapse" data-target="#menubelowHeader" aria-controls="menubelowHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                    <?php
                        wp_nav_menu( array(
                            'theme_location'	=> 'navMenubelowHeader',
                            'depth'				=> 2, // 1 = with dropdowns, 0 = no dropdowns.
                            'container'			=> 'div',
                            'container_class'	=> 'collapse navbar-collapse',
                            'container_id'		=> 'menubelowHeader',
                            'menu_class'		=> 'navbar-nav mr-auto ml-md-1',
                            'fallback_cb'		=> 'WP_Bootstrap_Navwalker::fallback',
                            'walker'			=> new WP_Bootstrap_Navwalker()
                        ) );
                    ?>
            </div>
        </nav>
    <?php endif; ?>
    <!-- <nav aria-label="breadcrumb" class="d-none d-md-flex">
        <?php //echo $bread; ?>
    </nav> -->
    <div class="container-fluid mt-5 max-large">