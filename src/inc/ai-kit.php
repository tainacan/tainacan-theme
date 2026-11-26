<?php
/**
 * Kit for AI: what an AI (Claude, ChatGPT, Gemini or another) needs to change
 * this theme, make child themes and build pages with blocks the right way.
 *
 * The guides, prompts, references and the starter child theme are files in
 * ai-kit/. Here: the context of this site (what an AI cannot guess), the ZIP
 * packages, the check of a child theme and of block markup, and WP-CLI.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAINACAN_INTERFACE_KIT_DIR', 'ai-kit' );

/**
 * Absolute path of the kit, or of a file in it.
 *
 * @param string $file Path inside the kit.
 * @return string
 */
function tainacan_interface_kit_path( $file = '' ) {
	return get_template_directory() . '/' . TAINACAN_INTERFACE_KIT_DIR . ( '' !== $file ? '/' . ltrim( $file, '/' ) : '' );
}

/* ---------- Context of the site ---------- */

/**
 * Short name of a Tainacan metadata type class (Tainacan\Metadata_Types\Text => text).
 *
 * @param string $class_name Class.
 * @return string
 */
function tainacan_interface_kit_type_name( $class_name ) {
	$parts = explode( '\\', (string) $class_name );
	return strtolower( (string) end( $parts ) );
}

/**
 * Public collections, with the metadata and the addresses an AI needs to build
 * pages and queries. Private collections, private metadata, items and people
 * are left out: the context may be handed to an outside service.
 *
 * @return array
 */
function tainacan_interface_kit_collections() {
	if ( ! class_exists( '\Tainacan\Repositories\Collections' ) ) {
		return array();
	}
	$collections = \Tainacan\Repositories\Collections::get_instance()->fetch(
		array(
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		),
		'OBJECT'
	);
	$metadata    = class_exists( '\Tainacan\Repositories\Metadata' ) ? \Tainacan\Repositories\Metadata::get_instance() : null;
	$out         = array();
	foreach ( (array) $collections as $collection ) {
		$post_type = $collection->get_db_identifier();
		$counts    = wp_count_posts( $post_type );
		$fields    = array();
		if ( $metadata ) {
			// tnc-check-ignore TNC-P02: at most 100 collections, only when an administrator downloads the kit or runs the command.
			foreach ( (array) $metadata->fetch_by_collection( $collection, array( 'post_status' => 'publish' ), 'OBJECT' ) as $metadatum ) {
				$fields[] = array(
					'id'   => (int) $metadatum->get_id(),
					'name' => $metadatum->get_name(),
					'type' => tainacan_interface_kit_type_name( $metadatum->get_metadata_type() ),
				);
				if ( count( $fields ) >= 60 ) {
					break;
				}
			}
		}
		$thumb = get_the_post_thumbnail_url( $collection->get_id(), 'large' );
		$out[] = array(
			'id'          => (int) $collection->get_id(),
			'name'        => $collection->get_name(),
			'slug'        => $collection->get_slug(),
			'post_type'   => $post_type,
			'url'         => (string) get_post_type_archive_link( $post_type ),
			'items'       => isset( $counts->publish ) ? (int) $counts->publish : 0,
			'description' => wp_trim_words( wp_strip_all_tags( (string) $collection->get_description() ), 40 ),
			'thumbnail'   => $thumb ? $thumb : '',
			'metadata'    => $fields,
		);
	}
	return $out;
}

/**
 * The collection the block patterns of a child theme show by default: the
 * first public collection (menu order, then title). One query, memoized.
 *
 * @return array{id: int, name: string, post_type: string, url: string, thumbnail: string}|array{}
 */
function tainacan_interface_kit_main_collection() {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$memo = array();
	$ids  = get_posts(
		array(
			'post_type'        => 'tainacan-collection',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		)
	);
	/**
	 * The collection the patterns of a child theme show by default.
	 *
	 * @param int $id Collection id; 0 for none.
	 */
	$id = (int) apply_filters( 'tainacan_interface_kit_main_collection', $ids ? (int) $ids[0] : 0 );
	if ( $id > 0 && post_type_exists( 'tnc_col_' . $id . '_item' ) ) {
		$thumb = get_the_post_thumbnail_url( $id, 'full' );
		$memo  = array(
			'id'        => $id,
			'name'      => get_the_title( $id ),
			'post_type' => 'tnc_col_' . $id . '_item',
			'url'       => (string) get_post_type_archive_link( 'tnc_col_' . $id . '_item' ),
			'thumbnail' => $thumb ? $thumb : '',
		);
	}
	return $memo;
}

