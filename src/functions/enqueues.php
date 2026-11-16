<?php
/**
 * Front-end scripts and styles of the theme.
 *
 * No file comes from another server: the Roboto of the original theme came
 * from Google Fonts, which sends every visitor's IP address to Google. The
 * theme ships Roboto (latin subset, Apache 2.0) in assets/fonts/roboto, declared
 * as fontFace in theme.json.
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_enqueues' ) ) {
	/**
	 * Includes the scripts and styles needed by the theme front end.
	 */
	function tainacan_enqueues() {
		$uri = get_template_directory_uri();

		/**
		 * Layout grid, utility classes and base components (assets/css/foundation.css),
		 * and the dropdowns and collapses (assets/js/nav.js). A child theme of 2.x may
		 * still depend on the old handles of Bootstrap: they stay as aliases, with no file.
		 */
		wp_register_style( 'tainacan-interface-foundation', $uri . '/assets/css/foundation.css', array(), TAINACAN_INTERFACE_VERSION );
		wp_register_style( 'bootstrap4CSS', false, array( 'tainacan-interface-foundation' ), TAINACAN_INTERFACE_VERSION );
		wp_enqueue_style( 'bootstrap4CSS' );
		wp_register_script( 'tainacan-interface-nav', $uri . '/assets/js/nav.js', array(), TAINACAN_INTERFACE_VERSION, true );
		wp_register_script( 'bootstrap4JS', false, array( 'tainacan-interface-nav' ), TAINACAN_INTERFACE_VERSION, true );
		wp_enqueue_script( 'bootstrap4JS' );

		/**
		 * Tainacan Interface theme
		 */
		wp_enqueue_script( 'tainacan_tainacanTruncate', $uri . '/assets/js/tainacan-interface-truncate.js', array( 'jquery' ), TAINACAN_INTERFACE_VERSION, false );
		wp_localize_script(
			'tainacan_tainacanTruncate',
			'tainacan_trucanteVars',
			array(
				'moreText' => __( 'Show more', 'tainacan-interface' ),
				'lessText' => __( 'Show less', 'tainacan-interface' ),
			)
		);

		// The parent's stylesheet, minified unless debugging. A child theme's own
		// style.css comes after it, in inc/child-theme.php.
		$style_uri     = $uri . '/style.css';
		$minified_path = get_template_directory() . '/style.min.css';

		if ( ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) && file_exists( $minified_path ) ) {
			$style_uri = $uri . '/style.min.css';
		}

		wp_register_style( 'tainacan_tainacanStyle', $style_uri, array( 'bootstrap4CSS' ), TAINACAN_INTERFACE_VERSION );
		wp_enqueue_style( 'tainacan_tainacanStyle' );
		wp_register_script( 'tainacan_tainacanJS', $uri . '/assets/js/js.js', array( 'jquery' ), TAINACAN_INTERFACE_VERSION, true );
		wp_enqueue_script( 'tainacan_tainacanJS' );

		// Item page: the header borders change (was a script printed after </html>).
		if ( tainacan_interface_is_item_page() ) {
			wp_add_inline_script( 'tainacan_tainacanJS', 'jQuery(function(){jQuery("#topNavbar").addClass("b-bottom-top");jQuery("nav.menu-belowheader").removeClass("border-bottom");jQuery("nav.menu-belowheader .max-large").addClass("b-bottom-bellow");});' );
		}

		if ( defined( 'TAINACAN_VERSION' ) && class_exists( '\Tainacan\Theme_Helper' ) ) {
			$settings = array(
				'theme_items_list_url'      => esc_url_raw( home_url( \Tainacan\Theme_Helper::get_instance()->get_items_list_slug() ) ),
				'theme_collection_list_url' => get_post_type_archive_link( 'tainacan-collection' ),
				'theme_taxonomy_list_url'   => get_post_type_archive_link( 'tainacan-taxonomy' ),
				'site_url'                  => home_url( '/' ),
			);
			wp_register_script( 'tainacan_searchBarRedirect', $uri . '/assets/js/search-bar-redirect.js', array(), TAINACAN_INTERFACE_VERSION, false );
			wp_enqueue_script( 'tainacan_searchBarRedirect' );
			wp_localize_script( 'tainacan_searchBarRedirect', 'tainacan_search_info', $settings );
		}

		wp_enqueue_script( 'tainacan_copyLink', $uri . '/assets/js/copy-link.js', array(), TAINACAN_INTERFACE_VERSION, false );
		wp_localize_script(
			'tainacan_copyLink',
			'tainacan_copyLinkVars',
			array(
				'linkCopied' => __( 'Copied! Link copied to clipboard.', 'tainacan-interface' ),
			)
		);

		// Tainacan Icons.
		wp_enqueue_style( 'TainacanIconsFont', $uri . '/assets/fonts/tainacan-icons-font/css/tainacanicons.min.css', array(), TAINACAN_INTERFACE_VERSION );

		/**
		 * Comments
		 */
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'tainacan_enqueues' );
