<?php

/**
 * Functions that register the options for the customizer
 * related to the Tainacan plugin Item single page - Document and attachment settings.
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_tainacan_single_item_page_document') ) {
	
	function tainacan_interface_customize_register_tainacan_single_item_page_document( $wp_customize ) {
		
        /* If the Tainacan plugin is installed */
		if (defined ( 'TAINACAN_VERSION' ) ) {
		
            /**
             * Adds section to control single items page documents section.
             */
            $wp_customize->add_section( 'tainacan_single_item_page_document', array(
                'title' 	  => __( 'Items document and attachments', 'tainacan-interface' ),
                'description' => __( 'Settings related to Tainacan single Items document and attachments sections.', 'tainacan-interface' ),
                'panel'		  => 'tainacan_single_item_page',
                'priority' 	  => 162,
                'capability'  => 'edit_theme_options'
                ) );

            if (version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {
            
                /**
                 * Adds option to display attachments and document as a gallery list.
                 */
                $wp_customize->add_setting( 'tainacan_single_item_gallery_mode', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => false,
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_gallery_mode', array(
                    'type' 	   	  => 'checkbox',
                    'priority' 	  => 0, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Show "Documents" section: Document and Attachments grouped in one slider.', 'tainacan-interface' ),
                    'description' => __( 'Toggle to show the document and attachments in the same list, a carousel with the current item on top.', 'tainacan-interface' )
                    ) );

                /**
                 * Disable gallery lightbox.
                 */
                $wp_customize->add_setting( 'tainacan_single_item_disable_gallery_lightbox', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => false,
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_disable_gallery_lightbox', array(
                    'type' 	   	  => 'checkbox',
                    'priority' 	  => 0, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Disable the gallery lightbox modal', 'tainacan-interface' ),
                    'description' => __( 'Toggle to not display a modal with the main slider when clicking in a gallery item of the "Documents" section.', 'tainacan-interface' )
                    ) );
            
                /**
                 * Adds options to display or not the document download button.
                 */
                $wp_customize->add_setting( 'tainacan_single_item_hide_download_document', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => false,
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_hide_download_document', array(
                    'type' 	   	  => 'checkbox',
                    'priority' 	  => 2, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Hide Document download button', 'tainacan-interface' ),
                    'description' => __( 'Toggle to never display a "Download" button when hovering the document.', 'tainacan-interface' )
                    ) );
            }

            /**
             * Adds option to configure Document section label.
             */
            $wp_customize->add_setting( 'tainacan_single_item_document_section_label', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => __( 'Document', 'tainacan-interface' ),
                'transport'  => 'postMessage',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_document_section_label', array(
                'type' 	   	  => 'text',
                'priority' 	  => 0, // Within the section.
                'section'  	  => 'tainacan_single_item_page_document',
                'label'    	  => __( 'Label for the "Document" section', 'tainacan-interface' ),
                'description' => __( 'Leave blank it for not displaying any label.', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_document_section_label', array(
                'selector' => '#single-item-document-label',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );

            /**
             * Adds option to configure Attachments section label.
             */
            $wp_customize->add_setting( 'tainacan_single_item_attachments_section_label', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => __( 'Attachments', 'tainacan-interface' ),
                'transport'  => 'postMessage',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_attachments_section_label', array(
                'type' 	   	  => 'text',
                'priority' 	  => 0, // Within the section.
                'section'  	  => 'tainacan_single_item_page_document',
                'label'    	  => __( 'Label for the "Attachments" section', 'tainacan-interface' ),
                'description' => __( 'Leave blank it for not displaying any label.', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_attachments_section_label', array(
                'selector' => '#single-item-attachments-label',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );
            
            /**
             * Adds option to configure Documents section label.
             */
            $wp_customize->add_setting( 'tainacan_single_item_documents_section_label', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => __( 'Documents', 'tainacan-interface' ),
                'transport'  => 'postMessage',
                'sanitize_callback'  => 'sanitize_text_field'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_documents_section_label', array(
                'type' 	   	  => 'text',
                'priority' 	  => 0, // Within the section.
                'section'  	  => 'tainacan_single_item_page_document',
                'label'    	  => __( 'Label for the "Documents" section', 'tainacan-interface' ),
                'description' => __( 'Section that labels Document and Attachments grouped if this option is enabled. Leave blank it for not displaying any label.', 'tainacan-interface' )
                ) );
            $wp_customize->selective_refresh->add_partial( 'tainacan_single_item_documents_section_label', array(
                'selector' => '#single-item-documents-label',
                'render_callback' => '__return_false',
                'fallback_refresh' => true
                ) );

            /**
             * Adds options to hide attachments file names on carousel.
             */
            $wp_customize->add_setting( 'tainacan_single_item_hide_files_name', array(
                'type' 		 => 'theme_mod',
                'capability' => 'edit_theme_options',
                'default' 	 => false,
                'transport'  => 'refresh',
                'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                ) );
            $wp_customize->add_control( 'tainacan_single_item_hide_files_name', array(
                'type' 	   	  => 'checkbox',
                'priority' 	  => 3, // Within the section.
                'section'  	  => 'tainacan_single_item_page_document',
                'label'    	  => __( 'Hide the attachments label (on carousel)', 'tainacan-interface' ),
                'description' => __( 'Toggle to not display the document and attachments name below its thumbnail.', 'tainacan-interface' )
                ) );

            if (version_compare(TAINACAN_VERSION, '0.18RC') >= 0) {

                /**
                 * Adds options to hide attachments file names on the main slider.
                 */
                $wp_customize->add_setting( 'tainacan_single_item_hide_files_name_main', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => true,
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_hide_files_name_main', array(
                    'type' 	   	  => 'checkbox',
                    'priority' 	  => 3, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Hide the attachments label (on the main slider)', 'tainacan-interface' ),
                    'description' => __( 'Toggle to not display the document and attachments name.', 'tainacan-interface' )
                    ) );

                /**
                 * Adds options to hide attachments file caption on the main slider.
                 */
                $wp_customize->add_setting( 'tainacan_single_item_hide_files_caption_main', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => true,
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_hide_files_caption_main', array(
                    'type' 	   	  => 'checkbox',
                    'priority' 	  => 3, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Hide the attachments caption (on the main slider)', 'tainacan-interface' ),
                    'description' => __( 'Toggle to not display the document and attachments caption.', 'tainacan-interface' )
                    ) );

                /**
                 * Adds options to hide attachments file description on the main slider.
                 */
                $wp_customize->add_setting( 'tainacan_single_item_hide_files_description_main', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => true,
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_hide_files_description_main', array(
                    'type' 	   	  => 'checkbox',
                    'priority' 	  => 3, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Hide the attachments description (on the main slider)', 'tainacan-interface' ),
                    'description' => __( 'Toggle to not display the document and attachments description.', 'tainacan-interface' )
                    ) );


                /**
                 * Light color palette to the media component gallery
                 */
                $wp_customize->add_setting( 'tainacan_single_item_gallery_color_scheme', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => 'dark',
                    'transport'  => 'refresh',
                    'sanitize_callback' => 'tainacan_sanitize_single_item_gallery_color_scheme_options',
                    ) );
                $wp_customize->add_control( 'tainacan_single_item_gallery_color_scheme', array(
                    'type' 	   	  => 'select',
                    'priority' 	  => 4, // Within the section.
                    'section'  	  => 'tainacan_single_item_page_document',
                    'label'    	  => __( 'Color scheme of the media gallery modal', 'tainacan-interface' ),
                    'choices'	  => tainacan_get_single_item_gallery_color_scheme_options()
                    ) );

                /**
                 * Allows setting max heigth for the document ---------------------------------------------------------
                 */
                $wp_customize->add_setting( 'tainacan_single_item_document_max_height', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => 60,
                    'transport'  => 'refresh',
                    'sanitize_callback'  => 'sanitize_text_field'
                ) );
                $wp_customize->add_control( 'tainacan_single_item_document_max_height', array(
                    'type' => 'number',
                    'priority' 	  => 2, // Within the section.
                    'section' => 'tainacan_single_item_page_document',
                    'label' => __( 'Document maximum height (vh)', 'tainacan-interface' ),
                    'description' => __( 'Set the maximum height for the document. The unit of measure is relative to the screen, for example: 60vh is 60% of the height of the browser window height.', 'tainacan-interface' ),
                    'input_attrs' => array(
                        'min' => 10,
                        'max' => 150,
                        'step' => 5
                    ),
                ) );

                /**
                 * Allows setting attachment carousel thumbnail size ---------------------------------------------------------
                 */
                $wp_customize->add_setting( 'tainacan_single_item_attachments_thumbnail_size', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => 136,
                    'transport'  => 'refresh',
                    'sanitize_callback'  => 'sanitize_text_field'
                ) );
                $wp_customize->add_control( 'tainacan_single_item_attachments_thumbnail_size', array(
                    'type' => 'number',
                    'priority' 	  => 2, // Within the section.
                    'section' => 'tainacan_single_item_page_document',
                    'label' => __( 'Attachment thumbnail size on carousel (px)', 'tainacan-interface' ),
                    'input_attrs' => array(
                        'min' => 12,
                        'max' => 240,
                        'step' => 2
                    ),
                ) );
            }
        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_single_item_page_document', 11 );
}