/**
 * Public taxonomies of Tainacan, with how many terms each one has.
 *
 * @return array
 */
function tainacan_interface_kit_taxonomies() {
	if ( ! class_exists( '\Tainacan\Repositories\Taxonomies' ) ) {
		return array();
	}
	$out = array();
	foreach ( (array) \Tainacan\Repositories\Taxonomies::get_instance()->fetch(
		array(
			'post_status'    => 'publish',
			'posts_per_page' => 100,
		),
		'OBJECT'
	) as $taxonomy ) {
		$slug  = $taxonomy->get_db_identifier();
		$terms = taxonomy_exists( $slug ) ? wp_count_terms(
			array(
				'taxonomy'   => $slug,
				'hide_empty' => false,
			)
		) : 0;
		// The most used terms, for queries filtered by term (taxQuery) and for menus.
		// tnc-check-ignore TNC-P02: at most 100 taxonomies, 30 terms each, only when an administrator downloads the kit or runs the command.
		$top   = taxonomy_exists( $slug ) ? get_terms(
			array(
				'taxonomy'   => $slug,
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => 30,
			)
		) : array();
		$out[] = array(
			'id'        => (int) $taxonomy->get_id(),
			'name'      => $taxonomy->get_name(),
			'slug'      => $slug,
			'url'       => (string) get_permalink( $taxonomy->get_id() ),
			'terms'     => is_wp_error( $terms ) ? 0 : (int) $terms,
			'top_terms' => is_wp_error( $top ) ? array() : array_map(
				static function ( $term ) {
					$link = get_term_link( $term );
					return array(
						'id'    => (int) $term->term_id,
						'name'  => $term->name,
						'count' => (int) $term->count,
						'url'   => is_string( $link ) ? $link : '',
					);
				},
				$top
			),
		);
	}
	return $out;
}

/**
 * Options of the theme that differ from the default, for the AI to keep.
 *
 * @return array<string, mixed>
 */
function tainacan_interface_kit_changed_options() {
	$changed = array();
	if ( ! function_exists( 'tainacan_interface_settings_schema' ) ) {
		return $changed;
	}
	foreach ( tainacan_interface_settings_schema() as $key => $option ) {
		$value = get_theme_mod( 'tainacan_interface_' . $key, null );
		if ( null !== $value && tainacan_interface_opt_normalize( $option, $value ) !== $option['default'] ) {
			$changed[ $key ] = tainacan_interface_opt_normalize( $option, $value );
		}
	}
	return $changed;
}

/**
 * Everything about this site an AI needs and cannot guess. No people, e-mails,
 * items or private data.
 *
 * @return array
 */
