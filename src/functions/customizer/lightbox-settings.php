<?php
/**
 * Tainacan Interface - Lightbox Configuration
 *
 * Adds customizer options for configuring the image lightbox behavior.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tainacan_lightbox_customizer( $wp_customize ) {

	if ( ! defined( 'TAINACAN_VERSION' ) ) {
		return;
	}

	// Lightbox Section
	$wp_customize->add_section( 'tainacan_lightbox_settings', array(
		'title'       => __( 'Lightbox', 'tainacan-interface' ),
		'description' => __( 'Configure the fullscreen image viewer behavior and display options.', 'tainacan-interface' ),
		'panel'       => 'tainacan_single_item_page',
		'priority'    => 45,
	) );

	// Enable/disable lightbox
	$wp_customize->add_setting( 'tainacan_lightbox_enabled', array(
		'default'           => true,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_enabled', array(
		'label'       => __( 'Enable Lightbox', 'tainacan-interface' ),
		'description' => __( 'Allow images to open in a fullscreen viewer when clicked.', 'tainacan-interface' ),
		'section'     => 'tainacan_lightbox_settings',
		'type'        => 'checkbox',
	) );

	// Show file name
	$wp_customize->add_setting( 'tainacan_lightbox_show_filename', array(
		'default'           => false,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_show_filename', array(
		'label'   => __( 'Show file name in lightbox', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'checkbox',
	) );

	// Show caption
	$wp_customize->add_setting( 'tainacan_lightbox_show_caption', array(
		'default'           => true,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_show_caption', array(
		'label'   => __( 'Show caption in lightbox', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'checkbox',
	) );

	// Show description
	$wp_customize->add_setting( 'tainacan_lightbox_show_description', array(
		'default'           => false,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_show_description', array(
		'label'   => __( 'Show description in lightbox', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'checkbox',
	) );

	// Show download button
	$wp_customize->add_setting( 'tainacan_lightbox_show_download', array(
		'default'           => true,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_show_download', array(
		'label'   => __( 'Show download button in lightbox', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'checkbox',
	) );

	// Show counter (e.g., "3 of 12")
	$wp_customize->add_setting( 'tainacan_lightbox_show_counter', array(
		'default'           => true,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_show_counter', array(
		'label'   => __( 'Show image counter in lightbox', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'checkbox',
	) );

	// Color scheme
	$wp_customize->add_setting( 'tainacan_lightbox_color_scheme', array(
		'default'           => 'dark',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_color_scheme', array(
		'label'   => __( 'Lightbox Color Scheme', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'select',
		'choices' => array(
			'dark'  => __( 'Dark', 'tainacan-interface' ),
			'light' => __( 'Light', 'tainacan-interface' ),
		),
	) );

	// Animation type
	$wp_customize->add_setting( 'tainacan_lightbox_animation', array(
		'default'           => 'fade',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_animation', array(
		'label'   => __( 'Lightbox Animation', 'tainacan-interface' ),
		'section' => 'tainacan_lightbox_settings',
		'type'    => 'select',
		'choices' => array(
			'fade'  => __( 'Fade', 'tainacan-interface' ),
			'slide' => __( 'Slide', 'tainacan-interface' ),
			'zoom'  => __( 'Zoom', 'tainacan-interface' ),
			'none'  => __( 'None', 'tainacan-interface' ),
		),
	) );

	// Thumbnail strip
	$wp_customize->add_setting( 'tainacan_lightbox_thumbnails', array(
		'default'           => true,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_lightbox_thumbnails', array(
		'label'       => __( 'Show thumbnail strip in lightbox', 'tainacan-interface' ),
		'description' => __( 'Display a strip of thumbnail previews at the bottom of the lightbox.', 'tainacan-interface' ),
		'section'     => 'tainacan_lightbox_settings',
		'type'        => 'checkbox',
	) );
}
add_action( 'customize_register', 'tainacan_lightbox_customizer' );
