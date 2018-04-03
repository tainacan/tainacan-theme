<?php 
//Header with banner and menu
get_header('banner'); ?>
    <?php
        $bread = "<ol class='breadcrumb pb-0 mb-1' style='background: transparent;'>";
        $bread .= "<li class='breadcrumb-item'><a href='#'>Home</a></li>";
        $bread .= "<li class='breadcrumb-item'><a href='#'>Site</a></li>";
        $bread .= "<li class='breadcrumb-item active' aria-current='page'>Blog</li>";
        $bread .= "</ol>";
    ?>
    <nav class="navbar navbar-expand-md navbar-light bg-white px-1" role="navigation">
        <div class="container-fluid">
            <?php if(wp_is_mobile()) echo $bread; ?>
            <a class="navbar-brand" href="#"></a>
            <!-- Brand and toggle get grouped for better mobile display -->
            <button class="navbar-toggler text-heavy-metal border-0 px-2 pt-2" type="button" data-toggle="collapse" data-target="#menubelowHeader" aria-controls="menubelowHeader" aria-expanded="false" aria-label="Toggle navigation">
                <i class="material-icons">more_vert</i>
            </button>
                <?php
                    wp_nav_menu( array(
                        'theme_location'	=> 'navMenubelowHeader',
                        'depth'				=> 1, // 1 = with dropdowns, 0 = no dropdowns.
                        'container'			=> 'div',
                        'container_class'	=> 'collapse navbar-collapse',
                        'container_id'		=> 'menubelowHeader',
                        'menu_class'		=> 'navbar-nav mr-auto ml-md-1',
                        'fallback_cb'		=> 'WP_Bootstrap_Navwalker::fallback',
                        'walker'			=> new WP_Bootstrap_Navwalker()
                    ) );
                ?>
        </div>
    </nav>
    <nav aria-label="breadcrumb" class="d-none d-md-flex">
        <?php echo $bread; ?>
    </nav>
    <div class="container-fluid mt-5">