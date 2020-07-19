<?php 

    //Get the links to the Previous and Next Post
    $previous_link_url = get_permalink( get_previous_post() );
    $next_link_url = get_permalink( get_next_post() );

    //Get the title of the previous post and next post
    $previous_title = get_the_title( get_previous_post() );
    $next_title = get_the_title( get_next_post() );
    
    $previous = '';
    $next = '';

    switch (get_theme_mod('tainacan_single_item_navigation_options', 'none')) {

        case 'link':
            $previous = '<a rel="prev" href="' . $previous_link_url . '"><i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp; ' . $previous_title . '</a>';
            $next = '<a rel="next" href="' . $next_link_url . '">' . $next_title . ' &nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i></a>';
        break;

        case 'thumbnail_small':
            //Get the thumnail url of the previous and next post
            $previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-small' );
            $next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-small' );

            // Creates the links
            $previous = 
                '<a class="has-small-thumbnail" rel="prev" href="' . $previous_link_url . '">' . 
                    '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp;' . 
                    $previous_title . '<img src="' . $previous_thumb . '" alt="">' .
                '</a>';
            $next = 
                '<a class="has-small-thumbnail" rel="next" href="' . $next_link_url . '">' . 
                    '<img src="' . $next_thumb . '" alt="">' . $next_title . 
                    '&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>' .
                '</a>';
        break;

        case 'thumbnail_large':
            //Get the thumnail url of the previous and next post
            $previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-medium' );
            $next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-medium' );

            //Get the links to the Previous and Next Post
            $previous_link_url = get_permalink( get_previous_post() );
            $next_link_url = get_permalink( get_next_post() );

            //Get the title of the previous post and next post
            $previous_title = get_the_title( get_previous_post() );
            $next_title = get_the_title( get_next_post() );

            // Creates the links
            $previous = 
                '<a class="has-large-thumbnail" rel="prev" href="' . $previous_link_url . '">' .
                    '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-36px"></i>&nbsp;' .
                    '<div><img src="' . $previous_thumb . '" alt="">' . $previous_title . 
                '</div></a>';
            $next = 
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

<div class="tainacan-single-post">
    <h2 class="title-content-items"><?php echo __('Also in this collection', 'tainacan-interface') ?></h2>
    <div id="item-single-navigation" class="d-flex justify-content-between margin-two-column">
        <div class="pagination">
            <?php previous_post_link($previous); ?>
        </div>
        <div class="pagination">
            <?php next_post_link($next); ?>
        </div>
    </div>
</div>