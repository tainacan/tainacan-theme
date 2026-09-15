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
				'priority' 	  => 1, // Within the section.
				'section'  	  => 'tainacan_single_item_page_related_items',
				'label'    	  => __( 'Label for the "Items related to this" section', 'tainacan-interface' ),
				'description' => __( 'Leave it blank to hide the label.', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_related_items_section_enabled'
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
				'priority' 	  => 0, // Within the section.
				'section'  	  => 'tainacan_single_item_page_related_items',
				'label'    	  => __( 'Enable the "Items related to this" section', 'tainacan-interface' ),
				'description' => __( 'Show the "Items related to this" section. This also depends on collection settings and whether related items exist.', 'tainacan-interface' )
				) );

			if ( function_exists('tainacan_the_related_items') ) {
			
				/**
				 * Adds options to select a layout for the related items list.
				 */
				$wp_customize->add_setting( 'tainacan_single_item_related_items_layout', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => 'carousel',
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_sanitize_single_item_related_items_layout_options',
					) );
				$wp_customize->add_control( 'tainacan_single_item_related_items_layout', array(
					'type' 	   	  => 'select',
					'priority' 	  => 2, // Within the section.
					'section'  	  => 'tainacan_single_item_page_related_items',
					'label'    	  => __( 'Layout for the related items list', 'tainacan-interface' ),
					'choices'	  => tainacan_get_single_item_related_items_layout_options(),
					'active_callback' => 'tainacan_interface_is_related_items_section_enabled'
					) );

				/**
				 * Adds options to select a order for the related items list.
				 */
				$wp_customize->add_setting( 'tainacan_single_item_related_items_order', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => 'title_asc',
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_sanitize_single_item_related_items_order_options',
					) );
				$wp_customize->add_control( 'tainacan_single_item_related_items_order', array(
					'type' 	   	  => 'select',
					'priority' 	  => 3, // Within the section.
					'section'  	  => 'tainacan_single_item_page_related_items',
					'label'    	  => __( 'Sorting criteria for the related items query', 'tainacan-interface' ),
					'choices'	  => tainacan_get_single_item_related_items_order_options(),
					'active_callback' => 'tainacan_interface_is_related_items_section_enabled'
					) );

				/**
				 * Allows setting max columns count on grid and list layout ---------------------------------------------------------
				 */
				$wp_customize->add_setting( 'tainacan_single_item_related_items_max_columns_count', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => 4,
					'transport'  => 'refresh',
					'sanitize_callback'  => 'sanitize_text_field'
				) );
				$wp_customize->add_control( 'tainacan_single_item_related_items_max_columns_count', array(
					'type' => 'number',
					'priority' 	  => 5, // Within the section.
					'section' => 'tainacan_single_item_page_related_items',
					'label' => __( 'Maximum number of columns', 'tainacan-interface' ),
					'description' => __( 'How many columns of items appear on a large screen for the "grid" and "list" layouts. In the "grid" layout, a smaller number makes each thumbnail larger.', 'tainacan-interface' ),
					'input_attrs' => array(
						'min' => 1,
						'max' => 8,
						'step' => 1
					),
					'active_callback' => 'tainacan_interface_is_related_items_layout_grid_or_list',
				) );
			}

			/**
			 * Allows setting max items per screen on carousel layout ---------------------------------------------------------
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
				'priority' 	  => 4, // Within the section.
				'section' => 'tainacan_single_item_page_related_items',
				'label' => __( 'Maximum number of slides per screen', 'tainacan-interface' ),
				'description' => __( 'How many slides per carousel row appear on a large screen. A smaller number makes each thumbnail larger.', 'tainacan-interface' ),
				'input_attrs' => array(
					'min' => 1,
					'max' => 10,
					'step' => 1
				),
				'active_callback' => 'tainacan_interface_is_related_items_layout_carousel',
			) );

			/**
			 * Adds options related to the items gallery layout.
			 */
			if ( method_exists('\Tainacan\Theme_Helper', 'get_tainacan_items_gallery') ) {

				/**
                 * Allows setting max heigth for the items gallery main slider ---------------------------------------------------------
                 */
                $wp_customize->add_setting( 'tainacan_single_item_related_items_gallery_max_height', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => 60,
                    'transport'  => 'refresh',
                    'sanitize_callback'  => 'sanitize_text_field'
                ) );
                $wp_customize->add_control( 'tainacan_single_item_related_items_gallery_max_height', array(
                    'type' => 'number',
                    'priority' 	  => 6, // Within the section.
                    'section' => 'tainacan_single_item_page_related_items',
                    'label' => __( 'Items gallery maximum height (vh)', 'tainacan-interface' ),
                    'description' => __( 'Set the maximum height for the items gallery slider. The unit is relative to the screen: 60vh is 60% of the browser window height.', 'tainacan-interface' ),
                    'input_attrs' => array(
                        'min' => 10,
                        'max' => 150,
                        'step' => 5
                    ),
                    'active_callback' => 'tainacan_interface_is_related_items_using_gallery',
                ) );

                /**
                 * Allows setting carousel thumbnail size for the items gallery ---------------------------------------------------------
                 */
                $wp_customize->add_setting( 'tainacan_single_item_related_items_gallery_thumbnail_size', array(
                    'type' 		 => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'default' 	 => 136,
                    'transport'  => 'refresh',
                    'sanitize_callback'  => 'sanitize_text_field'
                ) );
                $wp_customize->add_control( 'tainacan_single_item_related_items_gallery_thumbnail_size', array(
                    'type' => 'number',
                    'priority' 	  => 7, // Within the section.
                    'section' => 'tainacan_single_item_page_related_items',
                    'label' => __( 'Items gallery thumbnail size (px)', 'tainacan-interface' ),
                    'input_attrs' => array(
                        'min' => 12,
                        'max' => 240,
                        'step' => 2
                    ),
                    'active_callback' => 'tainacan_interface_is_related_items_gallery_showing_images',
                ) );

				if ( function_exists( 'tainacan_sanitize_media_thumbs_layout' ) ) {
					$wp_customize->add_setting( 'tainacan_single_item_related_items_thumbs_layout', array(
						'type' 		 => 'theme_mod',
						'capability' => 'edit_theme_options',
						'default' 	 => 'carousel',
						'transport'  => 'refresh',
						'sanitize_callback' => 'tainacan_sanitize_single_item_gallery_thumbs_layout_options',
					) );
					$wp_customize->add_control( 'tainacan_single_item_related_items_thumbs_layout', array(
						'type' 	   	  => 'select',
						'priority' 	  => 8,
						'section'  	  => 'tainacan_single_item_page_related_items',
						'label'    	  => __( 'Thumbnails layout', 'tainacan-interface' ),
						'description' => __( 'Shows images and file-type icons, not live embeds. List rows always show the item title.', 'tainacan-interface' ),
						'choices'	  => tainacan_get_single_item_gallery_thumbs_layout_options(),
						'active_callback' => 'tainacan_interface_is_related_items_using_gallery',
					) );

					$wp_customize->add_setting( 'tainacan_single_item_related_items_hide_image_thumbnails', array(
						'type' 		 => 'theme_mod',
						'capability' => 'edit_theme_options',
						'default' 	 => false,
						'transport'  => 'refresh',
						'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
					) );
					$wp_customize->add_control( 'tainacan_single_item_related_items_hide_image_thumbnails', array(
						'type' 	   	  => 'checkbox',
						'priority' 	  => 9,
						'section'  	  => 'tainacan_single_item_page_related_items',
						'label'    	  => __( 'Hide thumbnail image', 'tainacan-interface' ),
						'description' => __( 'Toggle to hide the item thumbnail and show only the title.', 'tainacan-interface' ),
						'active_callback' => 'tainacan_interface_is_related_items_gallery_thumbs_layout_list',
					) );
				}
			}

        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_single_item_page_related_items', 11 );
}


