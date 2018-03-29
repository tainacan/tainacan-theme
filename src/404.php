<?php 
//Header with menu
get_header('menu');?>

<div class="page-header page-404 header-filter clear-filter" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/404.png')">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mx-auto">
                    <div class="brand mb-5">
                        <h1>404</h1>
                        <h3><?php _e('The page you are looking for does not exist!', 'tainacan-theme');?></h3>
                        <h3>̄\_(ツ)_/ ̄</h3>
                    </div>
                </div>
                <div class="col-md-12 mx-auto text-center">
                    <button class="btn btn-primary bg-jungle-green w-25 p-0"><?php _e('Go to first page', 'tainacan-theme');?></button>
                </div>
            </div>
        </div>
    </div>

<?php
//Footer
get_footer();