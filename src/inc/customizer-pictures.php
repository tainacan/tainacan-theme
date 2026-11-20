<?php
/**
 * Pictures in the Customizer: each choice of the options with a drawing
 * (inc/collection-form.php for the ones a collection can change,
 * inc/customizer-drawings.php for the others) shows it, in the colors being set.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options of the Customizer drawn as pictures, with the drawing key and the
 * short names. The open panels of the metadata sections use the drawings of
 * the combined setting of the collection form.
 *
 * @return array<string, array{picture: string, names: array<string, string>, values: array<string, string>}>
 */
function tainacan_interface_picture_options() {
	$options = array();
	foreach ( tainacan_interface_cform_groups() as $group ) {
		foreach ( $group['options'] as $key => $names ) {
			if ( $names ) {
				$options[ $key ] = array(
					'picture' => $key,
					'names'   => $names,
					'values'  => array_combine( array_keys( $names ), array_keys( $names ) ),
				);
			}
		}
	}

	foreach ( tainacan_interface_site_picture_keys() as $key ) {
		$values          = array_map( 'strval', array_keys( tainacan_interface_settings_schema()[ $key ]['choices'] ) );
		$options[ $key ] = array(
			'picture' => $key,
			'names'   => array(),
			'values'  => array_combine( $values, $values ),
		);
	}

	$sections                 = tainacan_interface_sections_choices();
	$options['item_sections'] = array(
		'picture' => 'sections',
		'names'   => array(
			'first' => $sections['open-first'][0],
			'all'   => $sections['open-all'][0],
			'none'  => $sections['closed'][0],
		),
		'values'  => array(
			'first' => 'open-first',
			'all'   => 'open-all',
			'none'  => 'closed',
		),
	);
	return $options;
}

/**
 * Colors of the site used by the pictures of the Customizer: option => CSS
 * variable on the controls, which the panel script updates while they change.
 *
 * @return string[]
 */
function tainacan_interface_picture_colors() {
	return array(
		'brand_color'  => '--tt2c-brand',
		'accent_color' => '--tt2c-accent',
		'band_1'       => '--tt2c-b1',
		'band_2'       => '--tt2c-b2',
		'band_3'       => '--tt2c-b3',
		'band_4'       => '--tt2c-b4',
	);
}

/**
 * Declares the control once the Customizer classes exist.
 */
function tainacan_interface_picture_control_class() {
	if ( ! class_exists( 'Tainacan_Interface_Picture_Control' ) && class_exists( 'WP_Customize_Control' ) ) {
		require_once get_template_directory() . '/inc/class-tainacan-interface-picture-control.php';
	}
}
add_action( 'customize_register', 'tainacan_interface_picture_control_class', 1 );

/**
 * Style of the pictures in the Customizer, in the colors being set.
 */
function tainacan_interface_picture_control_assets() {
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'tainacan-interface-collection-form', $uri . '/assets/css/collection-form.css', array(), TAINACAN_INTERFACE_VERSION );
	$vars = '';
	foreach ( tainacan_interface_picture_colors() as $key => $var ) {
		$vars .= $var . ':' . tainacan_interface_cform_hex6( tainacan_interface_cform_site_value( $key ) ) . ';';
	}
	wp_add_inline_style( 'tainacan-interface-collection-form', '#customize-controls{' . esc_html( $vars ) . '}' );
}
add_action( 'customize_controls_enqueue_scripts', 'tainacan_interface_picture_control_assets' );
