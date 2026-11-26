<?php
/**
 * Blocks that an AI (or a person) can write as plain markup and that work on
 * any site: a search of the collection rendered by the server, styles for the
 * query loop (carousel, cards) and for groups, the colors of the theme in the
 * palette, the design tokens inside the editor, and the rebuild of Tainacan
 * blocks whose HTML was not made by the editor.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the blocks of the theme and the block styles.
 */
function tainacan_interface_register_blocks() {
	register_block_type( get_template_directory() . '/blocks/search' );

	wp_register_style( 'tainacan-interface-block-styles', get_template_directory_uri() . '/assets/css/block-styles.css', array(), TAINACAN_INTERFACE_VERSION );
	$styles = array(
		'core/post-template' => array(
			'tt2-carousel' => __( 'Carousel (theme)', 'tainacan-interface' ),
			'tt2-cards'    => __( 'Cards (theme)', 'tainacan-interface' ),
		),
		'core/group'         => array(
			'tt2-panel' => __( 'Panel (theme)', 'tainacan-interface' ),
			'tt2-band'  => __( 'Band on top (theme)', 'tainacan-interface' ),
		),
		'core/cover'         => array(
			'tt2-veil' => __( 'Brand veil (theme)', 'tainacan-interface' ),
		),
	);
	foreach ( $styles as $block => $list ) {
		foreach ( $list as $name => $label ) {
			register_block_style(
				$block,
				array(
					'name'         => $name,
					'label'        => $label,
					'style_handle' => 'tainacan-interface-block-styles',
				)
			);
		}
	}
}
// After the theme loads its translations (init, priority 10), so block.json comes translated.
add_action( 'init', 'tainacan_interface_register_blocks', 20 );

/**
 * Address the search of the block sends to: the list of a collection, or the
 * list of all items of Tainacan.
 *
 * @param int $collection_id Collection; 0 for all items.
 * @return string
 */
function tainacan_interface_search_block_action( $collection_id ) {
	if ( $collection_id > 0 && 'publish' === get_post_status( $collection_id ) && class_exists( '\Tainacan\Repositories\Collections' ) ) {
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( $collection_id );
		if ( $collection instanceof \Tainacan\Entities\Collection ) {
			$url = get_post_type_archive_link( $collection->get_db_identifier() );
			if ( $url ) {
				return $url;
			}
		}
	}
	if ( class_exists( '\Tainacan\Theme_Helper' ) ) {
		return home_url( '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug() . '/' );
	}
	return home_url( '/' );
}

/**
 * The palette a filter of theme.json has to start from. theme.json of this
 * theme has no palette: the colors come from add_theme_support(), and WordPress
 * lets a palette in theme.json replace them whole. A filter that adds colors to
 * an empty list would erase Default, Carmine, White... and the color of every
 * block painted with them.
 *
 * @param array $data Data of theme.json.
 * @return array<int, array{slug: string, name: string, color: string}>
 */
function tainacan_interface_palette_base( $data ) {
	if ( isset( $data['settings']['color']['palette']['theme'] ) && is_array( $data['settings']['color']['palette']['theme'] ) && $data['settings']['color']['palette']['theme'] ) {
		return $data['settings']['color']['palette']['theme'];
	}
	$support = get_theme_support( 'editor-color-palette' );
	$base    = array();
	foreach ( is_array( $support ) ? (array) current( $support ) : array() as $color ) {
		if ( isset( $color['slug'], $color['color'] ) ) {
			$base[] = array(
				'slug'  => (string) $color['slug'],
				'name'  => isset( $color['name'] ) ? (string) $color['name'] : (string) $color['slug'],
				'color' => (string) $color['color'],
			);
		}
	}
	return $base;
}

/**
 * Colors of the theme in the palette of the blocks, with the values chosen in
 * Customize: a block painted "Main color of the theme" follows the site.
 *
 * @param WP_Theme_JSON_Data $theme_json Data of theme.json.
 * @return WP_Theme_JSON_Data
 */
function tainacan_interface_theme_palette( $theme_json ) {
	if ( ! function_exists( 'tainacan_interface_opt' ) || ! method_exists( $theme_json, 'update_with' ) ) {
		return $theme_json;
	}
	$current = tainacan_interface_palette_base( $theme_json->get_data() );
	$slugs   = wp_list_pluck( $current, 'slug' );
	$colors  = array(
		'site-brand'   => array( __( 'Main color of the theme', 'tainacan-interface' ), tainacan_interface_opt( 'brand_color', 0 ) ),
		'site-accent'  => array( __( 'Accent color of the theme', 'tainacan-interface' ), tainacan_interface_opt( 'accent_color', 0 ) ),
		'site-text'    => array( __( 'Text color of the theme', 'tainacan-interface' ), tainacan_interface_opt( 'text_color', 0 ) ),
		'site-surface' => array( __( 'Box background of the theme', 'tainacan-interface' ), tainacan_interface_opt( 'surface_color', 0 ) ),
		'site-page'    => array( __( 'Page background of the theme', 'tainacan-interface' ), tainacan_interface_opt( 'page_background', 0 ) ),
	);
	$added   = array();
	foreach ( $colors as $slug => $color ) {
		if ( ! in_array( $slug, $slugs, true ) && is_string( $color[1] ) && sanitize_hex_color( $color[1] ) ) {
			$added[] = array(
				'slug'  => $slug,
				'name'  => $color[0],
				'color' => $color[1],
			);
		}
	}
	if ( ! $added ) {
		return $theme_json;
	}
	return $theme_json->update_with(
		array(
			'version'  => 2,
			'settings' => array( 'color' => array( 'palette' => array_merge( $added, $current ) ) ),
		)
	);
}
add_filter( 'wp_theme_json_data_theme', 'tainacan_interface_theme_palette' );

/**
 * The palette follows Customize: the styles built from theme.json are cached
 * on sites with an object cache, so they are cleared when the colors change.
 */
function tainacan_interface_palette_refresh() {
	if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
		wp_clean_theme_json_cache();
	}
}
add_action( 'customize_save_after', 'tainacan_interface_palette_refresh' );

