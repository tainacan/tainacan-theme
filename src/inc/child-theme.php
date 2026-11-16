<?php
/**
 * Child themes made for Tainacan Interface 2.x.
 *
 * Three things a 2.x child theme may rely on, and that 3.0 has to honour:
 * - the files that held the item record in 2.10.0 (single-items-*_new.php);
 * - its own style.css, which 2.10.0 stopped loading once style.min.css existed;
 * - copies of 2.10.0 templates with known security flaws, which an update of
 *   this theme cannot reach: the administrator is told which ones.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Template parts that moved ---------- */

/**
 * Loads a 2.10.0 template part from the child theme, when the child has it.
 *
 * In 2.10.0, single-items-metadata.php and single-items-attachments.php only
 * picked between a "_new" and an "_old" file, and the record was in the
 * "_new" one. A child theme that customized the record copied that file. Since
 * 3.0 the record is in the main file, so the child's copy would be skipped
 * without a word; this loads it instead. Only the child theme's directory is
 * looked at: the parent has no "_new" files any more. A "_new" file that calls
 * the main part again (a wrapper) gets the main part, not itself.
 *
 * @param string $slug Template part slug, e.g. 'template-parts/single-items-metadata_new'.
 * @return bool True when the child's file was loaded (the caller stops there).
 */
function tainacan_interface_child_legacy_part( $slug ) {
	static $loading = array();

	if ( isset( $loading[ $slug ] ) || ! is_child_theme() || ! is_readable( get_stylesheet_directory() . '/' . $slug . '.php' ) ) {
		return false;
	}
	$loading[ $slug ] = true;
	get_template_part( $slug );
	unset( $loading[ $slug ] );
	return true;
}

/* ---------- Reading the child theme's files ---------- */

/**
 * PHP or CSS source without its comments, so that a commented-out line is not
 * taken for code.
 *
 * @param string $code   Source.
 * @param string $kind   'php' or 'css'.
 * @return string
 */
function tainacan_interface_strip_comments( $code, $kind ) {
	if ( 'css' === $kind ) {
		return (string) preg_replace( '#/\*.*?\*/#s', '', $code );
	}
	$out = '';
	foreach ( token_get_all( $code ) as $token ) {
		if ( is_array( $token ) && in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$out .= is_array( $token ) ? $token[1] : $token;
	}
	return $out;
}

/**
 * A check on the child theme's files, cached in an option until one of the
 * files changes (the key is their modification times), so neither the front
 * end nor the admin read and parse them on every request.
 *
 * @param string   $name  Cache name.
 * @param string[] $files Paths relative to the child theme's directory.
 * @param callable $check Receives the absolute paths of the files that exist; returns the result.
 * @return mixed
 */
function tainacan_interface_child_cached( $name, $files, $check ) {
	static $memo = array();
	if ( array_key_exists( $name, $memo ) ) {
		return $memo[ $name ];
	}

	$dir      = get_stylesheet_directory();
	$existing = array();
	$stamp    = get_stylesheet();
	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;
		if ( is_readable( $path ) ) {
			$existing[ $file ] = $path;
			$stamp            .= '|' . $file . ':' . (int) filemtime( $path );
		}
	}

	$option = 'tainacan_interface_child_' . $name;
	$cached = get_option( $option );
	if ( is_array( $cached ) && isset( $cached['stamp'], $cached['value'] ) && $cached['stamp'] === $stamp ) {
		$memo[ $name ] = $cached['value'];
		return $memo[ $name ];
	}

	$memo[ $name ] = $existing ? call_user_func( $check, $existing ) : null;
	update_option(
		$option,
		array(
			'stamp' => $stamp,
			'value' => $memo[ $name ],
		),
		true
	);
	return $memo[ $name ];
}

/* ---------- The child theme's style.css ---------- */

/**
 * Whether the child theme replaced tainacan_enqueues() with its own copy
 * (a 2.x snippet). Then the child decides what loads, and nothing here
 * touches its stylesheets.
 *
 * @return bool
 */
function tainacan_interface_child_owns_enqueues() {
	static $owns = null;
	if ( null === $owns ) {
		$file = function_exists( 'tainacan_enqueues' ) ? ( new ReflectionFunction( 'tainacan_enqueues' ) )->getFileName() : '';
		$owns = is_string( $file ) && 0 === strpos( wp_normalize_path( $file ), trailingslashit( wp_normalize_path( get_stylesheet_directory() ) ) );
	}
	return $owns;
}

/**
 * Whether the child theme's style.css imports the parent's stylesheet, the
 * 2.x way of building on it (@import url("../tainacan-interface/style.css")).
 * Then the child's file already brings the parent's CSS.
 *
 * @return bool
 */
