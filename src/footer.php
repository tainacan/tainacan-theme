<?php if(!is_404()) : ?>
    <footer class="container-fluid p-4 p-sm-5 mt-5 bg-haiti" style="padding-bottom: 0 !important;">
        <?php if ( is_active_sidebar( 'footer-1' ) ) { ?>
            <div class="row">
                <div class="col-12 col-lg">
                    <ul class="p-4 d-lg-flex justify-content-md-center mb-md-0">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
        <hr class="bg-scooter"/>
        <div class="row p-4">
            <div class="col text-white font-weight-normal">
                <p>
                    <?php 
                        echo bloginfo('title');
                    if(!wp_is_mobile()) : echo '<br>';?>
                <?php
                    else :
                        echo '</p><p>';
                    endif;
                    echo get_option('blogaddress', ''); 
                ?>
                </p>
                <p>
                    <?php 
                        _e('E-mail: ');
                        echo bloginfo('admin_email'); 
                        if(wp_is_mobile()) :
                            echo '<br>';
                        else :
                            echo ' - ';
                        endif;
                        _e('Telephone: ');
                        echo get_option('blogphone', ''); 
                    ?>
                </p>
            </div>
            <div class="col-auto pr-0 pr-md-3 d-none d-sm-block align-self-md-center">
                <img src="<?php echo get_template_directory_uri() ?>/assets/images/logo-footer.svg" alt="">
            </div>
        </div>
    </footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>