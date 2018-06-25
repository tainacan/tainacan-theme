<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerHeader' ); ?>
<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menuBellowBanner' ); ?>

<main role="main" class="max-large margin-one-column">
    <div class="row">
        <div class="col col-sm mx-sm-auto">
            <?php get_template_part('template-parts/loop'); ?>
        </div>
    </div><!-- /.row -->
</main>
<?php get_footer(); ?>