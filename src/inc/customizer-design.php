<?php
/**
 * The Customizer of the design of version 4 at work: options that change the
 * preview without reloading it, options that stay out of the way when they would
 * do nothing, the warning about collections with their own value, and a preview
 * that goes to a page where the open section can be seen.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options the preview applies by itself, by CSS variable or body class. The other
 * options print different markup, so they reload the preview.
 *
 * @return array Key => how the preview script applies it.
 */
function tainacan_interface_live_options() {
	$shadows = tainacan_interface_shadow_values();
	$fonts   = array();
	foreach ( array( 'poppins', 'roboto', 'lora', 'system', 'georgia' ) as $font ) {
		$fonts[ $font ] = tainacan_interface_font_stack( $font );
	}

	$live = array(
		'brand_color'        => array( 'kind' => 'brand' ),
		'accent_color'       => array( 'kind' => 'accent' ),
		'page_background'    => array( 'vars' => array( '--tt2-page' ) ),
		'surface_color'      => array( 'vars' => array( '--tt2-surface' ) ),
		'text_color'         => array( 'vars' => array( '--tt2-text', '--tt2-value' ) ),
		'band_style'         => array( 'kind' => 'band' ),
		'band_1'             => array( 'kind' => 'band' ),
		'band_2'             => array( 'kind' => 'band' ),
		'band_3'             => array( 'kind' => 'band' ),
		'band_4'             => array( 'kind' => 'band' ),
		'radius'             => array(
			'vars' => array( '--tt2-radius' ),
			'unit' => 'px',
		),
		'page_width'         => array(
			'vars' => array( '--tt2-width' ),
			'unit' => 'px',
		),
		'font_size'          => array(
			'vars' => array( '--tt2-u', '--tainacan-base-font-size' ),
			'unit' => 'px',
		),
		'head_banner_height' => array(
			'vars' => array( '--tt2-banner-height' ),
			'unit' => 'px',
		),
		'head_banner_veil'   => array(
			'vars' => array( '--tt2-banner-veil' ),
			'unit' => '%',
		),
		'head_icon_size'     => array(
			'vars' => array( '--tt2-icon-size' ),
			'unit' => 'px',
		),
		'list_columns'       => array( 'vars' => array( '--tt2-list-columns' ) ),
		'list_ratio'         => array( 'vars' => array( '--tt2-list-ratio' ) ),
		'card_title_lines'   => array( 'vars' => array( '--tt2-title-lines' ) ),
		'drawer_width'       => array(
			'vars' => array( '--tt2-d-width' ),
			'unit' => 'px',
		),
		'shadow'             => array(
			'vars' => array( '--tt2-shadow' ),
			'map'  => $shadows,
		),
		'font_ui'            => array(
			'vars' => array( '--tt2-font-ui', '--tainacan-font-family' ),
			'map'  => $fonts,
		),
		'font_heading'       => array(
			'vars' => array( '--tt2-font-heading', '--tt2-title-font' ),
			'map'  => $fonts,
		),
		'font_reading'       => array(
			'vars' => array( '--tt2-font-reading' ),
			'map'  => $fonts,
		),
		'header_stack'       => array( 'prefix' => 'tt2-header-' ),
		'card_accent'        => array( 'prefix' => 'tt2-card-accent-' ),
		'card_hover'         => array( 'prefix' => 'tt2-card-hover-' ),
		'item_chips'         => array( 'prefix' => 'tt2-chips-' ),
		'item_labels'        => array( 'prefix' => 'tt2-labels-' ),
		'item_columns'       => array( 'prefix' => 'tt2-meta-columns-' ),
		'item_layout'        => array( 'prefix' => 'tt2-item-layout-' ),
		'header_sticky'      => array( 'flag' => 'tt2-header-is-sticky' ),
		'list_full_width'    => array( 'flag' => 'tt2-lists-full' ),
		'item_frame'         => array( 'flag' => 'tt2-doc-frame' ),
		'heading_weight'     => array( 'vars' => array( '--tt2-heading-weight' ) ),
		'heading_scale'      => array(
			'vars'   => array( '--tt2-heading-scale' ),
			'divide' => 100,
		),
		'heading_spacing'    => array(
			'vars'   => array( '--tt2-heading-spacing' ),
			'divide' => 100,
			'unit'   => 'em',
		),
		'line_height'        => array(
			'vars'   => array( '--tt2-leading' ),
			'divide' => 100,
		),
		'heading_transform'  => array(
			'flag' => 'tt2-heading-upper',
			'when' => 'uppercase',
		),
		'menu_transform'     => array(
			'flag' => 'tt2-menu-upper',
			'when' => 'uppercase',
		),
		'button_transform'   => array(
			'flag' => 'tt2-button-upper',
			'when' => 'uppercase',
		),
		'sidebar_sticky'     => array( 'flag' => 'tt2-sidebar-sticky' ),
		'menu_indicator'     => array( 'prefix' => 'tt2-indicator-' ),
		'mobile_menu'        => array( 'prefix' => 'tt2-mobile-menu-' ),
		'crumb_separator'    => array( 'prefix' => 'tt2-crumb-sep-' ),
		'button_style'       => array( 'prefix' => 'tt2-buttons-' ),
		'field_style'        => array( 'prefix' => 'tt2-fields-' ),
		'link_style'         => array( 'prefix' => 'tt2-links-' ),
		'selection'          => array( 'prefix' => 'tt2-selection-' ),
		'hero_align'         => array( 'prefix' => 'tt2-hero-align-' ),
		'page_title_style'   => array( 'prefix' => 'tt2-title-' ),
		'blog_layout'        => array( 'prefix' => 'tt2-blog-' ),
		'post_width'         => array( 'prefix' => 'tt2-post-' ),
		'back_top_shape'     => array( 'prefix' => 'tt2-back-top-' ),
		'back_top_side'      => array( 'prefix' => 'tt2-back-top-' ),
	);

	foreach ( array(
		'font_size_mobile' => '--tt2-u-mobile',
		'menu_size'        => '--tt2-menu-size',
		'edge_spacing'     => '--tt2-edge',
		'header_padding'   => '--tt2-header-pad',
		'hero_height'      => '--tt2-hero-height',
		'sidebar_width'    => '--tt2-sidebar-width',
		'narrow_width'     => '--tt2-narrow',
	) as $key => $name ) {
		$live[ $key ] = array(
			'vars' => array( $name ),
			'unit' => 'px',
		);
	}
	$live['blog_columns'] = array( 'vars' => array( '--tt2-blog-columns' ) );
	$live['blog_ratio']   = array( 'vars' => array( '--tt2-blog-ratio' ) );
	$names                = array(
		'content_spacing' => '--tt2-space',
		'button_shape'    => '--tt2-button-radius',
		'heading_color'   => '--tt2-ink',
	);
	foreach ( tainacan_interface_value_maps() as $key => $map ) {
		$live[ $key ] = array(
			'vars' => array( $names[ $key ] ),
			'map'  => $map,
		);
	}

	$schema = tainacan_interface_settings_schema();
	foreach ( $live as $key => $how ) {
		if ( ! isset( $schema[ $key ] ) ) {
			unset( $live[ $key ] );
		} elseif ( isset( $how['prefix'] ) ) {
			$live[ $key ]['values'] = array_map( 'strval', array_keys( $schema[ $key ]['choices'] ) );
		}
	}
	return $live;
}

