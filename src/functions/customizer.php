<?php
/**
 * Customizer functionality
 */

/**
 * Register Customizer color.
 *
 * @since Tainacan Theme
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function tainacan_customize_register( $wp_customize ) {
	$color_scheme = tainacan_get_color_scheme();

	/**
	 * Add others infos in Site identity on footer
	 */
	$wp_customize->add_section('tainacan_footer_info', array(
		'title'  	 => __( 'Footer settings', 'tainacan-interface' ),
		'priority'   => 200,
	));
	$wp_customize->add_setting( 'tainacan_blogaddress', array(
		'type'       => 'theme_mod',
		'capability' => 'manage_options',
		'sanitize_callback'  => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'tainacan_blogaddress', array(
		'type'       => 'theme_mod',
		'label'      => __( 'Address', 'tainacan-interface' ),
		'section'    => 'tainacan_footer_info',
	) );
	$wp_customize->add_setting( 'tainacan_blogphone', array(
		'type'       => 'theme_mod',
		'capability' => 'manage_options',
		'sanitize_callback'  => 'tainacan_sanitize_phone',
	) );

	$wp_customize->add_control( 'tainacan_blogphone', array(
		'type'       => 'theme_mod',
		'label'      => __( 'Phone Number', 'tainacan-interface' ),
		'section'    => 'tainacan_footer_info',
	) );

	$wp_customize->add_setting( 'tainacan_blogemail', array(
		'type'       => 'theme_mod',
		'capability' => 'manage_options',
		'sanitize_callback'  => 'tainacan_sanitize_email',
	) );

	$wp_customize->add_control( 'tainacan_blogemail', array(
		'type'       => 'theme_mod',
		'label'      => __( 'E-mail', 'tainacan-interface' ),
		'section'    => 'tainacan_footer_info',
	) );

	/**
	 * Footer Logo customizer
	 */
	$wp_customize->add_setting( 'tainacan_footer_logo', array(
		'type'       => 'theme_mod',
		'capability' => 'manage_options',
		'sanitize_callback' => 'tainacan_sanitize_upload',
	) );

	$wp_customize->add_control(
		new WP_Customize_Image_Control($wp_customize, 'tainacan_footer_logo',
			array(
			   'label'      => __( 'Upload a logo to the footer', 'tainacan-interface' ),
			   'section'    => 'tainacan_footer_info',
			   'settings'   => 'tainacan_footer_logo',
			)
		)
	);

	/**
	 * Checkbox to display or no the Proudly Powered by WordPress and Tainacan.
	 */
	$wp_customize->add_setting( 'tainacan_display_powered', array(
		'type'       => 'theme_mod',
		'default'        => true,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_display_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_display_powered', array(
		'type' => 'checkbox',
		'settings' => 'tainacan_display_powered',
		'section' => 'tainacan_footer_info',
		'label' => __( 'Display "Proudly Powered by..."', 'tainacan-interface' ),
		'description' => __( 'This checkbox shows the "Proudly Powered by Tainacan and Wordpress" sentence.', 'tainacan-interface' ),
	) );

	/**
	 * Social Share Links
	 */

	$wp_customize->add_section('tainacan_social_share', array(
		'title'  	 => __( 'Social Share', 'tainacan-interface' ),
		'priority'   => 200,
	));

	//Facebook
	$wp_customize->add_setting( 'tainacan_facebook_share', array(
		'type'       => 'theme_mod',
		'default'        => true,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_display_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_facebook_share', array(
		'type' => 'checkbox',
		'settings' => 'tainacan_facebook_share',
		'section' => 'tainacan_social_share',
		'label' => __( 'Display Facebook button', 'tainacan-interface' ),
	) );

	//Twitter
	$wp_customize->add_setting( 'tainacan_twitter_share', array(
		'type'       => 'theme_mod',
		'default'        => true,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_display_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_twitter_share', array(
		'type' => 'checkbox',
		'settings' => 'tainacan_twitter_share',
		'section' => 'tainacan_social_share',
		'label' => __( 'Display Twitter button', 'tainacan-interface' ),
	) );

	$wp_customize->add_setting( 'tainacan_twitter_user', array(
		'type'       => 'theme_mod',
		'capability' => 'manage_options',
		'sanitize_callback'  => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'tainacan_twitter_user', array(
		'label'      => __( 'Twitter User to be cited in tweets (via @user)', 'tainacan-interface' ),
		'section'    => 'tainacan_social_share',
	) );

	/**
	 * Add color scheme setting and control.
	 */
	$wp_customize->add_setting( 'tainacan_color_scheme', array(
		'type'       		=> 'theme_mod',
		'default'           => 'default',
		'sanitize_callback' => 'tainacan_sanitize_color_scheme',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'tainacan_color_scheme', array(
		'label'    => __( 'Choose a Color Scheme', 'tainacan-interface' ),
		'section'  => 'colors',
		'type'     => 'select',
		'choices'  => tainacan_get_color_scheme_choices(),
		'priority' => 1,
	) );

	/**
	 * Remove the core header textcolor control, as it shares the main text color.
	 */
	$wp_customize->remove_control( 'header_textcolor' );

	/**
	 * Add link color setting and control.
	 */
	$wp_customize->add_setting( 'tainacan_link_color', array(
		'type'       => 'theme_mod',
		'default'           => $color_scheme[2],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tainacan_link_color', array(
		'label'       => __( 'Or pick any color', 'tainacan-interface' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'tainacan_tooltip_color', array(
		'type'       => 'theme_mod',
		'default'           => $color_scheme[3],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tainacan_tooltip_color', array(
		'label'       => __( 'Tooltip Color', 'tainacan-interface' ),
		'section'     => 'colors',
	) ) );

	/**
	 * Adds option to hide Website Title on the Header Image cover, or the whole banner.
	 */
	$wp_customize->add_setting( 'tainacan_hide_site_title_on_header_banner', array(
		'type' 		 => 'theme_mod',
		'capability' => 'edit_theme_options',
		'default' 	 => false,
		'transport'  => 'refresh'
		) );
	$wp_customize->add_control( 'tainacan_hide_site_title_on_header_banner', array(
		'type' 	   	  => 'checkbox',
		'priority' 	  => 98, // Within the section.
		'section'  	  => 'header_image',
		'label'    	  => __( 'Hide the header banner site title', 'tainacan-interface' ),
		'description' => __( 'Toggle to hide the site title row that appears over the header banner', 'tainacan-interface' )
		) );
	$wp_customize->selective_refresh->add_partial( 'tainacan_hide_site_title_on_header_banner', array(
			'selector' => '.page-header h1.text-truncate',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
		) );
	$wp_customize->add_setting( 'tainacan_hide_header_banner', array(
		'type' 		 => 'theme_mod',
		'capability' => 'edit_theme_options',
		'default' 	 => false,
		'transport'  => 'refresh'
		) );
	$wp_customize->add_control( 'tainacan_hide_header_banner', array(
		'type' 	   	  => 'checkbox',
		'priority' 	  => 99, // Within the section.
		'section'  	  => 'header_image',
		'label'    	  => __( 'Hide the header banner completely', 'tainacan-interface' ),
		'description' => __( 'Toggle to hide the header banner from all pages of the site', 'tainacan-interface' )
		) );
	$wp_customize->add_setting( 'tainacan_hide_header_box_opacity', array(
		'type' 		 => 'theme_mod',
		'capability' => 'edit_theme_options',
		'default' 	 => 60,
		'transport'  => 'postMessage'
	) );
	$wp_customize->add_control( 'tainacan_hide_header_box_opacity', array(
		'type' => 'number',
		'section' => 'header_image',
		'label' => __( 'Title box opacity (%)' ),
		'description' => __( 'Change the opacity of the white box that holds the banner site title' ),
		'input_attrs' => array(
			'min' => 0,
			'max' => 100,
			'step' => 5
		),
	) );
	$wp_customize->selective_refresh->add_partial( 'tainacan_hide_header_box_opacity', array(
		'selector' => '.page-header .ph-title-description',
		'render_callback' => '__return_false',
		'fallback_refresh' => true
	) );

	/**
	 * Bellow are customizer options exclusivelly related to Tainacan pages.
	 */
	if ( defined ( 'TAINACAN_VERSION' ) ) {
		
		/**
		 * Adds section to control singe items page.
		 */
		$wp_customize->add_section( 'tainacan_single_item_page', array(
			'title' 	  => __( 'Tainacan single item page', 'tainacan-interface' ),
			'description' => __( 'Settings related to Tainacan single Items page only.', 'tainacan-interface' ),
			'priority' 	  => 160,
			'capability'  => 'edit_theme_options'
			) );

		/**
		 * Adds option to change the order of some page sections
		 */
		$wp_customize->add_setting( 'tainacan_single_item_layout_sections_order', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 'document-attachments-metadata',
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_layout_sections_order', array(
			'type' 	   	  => 'select',
			'priority' 	  => -1, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Layout sections order.', 'tainacan-interface' ),
			'description' => __( 'Display the document, attachments and metadata sections in different order.', 'tainacan-interface' ),
			'choices'	  => array(
				'document-attachments-metadata' => __('Document - Attachments - Metadata', 'tainacan-interface'),
				'metadata-document-attachments' => __('Metadata - Document - Attachments', 'tainacan-interface'),
				'document-metadata-attachments' => __('Document - Metadata - Attachments', 'tainacan-interface'),
			)
			) );

		/**
		 * Adds option to display Collection banner on the item single page.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_collection_header', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => false,
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_collection_header', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Display a header of the related collection.', 'tainacan-interface' ),
			'description' => __( 'Toggle to show a banner with name, thumbnail and color of the related collection.', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_collection_header', array(
			'selector' => '.tainacan-single-item-heading',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
			) );

		/**
		 * Adds option to display attachments and document as a gallery list.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_gallery_mode', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => false,
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_gallery_mode', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Show "Documents" section: Document and Attachments grouped in one slider.', 'tainacan-interface' ),
			'description' => __( 'Toggle to show the document and attachments in the same list, a carousel with the current item on top.', 'tainacan-interface' )
			) );

		/**
		 * Adds option to configure Document section label.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_document_section_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __( 'Document', 'tainacan-interface' ),
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_document_section_label', array(
			'type' 	   	  => 'text',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
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
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_attachments_section_label', array(
			'type' 	   	  => 'text',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
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
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_documents_section_label', array(
			'type' 	   	  => 'text',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Label for the "Documents" section', 'tainacan-interface' ),
			'description' => __( 'Section that labels Document and Attachments grouped if this option is enabled. Leave blank it for not displaying any label.', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_documents_section_label', array(
			'selector' => '#single-item-documents-label',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
			) );

		/**
		 * Adds option to configure Metadata section label.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_metadata_section_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => '',
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_metadata_section_label', array(
			'type' 	   	  => 'text',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Label for the "Metadata" section', 'tainacan-interface' ),
			'description' => __( 'Leave blank it for not displaying any label (which is the default).', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_metadata_section_label', array(
			'selector' => '#single-item-metadata-label',
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
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_hide_files_name', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 3, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Hide the attchments label', 'tainacan-interface' ),
			'description' => __( 'Toggle to not display the document and attachments name below its thumbnail.', 'tainacan-interface' )
			) );

		/**
		 * Adds options to display item author and publish date.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_hide_item_meta', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => false,
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_hide_item_meta', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 2, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Hide the item publish date and author', 'tainacan-interface' ),
			'description' => __( 'Toggle to not display the item publish date and author name below the item title.', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_hide_item_meta', array(
			'selector' => '.tainacan-single-post .header-meta .time',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
			) );

		/**
		 * Adds options to display or not the document download button.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_hide_download_document', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => false,
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_hide_download_document', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 2, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Hide Document download button', 'tainacan-interface' ),
			'description' => __( 'Toggle to never display a "Download" button when hovering the document.', 'tainacan-interface' )
			) );

		/**
		 * Adds options to display or not the thumbnail on items page.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_display_thumbnail', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => true,
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_display_thumbnail', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 2, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
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
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_display_share_buttons', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 3, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Display share buttons', 'tainacan-interface' ),
			'description' => __( 'Toggle to show or not the social icon share buttons, within the metadata list section or collection banner.', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_display_share_buttons', array(
			'selector' => '.tainacan-item-share-container',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
			) );

		/**
		 * Adds options to control singe items page number of metadata columns.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_metadata_columns_count_tablet', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => '2',
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_metadata_columns_count_tablet', array(
			'type' 	   	  => 'number',
			'priority' 	  => 4, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Number of metadata columns (tablet)', 'tainacan-interface' ),
			'description' => __( 'Choose how many metadata columns should be listed, for screen sizes between 728px and 1024px.', 'tainacan-interface' ),
			'input_attrs' => array(
				'placeholder' => __( '2' ),
				'min' => 1,
				'max' => 3,
				'step' => 1
			)
			) );
		$wp_customize->add_setting( 'tainacan_single_item_metadata_columns_count_desktop', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => '3',
			'transport'  => 'postMessage'
			) );
		$wp_customize->add_control( 'tainacan_single_item_metadata_columns_count_desktop', array(
			'type' 		  => 'number',
			'priority'    => 5, // Within the section.
			'section' 	  => 'tainacan_single_item_page',
			'label' 	  => __( 'Number of metadata columns (desktop)', 'tainacan-interface' ),
			'description' => __( 'For screen sizes between 1025px and 1366px.', 'tainacan-interface' ),
			'input_attrs' => array(
				'placeholder' => __( '3' ),
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
			'transport'  => 'refresh'
			) );
		$wp_customize->add_control( 'tainacan_single_item_metadata_columns_count_wide', array(
			'type' 		  => 'number',
			'priority' 	  => 6, // Within the section.
			'section' 	  => 'tainacan_single_item_page',
			'label' 	  => __( 'Number of metadata columns (wide)', 'tainacan-interface' ),
			'description' => __( 'For screens larger than 1366px.', 'tainacan-interface' ),
			'input_attrs' => array(
				'placeholder' => __( '3' ),
				'min' => 1,
				'max' => 4,
				'step' => 1
			)
			) );

		if (version_compare(TAINACAN_VERSION, '0.16') <= 0) {

			/**
			 * Adds section to control collection items page. ---------------------------------------------------------
			 */
			$wp_customize->add_panel( 'tainacan_items_page', array(
				'title' 	  => __( 'Tainacan items list page', 'tainacan-interface' ),
				'description' => __( 'Settings related to Tainacan items list pages, such as the repository items list, the colleciton item list and the term items list. Some settings ins this section may be overrided by collection settings or user preference.', 'tainacan-interface' ), 
				'priority' 	  => 160 // Mixed with top-level-section hierarchy.,
			) );
				
			/**
			 * Adds section to settings related to search control . ---------------------------------------------------------
			 */
			$wp_customize->add_section( 'tainacan_items_page_search_area', array(
				'title' 	  => __( 'Search control area', 'tainacan-interface' ),
				'description' => __( 'Settings related to Tainacan items list search control area. This is the bar over the items list container.', 'tainacan-interface' ),
				'panel'		  => 'tainacan_items_page',
				'priority' 	  => 161,
				'capability'  => 'edit_theme_options'
				) );

			/**
			 * Adds option to select default view modes for terms and repository items list.
			 */
			$view_modes = tainacan_get_default_view_mode_choices();

			$wp_customize->add_setting( 'tainacan_items_repository_list_default_view_mode', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => $view_modes['default_view_mode'],
				'transport'  => 'postMessage'
				) );	
			$wp_customize->add_control( 'tainacan_items_repository_list_default_view_mode', array(
				'type' 	   	  => 'select',
				'priority' 	  => -1, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Default view mode for Terms and Repository Items list.', 'tainacan-interface' ),
				'description' => __( 'Select a default view mode for Terms and Repository Items list.', 'tainacan-interface' ),
				'choices'	  => $view_modes['enabled_view_modes']
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_repository_list_default_view_mode', array(
				'selector' => '.repository-level-page #items-list-results',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );
			
			
			/**
			 * Adds option to hide search control on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_search', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'postMessage'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_search', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 3, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the Search block.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the  on the search control bar.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_page_hide_search', array(
				'selector' => '.theme-items-list .search-area',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );
			
			/**
			 * Adds option to hide advanced search link on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_advanced_search', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'postMessage'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_advanced_search', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 3, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the Advanced Search link.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the on the advanced search link on the control bar.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_page_hide_advanced_search', array(
				'selector' => '.theme-items-list .search-area a.has-text-secondary ',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );

			/**
			 * Adds option to hide the "sort by" button on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_sort_by_button', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'postMessage'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_sort_by_button', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 4, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the "Sort by" button.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the "Sort by" button on the control bar.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_page_hide_sort_by_button', array(
				'selector' => '.theme-items-list #tainacanSortByDropdown ',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );

			/**
			 * Adds option to hide the "View as..." button on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_exposers_button', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'postMessage'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_exposers_button', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 5, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the "View as..." button.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the "View as..." button, also refered to as "Exposers modal" on the control bar.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_page_hide_exposers_button', array(
				'selector' => '.theme-items-list #tainacanExposersButton ',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );

			/**
			 * Adds option to show inline view mode options on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_show_inline_view_mode_options', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'postMessage'
				) );
			$wp_customize->add_control( 'tainacan_items_page_show_inline_view_mode_options', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 11, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Show inline view mode options.', 'tainacan-interface' ),
				'description' => __( 'Toggle to show view mode options as a group of buttons instead of a dropdown.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_page_show_inline_view_mode_options', array(
				'selector' => '.theme-items-list #tainacanViewModesSection',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );

			/**
			 * Adds option to shows fullscreen with other view modes on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_show_fullscreen_with_view_modes', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_show_fullscreen_with_view_modes', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 12, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Show "fullscreen" view modes with other view modes.', 'tainacan-interface' ),
				'description' => __( 'Toggle to show "fullscreen" view modes with other view mode options instead of separate in the search control bar.', 'tainacan-interface' )
				) );
			$wp_customize->selective_refresh->add_partial( 'tainacan_items_page_show_fullscreen_with_view_modes', array(
				'selector' => '.theme-items-list #tainacanFullScreenViewMode',
				'render_callback' => '__return_false',
				'fallback_refresh' => true
				) );

			/**
			 * Adds section to settings related to filters panel . ---------------------------------------------------------
			 */
			$wp_customize->add_section( 'tainacan_items_page_filters_panel', array(
				'title' 	  => __( 'Filters panel', 'tainacan-interface' ),
				'description' => __( 'Settings related to Tainacan items list filters panel.', 'tainacan-interface' ),
				'panel'		  => 'tainacan_items_page',
				'priority' 	  => 161,
				'capability'  => 'edit_theme_options'
				) );

			/**
			 * Adds option to hide filters panel on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_filters', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_filters', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 0, // Within the section.
				'section'  	  => 'tainacan_items_page_filters_panel',
				'label'    	  => __( 'Hide the filters panel.', 'tainacan-interface' ),
				'description' => __( 'Toggle to hide the filters panel completely.', 'tainacan-interface' )
				) );

			/**
			 * Adds option to hide the "hide filters" button on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_hide_filters_button', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_hide_filters_button', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 1, // Within the section.
				'section'  	  => 'tainacan_items_page_filters_panel',
				'label'    	  => __( 'Hide the "Hide filters" button.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the "Hide filters" button for users.', 'tainacan-interface' )
				) );

			/**
			 * Adds option to show filters button inside the search control bar on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_show_filters_button_inside_search_control', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_show_filters_button_inside_search_control', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 8, // Within the section.
				'section'  	  => 'tainacan_items_page_filters_panel',
				'label'    	  => __( 'Show Filters button inside the search control bar.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do display the Filters button inside the search control bar.', 'tainacan-interface' )
				) );
				
			/**
			 * Adds option to start filters hidden by default on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_start_with_filters_hidden', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_start_with_filters_hidden', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 9, // Within the section.
				'section'  	  => 'tainacan_items_page_filters_panel',
				'label'    	  => __( 'Start with filters hidden.', 'tainacan-interface' ),
				'description' => __( 'Toggle to make filters start hidden by default.', 'tainacan-interface' )
				) );

			/**
			 * Adds option to display filters as a modal on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_filters_as_modal', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_filters_as_modal', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 10, // Within the section.
				'section'  	  => 'tainacan_items_page_filters_panel',
				'label'    	  => __( 'Filters as modal.', 'tainacan-interface' ),
				'description' => __( 'Toggle to make filters load inside a modal instead of a side panel.', 'tainacan-interface' )
				) );

			/**
			 * Adds section to settings related to pagination section . ---------------------------------------------------------
			 */
			$wp_customize->add_section( 'tainacan_items_page_pagination', array(
				'title' 	  => __( 'Pagination section', 'tainacan-interface' ),
				'description' => __( 'Settings related to Tainacan items list pagination section.', 'tainacan-interface' ),
				'panel'		  => 'tainacan_items_page',
				'priority' 	  => 161,
				'capability'  => 'edit_theme_options'
				) );

			/**
			 * Adds option to change default value of items per page.
			 */
			global $TAINACAN_API_MAX_ITEMS_PER_PAGE;
		
			$wp_customize->add_setting( 'tainacan_items_page_default_items_per_page', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => 12,
				'transport'  => 'refresh'
			) );
			$wp_customize->add_control( 'tainacan_items_page_default_items_per_page', array(
				'type' => 'number',
				'section' => 'tainacan_items_page_pagination',
				'label' => __( 'Default number of items per page' ),
				'description' => __( 'Change the default value for items loaded per page. Note that this affects loading duration.' ),
				'input_attrs' => array(
					'min' => 1,
					'max' => $TAINACAN_API_MAX_ITEMS_PER_PAGE,
					'step' => 1
				),
			) );

			/**
			 * Adds option to hide the "Items per Page" button on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_items_per_page_button', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_items_per_page_button', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 6, // Within the section.
				'section'  	  => 'tainacan_items_page_pagination',
				'label'    	  => __( 'Hide the "Items per Page" button.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the "Items per Page" button on the pagination bar.', 'tainacan-interface' )
				) );
			
			/**
			 * Adds option to hide the "Got to Page" button on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_hide_go_to_page_button', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_go_to_page_button', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 7, // Within the section.
				'section'  	  => 'tainacan_items_page_pagination',
				'label'    	  => __( 'Hide the "Go to Page" button.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the "Go to Page" button on the pagination bar.', 'tainacan-interface' )
				) );
		}
	}
}
add_action( 'customize_register', 'tainacan_customize_register', 11 );

/**
 * Callback to Checkbox to display or no the
 * Proudly Powered by Wordpress and Tainacan.
 */
function tainacan_display_callback_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

/**
 * Email sanitization callback.
 *
 * - Sanitization: email
 * - Control: text
 *
 * @param string               $email   Email address to sanitize.
 * @param WP_Customize_Setting $setting Setting instance.
 * @return string The sanitized email if not null; otherwise, the setting default.
 */
function tainacan_sanitize_email( $email, $setting ) {
	// Strips out all characters that are not allowable in an email address.
	$email = sanitize_email( $email );

	// If $email is a valid email, return it; otherwise, return the default.
	return ( ! is_null( $email ) ? $email : $setting->default );
}

/**
 * Phone number sanitization callback.
 *
 * - Sanitization: number
 * - Control: text
 *
 * @param string               $phone   Phone to sanitize.
 * @return string The sanitized phone if the number is <= 18; otherwise, the setting default.
 */
function tainacan_sanitize_phone( $phone ) {
	// Replace out all characters that are not allowable in an phone number.
	$phone = preg_replace( '/[^0-9 \\-\\(\\)\\+\\/]/', '', $phone );

	// If $phone is a valid number and <= 18, return it; otherwise, ''.
	return ( strlen( $phone ) <= 18 ? $phone : '' );
}

/**
 * Tainacan Upload sanitization callback.
 *
 * - Sanitization: upload
 * - Control: file
 *
 */
function tainacan_sanitize_upload( $input ) {

	/* default output */
	$output = '';

	/* check file type */
	$filetype = wp_check_filetype( $input );
	$mime_type = $filetype['type'];

	/* only mime type "image" allowed */
	if ( strpos( $mime_type, 'image' ) !== false ) {
		$output = $input;
	}

	return $output;
}

/**
 * Registers color schemes for Tainacan Theme.
 *
 * Can be filtered with {@see 'tainacan_color_schemes'}.
 *
 * The order of colors in a colors array:
 * 1. Main Background Color.
 * 2. Page Background Color.
 * 3. Link Color.
 * 4. Tooltip.
 *
 * @since Tainacan Theme
 *
 * @return array An associative array of color scheme options.
 */
function tainacan_get_color_schemes() {
	/**
	 * Filter the color schemes registered for use with Tainacan Theme.
	 *
	 * The default schemes include 'default', 'dark', 'gray', 'red', and 'yellow'.
	 *
	 * @since Tainacan Theme
	 *
	 * @param array $schemes {
	 *     Associative array of color schemes data.
	 *
	 *     @type array $slug {
	 *         Associative array of information for setting up the color scheme.
	 *
	 *         @type string $label  Color scheme label.
	 *         @type array  $colors HEX codes for default colors prepended with a hash symbol ('#').
	 *                              Colors are defined in the following order: Main background, page
	 *                              background, link, main text, secondary text.
	 *     }
	 * }
	 */
	return apply_filters( 'tainacan_color_schemes', array(
		'default' => array(
			'label'  => __( 'Default', 'tainacan-interface' ),
			'colors' => array(
				'#1a1a1a', //background
				'#ffffff', //background page
				'#298596', //link
				'#e6f6f8', //tooltip
			),
		),
		'carmine' => array(
			'label'  => __( 'Carmine', 'tainacan-interface' ),
			'colors' => array(
				'#262626', //background
				'#ffffff', //background page
				'#8c442c', //link
				'#e6d3cd', //tooltip
			),
		),
		'cherry' => array(
			'label'  => __( 'Cherry', 'tainacan-interface' ),
			'colors' => array(
				'#616a73', //background
				'#ffffff', //background page
				'#A12B42', //link
				'#e9cbd1', //tooltip
			),
		),
		'mustard' => array(
			'label'  => __( 'Mustard', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#754E24', //link
				'#f0e1cf', //tooltip
			),
		),
		'mintgreen' => array(
			'label'  => __( 'Mint Green', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#255F56', //link
				'#d4efe9', //tooltip
			),
		),
		'darkturquoise' => array(
			'label'  => __( 'Dark Turquoise', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#205E6F', //link
				'#cbe0e5', //tooltip
			),
		),
		'turquoise' => array(
			'label'  => __( 'Turquoise', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#185F6D', //link
				'#cdecef', //tooltip
			),
		),
		'blueheavenly' => array(
			'label'  => __( 'Blue Heavenly', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#1D5C86', //link
				'#d3e6f2', //tooltip
			),
		),
		'purple' => array(
			'label'  => __( 'Purple', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#4751a3', //link
				'#d1d4e7', //tooltip
			),
		),
		'violet' => array(
			'label'  => __( 'Violet', 'tainacan-interface' ),
			'colors' => array(
				'#ffffff', //background
				'#ffffff', //background page
				'#955ba5', //link
				'#e4d7e8', //tooltip
			),
		),
	) );
}

/**
 * Retrieves the current registered view modes on Tainacan plugin and filter some options to offer as default
 *
 * @since Tainacan Theme
 *
 * @return array An associative array with view mode options and the default one
 */
function tainacan_get_default_view_mode_choices () {
	$default_view_mode = '';
	$enabled_view_modes = [];

	if (function_exists('tainacan_get_the_view_modes')) {
		$view_modes = tainacan_get_the_view_modes();
		$default_view_mode = $view_modes['default_view_mode'];
		$enabled_view_modes = [];
		
		foreach ($view_modes['registered_view_modes'] as $key => $view_mode) {
			if (!$view_mode['full_screen'])
				$enabled_view_modes[$key] = $view_mode['label'];
		}
	} else {
		$default_view_mode = 'masonry';
		$enabled_view_modes = [
			'masonry' => __('Masonry', 'tainacan-interface'),
			'cards' => __('Cards', 'tainacan-interface'),
			'table' => __('Table', 'tainacan-interface'),
			'grid' => __('Grid', 'tainacan-interface')
		];
	}
	return [
		'default_view_mode' => $default_view_mode,
		'enabled_view_modes' => $enabled_view_modes
	];
}

if ( ! function_exists( 'tainacan_get_color_scheme' ) ) :
	/**
	 * Retrieves the current Tainacan Theme color scheme.
	 *
	 * Create your own tainacan_get_color_scheme() function to override in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @return array An associative array of either the current or default color scheme HEX values.
	 */
	function tainacan_get_color_scheme() {
		$color_scheme_option = get_theme_mod( 'tainacan_color_scheme', 'default' );
		$link_color = get_theme_mod( 'tainacan_link_color', 'default' ); // sanitized upon save
		$tooltip_color = get_theme_mod( 'tainacan_tooltip_color', 'default' ); // sanitized upon save
		$color_schemes       = tainacan_get_color_schemes();

		if ( array_key_exists( $color_scheme_option, $color_schemes ) ) {
			$return = $color_schemes[ $color_scheme_option ]['colors'];
		}

		$return = $color_schemes['default']['colors'];
		$return[2] = $link_color; // override link color with the one from color picker
		$return[3] = $tooltip_color;
		return $return;

	}
endif; // tainacan_get_color_scheme

if ( ! function_exists( 'tainacan_get_color_scheme_choices' ) ) :
	/**
	 * Retrieves an array of color scheme choices registered for Tainacan Theme.
	 *
	 * Create your own tainacan_get_color_scheme_choices() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @return array Array of color schemes.
	 */
	function tainacan_get_color_scheme_choices() {
		$color_schemes                = tainacan_get_color_schemes();
		$color_scheme_control_options = array();

		foreach ( $color_schemes as $color_scheme => $value ) {
			$color_scheme_control_options[ $color_scheme ] = $value['label'];
		}

		return $color_scheme_control_options;
	}
endif; // tainacan_get_color_scheme_choices


if ( ! function_exists( 'tainacan_sanitize_color_scheme' ) ) :
	/**
	 * Handles sanitization for Tainacan Theme color schemes.
	 *
	 * Create your own tainacan_sanitize_color_scheme() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @param string $value Color scheme name value.
	 * @return string Color scheme name.
	 */
	function tainacan_sanitize_color_scheme( $value ) {
		$color_schemes = tainacan_get_color_scheme_choices();

		if ( ! array_key_exists( $value, $color_schemes ) ) {
			return 'default';
		}

		return $value;
	}
endif; // tainacan_sanitize_color_scheme

/**
 * Enqueues front-end CSS for color scheme.
 *
 * @since Tainacan Theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_color_scheme_css() {

	$color_scheme = tainacan_get_color_scheme();

	// Convert main text hex color to rgba.
	$color_textcolor_rgb = tainacan_hex2rgb( $color_scheme[2] );

	// If the rgba values are empty return early.
	if ( empty( $color_textcolor_rgb ) ) {
		return;
	}

	// If we get this far, we have a custom color scheme.
	$colors = array(
		'background_color'      => $color_scheme[0],
		'page_background_color' => $color_scheme[1],
		'tainacan_link_color'            => $color_scheme[2],
		'tainacan_tooltip_color'            => $color_scheme[3],
		'backtransparent'			=> vsprintf( 'rgba( %1$s, %2$s, %3$s, 0.3)', $color_textcolor_rgb ),
	);

	$color_scheme_css = tainacan_get_color_scheme_css( $colors );

	echo '<style type="text/css" id="custom-theme-css">' .
	$color_scheme_css . '</style>';
}
add_action( 'wp_head', 'tainacan_color_scheme_css' );
/**
 * Binds the JS listener to make Customizer color_scheme control.
 *
 * Passes color scheme data as colorScheme global.
 *
 * @since Tainacan Theme
 */
function tainacan_customize_control_js() {
	wp_enqueue_script( 'tainacan-color-scheme-control', get_template_directory_uri() . '/assets/js/color-scheme-control.js', array( 'customize-controls', 'iris', 'underscore', 'wp-util' ), '20160816', true );
	wp_localize_script( 'tainacan-color-scheme-control', 'TainacanColorScheme', tainacan_get_color_schemes() );
}
add_action( 'customize_controls_enqueue_scripts', 'tainacan_customize_control_js' );

/**
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since Tainacan Theme
 */
function tainacan_customize_preview_js() {
	wp_enqueue_script( 'tainacan-customize-preview', get_template_directory_uri() . '/assets/js/customize-preview.js', array( 'customize-preview' ), '20160816', true );
}
add_action( 'customize_preview_init', 'tainacan_customize_preview_js' );

/**
 * Returns CSS for the color schemes.
 *
 * @since Tainacan Theme
 *
 * @param array $colors Color scheme colors.
 * @return string Color scheme CSS.
 */
function tainacan_get_color_scheme_css( $colors ) {
	$colors = wp_parse_args( $colors, array(
		'background_color'      => '',
		'page_background_color' => '',
		'tainacan_link_color'            => '',
		'tainacan_tooltip_color'            => '',
		'backtransparent'           => '',
	) );

	$filter = ( has_filter( 'tainacan-customize-css-class' ) ) ? apply_filters( 'tainacan-customize-css-class', $colors ) : '';
	return <<<CSS
	/* Color Scheme */
	
	body a,
	body a:hover, 
	.tainacan-title-page ul li a:hover, 
	.tainacan-list-post .blog-content h3 ,
	.tainacan-list-post .blog-content h3 a:hover,
	#comments .list-comments .media .media-body .comment-reply-link,
	#comments .list-comments .media .media-body .comment-edit-link {
		color: {$colors['tainacan_link_color']};
	}
	.tainacan-title-page ul li, 
	.tainacan-title-page ul li a,
	#menubelowHeader .menu-item a::after,
	.menu-shadow button[data-toggle='dropdown']::after{
		color: {$colors['tainacan_link_color']} !important;
	}
	.tainacan-single-post #comments,
	.tainacan-title-page,
	.tainacan-list-post .blog-post .blog-content .blog-read,
	.tainacan-list-post .blog-post .blog-content .blog-read:hover,
	.tainacan-content .wp-block-button:not(.is-style-outline) a,
	.tainacan-content .wp-block-button:not(.is-style-outline) a:hover {
		border-color: {$colors['tainacan_link_color']} !important;
	}
	.tainacan-list-post .blog-post .blog-content .blog-read,
	.tainacan-list-post .blog-post .blog-content .blog-read:hover,
	.tainacan-content .wp-block-button:not(.is-style-outline) a,
	.tainacan-content .wp-block-button:not(.is-style-outline) a:hover {
		background-color: {$colors['tainacan_link_color']};
	}
	.tainacan-content .wp-block-button.is-style-outline a,
	.tainacan-content .wp-block-button.is-style-outline a:hover {
		color: {$colors['tainacan_link_color']} !important;
	}

	.tainacan-content .wp-block-tainacan-carousel-items-list .swiper-button-prev svg, 
	.tainacan-content .wp-block-tainacan-carousel-items-list .swiper-button-next svg,
	.tainacan-content .wp-block-tainacan-carousel-collections-list .swiper-button-prev svg, 
	.tainacan-content .wp-block-tainacan-carousel-collections-list .swiper-button-next svg,
	.tainacan-content .wp-block-tainacan-carousel-terms-list .swiper-button-prev svg, 
	.tainacan-content .wp-block-tainacan-carousel-terms-list .swiper-button-next svg {
		fill: {$colors['tainacan_link_color']} !important;
	}

	.tainacan-single-post .single-item-collection--attachments-next,
	.tainacan-single-post .single-item-collection--attachments-prev {
		color: {$colors['tainacan_link_color']} !important;
	}

	.tainacan-single-post .single-item-collection .single-item-collection--gallery-items .slick-current img {
		border-bottom: 4px solid {$colors['tainacan_link_color']} !important;
	}

	.tainacan-single-post .single-item-collection .tainacan-item-file-download {
		background-color: {$colors['tainacan_link_color']} !important;
	}

	.tainacan-single-post .title-content-items {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Header Menu */
	nav .dropdown-menu .dropdown-item:hover {
		background-color: {$colors['backtransparent']};
	}
	nav.menu-belowheader #menubelowHeader > ul > li.menu-item a:hover::before {
		background-color: {$colors['tainacan_link_color']};
	}
	nav.menu-belowheader #menubelowHeader ul > li.current_page_item > a, 
	nav.menu-belowheader #menubelowHeader ul > li.current-menu-item > a {
		border-color: {$colors['tainacan_link_color']} !important;
	}
	nav.menu-belowheader #menubelowHeader ul.show > li.current_page_item > a, 
	nav.menu-belowheader #menubelowHeader ul.show > li.current-menu-item > a {
		border-color: {$colors['tainacan_link_color']};
		background-color: {$colors['backtransparent']};
	}

	.tainacan-single-post #comments .title-leave,
	.tainacan-single-post article .title-content-items,
	.tainacan-single-post article .tainacan-content h1 {
		color: {$colors['tainacan_link_color']} !important;
	}
	footer hr.bg-scooter {
		background-color: {$colors['tainacan_link_color']} !important;
	}

	/* Blockquote */
	.wp-block-quote:not(.is-large):not(.is-style-large) {
		border-left-color: {$colors['tainacan_link_color']} !important;
	}

	/* Pullquote */
    .wp-block-pullquote p {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Code */
    .wp-block-code code {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Extra title group class, that can be added for styling special headings */
	.wp-block-group.tainacan-group-heading h1,
	.wp-block-group.tainacan-group-heading h2,
	.wp-block-group.tainacan-group-heading h3,
	.wp-block-group.tainacan-group-heading h4 {
		color: {$colors['tainacan_link_color']} !important;
	}
	.wp-block-group.tainacan-group-heading hr.wp-block-separator {
		background-color: {$colors['tainacan_link_color']};
		border-color: {$colors['tainacan_link_color']};
	}

	/**
	* Tainacan Taxonomy Archive Page
	*/
	.page-header-taxonomy > .container-fluid > .page-header-content > .page-header-content-meta > .page-header-content-title {
		border-color: {$colors['tainacan_link_color']} !important;
	}
	.page-header-taxonomy > .container-fluid > .page-header-content > .page-header-content-meta > .page-header-content-title .page-header-title {
		color: {$colors['tainacan_link_color']};
	}

	/**
	* Tainacan Collections
	*/
	.tainacan-collection-list--simple-search .dropdown #dropdownMenuSorting::after, 
	.tainacan-collection-list--simple-search .dropdown #dropdownMenuViewMode::after {
		color: {$colors['tainacan_link_color']};
	}

	.tainacan-collection-list--simple-search .dropdown .dropdown-menu a:hover {
		background-color: {$colors['backtransparent']};
	}

	/**
	* Plugin Tainacan
	*/
	/* Selected Item background ------------------------------------------- */
	.table-container .table-wrapper table.tainacan-table tbody tr.selected-row,
	.table-container .table-wrapper table.tainacan-table tbody tr.selected-row .checkbox-cell .checkbox, 
	.table-container .table-wrapper table.tainacan-table tbody tr.selected-row .actions-cell .actions-container
	.tainacan-cards-container .selected-card .metadata-title,
	.tainacan-records-container .selected-record .metadata-title,
	.tainacan-grid-container .selected-grid-item.
	.tainacan-masonry-container .selected-masonry-item {
		background-color: {$colors['tainacan_link_color']};
	}
	.toast.is-secondary {
        background-color: {$colors['tainacan_link_color']};
	}
	/* // - Selected Item Title ------------------------------------------- */
	.table-container .table-wrapper table.tainacan-table tbody tr.selected-row has-text-secondary,
	.table-container .table-wrapper table.tainacan-table tbody tr.selected-row p,
	.tainacan-cards-container .selected-card .metadata-title p,
	.tainacan-records-container .selected-record .metadata-title p{
		color: {$colors['tainacan_link_color']} !important;
	}
	/* // - Selected Item Checkbox ------------------------------------------- */
	.table-container .table-wrapper table.tainacan-table tbody tr.selected-row checkbox-cell .b-checkbox.checkbox input[type="checkbox"]:checked + .check,
	.tainacan-cards-container .selected-card .card-checkbox .b-checkbox.checkbox input[type="checkbox"]:checked + .check,
	.tainacan-grid-container .selected-grid-item .grid-item-checkbox .b-checkbox.checkbox input[type="checkbox"]:checked + .check,
	.tainacan-masonry-container .selected-masonry-item .masonry-item-checkbox .b-checkbox.checkbox input[type="checkbox"]:checked + .check,
	.tainacan-records-container .selected-record .record-checkbox .b-checkbox.checkbox input[type="checkbox"]:checked + .check  {
		background-color: transparent;
		background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3Cpath style='fill:rgb(255,255,255)' d='M 0.04038059,0.6267767 0.14644661,0.52071068 0.42928932,0.80355339 0.3232233,0.90961941 z M 0.21715729,0.80355339 0.85355339,0.16715729 0.95961941,0.2732233 0.3232233,0.90961941 z'%3E%3C/path%3E%3C/svg%3E");
		border-color: {$colors['tainacan_link_color']} !important;
	}
	.tainacan-slide-main-view .slide-control-arrow .icon .tainacan-icon::before {
		color: {$colors['tainacan_link_color']};
	}
	.tainacan-slides-list #tainacan-slide-container .tainacan-slide-item.active-item img {
		border-bottom: 4px solid {$colors['tainacan_link_color']};
	}
	/** Abas no modal de termos */
	.tainacan-modal-content .tabs li.is-active a {
		border-bottom-color: {$colors['tainacan_link_color']};
	}
	/* Setinhas no mesmo modal */
	.tainacan-finder-columns-container a .tainacan-icon {
		color: {$colors['tainacan_link_color']};
	}

	/* Dropdown Arrow */
	.theme-items-list .dropdown .dropdown-trigger .button .icon, 
	.theme-items-list .autocomplete .dropdown-trigger .button .icon {
		color: {$colors['tainacan_link_color']};
	}

	/* Dropdown Active Item (for normal dropdown, autocomplete, taginput, etc... */
	.theme-items-list .dropdown .dropdown-menu .dropdown-content .dropdown-item.is-active, 
	.theme-items-list .dropdown .dropdown-menu .dropdown-content .has-link a.is-active, 
	.theme-items-list .dropdown .dropdown-menu .has-link .dropdown-content a.is-active, 
	.theme-items-list .autocomplete .dropdown-menu .dropdown-content .dropdown-item.is-active, 
	.theme-items-list .autocomplete .dropdown .dropdown-menu .dropdown-content .has-link a.is-active, 
	.theme-items-list .dropdown .autocomplete .dropdown-menu .dropdown-content .has-link a.is-active, 
	.theme-items-list .autocomplete .dropdown .dropdown-menu .has-link .dropdown-content a.is-active, 
	.theme-items-list .dropdown .autocomplete .dropdown-menu .has-link .dropdown-content a.is-active {
		background-color: {$colors['backtransparent']};
	}

	/* Document download button */
	.tainacan-single-post article .tainacan-content.single-item-collection .single-item-collection--document .tainacan-item-file-download {
		background-color: {$colors['tainacan_link_color']} !important;
	}

	/* Select Arrow */
	.theme-items-list .select:not(.is-loading)::after,
	.tainacan-modal-content .select:not(.is-loading)::after,
	button.link-style, 
	button.link-style:focus,
	button.link-style:hover {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Buefy's Numberinput */
	.theme-items-list .b-numberinput button .mdi::before,
	.tainacan-modal-content .b-numberinput button .mdi::before {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Anchor tag, links, buttons styled as links */
	.theme-items-list a, .theme-items-list a:hover,
	.tainacan-modal-content a, .tainacan-modal-content a:hover,
	.theme-items-list button.link-style, .theme-items-list button.link-style:hover,
	.tainacan-modal-content button.link-style, .tainacan-modal-content button.link-style:hover   {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Tooltip */
	.tooltip .tooltip-inner {
		background-color: {$colors['tainacan_tooltip_color']} !important;
	}
	.tooltip .tooltip-arrow {
		border-color: {$colors['tainacan_tooltip_color']} !important;
	}

	/* Colored text */
	.theme-items-list .has-text-secondary,
	.tainacan-modal-content .has-text-secondary {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Pagination icons and links */
	.theme-items-list .pagination-area .pagination .pagination-link, 
	.theme-items-list .pagination-area .pagination .pagination-previous, 
	.theme-items-list .pagination-area .pagination .pagination-next {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Datepicker */
	.theme-items-list .filter-item-forms .datepicker .datepicker-header a>span>i:before,
	.tainacan-modal-content .filter-item-forms .datepicker .datepicker-header a>span>i:before {
		color: {$colors['tainacan_link_color']} !important;
	} 
	.theme-items-list .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-selected,
	.theme-items-list .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-selected:hover,
	.tainacan-modal-content .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-selected,
	.tainacan-modal-content .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-selected:hover {
		background-color: {$colors['tainacan_link_color']} !important;
	}
	.theme-items-list .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-today,
	.theme-items-list .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-today:hover,
	.tainacan-modal-content .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-today,
	.tainacan-modal-content .filter-item-forms .datepicker .datepicker-table .datepicker-cell.is-today:hover {
		background-color: {$colors['tainacan_tooltip_color']} !important;
	}

	/* Outline Button */
	.theme-items-list .button.is-outlined,
	.tainacan-modal-content .button.is-outlined,
	.theme-items-list .button.is-outlined:hover,
	.tainacan-modal-content .button.is-outlined:hover {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Colored Button */
	.theme-items-list .button.is-secondary,
	.theme-items-list .button.is-secondary:hover, 
	.theme-items-list .button.is-secondary:focus {
		background: {$colors['tainacan_link_color']} !important;
	}

	/* Checkbox modal on finder columns */
	.tainacan-modal-content  .tainacan-li-checkbox-last-active,
	.tainacan-modal-content  .tainacan-li-checkbox-parent-active,
	.tainacan-modal-content  .tainacan-show-more:hover {
		background-color: {$colors['backtransparent']} !important;
	}

	/* Checkbox modal title and arrow*/
	.tainacan-modal-content h2,
	.tainacan-modal-content h3,
	.tainacan-modal-content .tainacan-icon-arrowright {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Advanced search criteria title */ 
	.advanced-search-criteria-title h1,
	.advanced-search-criteria-title h2 {
		color: {$colors['tainacan_link_color']} !important;
	} 

	/* Advanced search results title */ 
	.advanced-search-results-title h1 { 
		color: {$colors['tainacan_link_color']} !important; 
	}
	.advanced-search-results-title hr { 
		background-color: {$colors['backtransparent']} !important; 
	}

	/* Line above section titles */
	.tainacan-modal-title hr,
	.advanced-search-criteria-title hr {
		background-color: {$colors['tainacan_link_color']} !important;
	}

	/* Filter menu compress button */
	#filter-menu-compress-button,
	#filter-menu-compress-button-mobile {
		background-color: {$colors['backtransparent']} !important;
		color: {$colors['tainacan_link_color']} !important;
	}

	#filters-mobile-modal .modal-close::before,
	#filters-mobile-modal .modal-close::after {
		background-color: {$colors['tainacan_link_color']} !important;
	}

	.slide-control-arrow .icon .tainacan-icon::before {
		color: {$colors['tainacan_link_color']};
	}
	#tainacan-slide-container .tainacan-slide-item.active-item.active-item img {
		border-bottom: 4px solid {$colors['tainacan_link_color']} !important;
	}
	.metadata-menu .metadata-menu-header hr {
		background-color: {$colors['backtransparent']};
	}

	.slide-title-area .play-button .icon {
		border: 3px solid {$colors['tainacan_link_color']};
	}

	#return-to-top {
		background-color: {$colors['tainacan_link_color']};
	}

	{$filter}
	
CSS;
}


/**
 * Outputs an Underscore template for generating CSS for the color scheme.
 *
 * The template generates the css dynamically for instant display in the
 * Customizer preview.
 *
 * @since Tainacan Theme
 */
function tainacan_color_scheme_css_template() {
	$colors = array(
		'background_color'      => '{{ data.background_color }}',
		'page_background_color' => '{{ data.page_background_color }}',
		'tainacan_link_color'            => '{{ data.tainacan_link_color }}',
		'tainacan_tooltip_color'            => '{{ data.tainacan_tooltip_color }}',
		'backtransparent'		=> '{{ data.backtransparent }}',
	);
	?>
	<script type="text/html" id="tmpl-tainacan-color-scheme">
		<?php echo tainacan_get_color_scheme_css( $colors ); ?>
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', 'tainacan_color_scheme_css_template' );

/**
 * Enqueues front-end CSS for the link color.
 *
 * @since Tainacan Theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_link_color_css() {
	$color_scheme    = tainacan_get_color_scheme();
	$default_color   = $color_scheme[2];
	$link_color = get_theme_mod( 'tainacan_link_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $link_color === $default_color ) {
		return;
	}

	$css = '
		/* Custom Link Color */
		body a, 
		.tainacan-title-page ul li, 
		.tainacan-title-page ul li a,
		.tainacan-title-page ul li a:hover, 
		.tainacan-list-post .blog-content h3 {
			color: %1$s !important;
		}
	';

	wp_add_inline_style( 'tainacan-style', sprintf( $css, $link_color ) );
}
add_action( 'wp_enqueue_scripts', 'tainacan_link_color_css', 11 );

/**
 * Enqueues front-end CSS for the main text color.
 *
 * @since Tainacan Theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_tooltip_color_css() {
	$color_scheme    = tainacan_get_color_scheme();
	$default_color   = $color_scheme[3];
	$tooltip_color = get_theme_mod( 'tainacan_tooltip_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $tooltip_color === $default_color ) {
		return;
	}

	// Convert main text hex color to rgba.
	$tooltip_color_rgb = tainacan_hex2rgb( $tooltip_color );

	// If the rgba values are empty return early.
	if ( empty( $tooltip_color_rgb ) ) {
		return;
	}

	// If we get this far, we have a custom color scheme.
	$border_color = vsprintf( 'rgba( %1$s, %2$s, %3$s, 0.2)', $tooltip_color_rgb );

	$css = '
		/* Custom Main Text Color */
		.tainacan-list-post .blog-post .blog-content .blog-read {
			color: %1$s !important;
		}
	';

	wp_add_inline_style( 'tainacan-style', sprintf( $css, $tooltip_color, $border_color ) );
}
add_action( 'wp_enqueue_scripts', 'tainacan_tooltip_color_css', 11 );

/**
 * Enqueues front-end CSS for the single item page metadata columns.
 *
 * @since Tainacan Theme
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
