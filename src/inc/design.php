<?php
/**
 * New design: the tokens of the settings as CSS variables, the
 * stylesheet, the body classes and the images made from the items of a
 * collection.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font stacks of the settings. The Poppins and Lora files are in assets/fonts,
 * declared in modern.css: a browser downloads only the ones a page uses.
 *
 * @param string $font Slug.
 * @return string
 */
function tainacan_interface_font_stack( $font ) {
	$system = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
	$stacks = array(
		'poppins'      => '"Poppins", ' . $system,
		'roboto'       => '"Roboto", ' . $system,
		'inter'        => '"Inter", ' . $system,
		'open-sans'    => '"Open Sans", ' . $system,
		'montserrat'   => '"Montserrat", ' . $system,
		'lora'         => '"Lora", Georgia, "Times New Roman", serif',
		'source-serif' => '"Source Serif 4", Georgia, "Times New Roman", serif',
		'merriweather' => '"Merriweather", Georgia, "Times New Roman", serif',
		'playfair'     => '"Playfair Display", Georgia, "Times New Roman", serif',
		'georgia'      => 'Georgia, "Times New Roman", serif',
		'system'       => $system,
	);
	return isset( $stacks[ $font ] ) ? $stacks[ $font ] : $system;
}

/**
 * The band in CSS: four colors side by side, a gradient, the accent, or nothing.
 *
 * @return string
 */
function tainacan_interface_band_css() {
	$colors = array( tainacan_interface_opt( 'band_1' ), tainacan_interface_opt( 'band_2' ), tainacan_interface_opt( 'band_3' ), tainacan_interface_opt( 'band_4' ) );

	switch ( tainacan_interface_opt( 'band_style' ) ) {
		case 'bands':
			return sprintf( 'linear-gradient(90deg, %1$s 0 25%%, %2$s 25%% 50%%, %3$s 50%% 75%%, %4$s 75%%)', $colors[0], $colors[1], $colors[2], $colors[3] );
		case 'gradient':
			return sprintf( 'linear-gradient(90deg, %1$s, %2$s, %3$s, %4$s)', $colors[0], $colors[1], $colors[2], $colors[3] );
		case 'accent':
			return tainacan_interface_opt( 'accent_color' );
		default:
			return 'transparent';
	}
}

/**
 * Whether a light text reads better than a dark one over a color.
 *
 * @param string $hex Color as #rrggbb or #rgb.
 * @return bool
 */
function tainacan_interface_is_dark_color( $hex ) {
	$rgb = tainacan_hex2rgb( $hex );
	if ( empty( $rgb ) ) {
		return true;
	}
	return 0.2126 * $rgb['red'] + 0.7152 * $rgb['green'] + 0.0722 * $rgb['blue'] < 150;
}

/**
 * The shadows of the settings.
 *
 * @return array Slug => CSS value.
 */
function tainacan_interface_shadow_values() {
	return array(
		'none' => 'none',
		'soft' => '0 1px 2px rgba(20,24,40,.06), 0 10px 26px -14px rgba(20,24,40,.2)',
		'deep' => '0 2px 4px rgba(20,24,40,.08), 0 22px 44px -18px rgba(20,24,40,.38)',
	);
}

/**
 * CSS values of the options that are a choice between named values. The page
 * and the preview read them from here.
 *
 * @return array Key => value => CSS value.
 */
function tainacan_interface_value_maps() {
	return array(
		'content_spacing' => array(
			'compact'  => '0.7',
			'normal'   => '1',
			'spacious' => '1.4',
		),
		'button_shape'    => array(
			'rounded' => '8px',
			'pill'    => '999px',
			'square'  => '0',
		),
		'heading_color'   => array(
			'brand'  => 'var(--tt2-brand)',
			'accent' => 'var(--tt2-accent)',
			'text'   => 'var(--tt2-text)',
		),
	);
}

