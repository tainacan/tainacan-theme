<?php 
    if (function_exists('tainacan_get_adjacent_items')) {
        $adjacent_items = tainacan_get_adjacent_items();

        if (isset($adjacent_items['next'])) {
            $next_link_url = $adjacent_items['next']['url'];
            $next_title = $adjacent_items['next']['title'];
        } else {
            $next_link_url = false;
        }
        if (isset($adjacent_items['previous'])) {
            $previous_link_url = $adjacent_items['previous']['url'];
            $previous_title = $adjacent_items['previous']['title'];
        } else {
            $previous_link_url = false;
        }

        // In this case, the user entered the page without any query params.
        if (!isset($adjacent_items['previous']) && !isset($adjacent_items['previous'])) {
             //Get the links to the Previous and Next Post
            $previous_link_url = get_permalink( get_previous_post() );
            $next_link_url = get_permalink( get_next_post() );

            //Get the title of the previous post and next post
            $previous_title = get_the_title( get_previous_post() );
            $next_title = get_the_title( get_next_post() );
        }
    } else {
        //Get the links to the Previous and Next Post
        $previous_link_url = get_permalink( get_previous_post() );
        $next_link_url = get_permalink( get_next_post() );

        //Get the title of the previous post and next post
        $previous_title = get_the_title( get_previous_post() );
        $next_title = get_the_title( get_next_post() );
    }
    
    $previous = '';
    $next = '';

    switch (get_theme_mod('tainacan_single_item_navigation_options', 'thumbnail_small')) {

        case 'link':
            $previous = $previous_link_url === false ? '' : '<a rel="prev" href="' . $previous_link_url . '"><i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp; ' . $previous_title . '</a>';
            $next = $next_link_url === false ? '' :'<a rel="next" href="' . $next_link_url . '">' . $next_title . ' &nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i></a>';
        break;

        case 'thumbnail_small':
            //Get the thumnail url of the previous and next post
            if (function_exists('tainacan_get_adjacent_items')) {
                if ($adjacent_items['next']) {
                    $next_thumb = $adjacent_items['next']['thumbnail']['tainacan-small'][0];
                }
                if ($adjacent_items['previous']) {
                    $previous_thumb = $adjacent_items['previous']['thumbnail']['tainacan-small'][0];
                }
            } else {
                $previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-small' );
                $next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-small' );
            }

            // Creates the links
            $previous = $previous_link_url === false ? '' :
                '<a class="has-small-thumbnail" rel="prev" href="' . $previous_link_url . '">' . 
                    '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp;' . 
                    $previous_title . '<img src="' . $previous_thumb . '" alt="">' .
                '</a>';
            $next = $next_link_url === false ? '' :
                '<a class="has-small-thumbnail" rel="next" href="' . $next_link_url . '">' . 
                    '<img src="' . $next_thumb . '" alt="">' . $next_title . 
                    '&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>' .
                '</a>';
        break;

        case 'thumbnail_large':

            if (function_exists('tainacan_get_adjacent_items')) {
                if ($adjacent_items['next']) {
                    $next_thumb = $adjacent_items['next']['thumbnail']['tainacan-medium'][0];
                }
                if ($adjacent_items['previous']) {
                    $previous_thumb = $adjacent_items['previous']['thumbnail']['tainacan-medium'][0];
                }
            } else {
                //Get the thumnail url of the previous and next post
                $previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-medium' );
                $next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-medium' );
            }

            // Creates the links
            $previous = $previous_link_url === false ? '' :
                '<a class="has-large-thumbnail" rel="prev" href="' . $previous_link_url . '">' .
                    '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-36px"></i>&nbsp;' .
                    '<div><img src="' . $previous_thumb . '" alt="">' . $previous_title . 
                '</div></a>';
            $next = $next_link_url === false ? '' :
                '<a class="has-large-thumbnail" rel="next" href="' . $next_link_url . '">' . 
                    '<div><img src="' . $next_thumb . '" alt="">' . $next_title . 
                    '</div>&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-36px"></i>' .
                '</a>';
        break;
        
        case 'none':
        default:
        return '';
    }
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