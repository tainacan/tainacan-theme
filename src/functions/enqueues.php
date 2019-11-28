<?php
/**
 * Enqueues Theme
 */

if ( ! function_exists( 'tainacan_enqueues' ) ) {
	/**
	 * Inclui os scripts javascript e os styles necessários ao front-end do thema
	 */
	function tainacan_enqueues() {
		/**
		 * Bootstrap 4.1.3
		 */
			//Style
			wp_register_style( 'bootstrap4CSS', get_template_directory_uri() . '/assets/vendor/bootstrap/scss/bootstrap.min.css' );
			wp_enqueue_style( 'bootstrap4CSS' );
			//Javascript
			wp_register_script( 'bootstrap4JS', get_template_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.min.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'bootstrap4JS' );

		/**
		 * Slick Slider Carousel
		 */
			//Styles
			wp_register_style( 'SlickCss', get_template_directory_uri() . '/assets/vendor/slick/css/slick.min.css', '', '1.6.1', '' );
			wp_register_style( 'SlickThemeCss', get_template_directory_uri() . '/assets/vendor/slick/css/slick-theme.min.css', '', '1.6.1', '' );
			wp_enqueue_style( 'SlickCss' );
			wp_enqueue_style( 'SlickThemeCss' );
			//Javascript
			wp_register_script( 'SlickJS', get_template_directory_uri() . '/assets/vendor/slick/js/slick.min.js', array( 'jquery' ), '1.6.1', true );
			wp_enqueue_script( 'SlickJS' );

		/**
		 * Ekko Lightbox
		 */
			wp_register_style( 'EkkoLightboxCss', get_template_directory_uri() . '/assets/css/ekko-lightbox.css');
			wp_enqueue_style( 'EkkoLightboxCss' );
			wp_register_script( 'EkkoLightboxJs', get_template_directory_uri() . '/assets/js/ekko-lightbox.min.js', array('jquery'), null, true);
			wp_enqueue_script( 'EkkoLightboxJs' );

		/**
		 * Google
		 */
			// Google fonts Roboto
			wp_enqueue_style( 'RobotoFonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i' );

		/**
		 * Tainacan Theme
		 */
			wp_enqueue_script( 'tainacan_tainacanTruncate', get_template_directory_uri() . '/assets/js/tainacan-interface-truncate.js', array( 'jquery' ), '1.0', false );
			wp_localize_script( 'tainacan_tainacanTruncate', 'tainacan_trucanteVars', array(
				'moreText' => __( 'Show more', 'tainacan-interface' ),
				'lessText' => __( 'Show less', 'tainacan-interface' ),
			));

			wp_register_style( 'tainacan_tainacanStyle', get_stylesheet_uri(), array( 'bootstrap4CSS' ) );
			wp_enqueue_style( 'tainacan_tainacanStyle' );
			wp_register_script( 'tainacan_tainacanJS', get_template_directory_uri() . '/assets/js/js.js', '', '1.0', true );
			wp_enqueue_script( 'tainacan_tainacanJS' );

			// Tainacan Icons
			wp_register_style( 'TainacanIconsFont', get_template_directory_uri() . '/assets/fonts/tainacan-icons-font/css/tainacanicons.min.css', '', '1.0.2', '' );
			wp_enqueue_style( 'TainacanIconsFont' );

		/**
		 * Comments
		 */
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
} // End if().
add_action( 'wp_enqueue_scripts', 'tainacan_enqueues' );
