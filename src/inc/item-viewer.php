<?php
/**
 * Document and image viewer on the item page.
 *
 * Clicking a file of the item gallery opens the theme's viewer instead of the
 * Tainacan lightbox: images with zoom, pan and pinch, video and audio in the
 * native player, PDF in a reader (PDF.js, loaded only when a PDF is opened),
 * and the previous/next files of the gallery. Rebuilt from the Memorial COVID
 * Toolkit gallery, with one change of method: the server marks each gallery
 * slide with the id of its file, so the browser no longer guesses the file
 * from the image path.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_PDFJS_VERSION = '4.10.38';

/**
 * Whether the viewer is on. Theme mod in the Customizer, plus a filter.
 *
 * @return bool
 */
function tainacan_interface_viewer_enabled() {
	/**
	 * Turns the item file viewer on or off.
	 *
	 * @param bool $enabled Value of the Customizer option.
	 */
	return (bool) apply_filters( 'tainacan_interface_viewer_enabled', (bool) get_theme_mod( 'tainacan_interface_item_viewer', true ) );
}

/**
 * What kind of view a file gets, from its MIME type.
 *
 * Images the browser cannot draw (TIFF, HEIC) count as images only when
 * WordPress generated a drawable size for them; otherwise they are files to
 * download, and the click goes on as a download.
 *
 * @param string $mime  MIME type.
 * @param string $path  File path (for the .pdf fallback).
 * @param bool   $sized Whether WordPress generated intermediate sizes.
 * @return string image|video|audio|pdf|other
 */
function tainacan_interface_viewer_kind( $mime, $path, $sized ) {
	$drawable = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/svg+xml' );

	if ( 'application/pdf' === $mime || ( '' === $mime && '.pdf' === strtolower( substr( $path, -4 ) ) ) ) {
		return 'pdf';
	}
	if ( 0 === strpos( $mime, 'image/' ) ) {
		return in_array( $mime, $drawable, true ) || $sized ? 'image' : 'other';
	}
	if ( 0 === strpos( $mime, 'video/' ) ) {
		return 'video';
	}
	if ( 0 === strpos( $mime, 'audio/' ) ) {
		return 'audio';
	}
	return 'other';
}

/**
 * Decoded URL path, the form both the index and the lookup use (an accented
 * file name only matches decoded).
 *
 * @param string $url URL.
 * @return string
 */
function tainacan_interface_viewer_path( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );
	return is_string( $path ) ? rawurldecode( $path ) : '';
}

/**
 * Description of one attachment for the viewer, or null when it has no URL.
 *
 * @param int $attachment_id Attachment ID.
 * @return array|null
 */
function tainacan_interface_viewer_describe( $attachment_id ) {
	$url = wp_get_attachment_url( $attachment_id );
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}

	$mime  = (string) get_post_mime_type( $attachment_id );
	$path  = tainacan_interface_viewer_path( $url );
	$meta  = wp_get_attachment_metadata( $attachment_id );
	$sizes = is_array( $meta ) && ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ? $meta['sizes'] : array();
	$kind  = tainacan_interface_viewer_kind( $mime, $path, ! empty( $sizes ) );

	// The reader fetches the PDF: only a file served by this site can be read.
	if ( 'pdf' === $kind && wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		$kind = 'other';
	}

	$file = array(
		'id'       => (int) $attachment_id,
		'kind'     => $kind,
		'url'      => $url,
		'src'      => $url,
		'srcset'   => '',
		'filename' => '' !== $path ? wp_basename( $path ) : $url,
		'title'    => tainacan_interface_plain( get_the_title( $attachment_id ) ),
		'caption'  => tainacan_interface_plain( wp_get_attachment_caption( $attachment_id ) ),
		'mime'     => $mime,
	);

	if ( 'image' === $kind ) {
		$large = wp_get_attachment_image_src( $attachment_id, 'large' );
		if ( is_array( $large ) && ! empty( $large[0] ) && ! in_array( $mime, array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/svg+xml' ), true ) ) {
			// TIFF, HEIC, BMP: show the generated JPEG, keep the original for download.
			$file['src'] = $large[0];
		}
		$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
		if ( is_string( $srcset ) && $file['src'] === $url ) {
			$file['srcset'] = $srcset;
		}
	}

	$paths = array( $path );
	if ( '' !== $path ) {
		$base = substr( $path, 0, (int) strrpos( $path, '/' ) + 1 );
		foreach ( $sizes as $size ) {
			if ( ! empty( $size['file'] ) ) {
				$paths[] = $base . $size['file'];
			}
		}
		if ( ! empty( $meta['original_image'] ) ) {
			$paths[] = $base . $meta['original_image'];
		}
	}
	$file['paths'] = array_values( array_unique( array_filter( $paths ) ) );

	return $file;
}

/**
 * The item's files, in gallery order: the document (when it is a file), then
 * the attachments. Memoized per request and item.
 *
 * @param int $item_id Item post ID.
 * @return array<int, array> Keyed by attachment ID.
 */
