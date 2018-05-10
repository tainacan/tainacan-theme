<?php get_header('homecollection'); ?>
	<div class="row">
        <div class="col mx-sm-auto p-0">
            <div id="content" role="main">
                <div id="tainacan-items-page" collection-id="<?php echo tainacan_get_collection_id(); ?>"></div>
            </div><!-- /#content -->
        </div>
    </div><!-- /.row -->
<?php get_footer(); ?>