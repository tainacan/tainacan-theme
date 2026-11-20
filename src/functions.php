<?php
/**
 * Tainacan Interface: theme setup.
 *
 * Versions 3 and 4 keep the function names, theme mods, actions and CSS classes
 * of 2.x, so child themes and plugins written for it keep working; what is new
 * lives in inc/.
 *
 * @package tainacan-interface
 */

/** Theme version; also versions the assets. */
const TAINACAN_INTERFACE_VERSION = '2.15.2';

/* Disables Tainacan Theme Helper the_content filter, which is used to build a custom item and taxonomy (terms list) template. */
if ( ! defined( 'TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER' ) ) {
	define( 'TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER', true );
}

if ( ! defined( 'TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER' ) ) {
	define( 'TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER', true );
}

/**
 * Setup Theme
 */
if ( ! function_exists( 'tainacan_setup' ) ) {

	/**
	 * Runs after the theme is initialized: theme supports, menus, view modes.
	 */
	function tainacan_setup() {

		/**
		 * Actions that must be done after the init hook
		 */
		add_action(
			'init',
			function () {

				load_theme_textdomain( 'tainacan-interface', get_template_directory() . '/languages' );

				/**
				 * Register the menu for use after the banner
				 */
				register_nav_menus(
					array(
						'navMenubelowHeader' => __( 'Navigation menu below header', 'tainacan-interface' ),
					)
				);
			}
		);

		add_theme_support( 'html5', array( 'navigation-widgets', 'comment-list', 'comment-form' ) );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );

		/**
		 * Removes support for Gutenberg widgets editor, by now...
		 */
		remove_theme_support( 'widgets-block-editor' );

		/**
		 * Custom header to change the banner image
		 */
		add_theme_support(
			'custom-header',
			array(
				'width'       => 2000,
				'height'      => 625,
				'header-text' => false,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		add_theme_support( 'title-tag' );

		require_once get_template_directory() . '/functions/enqueues.php';

		/**
		 * Custom logo to change the logo image
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 30,
				'width'       => 175,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		if ( function_exists( 'tainacan_register_view_mode' ) ) {
			tainacan_register_view_mode(
				'grid',
				array(
					'label'            => __( 'Thumbnail', 'tainacan-interface' ),
					'description'      => __( 'A thumbnail grid view, showing only title and thumbnail', 'tainacan-interface' ),
					'icon'             => '<span class="icon"><i class="tainacan-icon tainacan-icon-viewminiature tainacan-icon-1-25em"></i></span>',
					'dynamic_metadata' => false,
					'template'         => get_template_directory() . '/tainacan/view-mode-grid.php',
					// No skeleton_template: with one, Tainacan 1.4 shows neither its loading
					// indicator nor the skeleton (the skeleton sits in a container it hides).
				)
			);
		}

		/**
		 * Gutenberg support
		 */
		$default_color = tainacan_interface_link_color();

		add_theme_support(
			'editor-color-palette',
			array(
				array(
					'name'  => __( 'Default', 'tainacan-interface' ),
					'slug'  => 'default',
					'color' => $default_color,
				),
				array(
					'name'  => __( 'Carmine', 'tainacan-interface' ),
					'slug'  => 'carmine',
					'color' => '#8c442c',
				),
				array(
					'name'  => __( 'Cherry', 'tainacan-interface' ),
					'slug'  => 'cherry',
					'color' => '#A12B42',
				),
				array(
					'name'  => __( 'Mustard', 'tainacan-interface' ),
					'slug'  => 'mustard',
					'color' => '#754E24',
				),
				array(
					'name'  => __( 'Mint Green', 'tainacan-interface' ),
					'slug'  => 'mintgreen',
					'color' => '#255F56',
				),
				array(
					'name'  => __( 'Dark Turquoise', 'tainacan-interface' ),
					'slug'  => 'darkturquoise',
					'color' => '#205E6F',
				),
				array(
					'name'  => __( 'Turquoise', 'tainacan-interface' ),
					'slug'  => 'turquoise',
					'color' => '#185F6D',
				),
				array(
					'name'  => __( 'Blue Heavenly', 'tainacan-interface' ),
					'slug'  => 'blueheavenly',
					'color' => '#1D5C86',
				),
				array(
					'name'  => __( 'Purple', 'tainacan-interface' ),
					'slug'  => 'purple',
					'color' => '#4751a3',
				),
				array(
					'name'  => __( 'Violet', 'tainacan-interface' ),
					'slug'  => 'violet',
					'color' => '#955ba5',
				),
				array(
					'name'  => __( 'White', 'tainacan-interface' ),
					'slug'  => 'white',
					'color' => '#ffffff',
				),
				array(
					'name'  => __( 'Light gray', 'tainacan-interface' ),
					'slug'  => 'lightgray',
					'color' => '#f2f2f2',
				),
				array(
					'name'  => __( 'Dark gray', 'tainacan-interface' ),
					'slug'  => 'darkgray',
					'color' => '#505253',
				),
			)
		);
		add_theme_support( 'align-wide' );
		add_theme_support( 'custom-line-height' );
		add_theme_support( 'custom-units' );
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name' => esc_attr__( 'Small', 'tainacan-interface' ),
					'size' => 14,
					'slug' => 'small',
				),
				array(
					'name' => esc_attr__( 'Regular', 'tainacan-interface' ),
					'size' => 16,
					'slug' => 'regular',
				),
				array(
					'name' => esc_attr__( 'Large', 'tainacan-interface' ),
					'size' => 18,
					'slug' => 'large',
				),
				array(
					'name' => esc_attr__( 'Huge', 'tainacan-interface' ),
					'size' => 24,
					'slug' => 'huge',
				),
			)
		);
		add_theme_support( 'appearance-tools' );
		add_theme_support( 'editor-style' );
		add_editor_style( 'editor-style.css' );
		add_theme_support( 'block-template-parts' );
	}
} // tainacan_setup check
add_action( 'after_setup_theme', 'tainacan_setup' );

