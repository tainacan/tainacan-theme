<?php
/**
 * Menus for Tainacan.
 *
 * In Appearance > Menus a "Tainacan" box adds the list of collections, the list
 * of items, each collection and each taxonomy; an item can grow a submenu with
 * all the collections by itself. Each collection and the list of collections
 * also have a menu location of their own, shown as a bar on their pages, and one
 * button creates a starting menu for each collection.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_AUTO_MENU_CLASS = 'tt2-menu-all-collections';

/**
 * The published collections, id => name.
 *
 * @return array
 */
function tainacan_interface_menu_collections() {
	static $names = null;

	if ( null !== $names ) {
		return $names;
	}
	if ( ! class_exists( '\Tainacan\Repositories\Collections' ) ) {
		return array();
	}

	// Every page with the automatic submenu needs this list.
	$names = get_transient( 'tainacan_interface_menu_collections' );
	if ( is_array( $names ) ) {
		return $names;
	}

	$names = array();
	foreach ( (array) \Tainacan\Repositories\Collections::get_instance()->fetch(
		array(
			'posts_per_page' => 500, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- the collections of a site, which are few; only in the menu screens and the automatic submenu.
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		),
		'OBJECT'
	) as $collection ) {
		$names[ (int) $collection->get_id() ] = tainacan_interface_plain( $collection->get_name() );
	}
	set_transient( 'tainacan_interface_menu_collections', $names, DAY_IN_SECONDS );
	return $names;
}

/**
 * A collection was created, renamed, published, hidden or deleted.
 *
 * @param int $post_id Post.
 */
function tainacan_interface_menu_collections_forget( $post_id ) {
	if ( 'tainacan-collection' === get_post_type( $post_id ) ) {
		delete_transient( 'tainacan_interface_menu_collections' );
	}
}
add_action( 'save_post', 'tainacan_interface_menu_collections_forget' );
add_action( 'before_delete_post', 'tainacan_interface_menu_collections_forget' );

/**
 * Menu locations: registered where menus are edited. On the public pages the
 * menu is read straight from the saved locations, so no visit pays for the list
 * of collections.
 */
function tainacan_interface_register_collection_menu_locations() {
	global $pagenow;
	// On a theme switch the core keeps only the locations it knows about, on whichever request it checks.
	if ( ! isset( $GLOBALS['wp_customize'] ) && ! get_option( 'theme_switched' ) && ! ( is_admin() && in_array( $pagenow, array( 'nav-menus.php', 'customize.php' ), true ) ) ) {
		return;
	}

	$locations = array( 'tt2_collections_list' => __( 'List of collections', 'tainacan-interface' ) );
	foreach ( tainacan_interface_menu_collections() as $id => $name ) {
		// The core prints the name of a location as HTML, and $name is plain text with its entities decoded.
		/* translators: %s: name of a collection. */
		$locations[ 'tt2_collection_' . $id ] = esc_html( sprintf( __( 'Collection: %s', 'tainacan-interface' ), $name ) );
	}
	register_nav_menus( $locations );
}
add_action( 'init', 'tainacan_interface_register_collection_menu_locations', 30 );

/**
 * Prints the menu saved in a location as a bar.
 *
 * @param string $location Location.
 * @param string $label    Accessible name of the bar.
 */
function tainacan_interface_the_location_bar( $location, $label ) {
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;
	if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location'       => $location,
			'depth'                => 2,
			'container'            => 'nav',
			'container_class'      => 'tt2-menu-bar',
			'container_aria_label' => $label,
			'menu_class'           => 'tt2-menu-bar__list navbar-nav',
			'fallback_cb'          => false,
			'walker'               => new Tainacan_Interface_Nav_Walker(),
		)
	);
}

/**
 * The menu bar of a collection.
 *
 * @param int $collection_id Collection.
 */
function tainacan_interface_the_collection_menu( $collection_id ) {
	tainacan_interface_the_location_bar( 'tt2_collection_' . absint( $collection_id ), __( 'Menu of the collection', 'tainacan-interface' ) );
}

/**
 * The menu bar of the list of collections.
 */
function tainacan_interface_the_collections_menu() {
	tainacan_interface_the_location_bar( 'tt2_collections_list', __( 'Menu of the collections', 'tainacan-interface' ) );
}

/**
 * The submenu that keeps itself: an item with the class of the automatic menu
 * gets every collection as a child, with the list of collections first, because
 * a parent with children only opens the submenu.
 *
 * @param WP_Post[] $items Sorted menu items.
 * @return WP_Post[]
 */
