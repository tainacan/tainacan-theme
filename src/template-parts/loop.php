<?php if(have_posts()): ?>
    <div class="card-columns mt-3">
        <div class="col-sm">
            <?php while(have_posts()): 
                the_post();
                //Index
                if(!is_single())
                            get_template_part('template-parts/list-post');
                else
                    printf('<p>%2</p>', _e('Sorry, no posts matched your criteria.', 'tainacan-theme'));
            endwhile; ?>
        </div>
    </div>
    <nav class="mx-auto">
        <?php echo pagination_bst4(); ?>
    </nav>
<?php else:
    wp_redirect(esc_url( home_url() ) . '/404', 404);
    exit; ?>
<?php endif; ?>