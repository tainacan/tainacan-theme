<?php get_header(); ?>
    <div class="tainacan-title">
        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
            <ul class="list-inline mb-0">
                <li class="list-inline-item text-midnight-blue font-weight-bold">Title</li>
                <li class="list-inline-item float-right"><a href=""><?php _e('Back'); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="row">

        <div class="col col-sm-9 mx-sm-auto p-3">
            <div id="content" role="main">
                <?php if(have_posts()): 
                    while(have_posts()): the_post(); ?>
                        <?php get_template_part('template-parts/single-post', get_post_format()); 
                    endwhile; 
                else:
                    wp_redirect(esc_url( home_url() ) . '/404', 404);
                    exit;
                endif; ?>
            </div><!-- /#content -->
        </div>

    </div><!-- /.row -->
<?php get_footer(); ?>