function tainacan_interface_expand_all_collections( $items ) {
	$auto = false;
	foreach ( $items as $item ) {
		$auto = $auto || in_array( TAINACAN_INTERFACE_AUTO_MENU_CLASS, (array) $item->classes, true );
	}
	if ( ! $auto ) {
		return $items;
	}

	$result = array();
	$order  = 0;

	foreach ( $items as $item ) {
		$item->menu_order = ++$order;
		$result[]         = $item;

		if ( ! in_array( TAINACAN_INTERFACE_AUTO_MENU_CLASS, (array) $item->classes, true ) ) {
			continue;
		}

		$item->classes[] = 'menu-item-has-children';
		$current         = function_exists( 'tainacan_get_collection_id' ) ? (int) tainacan_get_collection_id() : 0;
		$children        = array( 0 => __( 'All collections', 'tainacan-interface' ) ) + tainacan_interface_menu_collections();

		foreach ( $children as $id => $name ) {
			$child                   = new stdClass();
			$child->ID               = 2000000000 + $id;
			$child->db_id            = $child->ID;
			$child->menu_item_parent = (string) $item->db_id;
			$child->object_id        = (string) $id;
			$child->object           = 'tainacan-collection';
			$child->type             = $id ? 'post_type' : 'post_type_archive';
			$child->title            = esc_html( $name ); // A menu title is HTML to the walkers.
			$child->url              = $id ? get_permalink( $id ) : get_post_type_archive_link( 'tainacan-collection' );
			$child->target           = '';
			$child->attr_title       = '';
			$child->description      = '';
			$child->xfn              = '';
			$child->post_parent      = 0;
			$child->classes          = array( 'menu-item', $id && $id === $current ? 'current-menu-item' : '' );
			$child->current          = $id && $id === $current;
			$child->menu_order       = ++$order;
			$result[]                = new WP_Post( $child );
		}
	}

	return $result;
}
add_filter( 'wp_nav_menu_objects', 'tainacan_interface_expand_all_collections' );

/**
 * A row of the box in the Menus screen, in the shape the core checklist expects.
 *
 * @param int    $fake_id   Negative id of the row.
 * @param string $title     Title.
 * @param string $type      Menu item type.
 * @param string $item_object Object of the item.
 * @param int    $object_id Object id.
 * @param string $url       Address.
 * @param array  $classes   Classes.
 * @param string $label     Text in the box, when it differs from the title the item gets.
 * @return object
 */
function tainacan_interface_menu_row( $fake_id, $title, $type, $item_object, $object_id, $url, $classes = array(), $label = '' ) {
	return (object) array(
		'label'            => '' !== $label ? $label : $title,
		'ID'               => $fake_id,
		'db_id'            => 0,
		'menu_item_parent' => 0,
		'object_id'        => $object_id,
		'post_parent'      => 0,
		'type'             => $type,
		'object'           => $item_object,
		'type_label'       => __( 'Tainacan', 'tainacan-interface' ),
		'title'            => esc_html( $title ), // Saved as the title of the menu item, which is HTML.
		'url'              => $url,
		'target'           => '',
		'attr_title'       => '',
		'description'      => '',
		'classes'          => $classes,
		'xfn'              => '',
	);
}

/**
 * Adds the box to the Menus screen.
 */
function tainacan_interface_add_menu_box() {
	if ( ! class_exists( '\Tainacan\Repositories\Collections' ) ) {
		return;
	}
	add_meta_box( 'tainacan-interface-menu-box', __( 'Tainacan', 'tainacan-interface' ), 'tainacan_interface_menu_box', 'nav-menus', 'side', 'high' );
}
add_action( 'load-nav-menus.php', 'tainacan_interface_add_menu_box' );

/**
 * The core hides every box outside its own short list on a user's first visit to
 * the Menus screen, and this one holds the button to create the menus. It is shown
 * once per user; hiding it later through Screen Options is respected.
 */
function tainacan_interface_show_menu_box_once() {
	$user_id = get_current_user_id();
	if ( ! $user_id || get_user_meta( $user_id, 'tainacan_interface_menu_box_shown', true ) ) {
		return;
	}
	update_user_meta( $user_id, 'tainacan_interface_menu_box_shown', 1 );

	$hidden = get_user_option( 'metaboxhidden_nav-menus', $user_id );
	if ( is_array( $hidden ) ) {
		update_user_option( $user_id, 'metaboxhidden_nav-menus', array_values( array_diff( $hidden, array( 'tainacan-interface-menu-box' ) ) ), true );
	}
}
add_action( 'admin_head-nav-menus.php', 'tainacan_interface_show_menu_box_once' );

