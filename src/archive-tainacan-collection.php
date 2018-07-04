<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerHeader' ); ?>
<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menuBellowBanner' ); ?>

<main role="main" class="max-large margin-one-column">
    <div class="row">
        <div class="col col-sm mx-sm-auto">


            <form method="GET" id="tainacan-collection-search">
                
                <?php _e('Order by', 'tainacan-theme'); ?>
                
                <select name="orderby">
                    <option value="date" <?php selected('date', get_query_var('orderby')); ?> >
                        <?php _e('Creation date', 'tainacan-theme'); ?>
                    </option>
                    <option value="title" <?php selected('title', get_query_var('orderby')); ?> >
                        <?php _e('Title', 'tainacan-theme'); ?>
                    </option>
                </select>

                <select name="order">
                    <option value="ASC" <?php selected('ASC', get_query_var('order')); ?> >
                        ASC
                    </option>
                    <option value="DESC" <?php selected('DESC', get_query_var('order')); ?> >
                        DESC
                    </option>
                </select>


                <?php _e('View Mode', 'tainacan-theme'); ?>
                <select name="tainacan_collections_viewmode">
                    <option value="table" <?php selected('table', get_query_var('tainacan_collections_viewmode')); ?> >
                        <?php _e('Table', 'tainacan-theme'); ?>
                    </option>
                    <option value="cards" <?php selected('cards', get_query_var('tainacan_collections_viewmode')); ?> >
                        <?php _e('Cards', 'tainacan-theme'); ?>
                    </option>
                    <option value="grid" <?php selected('grid', get_query_var('tainacan_collections_viewmode')); ?> >
                        <?php _e('Grid', 'tainacan-theme'); ?>
                    </option>
                </select>

                <input type="text" name="s" value="<?php echo get_query_var('s'); ?>" />

                <input type="submit" value="Go!" />

                
            
            </form>


            <?php get_template_part('template-parts/loop-tainacan-collection', get_query_var('tainacan_collections_viewmode')); ?>
        </div>
    </div><!-- /.row -->
</main>
<?php get_footer(); ?>