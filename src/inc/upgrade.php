<?php
/**
 * Moving to version 3: from Tainacan Interface 2.x in place, and from another
 * theme by activation.
 *
 * An update of the theme replaces its folder and fires nothing else; an
 * activation fires after_switch_theme. That is how the two cases are told
 * apart:
 * - activation (a new site, or a site coming from another theme): the new
 *   features come on, and the settings of a Tainacan Interface derivative the
 *   site was using (a child theme, Tainacan Acervo) are carried over;
 * - update in place from 2.x (the WordPress.org update reaches every site at
 *   once): the page must look the same the next morning. The features the
 *   visitor sees are recorded as off, and the administrators get a notice
 *   that they exist. The security fixes and the local font have no switch.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_VERSION_OPTION = 'tainacan_interface_version';
const TAINACAN_INTERFACE_NOTICE_OPTION  = 'tainacan_interface_new_features_notice';

/**
 * Theme mods of the features the visitor sees, all on by default.
 *
 * @return string[]
 */
function tainacan_interface_feature_mods() {
	return array( 'tainacan_interface_item_drawer', 'tainacan_interface_item_viewer', 'tainacan_interface_item_highlight', 'tainacan_interface_item_aside' );
}

/**
 * Activation: records the version, so the next request does not take the
 * site for an update, and carries over the settings of the theme the site was
 * using when it derives from Tainacan Interface.
 *
 * The settings of a derivative are recognised by its tainacan_* theme mods.
 * They replace the ones this theme had stored (from an earlier time it was
 * active), which are kept in an option first so they can be put back.
 *
 * @param string        $old_name  Name of the previous theme.
 * @param WP_Theme|bool $old_theme Previous theme, or false when it no longer exists.
 */
function tainacan_interface_on_activation( $old_name = '', $old_theme = false ) {
	unset( $old_name );
	update_option( TAINACAN_INTERFACE_VERSION_OPTION, TAINACAN_INTERFACE_VERSION );

	$previous = $old_theme instanceof WP_Theme ? $old_theme->get_stylesheet() : '';
	if ( '' === $previous || get_stylesheet() === $previous || get_template() === $previous ) {
		return;
	}
	$mods = get_option( 'theme_mods_' . $previous );
	if ( ! is_array( $mods ) || ! preg_grep( '/^tainacan_/', array_filter( array_keys( $mods ), 'is_string' ) ) ) {
		return;
	}

	// The first backup is the one worth keeping: later ones would hold imported settings.
	$own = get_option( 'theme_mods_' . get_stylesheet() );
	if ( is_array( $own ) && ! empty( $own ) ) {
		add_option( 'tainacan_interface_mods_before_import', $own, '', false );
	}

	// Replace, not merge: a switch this theme kept from an earlier time must not survive the import.
	$imported = tainacan_interface_rename_working_mods( $mods );
	unset( $imported['custom_css_post_id'] );
	if ( is_array( $own ) && isset( $own['custom_css_post_id'] ) ) {
		$imported['custom_css_post_id'] = $own['custom_css_post_id'];
	}
	update_option( 'theme_mods_' . get_stylesheet(), $imported );
	tainacan_interface_rename_working_meta();

	// Additional CSS is a post named after the stylesheet: it needs its own copy.
	$css = wp_get_custom_css( $previous );
	if ( '' !== trim( $css ) && '' === trim( wp_get_custom_css( get_stylesheet() ) ) ) {
		wp_update_custom_css_post( $css, array( 'stylesheet' => get_stylesheet() ) );
	}
}
add_action( 'after_switch_theme', 'tainacan_interface_on_activation', 10, 2 );

/**
 * Update in place: the first request after the folder was replaced. Runs
 * after check_theme_switched() (init, 99), so an activation is already
 * recorded when this looks.
 */
function tainacan_interface_on_update() {
	$seen = get_option( TAINACAN_INTERFACE_VERSION_OPTION );
	if ( TAINACAN_INTERFACE_VERSION === $seen ) {
		return;
	}

	if ( false === $seen ) {
		$mods    = get_theme_mods();
		$renamed = tainacan_interface_rename_working_mods( is_array( $mods ) ? $mods : array() );
		$working = tainacan_interface_rename_working_meta() || $renamed !== $mods;
		if ( $renamed !== $mods ) {
			update_option( 'theme_mods_' . get_stylesheet(), $renamed );
		}
		// From the 2.x working name the features were already there: they stay as the site had them.
		if ( ! $working ) {
			// From official 2.x: what the visitor sees stays as it was until an administrator turns it on.
			foreach ( tainacan_interface_feature_mods() as $mod ) {
				if ( ! array_key_exists( $mod, $renamed ) ) {
					set_theme_mod( $mod, false );
				}
			}
			update_option( TAINACAN_INTERFACE_NOTICE_OPTION, 1 );
		}
	}

	update_option( TAINACAN_INTERFACE_VERSION_OPTION, TAINACAN_INTERFACE_VERSION );
}
add_action( 'init', 'tainacan_interface_on_update', 100 );

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
 * Notice to administrators after an update from 2.x.
 */
function tainacan_interface_new_features_notice() {
	if ( ! get_option( TAINACAN_INTERFACE_NOTICE_OPTION ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$customize = add_query_arg( array( 'autofocus[section]' => 'tainacan_interface_features' ), admin_url( 'customize.php' ) );
	$dismiss   = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_notice' ), 'tainacan_interface_dismiss_notice' );
	?>
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'Tainacan Interface 3 has new features, off on this site until you turn them on.', 'tainacan-interface' ); ?></strong></p>
		<p><?php esc_html_e( 'An item preview drawer on the lists, a file viewer and PDF reader on the item page, a highlighted item record and a side column of related items. The security fixes are already on.', 'tainacan-interface' ); ?></p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $customize ); ?>"><?php esc_html_e( 'See the new features', 'tainacan-interface' ); ?></a>
			<a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'tainacan-interface' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'tainacan_interface_new_features_notice' );

/**
 * Dismisses the notice for the whole site.
 */
function tainacan_interface_dismiss_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_dismiss_notice' );
	delete_option( TAINACAN_INTERFACE_NOTICE_OPTION );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_tainacan_interface_dismiss_notice', 'tainacan_interface_dismiss_notice' );

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
