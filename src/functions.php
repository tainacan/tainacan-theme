<?php

/** Theme version */
const TAINACAN_INTERFACE_VERSION = '2.0';

/**
 * Setup Theme
 */
if ( ! function_exists( 'tainacan_setup' ) ) {

	/**
	 * Execulta após o tema ser inicializado.
	 * Isso é usado para a configuração básica do tema, registro dos recursos do tema e init hooks.
	 * Observe que esta função está conectada ao gancho after_setup_theme, que é executado antes do gancho de init.
	 */
	function tainacan_setup() {
		
		load_theme_textdomain( 'tainacan-interface', get_template_directory() . '/languages' );
		
		/**
		 * Display in gutenberg plugin the full width for image
		 */
		add_theme_support( 'align-wide' );

		add_theme_support( 'html5', array( 'comment-list', 'comment-form' ) );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		/**
		 * Custom header to change the banner image
		 */
		$header_args = array(
			'width'              => 2000,
			'height'             => 625,
			'header-text'		 => false,
			'flex-width'         => true,
			'flex-height'        => true,
		);
		add_theme_support( 'custom-header', $header_args );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		require_once get_template_directory() . '/functions/enqueues.php' ;

		/**
		 * Custom logo to change the logo image
		 */
		$logo_args = array(
			'height'      => 30,
			'width'       => 175,
			'flex-height' => true,
			'flex-width'  => true,
		);
		add_theme_support( 'custom-logo', $logo_args );

		if ( function_exists( 'tainacan_register_view_mode' ) ) {
			tainacan_register_view_mode('grid', array(
				'label' => __( 'Thumbnail', 'tainacan-interface' ),
				'description' => __( 'A thumbnail grid view, showing only title and thumbnail', 'tainacan-interface' ),
				'icon' => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewminiature tainacan-icon-1-25em"></i></span>',
				'dynamic_metadata' => false,
				'template' => get_template_directory() . '/tainacan/view-mode-grid.php',
				'skeleton_template' => '<div class="tainacan-grid-container">
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
								<div class="skeleton tainacan-grid-item"></div>
							</div>'
			));
		}

		add_image_size( 'tainacan-interface-list-post', 300, 200, true );
		add_image_size( 'tainacan-interface-item-attachments', 125, 125, true );
		
		
		/**
		 * Gutenberg support
		 */
	    add_theme_support( 'editor-color-palette', array(
	        array(
	            'name' => __( 'Default', 'tainacan-interface' ),
	            'slug' => 'default',
	            'color' => '#298596',
	        ),
	        array(
	            'name' => __( 'Carmine', 'tainacan-interface' ),
	            'slug' => 'carmine',
	            'color' => '#8c442c',
	        ),
	        array(
	            'name' => __( 'Cherry', 'tainacan-interface' ),
	            'slug' => 'cherry',
	            'color' => '#A12B42',
	        ),
	        array(
	            'name' => __( 'Mustard', 'tainacan-interface' ),
	            'slug' => 'mustard',
	            'color' => '#754E24',
			),
			array(
	            'name' => __( 'Mint Green', 'tainacan-interface' ),
	            'slug' => 'mintgreen',
	            'color' => '#255F56',
			),
			array(
	            'name' => __( 'Dark Turquoise', 'tainacan-interface' ),
	            'slug' => 'darkturquoise',
	            'color' => '#205E6F',
			),
			array(
	            'name' => __( 'Turquoise', 'tainacan-interface' ),
	            'slug' => 'turquoise',
	            'color' => '#185F6D',
			),
			array(
	            'name' => __( 'Blue Heavenly', 'tainacan-interface' ),
	            'slug' => 'blueheavenly',
	            'color' => '#1D5C86',
			),
			array(
	            'name' => __( 'Purple', 'tainacan-interface' ),
	            'slug' => 'purple',
	            'color' => '#4751a3',
			),
			array(
	            'name' => __( 'Violet', 'tainacan-interface' ),
	            'slug' => 'violet',
	            'color' => '#955ba5',
	        ),
		) );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-style' );
		add_editor_style( 'editor-style.css' );
	
		
	}
} // End if().
add_action( 'after_setup_theme', 'tainacan_setup' );

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Tainacan Theme
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1400;
}

/**
 * Adds extra classes dp body tag. has-not-finished-loading is removed
 * from tag after jQuery.document(ready). It is used to style page while
 * not all DOM and JS is finished.
 *
 * @since Tainacan Theme
 */
add_filter( 'body_class', 'custom_class' );
function custom_class( $classes ) {

	$classes[] = 'loading-content';

	return $classes;
}

