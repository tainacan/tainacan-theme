<?php if(have_posts()): 
    while(have_posts()): the_post(); ?>
        <article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
            <header class="mb-4">
                <h1>
                    <?php the_title()?>
                </h1>
                <div class="header-meta text-muted">
                    <?php
                        _e('By ', 'tainacan-theme');
                        the_author_posts_link();
                        _e(' on ', 'tainacan-theme');
                        tainacan_post_date();
                    ?>
                </div>
            </header>
            <main>
                <?php
                    the_post_thumbnail();
                    the_content();
                    wp_link_pages();
                ?>
            </main>
            <footer class="mt-5 border-top pt-3">
                <p>
                    <?php 
                        _e('Category: ', 'tainacan-theme'); 
                        the_category(', ') ?> | 
                        <?php if (has_tag()) { 
                            the_tags('Tags: ', ', '); ?> | 
                        <?php } 
                        _e('Comments', 'tainacan-theme'); echo ':'; ?> 
                        <?php comments_popup_link(__('None', 'tainacan-theme'), '1', '%'); ?>
                </p>
                <div class="author-bio media border-top pt-3">
                    <?php get_avatar(); ?>
                <div class="media-body ml-3">
                    <p class="h4 author-name">
                        <?php the_author_posts_link(); ?>
                    </p>
                    <p class="author-description">
                        <?php the_author_description(); ?>
                    </p>
                    <p class="author-other-posts mb-0 border-top pt-3">
                        <?php _e('Other posts by ', 'tainacan-theme'); the_author_posts_link(); ?>
                    </p>
                </div>
                </div><!-- /.author-bio -->
            </footer>
        </article>
        <?php
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;
    endwhile; 
else:
    wp_redirect(esc_url( home_url() ) . '/404', 404);
    exit;
endif;
?>