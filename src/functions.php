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
        /**
         * Display in gutenberg plugin the full width for image
         */
        add_theme_support( 'align-wide' );

        add_theme_support( 'html5', array( 'comment-list' , 'comment-form') );
        add_theme_support( 'post-thumbnails' );
        define('FS_METHOD', 'direct');
        /**
         * Custom header to change the banner image
         */
		$header_args = array(
            //'default-text-color' => '000',
            'width'              => 1280,
            'height'             => 280,
            'header-text'		 => false,
			'flex-width'         => false,
			'flex-height'        => true,
		);
        add_theme_support( 'custom-header', $header_args );
        
		/* register_default_headers(
            array(
                'default-image' => array(
                    'url'           => '%s/assets/images/capa.png',
                    'thumbnail_url' => '%s/assets/images/capa.png',
                    'description'   => __( 'Default Image', 'tainacan-theme' ),
                ),
            )
        ); */
        require_once('functions/enqueues.php');
        
        /**
         * Custom logo to change the logo image
         */
		$logo_args = array(
			'height'      => 25,
			'width'       => 400,
			'flex-height' => false,
			'flex-width'  => true,
		);
		add_theme_support( 'custom-logo', $logo_args );
        
        if (function_exists('tainacan_register_view_mode')) {
            tainacan_register_view_mode('grid', [
                'label' => 'Thumbnail',
                'description' => 'A thumbnail grid view, showing only title and thumbnail',
                'icon' => '<span class="icon"><i class="mdi mdi-apps mdi-24px"></i></span>',
                'dynamic_metadata' => false,
            ]);
        }
        
        add_image_size( 'tainacan-theme-list-post', 300, 200, true );
    }

}
add_action( 'after_setup_theme', 'tainacan_setup' );

/*
* Register Widgets SideBar
*/
function tainacan_widgets_sidebar_init() {
    register_sidebar( array(
        'name'          => __( 'Tainacan Sidebar Right', 'tainacan-theme' ),
        'id'            => 'sidebar-right',
        'before_widget' => '<aside id="%1$s" class="pb-4 px-4 widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title font-weight-bold">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'tainacan_widgets_sidebar_init' );

/*
* Register Widgets footer
*/
function tainacan_widgets_footer_init() {
    register_sidebar( array(
        'name'          => __( 'Tainacan Sidebar Footer', 'tainacan-theme' ),
        'id'            => 'footer-1',
        'before_widget' => '<li class="border-left-0 border-right-0 tainacan-side"><input type="checkbox" checked><i></i>',
        'after_widget'  => '</li>',
        'before_title'  => '<h6 class="text-white font-weight-bold mb-lg-4">',
        'after_title'   => ' <i class="material-icons mt-0 symbol"></i></h6>',
    ) );
}
add_action( 'widgets_init', 'tainacan_widgets_footer_init' );

/**
 * get Logo function
 *
 * return custom logo or the default logo
 */
function tainacan_get_logo() {
	if (has_custom_logo()) {
		return get_custom_logo();
	} else {
		$html = '<a class="navbar-brand tainacan-logo" href="' . get_bloginfo( 'url' ) . '">';
		$html .= '<img src="' . get_template_directory_uri() . '/assets/images/logo.svg" class="logo" style="max-height: 23px">';
		$html .= '</a>';
		return $html;
	}
}

function tainacan_get_form_search(){
    if(wp_is_mobile()){
        $input = 'border-0 rounded-0';
        $reset = 'border-0 rounded-0';
        $submit = 'border-0 rounded-0';
    }else{
        $input = 'border-right-0 rounded-0';
        $reset = 'border-left-0 border-right-0 rounded-0';
        $submit = 'border border-left-0 rounded-0';
    }

    $form = '';
    $form .= '<form class="tainacan-form-search d-none align-items-center" action="'.home_url( '/' ).'">';
        $form .= '<div id="test-form-search" class="d-flex justify-content-between">';
            $form .= '<div id="input-search" class="w-100 pl-1">';
                $form .= '<input class="form-control tainacan-input-search py-0 pr-0 '.$input.'" type="search" name="s" placeholder="'.__('Search').'" style="height:31px">';
            $form .= '</div>';
            if(wp_is_mobile()){
                $form .= '<div id="btn-reset" class="d-none">';
                    $form .= '<button type="reset" class="btn border bg-white tainacan-button-search '.$reset.'"><i class="mdi mdi-close"></i></button>';
                $form .= '</div>';
            }
            $form .= '<div id="btn-submit" class="mr-1 d-none d-lg-block">';
                $form .= '<button type="submit" id="btn-submit-search" class="btn text-heavy-metal bg-white pl-0 pr-1 tainacan-button-search py-0 '.$submit.'"><i class="mdi mdi-magnify"></i></button>';
            $form .= '</div>';
        $form .= '</div>';
    $form .= '</form>';
    return $form;
}

/**
 * Change logo class function
 *
 * @param [type] $html
 * @return void
 */
function tainacan_change_logo_class( $html ) {

    $html = str_replace( 'custom-logo-link', 'navbar-brand tainacan-logo', $html );
	$html = str_replace( 'custom-logo', 'logo', $html );

    return $html;
}
add_filter( 'get_custom_logo', 'tainacan_change_logo_class' );

/**
 * Class navwalker
 */
require_once get_template_directory() . '/vendor/class-wp-bootstrap-navwalker.php';

/**
 * Register the menu for use after the banner
 */
register_nav_menus( array(
	'navMenubelowHeader' => __( 'Nav Menu Below Header', 'tainacan-theme' ),
) );

function tainacan_hex2rgb( $color ) {
	$color = trim( $color, '#' );

	if ( strlen( $color ) === 3 ) {
		$r = hexdec( substr( $color, 0, 1 ).substr( $color, 0, 1 ) );
		$g = hexdec( substr( $color, 1, 1 ).substr( $color, 1, 1 ) );
		$b = hexdec( substr( $color, 2, 1 ).substr( $color, 2, 1 ) );
	} else if ( strlen( $color ) === 6 ) {
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
	} else {
		return array();
	}

	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}

add_filter( 'query_vars', function ( $public_query_vars ) {

    $public_query_vars[] = "tainacan_collections_viewmode";
    return $public_query_vars;

} );

function tainacan_active($selected, $current = true, $echo = true) {

    $return = $selected == $current ? 'active' : '';

    if ($echo)
        echo $return;
    
    return $return;

}

add_filter('get_the_archive_title', function() {
    if (is_post_type_archive('tainacan-collection')) {
        return __('Collections', 'tainacan-theme');
    }
});

add_action('pre_get_posts', function($query) {

    if ($query->is_main_query() && $query->is_post_type_archive('tainacan-collection')) {
        $query->set('posts_per_page', 12);
    }

});

require get_template_directory() . '/functions/customizer.php';
require get_template_directory() . '/functions/pagination.php';
require get_template_directory() . '/functions/single-functions.php';