/*
* Register Widgets SideBar
*/
function tainacan_widgets_sidebar_init() {
	register_sidebar( array(
		'name'          => __( 'Tainacan Sidebar Right', 'tainacan-interface' ),
		'id'            => 'tainacan-sidebar-right',
		'before_widget' => '<aside id="%1$s" class="pb-4 pl-4 widget %2$s">',
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
		'name'          => __( 'Tainacan Sidebar Footer', 'tainacan-interface' ),
		'id'            => 'tainacan-sidebar-footer',
		'before_widget' => '<li class="border-left-0 border-right-0 tainacan-side"><input type="checkbox" checked><i></i>',
		'after_widget'  => '</li>',
		'before_title'  => '<h6 class="text-white font-weight-bold mb-lg-4">',
		'after_title'   => ' <i class="tainacan-icon mt-0"></i></h6>',
	) );
}
add_action( 'widgets_init', 'tainacan_widgets_footer_init' );

/**
 * get Logo function
 *
 * return custom logo or the default logo
 */
function tainacan_get_logo() {
	if ( has_custom_logo() ) {
		return get_custom_logo();
	} else {
		$html = '<a class="navbar-brand tainacan-logo" href="' . esc_url( home_url() ) . '">';
		$html .= '<h1>' . get_bloginfo( 'name' ) . '</h1>';
		$html .= '</a>';
		return $html;
	}
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
require_once get_template_directory() . '/lib/class-wp-bootstrap-navwalker.php';

/**
 * Register the menu for use after the banner
 */
register_nav_menus( array(
	'navMenubelowHeader' => __( 'Navigation menu below header', 'tainacan-interface' ),
) );

function tainacan_hex2rgb( $color ) {
	$color = trim( $color, '#' );

	if ( strlen( $color ) === 3 ) {
		$r = hexdec( substr( $color, 0, 1 ) . substr( $color, 0, 1 ) );
		$g = hexdec( substr( $color, 1, 1 ) . substr( $color, 1, 1 ) );
		$b = hexdec( substr( $color, 2, 1 ) . substr( $color, 2, 1 ) );
	} elseif ( strlen( $color ) === 6 ) {
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
	} else {
		return array();
	}

	return array(
		'red' => $r,
		'green' => $g,
		'blue' => $b,
	);
}

function tainacan_collections_viewmode( $public_query_vars ) {
	$public_query_vars[] = 'tainacan_collections_viewmode';
	return $public_query_vars;
}
add_filter( 'query_vars', 'tainacan_collections_viewmode' );

function tainacan_active( $selected, $current = true, $echo = true ) {

	$return = $selected == $current ? 'active' : '';

	if ( $echo ) {
		echo $return;
	}

	return $return;

}

function tainacan_theme_collection_title( $title ) {
	if ( is_post_type_archive( 'tainacan-collection' ) ) {
		return __( 'Collections', 'tainacan-interface' );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'tainacan_theme_collection_title' );

function tainacan_theme_collection_query( $query ) {
	if ( $query->is_main_query() && $query->is_post_type_archive( 'tainacan-collection' ) ) {
		$query->set( 'posts_per_page', 12 );
	}
}
add_action( 'pre_get_posts', 'tainacan_theme_collection_query' );

/**
 * Render customizer colors to Gutenberg.
 */
function tainacan_customizer_gutenberg_colors() {

	// Retrieve the link color from the Customizer.
	$link_color = get_theme_mod( 'tainacan_link_color', 'default' ); 

	// Build styles.
	$css  = '';
	$css .= '.has-accent-color { color: ' . esc_attr( $link_color ) . ' !important; }';
	$css .= '.has-accent-background-color { background-color: ' . esc_attr( $link_color ) . '; }';
	return wp_strip_all_tags( $css );
}

/**
* Enqueue editor styles for Gutenberg
*/
function tainacan_editor_styles() {

	// Adds Tainacan editor style for Gutenberg.
    wp_enqueue_style( 'tainacan-editor-style', get_template_directory_uri() . '/editor-style.css', [], TAINACAN_INTERFACE_VERSION );
	
	// Adds Robot fonts to Gutenberg.
	wp_enqueue_style( 'RobotoFonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i' );
	
	// Adds customizer colors to Gutenberg.
	//wp_add_inline_style( 'tainacan-customizer-editor-style', tainacan_customizer_gutenberg_colors() );
}
add_action( 'enqueue_block_editor_assets', 'tainacan_editor_styles' );

require get_template_directory() . '/functions/customizer.php';
require get_template_directory() . '/functions/single-functions.php';
require get_template_directory() . '/functions/class-tainacanthemecollectioncolor.php';
require get_template_directory() . '/functions/breadcrumb.php';
