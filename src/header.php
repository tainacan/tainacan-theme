<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
      <?php wp_head(); ?>
</head>
<body>
        
    <nav class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-1">
        <div class="d-md-flex ml-md-auto mr-md-4">
            <a class="navbar-brand tainacan-logo" href="#">
                <img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg' ?>" class="logo" style="width: 150px">
            </a>
            <div class="btn-group pt-1 ml-auto">
                <button type="button" class="btn btn-link text-heavy-metal pr-1"><i class="material-icons">folder_open</i></button>
                <button type="button" class="btn btn-link text-jelly-bean px-1 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Separated link</a>
                </div>
            </div>
            <div class="btn-group pt-1">
                <button type="button" class="btn btn-link text-heavy-metal pr-0 pl-1"><i class="material-icons">person_outline</i></button>
                <button type="button" class="btn btn-link text-jelly-bean px-1 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#">Trocar Senha</a>
                    <a class="dropdown-item" href="#">Sair</a>
                </div>
            </div>
            <button class="navbar-toggler text-heavy-metal border-0 px-0 pt-2" type="button" data-toggle="collapse" data-target="#menuTp" aria-controls="menuTp" aria-expanded="false" aria-label="Toggle navigation">
                <i class="material-icons">more_vert</i>
            </button>
            <div class="collapse navbar-collapse mt-sm-1" id="menuTp">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-heavy-metal" href="#"><i class="material-icons">notifications_none</i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-heavy-metal" href="#"><i class="material-icons">event</i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-heavy-metal" href="#"><i class="material-icons">help_outline</i></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <form class="tainacan-form-search d-sm-none">
        <div class="d-flex justify-content-between">
            <div class="w-100 pt-1 mt-2 pl-1">
                <input class="form-control h-75 tainacan-input-search py-0 border-0" type="search" placeholder="Pesquisar">
            </div>
            <div class="pt-1 mt-2 ml-1">
                <button type="reset" class="btn h-75 pt-0 pr-0 border-0 bg-white tainacan-button-search" onclick=""><i class="material-icons">close</i></button>
            </div>
            <div class="pt-1 mt-2 mx-1">
                <button type="submit" class="btn h-75 pt-0 border-0 text-heavy-metal bg-white tainacan-button-search" onclick=""><i class="material-icons">search</i></button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-column header">
        <img src="<?php echo get_template_directory_uri() ?>/assets/images/capa.png" class="img-fluid header-img">
        <i class="material-icons float-right p-1 m-2 mt-3 text-white rounded-circle position-absolute header-icon">settings</i>
    </div>