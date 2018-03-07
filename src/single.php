<?php get_header(); ?>

<div class="container-fluid mt-5">
    <div class="row">

        <div class="col col-sm-9 mx-sm-auto p-3">
            <div id="content" role="main">
                <?php get_template_part('template-parts/single-post', get_post_format()); ?>
            </div><!-- /#content -->
        </div>

    </div><!-- /.row -->
</div><!-- /.container-fluid -->

<?php get_footer(); ?>