<?php 

if (version_compare(TAINACAN_VERSION, '0.18RC') >= 0) {
    $hide_file_name = get_theme_mod('tainacan_single_item_hide_files_name_main', true);
    $hide_file_caption = get_theme_mod('tainacan_single_item_hide_files_caption_main', true);
    $hide_file_description = get_theme_mod('tainacan_single_item_hide_files_description_main', true);
    $is_document_type_attachment = tainacan_get_the_document_type() === 'attachment';

    $class_slide_metadata = '';

    if ($hide_file_name)
        $class_slide_metadata .= ' hide-name';
    if ($hide_file_description)
        $class_slide_metadata .= ' hide-description';
    if ($hide_file_caption)
        $class_slide_metadata .= ' hide-caption';

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
            <?php if ( !function_exists('tainacan_get_the_media_component') ) {
            
                    tainacan_the_document(); 

                    if ( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && function_exists('tainacan_the_item_document_download_link') && tainacan_the_item_document_download_link() != '' )
                        echo '<span class="tainacan-item-file-download">' . tainacan_the_item_document_download_link() . '</span>';
                    
                    if ( version_compare(TAINACAN_VERSION, '0.18RC') >= 0 ) {?>
                        <div class="document-metadata  <?php echo $class_slide_metadata ?>">
                            <?php if ( !$hide_file_caption && $is_document_type_attachment ): ?>
                                <span class="document-metadata__caption">
                                    <?php echo wp_get_attachment_caption(tainacan_get_the_document_raw()); ?>
                                    <br>
                                </span>
                            <?php endif; ?>
                            <?php if ( !$hide_file_name && $is_document_type_attachment ): ?>
                                <span class="document-metadata__name">
                                    <?php echo get_the_title(tainacan_get_the_document_raw()); ?>
                                </span>
                            <?php endif; ?>
                            <br>
                            <?php if ( !$hide_file_description && $is_document_type_attachment ): ?>
                                <span class="document-metadata__description">
                                    <?php echo get_the_content(tainacan_get_the_document_raw()); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php
                    }
                
                } else {
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
                                'after_slide_metadata' => (( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && tainacan_the_item_document_download_link() != '' ) ?
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
                                ) 
                            )
                        )
                    );

                }?>
            </div>
        </section>
    </div>
    
    <div class="my-5 border-bottom border-silver"></div>

<?php endif; ?>