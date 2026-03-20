<?php
/**
 * Tainacan Interface - Pastel Color System
 *
 * Provides configurable pastel color palettes for the theme.
 * Colors are softer, modern tones that can be applied across all theme elements.
 *
 * @package suspended Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pastel color presets with their variations
 */
function tainacan_get_pastel_palettes() {
	return array(
		'rose' => array(
			'name'       => __( 'Rose', 'tainacan-interface' ),
			'primary'    => '#f4a0a0',
			'secondary'  => '#f8c4c4',
			'accent'     => '#d4736e',
			'background' => '#fdf0f0',
			'surface'    => '#ffffff',
			'text'       => '#5a3e3e',
			'muted'      => '#c9a0a0',
		),
		'lavender' => array(
			'name'       => __( 'Lavender', 'tainacan-interface' ),
			'primary'    => '#b8a9d4',
			'secondary'  => '#d4c8eb',
			'accent'     => '#8b72b8',
			'background' => '#f5f0fa',
			'surface'    => '#ffffff',
			'text'       => '#4a3d5c',
			'muted'      => '#a89cc4',
		),
		'sage' => array(
			'name'       => __( 'Sage', 'tainacan-interface' ),
			'primary'    => '#a8c5a0',
			'secondary'  => '#c8dcc4',
			'accent'     => '#6d9a64',
			'background' => '#f0f5ee',
			'surface'    => '#ffffff',
			'text'       => '#3d4a3a',
			'muted'      => '#98b490',
		),
		'sky' => array(
			'name'       => __( 'Sky', 'tainacan-interface' ),
			'primary'    => '#a0c4e8',
			'secondary'  => '#c4daf0',
			'accent'     => '#5a9ad4',
			'background' => '#f0f5fa',
			'surface'    => '#ffffff',
			'text'       => '#3a4a5a',
			'muted'      => '#90b4d0',
		),
		'peach' => array(
			'name'       => __( 'Peach', 'tainacan-interface' ),
			'primary'    => '#f0b88c',
			'secondary'  => '#f4d0b0',
			'accent'     => '#d48a56',
			'background' => '#fdf5f0',
			'surface'    => '#ffffff',
			'text'       => '#5a4434',
			'muted'      => '#d4b098',
		),
		'mint' => array(
			'name'       => __( 'Mint', 'tainacan-interface' ),
			'primary'    => '#8cc8b8',
			'secondary'  => '#b0dcd0',
			'accent'     => '#58a898',
			'background' => '#f0faf5',
			'surface'    => '#ffffff',
			'text'       => '#344a44',
			'muted'      => '#90c0b0',
		),
		'coral' => array(
			'name'       => __( 'Coral', 'tainacan-interface' ),
			'primary'    => '#f0a89c',
			'secondary'  => '#f4c8c0',
			'accent'     => '#d47868',
			'background' => '#fdf2f0',
			'surface'    => '#ffffff',
			'text'       => '#5a4240',
			'muted'      => '#d4a8a0',
		),
		'butter' => array(
			'name'       => __( 'Butter', 'tainacan-interface' ),
			'primary'    => '#e8d88c',
			'secondary'  => '#f0e4b0',
			'accent'     => '#c8b458',
			'background' => '#fdfaf0',
			'surface'    => '#ffffff',
			'text'       => '#5a5434',
			'muted'      => '#d0c890',
		),
		'lilac' => array(
			'name'       => __( 'Lilac', 'tainacan-interface' ),
			'primary'    => '#c8a8d8',
			'secondary'  => '#dcc8e8',
			'accent'     => '#a478b8',
			'background' => '#f8f0fc',
			'surface'    => '#ffffff',
			'text'       => '#4a3854',
			'muted'      => '#b898c8',
		),
		'ocean' => array(
			'name'       => __( 'Ocean', 'tainacan-interface' ),
			'primary'    => '#88b8c8',
			'secondary'  => '#b0d0d8',
			'accent'     => '#5898a8',
			'background' => '#f0f8fa',
			'surface'    => '#ffffff',
			'text'       => '#344850',
			'muted'      => '#88b0c0',
		),
		'tainacan-classic' => array(
			'name'       => __( 'Tainacan Classic', 'tainacan-interface' ),
			'primary'    => '#187181',
			'secondary'  => '#e6f6f8',
			'accent'     => '#125a68',
			'background' => '#f5fafb',
			'surface'    => '#ffffff',
			'text'       => '#2c2d2d',
			'muted'      => '#8abcc4',
		),
		'custom' => array(
			'name'       => __( 'Custom', 'tainacan-interface' ),
			'primary'    => '#187181',
			'secondary'  => '#e6f6f8',
			'accent'     => '#125a68',
			'background' => '#f5fafb',
			'surface'    => '#ffffff',
			'text'       => '#2c2d2d',
			'muted'      => '#8abcc4',
		),
	);
}

