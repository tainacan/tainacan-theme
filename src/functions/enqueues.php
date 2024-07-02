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

			wp_register_style( 'tainacan_tainacanStyle', get_stylesheet_uri(), array( 'bootstrap4CSS' ), TAINACAN_INTERFACE_VERSION );
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
			wp_register_style( 'TainacanIconsFont', get_template_directory_uri() . '/assets/fonts/tainacan-icons-font/css/tainacanicons.min.css', '', '1.0.3', '' );
			wp_enqueue_style( 'TainacanIconsFont' );

		/**
		 * Comments
		 */
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

	} // tainacan_enqueues
}
add_action( 'wp_enqueue_scripts', 'tainacan_enqueues' );
