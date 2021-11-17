<?php

/**
 * Functions that register the options for the customizer
 * related to the Tainacan plugin Item single page - Metadata list settings.
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_tainacan_single_item_page_metadata') ) {
	
	function tainacan_interface_customize_register_tainacan_single_item_page_metadata( $wp_customize ) {
		
        /* If the Tainacan plugin is installed */
		if (defined ( 'TAINACAN_VERSION' ) ) {

            /**
             * Adds section to control single items page metadata section.
             */
            $wp_customize->add_section( 'tainacan_single_item_page_metadata', array(
                'title' 	  => __( 'Items metadata', 'tainacan-interface' ),
                'description' => __( 'Settings related to Tainacan single Items metadata section.', 'tainacan-interface' ),
                'panel'		  => 'tainacan_single_item_page',
                'priority' 	  => 161,
                'capability'  => 'edit_theme_options'
                ) );

            /**
             * Adds option to configure Metadata section label.
             */
            $wp_customize->add_setting( 'tainacan_single_item_metadata_section_label', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => '',
                'transport'  => 'postMessage',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_metadata_section_label', array(
                'type' 	   	  => 'text',
                'priority' 	  => 0, // Within the section.
                'section'  	  => 'tainacan_single_item_page_metadata',
                'label'    	  => __( 'Label for the "Metadata" section', 'tainacan-interface' ),
                'description' => __( 'Leave blank it for not displaying any label (which is the default).', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_metadata_section_label', array(
                'selector' => '#single-item-metadata-label',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );

            /**
             * Adds options to display or not the thumbnail on items page.
             */
            $wp_customize->add_setting( 'tainacan_single_item_display_thumbnail', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => true,
                'transport'  => 'postMessage',
                'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_display_thumbnail', array(
                'type' 	   	  => 'checkbox',
                'priority' 	  => 2, // Within the section.
                'section'  	  => 'tainacan_single_item_page_metadata',
                'label'    	  => __( 'Display item thumbnail', 'tainacan-interface' ),
                'description' => __( 'Toggle to show or not the item thumbnail, within the metadata list section.', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_display_thumbnail', array(
                'selector' => '.tainacan-item-thumbnail-container',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );

            /**
             * Adds options to display or not share buttons on items page.
             */	
            $wp_customize->add_setting( 'tainacan_single_item_display_share_buttons', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => true,
                'transport'  => 'postMessage',
                'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_display_share_buttons', array(
                'type' 	   	  => 'checkbox',
                'priority' 	  => 3, // Within the section.
                'section'  	  => 'tainacan_single_item_page_metadata',
                'label'    	  => __( 'Display share buttons', 'tainacan-interface' ),
                'description' => __( 'Toggle to show or not the social icon share buttons, within the metadata list section or collection banner.', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_display_share_buttons', array(
                'selector' => '.tainacan-item-share-container',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );
            
            /**
             * Adds options to hide or no the core metadata in the metadata list.
             */
            $wp_customize->add_setting( 'tainacan_single_item_hide_core_title_metadata', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => false,
                'transport'  => 'postMessage',
                'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_hide_core_title_metadata', array(
                'type' 	   	  => 'checkbox',
                'priority' 	  => 2, // Within the section.
                'section'  	  => 'tainacan_single_item_page_metadata',
                'label'    	  => __( 'Hide core title from metadata list', 'tainacan-interface' ),
                'description' => __( 'Toggle to hide or not the core title from the metadata list, as it already appears on the page title.', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_hide_core_title_metadata', array(
                'selector' => '.metadata-type-core_title',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );

            /**
             * Adds options to control single items page number of metadata columns.
             */
            $wp_customize->add_setting( 'tainacan_single_item_metadata_columns_count_tablet', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => '2',
                'transport'  => 'refresh',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_metadata_columns_count_tablet', array(
                'type' 	   	  => 'number',
                'priority' 	  => 4, // Within the section.
                'section'  	  => 'tainacan_single_item_page_metadata',
                'label'    	  => __( 'Number of metadata columns (tablet)', 'tainacan-interface' ),
                'description' => __( 'Choose how many metadata columns should be listed, for screen sizes between 728px and 1024px.', 'tainacan-interface' ),
                'input_attrs' => array(
                    'placeholder' => '2',
                    'min' => 1,
                    'max' => 3,
                    'step' => 1
                )
                ) );
            $wp_customize->add_setting( 'tainacan_single_item_metadata_columns_count_desktop', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => '3',
                'transport'  => 'postMessage',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_metadata_columns_count_desktop', array(
                'type' 		  => 'number',
                'priority'    => 5, // Within the section.
                'section' 	  => 'tainacan_single_item_page_metadata',
                'label' 	  => __( 'Number of metadata columns (desktop)', 'tainacan-interface' ),
                'description' => __( 'For screen sizes between 1025px and 1366px.', 'tainacan-interface' ),
                'input_attrs' => array(
                    'placeholder' => '3',
                    'min' => 1,
                    'max' => 3,
                    'step' => 1
                )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_metadata_columns_count_desktop', array(
                'selector' => '.single-item-collection--information .row',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );
            $wp_customize->add_setting( 'tainacan_single_item_metadata_columns_count_wide', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => '3',
                'transport'  => 'refresh',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_metadata_columns_count_wide', array(
                'type' 		  => 'number',
                'priority' 	  => 6, // Within the section.
                'section' 	  => 'tainacan_single_item_page_metadata',
                'label' 	  => __( 'Number of metadata columns (wide)', 'tainacan-interface' ),
                'description' => __( 'For screens larger than 1366px.', 'tainacan-interface' ),
                'input_attrs' => array(
                        'placeholder' => '3',
                        'min' => 1,
                        'max' => 4,
                        'step' => 1
                    )
                ) );
        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_single_item_page_metadata', 11 );
}


/**
 * Enqueues front-end CSS for the single item page metadata columns.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_single_item_metadata_columns_count_output() {
	$metadata_columns_count_tablet  = get_theme_mod( 'tainacan_single_item_metadata_columns_count_tablet', '2' );
	$metadata_columns_count_desktop = get_theme_mod( 'tainacan_single_item_metadata_columns_count_desktop', '2' );
	$metadata_columns_count_wide 	= get_theme_mod( 'tainacan_single_item_metadata_columns_count_wide', '3' );

	// If the value is not a number, return early.
	if ( !is_numeric( $metadata_columns_count_tablet ) || !is_numeric( $metadata_columns_count_desktop ) || !is_numeric( $metadata_columns_count_wide )  ) {
		return;
	}

	$css = '
		/* Custom Settings for Single Item Page Metadata Columns Count */
		
		@media only screen and (max-width: 768px) { 
			.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--information .s-item-collection--metadata {			
				-moz-column-count: 1 !important;
				-webkit-column-count: 1 !important;
				column-count: 1 !important;
			}
		}
		@media only screen and (min-width: 769px) and (max-width: 1024px) { 
			.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--information .s-item-collection--metadata {			
				-moz-column-count: %1$s !important;
				-webkit-column-count: %1$s !important;
				column-count: %1$s !important;
			}
		}
		@media only screen and (min-width: 1025px) and (max-width: 1366px) {
			.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--information .s-item-collection--metadata {			
				-moz-column-count: %2$s !important;
				-webkit-column-count: %2$s !important;
				column-count: %2$s !important;
			}
		}
		@media only screen and (min-width: 1367px) {
			.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--information .s-item-collection--metadata {			
				-moz-column-count: %3$s !important;
				-webkit-column-count: %3$s !important;
				column-count: %3$s !important;
			}
		}
	';
	
	echo '<style type="text/css" id="tainacan-style-metadata">' . sprintf( $css, $metadata_columns_count_tablet, $metadata_columns_count_desktop, $metadata_columns_count_wide ) . '</style>';
}
add_action( 'wp_head', 'tainacan_single_item_metadata_columns_count_output');
