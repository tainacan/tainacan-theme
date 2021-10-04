<?php

$attachments = tainacan_get_the_attachments();
$is_gallery_mode = get_theme_mod( 'tainacan_single_item_gallery_mode', false );
$hide_file_name = get_theme_mod('tainacan_single_item_hide_files_name_main', true);
$hide_file_caption = get_theme_mod('tainacan_single_item_hide_files_caption_main', true);
$hide_file_description = get_theme_mod('tainacan_single_item_hide_files_description_main', true);
$disable_gallery_lightbox = get_theme_mod('tainacan_single_item_disable_gallery_lightbox', false);

if ( !empty( $attachments )  || ( get_theme_mod( 'tainacan_single_item_gallery_mode', false) && tainacan_has_document() ) ) {
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

                $media_items_thumbs = array();
                $media_items_main = array();

                if ($is_gallery_mode) {

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
                    
                    foreach ( $attachments as $attachment ) {
                        $media_items_main[] =
                            tainacan_get_the_media_component_slide(array(
                                'after_slide_metadata' => (( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && tainacan_the_item_attachment_download_link($attachment->ID) != '' ) ?
                                                                '<span class="tainacan-item-file-download">' . tainacan_the_item_attachment_download_link($attachment->ID) . '</span>'
                                                        : ''),
                                'media_content' => tainacan_get_attachment_as_html($attachment->ID, 0),
                                'media_content_full' => wp_attachment_is('image', $attachment->ID) ? wp_get_attachment_image( $attachment->ID, 'full', false) : ('<div class="attachment-without-image tainacan-embed-container"><iframe id="tainacan-attachment-iframe" src="' . tainacan_get_attachment_html_url($attachment->ID) . '"></iframe></div>'),
                                'media_title' => $attachment->post_title,
                                'media_description' => $attachment->post_content,
                                'media_caption' => $attachment->post_excerpt,
                                'media_type' => $attachment->post_mime_type,
                                'class_slide_metadata' => $class_slide_metadata
                            ));
                    }
                }
                if ( 
                    (tainacan_has_document() && $attachments && sizeof($attachments) > 0 ) ||
                    (!tainacan_has_document() && $attachments && sizeof($attachments) > 1 ) 
                ) {
                    if ( tainacan_has_document() ) {
                        $is_document_type_attachment = tainacan_get_the_document_type() === 'attachment';
                        $media_items_thumbs[] =
                            tainacan_get_the_media_component_slide(array(
                                'media_content' => get_the_post_thumbnail(null, 'tainacan-medium'),
                                'media_content_full' => $is_document_type_attachment ? tainacan_get_the_document(0, 'full') : ('<div class="attachment-without-image">' . tainacan_get_the_document(0, 'full') . '</div>'),
                                'media_title' => $is_document_type_attachment ? get_the_title(tainacan_get_the_document_raw()) : '',
                                'media_description' => $is_document_type_attachment ? get_the_content(tainacan_get_the_document_raw()) : '',
                                'media_caption' => $is_document_type_attachment ? wp_get_attachment_caption(tainacan_get_the_document_raw()) : '',
                                'media_type' => tainacan_get_the_document_type(),
                                'class_slide_metadata' => 'hide-caption hide-description ' . ( get_theme_mod('tainacan_single_item_hide_files_name', false) ? 'hide-name' : '' )
                            ));
                        
                    }
                    foreach ( $attachments as $attachment ) {
                        $media_items_thumbs[] = 
                            tainacan_get_the_media_component_slide(array(
                                'media_content' => wp_get_attachment_image( $attachment->ID, 'tainacan-medium', false ),
                                'media_content_full' => wp_attachment_is('image', $attachment->ID) ? wp_get_attachment_image( $attachment->ID, 'full', false) : ('<div class="attachment-without-image tainacan-embed-container"><iframe id="tainacan-attachment-iframe" src="' . tainacan_get_attachment_html_url($attachment->ID) . '"></iframe></div>'),
                                'media_title' => $attachment->post_title,
                                'media_description' => $attachment->post_content,
                                'media_caption' => $attachment->post_excerpt,
                                'media_type' => $attachment->post_mime_type,
                                'class_slide_metadata' => 'hide-caption hide-description ' . ( get_theme_mod('tainacan_single_item_hide_files_name', false) ? 'hide-name' : '' )
                            ));
                    }
                }
                tainacan_the_media_component(
                    'tainacan-item-attachments_id-' . $post->ID,
                    $media_items_thumbs,
                    $is_gallery_mode ? $media_items_main : null,
                    array(
                        'class_main_div' => '',
                        'class_thumbs_div' => '',
                        'swiper_thumbs_options' => $is_gallery_mode ? '' : array(
                            'navigation' => array(
                                'nextEl' => '.swiper-navigation-next_' . 'tainacan-item-attachments_id-' . $post->ID . '-thumbs',
                                'prevEl' => '.swiper-navigation-prev_' . 'tainacan-item-attachments_id-' . $post->ID . '-thumbs',
                                'preloadImages' => false,
                                'lazy' => true
                            )
                        ),
                        'swiper_main_options' => $is_gallery_mode ? array(
                            'navigation' => array(
                                'nextEl' => '.swiper-navigation-next_' . 'tainacan-item-attachments_id-' . $post->ID . '-main',
                                'prevEl' => '.swiper-navigation-prev_' . 'tainacan-item-attachments_id-' . $post->ID . '-main',
                                'preloadImages' => false,
                                'lazy' => true
                            )
                        ) : '',
                        'disable_lightbox' => $is_gallery_mode ? $disable_gallery_lightbox : false,
                    )
                );
            ?>
        </section>
    </div>

    <div class="my-5 border-bottom border-silver"></div>
<?php
}
