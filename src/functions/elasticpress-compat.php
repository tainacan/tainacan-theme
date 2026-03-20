<?php
/**
 * Tainacan Interface - ElasticPress Compatibility
 *
 * Adds support for ElasticPress integration when Tainacan's
 * ElasticPress module is active.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if ElasticPress integration is available
 */
function tainacan_is_elasticpress_active() {
	if ( ! defined( 'TAINACAN_VERSION' ) ) {
		return false;
	}

	// Check if Tainacan's ElasticPress module is active
	if ( class_exists( '\Tainacan\Elastic_Press' ) ) {
		$elastic_press = \Tainacan\Elastic_Press::get_instance();
		if ( method_exists( $elastic_press, 'is_active' ) ) {
			return $elastic_press->is_active();
		}
	}

	// Fallback: check if ElasticPress plugin is active
	return class_exists( 'ElasticPress\Elasticsearch' ) || defined( 'EP_VERSION' );
}

/**
 * Enable ElasticPress on Tainacan search queries
 */
function tainacan_elasticpress_integrate_query( $query ) {
	if ( ! tainacan_is_elasticpress_active() ) {
		return;
	}

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Enable EP integration for Tainacan post types
	$post_type = $query->get( 'post_type' );

	if ( is_string( $post_type ) && strpos( $post_type, 'tnc_col_' ) === 0 ) {
		$query->set( 'ep_integrate', true );
	}

	if ( is_array( $post_type ) ) {
		foreach ( $post_type as $pt ) {
			if ( strpos( $pt, 'tnc_col_' ) === 0 ) {
				$query->set( 'ep_integrate', true );
				break;
			}
		}
	}

	// Also integrate for Tainacan search
	if ( $query->is_search() && isset( $_GET['s'] ) ) {
		$query->set( 'ep_integrate', true );
	}
}
add_action( 'pre_get_posts', 'tainacan_elasticpress_integrate_query', 5 );

/**
 * Add ElasticPress status indicator in admin
 */
function tainacan_elasticpress_admin_notice() {
	if ( ! is_admin() || ! tainacan_is_elasticpress_active() ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen && $screen->id === 'appearance_page_tainacan-interface-settings' ) {
		echo '<div class="notice notice-info"><p>';
		echo esc_html__( 'ElasticPress integration is active. Tainacan searches will use Elasticsearch for improved performance.', 'tainacan-interface' );
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'tainacan_elasticpress_admin_notice' );
