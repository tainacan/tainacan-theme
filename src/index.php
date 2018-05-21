<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerHeader' ); ?>
<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menuBellowBanner' ); ?>

<div class="container-fluid mt-5 max-large">
    <div class="row">
        <div class="col-sm">
            <div id="content" role="main">
                <?php get_template_part('template-parts/loop'); ?>
            </div><!-- /#content -->
        </div>
        <?php if ( is_active_sidebar( 'sidebar-right' ) ) { 
            get_sidebar(); 
        } ?>
    </div>
</div>
<?php get_footer();