/**
 * The box: the lists, the collections, the taxonomies and the button that
 * creates a menu per collection.
 */
function tainacan_interface_menu_box() {
	global $nav_menu_selected_id;

	require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

	$walker = new Walker_Nav_Menu_Checklist();
	$rows   = array();
	$fake   = -500000;

	$archive = get_post_type_archive_link( 'tainacan-collection' );
	// An archive row carries a negative object id, as in the core boxes: with 0 the core drops it.
	$rows[] = tainacan_interface_menu_row( --$fake, __( 'Collections', 'tainacan-interface' ), 'post_type_archive', 'tainacan-collection', -1, $archive );
	$rows[] = tainacan_interface_menu_row( --$fake, __( 'Collections', 'tainacan-interface' ), 'post_type_archive', 'tainacan-collection', -1, $archive, array( TAINACAN_INTERFACE_AUTO_MENU_CLASS ), __( 'Collections (with a submenu of all of them, kept up to date)', 'tainacan-interface' ) );
	$rows[] = tainacan_interface_menu_row( --$fake, __( 'All items', 'tainacan-interface' ), 'custom', 'custom', 0, home_url( \Tainacan\Theme_Helper::get_instance()->get_items_list_slug() ) );

	foreach ( tainacan_interface_menu_collections() as $id => $name ) {
		$rows[] = tainacan_interface_menu_row( --$fake, $name, 'post_type', 'tainacan-collection', $id, get_permalink( $id ) );
	}

	foreach ( get_posts(
		array(
			'post_type'      => 'tainacan-taxonomy',
			'post_status'    => 'publish',
			'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- the taxonomies of a site, for one box of an admin screen.
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	) as $taxonomy ) {
		$rows[] = tainacan_interface_menu_row(
			--$fake,
			/* translators: %s: name of a taxonomy. */
			sprintf( __( 'Taxonomy: %s', 'tainacan-interface' ), $taxonomy->post_title ),
			'post_type',
			'tainacan-taxonomy',
			(int) $taxonomy->ID,
			get_permalink( $taxonomy )
		);
	}
	?>
	<div id="tt2-tainacan-menu" class="posttypediv">
		<div class="tabs-panel tabs-panel-active" style="max-height:18rem;overflow:auto">
			<ul class="categorychecklist form-no-clear">
				<?php
				// tnc-check-ignore TNC-S05: the core checklist walker escapes each field, as on the other boxes of this screen.
				echo walk_nav_menu_tree( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the core checklist walker escapes each field, as on the other boxes of this screen.
					$rows,
					0,
					(object) array( 'walker' => $walker )
				);
				?>
			</ul>
		</div>
		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'tainacan-interface' ); ?>" name="add-post-type-menu-item" id="submit-tt2-tainacan-menu">
				<span class="spinner"></span>
			</span>
		</p>
	</div>
	<hr>
	<p class="description"><?php esc_html_e( 'Each collection and the list of collections also have a menu location of their own, in the "Manage Locations" tab: its menu appears as a bar on those pages.', 'tainacan-interface' ); ?></p>
	<p>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_collection_menus' ), 'tainacan_interface_collection_menus' ) ); ?>"><?php esc_html_e( 'Create a menu for each collection', 'tainacan-interface' ); ?></a>
	</p>
	<p class="description"><?php esc_html_e( 'One menu per collection that has none yet, with its start and its most used terms. They stay yours to edit.', 'tainacan-interface' ); ?></p>
	<?php
}

/**
 * The most used terms of a collection, by the taxonomy metadata it shows.
 *
 * @param \Tainacan\Entities\Collection $collection Collection.
 * @param int                           $per        Terms per taxonomy.
 * @return array Array of array( label, terms[] ).
 */
