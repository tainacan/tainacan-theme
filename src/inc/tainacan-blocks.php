<?php
/**
 * Tainacan blocks in pages and posts: carousels of items, collections and
 * terms, lists of items, collections and terms, and facets take the look of
 * the theme.
 *
 * The look is chosen in four places, from the widest to the narrowest:
 * - Customizer > Collections and item lists > Tainacan blocks in pages: the
 *   default of the site (options tblocks_* of inc/settings.php);
 * - the Styles panel of each block in the editor (block styles tt2-*): the
 *   whole look of that block, including "Tainacan plugin";
 * - classes written in "Additional CSS class(es)" of a block (tt2-ratio-4-3,
 *   tt2-fit-contain, tt2-lines-1, tt2-hover-none, tt2-arrows-plain,
 *   tt2-radius-round...): one detail of that block;
 * - the tokens --tt2-tb-* of assets/scss/tainacan-blocks.scss and the filter
 *   tainacan_interface_tblocks_classes, for child themes.
 *
 * The classes go on the root of the block when it is rendered (render_block),
 * so the blocks saved before keep working without being edited again.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocks of Tainacan that take the look, by kind: tiles (items, collections,
 * terms) or facets.
 *
 * @return array<string, string> Block name => kind.
 */
function tainacan_interface_tblocks_names() {
	return array(
		'tainacan/carousel-items-list'       => 'tiles',
		'tainacan/carousel-collections-list' => 'tiles',
		'tainacan/carousel-terms-list'       => 'tiles',
		'tainacan/dynamic-items-list'        => 'tiles',
		'tainacan/items-list'                => 'tiles',
		'tainacan/collections-list'          => 'tiles',
		'tainacan/terms-list'                => 'tiles',
		'tainacan/facets-list'               => 'facets',
	);
}

/**
 * The details a class of the block can change, with the values each accepts
 * and the option of the site that gives the default. A default not in the
 * list ("block", "theme") adds no class: the block decides.
 *
 * @return array<string, array{0: string, 1: string[]}> Detail => option, values.
 */
function tainacan_interface_tblocks_details() {
	return array(
		'ratio'  => array( 'tblocks_ratio', array( 'auto', '1-1', '4-3', '3-2', '16-9', '3-4' ) ),
		'fit'    => array( 'tblocks_fit', array( 'cover', 'contain' ) ),
		'lines'  => array( 'tblocks_lines', array( '1', '2', '3' ) ),
		'hover'  => array( 'tblocks_hover', array( 'zoom', 'lift', 'none' ) ),
		'radius' => array( 'tblocks_radius', array( 'theme', 'square', 'round' ) ),
		'arrows' => array( 'tblocks_arrows', array( 'round', 'accent', 'plain' ) ),
	);
}

/**
 * Classes the root of a block receives, from its own classes and the options
 * of the site. Empty when the block keeps the look of the plugin.
 *
 * @param string $block_name Name of the block.
 * @param string $own        Classes the block already has.
 * @return string[]
 */
function tainacan_interface_tblocks_classes( $block_name, $own ) {
	$names = tainacan_interface_tblocks_names();
	if ( ! isset( $names[ $block_name ] ) ) {
		return array();
	}
	$kind  = $names[ $block_name ];
	$own   = preg_split( '/\s+/', trim( (string) $own ), -1, PREG_SPLIT_NO_EMPTY );
	$style = '';
	foreach ( $own as $class ) {
		if ( 0 === strpos( $class, 'is-style-tt2-' ) ) {
			$style = substr( $class, strlen( 'is-style-tt2-' ) );
		}
	}

	if ( 'original' === $style || ( '' === $style && 'original' === tainacan_interface_opt( 'tblocks_skin' ) ) ) {
		$classes = array();
	} else {
		$classes = array( 'tt2-tb' );
		$cards   = array( 'card', 'clean', 'overlay' );
		// The facets with images are tiles too: they follow the cards of the site.
		$classes[] = 'tt2-tb-' . ( in_array( $style, $cards, true ) ? $style : tainacan_interface_opt( 'tblocks_card' ) );
		if ( 'facets' === $kind ) {
			$look = in_array( $style, array( 'chips', 'cloud' ), true ) ? $style : tainacan_interface_opt( 'tblocks_facets' );
			if ( 'plugin' !== $look ) {
				$classes[] = 'tt2-tb-' . $look;
			}
		}
		foreach ( tainacan_interface_tblocks_details() as $detail => $spec ) {
			if ( preg_grep( '/^tt2-' . $detail . '-/', $own ) ) {
				continue;
			}
			$value = (string) tainacan_interface_opt( $spec[0] );
			if ( in_array( $value, $spec[1], true ) && 'theme' !== $value ) {
				$classes[] = 'tt2-' . $detail . '-' . $value;
			}
		}
	}

	/**
	 * Classes the root of a Tainacan block receives from the theme.
	 *
	 * @param string[] $classes    Classes; empty keeps the look of the plugin.
	 * @param string   $block_name Name of the block.
	 * @param string[] $own        Classes the block already has.
	 */
	return array_values( array_filter( (array) apply_filters( 'tainacan_interface_tblocks_classes', $classes, $block_name, $own ), 'is_string' ) );
}

