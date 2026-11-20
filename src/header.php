<?php
/**
 * Theme header: document head, skip link, site header and navigation.
 *
 * @package tainacan-interface
 */

?>
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
	<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'tainacan-interface' ); ?></a>
	<?php

	if ( ! get_theme_mod( 'tainacan_use_block_template_parts_on_header', false ) ) :
		?>
	<nav class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0 navbar--border-bottom">
		<div class="container-fluid max-large px-0 margin-one-column" id="topNavbar">
		<?php echo wp_kses_post( (string) tainacan_get_logo() ); ?>

			<div class="navbar-box">
			<?php if ( has_nav_menu( 'navMenubelowHeader' ) ) : ?>
					<nav class="navbar navbar-expand-md navbar-light px-0 menu-belowheader" role="navigation">
						<div class="container-fluid max-large px-0 margin-one-column">
							<button class="navbar-toggler text-heavy-metal border-0 p-2 collapsed" type="button" data-toggle="collapse" data-target="#menubelowHeader" aria-controls="menubelowHeader" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open navigation menu', 'tainacan-interface' ); ?>">
								<span class="tainacan-icon tainacan-icon-menu"></span>
								<span class="tainacan-icon tainacan-icon-close"></span>
							</button>
							<?php
								wp_nav_menu(
									array(
										'theme_location'  => 'navMenubelowHeader',
										'container'       => 'div',
										'container_class' => 'collapse navbar-collapse',
										'container_id'    => 'menubelowHeader',
										'menu_class'      => 'navbar-nav mr-auto',
										'fallback_cb'     => 'Tainacan_Interface_Nav_Walker::fallback',
										'walker'          => new Tainacan_Interface_Nav_Walker(),
									)
								);
							?>
						</div>
					</nav>
				<?php endif; ?>

				<div class="btn-group" style="padding: 0.6rem 0;">
				<?php
				tainacan_interface_the_social_links( 'header' );
				$tainacan_interface_button = tainacan_interface_opt( 'header_button_label' );
				$tainacan_interface_target = tainacan_interface_opt( 'header_button_url' );
				if ( '' !== $tainacan_interface_button && '' !== $tainacan_interface_target ) {
					echo '<a class="tt2-header-button" href="' . esc_url( $tainacan_interface_target ) . '">' . esc_html( $tainacan_interface_button ) . '</a>';
				}
				?>
				
				<?php if ( ! get_theme_mod( 'tainacan_hide_search_input', false ) ) : ?>
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
		<?php if ( tainacan_interface_opt( 'header_band' ) ) : ?>
			<div class="tt2-band tt2-site-band" aria-hidden="true"></div>
		<?php endif; ?>

		<?php
	else :
		block_template_part( 'header' );
endif;
	?>

	<div id="main-content" tabindex="-1"></div>

	<?php if ( tainacan_interface_opt( 'back_top' ) ) : ?>
	<a href="#" id="return-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'tainacan-interface' ); ?>"><i class="tainacan-icon tainacan-icon-arrowup"></i></a>
	<?php endif; ?>

	<?php if ( ! is_page_template( 'page-templates/landing.php' ) ) : ?>
		<?php echo wp_kses_post( tainacan_interface_the_breadcrumb() ); ?>
	<?php endif; ?>