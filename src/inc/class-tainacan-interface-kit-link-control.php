<?php
/**
 * The button in Customize that opens the Kit for AI. Loaded inside
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
class Tainacan_Interface_Kit_Link_Control extends WP_Customize_Control {

	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'tainacan-interface-kit-link';

	/**
	 * The button.
	 */
	public function render_content() {
		echo '<p><a class="button button-primary" target="_top" href="' . esc_url( tainacan_interface_kit_page_url() ) . '">' . esc_html__( 'Open the Kit for AI', 'tainacan-interface' ) . '</a></p>';
	}
}
