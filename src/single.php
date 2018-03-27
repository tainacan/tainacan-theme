<?php get_header(); ?>
    <div class="row">
        <div class="col col-sm-9 mx-sm-auto">
            <div id="content" role="main">
                <?php get_template_part('template-parts/loop'); ?>
            </div><!-- /#content -->
        </div>
        <?php get_sidebar(); ?>
    </div><!-- /.row -->
<?php get_footer(); ?>