if ( ! function_exists( 'tainacan_get_single_item_gallery_color_scheme_options' ) ) :
	/**
	 * Retrieves an array of options for single item page gallery color scheme options for Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_single_item_gallery_color_scheme_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $option - a string with options for the color scheme.
	 */
	function tainacan_get_single_item_gallery_color_scheme_options() {
		$color_scheme = array(
			'dark' => __('Dark', 'tainacan-interface'),
			'light' => __('Light', 'tainacan-interface')
		);
		return $color_scheme;
	}
endif; // tainacan_get_single_item_gallery_color_scheme_options

if ( ! function_exists( 'tainacan_sanitize_single_item_gallery_color_scheme_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme item page gallery color scheme options for Tainacan Interface theme
	 *
	 * Create your own tainacan_sanitize_single_item_gallery_color_scheme_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $option - a string with options for the color scheme.
	 * @return string the selected option.
	 */
	function tainacan_sanitize_single_item_gallery_color_scheme_options( $option ) {
		$color_scheme = tainacan_get_single_item_gallery_color_scheme_options();

		if ( ! array_key_exists( $option, $color_scheme ) ) {
			return 'dark';
		}

		return $option;
	}
endif; // tainacan_sanitize_single_item_gallery_color_scheme_options


/**
 * Enqueues front-end CSS for the single item page document max-height.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_single_item_document_max_height_output() {
	$max_document_height = get_theme_mod( 'tainacan_single_item_document_max_height', 60 );

	// If the value is not a number, return early.
	if ( !is_numeric( $max_document_height )  ) {
		return;
	}

	$css = '
		/* Custom Settings for Single Item Page Document Height */
		.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--document img,
		.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--document video,
		.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--document audio {
			max-height: ' . $max_document_height . '%;
			max-height: ' . $max_document_height . 'vh;
		}
		.tainacan-single-post .tainacan-content.single-item-collection .single-item-collection--document,
		.tainacan-single-post .tainacan-content.single-item-collection .tainacan-media-component {
			--tainacan-media-main-carousel-height: ' . $max_document_height . '%;
			--tainacan-media-main-carousel-height: ' . $max_document_height . 'vh;
		}
	';
	
	echo '<style type="text/css" id="tainacan-style-document">' . $css . '</style>';
}
add_action( 'wp_head', 'tainacan_single_item_document_max_height_output');


/**
 * Enqueues front-end CSS for the single item page attachments carousel thumbnail size.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_single_item_attachments_thumbnail_size_output() {
	$thumbnail_size = get_theme_mod( 'tainacan_single_item_attachments_thumbnail_size', 136 );

	// If the value is not a number, return early.
	if ( !is_numeric( $thumbnail_size )  ) {
		return;
	}

	$css = '
		/* Custom Settings for Single Item Page Attachments Carousel thumbnail size */
	
		.tainacan-single-post .tainacan-content.single-item-collection .tainacan-media-component {
			--tainacan-media-thumbs-carousel-item-size: ' . $thumbnail_size . 'px;
		}
	';
	
	echo '<style type="text/css" id="tainacan-style-attachments">' . $css . '</style>';
}
add_action( 'wp_head', 'tainacan_single_item_attachments_thumbnail_size_output');