/**
 * When each option of the panel means something: a list of groups, all of which
 * must hold; a group holds when one of its conditions does. Options that have no
 * effect under the current choices leave the panel instead of seeming broken.
 *
 * @return array Setting id => groups of array( setting id, accepted values ).
 */
function tainacan_interface_option_rules() {
	$prefix = 'tainacan_interface_';
	$rules  = array();

	$only = array(
		'band_1'                => array( 'band_style', array( 'bands', 'gradient' ) ),
		'band_2'                => array( 'band_style', array( 'bands', 'gradient' ) ),
		'band_3'                => array( 'band_style', array( 'bands', 'gradient' ) ),
		'band_4'                => array( 'band_style', array( 'bands', 'gradient' ) ),
		'header_band'           => array( 'band_style', array( 'bands', 'gradient', 'accent' ) ),
		'crumb_home_icon'       => array( 'crumb_style', array( 'pills', 'plain' ) ),
		'crumb_item_collection' => array( 'crumb_style', array( 'pills', 'plain' ) ),
		'head_banner'           => array( 'head_layout', array( 'center', 'left', 'overlay' ) ),
		'head_banner_width'     => array( 'head_layout', array( 'center', 'left', 'overlay' ) ),
		'head_banner_height'    => array( 'head_layout', array( 'center', 'left', 'overlay' ) ),
		'head_banner_veil'      => array( 'head_layout', array( 'center', 'left', 'overlay' ) ),
		'head_icon_size'        => array( 'head_icon', array( 'tilted', 'square', 'circle' ) ),
		'list_ratio'            => array( 'list_style', array( 'card' ) ),
		'list_words'            => array( 'list_description', array( '1' ) ),
		'drawer_side'           => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_width'          => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_backdrop'       => array( 'drawer_mode', array( 'overlay' ) ),
		'drawer_media'          => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_meta_line'      => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_description'    => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_max_fields'     => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_open_label'     => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'drawer_open_new'       => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'head_surprise'         => array( 'drawer_mode', array( 'overlay', 'push' ) ),
		'blog_columns'          => array( 'blog_layout', array( 'grid' ) ),
		'sidebar_width'         => array( 'sidebar_position', array( 'right', 'left' ) ),
		'sidebar_sticky'        => array( 'sidebar_position', array( 'right', 'left' ) ),
		'narrow_width'          => array( 'post_width', array( 'narrow' ) ),
		'back_top_shape'        => array( 'back_top', array( '1' ) ),
		'back_top_side'         => array( 'back_top', array( '1' ) ),
		'back_top_mobile'       => array( 'back_top', array( '1' ) ),
		'footer_tone'           => array( 'footer_style', array( 'compact' ) ),
		'hero_align'            => array( 'hero_style', array( 'banner', 'plain' ) ),
		'hero_height'           => array( 'hero_style', array( 'banner', 'plain' ) ),
		'social_new_tab'        => array( 'social_place', array( 'footer', 'header', 'both' ) ),
	);
	foreach ( $only as $key => $condition ) {
		$rules[ $prefix . $key ][] = array( array( $prefix . $condition[0], $condition[1] ) );
	}
	foreach ( array( 'head_banner_width', 'head_banner_height', 'head_banner_veil' ) as $key ) {
		$rules[ $prefix . $key ][] = array( array( $prefix . 'head_banner', array( 'auto', 'image', 'mosaic' ) ) );
	}

	$rules['tainacan_footer_color'] = array( array( array( $prefix . 'footer_style', array( 'classic' ) ) ) );

	// The preview needs the switch of its feature on.
	foreach ( array( 'drawer_mode', 'drawer_side', 'drawer_width', 'drawer_backdrop', 'drawer_media', 'drawer_meta_line', 'drawer_description', 'drawer_max_fields', 'drawer_open_label', 'drawer_open_new', 'head_surprise' ) as $key ) {
		$rules[ $prefix . $key ][] = array( array( $prefix . 'item_drawer', array( '1' ) ) );
	}
	foreach ( array( 'side', 'title', 'relationships', 'terms', 'collection', 'order', 'per_thread', 'term_threads', 'sticky', 'hide_bottom' ) as $key ) {
		$rules[ $prefix . 'aside_' . $key ] = array( array( array( $prefix . 'item_aside', array( '1' ) ) ) );
	}

	// With the side column taking its place, the section of related items is not on the page.
	$related = array( array( array( $prefix . 'item_aside', array( '' ) ), array( $prefix . 'aside_hide_bottom', array( '' ) ) ) );
	foreach ( array( 'enable_related_items_section', 'related_items_section_label', 'related_items_layout', 'related_items_order', 'related_items_max_columns_count', 'related_items_max_items_per_screen', 'related_items_gallery_max_height', 'related_items_gallery_thumbnail_size', 'related_items_thumbs_layout', 'related_items_hide_image_thumbnails' ) as $key ) {
		$rules[ 'tainacan_single_item_' . $key ] = $related;
	}

	/**
	 * When each option of the Customizer panel is shown.
	 *
	 * @param array $rules Setting id => groups of conditions.
	 */
	return (array) apply_filters( 'tainacan_interface_option_rules', $rules );
}

