<?php if(!is_404()) : ?>
    <footer class="container-fluid p-4 p-sm-5 mt-5 tainacan-footer" style="padding-bottom: 0 !important;">
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
        <div class="row p-4 tainacan-footer-info">
            <div class="col text-white font-weight-normal">
                <?php
                    if (get_bloginfo('title')) {
                        echo '<p class="tainacan-footer-info--blog">' . get_bloginfo('title');

                        if(!wp_is_mobile()):
                            echo '<br>';
                        else:
                            echo '</p><p>';
                        endif;

                        if ( get_option('blogaddress') ) {
                            echo get_option('blogaddress', ''); 
                        }

                        echo '</p>';
                    }
                ?>
                <?php 
                    if( get_option('blogemail') ) {
                        echo '<p class="tainacan-footer-info--blog">';

                        printf(__('E-mail: %s', 'tainacan-theme'), get_option('blogemail', ''));

                        if(get_option('blogphone')) {
                            if(wp_is_mobile()):
                                echo '<br>';
                            else:
                                echo ' - ';
                            endif;

                            printf(__('Telephone: %s', 'tainacan-theme'), get_option('blogphone', ''));
                        }

                        echo '</p>';
                    } elseif (get_option('blogphone')) {
                        echo '<p class="tainacan-footer-info--blog">';
                        printf(__('Telephone: %s', 'tainacan-theme'), get_option('blogphone', ''));
                        echo '</p>';
                    }
                ?>
            </div>
            <div class="col-auto pr-0 pr-md-3 d-none d-md-block align-self-md-top">
                    <img src="<?php if(get_theme_mod( 'footer_logo' )) { echo get_theme_mod( 'footer_logo' ); }else{ echo get_template_directory_uri() ?>/assets/images/logo-footer.svg<?php }?>" class="tainacan-footer-info--logo" alt="Tainacan - Logo preto e branco">
            </div>
            <div class="col-12 tainacan-powered">
                <span>
                    <?php 
                        if ( true == get_theme_mod( 'display_powered', false ) ) {
                            printf(__('Proudly powered by %1$s and %2$s', 'tainacan-theme'), '<a href="https://wordpress.org/">Wordpress</a>', '<a href="http://tainacan.org/">Tainacan</a>'); 
                        }
                    ?>
                </span>
            </div>
        </div>
    </footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>

</html>