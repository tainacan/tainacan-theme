<?php
/**
 * Pictures for items, collections and terms without an image.
 *
 * Where there is no thumbnail, the Tainacan plugin shows a gray square or a gray
 * icon. The theme draws its own picture of the kind of file instead: image,
 * audio, video, PDF, text, spreadsheet, presentation, compressed file, 3D model,
 * link, other file or no document. Each kind has its own color, or all follow the
 * colors of the site (Customize > Item cards). The pictures are SVG drawn by the
 * theme on request, so the label follows the language of the site and the colors
 * follow the settings, and nothing in the plugin is touched:
 *
 * - PHP: filter tainacan-get-the-mime-type-icon (item page, attachments,
 *   relationship metadata, terms);
 * - JavaScript: filter tainacan_get_the_mime_type_icon of wp.hooks (lists of
 *   items and blocks), plus a swap of the gray square that some blocks of the
 *   plugin write straight in the page;
 * - the templates of the theme (grid view mode, threads beside the item).
 *
 * Icons adapted from Lucide (https://lucide.dev), ISC license.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How the pictures are drawn: 'type' (a color for each kind of file), 'site'
 * (the colors of the site) or 'original' (the pictures of the plugin).
 *
 * @return string
 */
function tainacan_interface_placeholder_mode() {
	return (string) tainacan_interface_opt( 'placeholder_style' );
}

/**
 * Whether the theme draws the pictures.
 *
 * @return bool
 */
function tainacan_interface_placeholder_on() {
	return 'original' !== tainacan_interface_placeholder_mode();
}

/**
 * The kinds of file: color and label of each.
 *
 * @return array Kind => array( color, label ).
 */
function tainacan_interface_placeholder_kinds() {
	return array(
		'image'      => array( '#0f766e', __( 'Image', 'tainacan-interface' ) ),
		'audio'      => array( '#7c3aed', __( 'Audio', 'tainacan-interface' ) ),
		'video'      => array( '#be123c', __( 'Video', 'tainacan-interface' ) ),
		'pdf'        => array( '#b91c1c', __( 'PDF document', 'tainacan-interface' ) ),
		'text'       => array( '#1d4ed8', __( 'Text', 'tainacan-interface' ) ),
		'sheet'      => array( '#15803d', __( 'Spreadsheet', 'tainacan-interface' ) ),
		'slides'     => array( '#c2410c', __( 'Presentation', 'tainacan-interface' ) ),
		'archive'    => array( '#a16207', __( 'Compressed file', 'tainacan-interface' ) ),
		'model'      => array( '#4338ca', __( '3D model', 'tainacan-interface' ) ),
		'url'        => array( '#0e7490', __( 'Link', 'tainacan-interface' ) ),
		'attachment' => array( '#475569', __( 'File', 'tainacan-interface' ) ),
		'empty'      => array( '#64748b', __( 'No image', 'tainacan-interface' ) ),
	);
}

/**
 * Which kind each document type or MIME type is. The same table goes to the
 * JavaScript, so both sides agree.
 *
 * @return array 'exact' => value => kind; 'prefix' => start => kind.
 */
function tainacan_interface_placeholder_rules() {
	return array(
		'exact'  => array(
			''                                        => 'empty',
			'empty'                                   => 'empty',
			'image'                                   => 'image',
			'audio'                                   => 'audio',
			'video'                                   => 'video',
			'text'                                    => 'text',
			'url'                                     => 'url',
			'embed'                                   => 'url',
			'attachment'                              => 'attachment',
			'application/pdf'                         => 'pdf',
			'text/csv'                                => 'sheet',
			'text/tab-separated-values'               => 'sheet',
			'application/msword'                      => 'text',
			'application/rtf'                         => 'text',
			'application/epub+zip'                    => 'text',
			'application/json'                        => 'text',
			'application/xml'                         => 'text',
			'application/vnd.oasis.opendocument.text' => 'text',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'text',
			'application/vnd.ms-excel'                => 'sheet',
			'application/vnd.oasis.opendocument.spreadsheet' => 'sheet',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'sheet',
			'application/vnd.ms-powerpoint'           => 'slides',
			'application/vnd.oasis.opendocument.presentation' => 'slides',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'slides',
			'application/zip'                         => 'archive',
			'application/x-zip-compressed'            => 'archive',
			'application/vnd.rar'                     => 'archive',
			'application/x-rar-compressed'            => 'archive',
			'application/x-7z-compressed'             => 'archive',
			'application/x-tar'                       => 'archive',
			'application/gzip'                        => 'archive',
			'application/x-gzip'                      => 'archive',
		),
		'prefix' => array(
			'image/' => 'image',
			'audio/' => 'audio',
			'video/' => 'video',
			'model/' => 'model',
			'text/'  => 'text',
		),
	);
}

