<?php 

    $adjacent_links = [
        'next' => '',
        'previous' => ''
    ];

    switch (get_theme_mod('tainacan_single_item_navigation_options', 'thumbnail_small')) {
        case 'thumbnail_small':
            $adjacent_links = tainacan_get_adjacent_item_links('small');
        break;
        case 'thumbnail_large': 
            $adjacent_links = tainacan_get_adjacent_item_links('large');
        break;
        case 'link': 
            $adjacent_links = tainacan_get_adjacent_item_links();
        break;
        case 'none':
        default:
            $adjacent_links = [
                'next' => '',
                'previous' => ''
            ];
    }

    $previous = $adjacent_links['previous'];
    $next = $adjacent_links['next'];
?>
<?php if ($previous !== '' || $next !== '') : ?>
    <div class="tainacan-single-post">
        <?php if ( get_theme_mod('tainacan_single_item_navigation_section_label', __( 'Continue browsing', 'tainacan-interface' )) != '') : ?>
            <h2 class="title-content-items" id="single-item-navigation-label">
                <?php echo get_theme_mod('tainacan_single_item_navigation_section_label', __('Continue browsing', 'tainacan-interface')); ?>
            </h2>
        <?php endif; ?>
        <div id="item-single-navigation" class="d-flex justify-content-between margin-two-column">
            <div class="pagination">
                <?php echo $previous; ?>
            </div>
            <div class="pagination">
                <?php echo $next; ?>
            </div>
        </div>
    </div>
<?php endif; ?>