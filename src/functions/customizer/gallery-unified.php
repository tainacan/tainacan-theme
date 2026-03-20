<?php
/**
 * Tainacan Interface - Unified Gallery & Layout Types
 *
 * Adds customizer options for the unified gallery mode (document + attachments)
 * and the 6 layout types for the single item page.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tainacan_gallery_unified_customizer( $wp_customize ) {

	if ( ! defined( 'TAINACAN_VERSION' ) ) {
		return;
	}

	// --- Gallery & Layout Section ---
	$wp_customize->add_section( 'tainacan_gallery_layout_settings', array(
		'title'       => __( 'Gallery & Page Layout', 'tainacan-interface' ),
		'description' => __( 'Configure the single item page structure, gallery mode, and media display options.', 'tainacan-interface' ),
		'panel'       => 'tainacan_single_item_page',
		'priority'    => 5,
	) );

	// Layout type (6 options)
	$wp_customize->add_setting( 'tainacan_single_item_layout_type', array(
		'default'           => 'type-dam',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_single_item_layout_type', array(
		'label'       => __( 'Page Layout Type', 'tainacan-interface' ),
		'description' => __( 'Choose the arrangement of document, attachments, and metadata on the item page. D=Document, A=Attachments, M=Metadata, G=Gallery (unified).', 'tainacan-interface' ),
		'section'     => 'tainacan_gallery_layout_settings',
		'type'        => 'select',
		'choices'     => array(
			'type-dam' => __( 'Document → Attachments → Metadata', 'tainacan-interface' ),
			'type-dma' => __( 'Document → Metadata → Attachments', 'tainacan-interface' ),
			'type-mda' => __( 'Metadata → Document → Attachments', 'tainacan-interface' ),
			'type-gm'  => __( 'Gallery (sidebar) → Metadata', 'tainacan-interface' ),
			'type-gtm' => __( 'Gallery (top) → Metadata', 'tainacan-interface' ),
			'type-mg'  => __( 'Metadata → Gallery (sidebar)', 'tainacan-interface' ),
		),
	) );

	// --- Unified Gallery Options ---
	$wp_customize->add_setting( 'tainacan_gallery_sticky', array(
		'default'           => false,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_gallery_sticky', array(
		'label'       => __( 'Sticky gallery on scroll', 'tainacan-interface' ),
		'description' => __( 'Keep the gallery visible while scrolling through metadata. Only applies to sidebar gallery layouts (G→M, M→G).', 'tainacan-interface' ),
		'section'     => 'tainacan_gallery_layout_settings',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'tainacan_gallery_width', array(
		'default'           => '50%',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_gallery_width', array(
		'label'       => __( 'Gallery Width', 'tainacan-interface' ),
		'description' => __( 'Width of the gallery section in sidebar layouts.', 'tainacan-interface' ),
		'section'     => 'tainacan_gallery_layout_settings',
		'type'        => 'select',
		'choices'     => array(
			'33%' => '33%',
			'40%' => '40%',
			'45%' => '45%',
			'50%' => '50%',
			'55%' => '55%',
			'60%' => '60%',
		),
	) );

	$wp_customize->add_setting( 'tainacan_gallery_color_scheme', array(
		'default'           => 'light',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_gallery_color_scheme', array(
		'label'   => __( 'Gallery Color Scheme', 'tainacan-interface' ),
		'section' => 'tainacan_gallery_layout_settings',
		'type'    => 'select',
		'choices' => array(
			'light' => __( 'Light', 'tainacan-interface' ),
			'dark'  => __( 'Dark', 'tainacan-interface' ),
		),
	) );

	$wp_customize->add_setting( 'tainacan_gallery_thumbnail_columns', array(
		'default'           => 5,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'tainacan_gallery_thumbnail_columns', array(
		'label'       => __( 'Gallery Thumbnail Columns', 'tainacan-interface' ),
		'description' => __( 'Number of thumbnail columns in the gallery strip.', 'tainacan-interface' ),
		'section'     => 'tainacan_gallery_layout_settings',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 3,
			'max'  => 10,
			'step' => 1,
		),
	) );

	// --- Archive Color Palette ---
	$wp_customize->add_section( 'tainacan_archive_colors', array(
		'title'       => __( 'Archive Page Colors', 'tainacan-interface' ),
		'description' => __( 'Set custom colors for items list archive pages.', 'tainacan-interface' ),
		'panel'       => 'tainacan_items_page',
		'priority'    => 5,
	) );

	$archive_colors = array(
		'tainacan_archive_background_color' => array(
			'label'   => __( 'Archive Background Color', 'tainacan-interface' ),
			'default' => '',
		),
		'tainacan_archive_text_color' => array(
			'label'   => __( 'Archive Text Color', 'tainacan-interface' ),
			'default' => '',
		),
		'tainacan_archive_heading_color' => array(
			'label'   => __( 'Archive Heading Color', 'tainacan-interface' ),
			'default' => '',
		),
		'tainacan_archive_label_color' => array(
			'label'   => __( 'Archive Label Color', 'tainacan-interface' ),
			'default' => '',
		),
	);

	foreach ( $archive_colors as $setting_id => $args ) {
		$wp_customize->add_setting( $setting_id, array(
			'default'           => $args['default'],
			'sanitize_callback' => 'sanitize_hex_color',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, array(
			'label'   => $args['label'],
			'section' => 'tainacan_archive_colors',
		) ) );
	}
}
add_action( 'customize_register', 'tainacan_gallery_unified_customizer' );