/**
 * The link color of the editor and the blocks: the accent color of the design.
 *
 * @return string
 */
function tainacan_interface_link_color() {
	return (string) tainacan_interface_opt( 'accent_color' );
}

/**
 * Passes the custom color to a css variable used on the theme-side editor style.
 */
function tainacan_customize_editor_css() {
	$color = tainacan_interface_link_color();
	printf(
		'<style>.editor-styles-wrapper{--tainacan-color-default:%1$s;--tainacan-block-primary:%1$s;}</style>',
		esc_html( $color )
	);
}
add_action( 'admin_head', 'tainacan_customize_editor_css' );

/**
 * Includes the items of the collections in the site search, which the
 * Tainacan plugin leaves out (exclude_from_search). ?onlyposts=1 and
 * ?onlypages=1 narrow the search.
 *
 * @param WP_Query $query The main WordPress query.
 */
function tainacan_include_items_in_search_results( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() || is_post_type_archive() ) {
		return;
	}

	if ( tainacan_interface_query_flag( 'onlypages' ) ) {
		$query->set( 'post_type', 'page' );
		return;
	}

	if ( tainacan_interface_query_flag( 'onlyposts' ) ) {
		$query->set( 'post_type', 'post' );
		return;
	}

	if ( ! defined( 'TAINACAN_VERSION' ) || ! class_exists( '\Tainacan\Repositories\Repository' ) ) {
		return;
	}

	// A search already narrowed to a type (the Search block set to one, a plugin's
	// form sending post_type) stays narrowed; the theme only widens the open search.
	if ( ! empty( array_filter( (array) $query->get( 'post_type' ) ) ) ) {
		return;
	}

	$collections_post_types = \Tainacan\Repositories\Repository::get_collections_db_identifiers();

	$query->set( 'post_type', array_values( array_unique( array_merge( array( 'post', 'page', 'tainacan-collection', 'tainacan-taxonomy' ), $collections_post_types ) ) ) );
	// Titles only: with the items of every collection in, a search over title,
	// excerpt and content reads the whole wp_posts (longtext) twice per query in
	// a large archive. The full-text search of the items is the Tainacan list.
	$query->set( 'search_columns', array( 'post_title' ) );
}
add_action( 'pre_get_posts', 'tainacan_include_items_in_search_results' );

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1400;
}

/**
 * Alert user if Tainacan plugin is not installed.
 */
