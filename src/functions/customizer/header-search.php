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
			'priority'  => 1,
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
				'priority' 	  => 10,
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Label for the "Global" search option', 'tainacan-interface' ),
				'description' => __( 'Includes all kinds of post types. Visible on the frontend when at least one search type above is selected.', 'tainacan-interface'),
				'active_callback' => 'tainacan_interface_is_header_search_visible'
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
				'priority' 	  => 11, // Within the section.
				'settings'	  => 'tainacan_search_default_option',
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Default search option', 'tainacan-interface' ),
				'description' => __( 'Used when at least one search type above is selected. Otherwise the default search happens on WordPress posts.', 'tainacan-interface'),
				'choices'	  => tainacan_get_search_options(),
				'active_callback' => 'tainacan_interface_is_header_search_visible'
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
				'priority'  => 2,
				'section' 	=> 'tainacan_header_search',
				'label'		=> __( 'Show option to search Tainacan items', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_visible'
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
				'priority' 	  => 3,
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Label for the "items" search option', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_on_items'
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
				'priority'  => 4,
				'section' 	=> 'tainacan_header_search',
				'label'		=> __( 'Show option to search Tainacan collections', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_visible'
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
				'priority' 	  => 5,
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Label for the "Collections" search option', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_on_collections'
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
				'priority'  => 6,
				'section' 	=> 'tainacan_header_search',
				'label'		=> __( 'Show option to search WordPress posts', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_visible'
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
				'priority' 	  => 7,
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Label for the "Posts" search option', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_on_posts'
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
				'priority'  => 8,
				'section' 	=> 'tainacan_header_search',
				'label'		=> __( 'Show option to search WordPress pages', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_visible'
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
				'priority' 	  => 9,
				'section'  	  => 'tainacan_header_search',
				'label'    	  => __( 'Label for the "Pages" search option', 'tainacan-interface' ),
				'active_callback' => 'tainacan_interface_is_header_search_on_pages'
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

if ( ! function_exists( 'tainacan_interface_is_header_search_visible' ) ) :
	/**
	 * Whether the header search icon and input are not hidden.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_search_visible( $control = null ) {
		unset( $control );
		return ! get_theme_mod( 'tainacan_hide_search_input', false );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_header_search_on_items' ) ) :
	/**
	 * Whether the items search option is enabled in the header.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_search_on_items( $control = null ) {
		return tainacan_interface_is_header_search_visible( $control ) && get_theme_mod( 'tainacan_search_on_items', false );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_header_search_on_collections' ) ) :
	/**
	 * Whether the collections search option is enabled in the header.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_search_on_collections( $control = null ) {
		return tainacan_interface_is_header_search_visible( $control ) && get_theme_mod( 'tainacan_search_on_collections', false );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_header_search_on_posts' ) ) :
	/**
	 * Whether the posts search option is enabled in the header.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_search_on_posts( $control = null ) {
		return tainacan_interface_is_header_search_visible( $control ) && get_theme_mod( 'tainacan_search_on_posts', false );
	}
endif;

if ( ! function_exists( 'tainacan_interface_is_header_search_on_pages' ) ) :
	/**
	 * Whether the pages search option is enabled in the header.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_header_search_on_pages( $control = null ) {
		return tainacan_interface_is_header_search_visible( $control ) && get_theme_mod( 'tainacan_search_on_pages', false );
	}
endif;
