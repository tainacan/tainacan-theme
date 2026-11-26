<?php
/**
 * Import from Blocksy: where the settings come from and how they are written.
 *
 * Two sources:
 * - this site: the theme mods Blocksy (or a child theme of it) left in the
 *   database, its Additional CSS, the menus it placed and the widgets it had;
 * - a file: the export of the Blocksy Companion (Customize > Manage Options >
 *   Export Customizations, a .dat file), from this or from another site, or a
 *   JSON of the theme mods (wp option get theme_mods_blocksy --format=json).
 *
 * The plan (Tainacan_Interface_Blocksy_Map) says what each setting becomes;
 * the import writes the groups the administrator kept checked, brings the
 * images the settings point to (from the media library of this site or, for
 * another site, by downloading them), and records what was there before, so
 * one click undoes it. Blocksy's own settings are never changed.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-tainacan-interface-serialized-reader.php';
require_once __DIR__ . '/class-tainacan-interface-blocksy-map.php';

/**
 * Largest file accepted, in bytes.
 */
const TAINACAN_INTERFACE_BLOCKSY_MAX_FILE = 8388608;

/**
 * Meta that marks a file this import downloaded, with the address it came from.
 */
const TAINACAN_INTERFACE_BLOCKSY_SOURCE_META = '_tainacan_interface_imported_from';

/**
 * Groups of the plan in the order they are shown.
 *
 * @return string[]
 */
function tainacan_interface_blocksy_group_order() {
	return array( 'colors', 'typography', 'layout', 'buttons', 'header', 'footer', 'social', 'navigation', 'blog', 'tainacan', 'collections', 'images', 'menus', 'widgets', 'css', 'content' );
}

/**
 * Whether a set of theme mods looks like Blocksy's.
 *
 * @param mixed $mods Theme mods.
 * @return bool
 */
function tainacan_interface_blocksy_looks_like( $mods ) {
	if ( ! is_array( $mods ) ) {
		return false;
	}
	foreach ( array( 'colorPalette', 'header_placements', 'footer_placements', 'rootTypography', 'maxSiteWidth', 'blog_archive_order' ) as $key ) {
		if ( array_key_exists( $key, $mods ) ) {
			return true;
		}
	}
	return (bool) preg_grep( '/^(tnc_col_\d+_item_|tainacan-repository-items_)/', array_map( 'strval', array_keys( $mods ) ) );
}

/**
 * Blocksy settings kept in this site: Blocksy itself and its child themes,
 * installed or not (the settings stay after a theme is deleted).
 *
 * @return array<string, array{label: string, count: int, css: int, active: bool}> Stylesheet => summary.
 */
function tainacan_interface_blocksy_site_sources() {
	$slugs = array( 'blocksy' => true );
	foreach ( wp_get_themes() as $slug => $theme ) {
		if ( 'blocksy' === $theme->get_template() ) {
			$slugs[ $slug ] = true;
		}
	}
	$found = array();
	foreach ( array_keys( $slugs ) as $slug ) {
		$mods = get_option( 'theme_mods_' . $slug );
		if ( ! tainacan_interface_blocksy_looks_like( $mods ) ) {
			continue;
		}
		$theme          = wp_get_theme( $slug );
		$found[ $slug ] = array(
			'label'  => $theme->exists() ? $theme->get( 'Name' ) : $slug,
			'count'  => count( $mods ),
			'css'    => strlen( wp_get_custom_css( $slug ) ),
			'active' => get_stylesheet() === $slug,
		);
	}
	return $found;
}

/**
 * The source "this site" in the shape of a file.
 *
 * @param string $slug Stylesheet of Blocksy or of a child theme.
 * @return array|WP_Error
 */
function tainacan_interface_blocksy_from_site( $slug ) {
	$sources = tainacan_interface_blocksy_site_sources();
	if ( ! isset( $sources[ $slug ] ) ) {
		return new WP_Error( 'tainacan_interface_blocksy_none', __( 'There are no Blocksy settings with this name on this site.', 'tainacan-interface' ) );
	}
	return array(
		'kind'      => 'site',
		'slug'      => $slug,
		'label'     => $sources[ $slug ]['label'],
		'site_url'  => home_url(),
		'same_site' => true,
		'mods'      => get_option( 'theme_mods_' . $slug ),
		'css'       => wp_get_custom_css( $slug ),
		'widgets'   => null,
	);
}

