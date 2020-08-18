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
        <div class="d-flex flex-wrap justify-content-between align-items-center">

            <?php if ( get_theme_mod('tainacan_single_item_navigation_section_label', __( 'Continue browsing', 'tainacan-interface' )) != '') : ?>
                <h2 class="mb-0 title-content-items" id="single-item-navigation-label">
                    <?php echo esc_html( get_theme_mod( 'tainacan_single_item_navigation_section_label', __('Continue browsing', 'tainacan-interface') ) ); ?>
                </h2>
            <?php endif; ?>

            <div id="item-single-navigation" class="d-flex align-items-center justify-center margin-one-column">
                <div class="pagination">
                    <?php echo $previous; ?>
                </div>
                <div class="pagination">
                    <?php echo $next; ?>
                </div>
            </div>

            <div style="margin: 0 4.1666667%"  class="pagination">
                <a class="d-inline-flex align-items-center" href="<?php echo tainacan_get_source_item_list_url(); ?>">
                    <i class="tainacan-icon tainacan-icon-viewtable tainacan-icon-1-25em"></i>&nbsp;&nbsp;<span><?php echo __('Back to items list', 'tainacan-interface') ?></span>
                </a>  
            </div>

        </div>
    </div>
<?php endif; ?>