<?php
/**
 * Setup Theme
 */

if(!function_exists('tainacan_setup')) {

    /**
     * Execulta após o tema ser inicializado. 
     * Isso é usado para a configuração básica do tema, registro dos recursos do tema e init hooks. 
     * Observe que esta função está conectada ao gancho after_setup_theme, que é executado antes do gancho de init.
     */
    function tainacan_setup() {

        $custom_header_support = array(// The default header text color.
            'default-text-color' => '212529',
            'wp-head-callback' => 'tainacan_header_style',
        );
        add_theme_support( 'custom-header', $custom_header_support );

        if ( ! function_exists( 'get_custom_header' ) ) {
            // This is all for compatibility with versions of WordPress prior to 3.4.
            define( 'HEADER_TEXTCOLOR', $custom_header_support['default-text-color'] );
        }
        
        add_theme_support( 'html5', array( 'comment-list' , 'comment-form') );
        add_theme_support( 'post-thumbnails' );
		
		$header_args = array(
			'default-image'      => get_template_directory_uri() . '/assets/images/capa.png',
			'default-text-color' => '000',
			'header-text'		 => true,
			'width'              => 1280,
			'height'             => 280,
			'flex-width'         => false,
			'flex-height'        => true,
		);
		add_theme_support( 'custom-header', $header_args );
		
		$logo_args = array(
			'height'      => 25,
			'width'       => 400,
			'flex-height' => false,
			'flex-width'  => true,
		);
		add_theme_support( 'custom-logo', $logo_args );
		
        /**
         * Desabilita o FTP na instalação de Plugins - Lembrar de retirar!!
         */
        define('FS_METHOD', 'direct');

    }

}
add_action( 'after_setup_theme', 'tainacan_setup' );

/*
* Register Widgets SideBar
*/
function tainacan_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Tainacan Sidebar', 'tainacan-theme' ),
        'id'            => 'sidebar-1',
        'before_widget' => '<aside id="%1$s" class="pb-4 px-4 widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title font-weight-bold">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'tainacan_widgets_init' );

function tainacan_get_logo() {
	if (has_custom_logo()) {
		return get_custom_logo();
	} else {
		$html = '<a class="navbar-brand tainacan-logo" href="' . get_bloginfo( 'url' ) . '">';
		$html .= '<img src="' . get_template_directory_uri() . '/assets/images/logo.svg" class="logo" style="width: 150px">';
		$html .= '</a>';
		return $html;
	}
}

function tainacan_change_logo_class( $html ) {

    $html = str_replace( 'custom-logo-link', 'navbar-brand tainacan-logo', $html );
	$html = str_replace( 'custom-logo', 'logo', $html );

    return $html;
}
add_filter( 'get_custom_logo', 'tainacan_change_logo_class' );

function custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

require get_template_directory() . '/functions/enqueues.php';
require get_template_directory() . '/functions/customize.php';
require get_template_directory() . '/functions/pagination.php';
require get_template_directory() . '/functions/single-functions.php';