/**
 * Collections that keep their own value for each option, so the panel can say
 * why the option does not change them.
 *
 * @return array Key => list of array( n => name, u => address of the form ).
 */
function tainacan_interface_collection_pins() {
	$pins        = array();
	$collections = get_posts(
		array(
			'post_type'              => 'tainacan-collection',
			'post_status'            => array( 'publish', 'private' ),
			'posts_per_page'         => 100,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $collections as $collection ) {
		foreach ( array_keys( tainacan_interface_collection_options() ) as $key ) {
			$own = get_post_meta( $collection->ID, 'tainacan_interface_opt_' . $key, true );
			if ( '' !== $own ) {
				$pins[ $key ][] = array(
					'n' => tainacan_interface_plain( $collection->post_title ),
					'u' => admin_url( 'admin.php?page=tainacan_admin#/collections/' . (int) $collection->ID . '/settings' ),
				);
			}
		}
	}
	return $pins;
}

/**
 * Pages the preview goes to when a section is opened: the list of collections, a
 * collection and one of its items. A collection without own values comes first,
 * so what the visitor changes in the panel appears on it.
 *
 * @return array Kind => address.
 */
function tainacan_interface_preview_targets() {
	$targets     = array(
		'collections' => (string) get_post_type_archive_link( 'tainacan-collection' ),
		'blog'        => tainacan_interface_blog_url(),
		'collection'  => '',
		'item'        => '',
	);
	$own_values  = array();
	$collections = get_posts(
		array(
			'post_type'              => 'tainacan-collection',
			'post_status'            => 'publish',
			'posts_per_page'         => 30,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $collections as $collection_id ) {
		$own_values[ $collection_id ] = 0;
		foreach ( array_keys( tainacan_interface_collection_options() ) as $key ) {
			$own_values[ $collection_id ] += '' !== get_post_meta( $collection_id, 'tainacan_interface_opt_' . $key, true ) ? 1 : 0;
		}
	}
	asort( $own_values );

	foreach ( array_keys( $own_values ) as $collection_id ) {
		// tnc-check-ignore TNC-P02: stops at the first collection with an image, at most 30, on the Customizer screen only.
		$items = get_posts(
			array(
				'post_type'              => 'tnc_col_' . (int) $collection_id . '_item',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_key'               => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one row per collection, on the Customizer screen only; a sample with an image shows the options that act on images.
			)
		);
		if ( $items ) {
			$targets['collection'] = (string) get_post_type_archive_link( 'tnc_col_' . (int) $collection_id . '_item' );
			$targets['item']       = (string) get_permalink( $items[0] );
			break;
		}
	}

	return $targets;
}

/**
 * The page with the list of posts, or '' when the site has none.
 *
 * @return string
 */
function tainacan_interface_blog_url() {
	if ( get_option( 'page_for_posts' ) ) {
		return (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	return 'page' === get_option( 'show_on_front' ) ? '' : home_url( '/' );
}

/**
 * Scripts of the panel: ready-made colors, rules, warnings and preview pages.
 */
function tainacan_interface_customizer_controls_script() {
	wp_enqueue_script( 'tainacan-interface-customizer-controls', get_template_directory_uri() . '/assets/js/customizer-controls.js', array( 'customize-controls', 'underscore' ), TAINACAN_INTERFACE_VERSION, true );

	$presets = array();
	foreach ( tainacan_interface_presets() as $slug => $preset ) {
		$presets[ $slug ] = $preset['values'];
	}

	wp_localize_script(
		'tainacan-interface-customizer-controls',
		'tainacanInterfaceCustomizer',
		array(
			'presets'  => $presets,
			'rules'    => tainacan_interface_option_rules(),
			'pins'     => tainacan_interface_collection_pins(),
			'targets'  => tainacan_interface_preview_targets(),
			'sections' => array(
				'tainacan_interface_opt_breadcrumb'       => 'collection',
				'tainacan_interface_opt_collection'       => 'collection',
				'tainacan_interface_opt_collections'      => 'collections',
				'tainacan_interface_opt_cards'            => 'collection',
				'tainacan_interface_opt_drawer'           => 'collection',
				'tainacan_interface_opt_item'             => 'item',
				'tainacan_interface_opt_blog'             => 'blog',
				'tainacan_interface_aside'                => 'item',
				'tainacan_single_item_page_general'       => 'item',
				'tainacan_single_item_page_metadata'      => 'item',
				'tainacan_single_item_page_document'      => 'item',
				'tainacan_single_item_page_related_items' => 'item',
				'tainacan_items_page_search_area'         => 'collection',
				'tainacan_items_page_filters_panel'       => 'collection',
				'tainacan_items_page_pagination'          => 'collection',
			),
			'i18n'     => array(
				/* translators: %s: names of collections, as links. */
				'pinned' => __( 'Does not change these collections, which have their own value on their form: %s.', 'tainacan-interface' ),
			),
		)
	);
}
add_action( 'customize_controls_enqueue_scripts', 'tainacan_interface_customizer_controls_script' );

/**
 * Kind of page in the preview.
 *
 * @return string item, collections, collection or other.
 */
function tainacan_interface_preview_kind() {
	if ( tainacan_interface_is_item_page() ) {
		return 'item';
	}
	if ( is_post_type_archive( 'tainacan-collection' ) ) {
		return 'collections';
	}
	if ( is_home() ) {
		return 'blog';
	}
	return tainacan_interface_is_list_page() ? 'collection' : 'other';
}

/**
 * Script of the preview.
 */
function tainacan_interface_customizer_preview_script() {
	if ( ! is_customize_preview() ) {
		return;
	}

	$collection_id = tainacan_interface_opt_context();
	$pinned        = array();
	if ( $collection_id > 0 ) {
		foreach ( array_keys( tainacan_interface_collection_options() ) as $key ) {
			if ( '' !== get_post_meta( $collection_id, 'tainacan_interface_opt_' . $key, true ) ) {
				$pinned[] = $key;
			}
		}
	}

	wp_enqueue_script( 'tainacan-interface-customizer-preview', get_template_directory_uri() . '/assets/js/customizer-preview-design.js', array( 'customize-preview' ), TAINACAN_INTERFACE_VERSION, true );
	wp_localize_script(
		'tainacan-interface-customizer-preview',
		'tainacanInterfacePreview',
		array(
			'live'   => tainacan_interface_live_options(),
			'pinned' => $pinned,
			'kind'   => tainacan_interface_preview_kind(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_customizer_preview_script', 30 );
