<?php 
    /* if(wp_is_mobile())
        echo tainacan_get_form_search(); */
?>

<div <?php if ( get_header_image() ) : ?>class="page-header header-filter clear-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else: ?>class="page-header header-filter clear-filter align-items-center" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
    <div class="container-fluid p-0 ph-title-description">
        <div class="bg-white-title title-header">
            <h1 class="mb-0">
                <?php if(is_home()) { ?> Blog <?php bloginfo('title'); }?>
                <?php if(is_archive()) { ?> <?php echo get_the_archive_title(); }?>
            </h1>
            <?php if(is_home()) {?> <span><?php bloginfo('description'); }?></span>
        </div>
    </div>
</div>