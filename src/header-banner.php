<?php 
//Header with menu
get_header('menu');?>
    <form class="tainacan-form-search d-sm-none">
        <div class="d-flex justify-content-between">
            <div class="w-100 pt-1 mt-2 pl-1">
                <input class="form-control h-75 tainacan-input-search py-0 border-0" type="search" placeholder="Pesquisar">
            </div>
            <div class="pt-1 mt-2 ml-1">
                <button type="reset" class="btn h-75 pt-0 pr-0 border-0 bg-white tainacan-button-search" onclick=""><i class="material-icons">close</i></button>
            </div>
            <div class="pt-1 mt-2 mx-1">
                <button type="submit" class="btn h-75 pt-0 border-0 text-heavy-metal bg-white tainacan-button-search" onclick=""><i class="material-icons">search</i></button>
            </div>
        </div>
    </form>

    <div <?php if ( get_header_image() ) : ?>class="page-header header-filter clear-filter page-height" style="background-image: url('<?php header_image(); ?>')"<?php else: ?>class="page-header header-filter clear-filter" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/capa.png')"<?php endif; ?>>
        <!-- <img src="<?php echo get_template_directory_uri() ?>/assets/images/capa.png" class="img-fluid header-img">
        <i class="material-icons float-right p-1 m-2 mt-3 text-white rounded-circle position-absolute header-icon">settings</i> -->
    </div>