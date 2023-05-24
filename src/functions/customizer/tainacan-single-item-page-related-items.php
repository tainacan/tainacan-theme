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
				'priority' 	  => 2, // Within the section.
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
				'priority' 	  => 0, // Within the section.
				'section'  	  => 'tainacan_single_item_page_related_items',
				'label'    	  => __( 'Enable the "Items related to this" section', 'tainacan-interface' ),
				'description' => __( 'Toggle to display or not the "Items related to this" section. This also depends on the collection settings and existence of items related to the current one.', 'tainacan-interface' )
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
					'priority' 	  => 3, // Within the section.
					'section'  	  => 'tainacan_single_item_page_related_items',
					'label'    	  => __( 'Layout for the related items list', 'tainacan-interface' ),
					'choices'	  => tainacan_get_single_item_related_items_layout_options()
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
					'choices'	  => tainacan_get_single_item_related_items_order_options()
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
					'description' => __( 'Sets how many columns of items slides will appear (on a large screen) for the layouts "grid" and "list". In the "grid" layout, the smaller this number is, the greater the item thumbnail will be.', 'tainacan-interface' ),
					'input_attrs' => array(
						'min' => 1,
						'max' => 8,
						'step' => 1
					),
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
				'description' => __( 'Sets how many slides per row of the carousel will appear (on a large screen) for the layout "carousel". The smaller this number is, the greater the item thumbnail will be.', 'tainacan-interface' ),
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


