<?php get_header(); ?>
<?php if(have_posts()): ?>
	<?php while(have_posts()): the_post(); ?>
	    <div class="tainacan-title mt-5">
	        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
	            <ul class="list-inline mb-1">
	                <li class="list-inline-item text-midnight-blue font-weight-bold title-page">
	                    <?php the_title(); ?>
	                </li>
	                <li class="list-inline-item float-right title-back"><a href="javascript:history.go(-1)"><?php _e('Back'); ?></a></li>
	            </ul>
	        </div>
	    </div>
	    <div class="mt-3 tainacan-single-post">
            <article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
                <header class="mb-4">
                    <div class="header-meta text-muted mb-5">
                        <?php
                            _e('By', 'tainacan-theme');
                            echo ' ';
                            the_author_posts_link();
                            echo ' ';
                            _e('on', 'tainacan-theme');
                        ?>
                    </div>
                    <?php //the_post_thumbnail(); ?>
                </header>
                <section class="tainacan-content text-tundora">
                    <?php
                        tainacan_the_document();
                    ?>
                </section>
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
	    </div>
	<?php endwhile; ?>
<?php else: ?>
	<?php _e('Nothing found', 'tainacan-theme'); ?>
<?php endif; ?>
<?php get_footer(); ?>