/**
 * Get the active pastel palette
 */
function tainacan_get_active_pastel_palette() {
	$palette_slug = get_theme_mod( 'tainacan_pastel_palette', 'tainacan-classic' );
	$palettes = tainacan_get_pastel_palettes();

	if ( $palette_slug === 'custom' ) {
		return array(
			'name'       => __( 'Custom', 'tainacan-interface' ),
			'primary'    => get_theme_mod( 'tainacan_pastel_custom_primary', '#187181' ),
			'secondary'  => get_theme_mod( 'tainacan_pastel_custom_secondary', '#e6f6f8' ),
			'accent'     => get_theme_mod( 'tainacan_pastel_custom_accent', '#125a68' ),
			'background' => get_theme_mod( 'tainacan_pastel_custom_background', '#f5fafb' ),
			'surface'    => get_theme_mod( 'tainacan_pastel_custom_surface', '#ffffff' ),
			'text'       => get_theme_mod( 'tainacan_pastel_custom_text', '#2c2d2d' ),
			'muted'      => get_theme_mod( 'tainacan_pastel_custom_muted', '#8abcc4' ),
		);
	}

	return isset( $palettes[ $palette_slug ] ) ? $palettes[ $palette_slug ] : $palettes['tainacan-classic'];
}

/**
 * Register pastel color customizer settings
 */
