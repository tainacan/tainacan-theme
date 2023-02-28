<?php
/**
 * Customizer functionality
 */

/**
 * Register Customizer color.
 *
 * @since Tainacan Interface theme
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function tainacan_customize_register( $wp_customize ) {

	/**
	 * Adds panel to control header settings. ---------------------------------------------------------
	 */
	$wp_customize->add_panel( 'tainacan_header_settings', array(
		'title' 	  => __( 'Site header settings', 'tainacan-interface' ),
		'description' => __( 'Settings related to the site header, such as the menu and logo position.', 'tainacan-interface' ),
		'priority' 	  => 40, // Mixed with top-level-section hierarchy.,
		'capability'  => 'edit_theme_options'
	) );


	/**
	 * Below are customizer options exclusivelly related to Tainacan pages.
	 */
	if ( defined ( 'TAINACAN_VERSION' ) ) {

		/**
		 * Adds panel to control single items page. ---------------------------------------------------------
		 */
		$wp_customize->add_panel( 'tainacan_single_item_page', array(
			'title' 	  => __( 'Tainacan single item page', 'tainacan-interface' ),
			'description' => __( 'Settings related to Tainacan single Items page only.', 'tainacan-interface' ),
			'priority' 	  => 160, // Mixed with top-level-section hierarchy.,
			'capability'  => 'edit_theme_options'
		) );


		if (version_compare(TAINACAN_VERSION, '0.16RC') >= 0) {

			/**
			 * Adds section to control collection items page. ---------------------------------------------------------
			 */
			$wp_customize->add_panel( 'tainacan_items_page', array(
				'title' 	  => __( 'Tainacan items list page', 'tainacan-interface' ),
				'description' => __( 'Settings related to Tainacan items list pages, such as the repository items list, the collection item list and the term items list. Some settings ins this section may be overrided by collection settings or user preference.', 'tainacan-interface' ),
				'priority' 	  => 160 // Mixed with top-level-section hierarchy.,
			) );

		}
	}

}
add_action( 'customize_register', 'tainacan_customize_register', 11 );

/**
 * Callback to Sanitize Checkboxes.
 */
function tainacan_callback_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && true == $checked ) ? true : false );
}


/**
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since Tainacan Interface theme
 */
function tainacan_customize_preview_js() {
	wp_enqueue_script( 'tainacan-customize-preview', get_template_directory_uri() . '/assets/js/customize-preview.js', array( 'customize-preview' ), TAINACAN_INTERFACE_VERSION , true );
}
add_action( 'customize_preview_init', 'tainacan_customize_preview_js' );


/* Requires remaining customizer options */
require get_template_directory() . '/functions/customizer/header-image.php';
require get_template_directory() . '/functions/customizer/header-general.php';
require get_template_directory() . '/functions/customizer/header-search.php';
require get_template_directory() . '/functions/customizer/colors.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-general.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-metadata.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-document.php';
require get_template_directory() . '/functions/customizer/tainacan-single-item-page-related-items.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-collection-banner.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-search-area.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-filters-panel.php';
require get_template_directory() . '/functions/customizer/tainacan-items-page-pagination.php';
require get_template_directory() . '/functions/customizer/footer-info.php';
require get_template_directory() . '/functions/customizer/social-share.php';
