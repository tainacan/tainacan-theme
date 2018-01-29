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

<nav class="navbar navbar-expand-lg navbar-light bg-white menu-shadow">
    <a class="navbar-brand pt-0" href="#">
        <img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg' ?>" class="logo" style="width: 150px">
    </a>
    <div class="btn-group pt-1 ml-auto">
        <button type="button" class="btn btn-link text-heavy-metal pr-0"><i class="material-icons">folder_open</i></button>
        <button type="button" class="btn btn-link text-jelly-bean pl-0 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        <button type="button" class="btn btn-link text-heavy-metal pr-0 pl-0"><i class="material-icons">person_outline</i></button>
        <button type="button" class="btn btn-link text-jelly-bean pl-0 dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
    <button class="navbar-toggler text-heavy-metal border-0 px-0" type="button" data-toggle="collapse" data-target="#menuTp" aria-controls="menuTp" aria-expanded="false" aria-label="Toggle navigation">
        <i class="material-icons">more_vert</i>
    </button>

    <div class="collapse navbar-collapse" id="menuTp">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link text-havy-metal" href="#"><i class="material-icons">notifications_none</i></a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-havy-metal" href="#"><i class="material-icons">event</i></a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-havy-metal" href="#"><i class="material-icons">help_outline</i></a>
            </li>
        </ul>
    </div>
</nav>

<div class="d-flex flex-column mt-2 d-lg-none">
    <form class="form-inline tainacan-form-search">
        <div class="input-group">
            <input class="form-control ml-1 tainacan-input-search invisible" type="search">
            <div class="input-group-prepend">
                <!-- <div class="input-group-text border-0 p-0 text-heavy-metal bg-white mr-2">
                    <i class="material-icons">search</i>
                </div> -->
                <button type="submit" class="btn btn-primary border-0 text-heavy-metal bg-white p-0 mx-2 pt-1" onclick=""><i class="material-icons">search</i></button>
            </div>
        </div>
    </form>
</div>

<div class="d-flex flex-column header">
    <img src="<?php echo get_template_directory_uri() ?>/assets/images/capa.png" class="img-fluid header-img">
    <i class="material-icons float-right p-1 m-3 text-white bg-dark rounded-circle position-absolute" style="right: 0">settings</i>
</div>