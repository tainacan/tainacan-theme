<?php if(have_posts()): ?>
    <div class="tainacan-title">
        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
            <ul class="list-inline mb-0">
                <li class="list-inline-item text-midnight-blue font-weight-bold">
                    <?php 
                        if(is_home()) echo 'Blog'; 
                        elseif(is_single()) the_title(); 
                    ?>
                </li>
                <li class="list-inline-item float-right"><a href=""><?php _e('Back'); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="mt-3 <?php if(is_home()) echo 'px-sm-5 mx-sm-5 tainacan-list-post'; elseif(is_single()) echo 'tainacan-single-post'; ?>">
        <?php while(have_posts()): 
            the_post();
            //List Post
            if(is_home()){
                get_template_part('template-parts/list-post');
            }
            //View Post
            elseif(is_single()){
                get_template_part('template-parts/single-post');
            } else {
                printf('<p>%2</p>', __('Sorry, no posts matched your criteria.', 'tainacan-theme'));
            }
        endwhile; ?>
    </div>
    <?php echo tainacan_pagination(3); ?>
<?php else:
    wp_redirect(esc_url( home_url() ) . '/404', 404);
    exit; ?>
<?php endif; ?>