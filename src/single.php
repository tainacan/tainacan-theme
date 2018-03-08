<?php get_header(); ?>
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