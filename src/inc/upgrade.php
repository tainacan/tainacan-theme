<?php
/**
 * Moving to this version: in place from an earlier Tainacan Interface, and from
 * another theme by activation.
 *
 * An update of the theme replaces its folder and fires nothing else; an
 * activation fires after_switch_theme. That is how the two cases are told
 * apart:
 * - activation (a new site, or a site coming from another theme): an
 *   administrator is offered the settings of a Tainacan Interface derivative the
 *   site was using (Tainacan Acervo, for example);
 * - update in place: the settings of the working name of 2.x are renamed, the
 *   choice of the earlier look is forgotten (there is one design) and
 *   the administrators get a notice saying where to adjust the design.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_STATE_OPTION = 'tainacan_interface';

/**
 * Last migration step of the theme. Steps, not version numbers, say what a
 * site still needs: the numbering went back from 4.x to 2.11, so comparing
 * versions would run old steps again on every update.
 */
const TAINACAN_INTERFACE_MIGRATION_STEP = 4;

/**
 * Last step that only concerns a site updated in place (the look of 4.2, the
 * order of the item of 4.8). Steps after it convert data any site may have,
 * so an activation starts after this one and runs the rest.
 */
const TAINACAN_INTERFACE_MIGRATION_IN_PLACE = 2;

/**
 * What the theme keeps about the site, all in one option: the version it last
 * saw, the notices waiting to be read or dismissed and what the checks of a
 * child theme found. Up to 4.0 each of these had an option of its own.
 *
 * @return array
 */
function tainacan_interface_state_all() {
	$state = get_option( TAINACAN_INTERFACE_STATE_OPTION );
	if ( is_array( $state ) ) {
		return $state;
	}

	$state = array();
	$found = array();
	foreach ( array(
		'version'              => 'tainacan_interface_version',
		'features_notice'      => 'tainacan_interface_new_features_notice',
		'design_notice'        => 'tainacan_interface_new_design_notice',
		'mods_before_import'   => 'tainacan_interface_mods_before_import',
		'child_imports_parent' => 'tainacan_interface_child_imports_parent',
		'child_flawed_copies'  => 'tainacan_interface_child_flawed_copies',
		'child_flaws_seen'     => 'tainacan_interface_child_flaws_dismissed',
	) as $key => $old ) {
		$value = get_option( $old );
		if ( false !== $value ) {
			$state[ $key ] = $value;
			$found[ $old ] = true;
		}
	}
	if ( $state ) {
		// The new one first: a request that arrives in between must not take the site for one that never had the theme.
		update_option( TAINACAN_INTERFACE_STATE_OPTION, $state, true );
		foreach ( array_keys( $found ) as $old ) {
			delete_option( $old );
		}
	}
	return $state;
}

/**
 * Reads one value of the state of the theme.
 *
 * @param string $key      Key.
 * @param mixed  $fallback Value when there is none.
 * @return mixed
 */
function tainacan_interface_state( $key, $fallback = false ) {
	$state = tainacan_interface_state_all();
	return array_key_exists( $key, $state ) ? $state[ $key ] : $fallback;
}

/**
 * Records one value of the state of the theme.
 *
 * @param string $key   Key.
 * @param mixed  $value Value; null forgets it.
 */
function tainacan_interface_state_set( $key, $value ) {
	$state = tainacan_interface_state_all();
	if ( null === $value ) {
		unset( $state[ $key ] );
	} else {
		$state[ $key ] = $value;
	}
	update_option( TAINACAN_INTERFACE_STATE_OPTION, $state, true );
}

/**
 * Activation: records the version, so the next request does not take the site
 * for an update. Settings of another theme come only when an administrator asks,
 * in the notice below.
 *
 * @param string        $old_name  Name of the previous theme.
 * @param WP_Theme|bool $old_theme Previous theme, or false when it no longer exists.
 */
