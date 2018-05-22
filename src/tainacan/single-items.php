<?php get_header(); ?>

<div class="container-fluid mt-5 max-large">
    <div class="row">
        <div class="col col-sm mx-sm-auto">
            <div id="content" role="main">
            <?php if(have_posts()): ?>
                <?php while(have_posts()): the_post(); ?>
                    <div class="tainacan-title">
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
                                        echo ' ';
                                        tainacan_post_date();
                                    ?>
                                </div>
                                <?php //the_post_thumbnail(); ?>
                            </header>
                            <section class="tainacan-content single-item-collection text-tundora">
                                <div class="single-item-collection--document">
                                    <?php tainacan_the_document(); ?>
                                </div>
                            </section>
                        </article>
                    </div>
                    <div class="tainacan-title my-5">
                        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
                            <ul class="list-inline mb-1">
                                <li class="list-inline-item text-midnight-blue font-weight-bold title-page">
                                    <?php _e('Attachments'); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 tainacan-single-post">
                        <article role="article">
                            <section class="tainacan-content single-item-collection text-tundora">
                                <div class="single-item-collection--attachments">
                                    <?php //tainacan_the_attachment(); ?>
                                </div>
                            </section>
                        </article>
                    </div>
                    <div class="tainacan-title my-5">
                        <div class="border-bottom border-jelly-bean tainacan-title-page" style="border-width: 2px !important;">
                            <ul class="list-inline mb-1">
                                <li class="list-inline-item text-midnight-blue font-weight-bold title-page">
                                    <?php _e('Information'); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 tainacan-single-post">
                        <article role="article">
                            <section class="tainacan-content single-item-collection text-tundora">
                                <div class="single-item-collection--information">
                                    <div class="row">
                                        <div class="col">
                                            <h5>Description</h5>
                                            <span><?php //tainacan_the_description(); ?></span>
                                            <div class="row">
                                                <div class="col">
                                                    <h5>Miniature</h5>
                                                </div>
                                                <div class="col">
                                                    <h5>Voting</h5>
                                                    <div class="col pl-0">
                                                        <h5>Sharing</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p><strong><?php _e('Type'); ?>:</strong></p>
                                            <p><strong><?php _e('Year'); ?>:</strong></p>
                                            <p><strong><?php _e('Pickers'); ?>:</strong></p>
                                            <p><strong><?php _e('Model'); ?>:</strong></p>
                                            <p><strong><?php _e('Brand'); ?>:</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </article>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <?php _e('Nothing found', 'tainacan-theme'); ?>
            <?php endif; ?>
            </div><!-- /#content -->
        </div>
    </div><!-- /.row -->
</div>
<?php get_footer(); ?>