if ( ! function_exists( 'tainacan_get_single_item_related_items_layout_options' ) ) :
	/**
	 * Retrieves an array of options for single item page related items layout options for Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_single_item_related_items_layout_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $option - a string with options for displaying the related items layout
	 */
	function tainacan_get_single_item_related_items_layout_options() {
		$related_items_layout_options = array(
			'carousel' => __('Carousel of items, with large thumbnails', 'tainacan-interface'),
			'grid' => __('Grid of items, with large thumbnails', 'tainacan-interface'),
			'list' => __('List of items, with smaller thumbnails', 'tainacan-interface')
		);
		if ( version_compare(TAINACAN_VERSION, '0.21.5') >= 0 ) {
			$tainacan_view_modes = tainacan_get_default_view_mode_choices();
			$tainacan_view_modes_options = array();
			foreach ($tainacan_view_modes['enabled_view_modes'] as $key => $value) {
				$tainacan_view_modes_options['tainacan-view-mode-' . $key] = $value;
			}
			$related_items_layout_options = array_merge(
				$related_items_layout_options,
				$tainacan_view_modes_options
			);
		}
		if ( method_exists('\Tainacan\Theme_Helper', 'get_tainacan_items_gallery') ) {
			$related_items_layout_options['gallery-slider'] = __('Gallery slider, documents with zoom and thumbnails', 'tainacan-interface');
			$related_items_layout_options['gallery-thumbs'] = __('Gallery carousel, thumbnails with zoom', 'tainacan-interface');
		}
		return $related_items_layout_options;
	}
