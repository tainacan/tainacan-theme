<?php 
    $previous = '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp; %link';
    $next = '%link &nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>';

    switch (get_theme_mod('tainacan_single_item_navigation_options', 'none')) {

        case 'link':
        break;
        case 'thumbnail_small':
            //Get the thumnail url of the previous and next post
            $previous_thumb = get_the_post_thumbnail_url( get_previous_post(), 'tainacan-small' );
            $next_thumb = get_the_post_thumbnail_url( get_next_post(), 'tainacan-small' );

            //Get the links to the Previous and Next Post
            $previous_link_url = get_permalink( get_previous_post() );
            $next_link_url = get_permalink( get_next_post() );

            //Get the title of the previous post and next post
            $previous_title = get_the_title( get_previous_post() );
            $next_title = get_the_title( get_next_post() );

            // Creates the links
            $previous = '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-30px"></i>&nbsp;' . 
                '<a class="has-small-thumbnail" rel="prev" href="' . $previous_link_url . '">' . $previous_title . 
                    '<img src="' . $previous_thumb . '" alt="">' .
                '</a>';
            $next = '<a class="has-small-thumbnail" rel="next" href="' . $next_link_url . '">' . 
                    '<img src="' . $next_thumb . '" alt="">' . $next_title . 
                '</a>' . '&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>';
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
            $previous = '<i class="tainacan-icon tainacan-icon-arrowleft tainacan-icon-36px"></i>&nbsp;' . 
                '<a class="has-large-thumbnail" rel="prev" href="' . $previous_link_url . '">' .
                    '<img src="' . $previous_thumb . '" alt="">' . $previous_title . 
                '</a>';
            $next = '<a class="has-large-thumbnail" rel="next" href="' . $next_link_url . '">' . 
                    '<img src="' . $next_thumb . '" alt="">' . $next_title . 
                '</a>' . '&nbsp;<i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-36px"></i>';
        break;
        case 'none':
        default:
        return '';

    } 
?>

<div id="item-single-navigation" class="d-flex margin-pagination justify-content-between mt-0">
    <div class="pagination">
        <?php previous_post_link($previous); ?>
    </div>
    <div class="pagination">
        <?php next_post_link($next); ?>
    </div>
</div>