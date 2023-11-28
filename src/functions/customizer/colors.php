<?php

/**
 * Functions that register the options for the customizer
 * related to the color scheme
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_colors') ) {

	function tainacan_interface_customize_register_colors( $wp_customize ) {

		$color_scheme = tainacan_get_color_scheme();
	
		/**
		 * Remove the core header textcolor control, as it shares the main text color.
		 */
		$wp_customize->remove_control( 'header_textcolor' );

		/**
		 * Add color scheme setting and control.
		 */
		$wp_customize->add_setting( 'tainacan_color_scheme', array(
			'type'       		=> 'theme_mod',
			'default'           => 'default',
			'sanitize_callback' => 'tainacan_sanitize_color_scheme',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( 'tainacan_color_scheme', array(
			'label'    => __( 'Choose a Color Scheme', 'tainacan-interface' ),
			'section'  => 'colors',
			'type'     => 'select',
			'choices'  => tainacan_get_color_scheme_choices(),
			'priority' => 1,
		) );

		/**
		 * Add link color setting and control.
		 */
		$wp_customize->add_setting( 'tainacan_link_color', array(
			'type'       		=> 'theme_mod',
			'default'           => $color_scheme[2],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tainacan_link_color', array(
			'label'       => __( 'Or pick any color', 'tainacan-interface' ),
			'section'     => 'colors',
		) ) );

		$wp_customize->add_setting( 'tainacan_tooltip_color', array(
			'type'       		=> 'theme_mod',
			'default'           => $color_scheme[3],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tainacan_tooltip_color', array(
			'label'       => __( 'Tooltip Color', 'tainacan-interface' ),
			'section'     => 'colors',
		) ) );

	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_colors', 11 );
}


/**
 * Registers color schemes for Tainacan Interface theme.
 *
 * Can be filtered with {@see 'tainacan_color_schemes'}.
 *
 * The order of colors in a colors array:
 * 1. Main Background Color.
 * 2. Page Background Color.
 * 3. Link Color.
 * 4. Tooltip.
 *
 * @since Tainacan Interface theme
 *
 * @return array An associative array of color scheme options.
 */
function tainacan_get_color_schemes() {
	/**
	 * Filter the color schemes registered for use with Tainacan Interface theme.
	 *
	 * The default schemes include 'default', 'dark', 'gray', 'red', and 'yellow'.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param array $schemes {
	 *     Associative array of color schemes data.
	 *
	 *     @type array $slug {
	 *         Associative array of information for setting up the color scheme.
	 *
	 *         @type string $label  Color scheme label.
	 *         @type array  $colors HEX codes for default colors prepended with a hash symbol ('#').
	 *                              Colors are defined in the following order: Main background, page
	 *                              background, link, main text, secondary text.
	 *     }
	 * }
	 */
	return apply_filters( 'tainacan_color_schemes', array(
		'default' => array(
			'label'  => __( 'Default', 'tainacan-interface' ),
			'colors' => array(
				'#1a1a1a', //background
				'#ffffff', //background page
				'#187181', //link
				'#e6f6f8', //tooltip
			),
		),
		'carmine' => array(
			'label'  => __( 'Carmine', 'tainacan-interface' ),
			'colors' => array(
				'#262626', //background
				'#ffffff', //background page
				'#8c442c', //link
				'#e6d3cd', //tooltip
			),
		),
		'cherry' => array(
			'label'  => __( 'Cherry', 'tainacan-interface' ),
			'colors' => array(
				'#616a73', //background
				'#ffffff', //background page
				'#A12B42', //link
				'#e9cbd1', //tooltip
			),
		),
		'mustard' => array(
			'label'  => __( 'Mustard', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#754E24', //link
				'#f0e1cf', //tooltip
			),
		),
		'mintgreen' => array(
			'label'  => __( 'Mint Green', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#255F56', //link
				'#d4efe9', //tooltip
			),
		),
		'darkturquoise' => array(
			'label'  => __( 'Dark Turquoise', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#205E6F', //link
				'#cbe0e5', //tooltip
			),
		),
		'turquoise' => array(
			'label'  => __( 'Turquoise', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#185F6D', //link
				'#cdecef', //tooltip
			),
		),
		'blueheavenly' => array(
			'label'  => __( 'Blue Heavenly', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#1D5C86', //link
				'#d3e6f2', //tooltip
			),
		),
		'purple' => array(
			'label'  => __( 'Purple', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#4751a3', //link
				'#d1d4e7', //tooltip
			),
		),
		'violet' => array(
			'label'  => __( 'Violet', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#955ba5', //link
				'#e4d7e8', //tooltip
			),
		),
		'gray' => array(
			'label'  => __( 'Gray', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#505253', //link
				'#f2f2f2', //tooltip
			),
		),
	) );
}