function tainacan_interface_kit_context() {
	global $wp_version;

	$plugins = array();
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all = get_plugins();
	foreach ( (array) get_option( 'active_plugins', array() ) as $file ) {
		if ( isset( $all[ $file ] ) ) {
			$plugins[] = $all[ $file ]['Name'] . ' ' . $all[ $file ]['Version'];
		}
	}

	$pages = array();
	foreach ( get_pages(
		array(
			'number'      => 60,
			'post_status' => 'publish',
			'sort_column' => 'menu_order,post_title',
		)
	) as $page ) {
		$pages[] = array(
			'id'       => (int) $page->ID,
			'title'    => get_the_title( $page ),
			'url'      => (string) get_permalink( $page ),
			'template' => (string) get_page_template_slug( $page ),
			'blocks'   => has_blocks( $page->post_content ),
		);
	}

	$menus = array();
	foreach ( (array) get_nav_menu_locations() as $location => $menu_id ) {
		$menu = $menu_id ? wp_get_nav_menu_object( $menu_id ) : false;
		if ( $menu ) {
			$menus[ $location ] = $menu->name;
		}
	}

	$patterns = array();
	foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern ) {
		if ( 0 === strpos( $pattern['name'], 'tainacan-interface/' ) || 0 === strpos( $pattern['name'], get_stylesheet() . '/' ) ) {
			$patterns[] = $pattern['name'];
		}
	}

	$blocks = array();
	foreach ( array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() ) as $name ) {
		if ( 0 === strpos( $name, 'tainacan/' ) || 0 === strpos( $name, 'tainacan-interface/' ) ) {
			$blocks[] = $name;
		}
	}
	sort( $blocks );

	$theme  = wp_get_theme();
	$parent = wp_get_theme( get_template() );
	$front  = (int) get_option( 'page_on_front' );

	return array(
		'kit'      => array(
			'about'     => 'Context of a Tainacan site for the AI kit of Tainacan Interface. Read ai-kit/AGENTS.md first.',
			'version'   => TAINACAN_INTERFACE_VERSION,
			'generated' => gmdate( 'c' ),
		),
		'site'     => array(
			'name'           => get_bloginfo( 'name' ),
			'description'    => get_bloginfo( 'description' ),
			'url'            => home_url( '/' ),
			'language'       => get_bloginfo( 'language' ),
			'front_page'     => 'page' === get_option( 'show_on_front' ) && $front ? array(
				'id'    => $front,
				'title' => get_the_title( $front ),
			) : 'latest posts',
			'posts_page'     => (int) get_option( 'page_for_posts' ),
			'has_logo'       => has_custom_logo(),
			'permalinks'     => (string) get_option( 'permalink_structure' ),
			'block_patterns' => $patterns,
		),
		'versions' => array(
			'wordpress'    => $wp_version,
			'php'          => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'tainacan'     => defined( 'TAINACAN_VERSION' ) ? TAINACAN_VERSION : '',
			'parent_theme' => $parent->get( 'Name' ) . ' ' . $parent->get( 'Version' ) . ' (folder ' . get_template() . ')',
			'active_theme' => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) . ' (folder ' . get_stylesheet() . ')',
			'is_child'     => is_child_theme(),
		),
		'plugins'  => $plugins,
		'tainacan' => array(
			'items_list_url'       => class_exists( '\Tainacan\Theme_Helper' ) ? home_url( '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug() . '/' ) : '',
			'collections_list_url' => (string) get_post_type_archive_link( 'tainacan-collection' ),
			'collections'          => tainacan_interface_kit_collections(),
			'taxonomies'           => tainacan_interface_kit_taxonomies(),
			'blocks'               => $blocks,
		),
		'theme'    => array(
			'changed_options' => tainacan_interface_kit_changed_options(),
			'menus'           => $menus,
			'custom_css'      => '' !== trim( (string) wp_get_custom_css() ),
		),
		'pages'    => $pages,
	);
}

/* ---------- Packages ---------- */

/**
 * Files of a folder, recursively, relative to it.
 *
 * @param string $dir Folder.
 * @return string[]
 */
function tainacan_interface_kit_files( $dir ) {
	$files = array();
	if ( ! is_dir( $dir ) ) {
		return $files;
	}
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $file ) {
		if ( $file->isFile() ) {
			$files[] = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dir ) ) ), '/' );
		}
	}
	sort( $files );
	return $files;
}

/**
 * Placeholders of the starter child theme for a name and a folder.
 *
 * @param string $name Name of the child theme.
 * @param string $slug Folder and text domain.
 * @return array<string, string>
 */
function tainacan_interface_kit_child_tokens( $name, $slug ) {
	return array(
		'{{THEME_NAME}}'     => $name,
		'{{THEME_SLUG}}'     => $slug,
		'{{THEME_PREFIX}}'   => tainacan_interface_kit_child_prefix( $slug ),
		// The starter names its functions starter_child_*, which stays valid PHP in the theme package.
		'starter_child_'     => tainacan_interface_kit_child_prefix( $slug ) . '_',
		'{{PARENT_FOLDER}}'  => get_template(),
		'{{PARENT_VERSION}}' => TAINACAN_INTERFACE_VERSION,
		'{{SITE_NAME}}'      => get_bloginfo( 'name' ),
		'{{DATE}}'           => gmdate( 'Y-m-d' ),
	);
}

/**
 * Prefix of the functions of a child theme: its folder with underscores, and
 * a letter first when the folder starts with a digit (a PHP name cannot).
 *
 * @param string $slug Folder of the child theme.
 * @return string
 */
function tainacan_interface_kit_child_prefix( $slug ) {
	$prefix = str_replace( '-', '_', (string) $slug );
	return preg_match( '/^[a-z_]/', $prefix ) ? $prefix : 'theme_' . $prefix;
}