function tainacan_interface_on_activation( $old_name = '', $old_theme = false ) {
	unset( $old_name );
	// A site that had the theme before keeps the steps it still lacks; the next request runs them.
	$previous = tainacan_interface_state( 'version' );
	tainacan_interface_state_set( 'migration', false === $previous ? TAINACAN_INTERFACE_MIGRATION_IN_PLACE : tainacan_interface_migration_step( $previous ) );
	tainacan_interface_state_set( 'version', TAINACAN_INTERFACE_VERSION );
	// An undo offered before another theme was active would put back settings older than what the site has now.
	tainacan_interface_state_set( 'pending_import', null );

	$previous = $old_theme instanceof WP_Theme ? $old_theme->get_stylesheet() : '';
	if ( '' === $previous || get_stylesheet() === $previous ) {
		return;
	}
	$mods = get_option( 'theme_mods_' . $previous );
	if ( ! is_array( $mods ) ) {
		return;
	}
	if ( get_template() === $previous ) {
		// From Tainacan Interface to a child of it. The settings of Customize are
		// kept per theme folder, so the child would open without the colors, logo
		// and menus of the site: a child that has none of its own starts with the
		// parent's (with the usual undo); one that has them is offered the import.
		if ( is_child_theme() ) {
			$own = get_option( 'theme_mods_' . get_stylesheet() );
			if ( is_array( $own ) && preg_grep( '/^tainacan_/', array_filter( array_keys( $own ), 'is_string' ) ) ) {
				tainacan_interface_state_set( 'import_from', $previous );
			} else {
				tainacan_interface_import_settings( $previous );
			}
		}
		return;
	}
	if ( preg_grep( '/^tainacan_/', array_filter( array_keys( $mods ), 'is_string' ) ) ) {
		tainacan_interface_state_set( 'import_from', $previous );
	}
}
add_action( 'after_switch_theme', 'tainacan_interface_on_activation', 10, 2 );

/**
 * Brings the settings of a theme derived from Tainacan Interface, recognised by
 * its tainacan_* theme mods. They replace the ones this theme had stored, which
 * are kept first.
 *
 * @param string $previous Stylesheet of the other theme.
 */
function tainacan_interface_import_settings( $previous ) {
	$mods = get_option( 'theme_mods_' . $previous );
	if ( ! is_array( $mods ) ) {
		return;
	}

	// What "undo" puts back: the settings as they are now, empty or not, and the
	// Additional CSS when the import writes it. One record, replaced at every import.
	$own     = get_option( 'theme_mods_' . get_stylesheet() );
	$pending = array(
		'from' => $previous,
		'mods' => is_array( $own ) ? $own : array(),
		'css'  => null,
	);

	// Replace, not merge: a switch this theme kept from an earlier time must not survive the import.
	$imported = tainacan_interface_rename_working_mods( $mods );
	unset( $imported['custom_css_post_id'] );
	if ( is_array( $own ) && isset( $own['custom_css_post_id'] ) ) {
		$imported['custom_css_post_id'] = $own['custom_css_post_id'];
	}
	unset( $imported['tainacan_interface_design'] );
	update_option( 'theme_mods_' . get_stylesheet(), $imported );
	tainacan_interface_rename_working_meta();

	// Additional CSS is a post named after the stylesheet: it needs its own copy.
	$css = wp_get_custom_css( $previous );
	if ( '' !== trim( $css ) && '' === trim( wp_get_custom_css( get_stylesheet() ) ) ) {
		wp_update_custom_css_post( $css, array( 'stylesheet' => get_stylesheet() ) );
		$pending['css'] = '';
	}
	tainacan_interface_state_set( 'pending_import', $pending );
}

/**
 * Back to the page the button was on.
 */
function tainacan_interface_admin_post_back() {
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'themes.php' ) );
	exit;
}

/**
 * After the import: says what was brought and offers to undo it. The record of
 * what the theme had before stays until this notice is dismissed.
 */
function tainacan_interface_import_done_notice() {
	$pending = tainacan_interface_state( 'pending_import' );
	if ( ! is_array( $pending ) || empty( $pending['from'] ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$theme   = wp_get_theme( (string) $pending['from'] );
	$undo    = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_undo_import' ), 'tainacan_interface_undo_import' );
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_import_done' ), 'tainacan_interface_dismiss_import_done' );
	?>
	<div class="notice notice-success">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: name of a theme. */
					__( 'The settings of %s were brought in. Undo puts back the settings, menus and logo Tainacan Interface had before, and discards what was changed since, including the Additional CSS the import copied.', 'tainacan-interface' ),
					$theme->exists() ? $theme->get( 'Name' ) : (string) $pending['from']
				)
			);
			?>
		</p>
		<p>
			<a class="button" href="<?php echo esc_url( $undo ); ?>"><?php esc_html_e( 'Undo the import', 'tainacan-interface' ); ?></a>
			<a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Keep them and dismiss', 'tainacan-interface' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'tainacan_interface_import_done_notice' );

/**
 * Puts back what the theme had before the import: the Additional CSS first,
 * so the restored settings do not inherit the id the CSS post would write.
 */
function tainacan_interface_undo_import() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_undo_import' );
	$pending = tainacan_interface_state( 'pending_import' );
	if ( is_array( $pending ) ) {
		if ( isset( $pending['css'] ) && is_string( $pending['css'] ) ) {
			wp_update_custom_css_post( $pending['css'], array( 'stylesheet' => get_stylesheet() ) );
		}
		update_option( 'theme_mods_' . get_stylesheet(), isset( $pending['mods'] ) && is_array( $pending['mods'] ) ? $pending['mods'] : array() );
		tainacan_interface_state_set( 'pending_import', null );
	}
	tainacan_interface_admin_post_back();
}
add_action( 'admin_post_tainacan_interface_undo_import', 'tainacan_interface_undo_import' );