/**
 * Binds the JS listener to make Customizer color_scheme control.
 *
 * Passes color scheme data as colorScheme global.
 *
 * @since Tainacan Interface theme
 */
function tainacan_customize_control_js() {
	wp_enqueue_script( 'tainacan-color-scheme-control', get_template_directory_uri() . '/assets/js/color-scheme-control.js', array( 'customize-controls', 'iris', 'underscore', 'wp-util' ), TAINACAN_INTERFACE_VERSION , true );
	wp_localize_script( 'tainacan-color-scheme-control', 'TainacanColorScheme', tainacan_get_color_schemes() );
}
add_action( 'customize_controls_enqueue_scripts', 'tainacan_customize_control_js' );


if ( ! function_exists( 'tainacan_get_color_scheme' ) ) :
	/**
	 * Retrieves the current Tainacan Interface theme color scheme.
	 *
	 * Create your own tainacan_get_color_scheme() function to override in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array An associative array of either the current or default color scheme HEX values.
	 */
	function tainacan_get_color_scheme() {
		$color_scheme_option = get_theme_mod( 'tainacan_color_scheme', 'default' );
		$link_color = get_theme_mod( 'tainacan_link_color', 'default' ); // sanitized upon save
		$tooltip_color = get_theme_mod( 'tainacan_tooltip_color', 'default' ); // sanitized upon save
		$color_schemes       = tainacan_get_color_schemes();

		if ( array_key_exists( $color_scheme_option, $color_schemes ) ) {
			$return = $color_schemes[ $color_scheme_option ]['colors'];
		}

		$return = $color_schemes['default']['colors'];
		$return[2] = $link_color; // override link color with the one from color picker
		$return[3] = $tooltip_color;
		return $return;

	}
endif; // tainacan_get_color_scheme



if ( ! function_exists( 'tainacan_get_color_scheme_choices' ) ) :
	/**
	 * Retrieves an array of color scheme choices registered for Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_color_scheme_choices() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array Array of color schemes.
	 */
	function tainacan_get_color_scheme_choices() {
		$color_schemes                = tainacan_get_color_schemes();
		$color_scheme_control_options = array();

		foreach ( $color_schemes as $color_scheme => $value ) {
			$color_scheme_control_options[ $color_scheme ] = $value['label'];
		}

		return $color_scheme_control_options;
	}
endif; // tainacan_get_color_scheme_choices


