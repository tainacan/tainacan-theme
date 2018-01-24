<?php


if(!function_exists('tainacan_setup')) {

    /**
     * Execulta apos o tema ser inicializado. 
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
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/vendor/bootstrap/js/bootstrap.min.js', array('jquery'), '4.0.0', true);
}
add_action('wp_enqueue_scripts', 'tainacanEnqueueScripts');

/**
 * Inclui os styles necessários ao front do thema
 */
function tainacanEnqueueStyles(){
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/vendor/bootstrap/scss/bootstrap.min.css');
    wp_enqueue_style('style', get_stylesheet_uri(), array('bootstrap'));
}
add_action('wp_enqueue_scripts', 'tainacanEnqueueStyles');