/**
 * The design tokens (--tt2-*) inside the editor, so blocks, patterns and child
 * themes that use them look the same while edited.
 */
function tainacan_interface_editor_tokens() {
	if ( ! is_admin() || ! function_exists( 'tainacan_interface_design_css' ) ) {
		return;
	}
	$css = str_replace( 'body.tt2-modern{', '.editor-styles-wrapper{', wp_strip_all_tags( tainacan_interface_design_css() ) );
	wp_register_style( 'tainacan-interface-editor-tokens', false, array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_style( 'tainacan-interface-editor-tokens' );
	wp_add_inline_style( 'tainacan-interface-editor-tokens', $css );
}
add_action( 'enqueue_block_assets', 'tainacan_interface_editor_tokens' );

/**
 * In the editor: rebuilds, from their settings, the Tainacan blocks whose HTML
 * was not made by the editor (written by an AI, pasted, from a pattern), and
 * offers the same for other blocks the editor cannot read.
 */
function tainacan_interface_editor_rebuild() {
	wp_enqueue_script( 'tainacan-interface-editor-rebuild', get_template_directory_uri() . '/assets/js/editor-rebuild.js', array( 'wp-blocks', 'wp-data', 'wp-dom-ready' ), TAINACAN_INTERFACE_VERSION, true );
	wp_localize_script(
		'tainacan-interface-editor-rebuild',
		'tt2Rebuild',
		array(
			'editorMade' => tainacan_interface_kit_editor_made_blocks(),
			/* translators: %d: number of blocks. */
			'rebuilt'    => __( 'Tainacan Interface rebuilt %d Tainacan block(s) from their settings. Check them and save the page.', 'tainacan-interface' ),
			/* translators: %d: number of blocks. */
			'invalid'    => __( '%d block(s) of this page do not match what the editor expects (markup written outside the editor).', 'tainacan-interface' ),
			'action'     => __( 'Rebuild them', 'tainacan-interface' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'tainacan_interface_editor_rebuild' );

/**
 * The search block of the theme offers the public collections in the editor.
 */
function tainacan_interface_search_block_data() {
	// Only id and title: the editor opens often, and the context of the kit also reads every metadatum.
	$collections = array();
	$posts       = get_posts(
		array(
			'post_type'              => 'tainacan-collection',
			'post_status'            => 'publish',
			'numberposts'            => 100,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	foreach ( $posts as $collection ) {
		$collections[] = array(
			'value' => (string) $collection->ID,
			'label' => wp_strip_all_tags( get_the_title( $collection ) ),
		);
	}
	$i18n = array(
		'allItems'    => __( 'All items', 'tainacan-interface' ),
		'search'      => __( 'Search', 'tainacan-interface' ),
		'searchIn'    => __( 'Search in', 'tainacan-interface' ),
		'placeholder' => __( 'Text inside the box', 'tainacan-interface' ),
		'button'      => __( 'Button text', 'tainacan-interface' ),
		'label'       => __( 'Name for screen readers', 'tainacan-interface' ),
		'size'        => __( 'Size', 'tainacan-interface' ),
		'regular'     => __( 'Regular', 'tainacan-interface' ),
		'large'       => __( 'Large', 'tainacan-interface' ),
	);
	wp_add_inline_script(
		'tainacan-interface-search-editor-script',
		'window.tt2SearchBlock = ' . wp_json_encode(
			array(
				'collections' => $collections,
				'i18n'        => $i18n,
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'tainacan_interface_search_block_data' );
