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
				'type'            => 'checkbox',
				'priority'        => 21, // Within the section.
				'section'         => 'header_image',
				'label'           => __( 'Hide the header banner site title', 'tainacan-interface' ),
				'description'     => __( 'Toggle to hide the site title row that appears over the header banner', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_banner_visible',
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
		$wp_customize->add_setting(
			'tainacan_hide_header_banner',
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => false,
				'transport'         => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			'tainacan_hide_header_banner',
			array(
				'type'        => 'checkbox',
				'priority'    => 20, // Within the section.
				'section'     => 'header_image',
				'label'       => __( 'Hide the header banner completely', 'tainacan-interface' ),
				'description' => __( 'Toggle to hide the header banner from all pages of the site', 'tainacan-interface' ),
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
				'type'            => 'checkbox',
				'priority'        => 23, // Within the section.
				'section'         => 'header_image',
				'label'           => __( 'Display post or page featured image on the header banner', 'tainacan-interface' ),
				'description'     => __( 'Use the current post or page featured image on the header banner, if available, instead of the default image. This also hides the featured image in the post content.', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_banner_visible',
			)
		);
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_header_image', 11 );
}

if ( ! function_exists( 'tainacan_interface_is_header_banner_visible' ) ) :
	/**
	 * Whether the site header banner is not hidden.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_banner_visible( $control = null ) {
		unset( $control );
		return ! get_theme_mod( 'tainacan_hide_header_banner', false );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_header_banner_title_visible' ) ) :
	/**
	 * Whether the header banner and its site title row are visible.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_banner_title_visible( $control = null ) {
		if ( ! tainacan_interface_is_header_banner_visible( $control ) ) {
			return false;
		}

		return ! get_theme_mod( 'tainacan_hide_site_title_on_header_banner', false );
	}
endif;
