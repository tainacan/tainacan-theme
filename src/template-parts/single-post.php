<article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
    <header class="mb-4">
        <h1>
            <?php the_title()?>
        </h1>
        <div class="header-meta text-muted">
            <?php
                _e('By', 'tainacan-theme');
                echo ' ';
                the_author_posts_link();
                echo ' ';
                _e('on', 'tainacan-theme');
                echo ' ';
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
    </footer>
</article>
<?php
if ( comments_open() || get_comments_number() ) :
    comments_template();
endif; ?>