function tainacan_interface_viewer_files( $item_id ) {
	static $memo = array();

	$item_id = (int) $item_id;
	if ( isset( $memo[ $item_id ] ) ) {
		return $memo[ $item_id ];
	}

	$files = array();
	if ( function_exists( 'tainacan_get_the_document_type' ) && 'attachment' === tainacan_get_the_document_type( $item_id ) ) {
		$document = tainacan_interface_viewer_describe( (int) tainacan_get_the_document_raw( $item_id ) );
		if ( $document ) {
			$files[ $document['id'] ] = $document;
		}
	}
	if ( function_exists( 'tainacan_get_the_attachments' ) ) {
		foreach ( (array) tainacan_get_the_attachments( null, $item_id ) as $attachment ) {
			if ( $attachment instanceof WP_Post && ! isset( $files[ $attachment->ID ] ) ) {
				$described = tainacan_interface_viewer_describe( (int) $attachment->ID );
				if ( $described ) {
					$files[ $described['id'] ] = $described;
				}
			}
		}
	}

	$memo[ $item_id ] = $files;
	return $files;
}

/**
 * Marks each slide of the item gallery with the id of its file.
 *
 * A slide carries only the URL of one of the generated sizes (images), the
 * file URL (the document's iframe) or tainacan_attachment_html/<id> (other
 * attachments). The first pass finds, for each slide, the first such address
 * that belongs to a file of the item; the second pass writes data-tt2-file on
 * that slide. Slides of embeds (YouTube, text documents) stay unmarked and
 * keep the Tainacan behaviour.
 *
 * @param string $html Gallery HTML.
 * @param array  $args Gallery arguments.
 * @return string
 */
function tainacan_interface_viewer_mark_gallery( $html, $args = array() ) {
	if ( ! tainacan_interface_viewer_enabled() || ! tainacan_interface_is_item_page() || ! class_exists( 'WP_HTML_Tag_Processor' ) || '' === (string) $html ) {
		return $html;
	}

	// The site turned the lightbox off: clicking a file does nothing, and so does the viewer.
	if ( is_array( $args ) && isset( $args['openLightboxOnClick'] ) && ! $args['openLightboxOnClick'] ) {
		return $html;
	}

	$files = tainacan_interface_viewer_files( get_queried_object_id() );
	if ( empty( $files ) ) {
		return $html;
	}

	$index = array();
	foreach ( $files as $id => $file ) {
		foreach ( $file['paths'] as $path ) {
			$index[ $path ] = $id;
		}
	}

	$found = array();
	$slide = -1;
	$scan  = new WP_HTML_Tag_Processor( $html );
	while ( $scan->next_tag() ) {
		if ( tainacan_interface_viewer_is_slide( $scan ) ) {
			++$slide;
			continue;
		}
		if ( $slide < 0 || isset( $found[ $slide ] ) ) {
			continue;
		}
		foreach ( array( 'src', 'href', 'data-src' ) as $attribute ) {
			$value = $scan->get_attribute( $attribute );
			if ( ! is_string( $value ) || '' === $value ) {
				continue;
			}
			if ( preg_match( '#tainacan_attachment_html/(\d+)#', $value, $match ) && isset( $files[ (int) $match[1] ] ) ) {
				$found[ $slide ] = (int) $match[1];
				break;
			}
			$path = tainacan_interface_viewer_path( html_entity_decode( $value, ENT_QUOTES, 'UTF-8' ) );
			if ( isset( $index[ $path ] ) ) {
				$found[ $slide ] = $index[ $path ];
				break;
			}
		}
	}

	if ( empty( $found ) ) {
		return $html;
	}

	$slide = -1;
	$write = new WP_HTML_Tag_Processor( $html );
	while ( $write->next_tag() ) {
		if ( ! tainacan_interface_viewer_is_slide( $write ) ) {
			continue;
		}
		++$slide;
		if ( isset( $found[ $slide ] ) && 'other' !== $files[ $found[ $slide ] ]['kind'] ) {
			$write->set_attribute( 'data-tt2-file', (string) $found[ $slide ] );
		}
	}

	return $write->get_updated_html();
}
add_filter( 'get_tainacan_item_gallery', 'tainacan_interface_viewer_mark_gallery', 10, 2 );

/**
 * Puts the theme's PDF reader in the page where Tainacan prints the browser's
 * own viewer (an iframe of the PDF). The reader is drawn by item-viewer.js;
 * this shell carries the file and, without JavaScript, links to the PDF. The
 * iframe is never printed, so the browser does not fetch the PDF twice.
 *
 * Steps aside when the Memorial COVID Toolkit already replaces the document.
 *
 * @param string $html Document HTML from Tainacan.
 * @param mixed  $item Item entity.
 * @return string
 */