if ( ! function_exists( 'tainacan_sanitize_color_scheme' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme color schemes.
	 *
	 * Create your own tainacan_sanitize_color_scheme() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $value Color scheme name value.
	 * @return string Color scheme name.
	 */
	function tainacan_sanitize_color_scheme( $value ) {
		$color_schemes = tainacan_get_color_scheme_choices();

		if ( ! array_key_exists( $value, $color_schemes ) ) {
			return 'default';
		}

		return $value;
	}
endif; // tainacan_sanitize_color_scheme



/**
 * Enqueues front-end CSS for color scheme.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_color_scheme_css() {

	$color_scheme = tainacan_get_color_scheme();

	// Convert main text hex color to rgba.
	$color_textcolor_rgb = tainacan_hex2rgb( $color_scheme[2] );

	// If the rgba values are empty return early.
	if ( empty( $color_textcolor_rgb ) ) {
		return;
	}

	// If we get this far, we have a custom color scheme.
	$colors = array(
		'background_color'      => $color_scheme[0],
		'page_background_color' => $color_scheme[1],
		'tainacan_link_color'            => $color_scheme[2],
		'tainacan_tooltip_color'            => $color_scheme[3],
		'backtransparent'			=> vsprintf( 'rgba( %1$s, %2$s, %3$s, 0.3)', $color_textcolor_rgb ),
	);

	$color_scheme_css = tainacan_get_color_scheme_css( $colors );

	echo '<style type="text/css" id="custom-theme-css">' . $color_scheme_css . '</style>';
}
add_action( 'wp_head', 'tainacan_color_scheme_css' );

/**
 * Returns CSS for the color schemes.
 *
 * @since Tainacan Interface theme
 *
 * @param array $colors Color scheme colors.
 * @return string Color scheme CSS.
 */
function tainacan_get_color_scheme_css( $colors ) {
	$colors = wp_parse_args( $colors, array(
		'background_color'      => '',
		'page_background_color' => '',
		'tainacan_link_color'            => '',
		'tainacan_tooltip_color'            => '',
		'backtransparent'           => '',
	) );

	$filter = ( has_filter( 'tainacan-customize-css-class' ) ) ? apply_filters( 'tainacan-customize-css-class', $colors ) : '';

	return <<<CSS
		body {
			--tainacan-interface--link-color: {$colors['tainacan_link_color']} !important;
			--tainacan-interface--tooltip-color: {$colors['tainacan_tooltip_color']} !important;
		}

	{$filter}
	
CSS;
}


/**
 * Outputs an Underscore template for generating CSS for the color scheme.
 *
 * The template generates the css dynamically for instant display in the
 * Customizer preview.
 *
 * @since Tainacan Interface theme
 */
function tainacan_color_scheme_css_template() {
	$colors = array(
		'background_color'      => '{{ data.background_color }}',
		'page_background_color' => '{{ data.page_background_color }}',
		'tainacan_link_color'            => '{{ data.tainacan_link_color }}',
		'tainacan_tooltip_color'            => '{{ data.tainacan_tooltip_color }}',
		'backtransparent'		=> '{{ data.backtransparent }}',
	);
	?>
	<script type="text/html" id="tmpl-tainacan-color-scheme">
		<?php echo tainacan_get_color_scheme_css( $colors ); ?>
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', 'tainacan_color_scheme_css_template' );

/**
 * Enqueues front-end CSS for the link color.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_link_color_css() {
	$color_scheme    = tainacan_get_color_scheme();
	$default_color   = $color_scheme[2];
	$link_color = get_theme_mod( 'tainacan_link_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $link_color === $default_color ) {
		return;
	}

	$css = '
		/* Custom Link Color */
		body a, 
		.tainacan-title-page ul li, 
		.tainacan-title-page ul li a,
		.tainacan-title-page ul li a:hover, 
		.tainacan-list-post .blog-content h3 {
			color: %1$s !important;
		}
	';

	wp_add_inline_style( 'tainacan-style', sprintf( $css, $link_color ) );
}
add_action( 'wp_enqueue_scripts', 'tainacan_link_color_css', 11 );

/**
 * Enqueues front-end CSS for the main text color.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_tooltip_color_css() {
	$color_scheme    = tainacan_get_color_scheme();
	$default_color   = $color_scheme[3];
	$tooltip_color = get_theme_mod( 'tainacan_tooltip_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $tooltip_color === $default_color ) {
		return;
	}

	// Convert main text hex color to rgba.
	$tooltip_color_rgb = tainacan_hex2rgb( $tooltip_color );

	// If the rgba values are empty return early.
	if ( empty( $tooltip_color_rgb ) ) {
		return;
	}

	// If we get this far, we have a custom color scheme.
	$border_color = vsprintf( 'rgba( %1$s, %2$s, %3$s, 0.2)', $tooltip_color_rgb );

	$css = '
		/* Custom Main Text Color */
		.tainacan-list-post .blog-post .blog-content .blog-read {
			color: %1$s !important;
		}
	';

	wp_add_inline_style( 'tainacan-style', sprintf( $css, $tooltip_color, $border_color ) );
}
add_action( 'wp_enqueue_scripts', 'tainacan_tooltip_color_css', 11 );