function tainacan_interface_admin_notice() {
	global $pagenow;

	if ( defined( 'TAINACAN_VERSION' ) || 'themes.php' !== $pagenow || tainacan_interface_state( 'no_plugin_seen' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%1$s</p><p><a class="button" href="%2$s">%3$s</a></p></div>',
		wp_kses(
			sprintf(
				/* translators: %s: URL of the Tainacan plugin page. */
				__( 'Warning: <a href="%s">Tainacan plugin</a> is not activated! While you may use this theme without it, we made it to take full advantage of the plugin features.', 'tainacan-interface' ),
				'https://wordpress.org/plugins/tainacan/'
			),
			array( 'a' => array( 'href' => array() ) )
		),
		esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_no_plugin' ), 'tainacan_interface_dismiss_no_plugin' ) ),
		esc_html__( 'Dismiss', 'tainacan-interface' )
	);
}
add_action( 'admin_notices', 'tainacan_interface_admin_notice' );

/**
 * Dismisses the notice about the plugin.
 */
function tainacan_interface_dismiss_no_plugin() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_dismiss_no_plugin' );
	tainacan_interface_state_set( 'no_plugin_seen', 1 );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_tainacan_interface_dismiss_no_plugin', 'tainacan_interface_dismiss_no_plugin' );

/**
 * Adds extra classes to the body tag. loading-content is removed once the
 * page scripts ran; it is used to style the page while not all DOM and JS is
 * finished.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tainacan_interface_body_class( $classes ) {
	$classes[] = 'loading-content';
	return $classes;
}
add_filter( 'body_class', 'tainacan_interface_body_class' );

/**
 * Register Widgets SideBar
 */
function tainacan_widgets_sidebar_init() {
	register_sidebar(
		array(
			'name'          => __( 'Tainacan Sidebar Right', 'tainacan-interface' ),
			'id'            => 'tainacan-sidebar-right',
			'before_widget' => '<aside id="%1$s" class="pb-4 pl-4 widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title font-weight-bold">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'tainacan_widgets_sidebar_init' );

/**
 * Register Widgets footer
 */
function tainacan_widgets_footer_init() {
	register_sidebar(
		array(
			'name'          => __( 'Tainacan Sidebar Footer', 'tainacan-interface' ),
			'id'            => 'tainacan-sidebar-footer',
			'before_widget' => '<li class="mb-lg-4 border-left-0 border-right-0 border-bottom-0 tainacan-side"><input type="checkbox" checked><i></i>',
			'after_widget'  => '</li>',
			'before_title'  => '<h6 class="text-white font-weight-bold mb-lg-4">',
			'after_title'   => ' <i class="tainacan-icon mt-0"></i></h6>',
		)
	);
}
add_action( 'widgets_init', 'tainacan_widgets_footer_init' );

/**
 * Tainacan's logo HTML: the custom logo, or the site name as a link.
 *
 * @return string
 */
function tainacan_get_logo() {
	if ( has_custom_logo() ) {
		return get_custom_logo();
	}
	return '<a class="navbar-brand tainacan-logo" href="' . esc_url( home_url( '/' ) ) . '"><h1>' . esc_html( get_bloginfo( 'name' ) ) . '</h1></a>';
}

/**
 * Change logo class function
 *
 * @param string $html Custom logo HTML.
 * @return string
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
require_once get_template_directory() . '/inc/class-tainacan-interface-nav-walker.php';

/**
 * Hex color to its red, green and blue components.
 *
 * @param string $color Hex color, with or without #.
 * @return array<string, int> Empty when the value is not a color.
 */
function tainacan_hex2rgb( $color ) {
	$color = trim( (string) $color, '#' );

	if ( ! preg_match( '/^([0-9a-f]{3}|[0-9a-f]{6})$/i', $color ) ) {
		return array();
	}
	if ( 3 === strlen( $color ) ) {
		$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
	}

	return array(
		'red'   => hexdec( substr( $color, 0, 2 ) ),
		'green' => hexdec( substr( $color, 2, 2 ) ),
		'blue'  => hexdec( substr( $color, 4, 2 ) ),
	);
}

/**
 * Enqueue editor styles for Gutenberg
 */
function tainacan_editor_styles() {
	wp_enqueue_style( 'tainacan-editor-style', get_template_directory_uri() . '/editor-style.css', array(), TAINACAN_INTERFACE_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'tainacan_editor_styles' );

require get_template_directory() . '/inc/upgrade.php';

require get_template_directory() . '/inc/request.php';
require get_template_directory() . '/inc/context.php';
require get_template_directory() . '/inc/share.php';
require get_template_directory() . '/inc/social.php';
require get_template_directory() . '/inc/page-title.php';
require get_template_directory() . '/inc/settings.php';
require get_template_directory() . '/inc/design.php';
require get_template_directory() . '/inc/customizer-design.php';
require get_template_directory() . '/inc/customizer-shortcuts.php';
require get_template_directory() . '/functions/customizer.php';
require get_template_directory() . '/functions/patterns.php';
require get_template_directory() . '/functions/single-functions.php';
require get_template_directory() . '/functions/archive-functions.php';
require get_template_directory() . '/functions/item-gallery.php';
require get_template_directory() . '/inc/collection-form.php';
require get_template_directory() . '/inc/customizer-drawings.php';
require get_template_directory() . '/inc/customizer-pictures.php';
require get_template_directory() . '/functions/class-tainacan-interface-textarea-readmore.php';
require get_template_directory() . '/functions/breadcrumb.php';
require get_template_directory() . '/inc/drawer-metadata-option.php';
require get_template_directory() . '/inc/item-drawer.php';
require get_template_directory() . '/inc/item-viewer.php';
require get_template_directory() . '/inc/item-highlight.php';
require get_template_directory() . '/inc/item-aside.php';
require get_template_directory() . '/inc/terms-explorer.php';
require get_template_directory() . '/inc/item-citation.php';
require get_template_directory() . '/inc/collection-pages.php';
require get_template_directory() . '/inc/nav-menus.php';
require get_template_directory() . '/inc/compat.php';
require get_template_directory() . '/inc/child-theme.php';
