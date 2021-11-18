<?php

/**
 * Functions that register the options for the customizer
 * related to the footer info
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_footer_info') ) {

	function tainacan_interface_customize_register_footer_info( $wp_customize ) {
		/**
		 * Add others infos in Site identity on footer
		 */
		$wp_customize->add_section('tainacan_interface_footer_info', array(
			'title'  	 => __( 'Footer settings', 'tainacan-interface' ),
			'priority'   => 170,
		));

		$wp_customize->add_setting( 'tainacan_blogaddress', array(
			'type'       => 'theme_mod',
			'capability' => 'manage_options',
			'sanitize_callback'  => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'tainacan_blogaddress', array(
			'type'       => 'theme_mod',
			'label'      => __( 'Address', 'tainacan-interface' ),
			'section'    => 'tainacan_interface_footer_info',
		) );

		$wp_customize->add_setting( 'tainacan_blogphone', array(
			'type'       => 'theme_mod',
			'capability' => 'manage_options',
			'sanitize_callback'  => 'tainacan_sanitize_phone',
		) );
		$wp_customize->add_control( 'tainacan_blogphone', array(
			'type'       => 'theme_mod',
			'label'      => __( 'Phone Number', 'tainacan-interface' ),
			'section'    => 'tainacan_interface_footer_info',
		) );

		$wp_customize->add_setting( 'tainacan_blogemail', array(
			'type'       => 'theme_mod',
			'capability' => 'manage_options',
			'sanitize_callback'  => 'tainacan_sanitize_email',
		) );
		$wp_customize->add_control( 'tainacan_blogemail', array(
			'type'       => 'theme_mod',
			'label'      => __( 'E-mail', 'tainacan-interface' ),
			'section'    => 'tainacan_interface_footer_info',
		) );

		$wp_customize->add_setting( 'tainacan_footer_color', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 'dark',
			'transport'  => 'refresh',
			'sanitize_callback' => 'tainacan_sanitize_footer_color_options',
			) );
		$wp_customize->add_control( 'tainacan_footer_color', array(
			'type' 	   	  => 'select',
			'priority' 	  => 6, // Within the section.
			'section'  	  => 'tainacan_interface_footer_info',
			'label'    	  => __( 'Footer color scheme', 'tainacan-interface' ),
			'choices'	  => tainacan_get_footer_color_options()
			) );

		/**
		 * Checkbox to display or no the Logo.
		 */
		$wp_customize->add_setting( 'tainacan_display_footer_logo', array(
			'type'       => 'theme_mod',
			'default'        => true,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
		) );

		$wp_customize->add_control( 'tainacan_display_footer_logo', array(
			'type' => 'checkbox',
			'settings' => 'tainacan_display_footer_logo',
			'section' => 'tainacan_interface_footer_info',
			'label' => __( 'Display logo', 'tainacan-interface' ),
			'description' => __( 'Toggle to display or not a logo on the bottom left corner.', 'tainacan-interface' ),
		) );

		/**
		 * Footer Logo customizer
		 */
		$wp_customize->add_setting( 'tainacan_footer_logo', array(
			'type'       => 'theme_mod',
			'capability' => 'manage_options',
			'sanitize_callback' => 'tainacan_sanitize_upload',
		) );
		$wp_customize->add_control(
			new WP_Customize_Image_Control($wp_customize, 'tainacan_footer_logo',
				array(
				'label'      => __( 'Upload a logo to the footer', 'tainacan-interface' ),
				'section'    => 'tainacan_interface_footer_info',
				'settings'   => 'tainacan_footer_logo',
				)
			)
		);

		$wp_customize->add_setting( 'tainacan_footer_logo_link', array(
			'type'       => 'theme_mod',
			'capability' => 'manage_options',
			'default' 	 => 'https://tainacan.org',
			'sanitize_callback'  => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'tainacan_footer_logo_link', array(
			'type'       => 'theme_mod',
			'label'      => __( 'Logo link', 'tainacan-interface' ),
			'section'    => 'tainacan_interface_footer_info',
		) );

		/**
		 * Checkbox to display or no the Proudly Powered by WordPress and Tainacan.
		 */
		$wp_customize->add_setting( 'tainacan_display_powered', array(
			'type'       => 'theme_mod',
			'default'        => true,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
		) );

		$wp_customize->add_control( 'tainacan_display_powered', array(
			'type' => 'checkbox',
			'settings' => 'tainacan_display_powered',
			'section' => 'tainacan_interface_footer_info',
			'label' => __( 'Display "Proudly Powered by..."', 'tainacan-interface' ),
			'description' => __( 'This checkbox shows the "Proudly Powered by WordPress and Tainacan" sentence.', 'tainacan-interface' ),
		) );


	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_footer_info', 11 );
}


/**
 * Email sanitization callback.
 *
 * - Sanitization: email
 * - Control: text
 *
 * @param string               $email   Email address to sanitize.
 * @param WP_Customize_Setting $setting Setting instance.
 * @return string The sanitized email if not null; otherwise, the setting default.
 */
function tainacan_sanitize_email( $email, $setting ) {
	// Strips out all characters that are not allowable in an email address.
	$email = sanitize_email( $email );

	// If $email is a valid email, return it; otherwise, return the default.
	return ( ! is_null( $email ) ? $email : $setting->default );
}

/**
 * Phone number sanitization callback.
 *
 * - Sanitization: number
 * - Control: text
 *
 * @param string               $phone   Phone to sanitize.
 * @return string The sanitized phone if the number is <= 18; otherwise, the setting default.
 */
function tainacan_sanitize_phone( $phone ) {
	// Replace out all characters that are not allowable in an phone number.
	$phone = preg_replace( '/[^0-9 \\-\\(\\)\\+\\/]/', '', $phone );

	// If $phone is a valid number and <= 18, return it; otherwise, ''.
	return ( strlen( $phone ) <= 18 ? $phone : '' );
}

/**
 * Tainacan Upload sanitization callback.
 *
 * - Sanitization: upload
 * - Control: file
 *
 */
function tainacan_sanitize_upload( $input ) {

	/* default output */
	$output = '';

	/* check file type */
	$filetype = wp_check_filetype( $input );
	$mime_type = $filetype['type'];

	/* only mime type "image" allowed */
	if ( strpos( $mime_type, 'image' ) !== false ) {
		$output = $input;
	}

	return $output;
}


if ( ! function_exists( 'tainacan_get_footer_color_options' ) ) :
	/**
	 * Retrieves an array of options for footer color on Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_footer_color_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $color_options - a string describing the color style option
	 */
	function tainacan_get_footer_color_options() {
		$color_options = array(
			'dark' 		=> __('Dark', 'tainacan-interface'),
			'light' 	=> __('Light', 'tainacan-interface'),
			'colored' 	=> __('Colored', 'tainacan-interface'),
		);
		return $color_options;
	}
endif; // tainacan_get_footer_color_options

if ( ! function_exists( 'tainacan_sanitize_footer_color_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme footer color style
	 *
	 * Create your own tainacan_sanitize_footer_color_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $option - a string describing the color style for the footer
	 * @return string the selected option.
	 */
	function tainacan_sanitize_footer_color_options( $option ) {
		$color_options = tainacan_get_footer_color_options();

		if ( ! array_key_exists( $option, $color_options ) ) {
			return 'dark';
		}

		return $option;
	}
endif; // tainacan_sanitize_footer_color_options
