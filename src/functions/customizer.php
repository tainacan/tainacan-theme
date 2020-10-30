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
		'priority'   => 170,
	));

	$wp_customize->add_setting( 'tainacan_blogaddress', array(
		'type'       => 'theme_mod',
		'capability' => 'manage_options',
		'sanitize_callback'  => 'sanitize_text_field'
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

	$wp_customize->add_setting( 'tainacan_footer_color', array(
		'type' 		 => 'theme_mod',
		'capability' => 'edit_theme_options',
		'default' 	 => 'dark',
		'transport'  => 'refresh',
		'sanitize_callback' => 'tainacan_sanitize_footer_color_options',
		) );
	$wp_customize->add_control( 'tainacan_footer_color', array(
		'type' 	   	  => 'select',
		'priority' 	  => 6, // Within the section.
		'section'  	  => 'tainacan_footer_info',
		'label'    	  => __( 'Footer color scheme', 'tainacan-interface' ),
		'choices'	  => tainacan_get_footer_color_options()
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
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
	) );

	$wp_customize->add_control( 'tainacan_display_powered', array(
		'type' => 'checkbox',
		'settings' => 'tainacan_display_powered',
		'section' => 'tainacan_footer_info',
		'label' => __( 'Display "Proudly Powered by..."', 'tainacan-interface' ),
		'description' => __( 'This checkbox shows the "Proudly Powered by Tainacan and Wordpress" sentence.', 'tainacan-interface' ),
	) );

	/**
	 * Header settings
	 */
	$wp_customize->add_section('tainacan_header_search', array(
		'title'  	 => __( 'Header search', 'tainacan-interface' ),
		'priority'   => 61,
	));

	// Hide search input on header
	$wp_customize->add_setting( 'tainacan_hide_search_input', array(
		'type'       => 'theme_mod',
		'default'    => false,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'tainacan_hide_search_input', array(
		'type' 		=> 'checkbox',
		'settings' 	=> 'tainacan_hide_search_input',
		'section' 	=> 'tainacan_header_search',
		'label' => __( 'Hide search icon and input', 'tainacan-interface' )
	) );

	if (defined ( 'TAINACAN_VERSION' ) ) {
		/**
		 * Adds option to configure the Global search option label.
		 */
		$wp_customize->add_setting( 'tainacan_search_global_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __( 'Global', 'tainacan-interface' ),
			'sanitize_callback'  => 'sanitize_text_field'
			) );
		$wp_customize->add_control( 'tainacan_search_global_label', array(
			'type' 	   	  => 'text',
			'settings'	  => 'tainacan_search_global_label',
			'section'  	  => 'tainacan_header_search',
			'label'    	  => __( 'Label for "Global" search option', 'tainacan-interface' ),
			'description' => __( 'The Global search is the default. Its option will only be visible if at least one of the bellow are selected.', 'tainacan-interface')
			) );
		
		// Option to search directly on repository items list
		$wp_customize->add_setting( 'tainacan_search_on_items', array(
			'type'       => 'theme_mod',
			'default'    => false,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'tainacan_search_on_items', array(
			'type' 		=> 'checkbox',
			'settings' 	=> 'tainacan_search_on_items',
			'section' 	=> 'tainacan_header_search',
			'label'		=> __( 'Display option to search on tainacan items repository list', 'tainacan-interface' )
		) );

		/**
		 * Adds option to configure the Items search option label.
		 */
		$wp_customize->add_setting( 'tainacan_search_on_items_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __( 'Items', 'tainacan-interface' ),
			'sanitize_callback'  => 'sanitize_text_field'
			) );
		$wp_customize->add_control( 'tainacan_search_on_items_label', array(
			'type' 	   	  => 'text',
			'settings'	  => 'tainacan_search_on_items_label',
			'section'  	  => 'tainacan_header_search',
			'label'    	  => __( 'Label for the "items" search option', 'tainacan-interface' )
			) );

		// Option to search directly on collections list
		$wp_customize->add_setting( 'tainacan_search_on_collections', array(
			'type'       => 'theme_mod',
			'default'    => false,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'tainacan_search_on_collections', array(
			'type' 		=> 'checkbox',
			'settings' 	=> 'tainacan_search_on_collections',
			'section' 	=> 'tainacan_header_search',
			'label'		=> __( 'Display option to search on tainacan collections list', 'tainacan-interface' )
		) );

		/**
		 * Adds option to configure the Collections search option label.
		 */
		$wp_customize->add_setting( 'tainacan_search_on_collections_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __( 'Collections', 'tainacan-interface' ),
			'sanitize_callback'  => 'sanitize_text_field'
			) );
		$wp_customize->add_control( 'tainacan_search_on_collections_label', array(
			'type' 	   	  => 'text',
			'settings'	  => 'tainacan_search_on_collections_label',
			'section'  	  => 'tainacan_header_search',
			'label'    	  => __( 'Label for the "Collections" search option', 'tainacan-interface' )
			) );

		// Option to search on wordpress posts only
		$wp_customize->add_setting( 'tainacan_search_on_posts', array(
			'type'       => 'theme_mod',
			'default'    => false,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'tainacan_search_on_posts', array(
			'type' 		=> 'checkbox',
			'settings' 	=> 'tainacan_search_on_posts',
			'section' 	=> 'tainacan_header_search',
			'label'		=> __( 'Display option to search only on WordPress posts', 'tainacan-interface' )
		) );

		/**
		 * Adds option to configure the Posts search option label.
		 */
		$wp_customize->add_setting( 'tainacan_search_on_posts_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __( 'Posts', 'tainacan-interface' ),
			'sanitize_callback'  => 'sanitize_text_field'
			) );
		$wp_customize->add_control( 'tainacan_search_on_posts_label', array(
			'type' 	   	  => 'text',
			'settings'	  => 'tainacan_search_on_posts_label',
			'section'  	  => 'tainacan_header_search',
			'label'    	  => __( 'Label for the "Posts" search option', 'tainacan-interface' )
			) );

		// Option to search on wordpress pages only
		$wp_customize->add_setting( 'tainacan_search_on_pages', array(
			'type'       => 'theme_mod',
			'default'    => false,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'tainacan_search_on_pages', array(
			'type' 		=> 'checkbox',
			'settings' 	=> 'tainacan_search_on_pages',
			'section' 	=> 'tainacan_header_search',
			'label'		=> __( 'Display option to search only on WordPress pages', 'tainacan-interface' )
		) );

		/**
		 * Adds option to configure the Pages search option label.
		 */
		$wp_customize->add_setting( 'tainacan_search_on_pages_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __( 'Pages', 'tainacan-interface' ),
			'sanitize_callback'  => 'sanitize_text_field'
			) );
		$wp_customize->add_control( 'tainacan_search_on_pages_label', array(
			'type' 	   	  => 'text',
			'settings'	  => 'tainacan_search_on_pages_label',
			'section'  	  => 'tainacan_header_search',
			'label'    	  => __( 'Label for the "Pages" search option', 'tainacan-interface' )
			) );
	}

	/**
	 * Social Share Links
	 */
	$wp_customize->add_section('tainacan_social_share', array(
		'title'  	 => __( 'Social Share', 'tainacan-interface' ),
		'priority'   => 171,
	));

	//Facebook
	$wp_customize->add_setting( 'tainacan_facebook_share', array(
		'type'       => 'theme_mod',
		'default'        => true,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
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
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
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

	// WhatsApp
	$wp_customize->add_setting( 'tainacan_whatsapp_share', array(
		'type'       => 'theme_mod',
		'default'        => false,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_whatsapp_share', array(
		'type' => 'checkbox',
		'settings' => 'tainacan_whatsapp_share',
		'section' => 'tainacan_social_share',
		'label' => __( 'Display WhatsApp button', 'tainacan-interface' ),
	) );

	// Telegram
	$wp_customize->add_setting( 'tainacan_telegram_share', array(
		'type'       => 'theme_mod',
		'default'        => false,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_telegram_share', array(
		'type' => 'checkbox',
		'settings' => 'tainacan_telegram_share',
		'section' => 'tainacan_social_share',
		'label' => __( 'Display Telegram button', 'tainacan-interface' ),
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
		'type'       		=> 'theme_mod',
		'default'           => $color_scheme[2],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tainacan_link_color', array(
		'label'       => __( 'Or pick any color', 'tainacan-interface' ),
		'section'     => 'colors',
	) ) );

	$wp_customize->add_setting( 'tainacan_tooltip_color', array(
		'type'       		=> 'theme_mod',
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
		'transport'  => 'refresh',
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
		) );
	$wp_customize->add_control( 'tainacan_hide_site_title_on_header_banner', array(
		'type' 	   	  => 'checkbox',
		'priority' 	  => 98, // Within the section.
		'section'  	  => 'header_image',
		'label'    	  => __( 'Hide the header banner site title', 'tainacan-interface' ),
		'description' => __( 'Toggle to hide the site title row that appears over the header banner', 'tainacan-interface' )
		) );
	$wp_customize->selective_refresh->add_partial( 'tainacan_hide_site_title_on_header_banner', array(
			'selector' => '.page-header .title-header h1',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
		) );
	$wp_customize->add_setting( 'tainacan_hide_header_banner', array(
		'type' 		 => 'theme_mod',
		'capability' => 'edit_theme_options',
		'default' 	 => false,
		'transport'  => 'refresh',
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
		'transport'  => 'postMessage',
		'sanitize_callback'  => 'sanitize_text_field'
	) );
	$wp_customize->add_control( 'tainacan_hide_header_box_opacity', array(
		'type' => 'number',
		'section' => 'header_image',
		'label' => __( 'Title box opacity (%)', 'tainacan-interface' ),
		'description' => __( 'Change the opacity of the white box that holds the banner site title', 'tainacan-interface' ),
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
			'transport'  => 'refresh',
			'sanitize_callback' => 'tainacan_sanitize_single_item_layout_sections_order',
			) );
		$wp_customize->add_control( 'tainacan_single_item_layout_sections_order', array(
			'type' 	   	  => 'select',
			'priority' 	  => -1, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Layout sections order.', 'tainacan-interface' ),
			'description' => __( 'Display the document, attachments and metadata sections in different order.', 'tainacan-interface' ),
			'choices'	  => tainacan_get_single_item_layout_sections_order_options()
			) );

		if (version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {
			/**
			 * Adds option to display Collection banner on the item single page.
			 */
			$wp_customize->add_setting( 'tainacan_single_item_collection_header', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
				) );
			$wp_customize->add_control( 'tainacan_single_item_gallery_mode', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 0, // Within the section.
				'section'  	  => 'tainacan_single_item_page',
				'label'    	  => __( 'Show "Documents" section: Document and Attachments grouped in one slider.', 'tainacan-interface' ),
				'description' => __( 'Toggle to show the document and attachments in the same list, a carousel with the current item on top.', 'tainacan-interface' )
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
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
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
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
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
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
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
		 * Adds option to configure Item Navigation section label.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_navigation_section_label', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => __('Continue browsing', 'tainacan-interface'),
			'transport'  => 'postMessage',
			'sanitize_callback'  => 'sanitize_text_field'
			) );
		$wp_customize->add_control( 'tainacan_single_item_navigation_section_label', array(
			'type' 	   	  => 'text',
			'priority' 	  => 0, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Label for the "Items navigation" or "Continue browsing" section', 'tainacan-interface' ),
			'description' => __( 'Leave blank it for not displaying any label.', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_item_navigation_section_label', array(
			'selector' => '#single-item-navigation-label',
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
			'transport'  => 'refresh',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
		 * Adds options to hide item naviagtion options.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_show_navigation_options', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => false,
			'transport'  => 'refresh',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
			) );
		$wp_customize->add_control( 'tainacan_single_item_show_navigation_options', array(
			'type' 	   	  => 'checkbox',
			'priority' 	  => 2, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Show the item navigation options in the breadcrumb section', 'tainacan-interface' ),
			'description' => __( 'Toggle to display two arrows and a list icon for navigating directly from the item page breadcrumb section.', 'tainacan-interface' )
			) );
		$wp_customize->selective_refresh->add_partial( 'tainacan_single_show_hide_navigation_options', array(
			'selector' => '#breadcrumb-single-item-pagination',
			'render_callback' => '__return_false',
			'fallback_refresh' => true
			) );

		if (version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {
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
				'section'  	  => 'tainacan_single_item_page',
				'label'    	  => __( 'Hide Document download button', 'tainacan-interface' ),
				'description' => __( 'Toggle to never display a "Download" button when hovering the document.', 'tainacan-interface' )
				) );
		}

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
			'transport'  => 'postMessage',
			'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
		 * Adds options to hide or no the core metadada in the metadada list.
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
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Hide core title from metadata list', 'tainacan-interface' ),
			'description' => __( 'Toggle to hide or not the core title from the metadada list, as it already appears on the page title.', 'tainacan-interface' )
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
			'section'  	  => 'tainacan_single_item_page',
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
			'section' 	  => 'tainacan_single_item_page',
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
			'section' 	  => 'tainacan_single_item_page',
			'label' 	  => __( 'Number of metadata columns (wide)', 'tainacan-interface' ),
			'description' => __( 'For screens larger than 1366px.', 'tainacan-interface' ),
			'input_attrs' => array(
				'placeholder' => '3',
				'min' => 1,
				'max' => 4,
				'step' => 1
			)
			) );

		/**
		 * Adds options to display previous/next links on item single page.
		 */
		$wp_customize->add_setting( 'tainacan_single_item_navigation_options', array(
			'type' 		 => 'theme_mod',
			'capability' => 'edit_theme_options',
			'default' 	 => 'thumbnail_small',
			'transport'  => 'refresh',
			'sanitize_callback' => 'tainacan_sanitize_single_item_navigation_links_options',
			) );
		$wp_customize->add_control( 'tainacan_single_item_navigation_options', array(
			'type' 	   	  => 'select',
			'priority' 	  => 3, // Within the section.
			'section'  	  => 'tainacan_single_item_page',
			'label'    	  => __( 'Navigation links to adjacent items', 'tainacan-interface' ),
			'description' => __( 'Sets how next and previous items links will be displayed. If your Tainacan version is bellow 0.17, this links only obey creation date order inside their collection.', 'tainacan-interface' ),
			'choices'	  => tainacan_get_single_item_navigation_links_options()
			) );

		if (version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {

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
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_sanitize_items_repository_list_default_view_mode'
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
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_search', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 3, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the Search block.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the search block on the control bar.', 'tainacan-interface' )
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
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_advanced_search', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 3, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the Advanced Search link.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the advanced search link on the control bar.', 'tainacan-interface' )
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
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
				) );
			$wp_customize->add_control( 'tainacan_items_page_hide_exposers_button', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 5, // Within the section.
				'section'  	  => 'tainacan_items_page_search_area',
				'label'    	  => __( 'Hide the "View as..." button.', 'tainacan-interface' ),
				'description' => __( 'Toggle to do not show the "View as..." button, also referred to as "Exposers modal" on the control bar.', 'tainacan-interface' )
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
				'transport'  => 'postMessage',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
				) );
			$wp_customize->add_control( 'tainacan_items_page_start_with_filters_hidden', array(
				'type' 	   	  => 'checkbox',
				'priority' 	  => 9, // Within the section.
				'section'  	  => 'tainacan_items_page_filters_panel',
				'label'    	  => __( 'Start with filters hidden.', 'tainacan-interface' ),
				'description' => __( 'Toggle to make filters start hidden by default.', 'tainacan-interface' )
				) );

			if (version_compare(TAINACAN_VERSION, '0.17RC') >= 0) {
				/**
				 * Adds option to display filters as a modal on every items list.
				 */
				$wp_customize->add_setting( 'tainacan_items_page_filters_fixed_on_scroll', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => true,
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
					) );
				$wp_customize->add_control( 'tainacan_items_page_filters_fixed_on_scroll', array(
					'type' 	   	  => 'checkbox',
					'priority' 	  => 10, // Within the section.
					'section'  	  => 'tainacan_items_page_filters_panel',
					'label'    	  => __( 'Filters side panel fixed on scroll', 'tainacan-interface' ),
					'description' => __( 'Toggle to make filters get fixed on screen when scrolling down the items list. This will only take effect if the items list itself is taller than the screen height.', 'tainacan-interface' )
					) );
			}

			/**
			 * Adds option to display filters as a modal on every items list.
			 */
			$wp_customize->add_setting( 'tainacan_items_page_filters_as_modal', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => false,
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback'  => 'sanitize_text_field'
			) );
			$wp_customize->add_control( 'tainacan_items_page_default_items_per_page', array(
				'type' => 'number',
				'section' => 'tainacan_items_page_pagination',
				'label' => __( 'Default number of items per page', 'tainacan-interface' ),
				'description' => __( 'Change the default value for items loaded per page. Note that this affects loading duration.', 'tainacan-interface' ),
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
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
 * Callback to Sanitize Checkboxes.
 */
function tainacan_callback_sanitize_checkbox( $checked ) {
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

if ( ! function_exists( 'tainacan_sanitize_items_repository_list_default_view_mode' ) ) :
	/**
	 * Handles sanitization for Tainacan Theme items repository list default view mode.
	 *
	 * Create your own tainacan_sanitize_items_repository_list_default_view_mode() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @param string $value a valid view mode slug.
	 * @return string view mode slug.
	 */
	function tainacan_sanitize_items_repository_list_default_view_mode( $value ) {
		$view_mode_options = tainacan_get_default_view_mode_choices();

		if ( ! array_key_exists( $value, $view_mode_options['enabled_view_modes'] ) ) {
			return 'masonry';
		}

		return $value;
	}
endif; // tainacan_sanitize_items_repository_list_default_view_mode

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

if ( ! function_exists( 'tainacan_get_single_item_layout_sections_order_options' ) ) :
	/**
	 * Retrieves an array of options for single item page sections order for Tainacan Theme.
	 *
	 * Create your own tainacan_get_single_item_layout_sections_order_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @return array $order - a string with slugs to the section order, separated by hiphen.
	 */
	function tainacan_get_single_item_layout_sections_order_options() {
		$section_orders = array(
			'document-attachments-metadata' => __('Document - Attachments - Metadata', 'tainacan-interface'),
			'metadata-document-attachments' => __('Metadata - Document - Attachments', 'tainacan-interface'),
			'document-metadata-attachments' => __('Document - Metadata - Attachments', 'tainacan-interface'),
		);
		return $section_orders;
	}
endif; // tainacan_get_single_item_layout_sections_order_options

if ( ! function_exists( 'tainacan_sanitize_single_item_layout_sections_order' ) ) :
	/**
	 * Handles sanitization for Tainacan Themeitem page layout sections order
	 *
	 * Create your own tainacan_sanitize_single_item_layout_sections_order() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @param string $order - a string with slugs to the section order, separated by hiphen
	 * @return string the selected order.
	 */
	function tainacan_sanitize_single_item_layout_sections_order( $order ) {
		$section_orders = tainacan_get_single_item_layout_sections_order_options();

		if ( ! array_key_exists( $order, $section_orders ) ) {
			return 'document-attachments-metadata';
		}

		return $order;
	}
endif; // tainacan_sanitize_single_item_layout_sections_order


if ( ! function_exists( 'tainacan_get_footer_color_options' ) ) :
	/**
	 * Retrieves an array of options for footer color on Tainacan Theme.
	 *
	 * Create your own tainacan_get_footer_color_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @return array $color_options - a string describing the color style option
	 */
	function tainacan_get_footer_color_options() {
		$color_options = array(
			'dark' 		=> __('Dark', 'tainacan-interface'),
			'light' 	=> __('Light', 'tainacan-interface'),
			'colored' 	=> __('Colored', 'tainacan-interface'),
		);
		return $color_options;
	}
endif; // tainacan_get_footer_color_options

if ( ! function_exists( 'tainacan_sanitize_footer_color_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Theme footer color style
	 *
	 * Create your own tainacan_sanitize_footer_color_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @param string $option - a string describing the color style for the footer
	 * @return string the selected option.
	 */
	function tainacan_sanitize_footer_color_options( $option ) {
		$color_options = tainacan_get_footer_color_options();

		if ( ! array_key_exists( $option, $color_options ) ) {
			return 'dark';
		}

		return $option;
	}
endif; // tainacan_sanitize_footer_color_options


if ( ! function_exists( 'tainacan_get_single_item_navigation_links_options' ) ) :
	/**
	 * Retrieves an array of options for single item page navigation options for Tainacan Theme.
	 *
	 * Create your own tainacan_get_single_item_navigation_links_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @return array $option - a string with options for displaying the naviation links.
	 */
	function tainacan_get_single_item_navigation_links_options() {
		$navigation_options = array(
			'none' => __('Do not display items links', 'tainacan-interface'),
			'link' => __('Show only items Link', 'tainacan-interface'),
			'thumbnail_small' => __('Show items links with a small thumbnail', 'tainacan-interface'),
			'thumbnail_large' => __('Show items links with a large thumbnail', 'tainacan-interface'),
		);
		return $navigation_options;
	}
endif; // tainacan_get_single_item_navigation_links_options

if ( ! function_exists( 'tainacan_sanitize_single_item_navigation_links_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Theme item page navigation link options
	 *
	 * Create your own tainacan_sanitize_single_item_navigation_links_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Theme
	 *
	 * @param string $option - a string with options for displaying the naviation links.
	 * @return string the selected option.
	 */
	function tainacan_sanitize_single_item_navigation_links_options( $option ) {
		$navigation_options = tainacan_get_single_item_navigation_links_options();

		if ( ! array_key_exists( $option, $navigation_options ) ) {
			return 'none';
		}

		return $option;
	}
endif; // tainacan_sanitize_single_item_navigation_links_options

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
	
	.has-default-color { 
		color: {$colors['tainacan_link_color']} !important;
	}
	.has-default-background-color {
		background-color: {$colors['tainacan_link_color']} !important;
	}

	body a,
	body a:hover, 
	.tainacan-title-page ul li a:hover, 
	.tainacan-list-post .blog-content h3 ,
	.tainacan-list-post .blog-content h3 a:hover,
	#comments .list-comments .media .media-body .comment-reply-link,
	#comments .list-comments .media .media-body .comment-edit-link {
		color: {$colors['tainacan_link_color']};
	}
	.tainacan-list-post .blog-content h4 {
		background-color: {$colors['tainacan_tooltip_color']} !important;
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
	.tainacan-content .wp-block-button:not(.is-style-outline):not(.has-background) a,
	.tainacan-content .wp-block-button:not(.is-style-outline):not(.has-background) a:hover {
		background-color: {$colors['tainacan_link_color']};
	}
	.tainacan-content .wp-block-button.is-style-outline a:not(.has-text-color),
	.tainacan-content .wp-block-button.is-style-outline a:hover:not(.has-text-color) {
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
	.tainacan-content .wp-block-tainacan-facets-list .show-more-button {
		background-color: {$colors['tainacan_link_color']} !important;
	}
	.wp-block-tainacan-facets-list ul.facets-list.facets-layout-cloud li.facet-list-item:hover a,
	.wp-block-tainacan-facets-list ul.facets-list-edit.facets-layout-cloud li.facet-list-item:hover a {
		color: {$colors['tainacan_link_color']} !important;
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

	/* Colored version of footer */
	footer.tainacan-footer-colored {
		background-color: {$colors['tainacan_link_color']} !important;
	}
	footer.tainacan-footer-colored  hr.bg-scooter {
		background-color: {$colors['tainacan_tooltip_color']} !important;
	}
	footer.tainacan-footer-colored a,
	footer.tainacan-footer-colored .tainacan-footer-widgets-area .tainacan-side ul li a,
	footer.tainacan-footer-colored .tainacan-footer-widgets-area .tainacan-side ol li a,
	footer.tainacan-footer-colored .tainacan-side .textwidget,
	footer.tainacan-footer-colored .tainacan-side .recentcomments,
	footer.tainacan-footer-colored .tainacan-side .calendar_wrap,
	footer.tainacan-footer-colored .tainacan-side ul li,
	footer.tainacan-footer-colored .tainacan-side div li,
	footer.tainacan-footer-colored .tainacan-side div,
	footer.tainacan-footer-colored .tainacan-side ul,
	footer.tainacan-footer-colored .tainacan-side li,
	footer.tainacan-footer-colored .tainacan-footer-info .tainacan-powered {
		color: {$colors['tainacan_tooltip_color']} !important;
	}
    @media screen and (max-width: 991.98px) {
		footer.tainacan-footer-colored .tainacan-side {
			border-top-color: {$colors['tainacan_tooltip_color']} !important;
		}
	}

	/* Blockquote */
	.wp-block-quote:not(.is-large):not(.is-style-large) {
		border-left-color: {$colors['tainacan_link_color']} !important;
	}

	/* Separator */ 
    .wp-block-separator:not(.has-background-color) {
		border-color: {$colors['tainacan_link_color']} !important;
	}
	.wp-block-separator.is-style-dots:not(.has-background-color)::before {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Pullquote */
    .wp-block-pullquote blockquote:not(.has-text-color) p {
		color: {$colors['tainacan_link_color']} !important;
	}
	.wp-block-pullquote:not(.is-style-solid-color)  {
		border-color: {$colors['tainacan_link_color']} !important;
	}

	/* Code */
    .wp-block-code code {
		color: {$colors['tainacan_link_color']} !important;
	}

	/* Extra title group class, that can be added for styling special headings */
	.wp-block-group.tainacan-group-heading h1:not(.has-text-color),
	.wp-block-group.tainacan-group-heading h2:not(.has-text-color),
	.wp-block-group.tainacan-group-heading h3:not(.has-text-color),
	.wp-block-group.tainacan-group-heading h4:not(.has-text-color) {
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
	.tainacan-slides-list #tainacan-slide-container .tainacan-slide-item.active-item img,
	.tainacan-slides-list #tainacan-slide-container .swiper-slide.swiper-slide-active img {
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
	.metadata-menu .metadata-menu-header hr {
		background-color: {$colors['backtransparent']};
	}

	.slide-title-area .play-button .icon,
	.slide-title-area .play-button:hover .icon {
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

/**
 * Enqueues front-end CSS for the items page fixed filters logic.
 *
 * @since Tainacan Theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_items_page_filters_fixed_on_scroll_output() {
	$should_use_fixed_filters_logic = get_theme_mod( 'tainacan_items_page_filters_fixed_on_scroll', true );
	
	if (!$should_use_fixed_filters_logic)
		return;
		
	$css = '
	/* Items list fixed filter logic (Introduced in Tainacan 0.17) */
	:not(.wp-block-tainacan-faceted-search)>.theme-items-list:not(.is-fullscreen).is-filters-menu-open.is-filters-menu-fixed-at-top .items-list-area {
		margin-left: var(--tainacan-filter-menu-width-theme) !important;
	}
	:not(.wp-block-tainacan-faceted-search)>.theme-items-list:not(.is-fullscreen).is-filters-menu-open.is-filters-menu-fixed-at-top .filters-menu:not(.filters-menu-modal) {
		position: fixed;
		top: 0px !important;
	}
	:not(.wp-block-tainacan-faceted-search)>.theme-items-list:not(.is-fullscreen).is-filters-menu-open.is-filters-menu-fixed-at-top .filters-menu:not(.filters-menu-modal) .modal-content {
		position: absolute;
		top: 0px;
		height: auto !important;
		background: var(--tainacan-background-color, inherit);
	}
	:not(.wp-block-tainacan-faceted-search)>.theme-items-list:not(.is-fullscreen).is-filters-menu-open.is-filters-menu-fixed-at-top.is-filters-menu-fixed-at-bottom .filters-menu:not(.filters-menu-modal) {
		position: absolute;
	}
	:not(.wp-block-tainacan-faceted-search)>.theme-items-list:not(.is-fullscreen).is-filters-menu-open.is-filters-menu-fixed-at-top.is-filters-menu-fixed-at-bottom .filters-menu:not(.filters-menu-modal) .modal-content {
		top: auto;
		bottom: 0;
	}
	';
	echo '<style type="text/css" id="tainacan-fixed-filters-style">' . sprintf( $css ) . '</style>';

}
add_action( 'wp_head', 'tainacan_items_page_filters_fixed_on_scroll_output');

