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
			wp_register_style( 'bootstrap4CSS', get_template_directory_uri() . '/assets/vendor/bootstrap/css/bootstrap.min.css' );
			wp_enqueue_style( 'bootstrap4CSS' );
			//Popper
			wp_register_script( 'popper_bootstrap4', get_template_directory_uri() . '/assets/vendor/bootstrap/js/popper.min.js', '', '', true );
			wp_enqueue_script( 'popper_bootstrap4' );
			//Javascript
			wp_register_script( 'bootstrap4JS', get_template_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.min.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'bootstrap4JS' );

		/**
		 * Slick Slider Carousel
		 */
			//Styles
			wp_enqueue_style( 'RobotoFonts', 'https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i' );
			wp_register_style( 'SlickCss', get_template_directory_uri() . '/assets/vendor/slick/css/slick.min.css', '', '1.6.1', '' );
			wp_register_style( 'SlickThemeCss', get_template_directory_uri() . '/assets/vendor/slick/css/slick-theme.min.css', '', '1.6.1', '' );
			wp_enqueue_style( 'SlickCss' );
			wp_enqueue_style( 'SlickThemeCss' );
			//Javascript
			wp_register_script( 'SlickJS', get_template_directory_uri() . '/assets/vendor/slick/js/slick.min.js', array( 'jquery' ), '1.6.1', true );
			wp_enqueue_script( 'SlickJS' );

		/**
		 * Google
		 */

		 /* Material Icons */
			wp_register_style( 'MaterialIconsFonts', get_template_directory_uri() . '/assets/fonts/material-design-icons/css/materialdesignicons.min.css', '', '2.4.85', '' );
			wp_enqueue_style( 'MaterialIconsFonts' );

		/**
		 * DotJS
		 */
			wp_register_script( 'DotJS', get_template_directory_uri() . '/assets/js/dotdotdot.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'DotJS' );
		/**
		 * Tainacan Theme
		 */
			wp_register_style( 'tainacanStyle', get_stylesheet_uri(), array( 'bootstrap4CSS' ) );
			wp_enqueue_style( 'tainacanStyle' );
			wp_register_script( 'tainacanJS', get_template_directory_uri() . '/assets/js/js.js', '', '1.0', true );
			wp_enqueue_script( 'tainacanJS' );
		/**
		 * Comments
		 */
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
} // End if().
add_action( 'wp_enqueue_scripts', 'tainacan_enqueues' );
