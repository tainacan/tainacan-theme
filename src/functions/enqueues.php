<?php
/**
 * Enqueues Theme
 */

if ( ! function_exists( 'tainacan_enqueues' ) ) {
	/**
	 * Inclui os scripts javascript e os styles necessários ao front-end do tema
	 */
	function tainacan_enqueues() {
		/**
		 * Bootstrap 4.1.3
		 */
			//Style
			wp_register_style( 'bootstrap4CSS', get_template_directory_uri() . '/assets/vendor/bootstrap/scss/bootstrap.min.css', [], TAINACAN_INTERFACE_VERSION );
			wp_enqueue_style( 'bootstrap4CSS' );
			//Javascript
			wp_register_script( 'bootstrap4JS', get_template_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.min.js', array( 'jquery' ), TAINACAN_INTERFACE_VERSION, true );
			wp_enqueue_script( 'bootstrap4JS' );

		/**
		 * Google
		 */
			// Google fonts Roboto
			wp_enqueue_style( 'RobotoFonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i' );

		/**
		 * Tainacan Interface theme
		 */
		wp_enqueue_script( 'tainacan_tainacanTruncate', get_template_directory_uri() . '/assets/js/tainacan-interface-truncate.js', array( 'jquery' ), TAINACAN_INTERFACE_VERSION, false );
		wp_localize_script( 'tainacan_tainacanTruncate', 'tainacan_trucanteVars', array(
			'moreText' => __( 'Show more', 'tainacan-interface' ),
			'lessText' => __( 'Show less', 'tainacan-interface' ),
		));

		// Use minified version if available and not in debug mode
		// For child themes, we still use the parent theme's minified CSS
		$style_uri = get_stylesheet_uri();
		$minified_uri = get_template_directory_uri() . '/style.min.css';
		$minified_path = get_template_directory() . '/style.min.css';
		
		// Use minified version if it exists and we're not in debug mode
		if ( ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) && file_exists( $minified_path ) ) {
			$style_uri = $minified_uri;
		}

		wp_register_style( 'tainacan_tainacanStyle', $style_uri, array( 'bootstrap4CSS' ), TAINACAN_INTERFACE_VERSION );
		wp_enqueue_style( 'tainacan_tainacanStyle' );
			wp_register_script( 'tainacan_tainacanJS', get_template_directory_uri() . '/assets/js/js.js', '', TAINACAN_INTERFACE_VERSION, true );
			wp_enqueue_script( 'tainacan_tainacanJS' );

			if ( defined ('TAINACAN_VERSION' ) ) {
				$settings = array(
					'theme_items_list_url' 		=> esc_url_raw( get_site_url( null, \Tainacan\Theme_Helper::get_instance()->get_items_list_slug()) ),
					'theme_collection_list_url' => get_post_type_archive_link( 'tainacan-collection' ),
					'theme_taxonomy_list_url' => get_post_type_archive_link( 'tainacan-taxonomy' ),
					'site_url' => get_site_url()
				);
				wp_register_script( 'tainacan_searchBarRedirect', get_template_directory_uri() . '/assets/js/search-bar-redirect.js', '', TAINACAN_INTERFACE_VERSION, false );
				wp_enqueue_script( 'tainacan_searchBarRedirect' );
				wp_localize_script( 'tainacan_searchBarRedirect', 'tainacan_search_info', $settings );
			}

			wp_enqueue_script( 'tainacan_copyLink', get_template_directory_uri() . '/assets/js/copy-link.js', [] , TAINACAN_INTERFACE_VERSION, false );
			wp_localize_script( 'tainacan_copyLink', 'tainacan_copyLinkVars', array(
				'linkCopied' => __( 'Copied! Link copied to clipboard.', 'tainacan-interface' )
			));

			// Tainacan Icons
			wp_enqueue_style( 'TainacanIconsFont', get_template_directory_uri() . '/assets/fonts/tainacan-icons-font/css/tainacanicons.min.css', [], TAINACAN_INTERFACE_VERSION );

		/**
		 * New assets added in 2.9.0
		 */
		// Modals & Tooltips JS
		wp_enqueue_script( 'tainacan-modals', get_template_directory_uri() . '/assets/js/modals.js', [], TAINACAN_INTERFACE_VERSION, true );

		// Dark mode toggle
		if ( get_theme_mod( 'tainacan_enable_dark_mode', false ) ) {
			wp_enqueue_script( 'tainacan-dark-mode', get_template_directory_uri() . '/assets/js/dark-mode.js', [], TAINACAN_INTERFACE_VERSION, false );
		}

		// Custom font family from typography settings
		$font_family = get_theme_mod( 'tainacan_typography_font_family', 'Roboto' );
		if ( $font_family && $font_family !== 'system-ui' && $font_family !== 'Roboto' ) {
			$font_slug = str_replace( ' ', '+', $font_family );
			wp_enqueue_style( 'tainacan-custom-font', 'https://fonts.googleapis.com/css2?family=' . $font_slug . ':wght@300;400;500;600;700&display=swap', [], null );
		}

		/**
		 * Comments
		 */
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

	} // tainacan_enqueues
}
add_action( 'wp_enqueue_scripts', 'tainacan_enqueues' );

/**
 * Enqueue admin-specific assets for modals in backend
 *
 * @since 2.9.0
 */
function tainacan_admin_enqueues() {
	wp_enqueue_style( 'tainacan-admin-modals', get_template_directory_uri() . '/assets/css/admin-modals.css', [], TAINACAN_INTERFACE_VERSION );

	// Color picker for collection advanced settings
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'tainacan_admin_enqueues' );