/**
 * The kind of a document type or MIME type.
 *
 * @param string $mime Document type ('empty', 'url', 'text') or MIME type.
 * @return string Kind.
 */
function tainacan_interface_placeholder_kind( $mime ) {
	$mime  = strtolower( trim( (string) $mime ) );
	$rules = tainacan_interface_placeholder_rules();
	if ( isset( $rules['exact'][ $mime ] ) ) {
		return $rules['exact'][ $mime ];
	}
	foreach ( $rules['prefix'] as $start => $kind ) {
		if ( 0 === strpos( $mime, $start ) ) {
			return $kind;
		}
	}
	return 'attachment';
}

/**
 * Whether a size name of the plugin is the small one (40px in the plugin).
 *
 * @param string $size Size as the plugin passes it ('_small', 'tainacan-small', 'thumbnail'...).
 * @return bool
 */
function tainacan_interface_placeholder_is_small( $size ) {
	return in_array( (string) $size, array( '_small', 'small', 'tainacan-small', 'thumbnail' ), true );
}

/**
 * The colors of a picture.
 *
 * @param string $kind          Kind.
 * @param int    $collection_id Collection whose colors count, 0 for the site.
 * @return array ink (icon and label), a and b (the two ends of the background).
 */
function tainacan_interface_placeholder_colors( $kind, $collection_id = 0 ) {
	if ( 'site' === tainacan_interface_placeholder_mode() ) {
		$accent = tainacan_interface_placeholder_hex( tainacan_interface_opt( 'accent_color', $collection_id ) );
		$brand  = tainacan_interface_placeholder_hex( tainacan_interface_opt( 'brand_color', $collection_id ) );
		$ink    = '#1f2937';
		if ( tainacan_interface_is_dark_color( $accent ) ) {
			$ink = $accent;
		} elseif ( tainacan_interface_is_dark_color( $brand ) ) {
			$ink = $brand;
		}
		return array(
			'ink' => $ink,
			'a'   => $accent,
			'b'   => $brand,
		);
	}
	$kinds = tainacan_interface_placeholder_kinds();
	$color = isset( $kinds[ $kind ] ) ? $kinds[ $kind ][0] : $kinds['empty'][0];
	return array(
		'ink' => $color,
		'a'   => $color,
		'b'   => $color,
	);
}

/**
 * A color as #rrggbb.
 *
 * @param string $color Color as #rgb or #rrggbb.
 * @return string
 */
function tainacan_interface_placeholder_hex( $color ) {
	$rgb = tainacan_hex2rgb( (string) $color );
	if ( empty( $rgb ) ) {
		return '#64748b';
	}
	return sprintf( '#%02x%02x%02x', $rgb['red'], $rgb['green'], $rgb['blue'] );
}

/**
 * A color mixed with white (light tints) or black (deeper tones).
 *
 * @param string $color  Color as #rrggbb.
 * @param float  $amount Share of the color: 0 is pure white (or black), 1 the color.
 * @param bool   $dark   Mix with black instead of white.
 * @return string #rrggbb.
 */
function tainacan_interface_placeholder_mix( $color, $amount, $dark = false ) {
	$rgb  = tainacan_hex2rgb( $color );
	$base = $dark ? 0 : 255;
	$out  = '#';
	foreach ( array( 'red', 'green', 'blue' ) as $channel ) {
		$value = isset( $rgb[ $channel ] ) ? (int) $rgb[ $channel ] : 128;
		$out  .= sprintf( '%02x', (int) round( $base + ( $value - $base ) * $amount ) );
	}
	return $out;
}

/**
 * The icon of a kind, drawn on a 24 x 24 grid with strokes.
 *
 * @param string $kind Kind.
 * @param string $ink  Color of the badge of the PDF.
 * @return string SVG shapes, constant markup of this file.
 */
