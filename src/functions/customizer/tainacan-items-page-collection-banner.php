<?php

/**
 * Functions that register the options for the customizer
 * related to the Tainacan Items page - Collection banner settings.
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_tainacan_items_page_collection_banner') ) {
	
	function tainacan_interface_customize_register_tainacan_items_page_collection_banner( $wp_customize ) {
		
        /* If the Tainacan plugin is installed */
		if (defined ( 'TAINACAN_VERSION' ) && version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {
		
			/**
			 * Adds section to settings related to search control . ---------------------------------------------------------
			 */
			$wp_customize->add_section( 'tainacan_items_page_collection_banner', array(
				'title' 	  => __( 'Collection Header', 'tainacan-interface' ),
				'description' => __( 'Settings related to Tainacan items list collection header.', 'tainacan-interface' ),
				'panel'		  => 'tainacan_items_page',
				'priority' 	  => 160,
				'capability'  => 'edit_theme_options'
				) );

			/**
			 * Allows setting max heigth of collection banner ---------------------------------------------------------
			 */
			$wp_customize->add_setting( 'tainacan_collection_banner_max_height', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => 624,
				'transport'  => 'postMessage',
				'sanitize_callback'  => 'sanitize_text_field'
			) );
			$wp_customize->add_control( 'tainacan_collection_banner_max_height', array(
				'type' => 'number',
				'section' => 'tainacan_items_page_collection_banner',
				'label' => __( 'Collection banner image maximum height (px)', 'tainacan-interface' ),
				'input_attrs' => array(
					'min' => 142,
					'max' => 680,
					'step' => 1
				),
			) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_collection_banner_max_height', array(
				'selector' => '.page-header img',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
			) );

        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_items_page_collection_banner', 11 );
}