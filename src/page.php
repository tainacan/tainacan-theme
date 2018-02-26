<?php get_header(); ?>

    <div class="container-fluid mt-5">
        <div class="row">

            <div class="col-sm">
                <div id="content" role="main">
                    <?php get_template_part('template-parts/page-content'); ?>
                </div><!-- /#content -->
            </div>

            <?php get_sidebar(); ?>

        </div><!-- /.row -->
    </div><!-- /.container-fluid -->

<?php get_footer(); ?>