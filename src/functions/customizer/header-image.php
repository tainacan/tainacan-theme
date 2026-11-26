<?php
/**
 * Functions that register the options for the customizer
 * related to the header image.
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_header_image' ) ) {
	/**
	 * Registers the Customizer options related to the header image.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_header_image( $wp_customize ) {
		/**
		 * Adds option to hide Website Title on the Header Image cover, or the whole banner.
		 */
		$wp_customize->add_setting(
			'tainacan_hide_site_title_on_header_banner',
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => false,
				'transport'         => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			'tainacan_hide_site_title_on_header_banner',
			array(
				'type'        => 'checkbox',
				'priority'    => 21, // Within the section.
				'section'     => 'tainacan_interface_opt_hero',
				'label'       => __( 'Hide the header banner site title', 'tainacan-interface' ),
				'description' => __( 'Toggle to hide the site title row that appears over the header banner', 'tainacan-interface' ),
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'tainacan_hide_site_title_on_header_banner',
			array(
				'selector'         => '.page-header .title-header h1',
				'render_callback'  => '__return_false',
				'fallback_refresh' => true,
			)
		);

		/**
		 * Adds option to display featured image on site header
		 */
		$wp_customize->add_setting(
			'tainacan_featured_image_on_header_banner',
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => false,
				'transport'         => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			'tainacan_featured_image_on_header_banner',
			array(
				'type'        => 'checkbox',
				'priority'    => 23, // Within the section.
				'section'     => 'tainacan_interface_opt_hero',
				'label'       => __( 'Display post or page featured image on the header banner', 'tainacan-interface' ),
				'description' => __( 'Use the current post or page featured image on the header banner, if available, instead of the default image. This also hides the featured image in the post content.', 'tainacan-interface' ),
			)
		);
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_header_image', 11 );
}
