<?php

/**
 * Functions that register the options for the customizer
 * related to the Tainacan plugin Item single page - Related items settings.
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_tainacan_single_item_page_related_items') ) {
	
	function tainacan_interface_customize_register_tainacan_single_item_page_related_items( $wp_customize ) {
		
        /* If the Tainacan plugin is installed */
		if (defined ( 'TAINACAN_VERSION' ) && version_compare(TAINACAN_VERSION, '0.18RC') >= 0 ) {

			/**
			 * Adds section to control single item related items settings.
			 */
			$wp_customize->add_section( 'tainacan_single_item_page_related_items', array(
				'title' 	  => __( 'Items related to this', 'tainacan-interface' ),
				'description' => __( 'Settings for the section that displays items related to the current item.', 'tainacan-interface' ),
				'panel'		  => 'tainacan_single_item_page',
				'priority' 	  => 170,
				'capability'  => 'edit_theme_options'
				) );


			/**
			 * Adds option to configure Related Items section label.
			 */
			$wp_customize->add_setting( 'tainacan_single_item_related_items_section_label', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => __( 'Related items', 'tainacan-interface' ),
				'transport'  => 'postMessage',
				'sanitize_callback'  => 'sanitize_text_field'
				) );
			$wp_customize->add_control( 'tainacan_single_item_related_items_section_label', array(
				'type' 	   	  => 'text',
				'priority' 	  => 0, // Within the section.
				'section'  	  => 'tainacan_single_item_page_related_items',
				'label'    	  => __( 'Label for the "Items related to this" section', 'tainacan-interface' ),
				'description' => __( 'Leave blank it for not displaying any label.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_related_items_section_label', array(
				'selector' => '#single-item-related-items-label',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );

			/**
			 * Adds options to enable related items section.
			 */
			$wp_customize->add_setting( 'tainacan_single_item_enable_related_items_section', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => true,
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
				) );
			$wp_customize->add_control( 'tainacan_single_item_enable_related_items_section', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 9, // Within the section.
				'section'  	  => 'tainacan_single_item_page_related_items',
				'label'    	  => __( 'Enable the "Items related to this" section', 'tainacan-interface' ),
				'description' => __( 'Toggle to display or not the "Items related to this" section. This also depends on the collection settings and existence of items related to the current one.', 'tainacan-interface' )
				) );

			/**
			 * Allows setting max heigth for the document ---------------------------------------------------------
			 */
			$wp_customize->add_setting( 'tainacan_single_item_related_items_max_items_per_screen', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => 6,
				'transport'  => 'refresh',
				'sanitize_callback'  => 'sanitize_text_field'
			) );
			$wp_customize->add_control( 'tainacan_single_item_related_items_max_items_per_screen', array(
				'type' => 'number',
				'priority' 	  => 0, // Within the section.
				'section' => 'tainacan_single_item_page_related_items',
				'label' => __( 'Maximum number of slides per screen', 'tainacan-interface' ),
				'description' => __( 'Sets how many slides per row of the carousel will appear (on a large screen). The smaller this number is, the greater the item thumbnail will be and depending of the size, there might not exist a cropped version of the image.', 'tainacan-interface' ),
				'input_attrs' => array(
					'min' => 1,
					'max' => 10,
					'step' => 1
				),
			) );
        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_single_item_page_related_items', 11 );
}