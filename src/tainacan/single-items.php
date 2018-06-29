<?php get_header(); ?>

<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menuBellowBanner' ); ?>

<main class="mt-5 max-large margin-one-column">
    <div class="row">
        <div class="col col-sm mx-sm-auto">
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
                    <div class="mt-3 tainacan-single-post collection-single-item">
                        <article role="article" id="post_<?php the_ID()?>" <?php post_class()?>>
                            <header class="mb-4">
                                <div class="header-meta text-muted mb-5">
                                    <?php _e('By ', 'tainacan-theme'); the_author_posts_link(); ?>
                                    <span class="time"><strong><?php _e('Send date', 'tainacan-theme'); ?>:</strong> <?php tainacan_post_date(); ?></span>
                                </div>
                                <?php //the_post_thumbnail(); ?>
                            </header>
                            <section class="tainacan-content single-item-collection margin-two-column">
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
                            <section class="tainacan-content single-item-collection margin-two-column">
                                <div class="single-item-collection--attachments">
                                    <?php
                                        $images = get_posts( array (
                                            'post_parent' => get_the_ID(),
                                            'post_type' => 'attachment',
                                            'post_per_page' => -1,
                                            'exclude' => get_post_thumbnail_id( get_the_ID() )
                                        ));
                                        if ( empty($images) ) {
                                            echo 'no attachments here';
                                        } else {
                                            foreach ( $images as $attachment ) {
                                                echo '<div class="single-item-collection--attachments-img">' . wp_get_attachment_image( $attachment->ID, 'medium' ) . '</div>';
                                            }
                                        }
                                    ?>
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
                            <section class="tainacan-content single-item-collection margin-two-column">
                                <div class="single-item-collection--information justify-content-center">
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="card border-0">
                                                <div class="card-body bg-white border-0 pl-0 pt-0 pb-1">
                                                    <h3>Miniature</h3>
                                                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID()) ?>" class="item-card--thumbnail mt-2">
                                                </div>
                                            </div>
                                            <div class="card border-0 my-3">
                                                <div class="card-body bg-white border-0 pl-0 pt-0 pb-1">
                                                    <h3>Sharing</h3>
                                                    <div class="btn-group" role="group">
                                                        <a href="#" class="item-card-link--sharing"><img src="<?=get_template_directory_uri().'/assets/images/facebook-circle.png'; ?>" alt=""></a>
                                                        <a href="#" class="item-card-link--sharing"><img src="<?=get_template_directory_uri().'/assets/images/twitter-circle.png'; ?>" alt=""></a>
                                                        <a href="#" class="item-card-link--sharing"><img src="<?=get_template_directory_uri().'/assets/images/google-plus-circle.png'; ?>" alt=""></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col s-item-collection--metadata">
                                                <?php 
                                                    $args = ['before_title' => '<div class="card border-0"><div class="card-body bg-white border-0 pl-0 pt-0 pb-1"><h3>', 'after_title' => '</h3></div>', 'after_value' => '</p></div>'];
                                                    //$field = null;
                                                    tainacan_the_metadata(null, $args); 
                                                ?>
                                        </div>
                                    </div>
                                </div>
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
        </div>
    </div><!-- /.row -->
</main>
<?php get_footer(); ?>
<script>
    jQuery('#topNavbar').addClass('b-bottom-top');
    jQuery('nav.menu-belowheader').removeClass('border-bottom');
    jQuery('nav.menu-belowheader .max-large').addClass('b-bottom-bellow');
</script>