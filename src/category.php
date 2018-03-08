<?php get_header(); ?>
    <div class="row">

        <div class="col-sm">
            <div id="content" role="main">
                <header class="mb-4 border-bottom">
                    <h1>
                        <?php _e('Category: ', 'tainacan-theme'); echo single_cat_title(); ?>
                    </h1>
                </header>
                <?php get_template_part('template-parts/index-loop'); ?>
            </div><!-- /#content -->
        </div>

        <?php get_sidebar(); ?>

    </div><!-- /.row -->
<?php get_footer(); ?>