<?php
/**
 * Where we are and who may see what. Every enqueue and every route asks here,
 * so the page detection and the read permission live in one place.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current page shows a Tainacan item list: a collection, a
 * taxonomy term, the repository list, or a page with the Faceted Search block.
 *
 * @return bool
 */
function tainacan_interface_is_list_page() {
	if ( ! defined( 'TAINACAN_VERSION' ) || is_admin() ) {
		return false;
	}

	$is_list = false;

	if ( is_post_type_archive() ) {
		foreach ( (array) get_query_var( 'post_type' ) as $type ) {
			if ( 0 === strpos( (string) $type, 'tnc_col_' ) ) {
				$is_list = true;
			}
		}
	}

	if ( ! $is_list && is_tax() ) {
		$term    = get_queried_object();
		$is_list = $term instanceof WP_Term && 0 === strpos( $term->taxonomy, 'tnc_tax_' );
	}

	if ( ! $is_list && get_query_var( 'tainacan_repository_archive' ) ) {
		$is_list = true;
	}

	if ( ! $is_list && is_singular() ) {
		$post    = get_post();
		$is_list = $post && ( has_block( 'tainacan/faceted-search', $post ) || has_shortcode( (string) $post->post_content, 'tainacan_faceted_search' ) );
	}

	/**
	 * Whether the theme treats the current page as a Tainacan item list
	 * (loads the preview drawer and the list skin).
	 *
	 * @param bool $is_list Detected value.
	 */
	return (bool) apply_filters( 'tainacan_interface_is_list_page', $is_list );
}

/**
 * Whether the current page is a single Tainacan item.
 *
 * @return bool
 */
function tainacan_interface_is_item_page() {
	if ( ! defined( 'TAINACAN_VERSION' ) || ! is_singular() ) {
		return false;
	}
	$post = get_post();
	return $post && 1 === preg_match( '/^tnc_col_\d+_item$/', (string) $post->post_type );
}

/**
 * The item, if the current visitor may read it; null otherwise.
 *
 * The item post type is registered as public even for private collections,
 * so is_post_publicly_viewable() says yes to items nobody should see. The
 * question goes to the collection and to the item entity, and a password
 * protected item is never summarised. Memoized per request: the REST route
 * asks in its permission check and again in its callback.
 *
 * @param int $id Item post ID.
 * @return \Tainacan\Entities\Item|null
 */
function tainacan_interface_readable_item( $id ) {
	static $memo = array();

	$id = (int) $id;
	if ( ! array_key_exists( $id, $memo ) ) {
		$memo[ $id ] = tainacan_interface_find_readable_item( $id );
	}
	return $memo[ $id ];
}

/**
 * Uncached body of tainacan_interface_readable_item().
 *
 * @param int $id Item post ID.
 * @return \Tainacan\Entities\Item|null
 */
function tainacan_interface_find_readable_item( $id ) {
	$post = $id > 0 ? get_post( $id ) : null;

	if ( ! $post || 1 !== preg_match( '/^tnc_col_(\d+)_item$/', (string) $post->post_type, $match ) ) {
		return null;
	}

	if ( post_password_required( $post ) || ! class_exists( '\Tainacan\Repositories\Items' ) ) {
		return null;
	}

	$item = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $post->ID );
	if ( ! $item instanceof \Tainacan\Entities\Item || ! $item->can_read() ) {
		return null;
	}

	$collection = $item->get_collection();
	if ( ! $collection instanceof \Tainacan\Entities\Collection || ! $collection->can_read() ) {
		return null;
	}

	if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) {
		return null;
	}

	return $item;
}

/**
 * Per-IP request budget for the theme's public routes.
 *
 * Runs inside the route callback, not in permission_callback: WordPress may
 * call the permission check more than once per request and the counter would
 * double. Logged-in users who can edit posts are exempt.
 *
 * Counts only with a persistent object cache. Without one, a counter would be
 * two wp_options writes per request and a row per visitor per minute, which
 * costs more than the read it protects (the route is a single item, already
 * behind read permission and a public Cache-Control).
 *
 * @param string $bucket Counter name.
 * @param int    $limit  Requests allowed per minute.
 * @return WP_Error|null Error 429 when the budget is spent; null to go on.
 */
function tainacan_interface_rate_limit( $bucket, $limit ) {
	if ( current_user_can( 'edit_posts' ) || ! wp_using_ext_object_cache() ) {
		return null;
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
		// One budget per /64: an IPv6 visitor controls the whole block.
		$ip = bin2hex( substr( (string) inet_pton( $ip ), 0, 8 ) );
	}

	$key   = sanitize_key( $bucket ) . '_' . md5( $ip . '|' . (int) floor( time() / MINUTE_IN_SECONDS ) );
	$count = wp_cache_add( $key, 0, 'tainacan_interface_rl', MINUTE_IN_SECONDS ) ? 0 : (int) wp_cache_get( $key, 'tainacan_interface_rl' );

	if ( $count >= (int) $limit ) {
		return new WP_Error( 'tainacan_interface_rate_limited', __( 'Too many requests. Please wait a minute.', 'tainacan-interface' ), array( 'status' => 429 ) );
	}

	wp_cache_incr( $key, 1, 'tainacan_interface_rl' );
	return null;
}
