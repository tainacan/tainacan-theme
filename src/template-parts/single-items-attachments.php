<?php
    if (function_exists('tainacan_get_the_attachments')) {
        $attachments = tainacan_get_the_attachments();
    } else {
        // compatibility with pre 0.11 tainacan plugin
        $attachments = array_values(
            get_children(
                array(
                    'post_parent' => $post->ID,
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'order' => 'ASC',
                    'numberposts'  => -1,
                )
            )
        );
    }
?>

<?php if ( !empty( $attachments )  || ( get_theme_mod( 'tainacan_single_item_gallery_mode', false) && tainacan_has_document() )) : ?>

    <div class="mt-3 tainacan-single-post">
        <?php if ( !get_theme_mod( 'tainacan_single_item_gallery_mode', false ) && get_theme_mod('tainacan_single_item_attachments_section_label', __( 'Attachments', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-attachments-label">
                <?php echo get_theme_mod('tainacan_single_item_attachments_section_label', __( 'Attachments', 'tainacan-interface' )); ?>
            </h2>
        <?php endif; ?>
        <?php if ( get_theme_mod( 'tainacan_single_item_gallery_mode', false ) && get_theme_mod('tainacan_single_item_documents_section_label', __( 'Documents', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-documents-label">
                <?php echo get_theme_mod('tainacan_single_item_documents_section_label', __( 'Documents', 'tainacan-interface' )); ?>
            </h2>
        <?php endif; ?>
        <section class="tainacan-content single-item-collection margin-two-column">
            <?php if (get_theme_mod( 'tainacan_single_item_gallery_mode', false )): ?>
                <div class="single-item-collection--gallery">
                    <?php if ( tainacan_has_document() ) : ?>
                        <section class="tainacan-content single-item-collection margin-two-column">
                            <div class="single-item-collection--document">
                                <?php 
                                    tainacan_the_document(); 
                                    if ( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && function_exists('tainacan_the_item_document_download_link') && tainacan_the_item_document_download_link() != '' ) {
                                        echo '<span class="tainacan-item-file-download">' . tainacan_the_item_document_download_link() . '</span>';
                                    } 
                                ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    <?php foreach ( $attachments as $attachment ) { ?>
                        <section class="tainacan-content single-item-collection margin-two-column">
                            <div class="single-item-collection--document">
                                <?php 
                                    if ( function_exists('tainacan_get_single_attachment_as_html') ) {
                                        tainacan_get_single_attachment_as_html($attachment->ID);
                                    }
                                    if ( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && function_exists('tainacan_the_item_attachment_download_link') && tainacan_the_item_attachment_download_link($attachment->ID) != '' ) {
                                        echo '<span class="tainacan-item-file-download">' . tainacan_the_item_attachment_download_link($attachment->ID) . '</span>';
                                    } 
                                ?>
                            </div>
                        </section>	
                    <?php } ?>
                </div>
                <?php if ( (tainacan_has_document() && $attachments && sizeof($attachments) > 0 ) || (!tainacan_has_document() && $attachments && sizeof($attachments) > 1 ) ) : ?>	
                    <div class="single-item-collection--gallery-items">
                        <?php if ( tainacan_has_document() ) : ?>
                            <div class="single-item-collection--attachments-file">
                                <?php
                                    the_post_thumbnail('tainacan-medium-full', array('class' => 'item-card--thumbnail mt-2'));
                                    echo '<br>';
                                ?>
                                <span class="single-item-file-name <?php if (get_theme_mod('tainacan_single_item_hide_files_name', false)) echo 'sr-only' ?>"><?php echo __( 'Document', 'tainacan-interface' ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php foreach ( $attachments as $attachment ) { ?>
                            <div class="single-item-collection--attachments-file">
                                <div class="<?php if (!wp_get_attachment_image( $attachment->ID, 'tainacan-interface-item-attachments')) echo'attachment-without-image'; ?>">
                                    <?php
                                        echo wp_get_attachment_image( $attachment->ID, 'tainacan-interface-item-attachments', true );
                                        echo '<br>';
                                    ?>
                                    <span class="single-item-file-name <?php if (get_theme_mod('tainacan_single_item_hide_files_name', false)) echo 'sr-only' ?>"><?php echo get_the_title( $attachment->ID ); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="single-item-collection--attachments">
                    <?php foreach ( $attachments as $attachment ) { ?>
                        <?php
                        if ( function_exists('tainacan_get_attachment_html_url') ) {
                            $href = tainacan_get_attachment_html_url($attachment->ID);
                        } else {
                            $href = wp_get_attachment_url($attachment->ID, 'large');
                        }
                        ?>
                        <div class="single-item-collection--attachments-file">
                            <a 
                                class="<?php if (!wp_get_attachment_image( $attachment->ID, 'tainacan-interface-item-attachments')) echo'attachment-without-image'; ?>"
                                href="<?php echo $href; ?>"
                                data-toggle="lightbox"
                                data-gallery="example-gallery">
                                <?php
                                    echo wp_get_attachment_image( $attachment->ID, 'tainacan-interface-item-attachments', true );
                                    echo '<br>';
                                ?>
                                <span class="single-item-file-name <?php if (get_theme_mod('tainacan_single_item_hide_files_name', false)) echo 'sr-only' ?>"><?php echo get_the_title( $attachment->ID ); ?></span>
                            </a>
                        </div>
                    <?php }
                    ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="my-5 border-bottom border-silver"></div>

<?php endif; ?>