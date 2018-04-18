<?php get_header(); ?>
    <div class="row">
        <div class="col-sm">
            <div id="content" role="main">
                <?php get_template_part('template-parts/loop'); ?>
            </div><!-- /#content -->
        </div>
        <?php if ( is_active_sidebar( 'sidebar-right' ) ) { 
            get_sidebar(); 
        } ?>
    </div>
<?php get_footer();