<?php
/**
 * Functions that register the options for the customizer
 * related to the header general settings
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_header_general' ) ) {
	/**
	 * Registers the Customizer options related to the header general settings.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_header_general( $wp_customize ) {
		// Use block Template Parts for Header.
		$wp_customize->add_setting(
			'tainacan_use_block_template_parts_on_header',
			array(
				'type'              => 'theme_mod',
				'default'           => false,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			'tainacan_use_block_template_parts_on_header',
			array(
				'type'        => 'checkbox',
				'settings'    => 'tainacan_use_block_template_parts_on_header',
				'priority'    => 90, // Last of the section: it replaces the others.
				'section'     => 'tainacan_interface_opt_site_header',
				'label'       => __( 'Use block template parts to configure header', 'tainacan-interface' ),
				'description' => __( 'Build your own header using the block editor in the menu Appearance -> Template Parts -> Header. If enabled, the other options of this section do not apply.', 'tainacan-interface' ),
			)
		);

		/**
		 * Allows setting max heigth of site logo ---------------------------------------------------------
		 */
		$wp_customize->add_setting(
			'tainacan_header_logo_max_height',
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => 120,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'tainacan_interface_sanitize_number',
			)
		);
		$wp_customize->add_control(
			'tainacan_header_logo_max_height',
			array(
				'type'            => 'number',
				'priority'        => 5,
				'section'         => 'tainacan_interface_opt_site_header',
				'label'           => __( 'Site header logo max height (px)', 'tainacan-interface' ),
				'input_attrs'     => array(
					'min'  => 42,
					'max'  => 220,
					'step' => 2,
				),
				'active_callback' => 'tainacan_interface_is_classic_header',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'tainacan_header_logo_max_height',
			array(
				'selector'         => '.tainacan-logo img.logo',
				'render_callback'  => '__return_false',
				'fallback_refresh' => true,
			)
		);

		/**
		 * Allows setting max width of site logo ---------------------------------------------------------
		 */
		$wp_customize->add_setting(
			'tainacan_header_logo_max_width',
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => 225,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'tainacan_interface_sanitize_number',
			)
		);
		$wp_customize->add_control(
			'tainacan_header_logo_max_width',
			array(
				'type'            => 'number',
				'priority'        => 6,
				'section'         => 'tainacan_interface_opt_site_header',
				'label'           => __( 'Site header logo max width (px)', 'tainacan-interface' ),
				'input_attrs'     => array(
					'min'  => 42,
					'max'  => 680,
					'step' => 2,
				),
				'active_callback' => 'tainacan_interface_is_classic_header',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'tainacan_header_logo_max_width',
			array(
				'selector'         => '.tainacan-logo img.logo',
				'render_callback'  => '__return_false',
				'fallback_refresh' => true,
			)
		);
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_header_general', 11 );
}

if ( ! function_exists( 'tainacan_interface_is_classic_header' ) ) :
	/**
	 * Whether the classic (non block-template) header options apply.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_classic_header( $control = null ) {
		unset( $control );
		return ! get_theme_mod( 'tainacan_use_block_template_parts_on_header', false );
	}
endif;

/**
 * Prints the maximum size of the logo in the site header.
 */
function tainacan_header_settings_style_output() {
	$height = absint( get_theme_mod( 'tainacan_header_logo_max_height', 120 ) );
	$width  = absint( get_theme_mod( 'tainacan_header_logo_max_width', 225 ) );
	if ( ! $height || ! $width ) {
		return;
	}

	$css = '.tainacan-logo .logo { max-height: ' . $height . 'px !important; max-width: ' . $width . 'px !important; }
		@media only screen and (max-width: 768px) { .tainacan-logo .logo { max-width: 100% !important; } }';

	tainacan_interface_print_style( 'tainacan-style-header-custom', $css );
}
add_action( 'wp_head', 'tainacan_header_settings_style_output' );
