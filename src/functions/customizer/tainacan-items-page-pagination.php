<?php
/**
 * Functions that register the options for the customizer
 * related to the Tainacan Items page - Pagination area settings.
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_tainacan_items_page_pagination' ) ) {

	/**
	 * Registers the Customizer options related to the Tainacan Items page - Pagination area settings.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_tainacan_items_page_pagination( $wp_customize ) {

		/* If the Tainacan plugin is installed */
		if ( defined( 'TAINACAN_VERSION' ) && version_compare( TAINACAN_VERSION, '0.16RC' ) >= 0 ) {

			/**
			 * Adds option to change default value of items per page.
			 */
			$tainacan_api_max_items_per_page = isset( $GLOBALS['TAINACAN_API_MAX_ITEMS_PER_PAGE'] ) ? $GLOBALS['TAINACAN_API_MAX_ITEMS_PER_PAGE'] : null;

			$wp_customize->add_setting(
				'tainacan_items_page_default_items_per_page',
				array(
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => 12,
					'transport'         => 'refresh',
					'sanitize_callback' => 'tainacan_interface_sanitize_number',
				)
			);
			$wp_customize->add_control(
				'tainacan_items_page_default_items_per_page',
				array(
					'type'        => 'number',
					'section'     => 'tainacan_items_page_pagination',
					'label'       => __( 'Default number of items per page', 'tainacan-interface' ),
					'description' => __( 'Change the default value for items loaded per page. Note that this affects loading duration.', 'tainacan-interface' ),
					'input_attrs' => array(
						'min'  => 1,
						'max'  => $tainacan_api_max_items_per_page,
						'step' => 1,
					),
				)
			);

			/**
			 * Adds option to hide the "Items per Page" button on every items list.
			 */
			$wp_customize->add_setting(
				'tainacan_items_page_hide_items_per_page_button',
				array(
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => false,
					'transport'         => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
				)
			);
			$wp_customize->add_control(
				'tainacan_items_page_hide_items_per_page_button',
				array(
					'type'        => 'checkbox',
					'priority'    => 6, // Within the section.
					'section'     => 'tainacan_items_page_pagination',
					'label'       => __( 'Hide the "Items per Page" button.', 'tainacan-interface' ),
					'description' => __( 'Toggle to not show the "Items per Page" button on the pagination bar.', 'tainacan-interface' ),
				)
			);

			/**
			 * Adds option to hide the "Go to Page" button on every items list.
			 */
			$wp_customize->add_setting(
				'tainacan_items_page_hide_go_to_page_button',
				array(
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => false,
					'transport'         => 'refresh',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
				)
			);
			$wp_customize->add_control(
				'tainacan_items_page_hide_go_to_page_button',
				array(
					'type'        => 'checkbox',
					'priority'    => 7, // Within the section.
					'section'     => 'tainacan_items_page_pagination',
					'label'       => __( 'Hide the "Go to Page" button.', 'tainacan-interface' ),
					'description' => __( 'Toggle to not show the "Go to Page" button on the pagination bar.', 'tainacan-interface' ),
				)
			);

		}
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_items_page_pagination', 11 );
}
