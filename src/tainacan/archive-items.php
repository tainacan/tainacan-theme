<?php get_header(); ?>
<?php get_template_part('tainacan/header-collection'); ?>
	<div class="row">
        <div class="col mx-sm-auto p-0">
            <div id="content" role="main">
                <?php tainacan_the_faceted_search(); ?>
            </div><!-- /#content -->
        </div>
    </div><!-- /.row -->
<?php get_footer(); ?>