function tainacan_interface_child_imports_parent() {
	if ( ! is_child_theme() ) {
		return false;
	}
	return (bool) tainacan_interface_child_cached(
		'imports_parent',
		array( 'style.css' ),
		static function ( $paths ) {
			$css = tainacan_interface_strip_comments( (string) file_get_contents( $paths['style.css'], false, null, 0, 65536 ), 'css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local file of the active child theme, read-only and cached until it changes; WP_Filesystem would need credentials on some hosts for no gain.
			return 1 === preg_match( '/@import[^;]*' . preg_quote( get_template(), '/' ) . '\/style(\.min)?\.css/i', $css );
		}
	);
}

/**
 * Enqueues the child theme's style.css after the parent's.
 *
 * 2.10.0 registered get_stylesheet_uri() but swapped it for the parent's
 * style.min.css when that file existed, so a child theme's own CSS stopped
 * loading outside debug mode. Now the parent's stylesheet keeps its handle
 * (tainacan_tainacanStyle) and the child's comes after it. When the child
 * imports the parent's CSS itself, the parent's handle is left without a file,
 * so the parent's CSS is not loaded twice.
 */
function tainacan_interface_enqueue_child_style() {
	if ( ! is_child_theme() || tainacan_interface_child_owns_enqueues() || ! wp_style_is( 'tainacan_tainacanStyle', 'registered' ) ) {
		return;
	}
	if ( tainacan_interface_child_imports_parent() ) {
		wp_styles()->registered['tainacan_tainacanStyle']->src = false;
	}
	wp_enqueue_style( 'tainacan-interface-child', get_stylesheet_uri(), array( 'tainacan_tainacanStyle' ), (string) wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_enqueue_child_style', 20 );

/**
 * Path of a stylesheet URL, for comparing addresses written in different ways
 * (scheme, host, query string).
 *
 * @param string $url URL.
 * @return string
 */
function tainacan_interface_style_path( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );
	return is_string( $path ) ? untrailingslashit( $path ) : '';
}

/**
 * Puts the stylesheets in order once everything is enqueued.
 *
 * - A child that enqueues its own style.css (a common 2.x snippet) keeps its
 *   handle, which now depends on the parent's, so the child's CSS still comes
 *   after the parent's; ours is dropped.
 * - A child that enqueues the parent's style.css keeps that handle (whatever
 *   depends on it still loads), but the handle loses its file: the parent's
 *   minified CSS is already there.
 * - The Customizer's inline CSS (colors) moves from the parent's handle to the
 *   child's, so it still comes after the child's CSS, as it did in 2.x, when
 *   the parent's stylesheet was the child's style.css.
 */
function tainacan_interface_order_child_styles() {
	if ( ! is_child_theme() || tainacan_interface_child_owns_enqueues() ) {
		return;
	}
	$styles = wp_styles();
	if ( ! isset( $styles->registered['tainacan_tainacanStyle'] ) ) {
		return;
	}
	$child  = tainacan_interface_style_path( get_stylesheet_uri() );
	$parent = array(
		tainacan_interface_style_path( get_template_directory_uri() . '/style.css' ),
		tainacan_interface_style_path( get_template_directory_uri() . '/style.min.css' ),
	);
	$ours   = array( 'tainacan_tainacanStyle', 'tainacan-interface-child' );
	$target = in_array( 'tainacan-interface-child', $styles->queue, true ) ? 'tainacan-interface-child' : '';

	foreach ( $styles->registered as $handle => $style ) {
		if ( in_array( $handle, $ours, true ) || ! is_string( $style->src ) || '' === $style->src ) {
			continue;
		}
		$path = tainacan_interface_style_path( $style->src );
		if ( $path === $child && in_array( $handle, $styles->queue, true ) ) {
			wp_dequeue_style( 'tainacan-interface-child' );
			if ( ! in_array( 'tainacan_tainacanStyle', $style->deps, true ) ) {
				$style->deps[] = 'tainacan_tainacanStyle';
			}
			$target = $handle;
		} elseif ( in_array( $path, $parent, true ) && ! tainacan_interface_child_imports_parent() ) {
			$style->src = false;
			if ( ! in_array( 'tainacan_tainacanStyle', $style->deps, true ) ) {
				$style->deps[] = 'tainacan_tainacanStyle';
			}
		}
	}

	$inline = $styles->get_data( 'tainacan_tainacanStyle', 'after' );
	if ( '' !== $target && is_array( $inline ) && $inline ) {
		$existing = $styles->get_data( $target, 'after' );
		$styles->add_data( $target, 'after', array_merge( $inline, is_array( $existing ) ? $existing : array() ) );
		$styles->add_data( 'tainacan_tainacanStyle', 'after', array() );
	}
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_order_child_styles', PHP_INT_MAX );

/* ---------- Copies of 2.10.0 templates with known flaws ---------- */

/**
 * Templates of 2.10.0 with a security flaw, and how to recognise the old code
 * in a child theme's copy (comments removed first). A copy made from 3.0 does
 * not match. The flaw text is looked up by key at display time.
 *
 * @return array<string, array{0: string, 1: string}> File => { pattern, flaw key }.
 */
function tainacan_interface_child_flawed_templates() {
	return array(
		'archive-tainacan-collection.php'        => array( '/echo\s+get_query_var\(\s*[\'"]s[\'"]\s*\)/', 'collections-search' ),
		'tainacan/archive-terms.php'             => array( '/echo\s+get_query_var\(\s*[\'"]search[\'"]\s*\)/', 'terms-search' ),
		'template-parts/headertaxonomy.php'      => array( '/echo\s+\$current_taxonomy->labels->name\s*\.\s*[\'"]:[\'"]\s*\.\s*\$current_term->get_name\(\)/', 'term-share' ),
		'tainacan/view-mode-grid.php'            => array( '/\$_SERVER\[\s*[\'"]QUERY_STRING[\'"]\s*\]/', 'query-string' ),
		'template-parts/headercollection.php'    => array( '/esc_attr\(\s*get_post_meta\([^;]*tainacan_theme_collection_(background_)?color/', 'collection-colors' ),
		'template-parts/single-items-header.php' => array( '/esc_attr\(\s*get_post_meta\([^;]*tainacan_theme_collection_(background_)?color/', 'collection-colors' ),
	);
}

/**
 * What each flaw is, for the notice.
 *
 * @param string $key Flaw key.
 * @return string
 */
function tainacan_interface_child_flaw_text( $key ) {
	$texts = array(
		'collections-search' => __( 'the collections search prints the typed text without escaping (a link can run a script)', 'tainacan-interface' ),
		'terms-search'       => __( 'the terms search prints the typed text without escaping (a link can run a script)', 'tainacan-interface' ),
		'term-share'         => __( 'the share link prints the term name without escaping', 'tainacan-interface' ),
		'query-string'       => __( 'the item links carry the raw query string', 'tainacan-interface' ),
		'collection-colors'  => __( 'the collection colors go into the CSS without being checked', 'tainacan-interface' ),
	);
	return isset( $texts[ $key ] ) ? $texts[ $key ] : $key;
}

/**
 * The child theme's copies of flawed 2.10.0 templates, cached until one of
 * those files changes.
 *
 * @return array<string, string> File => flaw key.
 */
function tainacan_interface_child_flawed_copies() {
	if ( ! is_child_theme() ) {
		return array();
	}
	$templates = tainacan_interface_child_flawed_templates();
	$found     = tainacan_interface_child_cached(
		'flawed_copies',
		array_keys( $templates ),
		static function ( $paths ) use ( $templates ) {
			$found = array();
			foreach ( $paths as $file => $path ) {
				$code = tainacan_interface_strip_comments( (string) file_get_contents( $path ), 'php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local template of the active child theme, read-only and cached until it changes.
				if ( 1 === preg_match( $templates[ $file ][0], $code ) ) {
					$found[ $file ] = $templates[ $file ][1];
				}
			}
			return $found;
		}
	);
	return is_array( $found ) ? $found : array();
}

/**
 * Tells administrators which templates of their child theme still have the
 * 2.10.0 code with a security flaw. Nothing is changed: the copies are the
 * child theme's. Dismissed per set of files, so a new finding shows again.
 */
function tainacan_interface_child_flaws_notice() {
	if ( ! is_child_theme() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$found = tainacan_interface_child_flawed_copies();
	if ( empty( $found ) || get_option( 'tainacan_interface_child_flaws_dismissed' ) === md5( implode( ',', array_keys( $found ) ) ) ) {
		return;
	}
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_child_flaws' ), 'tainacan_interface_dismiss_child_flaws' );
	?>
	<div class="notice notice-warning">
		<p><strong>
			<?php
			/* translators: %s: name of the child theme. */
			echo esc_html( sprintf( __( 'The child theme "%s" has copies of Tainacan Interface 2 templates with security flaws.', 'tainacan-interface' ), wp_get_theme()->get( 'Name' ) ) );
			?>
		</strong></p>
		<p><?php esc_html_e( 'Updating Tainacan Interface does not change these files. Copy each one again from Tainacan Interface 3 and redo the customization, or delete it if the customization is no longer needed:', 'tainacan-interface' ); ?></p>
		<ul style="list-style: disc; margin-left: 1.5em;">
			<?php foreach ( $found as $file => $flaw ) : ?>
				<li><code><?php echo esc_html( $file ); ?></code>: <?php echo esc_html( tainacan_interface_child_flaw_text( $flaw ) ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p><a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'tainacan-interface' ); ?></a></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'tainacan_interface_child_flaws_notice' );

/**
 * Dismisses the notice for the current set of files.
 */
function tainacan_interface_dismiss_child_flaws() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_dismiss_child_flaws' );
	update_option( 'tainacan_interface_child_flaws_dismissed', md5( implode( ',', array_keys( tainacan_interface_child_flawed_copies() ) ) ), false );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_tainacan_interface_dismiss_child_flaws', 'tainacan_interface_dismiss_child_flaws' );
