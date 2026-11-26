<?php
/**
 * Functions that register the options for the customizer
 * related to the Tainacan plugin Item single page - Metadata list settings.
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_tainacan_single_item_page_metadata' ) ) {
	/**
	 * Registers the Customizer options related to the Tainacan plugin Item single page - Metadata list settings.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_tainacan_single_item_page_metadata( $wp_customize ) {
		/* If the Tainacan plugin is installed */
		if ( defined( 'TAINACAN_VERSION' ) ) {
			/**
			 * Adds option to configure Metadata section label.
			 */
			$wp_customize->add_setting(
				'tainacan_single_item_metadata_section_label',
				array(
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '',
					'transport'         => 'postMessage',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'tainacan_single_item_metadata_section_label',
				array(
					'type'        => 'text',
					'priority'    => 0, // Within the section.
					'section'     => 'tainacan_interface_opt_item',
					'label'       => __( 'Label for the "Metadata" section', 'tainacan-interface' ),
					'description' => __( 'Leave it blank to hide the label (which is the default).', 'tainacan-interface' ),
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'tainacan_single_item_metadata_section_label',
				array(
					'selector'         => '#single-item-metadata-label',
					'render_callback'  => '__return_false',
					'fallback_refresh' => true,
				)
			);

			/**
			 * Adds options to display or not share buttons on items page.
			 */
			$wp_customize->add_setting(
				'tainacan_single_item_display_share_buttons',
				array(
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => true,
					'transport'         => 'postMessage',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
				)
			);
			$wp_customize->add_control(
				'tainacan_single_item_display_share_buttons',
				array(
					'type'        => 'checkbox',
					'priority'    => 3, // Within the section.
					'section'     => 'tainacan_interface_opt_item',
					'label'       => __( 'Display share buttons', 'tainacan-interface' ),
					'description' => __( 'Show social share buttons in the metadata list or collection banner.', 'tainacan-interface' ),
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'tainacan_single_item_display_share_buttons',
				array(
					'selector'         => '.tainacan-item-share-container',
					'render_callback'  => '__return_false',
					'fallback_refresh' => true,
				)
			);

			/**
			 * Adds options to hide or no the core metadata in the metadata list.
			 */
			$wp_customize->add_setting(
				'tainacan_single_item_hide_core_title_metadata',
				array(
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => true,
					'transport'         => 'postMessage',
					'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
				)
			);
			$wp_customize->add_control(
				'tainacan_single_item_hide_core_title_metadata',
				array(
					'type'        => 'checkbox',
					'priority'    => 2, // Within the section.
					'section'     => 'tainacan_interface_opt_item',
					'label'       => __( 'Hide core title from metadata list', 'tainacan-interface' ),
					'description' => __( 'Hide the core title from the metadata list, since it already appears as the page title.', 'tainacan-interface' ),
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'tainacan_single_item_hide_core_title_metadata',
				array(
					'selector'         => '.metadata-type-core_title',
					'render_callback'  => '__return_false',
					'fallback_refresh' => true,
				)
			);

		}
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_tainacan_single_item_page_metadata', 11 );
}
