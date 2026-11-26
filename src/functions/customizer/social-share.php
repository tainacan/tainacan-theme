<?php
/**
 * Functions that register the options for the customizer
 * related to the social share icons.
 *
 * @package tainacan-interface
 */

if ( ! function_exists( 'tainacan_interface_customize_register_social_share_options' ) ) {
	/**
	 * Registers the Customizer options related to the social share icons.
	 *
	 * @param WP_Customize_Manager $wp_customize The Customizer object.
	 */
	function tainacan_interface_customize_register_social_share_options( $wp_customize ) {
		// Facebook.
		$wp_customize->add_setting(
			'tainacan_facebook_share',
			array(
				'type'              => 'theme_mod',
				'default'           => true,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'tainacan_facebook_share',
			array(
				'type'     => 'checkbox',
				'settings' => 'tainacan_facebook_share',
				'section'  => 'tainacan_interface_opt_share',
				'label'    => __( 'Display Facebook button', 'tainacan-interface' ),
			)
		);

		// Twitter.
		$wp_customize->add_setting(
			'tainacan_twitter_share',
			array(
				'type'              => 'theme_mod',
				'default'           => true,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'tainacan_twitter_share',
			array(
				'type'     => 'checkbox',
				'settings' => 'tainacan_twitter_share',
				'section'  => 'tainacan_interface_opt_share',
				'label'    => __( 'Display Twitter button', 'tainacan-interface' ),
			)
		);

		$wp_customize->add_setting(
			'tainacan_twitter_user',
			array(
				'type'              => 'theme_mod',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'tainacan_twitter_user',
			array(
				'label'           => __( 'Twitter username to cite in tweets (via @user)', 'tainacan-interface' ),
				'section'         => 'tainacan_interface_opt_share',
				'active_callback' => 'tainacan_interface_is_twitter_share_enabled',
			)
		);

		// WhatsApp.
		$wp_customize->add_setting(
			'tainacan_whatsapp_share',
			array(
				'type'              => 'theme_mod',
				'default'           => false,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'tainacan_whatsapp_share',
			array(
				'type'     => 'checkbox',
				'settings' => 'tainacan_whatsapp_share',
				'section'  => 'tainacan_interface_opt_share',
				'label'    => __( 'Display WhatsApp button', 'tainacan-interface' ),
			)
		);

		// Telegram.
		$wp_customize->add_setting(
			'tainacan_telegram_share',
			array(
				'type'              => 'theme_mod',
				'default'           => false,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'tainacan_telegram_share',
			array(
				'type'     => 'checkbox',
				'settings' => 'tainacan_telegram_share',
				'section'  => 'tainacan_interface_opt_share',
				'label'    => __( 'Display Telegram button', 'tainacan-interface' ),
			)
		);
	}
	add_action( 'customize_register', 'tainacan_interface_customize_register_social_share_options', 11 );
}

if ( ! function_exists( 'tainacan_interface_is_twitter_share_enabled' ) ) :
	/**
	 * Whether Twitter sharing is enabled.
	 *
	 * @param WP_Customize_Control $control Customizer control.
	 * @return bool
	 */
	function tainacan_interface_is_twitter_share_enabled( $control = null ) {
		unset( $control );
		return (bool) get_theme_mod( 'tainacan_twitter_share', true );
	}
endif;
