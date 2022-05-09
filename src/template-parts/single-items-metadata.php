<?php
    if ( function_exists('tainacan_get_the_metadata_sections') ) { //if (version_compare(TAINACAN_VERSION, '0.19') >= 0) {
        get_template_part( 'template-parts/single-items-metadata_new' );
    } else {
        get_template_part( 'template-parts/single-items-metadata_old' );
    }
?>