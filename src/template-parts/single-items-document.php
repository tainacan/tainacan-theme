<?php 

if (version_compare(TAINACAN_VERSION, '0.18RC') >= 0) {
    $hide_file_name = get_theme_mod('tainacan_single_item_hide_files_name_main', true);
    $hide_file_caption = get_theme_mod('tainacan_single_item_hide_files_caption_main', true);
    $hide_file_description = get_theme_mod('tainacan_single_item_hide_files_description_main', true);
    $hide_thumbnail_info = get_theme_mod('tainacan_single_item_hide_files_name', false);
    $is_document_type_attachment = tainacan_get_the_document_type() === 'attachment';
    $disable_gallery_lightbox = get_theme_mod('tainacan_single_item_disable_gallery_lightbox', false);
    $hide_download_button = get_theme_mod( 'tainacan_single_item_hide_download_document', false );
    $has_light_dark_color_scheme = get_theme_mod( 'tainacan_single_item_gallery_color_scheme', 'dark' ) == 'light';

    if ( function_exists('tainacan_the_item_gallery') ) {
        $hide_file_name_lightbox = get_theme_mod('tainacan_single_item_hide_files_name_lightbox', false);
        $hide_file_caption_lightbox = get_theme_mod('tainacan_single_item_hide_files_caption_lightbox', false);
        $hide_file_description_lightbox = get_theme_mod('tainacan_single_item_hide_files_description_lightbox', false);
    }
    global $post;
}

if ( tainacan_has_document() && !get_theme_mod( 'tainacan_single_item_gallery_mode', false )) : ?>
    <div class="mt-3 tainacan-single-post">
        <?php if ( get_theme_mod('tainacan_single_item_document_section_label', __( 'Document', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-document-label">
                <?php echo esc_html( get_theme_mod('tainacan_single_item_document_section_label', __( 'Document', 'tainacan-interface' )) ); ?>
            </h2>
        <?php endif; ?>
        <section class="tainacan-content single-item-collection margin-two-column">
            <div class="single-item-collection--document">
            <?php if ( function_exists('tainacan_the_item_gallery') ) {
            
                    tainacan_the_item_gallery([
                        'blockId'                       => 'tainacan-item-document_id-' . $post->ID,
                        'layoutElements'                => array( 'main' => true, 'thumbnails' => false ),
                        'mediaSources'                  => array( 'document' => true, 'attachments' => false, 'metadata' => false),
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
                        'openLightboxOnClick'           => !$disable_gallery_lightbox,
                        'lightboxHasLightBackground'    => $has_light_dark_color_scheme 
                    ]);
                
                } else if (function_exists('tainacan_get_the_media_component')) {
                    $media_items_main = array();

                    $class_slide_metadata = '';
                    if ($hide_file_name)
                        $class_slide_metadata .= ' hide-name';
                    if ($hide_file_description)
                        $class_slide_metadata .= ' hide-description';
                    if ($hide_file_caption)
                        $class_slide_metadata .= ' hide-caption';

                    if ( tainacan_has_document() ) {
                        $is_document_type_attachment = tainacan_get_the_document_type() === 'attachment';
                        $media_items_main[] =
                            tainacan_get_the_media_component_slide(array(
                                'after_slide_metadata' => (( !$hide_download_button && tainacan_the_item_document_download_link() != '' ) ?
                                                                ('<span class="tainacan-item-file-download">' . tainacan_the_item_document_download_link() . '</span>')
                                                        : ''),
                                'media_content' => tainacan_get_the_document(),
                                'media_content_full' => $is_document_type_attachment ? tainacan_get_the_document(0, 'full') : ('<div class="attachment-without-image">' . tainacan_get_the_document(0, 'full') . '</div>'),
                                'media_title' => $is_document_type_attachment ? get_the_title(tainacan_get_the_document_raw()) : '',
                                'media_description' => $is_document_type_attachment ? get_the_content(tainacan_get_the_document_raw()) : '',
                                'media_caption' => $is_document_type_attachment ? wp_get_attachment_caption(tainacan_get_the_document_raw()) : '',
                                'media_type' => tainacan_get_the_document_type(),
                                'class_slide_metadata' => $class_slide_metadata
                            ));
                    }

                    tainacan_the_media_component(
                        'tainacan-item-document_id-' . $post->ID,
                        null,
                        $media_items_main,
                        array(
                            'class_main_div' => '',
                            'class_thumbs_div' => '',
                            'swiper_thumbs_options' => '',
                            'swiper_main_options' => array(
                                'navigation' => array(
                                    'nextEl' => '.swiper-navigation-next_' . 'tainacan-item-document_id-' . $post->ID . '-main',
                                    'prevEl' => '.swiper-navigation-prev_' . 'tainacan-item-document_id-' . $post->ID . '-main',
                                    'preloadImages' => false,
                                    'lazy' => true
                                ) 
                            ),
                            'disable_lightbox' => $disable_gallery_lightbox
                        )
                    );

                } else {
                    ?>
                    <div style="text-aling: center; max-width: 600px; margin: 2em auto; width: 100%; font-style: italic;">
                        <p><?php __('It seems that you are using a legacy vesion of the Tainacan plugin. Please update in order to use the latest features for displaying item media.', 'tainacan-interface') ?></p>
                    </div>
                    <?php
                }?>
            </div>
        </section>
    </div>
    
    <div class="my-5 border-bottom border-silver"></div>

<?php endif; ?>