/**
 * Keeps the imported settings and forgets what was there before.
 */
function tainacan_interface_dismiss_import_done() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_dismiss_import_done' );
	tainacan_interface_state_set( 'pending_import', null );
	tainacan_interface_admin_post_back();
}
add_action( 'admin_post_tainacan_interface_dismiss_import_done', 'tainacan_interface_dismiss_import_done' );

/**
 * Offers the settings of the theme the site was using.
 */
function tainacan_interface_import_notice() {
	$previous = (string) tainacan_interface_state( 'import_from', '' );
	if ( '' === $previous || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$theme   = wp_get_theme( $previous );
	$import  = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_import' ), 'tainacan_interface_import' );
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_import' ), 'tainacan_interface_dismiss_import' );
	?>
	<div class="notice notice-info">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: name of a theme. */
					__( 'This site was using %s, which derives from Tainacan Interface. Do you want to bring its settings? They replace the ones stored for Tainacan Interface.', 'tainacan-interface' ),
					$theme->exists() ? $theme->get( 'Name' ) : $previous
				)
			);
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $import ); ?>"><?php esc_html_e( 'Bring the settings', 'tainacan-interface' ); ?></a>
			<a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'tainacan-interface' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'tainacan_interface_import_notice' );

/**
 * The button that brings the settings.
 */
function tainacan_interface_import_action() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_import' );
	$previous = (string) tainacan_interface_state( 'import_from', '' );
	if ( '' !== $previous ) {
		tainacan_interface_import_settings( $previous );
		tainacan_interface_state_set( 'import_from', null );
	}
	tainacan_interface_admin_post_back();
}
add_action( 'admin_post_tainacan_interface_import', 'tainacan_interface_import_action' );

/**
 * Leaves the settings of the other theme where they are.
 */
function tainacan_interface_dismiss_import() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_dismiss_import' );
	tainacan_interface_state_set( 'import_from', null );
	tainacan_interface_admin_post_back();
}
add_action( 'admin_post_tainacan_interface_dismiss_import', 'tainacan_interface_dismiss_import' );

/**
 * Migration step a site has reached. Sites from before the steps existed
 * are placed by the version they last saw (3.x and 4.x development builds).
 *
 * @param string|false $seen Version the site last saw, false for none.
 * @return int
 */
function tainacan_interface_migration_step( $seen ) {
	$step = tainacan_interface_state( 'migration' );
	if ( false !== $step ) {
		return (int) $step;
	}
	if ( false === $seen ) {
		return 0;
	}
	if ( version_compare( (string) $seen, '4.9.0', '>=' ) ) {
		return 2;
	}
	if ( version_compare( (string) $seen, '4.3.0', '>=' ) ) {
		return 1;
	}
	return 0;
}

/**
 * Update in place: the first request after the folder was replaced. Runs
 * after check_theme_switched() (init, 99), so an activation is already
 * recorded when this looks.
 */