/**
 * Reads an uploaded export.
 *
 * @param string $text     Contents.
 * @param string $filename Name of the file, for the label.
 * @return array|WP_Error
 */
function tainacan_interface_blocksy_from_text( $text, $filename ) {
	$text = (string) $text;
	if ( '' === trim( $text ) ) {
		return new WP_Error( 'tainacan_interface_blocksy_empty', __( 'The file is empty.', 'tainacan-interface' ) );
	}
	if ( strlen( $text ) > TAINACAN_INTERFACE_BLOCKSY_MAX_FILE ) {
		return new WP_Error( 'tainacan_interface_blocksy_big', __( 'The file is larger than 8 MB; an export of Blocksy is much smaller.', 'tainacan-interface' ) );
	}
	$text     = preg_replace( '/^\xEF\xBB\xBF/', '', $text ); // A byte order mark, from an editor.
	$repaired = false;
	$trimmed  = ltrim( $text );
	if ( '' !== $trimmed && ( '{' === $trimmed[0] || '[' === $trimmed[0] ) ) {
		$raw = json_decode( $trimmed, true );
		if ( ! is_array( $raw ) ) {
			return new WP_Error( 'tainacan_interface_blocksy_json', __( 'The file looks like JSON but could not be read.', 'tainacan-interface' ) );
		}
	} else {
		$read = Tainacan_Interface_Serialized_Reader::read( $text );
		if ( ! $read['ok'] || ! is_array( $read['value'] ) ) {
			return new WP_Error( 'tainacan_interface_blocksy_format', __( 'The file could not be read. Use the file Blocksy exports (Customize > Manage Options > Export Customizations), without opening it in an editor.', 'tainacan-interface' ) );
		}
		$raw      = $read['value'];
		$repaired = $read['repaired'];
	}

	// A bare JSON of the theme mods, or the export with mods inside.
	$mods = isset( $raw['mods'] ) && is_array( $raw['mods'] ) ? $raw['mods'] : $raw;
	if ( ! tainacan_interface_blocksy_looks_like( $mods ) ) {
		return new WP_Error( 'tainacan_interface_blocksy_not', __( 'This file does not hold settings of Blocksy.', 'tainacan-interface' ) );
	}
	$site_url  = isset( $raw['site_url'] ) && is_string( $raw['site_url'] ) ? esc_url_raw( $raw['site_url'], array( 'http', 'https' ) ) : '';
	$same_site = '' !== $site_url && untrailingslashit( set_url_scheme( $site_url, 'https' ) ) === untrailingslashit( set_url_scheme( home_url(), 'https' ) );
	$widgets   = isset( $raw['blocksy_widgets'] ) && is_array( $raw['blocksy_widgets'] ) ? $raw['blocksy_widgets'] : null;

	return array(
		'kind'      => 'file',
		'slug'      => isset( $raw['template'] ) && is_string( $raw['template'] ) ? sanitize_key( $raw['template'] ) : 'blocksy',
		'label'     => sanitize_file_name( (string) $filename ),
		'site_url'  => $site_url,
		'same_site' => $same_site,
		'repaired'  => $repaired,
		'mods'      => $mods,
		'css'       => isset( $raw['wp_css'] ) && is_string( $raw['wp_css'] ) ? $raw['wp_css'] : '',
		'widgets'   => $widgets,
	);
}

/**
 * Key of the uploaded export of the current administrator, kept between the
 * preview and the import.
 *
 * @return string
 */
function tainacan_interface_blocksy_upload_key() {
	return 'tainacan_interface_blocksy_file_' . get_current_user_id();
}

/**
 * The source chosen in the request: "site:<slug>" or "file".
 *
 * @param string $source Source.
 * @return array|WP_Error
 */
function tainacan_interface_blocksy_source( $source ) {
	if ( 'file' === $source ) {
		$data = get_transient( tainacan_interface_blocksy_upload_key() );
		return is_array( $data ) ? $data : new WP_Error( 'tainacan_interface_blocksy_expired', __( 'The file sent is no longer kept (after two hours it is forgotten). Send it again.', 'tainacan-interface' ) );
	}
	if ( 0 === strpos( $source, 'site:' ) ) {
		return tainacan_interface_blocksy_from_site( sanitize_key( substr( $source, 5 ) ) );
	}
	return new WP_Error( 'tainacan_interface_blocksy_source', __( 'Choose where the settings come from.', 'tainacan-interface' ) );
}

