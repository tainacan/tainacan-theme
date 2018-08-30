<article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
    <header class="mb-4">
        <div class="header-meta text-muted mb-5 d-flex">
            <?php if(!is_singular('page')) { ?>
                <?php tainacan_post_date(); ?> &nbsp;<?php printf(__('by&nbsp;%s', 'tainacan-theme'), get_the_author_posts_link()); ?>
            <?php } ?>
            <div class="btn-group ml-auto" role="group">
                <?php if ( true == get_theme_mod( 'facebook_share', true ) ) : ?> 
                    <a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" class="" target="_blank">
                        <img src="<?php echo get_template_directory_uri().'/assets/images/facebook-circle.png'; ?>" alt="">
                    </a>
                <?php endif; ?>
                <?php if ( true == get_theme_mod( 'twitter_share', true ) ) : ?> 
                    <?php $twitter_option = get_option( 'twitter_user'); ?>
                    <?php $via = !empty($twitter_option) ? '&amp;via=' . get_option( 'twitter_user') : ''; ?>
                    <a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?><?php echo $via; ?>" target="_blank" class="">
                        <img src="<?php echo get_template_directory_uri().'/assets/images/twitter-circle.png'; ?>" alt="">
                    </a>
                <?php endif; ?>
                <?php if ( true == get_theme_mod( 'google_share', true ) ) : ?> 
                    <a href="https://plus.google.com/share?url=<?php the_permalink(); ?>" target="_blank" class="">
                        <img src="<?php echo get_template_directory_uri().'/assets/images/google-plus-circle.png'; ?>" alt="">
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php the_post_thumbnail(); ?>
    </header>
    <section class="tainacan-content text-black margin-two-column">
        <?php
            the_content();
            wp_link_pages();
        ?>
    </section>
    <?php if(!is_singular('page')) { ?>
    <footer class="mt-5 border-top pt-3">
        <p>
            <?php 
                _e('Category: ', 'tainacan-theme'); 
                the_category(' <span>|</span> ') ?> -
                <?php if (has_tag()) { 
                    the_tags('Tags: ', ' <span>|</span> '); ?> -
                <?php } 
                _e('Comments', 'tainacan-theme'); echo ':'; ?> 
                <?php comments_popup_link(__('None', 'tainacan-theme'), '1', '%'); ?>
        </p>
    </footer>
    <?php } ?>
</article>
<div class="row">
	<!-- Container -->
	<div class="col mt-3 mx-auto">
        <?php
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif; ?>
    </div>
</div>