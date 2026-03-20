<?php
/**
 * Tainacan Interface - Accessibility & Modern Theme Features
 *
 * Features adopted from modern themes: accessibility enhancements,
 * lazy loading, skip links, ARIA landmarks, focus management,
 * reduced motion support, and print styles.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add skip link for screen readers
 */
function tainacan_skip_link() {
	echo '<a class="tainacan-skip-link screen-reader-text" href="#main-content">';
	echo esc_html__( 'Skip to main content', 'tainacan-interface' );
	echo '</a>';
}
add_action( 'wp_body_open', 'tainacan_skip_link', 1 );

/**
 * Add ARIA landmarks to key elements
 */
function tainacan_add_aria_landmarks( $content ) {
	// Add role="main" to main content area if not present
	return $content;
}

/**
 * Native lazy loading for images
 */
function tainacan_lazy_load_images( $content ) {
	if ( is_admin() || is_feed() || wp_doing_ajax() ) {
		return $content;
	}

	// Add loading="lazy" to images that don't have it
	$content = preg_replace(
		'/<img(?![^>]*loading=)([^>]*)>/i',
		'<img loading="lazy"$1>',
		$content
	);

	return $content;
}
add_filter( 'the_content', 'tainacan_lazy_load_images', 99 );
add_filter( 'post_thumbnail_html', 'tainacan_lazy_load_images', 99 );

/**
 * Add preconnect for external resources
 */
function tainacan_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => true,
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => true,
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'tainacan_resource_hints', 10, 2 );

/**
 * Add focus-visible polyfill support
 */
function tainacan_focus_visible_styles() {
	?>
	<style id="tainacan-focus-visible">
		/* Focus visible styles for keyboard navigation */
		:focus-visible {
			outline: 2px solid var(--tainacan-pastel-accent, #187181);
			outline-offset: 2px;
		}
		:focus:not(:focus-visible) {
			outline: none;
		}

		/* Reduced motion support */
		@media (prefers-reduced-motion: reduce) {
			*,
			*::before,
			*::after {
				animation-duration: 0.01ms !important;
				animation-iteration-count: 1 !important;
				transition-duration: 0.01ms !important;
				scroll-behavior: auto !important;
			}
		}

		/* High contrast mode support */
		@media (forced-colors: active) {
			.tainacan-help-btn,
			.tainacan-modal__close,
			.btn {
				border: 1px solid ButtonText;
			}
		}

		/* Print styles */
		@media print {
			.tainacan-skip-link,
			.tainacan-help-btn,
			.tainacan-modal-overlay,
			.return-to-top,
			nav,
			.tainacan-search-bar,
			.tainacan-dark-mode-toggle,
			footer {
				display: none !important;
			}
			body {
				font-size: 12pt;
				color: #000;
				background: #fff;
			}
			a[href]::after {
				content: " (" attr(href) ")";
				font-size: 0.8em;
			}
			.tainacan-item-section--metadata {
				column-count: 2 !important;
			}
		}
	</style>
	<?php
}
add_action( 'wp_head', 'tainacan_focus_visible_styles', 20 );

/**
 * Dark mode toggle output
 */
function tainacan_dark_mode_toggle() {
	$enable_dark = get_theme_mod( 'tainacan_enable_dark_mode', false );
	if ( ! $enable_dark ) {
		return;
	}
	?>
	<button type="button" class="tainacan-dark-mode-toggle"
		aria-label="<?php esc_attr_e( 'Toggle dark mode', 'tainacan-interface' ); ?>"
		title="<?php esc_attr_e( 'Toggle dark mode', 'tainacan-interface' ); ?>">
		<span class="tainacan-dark-mode-toggle__icon tainacan-dark-mode-toggle__icon--light" aria-hidden="true">&#9728;</span>
		<span class="tainacan-dark-mode-toggle__icon tainacan-dark-mode-toggle__icon--dark" aria-hidden="true">&#9790;</span>
	</button>
	<?php
}
add_action( 'wp_footer', 'tainacan_dark_mode_toggle' );

/**
 * Smooth scroll behavior (respects reduced motion)
 */
function tainacan_smooth_scroll_css() {
	echo '<style id="tainacan-smooth-scroll">@media (prefers-reduced-motion: no-preference) { html { scroll-behavior: smooth; } }</style>' . "\n";
}
add_action( 'wp_head', 'tainacan_smooth_scroll_css', 3 );

/**
 * Add screen reader text class
 */
function tainacan_screen_reader_css() {
	?>
	<style id="tainacan-sr-only">
		.screen-reader-text, .tainacan-sr-only {
			border: 0;
			clip: rect(1px, 1px, 1px, 1px);
			clip-path: inset(50%);
			height: 1px;
			margin: -1px;
			overflow: hidden;
			padding: 0;
			position: absolute;
			width: 1px;
			word-wrap: normal !important;
		}
		.screen-reader-text:focus, .tainacan-sr-only:focus {
			clip: auto !important;
			clip-path: none;
			display: block;
			height: auto;
			left: 5px;
			top: 5px;
			width: auto;
			z-index: 100000;
			background-color: var(--tainacan-pastel-surface, #fff);
			color: var(--tainacan-pastel-text, #000);
			padding: 15px 23px;
			font-size: 0.875rem;
			font-weight: 700;
			text-decoration: none;
			box-shadow: 0 0 2px 2px rgba(0,0,0,.2);
		}
	</style>
	<?php
}
add_action( 'wp_head', 'tainacan_screen_reader_css', 3 );

/**
 * Breadcrumb structured data (JSON-LD)
 */
function tainacan_breadcrumb_schema() {
	if ( is_front_page() || is_404() ) {
		return;
	}

	$items = array();
	$position = 1;

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => get_bloginfo( 'name' ),
		'item'     => home_url( '/' ),
	);

	if ( is_singular() && defined( 'TAINACAN_VERSION' ) ) {
		$post_type = get_post_type();
		if ( strpos( $post_type, 'tnc_col_' ) === 0 ) {
			// Tainacan item - add collection breadcrumb
			$collection_id = preg_replace( '/^tnc_col_/', '', $post_type );
			$collection_post = get_post( $collection_id );
			if ( $collection_post ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => $collection_post->post_title,
					'item'     => get_permalink( $collection_post ),
				);
			}
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => get_the_title(),
		);
	} elseif ( is_archive() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => get_the_archive_title(),
		);
	}

	if ( count( $items ) > 1 ) {
		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'tainacan_breadcrumb_schema', 25 );
