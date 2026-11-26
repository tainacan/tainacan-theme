<?php
/**
 * Template Name: Landing with Breadcrumb
 *
 * Page template without a sidebar, with a breadcrumb.
 *
 * @package tainacan-interface
 */

?>

<?php get_header(); ?>

<div class="max-large margin-one-column">
	<div id="content" role="main">
		<?php get_template_part( 'template-parts/loop', 'singular' ); ?>
	</div><!-- /#content -->
</div>
<?php get_footer(); ?>
