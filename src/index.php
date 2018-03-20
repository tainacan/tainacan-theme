<?php get_header(); ?>
    <div class="row">
        <div class="col-sm">
            <div id="content" role="main">
                <?php get_template_part('template-parts/loop'); ?>
            </div><!-- /#content -->
        </div>
        <?php get_sidebar(); ?>
    </div>
<?php get_footer();