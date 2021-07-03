<?php

if ( function_exists('tainacan_the_related_items_carousel') && get_theme_mod('tainacan_single_item_enable_related_items_section', true) && tainacan_has_related_items() ) : ?>

<div class="mt-3 tainacan-single-post">

    <?php if ( get_theme_mod('tainacan_single_item_related_items_section_label', __( 'Items related to this', 'tainacan-interface' )) != '') : ?>
        <h2 class="title-content-items" id="single-item-related-items-label">
            <?php echo esc_html( get_theme_mod('tainacan_single_item_related_items_section_label', __( 'Items related to this', 'tainacan-interface' )) ); ?>
        </h2>
    <?php endif; ?>
    <section class="tainacan-content single-item-collection margin-two-column">
        <div class="single-item-collection--related-items justify-content-center">
            <div class="row">
            <?php 
                tainacan_the_related_items_carousel([
                    // 'class_name' => 'mt-2 tainacan-single-post',
                    // 'collection_heading_class_name' => 'title-content-items',
                    'collection_heading_tag' => 'h3',
                    'carousel_args' => [
                        'max_items_per_screen' => get_theme_mod('tainacan_single_item_related_items_max_items_per_screen', 6)
                    ]
                ]);
            ?>
            </div>
        <div>
    </section>
</div>

<?php endif; ?>