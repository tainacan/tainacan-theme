<?php get_header(); ?>
    <div class="row">

        <div class="col-sm">
            <div id="content" role="main">
                <?php get_template_part('template-parts/page-content'); ?>
            </div><!-- /#content -->
        </div>

        <?php get_sidebar(); ?>

    </div><!-- /.row -->
<?php get_footer(); ?>