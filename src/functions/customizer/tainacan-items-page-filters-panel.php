<?php

/**
 * Functions that register the options for the customizer
 * related to the Tainacan Items page - Filters panel settings.
 * 
 */
if ( !function_exists('tainacan_interface_customize_register_tainacan_items_page_filters_panel') ) {
	
	function tainacan_interface_customize_register_tainacan_items_page_filters_panel( $wp_customize ) {
		
        /* If the Tainacan plugin is installed */
		if (defined ( 'TAINACAN_VERSION' ) && version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {
			
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
				'description' => __( 'Toggle to not show the "Hide filters" button for users.', 'tainacan-interface' )
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
				'description' => __( 'Toggle to display the Filters button inside the search control bar.', 'tainacan-interface' )
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
					'default' 	 => false,
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
					) );
				$wp_customize->add_control( 'tainacan_items_page_filters_fixed_on_scroll', array(
					'type' 	   	  => 'checkbox',
					'priority' 	  => 10, // Within the section.
					'section'  	  => 'tainacan_items_page_filters_panel',
					'label'    	  => __( 'Filters side panel fixed on scroll', 'tainacan-interface' ),
					'description' => __( 'Toggle to if you want filters panel to get fixed on screen when scrolling down the items list. This will only take effect if the items list itself is taller than the screen height.', 'tainacan-interface' )
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

			if (version_compare(TAINACAN_VERSION, '0.21.7') >= 0) {
				/**
				 * Adds option to disable the behavior of hiding filters on mobile inside a modal.
				 */
				$wp_customize->add_setting( 'tainacan_items_page_should_not_hide_filters_on_mobile', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => false,
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
					) );
				$wp_customize->add_control( 'tainacan_items_page_should_not_hide_filters_on_mobile', array(
					'type' 	   	  => 'checkbox',
					'priority' 	  => 10, // Within the section.
					'section'  	  => 'tainacan_items_page_filters_panel',
					'label'    	  => __( 'Should not hide filters even on mobile', 'tainacan-interface' ),
					'description' => __( 'Toggle to keep filters area visible even on small screen sizes.', 'tainacan-interface' )
					) );

				/**
				 * Adds option to display filters horizontally.
				 */
				$wp_customize->add_setting( 'tainacan_items_page_display_filters_horizontally', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => false,
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
					) );
				$wp_customize->add_control( 'tainacan_items_page_display_filters_horizontally', array(
					'type' 	   	  => 'checkbox',
					'priority' 	  => 10, // Within the section.
					'section'  	  => 'tainacan_items_page_filters_panel',
					'label'    	  => __( 'Display filters horizontally', 'tainacan-interface' ),
					'description' => __( 'Toggle to show filters in an horizontal pane above the search control instead of a vertical sidebar. This layout fits better with select and textual input filters. Must not to be used combined with "Filters as a modal".', 'tainacan-interface' )
					) );

				/**
				 * Adds option to hide the "Collapse all" filters button.
				 */
				$wp_customize->add_setting( 'tainacan_items_page_hide_filter_collapses', array(
					'type' 		 => 'theme_mod',
					'capability' => 'edit_theme_options',
					'default' 	 => false,
					'transport'  => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox'
					) );
				$wp_customize->add_control( 'tainacan_items_page_hide_filter_collapses', array(
					'type' 	   	  => 'checkbox',
					'priority' 	  => 10, // Within the section.
					'section'  	  => 'tainacan_items_page_filters_panel',
					'label'    	  => __( 'Hide filter collapses button', 'tainacan-interface' ),
					'description' => __( 'Toggle to not display each filter label as a collapsable button. This is suggested when you have a small amount of filters.', 'tainacan-interface' )
					) );
			}

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
        }
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_items_page_filters_panel', 11 );
}


/**
 * Enqueues front-end CSS for the items page fixed filters logic.
 *
 * @since Tainacan Interface theme
 *
 * @see wp_add_inline_style()
 */
function tainacan_items_page_filters_fixed_on_scroll_output() {
    if (!defined('TAINACAN_VERSION')) {
        return;
    }
	$should_use_fixed_filters_logic = (version_compare(TAINACAN_VERSION, '0.17RC') >= 0) && get_theme_mod( 'tainacan_items_page_filters_fixed_on_scroll', false );
	
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
		z-index: 9;
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
