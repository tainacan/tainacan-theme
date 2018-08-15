<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">
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
                    <form class="form-horizontal my-2 my-md-0 tainacan-search-form d-none d-md-block" [formGroup]="searchForm" role="form" (keyup.enter)="onSubmit()">
                        <div class="input-group">
                            <input type="text" name="s" placeholder="<?php _e('Search', 'tainacan-theme'); ?>" class="form-control" formControlName="searchText" size="50">
                            <span class="text-midnight-blue input-group-btn mdi mdi-magnify form-control-feedback"></span>
                        </div>
                    </form>
                    <div class="dropdown tainacan-form-dropdown d-md-none">
                        <a class="btn btn-link text-midnight-blue px-1 dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-magnify"></i></a>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
                                <div class="input-group border">
                                    <input class="form-control py-2 border-0" type="search" name="s" placeholder="<?php _e('Search', 'tainacan-theme'); ?>" id="tainacan-search">
                                </div>
                            </form>
                        </div>
                    </div>
                <!-- <button type="button" class="btn btn-link text-heavy-metal dropdown-toggle dropdown-toggle-split px-1 d-flex align-items-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-account-outline"></i><div class="d-none d-md-inline-flex px-1">User</div><span class="sr-only text-jelly-bean">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu tainacan-dropdown-menu">
                    <a class="dropdown-item" href="#">Perfil</a>
                    <a class="dropdown-item" href="#">Coleção</a>
                    <a class="dropdown-item" href="#">Sair</a>
                </div>
                <button type="button" class="btn btn-link text-heavy-metal px-1"><i class="mdi mdi-bell-outline"></i></button>
                <button type="button" class="btn btn-link text-heavy-metal pl-1 pr-0"><i class="mdi mdi-help-circle-outline"></i></button> -->
            </div>
        </div>
    </nav>