/**
 * Normalizes the name and folder asked for a child theme.
 *
 * @param string $name Name.
 * @param string $slug Folder; made from the name when empty.
 * @return array{0: string, 1: string}|WP_Error
 */
function tainacan_interface_kit_child_names( $name, $slug ) {
	$name = trim( wp_strip_all_tags( (string) $name ) );
	if ( '' === $name ) {
		/* translators: %s: name of the site. */
		$name = sprintf( __( '%s (child of Tainacan Interface)', 'tainacan-interface' ), get_bloginfo( 'name' ) );
	}
	$slug = sanitize_title( '' !== trim( (string) $slug ) ? $slug : $name );
	$slug = trim( preg_replace( '/[^a-z0-9-]+/', '-', $slug ), '-' );
	if ( '' === $slug || get_template() === $slug ) {
		return new WP_Error( 'tainacan_interface_kit_slug', __( 'Choose another folder name for the child theme.', 'tainacan-interface' ) );
	}
	return array( $name, rtrim( substr( $slug, 0, 60 ), '-' ) );
}

/**
 * Builds a ZIP file.
 *
 * @param string                $target Path of the ZIP.
 * @param array<string, string> $files  Path in the ZIP => absolute path of the source.
 * @param array<string, string> $texts  Path in the ZIP => contents.
 * @return true|WP_Error
 */
function tainacan_interface_kit_zip( $target, $files, $texts = array() ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'tainacan_interface_kit_zip', __( 'This server cannot make ZIP files (the PHP zip extension is missing). The kit is in the ai-kit folder of the theme.', 'tainacan-interface' ) );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $target, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		return new WP_Error( 'tainacan_interface_kit_zip', __( 'The ZIP file could not be created.', 'tainacan-interface' ) );
	}
	foreach ( $files as $inside => $source ) {
		$zip->addFile( $source, $inside );
	}
	foreach ( $texts as $inside => $contents ) {
		$zip->addFromString( $inside, $contents );
	}
	$zip->close();
	return true;
}

/**
 * The kit with the context of this site, as a ZIP: what is handed to an AI.
 *
 * @param string $target Path of the ZIP.
 * @return true|WP_Error
 */