function tainacan_interface_on_update() {
	$seen = tainacan_interface_state( 'version' );
	// Same version: only a step that waits for Tainacan (step 3) brings this back, once Tainacan is active.
	if ( TAINACAN_INTERFACE_VERSION === $seen && ( (int) tainacan_interface_state( 'migration', 0 ) >= TAINACAN_INTERFACE_MIGRATION_STEP || ! post_type_exists( 'tainacan-collection' ) ) ) {
		return;
	}

	// The first request after an update runs this; those that arrive with it wait for the next time.
	$lock = 'tainacan_interface_updating';
	if ( ! add_option( $lock, time(), '', false ) ) {
		if ( time() - (int) get_option( $lock ) < MINUTE_IN_SECONDS ) {
			return;
		}
		update_option( $lock, time(), false );
	}

	if ( false === $seen ) {
		$mods    = get_theme_mods();
		$renamed = tainacan_interface_rename_working_mods( is_array( $mods ) ? $mods : array() );
		tainacan_interface_rename_working_meta();
		if ( $renamed !== $mods ) {
			update_option( 'theme_mods_' . get_stylesheet(), $renamed );
		}
	}

	$step = tainacan_interface_migration_step( $seen );

	// Step 1 (was 4.3.0): a site could stay on the earlier look; now there is one design.
	if ( $step < 1 ) {
		remove_theme_mod( 'tainacan_interface_design' );
		tainacan_interface_state_set( 'design_notice', 1 );
		tainacan_interface_state_set( 'features_notice', null );
	}

	// Step 2 (was 4.9.0): the order of document and record became part of the layout.
	if ( $step < 2 && false !== $seen ) {
		tainacan_interface_merge_item_order();
	}

	// Step 3 (2.12.0): the layout of the metadata sections and the open panels became one setting.
	$reached = TAINACAN_INTERFACE_MIGRATION_STEP;
	if ( $step < 3 && ! tainacan_interface_migrate_sections() ) {
		$reached = 2;
	}

	// Step 4 (2.14.0): options of the original that repeated newer ones.
	if ( $step < 4 ) {
		tainacan_interface_merge_repeated_mods();
	}

	tainacan_interface_state_set( 'migration', max( $step, $reached ) );
	tainacan_interface_state_set( 'version', TAINACAN_INTERFACE_VERSION );
	delete_option( $lock );
}
add_action( 'init', 'tainacan_interface_on_update', 100 );

/**
 * Passes the value of the options of the original that repeated newer ones
 * (fixed header, hidden banner, hidden date of the item) to the newer option,
 * unless the site already chose it, and removes them with the header options
 * that no longer had an effect.
 */
function tainacan_interface_merge_repeated_mods() {
	$mods  = get_theme_mods();
	$mods  = is_array( $mods ) ? $mods : array();
	$pairs = array(
		'tainacan_fixed_header'               => array( 'tainacan_interface_header_sticky', true ),
		'tainacan_hide_header_banner'         => array( 'tainacan_interface_hero_style', 'none' ),
		'tainacan_single_item_hide_item_meta' => array( 'tainacan_interface_item_subtitle', false ),
	);
	foreach ( $pairs as $old => $new ) {
		if ( ! empty( $mods[ $old ] ) && ! array_key_exists( $new[0], $mods ) ) {
			set_theme_mod( $new[0], $new[1] );
		}
	}
	foreach ( array_merge( array_keys( $pairs ), array( 'tainacan_header_alignment_options', 'tainacan_header_min_height' ) ) as $old ) {
		remove_theme_mod( $old );
	}
}

/**
 * Layout that shows what a page showed with a layout and an order of 4.8.
 *
 * @param string $layout Layout of 4.8 (stacked, media-left, media-right).
 * @param string $order  Order: 4.5 key or 2.x value.
 * @return string Layout of 4.9.
 */
function tainacan_interface_layout_for_order( $layout, $order ) {
	if ( in_array( $layout, array( 'media-left', 'media-right' ), true ) ) {
		return $layout;
	}
	$map = array(
		'document-record'               => 'stacked-split',
		'record-first'                  => 'stacked-record',
		'document-metadata-attachments' => 'stacked-split',
		'metadata-document-attachments' => 'stacked-record',
	);
	return isset( $map[ $order ] ) ? $map[ $order ] : 'stacked';
}

/**
 * Converts the separate "order of the document and the record" (4.5 to 4.8,
 * and the 2.x "sections order") into the layout, for the site and for each
 * collection, so every item page keeps the order it had; removes the old values.
 */
