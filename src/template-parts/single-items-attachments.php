<?php
    if ( function_exists('tainacan_the_item_gallery') ) { //if (version_compare(TAINACAN_VERSION, '0.19RC') >= 0) {
        get_template_part( 'template-parts/single-items-attachments_new' );
    } else if ( function_exists('tainacan_get_the_media_component') ) { //if (version_compare(TAINACAN_VERSION, '0.18RC') >= 0) {
        get_template_part( 'template-parts/single-items-attachments_old' );
    } else {
        ?>
        <div style="text-aling: center; max-width: 600px; margin: 2em auto; width: 100%; font-style: italic;">
            <p><?php __('It seems that you are using a legacy vesion of the Tainacan plugin. Please update in order to use the latest features for displaying item media.', 'tainacan-interface') ?></p>
        </div>
        <?php
    }
?>