function tainacan_interface_collection_menu_taxonomies( $collection, $per = 8 ) {
	$found = array();
	foreach ( (array) \Tainacan\Repositories\Metadata::get_instance()->fetch_by_collection( $collection, array() ) as $metadatum ) {
		if ( ! $metadatum instanceof \Tainacan\Entities\Metadatum || 'publish' !== $metadatum->get_status() || 'never' === $metadatum->get_display() ) {
			continue;
		}
		if ( 'Tainacan\Metadata_Types\Taxonomy' !== (string) $metadatum->get_metadata_type() ) {
			continue;
		}
		$options  = (array) $metadatum->get_metadata_type_options();
		$taxonomy = empty( $options['taxonomy_id'] ) ? '' : 'tnc_tax_' . absint( $options['taxonomy_id'] );
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		// tnc-check-ignore TNC-P02: one query per taxonomy metadatum of one collection, only when an administrator presses the button.
		$terms = get_terms(
			array(
				'taxonomy'               => $taxonomy,
				'parent'                 => 0,
				'orderby'                => 'count',
				'order'                  => 'DESC',
				'number'                 => $per,
				// With hierarchical on, the core reads every root term to cut them in PHP. A root term may have its items only in the terms under it, so it cannot be left out for an empty count.
				'hierarchical'           => false,
				'hide_empty'             => false,
				'update_term_meta_cache' => false,
			)
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$found[] = array(
				'label'    => esc_html( tainacan_interface_plain( $metadatum->get_name() ) ), // Saved as the title of a menu item, which is HTML.
				'taxonomy' => $taxonomy,
				'terms'    => $terms,
			);
		}
	}
	return $found;
}

/**
 * Creates a menu for each collection without one and saves it in the location of
 * the collection. A collection whose location holds a menu is left alone.
 *
 * Each collection is recorded as it is done, and the work stops after twenty
 * seconds, before a server's time limit cuts it: pressing the button again goes
 * on from where it stopped.
 *
 * @param bool $complete Set to false when collections were left for another run.
 * @return int How many menus were created.
 */
function tainacan_interface_create_collection_menus( &$complete = true ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations = is_array( $locations ) ? $locations : array();
	$created   = 0;
	$started   = microtime( true );
	$complete  = true;

	foreach ( tainacan_interface_menu_collections() as $id => $name ) {
		$location = 'tt2_collection_' . $id;
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( (int) $locations[ $location ] ) ) {
			continue;
		}

		if ( microtime( true ) - $started > 20 ) {
			$complete = false;
			break;
		}

		/* translators: %s: name of a collection. */
		$menu_name = esc_html( sprintf( __( 'Collection: %s', 'tainacan-interface' ), $name ) );
		$menu_id   = wp_create_nav_menu( $menu_name );
		// Two collections with the same name, or a menu left from before: the number tells them apart.
		if ( is_wp_error( $menu_id ) ) {
			$menu_id = wp_create_nav_menu( $menu_name . ' (' . $id . ')' );
		}
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => __( 'Start of the collection', 'tainacan-interface' ),
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'tainacan-collection',
				'menu-item-object-id' => $id,
				'menu-item-status'    => 'publish',
			)
		);

		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( $id ); // tnc-check-ignore TNC-P02: per collection without a menu, only when an administrator presses the button.
		if ( $collection instanceof \Tainacan\Entities\Collection ) {
			foreach ( tainacan_interface_collection_menu_taxonomies( $collection ) as $group ) {
				$parent = wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $group['label'],
						'menu-item-type'   => 'custom',
						'menu-item-url'    => '#',
						'menu-item-status' => 'publish',
					)
				);
				if ( is_wp_error( $parent ) ) {
					continue;
				}
				foreach ( $group['terms'] as $term ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-title'     => $term->name,
							'menu-item-type'      => 'taxonomy',
							'menu-item-object'    => $group['taxonomy'],
							'menu-item-object-id' => $term->term_id,
							'menu-item-parent-id' => $parent,
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}

		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		++$created;
	}

	return $created;
}

/**
 * The button of the box.
 */
function tainacan_interface_collection_menus_action() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_collection_menus' );
	$complete = true;
	$created  = tainacan_interface_create_collection_menus( $complete );
	wp_safe_redirect(
		add_query_arg(
			array(
				'tainacan_interface_menus_created' => $created,
				'tainacan_interface_menus_more'    => $complete ? 0 : 1,
			),
			admin_url( 'nav-menus.php?action=locations' )
		)
	);
	exit;
}
add_action( 'admin_post_tainacan_interface_collection_menus', 'tainacan_interface_collection_menus_action' );

/**
 * Confirms how many menus the button created.
 */
function tainacan_interface_collection_menus_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'nav-menus' !== $screen->id || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$created = absint( tainacan_interface_query_arg( 'tainacan_interface_menus_created' ) );
	if ( '' === tainacan_interface_query_arg( 'tainacan_interface_menus_created' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %d: number of menus created. */
				_n( '%d menu created.', '%d menus created.', $created, 'tainacan-interface' ),
				$created
			) . ( absint( tainacan_interface_query_arg( 'tainacan_interface_menus_more' ) ) ? ' ' . __( 'Some collections are left: press the button again to go on.', 'tainacan-interface' ) : '' )
		)
	);
}
add_action( 'admin_notices', 'tainacan_interface_collection_menus_notice' );
