<?php
/**
 * {{THEME_NAME}}: child theme of Tainacan Interface.
 *
 * Made from the starter of the Kit for AI of Tainacan Interface {{PARENT_VERSION}} ({{DATE}}).
 * The standard it follows is in ai-kit/docs/02-tema-filho.md of Tainacan Interface:
 * options and tokens before CSS, hooks before copies of templates, patterns
 * with blocks that work when written by hand.
 *
 * @package {{THEME_SLUG}}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translations of this child theme (languages/<locale>.mo).
 */
function starter_child_setup() {
	load_child_theme_textdomain( '{{THEME_SLUG}}', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'starter_child_setup' );

/**
 * Identity of the site: the starting values of the options of Tainacan
 * Interface. Customize still changes every one of them; this only decides
 * what a site without its own choice shows. Keys and accepted values:
 * ai-kit/reference/options.json of Tainacan Interface.
 *
 * @param array $schema Options of Tainacan Interface.
 * @return array
 */
function starter_child_defaults( $schema ) {
	$defaults = array(
		// 'brand_color'  => '#1c355e',
		// 'accent_color' => '#5b4fcf',
		// 'font_heading' => 'playfair',
		// 'font_ui'      => 'inter',
		// 'radius'       => 12,
	);
	foreach ( $defaults as $key => $value ) {
		if ( isset( $schema[ $key ] ) ) {
			$schema[ $key ]['default'] = $value;
		}
	}
	return $schema;
}
add_filter( 'tainacan_interface_settings_schema', 'starter_child_defaults' );

/**
 * Category of the block patterns of this theme (the files in patterns/ are
 * registered by WordPress).
 */
function starter_child_pattern_category() {
	register_block_pattern_category( '{{THEME_SLUG}}', array( 'label' => __( 'Site sections', '{{THEME_SLUG}}' ) ) );
}
add_action( 'init', 'starter_child_pattern_category' );

/*
 * Hooks of Tainacan Interface: the way to add something to its pages without
 * copying a template (list in ai-kit/reference/hooks.md). Example:
 *
 * add_action( 'tainacan-interface-single-item-after-metadata', function () {
 *     echo '<p class="{{THEME_SLUG}}-note">' . esc_html__( 'Questions about this item? Write to us.', '{{THEME_SLUG}}' ) . '</p>';
 * } );
 */
