<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<?php
		if ( function_exists( 'wp_body_open' ) ) {
			wp_body_open();
		} else {
			do_action( 'wp_body_open' );
		}
	?>
	<nav class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0 navbar--border-bottom">
		<div class="container-fluid max-large px-0 margin-one-column" id="topNavbar">
			<?php echo tainacan_get_logo(); ?>

			<div class="navbar-box">
				<?php if ( has_nav_menu( 'navMenubelowHeader' ) ) : ?>
					<nav class="navbar navbar-expand-md navbar-light px-0 menu-belowheader" role="navigation">
						<div class="container-fluid max-large px-0 margin-one-column">
							<button class="navbar-toggler text-heavy-metal border-0 p-2 collapsed" type="button" data-toggle="collapse" data-target="#menubelowHeader" aria-controls="menubelowHeader" aria-expanded="false" aria-label="<?php _e('Open navigation menu', 'tainacan-interface') ?>">
								<span class="tainacan-icon tainacan-icon-menu"></span>
								<span class="tainacan-icon tainacan-icon-close"></span>
							</button>
							<?php
								wp_nav_menu( array(
									'theme_location'  => 'navMenubelowHeader',
									'container'       => 'div',
									'container_class' => 'collapse navbar-collapse',
									'container_id'    => 'menubelowHeader',
									'menu_class'      => 'navbar-nav mr-auto',
									'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
									'walker'          => new WP_Bootstrap_Navwalker(),
								) );
							?>
						</div>
					</nav>
				<?php endif; ?>

				<div class="btn-group" style="padding: 0.6rem 0rem;">
				
				<?php if (!get_theme_mod('tainacan_hide_search_input', false)) : ?>
					<div class="dropdown tainacan-form-dropdown">
						<button class="btn btn-link text-midnight-blue px-1 dropdown-toggle" type="button" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="tainacan-icon tainacan-icon-search"></i>
							<i class="tainacan-icon tainacan-icon-close"></i>
						</button>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
							<?php get_search_form(); ?>
						</div>
					</div>
				<?php endif; ?>

				</div>
			</div>
		</div>
	</nav>

	<a href="javascript:" id="return-to-top" style="<?php echo (get_theme_mod( 'tainacan_footer_color', 'dark' ) == 'colored' ? 'background-color: #2c2d2d;' : '') ?>"><i class="tainacan-icon tainacan-icon-arrowup"></i></a>

    <?php if ( !is_page_template( 'page-templates/landing.php' ) ) : ?>
		<?php tainacan_interface_the_breadcrumb(); ?>
	<?php endif; ?>