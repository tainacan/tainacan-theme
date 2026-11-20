<?php
/**
 * Pictures in the Customizer: the options that a collection can change on its
 * form show, here too, a drawing of each choice (the same drawings of
 * inc/collection-form.php), in the colors being set.
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
	$brand  = tainacan_interface_cform_hex6( tainacan_interface_cform_site_value( 'brand_color' ) );
	$accent = tainacan_interface_cform_hex6( tainacan_interface_cform_site_value( 'accent_color' ) );
	wp_add_inline_style( 'tainacan-interface-collection-form', sprintf( '.tt2-cform--customizer{--tt2f-brand:%1$s;--tt2f-accent:%2$s;}', esc_html( $brand ), esc_html( $accent ) ) );
}
add_action( 'customize_controls_enqueue_scripts', 'tainacan_interface_picture_control_assets' );
