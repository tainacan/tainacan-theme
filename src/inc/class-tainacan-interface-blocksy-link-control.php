<?php
/**
 * The button in Customize that opens the import from Blocksy. Loaded inside
 * customize_register, when WP_Customize_Control exists.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The button in Customize that opens the page.
 */
class Tainacan_Interface_Blocksy_Link_Control extends WP_Customize_Control {

	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'tainacan-interface-blocksy-link';

	/**
	 * The button and what it does.
	 */
	public function render_content() {
		$sources = tainacan_interface_blocksy_site_sources();
		echo '<p>';
		if ( $sources ) {
			esc_html_e( 'This site still has the settings of Blocksy: the import can start from them.', 'tainacan-interface' );
		} else {
			esc_html_e( 'Send the file Blocksy exports (Manage Options > Export Customizations), from this or another site.', 'tainacan-interface' );
		}
		echo '</p><p><a class="button button-primary" target="_top" href="' . esc_url( tainacan_interface_blocksy_page_url() ) . '">' . esc_html__( 'Open the importer', 'tainacan-interface' ) . '</a></p>';
	}
}