/**
 * The plan of a source, with the group of images filled in.
 *
 * @param array $data Source.
 * @return Tainacan_Interface_Blocksy_Map
 */
function tainacan_interface_blocksy_plan( $data ) {
	$plan = new Tainacan_Interface_Blocksy_Map( $data );

	// The images get a group of their own, after the plan knows them all.
	$rows = array();
	foreach ( $plan->images as $ref => $image ) {
		$local = tainacan_interface_blocksy_local_image( $image, ! empty( $data['same_site'] ) );
		if ( $local ) {
			$rows[] = array(
				'label' => $image['use'],
				'from'  => '#' . $image['id'],
				'to'    => __( 'Already in the media library', 'tainacan-interface' ),
				'level' => 'exact',
				'note'  => '',
				'image' => (string) wp_get_attachment_image_url( $local, 'medium' ),
			);
		} else {
			$rows[] = array(
				'label' => $image['use'],
				'from'  => '' !== $image['url'] ? $image['url'] : '#' . $image['id'],
				'to'    => __( 'Downloaded to the media library', 'tainacan-interface' ),
				'level' => 'exact',
				'note'  => '' !== $image['url'] ? '' : __( 'Its address is asked to the other site (public media API).', 'tainacan-interface' ),
				'image' => '' !== $image['url'] ? $image['url'] : '',
				'ref'   => $ref,
			);
		}
	}
	if ( ! $rows ) {
		$rows[] = array(
			'label' => __( 'Images', 'tainacan-interface' ),
			'from'  => __( 'None in the settings', 'tainacan-interface' ),
			'to'    => '-',
			'level' => 'exact',
			'note'  => '',
		);
	}
	$groups = array();
	foreach ( tainacan_interface_blocksy_group_order() as $id ) {
		if ( 'images' === $id ) {
			$groups['images'] = array(
				'title' => __( 'Images', 'tainacan-interface' ),
				'text'  => __( 'Logo, banner and images of the Additional CSS. From this site they are used as they are; from another site they are downloaded to the media library once (a second import reuses them).', 'tainacan-interface' ),
				'rows'  => $rows,
			);
		} elseif ( isset( $plan->groups[ $id ] ) ) {
			$groups[ $id ] = $plan->groups[ $id ];
		}
	}
	$plan->groups = $groups;
	return $plan;
}

/**
 * The attachment of this site for an image of the settings, if there is one:
 * the same id when the source is this site, or a file an earlier import
 * downloaded from the same address.
 *
 * @param array $image     Image of the plan.
 * @param bool  $same_site Whether the source is this site.
 * @return int Attachment id, 0 for none.
 */
function tainacan_interface_blocksy_local_image( $image, $same_site ) {
	if ( $same_site && $image['id'] > 0 && wp_attachment_is_image( $image['id'] ) ) {
		return (int) $image['id'];
	}
	$urls = array_values( array_unique( array_filter( array( $image['url'], isset( $image['resolved'] ) ? $image['resolved'] : '' ) ) ) );
	if ( ! $urls ) {
		return 0;
	}
	// The address an earlier import downloaded it from; there is no other index of where a file came from.
	$found = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_query -- the origin of a download is kept only in this meta; one query per image of an import (logo, banner, a few CSS images), run by an administrator.
				array(
					'key'     => TAINACAN_INTERFACE_BLOCKSY_SOURCE_META,
					'value'   => $urls,
					'compare' => 'IN',
				),
			),
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Address of an image of another site that the settings only name by id,
 * through the public media endpoint of that site.
 *
 * @param string $site_url Site.
 * @param int    $id       Attachment id there.
 * @return string Address, '' when the site does not answer.
 */