function tainacan_interface_placeholder_icon( $kind, $ink ) {
	$file = '<path d="M14.5 2H6.5a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7z"/><path d="M14 2v4.5a1 1 0 0 0 1 1h4.5"/>';

	switch ( $kind ) {
		case 'image':
			return '<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/>';
		case 'audio':
			return '<path d="M2.5 10.5v3"/><path d="M6.5 7v10"/><path d="M10.5 3.5v17"/><path d="M14.5 8v8"/><path d="M18.5 5.5v13"/><path d="M22 10.5v3"/>';
		case 'video':
			return '<rect x="2" y="4" width="20" height="16" rx="3"/><path d="M10 9.2v5.6a.5.5 0 0 0 .76.43l4.6-2.8a.5.5 0 0 0 0-.86l-4.6-2.8a.5.5 0 0 0-.76.43z" fill="currentColor"/>';
		case 'pdf':
			return $file . '<rect x="1.5" y="12" width="13" height="7.5" rx="1.8" fill="' . esc_attr( $ink ) . '" stroke="none"/><text x="8" y="17.6" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="5.2" font-weight="700" fill="#ffffff" stroke="none">PDF</text>';
		case 'text':
			return $file . '<path d="M8.5 12.5h7"/><path d="M8.5 16h7"/><path d="M8.5 9h2.5"/>';
		case 'sheet':
			return $file . '<rect x="7.5" y="11" width="9" height="7" rx="0.6"/><path d="M7.5 14.5h9"/><path d="M12 11v7"/>';
		case 'slides':
			return '<path d="M2 3.5h20"/><path d="M20.5 3.5v10.5a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2V3.5"/><path d="m7.5 21 4.5-5 4.5 5"/><path d="M8 11.5l2.5-2.5 2 2 3.5-3.5"/>';
		case 'archive':
			return '<rect x="2" y="3" width="20" height="5" rx="1.2"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>';
		case 'model':
			return '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>';
		case 'url':
			return '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>';
		case 'attachment':
			return '<path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>';
		default:
			return '<rect x="3" y="3" width="18" height="18" rx="2.5" stroke-dasharray="3.2 2.6"/><circle cx="9" cy="9" r="1.8"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/>';
	}
}

/**
 * The picture of a kind, as SVG.
 *
 * @param string $kind          Kind.
 * @param bool   $small         The small version (40px): no label, bigger icon.
 * @param int    $collection_id Collection whose colors count, 0 for the site.
 * @return string SVG document.
 */
function tainacan_interface_placeholder_svg( $kind, $small = false, $collection_id = 0 ) {
	$kinds  = tainacan_interface_placeholder_kinds();
	$kind   = isset( $kinds[ $kind ] ) ? $kind : 'empty';
	$colors = tainacan_interface_placeholder_colors( $kind, $collection_id );
	$ink    = $colors['ink'];
	$label  = $kinds[ $kind ][1];
	$from   = tainacan_interface_placeholder_mix( $colors['a'], 0.07 );
	$to     = tainacan_interface_placeholder_mix( $colors['b'], 0.2 );
	$icon   = tainacan_interface_placeholder_icon( $kind, $ink );

	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300" role="img" aria-label="' . esc_attr( $label ) . '">';
	$svg .= '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . esc_attr( $from ) . '"/><stop offset="1" stop-color="' . esc_attr( $to ) . '"/></linearGradient>';
	if ( ! $small ) {
		$svg .= '<filter id="s" x="-30%" y="-30%" width="160%" height="170%"><feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="' . esc_attr( $ink ) . '" flood-opacity="0.18"/></filter>';
	}
	$svg .= '</defs><rect width="300" height="300" fill="url(#g)"/>';

	if ( $small ) {
		$svg .= '<rect x="55" y="55" width="190" height="190" rx="46" fill="#ffffff" fill-opacity="0.9"/>';
		$svg .= '<g transform="translate(90 90) scale(5)" fill="none" stroke="' . esc_attr( $ink ) . '" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" color="' . esc_attr( $ink ) . '">' . $icon . '</g>';
		return $svg . '</svg>';
	}

	$svg .= '<circle cx="268" cy="30" r="120" fill="' . esc_attr( $colors['a'] ) . '" fill-opacity="0.07"/>';
	$svg .= '<circle cx="22" cy="296" r="96" fill="' . esc_attr( $colors['b'] ) . '" fill-opacity="0.08"/>';
	// Everything between y 76 and 226: a 16:9 crop of the square (y 66 to 234) still shows it all.
	$svg .= '<rect x="98" y="76" width="104" height="104" rx="28" fill="#ffffff" fill-opacity="0.94" filter="url(#s)"/>';
	$svg .= '<g transform="translate(117 95) scale(2.75)" fill="none" stroke="' . esc_attr( $ink ) . '" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" color="' . esc_attr( $ink ) . '">' . $icon . '</g>';
	$svg .= '<text x="150" y="212" text-anchor="middle" font-family="Inter, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif" font-size="17" font-weight="600" letter-spacing="0.2" fill="' . esc_attr( tainacan_interface_placeholder_mix( $ink, 0.85, true ) ) . '">' . esc_xml( $label ) . '</text>';
	$svg .= '<rect x="136" y="223" width="28" height="3" rx="1.5" fill="' . esc_attr( $ink ) . '" fill-opacity="0.35"/>';
	return $svg . '</svg>';
}

