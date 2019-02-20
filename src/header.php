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
		
	<nav class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0">
		<div class="container-fluid max-large px-0 margin-one-column" id="topNavbar">
			<?php echo tainacan_get_logo(); ?>
			<div class="btn-group ml-auto"> 
					<form class="form-horizontal my-2 my-md-0 tainacan-search-form d-none d-md-block" [formGroup]="searchForm" role="form" (keyup.enter)="onSubmit()" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="input-group">
							<input type="text" name="s" placeholder="<?php esc_attr_e( 'Search', 'tainacan-interface' ); ?>" class="form-control" formControlName="searchText" size="50">
							<span class="text-midnight-blue input-group-btn mdi mdi-magnify form-control-feedback"></span>
						</div>
					</form>
					<div class="dropdown tainacan-form-dropdown d-md-none">
						<a class="btn btn-link text-midnight-blue px-1 dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-magnify"></i></a>

						<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
							<?php get_search_form(); ?>
						</div>
					</div>
			</div>
		</div>
	</nav>

	<a href="javascript:" id="return-to-top"><i class="mdi mdi-menu-up"></i></a>
