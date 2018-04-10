<?php
/**
 * Display custom color CSS.
 */
function tainacan_colors_css_wrap() {
	if ( 'custom' !== get_theme_mod( 'colorscheme' ) && ! is_customize_preview() ) {
		return;
	}

	require_once( get_parent_theme_file_path( '/functions/color-patterns.php' ) );
	$hue = absint( get_theme_mod( 'colorscheme_hue', 250 ) );

	$customize_preview_data_hue = '';
	if ( is_customize_preview() ) {
		$customize_preview_data_hue = 'data-hue="' . $hue . '"';
	}
?>
	<style type="text/css" id="custom-theme-colors" <?php echo $customize_preview_data_hue; ?>>
		<?php echo tainacan_custom_colors_css(); ?>
	</style>
<?php
}
add_action( 'wp_head', 'tainacan_colors_css_wrap' );

function tainacan_customize_register( $wp_customize ) {

	/**
	 * Custom colors.
	 */
	$wp_customize->add_setting(
		'colorscheme', array(
			'default'           => 'light',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'tainacan_sanitize_colorscheme',
		)
	);

	$wp_customize->add_setting(
		'colorscheme_hue', array(
			'default'           => 250,
			'transport'         => 'postMessage',
			'sanitize_callback' => 'absint', // The hue is stored as a positive integer.
		)
	);

	$wp_customize->add_control(
		'colorscheme', array(
			'type'     => 'radio',
			'label'    => __( 'Color Scheme', 'tainacan-theme' ),
			'choices'  => array(
				'light'  => __( 'Light', 'tainacan-theme' ),
				'dark'   => __( 'Dark', 'tainacan-theme' ),
				'custom' => __( 'Custom', 'tainacan-theme' ),
			),
			'section'  => 'colors',
			'priority' => 5,
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, 'colorscheme_hue', array(
				'mode'     => 'hue',
				'section'  => 'colors',
				'priority' => 6,
			)
		)
	);
}
add_action( 'customize_register', 'tainacan_customize_register' );

function tainacan_sanitize_colorscheme( $input ) {
	$valid = array( 'light', 'dark', 'custom' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'light';
}

/**
 * Bind JS handlers to instantly live-preview changes.
 */
function tainacan_customize_preview_js() {
	wp_enqueue_script( 'tainacan-customize-preview', get_theme_file_uri( '/assets/js/customize-preview.js' ), array( 'customize-preview' ), '1.0', true );
}
add_action( 'customize_preview_init', 'tainacan_customize_preview_js' );

/**
 * Load dynamic logic for the customizer controls area.
 */
function tainacan_panels_js() {
	wp_enqueue_script( 'tainacan-customize-controls', get_theme_file_uri( '/assets/js/customize-controls.js' ), array(), '1.0', true );
}
add_action( 'customize_controls_enqueue_scripts', 'tainacan_panels_js' );