/**
 * The tokens. Every value comes through tainacan_interface_opt(): colors are hex
 * colors, numbers are integers, fonts come from a closed list. The two Tainacan
 * variables get literal colors, so they cannot loop back to a variable that
 * depends on them.
 *
 * @return string
 */
function tainacan_interface_design_css() {
	$brand   = tainacan_interface_opt( 'brand_color' );
	$accent  = tainacan_interface_opt( 'accent_color' );
	$shadows = tainacan_interface_shadow_values();

	$headers = array(
		'brand'  => array( $brand, tainacan_interface_is_dark_color( $brand ) ? '#ffffff' : '#111111' ),
		'accent' => array( $accent, tainacan_interface_is_dark_color( $accent ) ? '#ffffff' : '#111111' ),
		'dark'   => array( '#16191d', '#ffffff' ),
		'light'  => array( '#ffffff', tainacan_interface_opt( 'text_color' ) ),
	);
	$header  = $headers[ tainacan_interface_opt( 'header_style' ) ];
	$footer  = $headers[ tainacan_interface_opt( 'footer_tone' ) ];
	$maps    = tainacan_interface_value_maps();

	$tokens = array(
		'--tt2-brand'                         => $brand,
		'--tt2-on-brand'                      => tainacan_interface_is_dark_color( $brand ) ? '#ffffff' : '#111111',
		'--tt2-accent'                        => $accent,
		'--tt2-on-accent'                     => tainacan_interface_is_dark_color( $accent ) ? '#ffffff' : '#111111',
		'--tt2-ink'                           => $maps['heading_color'][ tainacan_interface_opt( 'heading_color' ) ],
		'--tt2-text'                          => tainacan_interface_opt( 'text_color' ),
		'--tt2-value'                         => tainacan_interface_opt( 'text_color' ),
		'--tt2-surface'                       => tainacan_interface_opt( 'surface_color' ),
		'--tt2-page'                          => tainacan_interface_opt( 'page_background' ),
		'--tt2-radius'                        => absint( tainacan_interface_opt( 'radius' ) ) . 'px',
		'--tt2-shadow'                        => $shadows[ tainacan_interface_opt( 'shadow' ) ],
		'--tt2-width'                         => absint( tainacan_interface_opt( 'page_width' ) ) . 'px',
		'--tt2-u'                             => absint( tainacan_interface_opt( 'font_size' ) ) . 'px',
		'--tt2-font-ui'                       => tainacan_interface_font_stack( tainacan_interface_opt( 'font_ui' ) ),
		'--tt2-font-heading'                  => tainacan_interface_font_stack( tainacan_interface_opt( 'font_heading' ) ),
		'--tt2-font-reading'                  => tainacan_interface_font_stack( tainacan_interface_opt( 'font_reading' ) ),
		'--tt2-title-font'                    => tainacan_interface_font_stack( tainacan_interface_opt( 'font_heading' ) ),
		'--tt2-band'                          => tainacan_interface_band_css(),
		'--tt2-banner-height'                 => absint( tainacan_interface_opt( 'head_banner_height' ) ) . 'px',
		'--tt2-banner-veil'                   => absint( tainacan_interface_opt( 'head_banner_veil' ) ) . '%',
		'--tt2-icon-size'                     => absint( tainacan_interface_opt( 'head_icon_size' ) ) . 'px',
		'--tt2-list-columns'                  => absint( tainacan_interface_opt( 'list_columns' ) ),
		'--tt2-list-ratio'                    => tainacan_interface_opt( 'list_ratio' ),
		'--tt2-title-lines'                   => absint( tainacan_interface_opt( 'card_title_lines' ) ),
		'--tt2-d-width'                       => absint( tainacan_interface_opt( 'drawer_width' ) ) . 'px',
		'--tt2-d-accent'                      => $accent,
		'--tainacan-secondary'                => $accent,
		'--tainacan-primary'                  => 'color-mix(in srgb, ' . $accent . ' 14%, #fff)',
		'--tainacan-interface--link-color'    => $accent,
		'--tainacan-interface--tooltip-color' => 'color-mix(in srgb, ' . $accent . ' 18%, #fff)',
		'--tainacan-block-primary'            => $accent,
		'--tainacan-font-family'              => tainacan_interface_font_stack( tainacan_interface_opt( 'font_ui' ) ),
		'--tt2-header-bg'                     => $header[0],
		'--tt2-header-fg'                     => $header[1],
		'--tt2-footer-bg'                     => $footer[0],
		'--tt2-footer-fg'                     => $footer[1],
		'--tt2-header-pad'                    => absint( tainacan_interface_opt( 'header_padding' ) ) . 'px',
		'--tt2-heading-weight'                => (int) tainacan_interface_opt( 'heading_weight' ),
		'--tt2-body-weight'                   => (int) tainacan_interface_opt( 'body_weight' ),
		'--tt2-heading-scale'                 => absint( tainacan_interface_opt( 'heading_scale' ) ) / 100,
		'--tt2-heading-spacing'               => (int) tainacan_interface_opt( 'heading_spacing' ) / 100 . 'em',
		'--tt2-leading'                       => absint( tainacan_interface_opt( 'line_height' ) ) / 100,
		'--tt2-h1'                               => absint( tainacan_interface_opt( 'h1_size' ) ) . 'px',
		'--tt2-h2'                               => absint( tainacan_interface_opt( 'h2_size' ) ) . 'px',
		'--tt2-h3'                               => absint( tainacan_interface_opt( 'h3_size' ) ) . 'px',
		'--tt2-h4'                               => absint( tainacan_interface_opt( 'h4_size' ) ) . 'px',
		'--tt2-h5'                               => absint( tainacan_interface_opt( 'h5_size' ) ) . 'px',
		'--tt2-h6'                               => absint( tainacan_interface_opt( 'h6_size' ) ) . 'px',
		'--tt2-meta-label'                    => absint( tainacan_interface_opt( 'item_label_size' ) ) . 'px',
		'--tt2-meta-value'                    => absint( tainacan_interface_opt( 'item_value_size' ) ) . 'px',
		'--tt2-u-mobile'                      => absint( tainacan_interface_opt( 'font_size_mobile' ) ) . 'px',
		'--tt2-menu-size'                     => absint( tainacan_interface_opt( 'menu_size' ) ) . 'px',
		'--tt2-edge'                          => absint( tainacan_interface_opt( 'edge_spacing' ) ) . 'px',
		'--tt2-space'                         => $maps['content_spacing'][ tainacan_interface_opt( 'content_spacing' ) ],
		'--tt2-frame'                         => absint( tainacan_interface_opt( 'frame_size' ) ) . 'px',
		'--tt2-button-radius'                 => $maps['button_shape'][ tainacan_interface_opt( 'button_shape' ) ],
		'--tt2-hero-height'                   => absint( tainacan_interface_opt( 'hero_height' ) ) . 'px',
		'--tt2-blog-columns'                  => absint( tainacan_interface_opt( 'blog_columns' ) ),
		'--tt2-blog-ratio'                    => tainacan_interface_opt( 'blog_ratio' ),
		'--tt2-sidebar-width'                 => absint( tainacan_interface_opt( 'sidebar_width' ) ) . 'px',
		'--tt2-narrow'                        => absint( tainacan_interface_opt( 'narrow_width' ) ) . 'px',
		'--tainacan-base-font-size'           => absint( tainacan_interface_opt( 'font_size' ) ) . 'px',
	);

	$css = '';
	foreach ( $tokens as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}

	return 'body.tt2-modern{' . $css . '}';
}