function tainacan_interface_kit_package( $target ) {
	$dir   = tainacan_interface_kit_path();
	$files = array();
	foreach ( tainacan_interface_kit_files( $dir ) as $file ) {
		$files[ 'tainacan-interface-ai-kit/' . $file ] = $dir . '/' . $file;
	}
	return tainacan_interface_kit_zip(
		$target,
		$files,
		array( 'tainacan-interface-ai-kit/site-context.json' => (string) wp_json_encode( tainacan_interface_kit_context(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) )
	);
}

/**
 * The starter child theme with its name filled in, as a ZIP ready for
 * Appearance > Themes > Add New > Upload Theme.
 *
 * @param string $target Path of the ZIP.
 * @param string $name   Name.
 * @param string $slug   Folder.
 * @return true|WP_Error
 */
function tainacan_interface_kit_child_package( $target, $name, $slug ) {
	$names = tainacan_interface_kit_child_names( $name, $slug );
	if ( is_wp_error( $names ) ) {
		return $names;
	}
	list( $name, $slug ) = $names;
	$dir                 = tainacan_interface_kit_path( 'starter-child' );
	$tokens              = tainacan_interface_kit_child_tokens( $name, $slug );
	$texts               = array();
	foreach ( tainacan_interface_kit_files( $dir ) as $file ) {
		$texts[ $slug . '/' . $file ] = strtr( (string) file_get_contents( $dir . '/' . $file ), $tokens ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a file of this theme, read to be copied into the ZIP.
	}
	if ( ! $texts ) {
		// ZipArchive writes nothing for an empty archive: say why instead of sending no file.
		return new WP_Error( 'tainacan_interface_kit_starter', __( 'The starter child theme is missing from the ai-kit folder of the theme.', 'tainacan-interface' ) );
	}
	return tainacan_interface_kit_zip( $target, array(), $texts );
}

/**
 * Writes the starter child theme into wp-content/themes (WP-CLI).
 *
 * @param string $name Name.
 * @param string $slug Folder.
 * @return string|WP_Error The folder.
 */
function tainacan_interface_kit_child_write( $name, $slug ) {
	global $wp_filesystem;

	$names = tainacan_interface_kit_child_names( $name, $slug );
	if ( is_wp_error( $names ) ) {
		return $names;
	}
	list( $name, $slug ) = $names;
	$target              = trailingslashit( get_theme_root() ) . $slug;
	if ( file_exists( $target ) ) {
		/* translators: %s: folder of a theme. */
		return new WP_Error( 'tainacan_interface_kit_exists', sprintf( __( 'The folder %s already exists.', 'tainacan-interface' ), $target ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( ! WP_Filesystem() || ! $wp_filesystem ) {
		return new WP_Error( 'tainacan_interface_kit_fs', __( 'The theme folder cannot be written. Download the ZIP and send it in Appearance > Themes > Add New.', 'tainacan-interface' ) );
	}
	$dir    = tainacan_interface_kit_path( 'starter-child' );
	$tokens = tainacan_interface_kit_child_tokens( $name, $slug );
	foreach ( tainacan_interface_kit_files( $dir ) as $file ) {
		$path = $target . '/' . $file;
		wp_mkdir_p( dirname( $path ) );
		$wp_filesystem->put_contents( $path, strtr( (string) $wp_filesystem->get_contents( $dir . '/' . $file ), $tokens ), FS_CHMOD_FILE );
	}
	return $target;
}

/* ---------- Checks ---------- */

/**
 * Blocks of Tainacan whose saved HTML is made by the editor (it carries the
 * address of the site, or content built while editing). Written by hand they
 * show nothing; the theme rebuilds them when the page opens in the editor.
 *
 * @return string[]
 */
function tainacan_interface_kit_editor_made_blocks() {
	return array(
		'tainacan/carousel-collections-list',
		'tainacan/carousel-items-list',
		'tainacan/carousel-terms-list',
		'tainacan/collections-list',
		'tainacan/dynamic-items-list',
		'tainacan/faceted-search',
		'tainacan/facets-list',
		'tainacan/item-submission-form',
		'tainacan/search-bar',
		'tainacan/terms-list',
	);
}

/**
 * Checks block markup: unknown blocks, Tainacan blocks without the HTML the
 * editor makes, queries of collections that do not exist here.
 *
 * @param string $markup Block markup.
 * @param string $where  Label for the messages.
 * @param bool   $loose  True for a file read without running its PHP: values the PHP would print are missing, so queries are not checked.
 * @return array<int, array{0: string, 1: string}> Level (error, warning, ok) and message.
 */
function tainacan_interface_kit_check_markup( $markup, $where, $loose = false ) {
	$found    = array();
	$registry = WP_Block_Type_Registry::get_instance();
	$editor   = tainacan_interface_kit_editor_made_blocks();
	// Tainacan registers its blocks rendered in the browser only in the admin; outside it (WP-CLI, the site) they are known by its list.
	$tainacan = array();
	if ( class_exists( '\Tainacan\Gutenberg_Blocks' ) ) {
		foreach ( array_keys( (array) \Tainacan\Gutenberg_Blocks::get_instance()->get_blocks() ) as $slug ) {
			$tainacan[] = 'tainacan/' . $slug;
		}
	}
	$count = 0;
	$walk  = static function ( $blocks ) use ( &$walk, &$found, &$count, $registry, $editor, $tainacan, $where, $loose ) {
		foreach ( $blocks as $block ) {
			$name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
			if ( '' === $name ) {
				if ( '' !== trim( (string) $block['innerHTML'] ) ) {
					/* translators: %s: where the markup is (a file or a page). */
					$found[] = array( 'warning', sprintf( __( '%s: HTML outside a block (it becomes a Classic block in the editor).', 'tainacan-interface' ), $where ) );
				}
				continue;
			}
			++$count;
			if ( ! $registry->is_registered( $name ) && ! in_array( $name, $tainacan, true ) ) {
				/* translators: 1: where the markup is; 2: name of a block. */
				$found[] = array( 'error', sprintf( __( '%1$s: the block %2$s does not exist on this site.', 'tainacan-interface' ), $where, $name ) );
			} elseif ( in_array( $name, $editor, true ) && false === strpos( (string) $block['innerHTML'], 'data-module' ) && false === strpos( (string) $block['innerHTML'], 'tainacan-api-root' ) ) {
				/* translators: 1: where the markup is; 2: name of a block. */
				$found[] = array( 'warning', sprintf( __( '%1$s: %2$s has no HTML made by the editor. It shows nothing until the page is opened in the editor and saved (the theme rebuilds it there).', 'tainacan-interface' ), $where, $name ) );
			}
			if ( ! $loose && 'core/query' === $name && isset( $block['attrs']['query']['postType'] ) ) {
				$post_type = (string) $block['attrs']['query']['postType'];
				if ( ! post_type_exists( $post_type ) ) {
					/* translators: 1: where the markup is; 2: post type. */
					$found[] = array( 'error', sprintf( __( '%1$s: the query lists %2$s, which does not exist on this site (collection number?).', 'tainacan-interface' ), $where, $post_type ) );
				}
			}
			if ( preg_match( '/https?:\/\/(fonts\.googleapis|fonts\.gstatic|cdn\.jsdelivr|cdnjs\.cloudflare|unpkg)\b/i', (string) $block['innerHTML'] ) ) {
				/* translators: %s: where the markup is. */
				$found[] = array( 'error', sprintf( __( '%s: loads a file from an outside server (CDN). Keep everything in the theme.', 'tainacan-interface' ), $where ) );
			}
			$walk( isset( $block['innerBlocks'] ) ? $block['innerBlocks'] : array() );
		}
	};
	$walk( parse_blocks( $markup ) );
	if ( ! $found ) {
		/* translators: 1: where the markup is; 2: number of blocks. */
		$found[] = array( 'ok', sprintf( _n( '%1$s: %2$d block, all known.', '%1$s: %2$d blocks, all known.', $count, 'tainacan-interface' ), $where, $count ) );
	}
	return $found;
}

/**
 * Checks a child theme of this theme against the standard of the kit.
 *
 * @param string $stylesheet Folder of the child theme.
 * @return array<int, array{0: string, 1: string}> Level (error, warning, ok) and message.
 */
function tainacan_interface_kit_check_child( $stylesheet ) {
	$theme = wp_get_theme( $stylesheet );
	$found = array();
	if ( ! $theme->exists() ) {
		return array( array( 'error', __( 'This theme is not installed.', 'tainacan-interface' ) ) );
	}
	if ( $theme->errors() ) {
		$found[] = array( 'error', $theme->errors()->get_error_message() );
	}
	if ( get_template() !== $theme->get( 'Template' ) ) {
		/* translators: %s: folder of the parent theme. */
		$found[] = array( 'error', sprintf( __( 'The "Template:" line of style.css has to be %s.', 'tainacan-interface' ), get_template() ) );
	}
	$dir    = $theme->get_stylesheet_directory();
	$domain = (string) $theme->get( 'TextDomain' );
	if ( '' === $domain || 'tainacan-interface' === $domain || 'tainacan' === $domain ) {
		$found[] = array( 'warning', __( 'Give the child theme its own "Text Domain" (its folder name), not the one of Tainacan Interface or Tainacan.', 'tainacan-interface' ) );
	}

	$parent = get_template_directory();
	$copies = array();
	foreach ( tainacan_interface_kit_files( $dir ) as $file ) {
		$path      = $dir . '/' . $file;
		$extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		if ( 'php' === $extension && ! in_array( $file, array( 'functions.php' ), true ) && 0 !== strpos( $file, 'patterns/' ) && 0 !== strpos( $file, 'inc/' ) && file_exists( $parent . '/' . $file ) ) {
			$copies[] = $file;
		}
		if ( ! in_array( $extension, array( 'php', 'css', 'js', 'json', 'html' ), true ) ) {
			continue;
		}
		$code = (string) file_get_contents( $path, false, null, 0, 262144 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a file of an installed theme, read-only, for the check.
		if ( preg_match( '/https?:\/\/(fonts\.googleapis|fonts\.gstatic|cdn\.jsdelivr|cdnjs\.cloudflare|unpkg|ajax\.googleapis|code\.jquery)\b/i', $code ) ) {
			/* translators: %s: file. */
			$found[] = array( 'error', sprintf( __( '%s loads a file from an outside server (CDN). Put the file in the theme.', 'tainacan-interface' ), $file ) );
		}
		if ( 'php' === $extension && preg_match( '/get_template_directory_uri\(\)\s*\.\s*[\'"]\/style(\.min)?\.css/', $code ) ) {
			/* translators: %s: file. */
			$found[] = array( 'warning', sprintf( __( '%s enqueues the stylesheet of Tainacan Interface, which already loads by itself. Remove it: the theme keeps the right order.', 'tainacan-interface' ), $file ) );
		}
		if ( 'php' === $extension && preg_match( '/\$_(GET|POST|REQUEST|SERVER)\[/', tainacan_interface_strip_comments( $code, 'php' ) ) ) {
			/* translators: %s: file. */
			$found[] = array( 'warning', sprintf( __( '%s reads the request directly. Use the helpers of inc/request.php (tainacan_interface_query_arg and the others).', 'tainacan-interface' ), $file ) );
		}
		if ( 'json' === $extension && 'theme.json' === $file ) {
			$json = json_decode( $code, true );
			if ( ! is_array( $json ) ) {
				$found[] = array( 'error', __( 'theme.json is not valid JSON.', 'tainacan-interface' ) );
			} elseif ( ! isset( $json['version'] ) || (int) $json['version'] < 2 ) {
				$found[] = array( 'error', __( 'theme.json needs "version": 2 or 3.', 'tainacan-interface' ) );
			}
		}
	}
	if ( $copies ) {
		/* translators: %s: list of files. */
		$found[] = array( 'warning', sprintf( __( 'Copies of templates of Tainacan Interface (they stop receiving its fixes): %s. Prefer the hooks and options listed in the kit.', 'tainacan-interface' ), implode( ', ', $copies ) ) );
	}

	$pattern_files = glob( $dir . '/patterns/*.php' );
	foreach ( is_array( $pattern_files ) ? $pattern_files : array() as $pattern_file ) {
		$headers = get_file_data(
			$pattern_file,
			array(
				'title' => 'Title',
				'slug'  => 'Slug',
			)
		);
		$label   = 'patterns/' . wp_basename( $pattern_file );
		if ( '' === $headers['title'] || '' === $headers['slug'] ) {
			/* translators: %s: file. */
			$found[] = array( 'error', sprintf( __( '%s needs the "Title:" and "Slug:" lines in its header.', 'tainacan-interface' ), $label ) );
			continue;
		}
		if ( 0 !== strpos( $headers['slug'], $stylesheet . '/' ) ) {
			/* translators: 1: file; 2: expected prefix. */
			$found[] = array( 'warning', sprintf( __( '%1$s: start the Slug with %2$s/.', 'tainacan-interface' ), $label, $stylesheet ) );
		}
		// The patterns of the active theme are registered (their PHP already ran); the others are read without running it.
		$pattern = get_stylesheet() === $stylesheet ? WP_Block_Patterns_Registry::get_instance()->get_registered( $headers['slug'] ) : null;
		if ( $pattern && isset( $pattern['content'] ) ) {
			$found = array_merge( $found, tainacan_interface_kit_check_markup( (string) $pattern['content'], $label ) );
		} else {
			$code  = (string) file_get_contents( $pattern_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a pattern of an installed theme, read-only, for the check.
			$found = array_merge( $found, tainacan_interface_kit_check_markup( (string) preg_replace( '/<\?php.*?(\?>|$)/s', '', $code ), $label, true ) );
		}
	}

	$has_error = false;
	foreach ( $found as $row ) {
		$has_error = $has_error || 'error' === $row[0];
	}
	if ( ! $has_error ) {
		array_unshift( $found, array( 'ok', __( 'Child theme of Tainacan Interface: header, files and patterns follow the standard of the kit.', 'tainacan-interface' ) ) );
	}
	return $found;
}

/**
 * Installed child themes of this theme.
 *
 * @return array<string, string> Folder => name.
 */
function tainacan_interface_kit_child_themes() {
	$out = array();
	foreach ( wp_get_themes() as $stylesheet => $theme ) {
		if ( get_template() === $theme->get_template() && get_template() !== $stylesheet ) {
			$out[ $stylesheet ] = $theme->get( 'Name' );
		}
	}
	return $out;
}

/* ---------- WP-CLI ---------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Kit for AI.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tainacan-interface kit context > site-context.json
	 *     wp tainacan-interface kit zip /tmp/ai-kit.zip
	 *     wp tainacan-interface kit child my-museum --name="My Museum" [--activate]
	 *     wp tainacan-interface kit check [my-museum]
	 *     wp tainacan-interface kit blocks 123
	 *     wp tainacan-interface kit blocks page.html
	 *     wp tainacan-interface kit reference ai-kit/reference
	 *
	 * @param array $args       Subcommand and its argument.
	 * @param array $assoc_args Options.
	 */
	function tainacan_interface_kit_cli( $args, $assoc_args ) {
		$command = isset( $args[0] ) ? $args[0] : 'context';
		$arg     = isset( $args[1] ) ? (string) $args[1] : '';
		$print   = static function ( $rows ) {
			$errors = 0;
			foreach ( $rows as $row ) {
				WP_CLI::log( strtoupper( $row[0] ) . ': ' . $row[1] );
				$errors += 'error' === $row[0] ? 1 : 0;
			}
			if ( $errors ) {
				WP_CLI::error( $errors . ' error(s).' );
			}
			WP_CLI::success( 'Checked.' );
		};
		switch ( $command ) {
			case 'context':
				WP_CLI::log( (string) wp_json_encode( tainacan_interface_kit_context(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
				return;
			case 'zip':
				$result = tainacan_interface_kit_package( '' !== $arg ? $arg : 'tainacan-interface-ai-kit.zip' );
				if ( is_wp_error( $result ) ) {
					WP_CLI::error( $result->get_error_message() );
				}
				WP_CLI::success( 'Kit written.' );
				return;
			case 'child':
				$folder = tainacan_interface_kit_child_write( isset( $assoc_args['name'] ) ? (string) $assoc_args['name'] : '', $arg );
				if ( is_wp_error( $folder ) ) {
					WP_CLI::error( $folder->get_error_message() );
				}
				WP_CLI::success( 'Child theme in ' . $folder );
				if ( ! empty( $assoc_args['activate'] ) ) {
					switch_theme( wp_basename( $folder ) );
					WP_CLI::success( 'Activated.' );
				}
				return;
			case 'check':
				$print( tainacan_interface_kit_check_child( '' !== $arg ? $arg : get_stylesheet() ) );
				return;
			case 'blocks':
				if ( ctype_digit( $arg ) ) {
					$post = get_post( (int) $arg );
					if ( ! $post ) {
						WP_CLI::error( 'No such post.' );
					}
					$print( tainacan_interface_kit_check_markup( $post->post_content, '#' . $post->ID ) );
					return;
				}
				if ( '' === $arg || ! is_readable( $arg ) ) {
					WP_CLI::error( 'Give a post ID or a readable file.' );
				}
				$print( tainacan_interface_kit_check_markup( (string) file_get_contents( $arg ), wp_basename( $arg ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local file named on the command line.
				return;
			case 'reference':
				$dir = '' !== $arg ? rtrim( $arg, '/' ) : tainacan_interface_kit_path( 'reference' );
				wp_mkdir_p( $dir );
				$options = array();
				foreach ( tainacan_interface_settings_schema() as $key => $option ) {
					$options[ $key ] = array_merge( array( 'theme_mod' => 'tainacan_interface_' . $key ), $option );
				}
				$presets = array();
				foreach ( tainacan_interface_presets() as $slug => $preset ) {
					$presets[ $slug ] = $preset;
				}
				$data = array(
					'about'    => 'Options of Tainacan Interface ' . TAINACAN_INTERFACE_VERSION . '. Read with tainacan_interface_opt( key ); saved as the theme_mod "tainacan_interface_<key>"; "collection": true means a collection may set its own value (post meta "tainacan_interface_opt_<key>"). Generated by: wp tainacan-interface kit reference.',
					'sections' => tainacan_interface_settings_sections(),
					'options'  => $options,
					'presets'  => $presets,
				);
				// tnc-check-ignore TNC-W13: WP-CLI writing to a folder named on the command line; WP_Filesystem could ask for FTP credentials there.
				file_put_contents( $dir . '/options.json', wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- WP-CLI, a file named on the command line.
				WP_CLI::success( 'Written ' . $dir . '/options.json' );
				return;
		}
		WP_CLI::error( 'Unknown subcommand. Use: context, zip, child, check, blocks, reference.' );
	}
	WP_CLI::add_command( 'tainacan-interface kit', 'tainacan_interface_kit_cli' );
}
