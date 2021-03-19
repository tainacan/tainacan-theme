<?php
    if ( function_exists('tainacan_get_the_media_component') ) { //if (version_compare(TAINACAN_VERSION, '0.18RC') >= 0) {
        get_template_part( 'template-parts/single-items-attachments_new' );
    } else {
        get_template_part( 'template-parts/single-items-attachments_old' );
    }
?>