function tainacan_interface_blocksy_remote_image_url( $site_url, $id ) {
	if ( '' === $site_url || $id <= 0 ) {
		return '';
	}
	$response = wp_safe_remote_get(
		trailingslashit( $site_url ) . 'wp-json/wp/v2/media/' . (int) $id . '?_fields=source_url,mime_type',
		array(
			'timeout'     => 10,
			'redirection' => 2,
		)
	);
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return '';
	}
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || empty( $body['source_url'] ) || ! is_string( $body['source_url'] ) || empty( $body['mime_type'] ) || 0 !== strpos( (string) $body['mime_type'], 'image/' ) ) {
		return '';
	}
	return esc_url_raw( $body['source_url'], array( 'http', 'https' ) );
}

/**
 * Downloads an image to the media library, once per address.
 *
 * @param string $url Address.
 * @return int|WP_Error Attachment id.
 */
function tainacan_interface_blocksy_download_image( $url ) {
	if ( ! current_user_can( 'upload_files' ) ) {
		return new WP_Error( 'tainacan_interface_blocksy_upload', __( 'Your account cannot add files to the media library.', 'tainacan-interface' ) );
	}
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( ! preg_match( '/\.(jpe?g|png|gif|webp|avif)$/i', $path ) ) {
		// SVG is not accepted by the media library of WordPress without a plugin.
		return new WP_Error( 'tainacan_interface_blocksy_type', __( 'Not an image the media library accepts (JPEG, PNG, GIF, WebP or AVIF). An SVG logo, for one, needs a plugin that allows SVG; send it in Customize after that.', 'tainacan-interface' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url, 30 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}
	if ( filesize( $tmp ) > 20 * MB_IN_BYTES ) {
		wp_delete_file( $tmp );
		return new WP_Error( 'tainacan_interface_blocksy_size', __( 'The image is larger than 20 MB.', 'tainacan-interface' ) );
	}
	$id = media_handle_sideload(
		array(
			'name'     => wp_basename( $path ),
			'tmp_name' => $tmp,
		),
		0
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return $id;
	}
	update_post_meta( $id, TAINACAN_INTERFACE_BLOCKSY_SOURCE_META, $url );
	return (int) $id;
}

/**
 * Brings every image of the plan: an attachment id for each reference.
 *
 * @param Tainacan_Interface_Blocksy_Map $plan Plan.
 * @param array                          $data Source.
 * @return array{ids: array<string, int>, failed: array<string, string>, created: int[]}
 */
function tainacan_interface_blocksy_bring_images( $plan, $data ) {
	$ids     = array();
	$failed  = array();
	$created = array();
	foreach ( $plan->images as $ref => $image ) {
		$local = tainacan_interface_blocksy_local_image( $image, ! empty( $data['same_site'] ) );
		if ( ! $local ) {
			$url = '' !== $image['url'] ? $image['url'] : tainacan_interface_blocksy_remote_image_url( isset( $data['site_url'] ) ? (string) $data['site_url'] : '', $image['id'] );
			if ( '' === $url ) {
				$failed[ $ref ] = __( 'The other site did not give the address of the image.', 'tainacan-interface' );
				continue;
			}
			$image['resolved'] = $url;
			$local             = tainacan_interface_blocksy_local_image( $image, false );
			if ( ! $local ) {
				$local = tainacan_interface_blocksy_download_image( $url );
				if ( is_wp_error( $local ) ) {
					$failed[ $ref ] = $local->get_error_message();
					continue;
				}
				$created[] = $local;
			}
		}
		$ids[ $ref ] = (int) $local;
	}
	return compact( 'ids', 'failed', 'created' );
}

/**
 * The values of the theme mods of the groups chosen, with the images in place.
 *
 * @param Tainacan_Interface_Blocksy_Map $plan   Plan.
 * @param string[]                       $groups Groups.
 * @param array<string, int>             $images Attachment of each image reference.
 * @return array Theme mod => value.
 */
function tainacan_interface_blocksy_mods( $plan, $groups, $images ) {
	$mods = array();
	foreach ( $plan->set as $group => $values ) {
		if ( ! in_array( $group, $groups, true ) ) {
			continue;
		}
		foreach ( $values as $mod => $value ) {
			if ( is_array( $value ) && isset( $value['__image'] ) ) {
				$id = isset( $images[ $value['__image'] ] ) ? (int) $images[ $value['__image'] ] : 0;
				if ( ! $id ) {
					continue;
				}
				if ( 'header_image' === $mod ) {
					$meta                      = wp_get_attachment_metadata( $id );
					$mods['header_image']      = (string) wp_get_attachment_url( $id );
					$mods['header_image_data'] = (object) array(
						'attachment_id' => $id,
						'url'           => (string) wp_get_attachment_url( $id ),
						'thumbnail_url' => (string) wp_get_attachment_image_url( $id, 'medium' ),
						'width'         => is_array( $meta ) && isset( $meta['width'] ) ? (int) $meta['width'] : 0,
						'height'        => is_array( $meta ) && isset( $meta['height'] ) ? (int) $meta['height'] : 0,
					);
					continue;
				}
				$value = $id;
			}
			$mods[ $mod ] = $value;
		}
	}
	return $mods;
}

/**
 * Additional CSS of the import: this theme's, then Blocksy's, with the images
 * of the other site pointing to the copies.
 *
 * @param Tainacan_Interface_Blocksy_Map $plan   Plan.
 * @param array<string, int>             $images Attachment of each image reference.
 * @param string[]                       $groups Groups chosen.
 * @return string
 */
function tainacan_interface_blocksy_css( $plan, $images, $groups ) {
	// Additional CSS is written only by who may edit it (on a network, not every site administrator).
	if ( ! current_user_can( 'edit_css' ) ) {
		return '';
	}
	$css  = in_array( 'css', $groups, true ) ? (string) $plan->css : '';
	$fine = array();
	foreach ( $plan->fine as $group => $tokens ) {
		if ( in_array( $group, $groups, true ) ) {
			$fine = array_merge( $fine, $tokens );
		}
	}
	if ( $fine ) {
		// Same selector as the tokens of the design, later in the page: these win.
		$css = 'body.tt2-modern{' . implode( ';', $fine ) . "}\n" . $css;
	}
	if ( '' === trim( $css ) ) {
		return '';
	}
	foreach ( $plan->images as $ref => $image ) {
		if ( 0 === strpos( $ref, 'css-' ) && isset( $images[ $ref ] ) ) {
			$css = str_replace( $image['url'], (string) wp_get_attachment_url( $images[ $ref ] ), $css );
		}
	}
	$own    = (string) wp_get_custom_css( get_stylesheet() );
	$marker = '/* ' . sprintf( 'Blocksy: %s', gmdate( 'Y-m-d' ) ) . ' */';
	// A second import replaces the part of the first one.
	$own = trim( (string) preg_replace( '#/\* Blocksy: \d{4}-\d{2}-\d{2} \*/.*\z#s', '', $own ) );
	return trim( $own . "\n\n" . $marker . "\n" . trim( $css ) ) . "\n";
}

/**
 * Applies the plan: writes the chosen groups and keeps what there was, for undo.
 *
 * @param array    $data   Source.
 * @param string[] $groups Groups to bring.
 * @return array Report: groups, counts, images (ids, failed), left_out.
 */
function tainacan_interface_blocksy_apply( $data, $groups ) {
	$plan   = tainacan_interface_blocksy_plan( $data );
	$groups = array_values( array_intersect( $groups, array_keys( $plan->groups ) ) );

	// What undo puts back: the settings, the Additional CSS, the widgets and the meta of the collections touched.
	$own     = get_option( 'theme_mods_' . get_stylesheet() );
	$pending = array(
		'from'     => isset( $data['slug'] ) ? (string) $data['slug'] : 'blocksy',
		'label'    => isset( $data['label'] ) ? (string) $data['label'] : 'Blocksy',
		'mods'     => is_array( $own ) ? $own : array(),
		'css'      => null,
		'sidebars' => null,
		'meta'     => array(),
	);

	$images = array(
		'ids'     => array(),
		'failed'  => array(),
		'created' => array(),
	);
	if ( in_array( 'images', $groups, true ) ) {
		$images = tainacan_interface_blocksy_bring_images( $plan, $data );
		// Images go with the groups that use them (logo with the header, banner with posts).
		$groups[] = 'images';
	}

	$mods = tainacan_interface_blocksy_mods( $plan, $groups, $images['ids'] );
	if ( in_array( 'menus', $groups, true ) && $plan->locations ) {
		$locations                  = get_theme_mod( 'nav_menu_locations', array() );
		$mods['nav_menu_locations'] = array_merge( is_array( $locations ) ? $locations : array(), $plan->locations );
	}
	$theme_mods = is_array( $own ) ? $own : array();
	foreach ( $mods as $mod => $value ) {
		$theme_mods[ $mod ] = $value;
	}
	update_option( 'theme_mods_' . get_stylesheet(), $theme_mods );

	if ( in_array( 'collections', $groups, true ) && isset( $plan->meta['collections'] ) ) {
		foreach ( $plan->meta['collections'] as $collection_id => $values ) {
			if ( ! tainacan_interface_blocksy_can_edit_collection( (int) $collection_id ) ) {
				continue;
			}
			foreach ( $values as $key => $value ) {
				if ( '__sections' === $key ) {
					foreach ( array( TAINACAN_INTERFACE_SECTIONS_META, 'tainacan_interface_section_layout', 'tainacan_interface_opt_item_sections' ) as $meta ) {
						$pending['meta'][ $collection_id ][ $meta ] = metadata_exists( 'post', $collection_id, $meta ) ? get_post_meta( $collection_id, $meta, true ) : null;
					}
					tainacan_interface_sections_save( (int) $collection_id, $value );
					continue;
				}
				$pending['meta'][ $collection_id ][ $key ] = metadata_exists( 'post', $collection_id, $key ) ? get_post_meta( $collection_id, $key, true ) : null;
				update_post_meta( (int) $collection_id, $key, $value );
			}
		}
	}

	if ( in_array( 'widgets', $groups, true ) && $plan->widgets ) {
		$pending['sidebars'] = wp_get_sidebars_widgets();
		tainacan_interface_blocksy_place_widgets( $plan->widgets );
	}

	$css = tainacan_interface_blocksy_css( $plan, $images['ids'], $groups );
	if ( '' !== $css ) {
		$pending['css'] = (string) wp_get_custom_css( get_stylesheet() );
		$result         = wp_update_custom_css_post( $css, array( 'stylesheet' => get_stylesheet() ) );
		if ( ! is_wp_error( $result ) ) {
			// The CSS post id is a theme mod: the restored settings must keep the new id.
			$pending['mods']['custom_css_post_id'] = $result->ID;
		}
	}

	tainacan_interface_state_set( 'pending_import', $pending );
	tainacan_interface_state_set( 'import_blocksy', null );

	$report = array(
		'date'     => gmdate( 'c' ),
		'label'    => $pending['label'],
		'kind'     => isset( $data['kind'] ) ? $data['kind'] : 'site',
		'groups'   => $groups,
		'counts'   => $plan->counts( $groups ),
		'failed'   => array(),
		'created'  => $images['created'],
		'left_out' => $plan->left_out,
		'skipped'  => array(),
	);
	foreach ( array_diff( array_keys( $plan->groups ), $groups ) as $skipped ) {
		$report['skipped'][] = $plan->groups[ $skipped ]['title'];
	}
	foreach ( $images['failed'] as $ref => $why ) {
		$report['failed'][] = array(
			'what' => $plan->images[ $ref ]['use'],
			'why'  => $why,
		);
	}
	update_option( 'tainacan_interface_blocksy_report', $report, false );
	return $report;
}

/**
 * Whether the current user may change a collection, asked to Tainacan.
 *
 * @param int $collection_id Collection.
 * @return bool
 */
function tainacan_interface_blocksy_can_edit_collection( $collection_id ) {
	if ( ! class_exists( '\Tainacan\Repositories\Collections' ) ) {
		return false;
	}
	$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $collection_id );
	return $collection instanceof \Tainacan\Entities\Collection && $collection->can_edit();
}

/**
 * Puts the widgets of Blocksy in the areas of this theme, after the ones
 * they already have. From this site the widgets exist and only move; from a
 * file they are created.
 *
 * @param array $widgets Sidebar => widget id => settings (null when the widget is of this site).
 */
function tainacan_interface_blocksy_place_widgets( $widgets ) {
	$sidebars = wp_get_sidebars_widgets();
	foreach ( $widgets as $sidebar => $list ) {
		$placed = isset( $sidebars[ $sidebar ] ) && is_array( $sidebars[ $sidebar ] ) ? $sidebars[ $sidebar ] : array();
		foreach ( $list as $widget_id => $settings ) {
			$widget_id = (string) $widget_id;
			if ( ! preg_match( '/^(.+)-(\d+)$/', $widget_id, $m ) ) {
				continue;
			}
			if ( null !== $settings ) {
				// From the file: a new instance of the same kind, if this site has that kind of widget.
				$instances = get_option( 'widget_' . $m[1] );
				if ( ! is_array( $instances ) && ! tainacan_interface_blocksy_widget_type_exists( $m[1] ) ) {
					continue;
				}
				$instances          = is_array( $instances ) ? $instances : array( '_multiwidget' => 1 );
				$numbers            = array_filter( array_keys( $instances ), 'is_int' );
				$next               = $numbers ? max( $numbers ) + 1 : 1;
				$instances[ $next ] = map_deep( $settings, 'wp_kses_post' );
				update_option( 'widget_' . $m[1], $instances );
				$widget_id = $m[1] . '-' . $next;
			} else {
				// From this site: take it out of where it is now.
				foreach ( $sidebars as $other => $ids ) {
					if ( is_array( $ids ) ) {
						$sidebars[ $other ] = array_values( array_diff( $ids, array( $widget_id ) ) );
					}
				}
				$placed    = array_values( array_diff( $placed, array( $widget_id ) ) );
				$instances = get_option( 'widget_' . $m[1] );
				if ( ! is_array( $instances ) || ! isset( $instances[ (int) $m[2] ] ) ) {
					continue;
				}
			}
			$placed[] = $widget_id;
		}
		$sidebars[ $sidebar ] = $placed;
	}
	wp_set_sidebars_widgets( $sidebars );
}

/**
 * Whether this site has a kind of widget.
 *
 * @param string $id_base Kind.
 * @return bool
 */
function tainacan_interface_blocksy_widget_type_exists( $id_base ) {
	global $wp_widget_factory;
	if ( $wp_widget_factory instanceof WP_Widget_Factory ) {
		foreach ( $wp_widget_factory->widgets as $widget ) {
			if ( $widget->id_base === $id_base ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Undo of an import from Blocksy, on top of the one of upgrade.php: the meta
 * of the collections and the widgets. Runs before it (priority 5).
 */
function tainacan_interface_blocksy_undo_extra() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	check_admin_referer( 'tainacan_interface_undo_import' );
	tainacan_interface_blocksy_undo_parts( tainacan_interface_state( 'pending_import' ) );
}
add_action( 'admin_post_tainacan_interface_undo_import', 'tainacan_interface_blocksy_undo_extra', 5 );

/**
 * Puts back the meta and the widgets an import changed.
 *
 * @param mixed $pending Record of the import.
 */
function tainacan_interface_blocksy_undo_parts( $pending ) {
	if ( ! is_array( $pending ) ) {
		return;
	}
	if ( ! empty( $pending['meta'] ) && is_array( $pending['meta'] ) ) {
		foreach ( $pending['meta'] as $collection_id => $values ) {
			foreach ( (array) $values as $key => $value ) {
				if ( null === $value ) {
					delete_post_meta( (int) $collection_id, (string) $key );
				} else {
					update_post_meta( (int) $collection_id, (string) $key, $value );
				}
			}
		}
	}
	if ( isset( $pending['sidebars'] ) && is_array( $pending['sidebars'] ) ) {
		wp_set_sidebars_widgets( $pending['sidebars'] );
	}
	delete_option( 'tainacan_interface_blocksy_report' );
}

/**
 * CSS of the palette of Blocksy for the content: the variables and the
 * classes the blocks use, with the colors the import brought.
 *
 * @return string
 */
function tainacan_interface_blocksy_palette_css() {
	$palette = get_theme_mod( 'tainacan_interface_blocksy_palette', array() );
	if ( ! is_array( $palette ) || ! $palette ) {
		return '';
	}
	$vars    = array();
	$classes = array();
	foreach ( $palette as $n => $color ) {
		$color = sanitize_hex_color( (string) $color );
		$n     = (int) $n;
		if ( ! $color || $n < 1 ) {
			continue;
		}
		$vars[]    = sprintf( '--theme-palette-color-%1$d:%2$s;--wp--preset--color--palette-color-%1$d:%2$s', $n, $color );
		$classes[] = sprintf( '.has-palette-color-%1$d-color{color:var(--theme-palette-color-%1$d)!important}.has-palette-color-%1$d-background-color{background-color:var(--theme-palette-color-%1$d)!important}.has-palette-color-%1$d-border-color{border-color:var(--theme-palette-color-%1$d)!important}', $n );
	}
	if ( ! $vars ) {
		return '';
	}
	// Names Blocksy gives to the global colors, which Additional CSS written for it may use.
	$aliases = array(
		'--theme-text-color'                      => 3,
		'--theme-headings-color'                  => 4,
		'--theme-link-initial-color'              => 1,
		'--theme-link-hover-color'                => 2,
		'--theme-border-color'                    => 5,
		'--theme-button-background-initial-color' => 1,
		'--theme-button-background-hover-color'   => 2,
	);
	foreach ( $aliases as $name => $n ) {
		if ( isset( $palette[ $n ] ) && sanitize_hex_color( (string) $palette[ $n ] ) ) {
			$vars[] = $name . ':var(--theme-palette-color-' . $n . ')';
		}
	}
	return ':root{' . implode( ';', $vars ) . '}' . implode( '', $classes );
}

/**
 * Prints the palette on the public pages.
 */
function tainacan_interface_blocksy_palette_enqueue() {
	$css = tainacan_interface_blocksy_palette_css();
	if ( '' !== $css && wp_style_is( 'tainacan-interface-modern', 'enqueued' ) ) {
		wp_add_inline_style( 'tainacan-interface-modern', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_blocksy_palette_enqueue', 30 );

/**
 * And in the block editor, so the blocks look the same while edited.
 */
function tainacan_interface_blocksy_palette_editor() {
	$css = tainacan_interface_blocksy_palette_css();
	if ( '' === $css ) {
		return;
	}
	wp_register_style( 'tainacan-interface-blocksy-palette', false, array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_style( 'tainacan-interface-blocksy-palette' );
	wp_add_inline_style( 'tainacan-interface-blocksy-palette', $css );
}
add_action( 'enqueue_block_assets', 'tainacan_interface_blocksy_palette_editor' );

/**
 * The colors of the palette in the color picker of the blocks, with the slugs
 * Blocksy used (palette-color-N), so a block keeps showing which one it uses.
 *
 * @param WP_Theme_JSON_Data $theme_json Data of theme.json.
 * @return WP_Theme_JSON_Data
 */
function tainacan_interface_blocksy_palette_theme_json( $theme_json ) {
	$palette = get_theme_mod( 'tainacan_interface_blocksy_palette', array() );
	if ( ! is_array( $palette ) || ! $palette || ! method_exists( $theme_json, 'update_with' ) ) {
		return $theme_json;
	}
	// Starts from the colors of the theme: a palette in theme.json replaces them whole.
	$current = tainacan_interface_palette_base( $theme_json->get_data() );
	foreach ( $palette as $n => $color ) {
		$color = sanitize_hex_color( (string) $color );
		if ( $color ) {
			$current[] = array(
				/* translators: %d: number of a color of the palette of Blocksy. */
				'name'  => sprintf( __( 'Blocksy color %d', 'tainacan-interface' ), (int) $n ),
				'slug'  => 'palette-color-' . (int) $n,
				'color' => $color,
			);
		}
	}
	return $theme_json->update_with(
		array(
			'version'  => 2, // WordPress 6.5 (the minimum of the theme) does not know version 3.
			'settings' => array( 'color' => array( 'palette' => $current ) ),
		)
	);
}
add_filter( 'wp_theme_json_data_theme', 'tainacan_interface_blocksy_palette_theme_json' );

/**
 * Activation coming from Blocksy or a child of it: the administrator is
 * offered the importer (nothing is written without the button).
 *
 * @param string        $old_name  Name of the previous theme.
 * @param WP_Theme|bool $old_theme Previous theme.
 */
function tainacan_interface_blocksy_on_activation( $old_name = '', $old_theme = false ) {
	unset( $old_name );
	if ( $old_theme instanceof WP_Theme && 'blocksy' === $old_theme->get_template() && tainacan_interface_blocksy_looks_like( get_option( 'theme_mods_' . $old_theme->get_stylesheet() ) ) ) {
		tainacan_interface_state_set( 'import_blocksy', $old_theme->get_stylesheet() );
	}
}
add_action( 'after_switch_theme', 'tainacan_interface_blocksy_on_activation', 20, 2 );