/**
 * Version of the pictures in their address: changes with the theme, the way
 * they are drawn, the language and, when they follow the site, the colors.
 *
 * @param int $collection_id Collection whose colors count, 0 for the site.
 * @return string
 */
function tainacan_interface_placeholder_version( $collection_id = 0 ) {
	static $versions = array();

	$collection_id = (int) $collection_id;
	if ( ! isset( $versions[ $collection_id ] ) ) {
		$parts = array( TAINACAN_INTERFACE_VERSION, tainacan_interface_placeholder_mode(), determine_locale() );
		if ( 'site' === tainacan_interface_placeholder_mode() ) {
			$parts[] = tainacan_interface_opt( 'accent_color', $collection_id );
			$parts[] = tainacan_interface_opt( 'brand_color', $collection_id );
		}
		$versions[ $collection_id ] = substr( md5( implode( '|', $parts ) ), 0, 8 );
	}
	return $versions[ $collection_id ];
}

/**
 * Address of a picture.
 *
 * @param string $kind          Kind, or '__KIND__' for the template of the JavaScript.
 * @param string $size          's' (small) or 'm'; '__SIZE__' for the template.
 * @param int    $collection_id Collection whose colors count, 0 for the site.
 * @return string
 */
function tainacan_interface_placeholder_url( $kind, $size = 'm', $collection_id = 0 ) {
	$collection_id = 'site' === tainacan_interface_placeholder_mode() ? (int) $collection_id : 0;
	$args          = array(
		'tt2-ph' => $kind,
		's'      => $size,
	);
	if ( $collection_id ) {
		$args['c'] = $collection_id;
	}
	$args['v'] = tainacan_interface_placeholder_version( $collection_id );
	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * The collection of the page, 0 outside a collection.
 *
 * @return int
 */
function tainacan_interface_placeholder_collection() {
	return function_exists( 'tainacan_get_collection_id' ) ? (int) tainacan_get_collection_id() : 0;
}

/**
 * Address of the picture of an item without thumbnail, by its document.
 *
 * @param int  $item_id Item.
 * @param bool $small   The small version.
 * @return string '' when the theme does not draw the pictures.
 */
function tainacan_interface_item_placeholder( $item_id, $small = false ) {
	if ( ! tainacan_interface_placeholder_on() || ! class_exists( '\Tainacan\Entities\Item' ) ) {
		return '';
	}
	$item          = new \Tainacan\Entities\Item( (int) $item_id );
	$collection_id = preg_match( '/^tnc_col_(\d+)_item$/', (string) get_post_type( $item_id ), $match ) ? (int) $match[1] : 0;
	return tainacan_interface_placeholder_url( tainacan_interface_placeholder_kind( $item->get_document_mimetype() ), $small ? 's' : 'm', $collection_id );
}

/**
 * Filter of the plugin: its picture for a document type becomes the theme's.
 *
 * @param string $src        Address of the picture of the plugin.
 * @param string $mime_type  Document type or MIME type.
 * @param string $image_size Size ('', '_medium', '_small').
 * @return string
 */
function tainacan_interface_placeholder_filter( $src, $mime_type = '', $image_size = '' ) {
	if ( ! tainacan_interface_placeholder_on() ) {
		return $src;
	}
	return tainacan_interface_placeholder_url(
		tainacan_interface_placeholder_kind( $mime_type ),
		tainacan_interface_placeholder_is_small( $image_size ) ? 's' : 'm',
		tainacan_interface_placeholder_collection()
	);
}
add_filter( 'tainacan-get-the-mime-type-icon', 'tainacan_interface_placeholder_filter', 10, 3 );

/**
 * Serves a picture: ?tt2-ph=<kind>&s=<s|m>&c=<collection>&v=<version>.
 * Read-only and public; the address changes when the picture changes, so the
 * browser keeps it for a year.
 */
function tainacan_interface_placeholder_serve() {
	$kind = sanitize_key( tainacan_interface_query_arg( 'tt2-ph' ) );
	if ( '' === $kind || is_admin() ) {
		return;
	}
	$kinds = tainacan_interface_placeholder_kinds();
	if ( ! isset( $kinds[ $kind ] ) ) {
		status_header( 404 );
		exit;
	}
	$collection_id = absint( tainacan_interface_query_arg( 'c', '0' ) );
	if ( $collection_id && 'tainacan-collection' !== get_post_type( $collection_id ) ) {
		$collection_id = 0;
	}
	// Served before init 20, where the theme loads its translations: load them now for the label.
	if ( function_exists( 'tainacan_interface_load_translations' ) ) {
		tainacan_interface_load_translations();
	}
	$size  = tainacan_interface_query_arg( 's' );
	$small = ( 's' === $size );
	$svg   = tainacan_interface_placeholder_svg( $kind, $small, $collection_id );

	status_header( 200 );
	header( 'Content-Type: image/svg+xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=31536000, immutable' );
	header( 'X-Content-Type-Options: nosniff' );
	header( "Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'" );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an SVG image, not HTML: wp_kses() would need an SVG allow-list for markup this file builds from constants; the label goes through esc_xml() and every color through esc_attr() where the SVG is built.
	echo $svg; // tnc-check-ignore TNC-S05: an SVG image, not HTML; escaped where it is built (see the line above).
	exit;
}
add_action( 'init', 'tainacan_interface_placeholder_serve', 1 );

/**
 * Whether the page may show pictures drawn by the JavaScript of the plugin: the
 * block editor, the Tainacan pages (lists, collection, item, term) and the pages
 * with Tainacan blocks.
 *
 * @return bool
 */
function tainacan_interface_placeholder_needs_script() {
	$needed = is_admin() || tainacan_interface_is_list_page() || tainacan_interface_placeholder_collection() > 0;
	if ( ! $needed && is_singular() ) {
		$needed = false !== strpos( (string) get_post_field( 'post_content', get_queried_object_id() ), '<!-- wp:tainacan/' );
	}

	/**
	 * Filters whether the page loads the script of the pictures for what has no image.
	 *
	 * @param bool $needed Whether the page needs it.
	 */
	return (bool) apply_filters( 'tainacan_interface_placeholder_script', $needed );
}

/**
 * The JavaScript side: the filter of wp.hooks for the lists and blocks of the
 * plugin, and the swap of the gray square some blocks write in the page.
 */
function tainacan_interface_placeholder_script() {
	if ( ! tainacan_interface_placeholder_on() || ! tainacan_interface_placeholder_needs_script() ) {
		return;
	}
	wp_enqueue_script( 'tainacan-interface-placeholders', get_template_directory_uri() . '/assets/js/placeholders.js', array( 'wp-hooks' ), TAINACAN_INTERFACE_VERSION, false );
	wp_add_inline_script(
		'tainacan-interface-placeholders',
		'window.tainacanInterfacePlaceholders = ' . wp_json_encode(
			array_merge(
				tainacan_interface_placeholder_rules(),
				array( 'url' => tainacan_interface_placeholder_url( '__KIND__', '__SIZE__', tainacan_interface_placeholder_collection() ) )
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_placeholder_script' );
add_action( 'enqueue_block_editor_assets', 'tainacan_interface_placeholder_script' );
