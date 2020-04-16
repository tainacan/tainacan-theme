<?php if ( tainacan_has_document() && !get_theme_mod( 'tainacan_single_item_gallery_mode', false )) : ?>
    <div class="pt-4 tainacan-single-post">
        <?php if ( get_theme_mod('tainacan_single_item_document_section_label', __( 'Document', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-document-label">
                <?php echo get_theme_mod('tainacan_single_item_document_section_label', __( 'Document', 'tainacan-interface' )); ?>
            </h2>
        <?php endif; ?>
        <section class="tainacan-content single-item-collection margin-two-column">
            <div class="single-item-collection--document">
                <?php tainacan_the_document(); ?>
                <?php if( function_exists('tainacan_the_item_document_download_link') && tainacan_the_item_document_download_link() != '' ) {
                    echo '<span class="tainacan-item-document-download">';
                    echo tainacan_the_item_document_download_link();
                    echo '</span>';
                } ?>
            </div>
        </section>
    </div>
    <div class="tainacan-title my-5">
        <div class="border-bottom border-silver tainacan-title-page" style="border-width: 1px !important;">
        </div>
    </div>
<?php endif; ?>