/**
 * Puts the classes on the root of each Tainacan block of the page.
 *
 * @param string $html  HTML of the block.
 * @param array  $block The block.
 * @return string
 */
function tainacan_interface_tblocks_render( $html, $block ) {
	$name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
	if ( '' === $html || 0 !== strpos( $name, 'tainacan/' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( ! $tags->next_tag() ) {
		return $html;
	}
	$own = (string) $tags->get_attribute( 'class' );
	if ( false === strpos( $own, 'wp-block-tainacan-' ) ) {
		return $html;
	}
	$classes = tainacan_interface_tblocks_classes( $name, $own );
	if ( ! $classes ) {
		return $html;
	}
	foreach ( $classes as $class ) {
		$tags->add_class( sanitize_html_class( $class ) );
	}
	tainacan_interface_tblocks_enqueue();
	return $tags->get_updated_html();
}
add_filter( 'render_block', 'tainacan_interface_tblocks_render', 10, 2 );

/**
 * The stylesheet and the small script of the look. Called in the head for
 * pages whose content has a Tainacan block, and again by each block rendered
 * elsewhere (widgets, templates), which then arrive at the end of the page.
 */
function tainacan_interface_tblocks_enqueue() {
	$uri  = get_template_directory_uri();
	$deps = wp_style_is( 'tainacan-interface-modern', 'registered' ) ? array( 'tainacan-interface-modern' ) : array();
	wp_enqueue_style( 'tainacan-interface-tblocks', $uri . '/assets/css/tainacan-blocks.css', $deps, TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script(
		'tainacan-interface-tblocks',
		$uri . '/assets/js/tainacan-blocks.js',
		array(),
		TAINACAN_INTERFACE_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

/**
 * In the head, for the pages whose content has a Tainacan block, so the
 * stylesheet does not arrive after the blocks.
 */
function tainacan_interface_tblocks_head() {
	if ( is_admin() || ! is_singular() ) {
		return;
	}
	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	if ( false !== strpos( $content, '<!-- wp:tainacan/' ) ) {
		tainacan_interface_tblocks_enqueue();
	}
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_tblocks_head', 30 );

/**
 * The looks of the theme in the Styles panel of each Tainacan block. "Default"
 * (no style) follows the Customizer.
 */
function tainacan_interface_tblocks_styles() {
	$tiles  = array(
		'tt2-card'     => __( 'Cards (theme)', 'tainacan-interface' ),
		'tt2-clean'    => __( 'Clean (theme)', 'tainacan-interface' ),
		'tt2-overlay'  => __( 'Title over the image (theme)', 'tainacan-interface' ),
		'tt2-original' => __( 'Tainacan plugin', 'tainacan-interface' ),
	);
	$facets = array(
		'tt2-chips'    => __( 'Labels (theme)', 'tainacan-interface' ),
		'tt2-cloud'    => __( 'Cloud of labels (theme)', 'tainacan-interface' ),
		'tt2-original' => __( 'Tainacan plugin', 'tainacan-interface' ),
	);
	foreach ( tainacan_interface_tblocks_names() as $name => $kind ) {
		foreach ( 'facets' === $kind ? $facets : $tiles as $style => $label ) {
			register_block_style(
				$name,
				array(
					'name'  => $style,
					'label' => $label,
				)
			);
		}
	}
}
add_action( 'init', 'tainacan_interface_tblocks_styles', 20 );
