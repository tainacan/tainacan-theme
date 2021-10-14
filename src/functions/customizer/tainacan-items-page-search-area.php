<?php

/**
 * Functions that register the options for the customizer
 * related to the Tainacan Items page - Search area settings.
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_tainacan_items_page_search_area') ) {
	
	function tainacan_interface_customize_register_tainacan_items_page_search_area( $wp_customize ) {
		
        /* If the Tainacan plugin is installed */
		if (defined ( 'TAINACAN_VERSION' ) && version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {
			
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
        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_items_page_search_area', 11 );
}


/**
 * Retrieves the current registered view modes on Tainacan plugin and filter some options to offer as default
 *
 * @since Tainacan Interface theme
 *
 * @return array An associative array with view mode options and the default one
 */
function tainacan_get_default_view_mode_choices() {
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
	 * Handles sanitization for Tainacan Interface theme items repository list default view mode.
	 *
	 * Create your own tainacan_sanitize_items_repository_list_default_view_mode() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
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
