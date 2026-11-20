<?php
/**
 * Functions that register the options for the customizer
 * related to the Tainacan plugin Item single page - General layout settings.
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_tainacan_single_item_page_general' ) ) {
	/**
	 * Registers the Customizer options related to the Tainacan plugin Item single page - General layout settings.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_tainacan_single_item_page_general( $wp_customize ) {
		/* If the Tainacan plugin is installed */
		if ( defined( 'TAINACAN_VERSION' ) ) {
			if ( version_compare( TAINACAN_VERSION, '0.16RC' ) >= 0 ) {
				/**
				 * Adds option to display Collection banner on the item single page.
				 */
				$wp_customize->add_setting(
					'tainacan_single_item_collection_header',
					array(
						'type'              => 'theme_mod',
						'capability'        => 'edit_theme_options',
						'default'           => false,
						'transport'         => 'postMessage',
						'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
					)
				);
				$wp_customize->add_control(
					'tainacan_single_item_collection_header',
					array(
						'type'        => 'checkbox',
						'priority'    => 0, // Within the section.
						'section'     => 'tainacan_interface_opt_item',
						'label'       => __( 'Display a header of the related collection.', 'tainacan-interface' ),
						'description' => __( 'Toggle to show a banner with name, thumbnail and color of the related collection.', 'tainacan-interface' ),
					)
				);
				$wp_customize->selective_refresh->add_partial(
					'tainacan_single_item_collection_header',
					array(
						'selector'         => '.tainacan-single-item-heading',
						'render_callback'  => '__return_false',
						'fallback_refresh' => true,
					)
				);

				/**
				 * Adds option to configure Item Navigation section label.
				 */
				$wp_customize->add_setting(
					'tainacan_single_item_navigation_section_label',
					array(
						'type'              => 'theme_mod',
						'capability'        => 'edit_theme_options',
						'default'           => __( 'Continue browsing', 'tainacan-interface' ),
						'transport'         => 'postMessage',
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					'tainacan_single_item_navigation_section_label',
					array(
						'type'            => 'text',
						'priority'        => 4, // Within the section.
						'section'         => 'tainacan_interface_opt_item_nav',
						'label'           => __( 'Label for the "Items navigation" or "Continue browsing" section', 'tainacan-interface' ),
						'description'     => __( 'Leave it blank to hide the label.', 'tainacan-interface' ),
						'active_callback' => 'tainacan_interface_is_item_navigation_enabled',
					)
				);
				$wp_customize->selective_refresh->add_partial(
					'tainacan_single_item_navigation_section_label',
					array(
						'selector'         => '#single-item-navigation-label',
						'render_callback'  => '__return_false',
						'fallback_refresh' => true,
					)
				);

				/**
				 * Adds options to display previous/next links on item single page.
				 */
				$wp_customize->add_setting(
					'tainacan_single_item_navigation_options',
					array(
						'type'              => 'theme_mod',
						'capability'        => 'edit_theme_options',
						'default'           => 'thumbnail_small',
						'transport'         => 'refresh',
						'sanitize_callback' => 'tainacan_sanitize_single_item_navigation_links_options',
					)
				);
				$wp_customize->add_control(
					'tainacan_single_item_navigation_options',
					array(
						'type'        => 'select',
						'priority'    => 3, // Within the section.
						'section'     => 'tainacan_interface_opt_item_nav',
						'label'       => __( 'Navigation links to adjacent items', 'tainacan-interface' ),
						'description' => __( 'Sets how next and previous item links will be displayed. If your Tainacan version is below 0.17, these links only follow creation date order inside their collection.', 'tainacan-interface' ),
						'choices'     => tainacan_get_single_item_navigation_links_options(),
					)
				);

				/**
				 * Adds options to hide item naviagtion options.
				 */
				$wp_customize->add_setting(
					'tainacan_single_item_show_navigation_options',
					array(
						'type'              => 'theme_mod',
						'capability'        => 'edit_theme_options',
						'default'           => false,
						'transport'         => 'refresh',
						'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
					)
				);
				$wp_customize->add_control(
					'tainacan_single_item_show_navigation_options',
					array(
						'type'        => 'checkbox',
						'priority'    => 2, // Within the section.
						'section'     => 'tainacan_interface_opt_item_nav',
						'label'       => __( 'Show the item navigation options in the breadcrumb section', 'tainacan-interface' ),
						'description' => __( 'Toggle to display two arrows and a list icon for navigating directly from the item page breadcrumb section.', 'tainacan-interface' ),
					)
				);
				$wp_customize->selective_refresh->add_partial(
					'tainacan_single_show_hide_navigation_options',
					array(
						'selector'         => '#breadcrumb-single-item-pagination',
						'render_callback'  => '__return_false',
						'fallback_refresh' => true,
					)
				);

			}
		}
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_single_item_page_general', 11 );
}

if ( ! function_exists( 'tainacan_get_single_item_navigation_links_options' ) ) :
	/**
	 * Retrieves an array of options for single item page navigation options for Tainacan Interface theme.
	 *
	 * Create your own tainacan_get_single_item_navigation_links_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
	 *
	 * @return array $option - a string with options for displaying the naviation links.
	 */
	function tainacan_get_single_item_navigation_links_options() {
		$navigation_options = array(
			'none'            => __( 'Do not display item links', 'tainacan-interface' ),
			'link'            => __( 'Show only item links', 'tainacan-interface' ),
			'thumbnail_small' => __( 'Show item links with a small thumbnail', 'tainacan-interface' ),
			'thumbnail_large' => __( 'Show item links with a large thumbnail', 'tainacan-interface' ),
		);
		return $navigation_options;
	}
endif; // tainacan_get_single_item_navigation_links_options.

if ( ! function_exists( 'tainacan_sanitize_single_item_navigation_links_options' ) ) :
	/**
	 * Handles sanitization for Tainacan Interface theme item page navigation link options
	 *
	 * Create your own tainacan_sanitize_single_item_navigation_links_options() function to override
	 * in a child theme.
	 *
	 * @since Tainacan Interface theme
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
endif; // tainacan_sanitize_single_item_navigation_links_options.

if ( ! function_exists( 'tainacan_interface_is_item_navigation_enabled' ) ) :
	/**
	 * Whether adjacent item navigation links are displayed.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_item_navigation_enabled( $control = null ) {
		unset( $control );
		return get_theme_mod( 'tainacan_single_item_navigation_options', 'thumbnail_small' ) !== 'none';
	}
endif;
