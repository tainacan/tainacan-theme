<?php

/**
 * Functions that register the options for the customizer
 * related to the header general settings
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_header_general') ) {

	function tainacan_interface_customize_register_header_general( $wp_customize ) {
	
		/**
		 * Adds section to control Header search settings
		 */
		$wp_customize->add_section('tainacan_header_general', array(
			'title'  	 => __( 'Header layout and elements', 'tainacan-interface' ),
			'priority'   => 60,
			'panel' 	 => 'tainacan_header_settings'
		));

		/**
		 * Adds options to align header elements
		 */
		$wp_customize->add_setting( 'tainacan_header_alignment_options', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 'default',
			'transport'  => 'refresh',
			'sanitize_callback' => 'tainacan_sanitize_header_alignment_options',
			) );
		$wp_customize->add_control( 'tainacan_header_alignment_options', array(
			'type' 	   	  => 'select',
			'section'  	  => 'tainacan_header_general',
			'label'    	  => __( 'Header elements alignment', 'tainacan-interface' ),
			'description' => __( 'Sets how the header elements, such as the logo and navigation menu are aligned.', 'tainacan-interface' ),
			'choices'	  => tainacan_get_header_alignment_options()
			) );

		// Fixed header
		$wp_customize->add_setting( 'tainacan_fixed_header', array(
			'type'       => 'theme_mod',
			'default'    => false,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'tainacan_fixed_header', array(
			'type' 		=> 'checkbox',
			'settings' 	=> 'tainacan_fixed_header',
			'section' 	=> 'tainacan_header_general',
			'label' => __( 'Fix header position after scroll', 'tainacan-interface' )
		) );
		
		/**
		 * Allows setting min heigth of site header ---------------------------------------------------------
		 */
		$wp_customize->add_setting( 'tainacan_header_min_height', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 50,
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'tainacan_header_min_height', array(
			'type' => 'number',
			'section' => 'tainacan_header_general',
			'label' => __( 'Site header minimum height (px)', 'tainacan-interface' ),
			'input_attrs' => array(
				'min' => 42,
				'max' => 320,
				'step' => 2
			),
		) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_header_min_height', array(
			'selector' => 'nav.navbar',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
		) );

		/**
		 * Allows setting max heigth of site logo ---------------------------------------------------------
		 */
		$wp_customize->add_setting( 'tainacan_header_logo_max_height', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 120,
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'tainacan_header_logo_max_height', array(
			'type' => 'number',
			'section' => 'tainacan_header_general',
			'label' => __( 'Site header logo max height (px)', 'tainacan-interface' ),
			'input_attrs' => array(
				'min' => 42,
				'max' => 220,
				'step' => 2
			),
		) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_header_logo_max_height', array(
			'selector' => '.tainacan-logo img.logo',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
		) );

		/**
		 * Allows setting max width of site logo ---------------------------------------------------------
		 */
		$wp_customize->add_setting( 'tainacan_header_logo_max_width', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 225,
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'tainacan_header_logo_max_width', array(
			'type' => 'number',
			'section' => 'tainacan_header_general',
			'label' => __( 'Site header logo max width (px)', 'tainacan-interface' ),
			'input_attrs' => array(
				'min' => 42,
				'max' => 680,
				'step' => 2
			),
		) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_header_logo_max_width', array(
			'selector' => '.tainacan-logo img.logo',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
		) );

	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_header_general', 11 );
}


if ( ! function_exists( 'tainacan_get_header_alignment_options' ) ) :
	/**
	 * Retrieves an array of options for  header alignment options for Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_header_alignment_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $option - a string with options for header alignments.
	 */
	function tainacan_get_header_alignment_options() {
		$header_alignment_options = array(
			'default' => __('One line, spaced', 'tainacan-interface'),
			'left' => __('Two lines, to the left', 'tainacan-interface'),
			'center' => __('Two lines, centered', 'tainacan-interface')
		);
		return $header_alignment_options;
	}
endif; // tainacan_get_header_alignment_options

if ( ! function_exists( 'tainacan_sanitize_header_alignment_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme item page navigation link options
	 *
	 * Create your own tainacan_sanitize_header_alignment_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $option - a string with options for hader alignments.
	 * @return string the selected option.
	 */
	function tainacan_sanitize_header_alignment_options( $option ) {
		$header_alignment_options = tainacan_get_header_alignment_options();

		if ( ! array_key_exists( $option, $header_alignment_options ) ) {
			return 'default';
		}

		return $option;
	}
endif; // tainacan_sanitize_header_alignment_options


/**
 * Enqueues front-end CSS for the single item page attachments carousel thumbnail size.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_header_settings_style_output() {
	$header_logo_max_height = get_theme_mod( 'tainacan_header_logo_max_height', 120 );
	$header_logo_max_width = get_theme_mod( 'tainacan_header_logo_max_width', 225 );
	$is_fixed_header = get_theme_mod( 'tainacan_fixed_header', false );

	// If the value is not a number, return early.
	if ( !is_numeric( $header_logo_max_height ) || !is_numeric( $header_logo_max_width ) || !is_bool($is_fixed_header) ) {
		return;
	}

	$css = '
		/* Custom Settings for Site Header */
		.tainacan-logo .logo {
			max-height: ' . $header_logo_max_height . 'px !important;
			max-width: ' . $header_logo_max_width . 'px !important;
		}
		@media only screen and (max-width: 768px) {
			.tainacan-logo .logo {
				max-width: 100% !important;
			}
		}' . ( $is_fixed_header ? 
		'body nav.navbar { 
			position: sticky;
			z-index: 9999;
		}
		body:not(.admin-bar) nav.navbar { 
			top: 0;
		}
		body.admin-bar nav.navbar { 
			top: 32px;
		}' 
		: '');
	
	echo '<style type="text/css" id="tainacan-style-header-custom">' . $css . '</style>';
}
add_action( 'wp_head', 'tainacan_header_settings_style_output');

