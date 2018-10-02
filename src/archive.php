<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerHeader' ); ?>
<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menuBellowBanner' ); ?>


<main role="main" class="mt-5 max-large margin-one-column">
    <div class="row">
        <div id="content" class="col-12 <?php if ( is_active_sidebar( 'sidebar-right' ) ) { ?>col-lg-9<?php } ?>">
            <?php get_template_part('template-parts/loop'); ?>
        </div>
        <?php if ( is_active_sidebar( 'sidebar-right' ) ) { 
            get_sidebar(); 
        } ?>
    </div>
</main>
<?php get_footer();