function tainacan_interface_viewer_inline_pdf( $html, $item ) {
	if ( ! tainacan_interface_viewer_enabled() || ! tainacan_interface_is_item_page() || defined( 'MCTK_VERSION' ) ) {
		return $html;
	}
	if ( ! $item instanceof \Tainacan\Entities\Item || (int) $item->get_id() !== (int) get_queried_object_id() || 'attachment' !== $item->get_document_type() ) {
		return $html;
	}

	$files = tainacan_interface_viewer_files( $item->get_id() );
	$id    = (int) $item->get_document();
	if ( ! isset( $files[ $id ] ) || 'pdf' !== $files[ $id ]['kind'] ) {
		return $html;
	}
	$file = $files[ $id ];

	return sprintf(
		// The link stays in <noscript>: Tainacan takes the first link of a slide as its lightbox trigger.
		'<div class="tt2-reader swiper-no-swiping" data-tt2-reader="%1$d" data-src="%2$s"><noscript><p class="tt2-reader__fallback"><a href="%2$s" target="_blank" rel="noopener">%3$s</a></p></noscript></div>',
		$id,
		esc_url( $file['url'] ),
		/* translators: %s: file name of the PDF. */
		esc_html( sprintf( __( 'Open the PDF: %s', 'tainacan-interface' ), $file['filename'] ) )
	);
}
add_filter( 'tainacan-get-the-document', 'tainacan_interface_viewer_inline_pdf', 20, 2 );

/**
 * Whether the current tag opens one media item of the gallery. Tainacan 1.1
 * marks it .swiper-slide; Tainacan 1.4 marks it .tainacan-media-item, and adds
 * .swiper-slide only when the carousel is on. An element with both counts once.
 *
 * @param WP_HTML_Tag_Processor $processor Processor on a tag.
 * @return bool
 */
function tainacan_interface_viewer_is_slide( $processor ) {
	return $processor->has_class( 'swiper-slide' ) || $processor->has_class( 'tainacan-media-item' );
}

/**
 * Script, style and the file list, only on item pages that have files.
 */
function tainacan_interface_viewer_enqueue() {
	if ( ! tainacan_interface_viewer_enabled() || ! tainacan_interface_is_item_page() ) {
		return;
	}

	$files = array();
	foreach ( tainacan_interface_viewer_files( get_queried_object_id() ) as $id => $file ) {
		if ( 'other' === $file['kind'] ) {
			continue;
		}
		unset( $file['paths'] );
		$files[ (string) $id ] = $file;
	}
	if ( empty( $files ) ) {
		return;
	}

	$uri    = get_template_directory_uri();
	$vendor = $uri . '/assets/vendor/pdfjs/';

	wp_enqueue_style( 'tt2-item-viewer', $uri . '/assets/css/item-viewer.css', array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script( 'tt2-item-viewer', $uri . '/assets/js/item-viewer.js', array(), TAINACAN_INTERFACE_VERSION, true );

	$config = array(
		'files' => $files,
		'pdf'   => array(
			'lib'    => add_query_arg( 'ver', TAINACAN_INTERFACE_PDFJS_VERSION, $vendor . 'pdf.min.js' ),
			'worker' => add_query_arg( 'ver', TAINACAN_INTERFACE_PDFJS_VERSION, $vendor . 'pdf.worker.min.js' ),
			'fonts'  => $vendor . 'standard_fonts/',
		),
		'i18n'  => array(
			'dialog'     => __( 'File viewer', 'tainacan-interface' ),
			'close'      => __( 'Close', 'tainacan-interface' ),
			'prev'       => __( 'Previous file', 'tainacan-interface' ),
			'next'       => __( 'Next file', 'tainacan-interface' ),
			'zoomIn'     => __( 'Zoom in', 'tainacan-interface' ),
			'zoomOut'    => __( 'Zoom out', 'tainacan-interface' ),
			'zoomReset'  => __( 'Fit to screen', 'tainacan-interface' ),
			'download'   => __( 'Download', 'tainacan-interface' ),
			'original'   => __( 'Open the original file', 'tainacan-interface' ),
			'openViewer' => __( 'Open in the viewer', 'tainacan-interface' ),
			'reader'     => __( 'PDF reader', 'tainacan-interface' ),
			'prevPage'   => __( 'Previous page', 'tainacan-interface' ),
			'nextPage'   => __( 'Next page', 'tainacan-interface' ),
			'pageNumber' => __( 'Page number', 'tainacan-interface' ),
			'fitWidth'   => __( 'Fit to width', 'tainacan-interface' ),
			'fullscreen' => __( 'Full screen', 'tainacan-interface' ),
			'loading'    => __( 'Loading…', 'tainacan-interface' ),
			'unplayable' => __( 'This browser cannot play this file. You can download it.', 'tainacan-interface' ),
			'unreadable' => __( 'The reader could not open this PDF. You can open the original file.', 'tainacan-interface' ),
			/* translators: 1: position of the file, 2: number of files. */
			'count'      => __( '%1$d of %2$d', 'tainacan-interface' ),
			/* translators: 1: current page, 2: number of pages. */
			'page'       => __( 'Page %1$d of %2$d', 'tainacan-interface' ),
		),
	);

	// JSON, not wp_localize_script(): it would turn booleans and numbers into strings.
	wp_add_inline_script( 'tt2-item-viewer', 'window.tt2Viewer = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_viewer_enqueue' );
