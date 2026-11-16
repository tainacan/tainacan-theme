<?php
/**
 * Item preview drawer on the Tainacan lists.
 *
 * A plain click on an item of a list opens a side drawer with the cover, the
 * title, the description and the metadata the list is showing; the arrows walk
 * the page, Esc closes and gives the focus back. Ctrl/Cmd/Shift/Alt and the
 * middle button open the item page as always. Ported from TainacanBiblio's
 * item peek, without the library parts.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_REST_NAMESPACE = 'tainacan-interface/v1';

/**
 * Whether the drawer is on. Theme mod in the Customizer, plus a filter.
 *
 * @return bool
 */
function tainacan_interface_drawer_enabled() {
	/**
	 * Turns the item preview drawer on or off.
	 *
	 * @param bool $enabled Value of the Customizer option.
	 */
	return (bool) apply_filters( 'tainacan_interface_drawer_enabled', (bool) get_theme_mod( 'tainacan_interface_item_drawer', true ) );
}

/**
 * Registers the preview route.
 */
function tainacan_interface_drawer_register_route() {
	register_rest_route(
		TAINACAN_INTERFACE_REST_NAMESPACE,
		'/item/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'tainacan_interface_drawer_rest_item',
			'permission_callback' => 'tainacan_interface_drawer_can_read',
			'args'                => array(
				'id'   => array(
					'required'          => true,
					'validate_callback' => 'tainacan_interface_drawer_validate_id',
					'sanitize_callback' => 'absint',
				),
				'meta' => array(
					'required'          => false,
					'default'           => '',
					'validate_callback' => 'tainacan_interface_drawer_validate_meta',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'tainacan_interface_drawer_register_route' );

/**
 * The id is a positive integer.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function tainacan_interface_drawer_validate_id( $value ) {
	return is_numeric( $value ) && (int) $value > 0;
}

/**
 * The metadata list is empty or up to 60 comma-separated ids.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function tainacan_interface_drawer_validate_meta( $value ) {
	return '' === (string) $value || 1 === preg_match( '/^\d+(,\d+){0,59}$/', (string) $value );
}

/**
 * Permission: the visitor may read the item. Unknown and unreadable items get
 * the same 404, so the route does not tell which ids exist.
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function tainacan_interface_drawer_can_read( $request ) {
	if ( ! tainacan_interface_drawer_enabled() || null === tainacan_interface_readable_item( (int) $request->get_param( 'id' ) ) ) {
		return new WP_Error( 'tainacan_interface_not_found', __( 'Item not found.', 'tainacan-interface' ), array( 'status' => 404 ) );
	}
	return true;
}

/**
 * Route callback.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function tainacan_interface_drawer_rest_item( $request ) {
	$limited = tainacan_interface_rate_limit( 'drawer', 120 );
	if ( $limited ) {
		return $limited;
	}

	$item = tainacan_interface_readable_item( (int) $request->get_param( 'id' ) );
	if ( null === $item ) {
		return new WP_Error( 'tainacan_interface_not_found', __( 'Item not found.', 'tainacan-interface' ), array( 'status' => 404 ) );
	}

	$response = rest_ensure_response( tainacan_interface_drawer_payload( $item, (string) $request->get_param( 'meta' ) ) );
	$response->header( 'Cache-Control', is_user_logged_in() ? 'private, no-store' : 'public, max-age=300' );
	return $response;
}

/**
 * Plain text from an entity-encoded string (titles come with &#8211;).
 *
 * @param string $text Text.
 * @return string
 */
function tainacan_interface_plain( $text ) {
	return trim( html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * The document of the item, for the media box of the drawer: an image, a
 * video or an audio played in the browser's own player, or a PDF shown by its
 * cover with a link. Embeds (YouTube, text documents) are left to the item
 * page. The file is described by the viewer's own function, so both always
 * classify a document the same way.
 *
 * @param int    $item_id Item post ID.
 * @param string $cover   Item thumbnail URL, used as poster and PDF cover.
 * @return array|null { kind, src }
 */
function tainacan_interface_drawer_media( $item_id, $cover ) {
	if ( ! function_exists( 'tainacan_get_the_document_type' ) || 'attachment' !== tainacan_get_the_document_type( $item_id ) ) {
		return null;
	}

	$file = tainacan_interface_viewer_describe( (int) tainacan_get_the_document_raw( $item_id ) );
	if ( ! $file || ! in_array( $file['kind'], array( 'image', 'video', 'audio', 'pdf' ), true ) || ( 'pdf' === $file['kind'] && '' === $cover ) ) {
		return null;
	}

	$src = $file['url'];
	if ( 'image' === $file['kind'] ) {
		$large = wp_get_attachment_image_src( $file['id'], 'large' );
		$src   = is_array( $large ) && ! empty( $large[0] ) ? $large[0] : $file['src'];
	}

	return array(
		'kind' => $file['kind'],
		'src'  => $src,
	);
}

/**
 * The line above the title: the collection, the kind of document and how many
 * files the item has.
 *
 * @param \Tainacan\Entities\Item $item Item.
 * @return string[]
 */
function tainacan_interface_drawer_meta_line( $item ) {
	$id         = (int) $item->get_id();
	$collection = $item->get_collection();
	$line       = $collection ? array( tainacan_interface_plain( $collection->get_name() ) ) : array();
	$type       = function_exists( 'tainacan_get_the_document_type' ) ? (string) tainacan_get_the_document_type( $id ) : '';
	$kinds      = array(
		'text'  => __( 'Text', 'tainacan-interface' ),
		'url'   => __( 'Link', 'tainacan-interface' ),
		'image' => __( 'Image', 'tainacan-interface' ),
		'video' => __( 'Video', 'tainacan-interface' ),
		'audio' => __( 'Audio', 'tainacan-interface' ),
		'pdf'   => __( 'PDF', 'tainacan-interface' ),
	);

	if ( 'attachment' === $type ) {
		$file = tainacan_interface_viewer_describe( (int) tainacan_get_the_document_raw( $id ) );
		$type = $file ? (string) $file['kind'] : '';
	}
	if ( isset( $kinds[ $type ] ) ) {
		$line[] = $kinds[ $type ];
	}

	$files = count( $item->get_attachments() );
	if ( $files > 0 ) {
		$line[] = sprintf(
			/* translators: %s: number of files attached to an item. */
			_n( '%s file', '%s files', $files, 'tainacan-interface' ),
			number_format_i18n( $files )
		);
	}

	return $line;
}

/**
 * What the drawer shows. Text goes as plain text (the browser puts it in with
 * textContent); metadata values go as HTML filtered by wp_kses_post().
 *
 * Which metadata: the ones marked "Show in the side drawer" on the metadatum
 * form. When the collection marked none, what the item page shows minus what
 * the list card already shows, so the drawer adds to the card instead of
 * repeating it.
 *
 * @param \Tainacan\Entities\Item $item Readable item.
 * @param string                  $meta Comma-separated metadata ids the list shows, or ''.
 * @return array
 */
function tainacan_interface_drawer_payload( $item, $meta ) {
	$id         = (int) $item->get_id();
	$collection = $item->get_collection();
	$cover      = get_the_post_thumbnail_url( $id, 'medium_large' );
	$cover      = $cover ? $cover : '';

	$data = array(
		'id'          => $id,
		'title'       => tainacan_interface_plain( get_the_title( $id ) ),
		'url'         => get_permalink( $id ),
		'collection'  => $collection ? tainacan_interface_plain( $collection->get_name() ) : '',
		'meta_line'   => tainacan_interface_drawer_meta_line( $item ),
		'cover'       => $cover,
		'media'       => tainacan_interface_drawer_media( $id, $cover ),
		'description' => '',
		'fields'      => array(),
	);

	if ( ! class_exists( '\Tainacan\Repositories\Item_Metadata' ) ) {
		return $data;
	}

	$listed      = '' !== $meta ? array_map( 'intval', explode( ',', $meta ) ) : array();
	$staff       = current_user_can( 'edit_post', $id );
	$marked      = array();
	$default     = array();
	$others      = array();
	$any_marked  = false;
	$description = array(
		'text'   => '',
		'marked' => false,
	);

	foreach ( (array) \Tainacan\Repositories\Item_Metadata::get_instance()->fetch( $item, 'OBJECT' ) as $item_metadatum ) {
		$metadatum = $item_metadatum instanceof \Tainacan\Entities\Item_Metadata_Entity ? $item_metadatum->get_metadatum() : null;

		if ( ! $metadatum instanceof \Tainacan\Entities\Metadatum || (int) $metadatum->get_parent() > 0 ) {
			continue;
		}

		$type      = (string) $metadatum->get_metadata_type();
		$display   = (string) $metadatum->get_display();
		$is_marked = tainacan_interface_drawer_shows( $metadatum );

		if ( 'never' === $display || 'Tainacan\Metadata_Types\Core_Title' === $type ) {
			continue;
		}
		if ( ! $staff && 'publish' !== $metadatum->get_status() ) {
			continue;
		}

		// Only a marked metadatum this visitor may see switches to marked-only mode;
		// an empty value still counts, because the choice is the collection's.
		$any_marked = $any_marked || $is_marked;

		$html = trim( wp_kses_post( (string) $item_metadatum->get_value_as_html() ) );
		$text = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) ) );
		if ( '' === $text ) {
			continue;
		}

		if ( 'Tainacan\Metadata_Types\Core_Description' === $type ) {
			$description = array(
				'text'   => wp_html_excerpt( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), 480, '…' ),
				'marked' => $is_marked,
				'listed' => in_array( (int) $metadatum->get_id(), $listed, true ),
			);
			continue;
		}

		if ( mb_strlen( $text ) > 600 ) {
			$html = esc_html( wp_html_excerpt( $text, 560, '…' ) );
		}

		$field = array(
			'label' => tainacan_interface_plain( $metadatum->get_name() ),
			'html'  => $html,
		);

		if ( $is_marked ) {
			$marked[] = $field;
		} elseif ( in_array( (int) $metadatum->get_id(), $listed, true ) ) {
			continue;
		} elseif ( 'yes' === $display ) {
			$default[] = $field;
		} else {
			$others[] = $field;
		}
	}

	if ( $any_marked ) {
		$data['fields']      = array_slice( $marked, 0, tainacan_interface_is_modern() ? tainacan_interface_opt( 'drawer_max_fields' ) : 16 );
		$data['description'] = $description['marked'] ? $description['text'] : '';
	} else {
		$data['fields']      = array_slice( array_merge( $default, $others ), 0, tainacan_interface_is_modern() ? tainacan_interface_opt( 'drawer_max_fields' ) : 12 );
		$data['description'] = empty( $description['listed'] ) ? $description['text'] : '';
	}

	return $data;
}

/**
 * Script and style, only on the list pages.
 */
function tainacan_interface_drawer_enqueue() {
	if ( ! tainacan_interface_drawer_enabled() || ! tainacan_interface_is_list_page() ) {
		return;
	}

	$modern = tainacan_interface_is_modern();
	$mode   = $modern ? tainacan_interface_opt( 'drawer_mode' ) : 'push';
	if ( 'off' === $mode ) {
		return;
	}

	$uri   = get_template_directory_uri();
	$label = $modern ? tainacan_interface_opt( 'drawer_open_label' ) : '';

	wp_enqueue_style( 'tt2-item-drawer', $uri . '/assets/css/item-drawer.css', array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script( 'tt2-item-drawer', $uri . '/assets/js/item-drawer.js', array(), TAINACAN_INTERFACE_VERSION, true );
	wp_localize_script(
		'tt2-item-drawer',
		'tt2Drawer',
		array(
			'endpoint' => esc_url_raw( rest_url( TAINACAN_INTERFACE_REST_NAMESPACE . '/item/' ) ),
			'nonce'    => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
			'mode'     => $mode,
			'side'     => $modern ? tainacan_interface_opt( 'drawer_side' ) : 'right',
			'backdrop' => $modern && tainacan_interface_opt( 'drawer_backdrop' ),
			'media'    => $modern ? tainacan_interface_opt( 'drawer_media' ) : 'contain',
			'metaLine' => $modern && tainacan_interface_opt( 'drawer_meta_line' ),
			'noDesc'   => $modern && ! tainacan_interface_opt( 'drawer_description' ),
			'openNew'  => $modern && tainacan_interface_opt( 'drawer_open_new' ),
			'modern'   => $modern,
			'i18n'     => array(
				'label'      => __( 'Item preview', 'tainacan-interface' ),
				'close'      => __( 'Close preview', 'tainacan-interface' ),
				'prev'       => __( 'Previous item', 'tainacan-interface' ),
				'next'       => __( 'Next item', 'tainacan-interface' ),
				/* translators: 1: position of the item in the list, 2: number of items in the list. */
				'pos'        => __( '%1$d of %2$d', 'tainacan-interface' ),
				'loading'    => __( 'Loading…', 'tainacan-interface' ),
				'unplayable' => __( 'This browser cannot play this file. You can download it.', 'tainacan-interface' ),
				'download'   => __( 'Download', 'tainacan-interface' ),
				'error'      => __( 'This item could not be loaded.', 'tainacan-interface' ),
				'open'       => '' !== $label ? $label : ( $modern ? __( 'Open item', 'tainacan-interface' ) : __( 'Open the full record', 'tainacan-interface' ) ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_drawer_enqueue' );
