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
	<?php if ( true == get_theme_mod( 'tainacan_accessibility_setting', false ) ) { ?>
		<!-- ACESSIBILITY MENU -->
		<div class="accessibility-bar">
			<nav class="accessibility-bar__container">
				<ul class="accessibility-shortcuts" role="menubar">
					<li role="menuitem"><a href="#tainacan-site-content" accesskey="c"><span>c</span><?php _e('Skip to content', 'tainacan-interface') ?></a></li>
					<li role="menuitem"><a href="#tainacan-site-menu" accesskey="m"><span>m</span><?php _e('Skip to menu', 'tainacan-interface') ?></a></li>
					<li role="menuitem"><a href="#tainacan-site-search" accesskey="b"><span>b</span><?php _e('Skip to search', 'tainacan-interface') ?></a></li>
					<li role="menuitem"><a href="#tainacan-site-footer" accesskey="r"><span>r</span><?php _e('Skip to footer', 'tainacan-interface') ?></a></li>
				</ul>

				<ul class="accessibility-options" role="menubar">
					<li role="menuitem">
						<span><?php _e('Font', 'tainacan-interface') ?></span>
						<button type="button" class="button-text-minus" accesskey="5">A-</button>
						<button type="button" class="button-text-default" accesskey="6">A</button>
						<button type="button" class="button-text-plus" accesskey="7">A+</button>
					</li>
					<li role="menuitem">
						<span><?php _e('Contrast', 'tainacan-interface') ?></span>
						<button type="button" class="button-high-contrast" accesskey="8"><?php _e('High contrast', 'tainacan-interface') ?></button>
					</li>
				</ul>
			</nav>
		</div>

		<!-- ERROR WARNING IN CASE JS IS NOT ACTIVATED OR IS NOT WORKING -->
		<noscript>
			<style>
				noscript {
					margin: 0;
					padding: 12px 15px;
					font-size: 18px;
					color: #000;
					text-align: center;
					display: block;
					background-color: #FFC107;
				}
			</style>

			<span><?php _e('Your browser does not support JavaScript or it is disabled.', 'tainacan-interface') ?></span>
		</noscript>
	<?php } ?>
		
	<nav class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0 navbar--border-bottom">
		<div class="container-fluid max-large px-0 margin-one-column" id="topNavbar">
			<?php echo tainacan_get_logo(); ?>

			<div class="navbar-box">
				<?php if ( has_nav_menu( 'navMenubelowHeader' ) ) : ?>
					<nav id="tainacan-site-menu" class="navbar navbar-expand-md navbar-light px-0 menu-belowheader" role="navigation">
						<div class="container-fluid max-large px-0 margin-one-column">
							<!-- Brand and toggle get grouped for better mobile display -->	
							<!-- <a class="navbar-brand d-md-none" href="#"></a> -->
							<button class="navbar-toggler text-heavy-metal border-0 px-2 pt-2 collapsed" type="button" data-toggle="collapse" data-target="#menubelowHeader" aria-controls="menubelowHeader" aria-expanded="false" aria-label="Toggle navigation">
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

				<div class="btn-group">
					<form  id="tainacan-site-search" class="form-horizontal my-2 my-md-0 tainacan-search-form d-none d-md-block" [formGroup]="searchForm" role="form" (keyup.enter)="onSubmit()" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="input-group">
							<input type="text" name="s" placeholder="<?php esc_attr_e( 'Search', 'tainacan-interface' ); ?>" class="form-control" formControlName="searchText" size="50">
							<span class="text-midnight-blue input-group-btn tainacan-icon tainacan-icon-search form-control-feedback"></span>
						</div>
					</form>
					<div class="dropdown tainacan-form-dropdown d-md-none">
						<a class="btn btn-link text-midnight-blue px-1 dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="tainacan-icon tainacan-icon-search"></i><i class="tainacan-icon tainacan-icon-close"></i></a>

						<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
							<?php get_search_form(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</nav>

	<a href="javascript:" id="return-to-top"><i class="tainacan-icon tainacan-icon-arrowup"></i></a>

	<?php tainacan_interface_the_breadcrumb(); ?>