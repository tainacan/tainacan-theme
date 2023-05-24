<?php

$order_option = get_theme_mod( 'tainacan_single_item_related_items_order', 'title_asc' );

$order_option_split = explode( '_', $order_option ); 
$order_by = $order_option_split[0] ? $order_option_split[0] : 'title';
$order = $order_option_split[1] ? $order_option_split[1] : 'asc';

if ( !in_array($order_by, [ 'title', 'date', 'modified' ]) )
    $order_by = 'title';

if ( !in_array($order, [ 'asc', 'desc' ]) )
    $order = 'asc';

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
                    'items_list_layout' => get_theme_mod( 'tainacan_single_item_related_items_layout', 'carousel' ),
                    'collection_heading_tag' => 'h3',
                    'order' => $order,
                    'orderby' => $order_by,
                    'dynamic_items_args' => [
                        'max_columns_count' => get_theme_mod('tainacan_single_item_related_items_max_columns_count', 4)
                    ],
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