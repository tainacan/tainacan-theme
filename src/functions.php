<?php


if(!function_exists('tainacan_setup')) {

    /**
     * Execulta após o tema ser inicializado. 
     * Isso é usado para a configuração básica do tema, registro dos recursos do tema e init hooks. 
     * Observe que esta função está conectada ao gancho after_setup_theme, que é executado antes do gancho de init.
     */
    function tainacan_setup() {
        /**
        * Não exibe o menu do administrador na pagina do site. Mesmo quando estiver logado!
        **/
        show_admin_bar( false );

        /**
         * Desabilita o FTP na instalação de Plugins
         */
        define('FS_METHOD', 'direct');
    }

}
add_action( 'after_setup_theme', 'tainacan_setup' );

/**
 * Inclui os scripts javascript necessários ao front do thema
 */
function tainacanEnqueueScripts(){
    //Adicionado o jquery ao footer das páginas.
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true );
    wp_enqueue_script( 'jquery' );

    //Dependencia do bootstrap 4
    wp_enqueue_script('popper', get_template_directory_uri() . '/assets/vendor/bootstrap/js/popper.min.js', '', '', true);
    //JS Bootstrap 4
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.min.js', array('jquery'), '4.0.0', true);
}
add_action('wp_enqueue_scripts', 'tainacanEnqueueScripts');

/**
 * Inclui os styles necessários ao front do thema
 */
function tainacanEnqueueStyles(){
    //Style Bootstrap 4
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap/css/bootstrap.min.css');

    //Style Tainacan
    wp_enqueue_style('style', get_stylesheet_uri(), array('bootstrap'));
}
add_action('wp_enqueue_scripts', 'tainacanEnqueueStyles');