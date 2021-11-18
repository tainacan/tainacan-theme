<?php

/**
 * Functions that register the options for the customizer
 * related to the header search settings
 *
 */
if ( !function_exists('tainacan_interface_customize_register_header_search') ) {

	function tainacan_interface_customize_register_header_search( $wp_customize ) {

		/**
		 * Adds section to control Header search settings
		 */
		$wp_customize->add_section('tainacan_header_search', array(
			'title'  	 => __( 'Header search', 'tainacan-interface' ),
			'priority'   => 61,
			'panel' 	 => 'tainacan_header_settings'
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
			'priority'  => '1',
			'section' 	=> 'tainacan_header_search',
			'label' => __( 'Hide search icon and input', 'tainacan-interface' )
		) );

		/* If the Tainacan plugin is installed */
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
				'description' => __( 'Includes all kinds of post types. This option will only be visible if at least one of the below are selected.', 'tainacan-interface')
				) );

			/**
			 * Adds option to change the order of some page sections
			 */
			$wp_customize->add_setting( 'tainacan_search_default_option', array(
				'type' 		 => 'theme_mod',
				'capability' => 'edit_theme_options',
				'default' 	 => 'global',
				'transport'  => 'refresh',
				'sanitize_callback' => 'tainacan_sanitize_search_options',
				) );
			$wp_customize->add_control( 'tainacan_search_default_option', array(
				'type' 	   	  => 'select',
				'priority' 	  => 9, // Within the section.
				'settings'	  => 'tainacan_search_default_option',
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Default search option', 'tainacan-interface' ),
				'description' => __( 'This option will only be valid if at least one of the below are selected, otherwise the default search happens on WordPress posts.', 'tainacan-interface'),
				'choices'	  => tainacan_get_search_options()
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
				'label'		=> __( 'Display option to search on Tainacan items repository list', 'tainacan-interface' )
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
				'label'		=> __( 'Display option to search on Tainacan collections list', 'tainacan-interface' )
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

	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_header_search', 11 );
}


if ( ! function_exists( 'tainacan_get_search_options' ) ) :
	/**
	 * Retrieves an array of options for the header search on Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_search_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $order - a string with slugs to the section order, separated by hiphen.
	 */
	function tainacan_get_search_options() {
		$search_options = array(
			'global' => __('Global', 'tainacan-interface'),
			'posts' => __('Posts', 'tainacan-interface'),
			'pages' => __('Pages', 'tainacan-interface'),
			'tainacan-items' => __('Items', 'tainacan-interface'),
			'tainacan-collections' => __('Collections', 'tainacan-interface'),
		);
		return $search_options;
	}
endif; // tainacan_get_search_options

if ( ! function_exists( 'tainacan_sanitize_search_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme search options
	 *
	 * Create your own tainacan_sanitize_search_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @param string $option - a string with slugs to the search option
	 * @return string the selected search option.
	 */
	function tainacan_sanitize_search_options( $option ) {
		$search_options = tainacan_get_search_options();

		if ( ! array_key_exists( $option, $search_options) ) {
			return 'global';
		}

		return $option;
	}
endif; // tainacan_sanitize_search_options
