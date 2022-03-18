<?php
$attachments = tainacan_get_the_attachments();
$is_gallery_mode = get_theme_mod( 'tainacan_single_item_gallery_mode', false );
$hide_file_name = get_theme_mod('tainacan_single_item_hide_files_name_main', true);
$hide_file_caption = get_theme_mod('tainacan_single_item_hide_files_caption_main', true);
$hide_file_description = get_theme_mod('tainacan_single_item_hide_files_description_main', true);
$hide_thumbnail_info = get_theme_mod('tainacan_single_item_hide_files_name', false);
$disable_gallery_lightbox = get_theme_mod('tainacan_single_item_disable_gallery_lightbox', false);
$hide_download_button = get_theme_mod( 'tainacan_single_item_hide_download_document', false );
$hide_file_name_lightbox = get_theme_mod('tainacan_single_item_hide_files_name_lightbox', false);
$hide_file_caption_lightbox = get_theme_mod('tainacan_single_item_hide_files_caption_lightbox', false);
$hide_file_description_lightbox = get_theme_mod('tainacan_single_item_hide_files_description_lightbox', false);
$has_light_dark_color_scheme = get_theme_mod( 'tainacan_single_item_gallery_color_scheme', 'dark' ) == 'light';

if ( !empty( $attachments )  || ( $is_gallery_mode && tainacan_has_document() ) ) {
?>

    <div class="mt-3 tainacan-single-post">
        <?php if ( !$is_gallery_mode && get_theme_mod('tainacan_single_item_attachments_section_label', __( 'Attachments', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-attachments-label">
                <?php echo esc_html( get_theme_mod('tainacan_single_item_attachments_section_label', __( 'Attachments', 'tainacan-interface' )) ); ?>
            </h2>
        <?php endif; ?>
        <?php if ( $is_gallery_mode && get_theme_mod('tainacan_single_item_documents_section_label', __( 'Documents', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-documents-label">
                <?php echo esc_html( get_theme_mod('tainacan_single_item_documents_section_label', __( 'Documents', 'tainacan-interface' )) ); ?>
            </h2>
        <?php endif; ?>

        <section 
                style="<?php echo (!$is_gallery_mode ? 'min-height: 10vh;' : '') ?>"
                class="tainacan-content single-item-collection margin-two-column">
            <?php
                tainacan_the_item_gallery([
                    'blockId'                       => 'tainacan-item-attachments_id-' . $post->ID,
                    'layoutElements'                => array( 'main' => $is_gallery_mode, 'thumbnails' => true ),
			        'mediaSources'                  => array( 'document' => $is_gallery_mode, 'attachments' => true, 'metadata' => false),
                    'hideFileNameMain'              => $hide_file_name,
                    'hideFileCaptionMain'           => $hide_file_caption, 
                    'hideFileDescriptionMain'       => $hide_file_description,
                    'hideFileNameThumbnails'        => $hide_thumbnail_info, 
                    'hideFileCaptionThumbnails'     => true, 
                    'hideFileDescriptionThumbnails' => true, 
                    'showDownloadButtonMain'        => !$hide_download_button,
                    'showArrowsAsSVG'               => false,
                    'hideFileNameLightbox'          => $hide_file_name_lightbox,
                    'hideFileCaptionLightbox'       => $hide_file_caption_lightbox, 
                    'hideFileDescriptionLightbox'   => $hide_file_description_lightbox,
                    'openLightboxOnClick'           => $is_gallery_mode ? !$disable_gallery_lightbox : true,
                    'lightboxHasLightBackground'    => $has_light_dark_color_scheme 
                ]);
            ?>
        </section>

    </div>

    <div class="my-5 border-bottom border-silver"></div>
<?php
}