/**
 * The stylesheet of the design, after the theme's and the highlight's, so its
 * rules win without more specificity than they need.
 */
function tainacan_interface_design_enqueue() {
	$uri = get_template_directory_uri();
	// A child theme with its own enqueues may not have the handle, and a missing dependency drops the stylesheet.
	wp_enqueue_style( 'tainacan-interface-modern', $uri . '/assets/css/modern.css', wp_style_is( 'tainacan_tainacanStyle', 'registered' ) ? array( 'tainacan_tainacanStyle' ) : array(), TAINACAN_INTERFACE_VERSION );
	wp_add_inline_style( 'tainacan-interface-modern', wp_strip_all_tags( tainacan_interface_design_css() ) );

	wp_enqueue_script( 'tainacan-interface-modern', $uri . '/assets/js/modern.js', array(), TAINACAN_INTERFACE_VERSION, true );
	wp_localize_script(
		'tainacan-interface-modern',
		'tainacanInterfaceModern',
		array(
			'i18n' => array(
				'copied'       => __( 'Copied', 'tainacan-interface' ),
				'copyFailed'   => __( 'Could not copy. Select the text and copy it.', 'tainacan-interface' ),
				/* translators: %d: number of fields in a metadata section. */
				'fieldsOne'    => __( '%d field', 'tainacan-interface' ),
				/* translators: %d: number of fields in a metadata section. */
				'fieldsMany'   => __( '%d fields', 'tainacan-interface' ),
				'nothingFound' => __( 'No collection matches this text.', 'tainacan-interface' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_design_enqueue', 20 );

/**
 * Body classes the stylesheet reads.
 *
 * @param string[] $box_classes Body classes.
 * @return string[]
 */
function tainacan_interface_design_body_class( $box_classes ) {
	$box_classes[] = 'tt2-modern';
	$box_classes[] = 'tt2-header-' . tainacan_interface_opt( 'header_style' );
	if ( 'light' !== tainacan_interface_opt( 'header_style' ) ) {
		$box_classes[] = 'tt2-header-colored';
	}
	$box_classes[] = 'tt2-indicator-' . tainacan_interface_opt( 'menu_indicator' );
	$box_classes[] = 'tt2-mobile-menu-' . tainacan_interface_opt( 'mobile_menu' );
	$box_classes[] = 'tt2-crumb-sep-' . tainacan_interface_opt( 'crumb_separator' );
	$box_classes[] = 'tt2-buttons-' . tainacan_interface_opt( 'button_style' );
	$box_classes[] = 'tt2-fields-' . tainacan_interface_opt( 'field_style' );
	$box_classes[] = 'tt2-links-' . tainacan_interface_opt( 'link_style' );
	$box_classes[] = 'tt2-selection-' . tainacan_interface_opt( 'selection' );
	$box_classes[] = 'tt2-hero-' . tainacan_interface_opt( 'hero_style' );
	$box_classes[] = 'tt2-hero-align-' . tainacan_interface_opt( 'hero_align' );
	$box_classes[] = 'tt2-title-' . tainacan_interface_opt( 'page_title_style' );
	$box_classes[] = 'tt2-blog-' . tainacan_interface_opt( 'blog_layout' );
	$box_classes[] = 'tt2-post-' . tainacan_interface_opt( 'post_width' );
	$box_classes[] = 'tt2-back-top-' . tainacan_interface_opt( 'back_top_shape' );
	$box_classes[] = 'tt2-back-top-' . tainacan_interface_opt( 'back_top_side' );
	foreach ( array(
		'heading_transform' => 'tt2-heading-upper',
		'menu_transform'    => 'tt2-menu-upper',
		'button_transform'  => 'tt2-button-upper',
	) as $key => $flag ) {
		if ( 'uppercase' === tainacan_interface_opt( $key ) ) {
			$box_classes[] = $flag;
		}
	}
	if ( 'left' === tainacan_interface_opt( 'sidebar_position' ) ) {
		$box_classes[] = 'tt2-sidebar-left';
	}
	if ( tainacan_interface_has_sidebar() ) {
		$box_classes[] = 'tt2-has-sidebar';
	}
	if ( tainacan_interface_opt( 'sidebar_sticky' ) ) {
		$box_classes[] = 'tt2-sidebar-sticky';
	}
	if ( tainacan_interface_opt( 'post_progress' ) && is_singular( 'post' ) ) {
		$box_classes[] = 'tt2-progress';
	}
	if ( ! tainacan_interface_opt( 'back_top_mobile' ) ) {
		$box_classes[] = 'tt2-back-top-desktop';
	}
	if ( tainacan_interface_opt( 'frame_size' ) > 0 ) {
		$box_classes[] = 'tt2-frame';
	}
	$box_classes[] = 'tt2-header-' . tainacan_interface_opt( 'header_stack' );
	$box_classes[] = 'tt2-crumbs-' . tainacan_interface_opt( 'crumb_style' );
	$box_classes[] = 'tt2-card-accent-' . tainacan_interface_opt( 'card_accent' );
	$box_classes[] = 'tt2-card-hover-' . tainacan_interface_opt( 'card_hover' );
	$box_classes[] = 'tt2-chips-' . tainacan_interface_opt( 'item_chips' );
	$box_classes[] = 'tt2-labels-' . tainacan_interface_opt( 'item_labels' );
	$box_classes[] = 'tt2-meta-columns-' . tainacan_interface_opt( 'item_columns' );
	$box_classes[] = 'tt2-item-layout-' . tainacan_interface_opt( 'item_layout' );

	if ( tainacan_interface_opt( 'header_sticky' ) ) {
		$box_classes[] = 'tt2-header-is-sticky';
	}
	if ( tainacan_interface_opt( 'list_full_width' ) ) {
		$box_classes[] = 'tt2-lists-full';
	}
	if ( tainacan_interface_opt( 'item_frame' ) ) {
		$box_classes[] = 'tt2-doc-frame';
	}
	// The design leaves these two headings out until someone writes a label for them.
	if ( '' !== (string) get_theme_mod( 'tainacan_single_item_document_section_label', '' ) ) {
		$box_classes[] = 'tt2-label-document';
	}
	if ( '' !== (string) get_theme_mod( 'tainacan_single_item_attachments_section_label', '' ) ) {
		$box_classes[] = 'tt2-label-attachments';
	}

	return $box_classes;
}
add_filter( 'body_class', 'tainacan_interface_design_body_class' );

/**
 * URLs of images of a collection's items, for the mosaics. Only published items;
 * stored for twelve hours, because the header of a large collection must not
 * cost a query on each visit.
 *
 * @param int $collection_id Collection.
 * @param int $count         How many.
 * @return string[]
 */
function tainacan_interface_collection_images( $collection_id, $count ) {
	$collection_id = absint( $collection_id );
	if ( ! $collection_id ) {
		return array();
	}

	$key    = 'tainacan_interface_mosaic_' . $collection_id;
	$stored = get_transient( $key );

	if ( ! is_array( $stored ) ) {
		$items = new WP_Query(
			array(
				'post_type'              => 'tnc_col_' . $collection_id . '_item',
				'post_status'            => 'publish',
				'has_password'           => false,
				'posts_per_page'         => 8,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'suppress_filters'       => true, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters -- the result is stored for everyone: a filter of another plugin for one visitor must not end up in it.
				'meta_key'               => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- limited to 8 rows and kept for twelve hours; there is no other way to ask for items with an image.
			)
		);
		update_post_thumbnail_cache( $items );

		$stored = array();
		foreach ( $items->posts as $item ) {
			$url = get_the_post_thumbnail_url( $item, 'tainacan-medium-full' );
			if ( $url ) {
				$stored[] = $url;
			}
		}
		// An image that arrives later clears this through the hooks below. The spread keeps the lists of many collections from expiring together.
		set_transient( $key, $stored, 12 * HOUR_IN_SECONDS + wp_rand( 0, HOUR_IN_SECONDS ) );
	}

	return array_slice( $stored, 0, max( 1, (int) $count ) );
}

/**
 * An item that enters or leaves the public view, or loses its image, must not
 * stay in the mosaic until the stored list expires.
 *
 * @param string  $new_status New status.
 * @param string  $old_status Old status.
 * @param WP_Post $post       Post.
 */
function tainacan_interface_mosaic_on_status( $new_status, $old_status, $post ) {
	if ( $new_status !== $old_status && ( 'publish' === $new_status || 'publish' === $old_status ) ) {
		tainacan_interface_mosaic_forget( $post );
	}
}
add_action( 'transition_post_status', 'tainacan_interface_mosaic_on_status', 10, 3 );

/**
 * Forgets the mosaic of the collection of an item.
 *
 * @param WP_Post|int $post Post or post id.
 */
function tainacan_interface_mosaic_forget( $post ) {
	static $types = array();

	$type = (string) get_post_type( $post );
	if ( isset( $types[ $type ] ) || 1 !== preg_match( '/^tnc_col_(\d+)_item$/', $type, $match ) ) {
		return;
	}
	$types[ $type ] = true;

	// At the end of the request, once: an import or a bulk edit touches many items, and the list must not be rebuilt from half of them.
	$collection_id = $match[1];
	add_action(
		'shutdown',
		function () use ( $collection_id ) {
			delete_transient( 'tainacan_interface_mosaic_' . $collection_id );
			delete_transient( 'tainacan_interface_collection_counts' );
		}
	);
}
add_action( 'before_delete_post', 'tainacan_interface_mosaic_forget' );

/**
 * The thumbnail of an item changed.
 *
 * @param int    $meta_id   Meta id.
 * @param int    $object_id Post id.
 * @param string $meta_key  Meta key.
 */
function tainacan_interface_mosaic_on_thumbnail( $meta_id, $object_id, $meta_key ) {
	unset( $meta_id );
	if ( '_thumbnail_id' === $meta_key ) {
		tainacan_interface_mosaic_forget( (int) $object_id );
	}
}
add_action( 'updated_post_meta', 'tainacan_interface_mosaic_on_thumbnail', 10, 3 );
add_action( 'added_post_meta', 'tainacan_interface_mosaic_on_thumbnail', 10, 3 );
add_action( 'deleted_post_meta', 'tainacan_interface_mosaic_on_thumbnail', 10, 3 );

/**
 * What the images of the headers and cards may carry: wp_kses_post() would drop
 * the attributes that make a large image load well on a phone.
 *
 * @return array Allowed tags, for wp_kses().
 */
function tainacan_interface_media_tags() {
	return array(
		'img'  => array(
			'src'           => true,
			'srcset'        => true,
			'sizes'         => true,
			'alt'           => true,
			'class'         => true,
			'width'         => true,
			'height'        => true,
			'loading'       => true,
			'decoding'      => true,
			'fetchpriority' => true,
		),
		'span' => array(
			'class'       => true,
			'aria-hidden' => true,
		),
	);
}

/**
 * Prints a mosaic of images. Decorative: the name of the collection is next to it.
 *
 * @param string[] $urls  Image URLs.
 * @param string   $box_class Class of the box.
 * @param bool     $lazy      Whether the images load as they come near the screen; not for what is on it from the start.
 * @return string HTML.
 */
function tainacan_interface_mosaic_html( $urls, $box_class, $lazy = true ) {
	$count = count( $urls );
	if ( ! $count ) {
		return '';
	}
	$html = '<span class="tt2-mosaic tt2-mosaic--' . absint( $count ) . ' ' . esc_attr( $box_class ) . '" aria-hidden="true">';
	foreach ( $urls as $url ) {
		$html .= '<img src="' . esc_url( $url ) . '" alt=""' . ( $lazy ? ' loading="lazy"' : '' ) . ' decoding="async">';
	}
	return $html . '</span>';
}

/**
 * A pattern for a collection or term without any image: chosen by the name, so
 * it stays the same between visits, with the initials over it.
 *
 * @param string $name Name.
 * @return string HTML.
 */
function tainacan_interface_art_html( $name ) {
	$words    = preg_split( '/[\s(]+/u', trim( (string) $name ) );
	$initials = '';
	foreach ( array_slice( array_values( array_filter( (array) $words, 'tainacan_interface_art_word' ) ), 0, 2 ) as $word ) {
		$initials .= mb_strtoupper( mb_substr( $word, 0, 1 ) );
	}
	return '<span class="tt2-art tt2-art--' . absint( abs( crc32( (string) $name ) ) % 8 ) . '" aria-hidden="true">' . esc_html( $initials ) . '</span>';
}

/**
 * Whether a word of a name counts for the initials: not a connective.
 *
 * @param string $word Word.
 * @return bool
 */
function tainacan_interface_art_word( $word ) {
	return mb_strlen( $word ) > 2 && preg_match( '/^\p{L}/u', $word );
}

/**
 * Number of published items of a collection.
 *
 * @param int $collection_id Collection.
 * @return int
 */
function tainacan_interface_collection_count( $collection_id ) {
	static $counts = null;

	if ( null === $counts ) {
		$counts = get_transient( 'tainacan_interface_collection_counts' );
	}
	if ( ! is_array( $counts ) ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- wp_count_posts() is one query per collection; this groups them all in one, and the result goes to a transient.
		$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT post_type, COUNT(*) AS items FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type LIKE %s GROUP BY post_type", $wpdb->esc_like( 'tnc_col_' ) . '%' ) );
		$counts = array();
		foreach ( (array) $rows as $row ) {
			$counts[ (string) $row->post_type ] = (int) $row->items;
		}
		set_transient( 'tainacan_interface_collection_counts', $counts, 10 * MINUTE_IN_SECONDS );
	}

	$type = 'tnc_col_' . absint( $collection_id ) . '_item';
	return isset( $counts[ $type ] ) ? $counts[ $type ] : 0;
}

/**
 * Whether the blog page shows the sidebar: it has widgets, and on the new design
 * it was not turned off.
 *
 * @return bool
 */
function tainacan_interface_has_sidebar() {
	if ( ! is_active_sidebar( 'tainacan-sidebar-right' ) ) {
		return false;
	}
	// The blog and its archives; the lists of collections and taxonomies have no sidebar.
	$blog = is_home() || is_search() || ( is_archive() && ! is_post_type_archive() && ! is_tax() );
	return 'none' !== tainacan_interface_opt( 'sidebar_position' ) && $blog && ! tainacan_interface_is_list_page();
}

/**
 * Whether a part of the page of a post shows: always on the classic design.
 *
 * @param string $part featured, meta, share, terms or nav.
 * @return bool
 */
function tainacan_interface_post_shows( $part ) {
	return (bool) tainacan_interface_opt( 'post_' . $part );
}

/**
 * The emoji script of WordPress, left out when the administrator chose to.
 */
function tainacan_interface_without_emoji() {
	if ( is_admin() || ! tainacan_interface_opt( 'perf_emoji' ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}
// After the Customizer applies the previewed values, before the head is printed.
add_action( 'template_redirect', 'tainacan_interface_without_emoji' );

/**
 * The footer menu of the new design.
 */
function tainacan_interface_register_footer_menu() {
	register_nav_menus( array( 'tt2_footer' => __( 'Footer menu', 'tainacan-interface' ) ) );
}
add_action( 'init', 'tainacan_interface_register_footer_menu', 20 ); // After the translations load (on init).

/**
 * An address for a CSS url('…') in a style attribute, before esc_url().
 *
 * The function esc_url() keeps the single quote (as &#039;, which the HTML
 * parser turns back into a quote inside the attribute) and the parentheses,
 * and either one ends the CSS string or the url(). They are percent-encoded
 * here, with the double quote and the backslash, which leaves the address
 * the same.
 *
 * @param string $url Address.
 * @return string Unescaped address; escape with esc_url() on output.
 */
function tainacan_interface_css_url( $url ) {
	return str_replace( array( "'", '"', '(', ')', '\\' ), array( '%27', '%22', '%28', '%29', '%5C' ), (string) $url );
}