function tainacan_interface_merge_item_order() {
	$site_order = (string) get_theme_mod( 'tainacan_interface_item_order', '' );
	if ( '' === $site_order ) {
		$site_order = (string) get_theme_mod( 'tainacan_single_item_layout_sections_order', '' );
	}
	$site_old = (string) get_theme_mod( 'tainacan_interface_item_layout', '' );
	$site_old = '' !== $site_old ? $site_old : 'stacked';
	$site_new = tainacan_interface_layout_for_order( $site_old, $site_order );
	if ( $site_new !== $site_old ) {
		set_theme_mod( 'tainacan_interface_item_layout', $site_new );
	}
	remove_theme_mod( 'tainacan_interface_item_order' );

	// Every collection with its own layout or order: what its pages showed, with what it inherited.
	$collections = get_posts(
		array(
			'post_type'      => 'tainacan-collection',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_query -- one-time conversion on update.
				'relation' => 'OR',
				array(
					'key'     => 'tainacan_interface_opt_item_order',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'tainacan_interface_opt_item_layout',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	foreach ( $collections as $collection_id ) {
		$own_layout = (string) get_post_meta( $collection_id, 'tainacan_interface_opt_item_layout', true );
		$own_order  = (string) get_post_meta( $collection_id, 'tainacan_interface_opt_item_order', true );
		$shown      = tainacan_interface_layout_for_order( '' !== $own_layout ? $own_layout : $site_old, '' !== $own_order ? $own_order : $site_order );
		if ( '' !== $own_layout || $shown !== $site_new ) {
			if ( $shown !== $own_layout ) {
				update_post_meta( $collection_id, 'tainacan_interface_opt_item_layout', $shown );
			}
		}
		delete_post_meta( $collection_id, 'tainacan_interface_opt_item_order' );
	}
}

/**
 * Theme mods of the 2.x working name (tema_tainacan_*) under their 3.0 names.
 * A 3.0 name already set wins.
 *
 * @param array $mods Theme mods.
 * @return array
 */
function tainacan_interface_rename_working_mods( $mods ) {
	foreach ( $mods as $name => $value ) {
		if ( is_string( $name ) && 0 === strpos( $name, 'tema_tainacan_' ) ) {
			$new = 'tainacan_interface_' . substr( $name, strlen( 'tema_tainacan_' ) );
			if ( ! array_key_exists( $new, $mods ) ) {
				$mods[ $new ] = $value;
			}
			unset( $mods[ $name ] );
		}
	}
	return $mods;
}

/**
 * Metadatum options of the 2.x working name ("show in the side drawer", "use
 * to suggest related items") under their 3.0 keys. Runs once per site.
 *
 * @return bool Whether any option was renamed.
 */
function tainacan_interface_rename_working_meta() {
	global $wpdb;
	$renamed = 0;
	foreach ( array(
		'tema_tainacan_drawer'  => 'tainacan_interface_drawer',
		'tema_tainacan_related' => 'tainacan_interface_related',
	) as $old => $new ) {
		// One UPDATE renames every row of a key; the meta API would read and rewrite each metadatum post. Runs once, on the first request of 3.0.
		$renamed += (int) $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s", $new, $old ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk rename of a meta key, once per site; there is no API for it, and the caches are flushed below.
	}
	if ( $renamed > 0 ) {
		if ( function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush_group( 'post_meta' );
		} else {
			wp_cache_flush();
		}
	}
	return $renamed > 0;
}

/**
 * Notice to administrators after an update from an earlier version: the new
 * design exists and is off.
 */
function tainacan_interface_new_design_notice() {
	if ( ! tainacan_interface_state( 'design_notice' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$customize = add_query_arg( array( 'autofocus[panel]' => 'tainacan_interface_style' ), admin_url( 'customize.php' ) );
	$dismiss   = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_design_notice' ), 'tainacan_interface_dismiss_design_notice' );
	?>
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'Tainacan Interface has a new design.', 'tainacan-interface' ); ?></strong></p>
		<p><?php esc_html_e( 'A new collection header, breadcrumb, list of collections, item preview and item page, all adjustable in Appearance > Customize > Tainacan Interface, and menus for each collection in Appearance > Menus.', 'tainacan-interface' ); ?></p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $customize ); ?>"><?php esc_html_e( 'Adjust the design', 'tainacan-interface' ); ?></a>
			<a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'tainacan-interface' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'tainacan_interface_new_design_notice' );

/**
 * Dismisses the design notice for the whole site.
 */
function tainacan_interface_dismiss_design_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_dismiss_design_notice' );
	tainacan_interface_state_set( 'design_notice', null );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_tainacan_interface_dismiss_design_notice', 'tainacan_interface_dismiss_design_notice' );

/**
 * Translations. The files shipped in languages/ come first: in a domain with
 * several files the first one loaded wins, and these carry the texts new in 3.0
 * and the corrections. The WordPress.org language pack of tainacan-interface
 * (translate.wordpress.org, the same domain as 2.x) fills in the rest, such as
 * the languages the theme does not ship.
 *
 * Hooked on its own (not inside the pluggable tainacan_setup(), which a child
 * theme may replace), after the init callback that calls
 * load_theme_textdomain(), and again on every locale switch.
 */
function tainacan_interface_load_translations() {
	$locale = determine_locale();
	unload_textdomain( 'tainacan-interface', true );
	foreach ( array( get_template_directory() . '/languages/' . $locale . '.mo', WP_LANG_DIR . '/themes/tainacan-interface-' . $locale . '.mo' ) as $file ) {
		if ( is_readable( $file ) ) {
			load_textdomain( 'tainacan-interface', $file, $locale );
		}
	}
}
add_action( 'init', 'tainacan_interface_load_translations', 20 );
add_action( 'change_locale', 'tainacan_interface_load_translations' );
