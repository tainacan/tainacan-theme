<?php if(have_posts()): ?>
    <?php if(!is_single()) { $title = 'Blog'; } ?>
    <div class="px-sm-3">
        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
            <ul class="list-inline mb-0">
                <li class="list-inline-item text-midnight-blue font-weight-bold"><?php echo $title; ?></li>
                <li class="list-inline-item float-right"><a href=""><?php _e('Back'); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="mt-3 px-sm-5 mx-sm-5 <?php if(!is_single()) : echo 'blog-posts'; endif; ?>">
        <?php while(have_posts()): 
            the_post();
            //Index
            if(!is_single())
                        get_template_part('template-parts/list-post');
            else
                printf('<p>%2</p>', _e('Sorry, no posts matched your criteria.', 'tainacan-theme'));
        endwhile; ?>
    </div>
    <?php echo tainacan_pagination(3); ?>
<?php else:
    wp_redirect(esc_url( home_url() ) . '/404', 404);
    exit; ?>
<?php endif; ?>