endif; // tainacan_get_single_item_related_items_layout_options

if ( ! function_exists( 'tainacan_sanitize_single_item_related_items_layout_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme item page related items section layout options
	 *
	 * Create your own tainacan_sanitize_single_item_related_items_layout_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $option - a string with options for displaying the related items layout.
	 * @return string the selected option.
	 */
	function tainacan_sanitize_single_item_related_items_layout_options( $option ) {
		$related_items_layout_options = tainacan_get_single_item_related_items_layout_options();

		if ( ! array_key_exists( $option, $related_items_layout_options ) ) {
			return 'carousel';
		}

		return $option;
	}
endif; // tainacan_sanitize_single_item_related_items_layout_options


if ( ! function_exists( 'tainacan_get_single_item_related_items_order_options' ) ) :
	/**
	 * Retrieves an array of options for single item page related items sorting options for Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_single_item_related_items_order_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $option - a string with sorting options for displaying the related items query
	 */
	function tainacan_get_single_item_related_items_order_options() {
		$related_items_order_options = array(
			'title_asc' => __( 'Title A-Z', 'tainacan-interface'),
            'title_desc' => __( 'Title Z-A', 'tainacan-interface'),
            'date_asc' => __( 'Latest created last', 'tainacan-interface'),
            'date_desc' => __( 'Latest created first', 'tainacan-interface'),
            'modified_asc' => __( 'Latest modified last', 'tainacan-interface'),
            'modified_desc' => __( 'Latest modified first', 'tainacan-interface')
		);
		return $related_items_order_options;
	}
endif; // tainacan_get_single_item_related_items_order_options

if ( ! function_exists( 'tainacan_sanitize_single_item_related_items_order_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme item page related items section sorting options
	 *
	 * Create your own tainacan_sanitize_single_item_related_items_order_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $option - a string with sorting options for displaying the related items query.
	 * @return string the selected option.
	 */
	function tainacan_sanitize_single_item_related_items_order_options( $option ) {
		$related_items_order_options = tainacan_get_single_item_related_items_order_options();

		if ( ! array_key_exists( $option, $related_items_order_options ) ) {
			return 'title_asc';
		}

		return $option;
	}
endif; // tainacan_sanitize_single_item_related_items_order_options

if ( ! function_exists( 'tainacan_interface_is_related_items_section_enabled' ) ) :
	/**
	 * Whether the related items section is enabled.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_related_items_section_enabled( $control = null ) {
		unset( $control );
		return (bool) get_theme_mod( 'tainacan_single_item_enable_related_items_section', true );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_related_items_layout_carousel' ) ) :
	/**
	 * Whether related items use the carousel layout.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_related_items_layout_carousel( $control = null ) {
		if ( ! tainacan_interface_is_related_items_section_enabled( $control ) ) {
			return false;
		}

		return get_theme_mod( 'tainacan_single_item_related_items_layout', 'carousel' ) === 'carousel';
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_related_items_layout_grid_or_list' ) ) :
	/**
	 * Whether related items use the grid or list layout.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_related_items_layout_grid_or_list( $control = null ) {
		if ( ! tainacan_interface_is_related_items_section_enabled( $control ) ) {
			return false;
		}

		$layout = get_theme_mod( 'tainacan_single_item_related_items_layout', 'carousel' );
		return in_array( $layout, array( 'grid', 'list' ), true );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_related_items_using_gallery' ) ) :
	/**
	 * Whether related items use an items gallery layout.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_related_items_using_gallery( $control = null ) {
		if ( ! tainacan_interface_is_related_items_section_enabled( $control ) ) {
			return false;
		}

		$layout = get_theme_mod( 'tainacan_single_item_related_items_layout', 'carousel' );
		return in_array( $layout, array( 'gallery-slider', 'gallery-thumbs' ), true );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_related_items_gallery_thumbs_layout_list' ) ) :
	/**
	 * Whether related items gallery thumbnails use the list layout.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_related_items_gallery_thumbs_layout_list( $control = null ) {
		if ( ! tainacan_interface_is_related_items_using_gallery( $control ) ) {
			return false;
		}

		return get_theme_mod( 'tainacan_single_item_related_items_thumbs_layout', 'carousel' ) === 'list';
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_related_items_gallery_showing_images' ) ) :
	/**
	 * Whether related items gallery thumbnail size controls apply.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_related_items_gallery_showing_images( $control = null ) {
		if ( ! tainacan_interface_is_related_items_using_gallery( $control ) ) {
			return false;
		}

		if ( ! tainacan_interface_is_related_items_gallery_thumbs_layout_list( $control ) ) {
			return true;
		}

		return ! get_theme_mod( 'tainacan_single_item_related_items_hide_image_thumbnails', false );
	}
endif;