function tainacan_pastel_colors_customizer( $wp_customize ) {

	// Section
	$wp_customize->add_section( 'tainacan_pastel_colors', array(
		'title'       => __( 'Pastel Color Palette', 'tainacan-interface' ),
		'description' => __( 'Choose a modern pastel color palette for your site. Each palette provides harmonious colors for all theme elements.', 'tainacan-interface' ),
		'priority'    => 25,
	) );

	// Palette selector
	$palettes = tainacan_get_pastel_palettes();
	$choices = array();
	foreach ( $palettes as $slug => $palette ) {
		$choices[ $slug ] = $palette['name'];
	}

	$wp_customize->add_setting( 'tainacan_pastel_palette', array(
		'default'           => 'tainacan-classic',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'tainacan_pastel_palette', array(
		'label'   => __( 'Color Palette', 'tainacan-interface' ),
		'section' => 'tainacan_pastel_colors',
		'type'    => 'select',
		'choices' => $choices,
	) );

	// Custom color controls (shown when "custom" palette is selected)
	$custom_colors = array(
		'primary'    => __( 'Primary Color', 'tainacan-interface' ),
		'secondary'  => __( 'Secondary Color', 'tainacan-interface' ),
		'accent'     => __( 'Accent Color', 'tainacan-interface' ),
		'background' => __( 'Background Color', 'tainacan-interface' ),
		'surface'    => __( 'Surface Color', 'tainacan-interface' ),
		'text'       => __( 'Text Color', 'tainacan-interface' ),
		'muted'      => __( 'Muted Color', 'tainacan-interface' ),
	);

	$defaults = array(
		'primary'    => '#187181',
		'secondary'  => '#e6f6f8',
		'accent'     => '#125a68',
		'background' => '#f5fafb',
		'surface'    => '#ffffff',
		'text'       => '#2c2d2d',
		'muted'      => '#8abcc4',
	);

	foreach ( $custom_colors as $key => $label ) {
		$setting_id = 'tainacan_pastel_custom_' . $key;

		$wp_customize->add_setting( $setting_id, array(
			'default'           => $defaults[ $key ],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, array(
			'label'   => $label,
			'section' => 'tainacan_pastel_colors',
		) ) );
	}

	// Dark mode toggle
	$wp_customize->add_setting( 'tainacan_enable_dark_mode', array(
		'default'           => false,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_enable_dark_mode', array(
		'label'       => __( 'Enable dark mode toggle', 'tainacan-interface' ),
		'description' => __( 'Allow visitors to switch between light and dark modes.', 'tainacan-interface' ),
		'section'     => 'tainacan_pastel_colors',
		'type'        => 'checkbox',
	) );
}
add_action( 'customize_register', 'tainacan_pastel_colors_customizer' );

/**
 * Output pastel color CSS variables
 */
function tainacan_output_pastel_css() {
	$palette = tainacan_get_active_pastel_palette();

	$css = ':root {' . "\n";
	$css .= '  --tainacan-pastel-primary: ' . esc_attr( $palette['primary'] ) . ';' . "\n";
	$css .= '  --tainacan-pastel-secondary: ' . esc_attr( $palette['secondary'] ) . ';' . "\n";
	$css .= '  --tainacan-pastel-accent: ' . esc_attr( $palette['accent'] ) . ';' . "\n";
	$css .= '  --tainacan-pastel-background: ' . esc_attr( $palette['background'] ) . ';' . "\n";
	$css .= '  --tainacan-pastel-surface: ' . esc_attr( $palette['surface'] ) . ';' . "\n";
	$css .= '  --tainacan-pastel-text: ' . esc_attr( $palette['text'] ) . ';' . "\n";
	$css .= '  --tainacan-pastel-muted: ' . esc_attr( $palette['muted'] ) . ';' . "\n";

	// Generate alpha variants
	$rgb = tainacan_hex2rgb( $palette['primary'] );
	if ( ! empty( $rgb ) ) {
		$css .= '  --tainacan-pastel-primary-rgb: ' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ';' . "\n";
		$css .= '  --tainacan-pastel-primary-10: rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ', 0.1);' . "\n";
		$css .= '  --tainacan-pastel-primary-20: rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ', 0.2);' . "\n";
		$css .= '  --tainacan-pastel-primary-50: rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ', 0.5);' . "\n";
	}

	$rgb_accent = tainacan_hex2rgb( $palette['accent'] );
	if ( ! empty( $rgb_accent ) ) {
		$css .= '  --tainacan-pastel-accent-rgb: ' . $rgb_accent['red'] . ',' . $rgb_accent['green'] . ',' . $rgb_accent['blue'] . ';' . "\n";
	}

	$css .= '}' . "\n\n";

	// Dark mode overrides
	$enable_dark = get_theme_mod( 'tainacan_enable_dark_mode', false );
	if ( $enable_dark ) {
		$css .= '[data-theme="dark"] {' . "\n";
		$css .= '  --tainacan-pastel-background: #1a1a2e;' . "\n";
		$css .= '  --tainacan-pastel-surface: #25253e;' . "\n";
		$css .= '  --tainacan-pastel-text: #e0e0e0;' . "\n";
		$css .= '  --tainacan-pastel-muted: #6a6a8a;' . "\n";
		$css .= '}' . "\n";
	}

	echo '<style id="tainacan-pastel-colors">' . "\n" . $css . '</style>' . "\n";
}
add_action( 'wp_head', 'tainacan_output_pastel_css', 5 );
