<?php
/**
 * Tainacan Interface - Per-Section Typography Controls
 *
 * Adds customizer options for configuring typography independently
 * for metadata labels, values, section titles, and document headings.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tainacan_typography_customizer( $wp_customize ) {

	if ( ! defined( 'TAINACAN_VERSION' ) ) {
		return;
	}

	// Typography Section
	$wp_customize->add_section( 'tainacan_typography_settings', array(
		'title'       => __( 'Typography', 'tainacan-interface' ),
		'description' => __( 'Configure font sizes, weights, and styles for different sections of the item page.', 'tainacan-interface' ),
		'panel'       => 'tainacan_single_item_page',
		'priority'    => 50,
	) );

	// Font family
	$wp_customize->add_setting( 'tainacan_typography_font_family', array(
		'default'           => 'Roboto',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_font_family', array(
		'label'   => __( 'Primary Font Family', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'Roboto'         => 'Roboto',
			'Open Sans'      => 'Open Sans',
			'Lato'           => 'Lato',
			'Montserrat'     => 'Montserrat',
			'Source Sans Pro' => 'Source Sans Pro',
			'Nunito'         => 'Nunito',
			'Poppins'        => 'Poppins',
			'Inter'          => 'Inter',
			'system-ui'      => __( 'System Default', 'tainacan-interface' ),
		),
	) );

	// --- Metadata Labels ---
	$wp_customize->add_setting( 'tainacan_typography_meta_label_size', array(
		'default'           => '0.875rem',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_meta_label_size', array(
		'label'   => __( 'Metadata Label Font Size', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'0.75rem'  => __( 'Small (12px)', 'tainacan-interface' ),
			'0.875rem' => __( 'Medium (14px)', 'tainacan-interface' ),
			'1rem'     => __( 'Normal (16px)', 'tainacan-interface' ),
			'1.125rem' => __( 'Large (18px)', 'tainacan-interface' ),
		),
	) );

	$wp_customize->add_setting( 'tainacan_typography_meta_label_weight', array(
		'default'           => '600',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_meta_label_weight', array(
		'label'   => __( 'Metadata Label Font Weight', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'400' => __( 'Normal', 'tainacan-interface' ),
			'500' => __( 'Medium', 'tainacan-interface' ),
			'600' => __( 'Semi-Bold', 'tainacan-interface' ),
			'700' => __( 'Bold', 'tainacan-interface' ),
		),
	) );

	$wp_customize->add_setting( 'tainacan_typography_meta_label_transform', array(
		'default'           => 'uppercase',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_meta_label_transform', array(
		'label'   => __( 'Metadata Label Text Transform', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'none'       => __( 'None', 'tainacan-interface' ),
			'uppercase'  => __( 'UPPERCASE', 'tainacan-interface' ),
			'lowercase'  => __( 'lowercase', 'tainacan-interface' ),
			'capitalize' => __( 'Capitalize', 'tainacan-interface' ),
		),
	) );

	// --- Metadata Values ---
	$wp_customize->add_setting( 'tainacan_typography_meta_value_size', array(
		'default'           => '1rem',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_meta_value_size', array(
		'label'   => __( 'Metadata Value Font Size', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'0.875rem' => __( 'Small (14px)', 'tainacan-interface' ),
			'1rem'     => __( 'Normal (16px)', 'tainacan-interface' ),
			'1.125rem' => __( 'Large (18px)', 'tainacan-interface' ),
			'1.25rem'  => __( 'Extra Large (20px)', 'tainacan-interface' ),
		),
	) );

	$wp_customize->add_setting( 'tainacan_typography_meta_value_weight', array(
		'default'           => '400',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_meta_value_weight', array(
		'label'   => __( 'Metadata Value Font Weight', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'300' => __( 'Light', 'tainacan-interface' ),
			'400' => __( 'Normal', 'tainacan-interface' ),
			'500' => __( 'Medium', 'tainacan-interface' ),
			'600' => __( 'Semi-Bold', 'tainacan-interface' ),
		),
	) );

	// --- Section Titles ---
	$wp_customize->add_setting( 'tainacan_typography_section_title_size', array(
		'default'           => '1.25rem',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_section_title_size', array(
		'label'   => __( 'Section Title Font Size', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'1rem'     => __( 'Normal (16px)', 'tainacan-interface' ),
			'1.125rem' => __( 'Medium (18px)', 'tainacan-interface' ),
			'1.25rem'  => __( 'Large (20px)', 'tainacan-interface' ),
			'1.5rem'   => __( 'Extra Large (24px)', 'tainacan-interface' ),
		),
	) );

	$wp_customize->add_setting( 'tainacan_typography_section_title_weight', array(
		'default'           => '600',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_section_title_weight', array(
		'label'   => __( 'Section Title Font Weight', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'400' => __( 'Normal', 'tainacan-interface' ),
			'500' => __( 'Medium', 'tainacan-interface' ),
			'600' => __( 'Semi-Bold', 'tainacan-interface' ),
			'700' => __( 'Bold', 'tainacan-interface' ),
		),
	) );

	// --- Document Title ---
	$wp_customize->add_setting( 'tainacan_typography_document_title_size', array(
		'default'           => '1.125rem',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_typography_document_title_size', array(
		'label'   => __( 'Document Title Font Size', 'tainacan-interface' ),
		'section' => 'tainacan_typography_settings',
		'type'    => 'select',
		'choices' => array(
			'0.875rem' => __( 'Small (14px)', 'tainacan-interface' ),
			'1rem'     => __( 'Normal (16px)', 'tainacan-interface' ),
			'1.125rem' => __( 'Medium (18px)', 'tainacan-interface' ),
			'1.25rem'  => __( 'Large (20px)', 'tainacan-interface' ),
		),
	) );
}
add_action( 'customize_register', 'tainacan_typography_customizer' );
