<?php get_header(); ?>
<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>
<!-- Get the menu if is create in panel admin -->
<?php get_template_part( 'template-parts/menubellowbanner' ); ?>

	<?php tainacan_the_faceted_search(); ?>

<?php get_footer(); ?>
