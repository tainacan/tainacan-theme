<?php
/**
 * Functions that register the options for the customizer
 * related to posts listing settings
 *
 * @since 1.0.0
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_posts_list' ) ) {
	/**
	 * Registers the Customizer options related to posts listing settings.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_posts_list( $wp_customize ) {
		/**
		 * Checkbox to hide author information in posts listing
		 */
		$wp_customize->add_setting(
			'tainacan_hide_author_in_posts_listing',
			array(
				'type'              => 'theme_mod',
				'default'           => false,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'tainacan_hide_author_in_posts_listing',
			array(
				'type'        => 'checkbox',
				'settings'    => 'tainacan_hide_author_in_posts_listing',
				'section'     => 'tainacan_interface_opt_blog',
				'label'       => __( 'Hide author information', 'tainacan-interface' ),
				'description' => __( 'Toggle to hide or show author information in posts listing pages.', 'tainacan-interface' ),
			)
		);

		/**
		 * Checkbox to hide date information in posts listing
		 */
		$wp_customize->add_setting(
			'tainacan_hide_date_in_posts_listing',
			array(
				'type'              => 'theme_mod',
				'default'           => false,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'tainacan_hide_date_in_posts_listing',
			array(
				'type'        => 'checkbox',
				'settings'    => 'tainacan_hide_date_in_posts_listing',
				'section'     => 'tainacan_interface_opt_blog',
				'label'       => __( 'Hide date information', 'tainacan-interface' ),
				'description' => __( 'Toggle to hide or show date information in posts listing pages.', 'tainacan-interface' ),
			)
		);
	}

}
add_action( 'customize_register', 'tainacan_interface_customize_register_posts_list', 11 );