/**
 * Enqueues front-end CSS for the light scheme of the photoswipe layer
 */
if ( !function_exists('tainacan_gallery_light_color_scheme') ) {
	function tainacan_gallery_light_color_scheme() {

		$has_light_dark_color_scheme = get_theme_mod( 'tainacan_single_item_gallery_color_scheme', 'dark' ) == 'light';
		
		if (!$has_light_dark_color_scheme)
			return;
			
		$css = '
		/* Photoswipe layer for the gallery dark */
		.tainacan-photoswipe-layer .pswp__bg {
			background-color: rgba(255, 255, 255, 0.85) !important;
		}
		.tainacan-photoswipe-layer .pswp__ui .pswp__top-bar,
		.tainacan-photoswipe-layer .pswp__ui .pswp__caption {
			background-color: rgba(255, 255, 255, 0.7) !important;
		}
		.tainacan-photoswipe-layer .pswp__top-bar .pswp__name,
		.tainacan-photoswipe-layer .pswp__caption__center,
        .tainacan-photoswipe-layer .pswp__counter {
			color: black !important;
		}
		.tainacan-photoswipe-layer .pswp__button {
			filter: invert(100) !important;
		}
		.tainacan-photoswipe-layer .pswp--css_animation .pswp__preloader__donut {
			border: 2px solid #000000 !important;
		}
		';
		echo '<style type="text/css" id="tainacan-gallery-color-scheme">' . sprintf( $css ) . '</style>';

	}
}
add_action( 'wp_head', 'tainacan_gallery_light_color_scheme');