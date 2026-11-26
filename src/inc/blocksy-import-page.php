<?php
/**
 * The page of the import from Blocksy (Appearance > Import from Blocksy), its
 * place in the Customizer, the notice after an activation that came from
 * Blocksy, the "try it in Customize" preview and the WP-CLI command.
 *
 * Three steps: where the settings come from (this site or a file), what each
 * one becomes (a table per topic, with a sketch before and after, and a box to
 * leave a topic out), and what was done (with undo).
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_BLOCKSY_PAGE = 'tainacan-interface-blocksy';

/**
 * Address of the page.
 *
 * @param array $args Query arguments.
 * @return string
 */
function tainacan_interface_blocksy_page_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => TAINACAN_INTERFACE_BLOCKSY_PAGE ), $args ), admin_url( 'themes.php' ) );
}

/**
 * The page under Appearance.
 */
function tainacan_interface_blocksy_menu() {
	$hook = add_theme_page(
		__( 'Import from Blocksy', 'tainacan-interface' ),
		__( 'Import from Blocksy', 'tainacan-interface' ),
		'edit_theme_options',
		TAINACAN_INTERFACE_BLOCKSY_PAGE,
		'tainacan_interface_blocksy_render'
	);
	if ( $hook ) {
		add_action( 'admin_print_styles-' . $hook, 'tainacan_interface_blocksy_assets' );
	}
}
add_action( 'admin_menu', 'tainacan_interface_blocksy_menu' );

/**
 * Style and script of the page, only on it.
 */
function tainacan_interface_blocksy_assets() {
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'tainacan-interface-blocksy-import', $uri . '/assets/css/blocksy-import.css', array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script( 'tainacan-interface-blocksy-import', $uri . '/assets/js/blocksy-import.js', array(), TAINACAN_INTERFACE_VERSION, true );
}

/**
 * Labels of the levels of a row.
 *
 * @return array<string, array{0: string, 1: string}> Level => label, explanation.
 */
function tainacan_interface_blocksy_levels() {
	return array(
		'exact'  => array( __( 'Same', 'tainacan-interface' ), __( 'Comes as it was.', 'tainacan-interface' ) ),
		'approx' => array( __( 'Nearest', 'tainacan-interface' ), __( 'This theme has steps or one value for the site; the nearest was chosen.', 'tainacan-interface' ) ),
		'adjust' => array( __( 'Kept as it was', 'tainacan-interface' ), __( 'An option of this theme that Blocksy did not have, set so the page looks as it did.', 'tainacan-interface' ) ),
		'none'   => array( __( 'No equivalent', 'tainacan-interface' ), __( 'Stays only in Blocksy, which keeps it.', 'tainacan-interface' ) ),
	);
}

/**
 * The steps, on top of every screen of the page.
 *
 * @param int $current Step (1 to 3).
 */
function tainacan_interface_blocksy_steps( $current ) {
	$steps = array(
		1 => __( 'Where from', 'tainacan-interface' ),
		2 => __( 'Check what comes', 'tainacan-interface' ),
		3 => __( 'Done', 'tainacan-interface' ),
	);
	echo '<ol class="tib-steps">';
	foreach ( $steps as $n => $label ) {
		printf(
			'<li class="%1$s"><span class="tib-step-n">%2$d</span> %3$s</li>',
			esc_attr( $n === $current ? 'is-current' : ( $n < $current ? 'is-done' : '' ) ),
			(int) $n,
			esc_html( $label )
		);
	}
	echo '</ol>';
}

/**
 * The page.
 */
function tainacan_interface_blocksy_render() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the page only reads which screen to show; every change goes through admin-post with a nonce.
	$source = isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '';
	$done   = isset( $_GET['done'] );
	$error  = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	echo '<div class="wrap tib">';
	echo '<h1 class="tib-title">' . esc_html__( 'Import from Blocksy', 'tainacan-interface' ) . '</h1>';
	echo '<p class="tib-lead">' . esc_html__( 'Brings the look of a site made with Blocksy (and Tainacan Support for Blocksy) to Tainacan Interface: colors, fonts, header, footer, posts, the Tainacan pages, each collection, menus, widgets, images and Additional CSS. You see everything before anything is written.', 'tainacan-interface' ) . '</p>';

	if ( '' !== $error ) {
		$messages = array(
			'upload'  => __( 'The file did not arrive. Choose it again.', 'tainacan-interface' ),
			'expired' => __( 'The file sent is no longer kept (after two hours it is forgotten). Send it again.', 'tainacan-interface' ),
		);
		$text     = isset( $messages[ $error ] ) ? $messages[ $error ] : (string) get_transient( 'tainacan_interface_blocksy_error_' . get_current_user_id() );
		if ( '' !== $text ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $text ) . '</p></div>';
		}
	}

	if ( $done ) {
		tainacan_interface_blocksy_steps( 3 );
		tainacan_interface_blocksy_render_done();
	} elseif ( '' !== $source ) {
		$data = tainacan_interface_blocksy_source( $source );
		if ( is_wp_error( $data ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $data->get_error_message() ) . '</p></div>';
			tainacan_interface_blocksy_steps( 1 );
			tainacan_interface_blocksy_render_sources();
		} else {
			tainacan_interface_blocksy_steps( 2 );
			tainacan_interface_blocksy_render_plan( $source, $data );
		}
	} else {
		tainacan_interface_blocksy_steps( 1 );
		tainacan_interface_blocksy_render_sources();
	}
	echo '</div>';
}

/**
 * Step 1: the sources.
 */
function tainacan_interface_blocksy_render_sources() {
	$sites   = tainacan_interface_blocksy_site_sources();
	$pending = tainacan_interface_state( 'pending_import' );
	$report  = get_option( 'tainacan_interface_blocksy_report' );
	if ( is_array( $pending ) && is_array( $report ) ) {
		echo '<div class="tib-card tib-last">';
		/* translators: 1: name of the source; 2: date. */
		echo '<p>' . esc_html( sprintf( __( 'Last import: %1$s, on %2$s.', 'tainacan-interface' ), (string) $report['label'], wp_date( get_option( 'date_format' ) . ' H:i', strtotime( (string) $report['date'] ) ) ) ) . '</p>';
		echo '<p><a class="button" href="' . esc_url( tainacan_interface_blocksy_page_url( array( 'done' => 1 ) ) ) . '">' . esc_html__( 'See what was done', 'tainacan-interface' ) . '</a></p>';
		echo '</div>';
	}

	echo '<div class="tib-sources">';
	foreach ( $sites as $slug => $site ) {
		echo '<div class="tib-card tib-source">';
		echo '<span class="dashicons dashicons-database-view" aria-hidden="true"></span>';
		/* translators: %s: name of a theme. */
		echo '<h2>' . esc_html( sprintf( __( '%s on this site', 'tainacan-interface' ), $site['label'] ) ) . '</h2>';
		echo '<p>' . esc_html__( 'The settings this site kept when it used Blocksy. Images, menus and widgets are the ones of this site.', 'tainacan-interface' ) . '</p>';
		echo '<ul class="tib-facts">';
		/* translators: %d: number of settings. */
		echo '<li>' . esc_html( sprintf( _n( '%d setting saved', '%d settings saved', $site['count'], 'tainacan-interface' ), $site['count'] ) ) . '</li>';
		if ( $site['css'] > 0 ) {
			/* translators: %s: size in kB. */
			echo '<li>' . esc_html( sprintf( __( 'Additional CSS of %s kB', 'tainacan-interface' ), number_format_i18n( $site['css'] / 1024, 1 ) ) ) . '</li>';
		}
		echo '</ul>';
		echo '<p><a class="button button-primary button-hero" href="' . esc_url( tainacan_interface_blocksy_page_url( array( 'source' => 'site:' . $slug ) ) ) . '">' . esc_html__( 'Check what comes', 'tainacan-interface' ) . '</a></p>';
		echo '</div>';
	}

	echo '<div class="tib-card tib-source">';
	echo '<span class="dashicons dashicons-upload" aria-hidden="true"></span>';
	echo '<h2>' . esc_html__( 'A file exported from Blocksy', 'tainacan-interface' ) . '</h2>';
	echo '<p>' . esc_html__( 'From this or from another site. The images are downloaded from the other site to the media library of this one.', 'tainacan-interface' ) . '</p>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="tib-upload">';
	echo '<input type="hidden" name="action" value="tainacan_interface_blocksy_upload">';
	wp_nonce_field( 'tainacan_interface_blocksy_upload' );
	echo '<label class="tib-drop"><input type="file" name="blocksy_file" accept=".dat,.json,.txt" required> <span class="tib-drop-text">' . esc_html__( 'Choose the .dat file, or drop it here', 'tainacan-interface' ) . '</span></label>';
	echo '<p><button type="submit" class="button button-primary button-hero">' . esc_html__( 'Send and check what comes', 'tainacan-interface' ) . '</button></p>';
	echo '</form>';
	echo '<details class="tib-howto"><summary>' . esc_html__( 'How to export from Blocksy', 'tainacan-interface' ) . '</summary><ol>';
	echo '<li>' . esc_html__( 'In the site with Blocksy and the Blocksy Companion plugin, open Appearance > Customize.', 'tainacan-interface' ) . '</li>';
	echo '<li>' . esc_html__( 'At the end of the list, open Manage Options (Gerenciar opções) and click Export Customizations.', 'tainacan-interface' ) . '</li>';
	echo '<li>' . esc_html__( 'Check "Customizer settings" and, to bring the widgets too, "Widgets settings". Click Export: a file ending in -export.dat is saved.', 'tainacan-interface' ) . '</li>';
	echo '<li>' . esc_html__( 'Without the Companion, a JSON of the settings also works: wp option get theme_mods_blocksy --format=json > blocksy.json', 'tainacan-interface' ) . '</li>';
	echo '</ol></details>';
	echo '</div>';
	echo '</div>';

	echo '<div class="tib-card tib-promises"><h2>' . esc_html__( 'How the import works', 'tainacan-interface' ) . '</h2><ul>';
	foreach ( array(
		array( 'visibility', __( 'Nothing is written before you confirm. You can also try it in Customize first, without saving.', 'tainacan-interface' ) ),
		array( 'lock', __( 'Blocksy stays as it is: its settings are only read, so going back to it gives the site it was.', 'tainacan-interface' ) ),
		array( 'undo', __( 'One click undoes the import: the settings, the Additional CSS, the widgets and the collections go back to what they were.', 'tainacan-interface' ) ),
		array( 'list-view', __( 'What has no equivalent here is listed, so you know what stays behind.', 'tainacan-interface' ) ),
	) as $promise ) {
		echo '<li><span class="dashicons dashicons-' . esc_attr( $promise[0] ) . '" aria-hidden="true"></span> ' . esc_html( $promise[1] ) . '</li>';
	}
	echo '</ul></div>';
}

/**
 * Prints a color swatch (nothing for a value that is not a hex color).
 *
 * @param string $color Hex.
 */
function tainacan_interface_blocksy_swatch( $color ) {
	$color = sanitize_hex_color( (string) $color );
	if ( $color ) {
		printf( '<span class="tib-swatch" style="background:%s"></span>', esc_attr( $color ) );
	}
}

/**
 * The sketch: a small page before (Blocksy) and after.
 *
 * @param Tainacan_Interface_Blocksy_Map $plan Plan.
 */
function tainacan_interface_blocksy_sketch( $plan ) {
	$b   = $plan->before;
	$set = array();
	foreach ( $plan->set as $values ) {
		$set = array_merge( $set, $values );
	}
	$a            = function ( $key ) use ( $set ) {
		return isset( $set[ 'tainacan_interface_' . $key ] ) ? $set[ 'tainacan_interface_' . $key ] : tainacan_interface_opt( $key );
	};
	$brand        = $a( 'brand_color' );
	$accent       = $a( 'accent_color' );
	$header       = array(
		'brand'  => $brand,
		'accent' => $accent,
		'dark'   => '#16191d',
		'light'  => '#ffffff',
	);
	$header_style = (string) $a( 'header_style' );
	$after_header = isset( $header[ $header_style ] ) ? $header[ $header_style ] : $brand;
	foreach ( $plan->fine as $tokens ) {
		foreach ( $tokens as $token ) {
			if ( preg_match( '/--tt2-header-bg:(#[0-9a-f]{6})/', $token, $m ) ) {
				$after_header = $m[1];
			}
		}
	}
	$maps         = tainacan_interface_value_maps();
	$shape        = (string) $a( 'button_shape' );
	$after_radius = isset( $maps['button_shape'][ $shape ] ) ? $maps['button_shape'][ $shape ] : '8px';
	if ( isset( $b['button_radius'] ) && ! empty( $plan->fine['buttons'] ) ) {
		$after_radius = (int) $b['button_radius'] . 'px';
	}
	$fonts = array(
		'before' => array( isset( $b['font_heading'] ) ? $b['font_heading'] : '', isset( $b['font'] ) ? $b['font'] : '' ),
		'after'  => array( tainacan_interface_font_stack( $a( 'font_heading' ) ), tainacan_interface_font_stack( $a( 'font_ui' ) ) ),
	);
	$looks = array(
		'before' => array(
			'title'   => __( 'In Blocksy', 'tainacan-interface' ),
			'header'  => isset( $b['header'] ) ? $b['header'] : '#ffffff',
			'brand'   => isset( $b['brand'] ) ? $b['brand'] : '',
			'accent'  => isset( $b['accent'] ) ? $b['accent'] : '',
			'text'    => isset( $b['text'] ) ? $b['text'] : '',
			'page'    => isset( $b['page'] ) ? $b['page'] : '',
			'surface' => isset( $b['surface'] ) ? $b['surface'] : '',
			'radius'  => ( isset( $b['radius'] ) ? (int) $b['radius'] : 3 ) . 'px',
			'button'  => ( isset( $b['button_radius'] ) ? (int) $b['button_radius'] : 3 ) . 'px',
			'heading' => '"' . str_replace( '"', '', (string) $fonts['before'][0] ) . '", system-ui, sans-serif',
			'body'    => '"' . str_replace( '"', '', (string) $fonts['before'][1] ) . '", system-ui, sans-serif',
		),
		'after'  => array(
			'title'   => __( 'In Tainacan Interface', 'tainacan-interface' ),
			'header'  => $after_header,
			'brand'   => $brand,
			'accent'  => $accent,
			'text'    => $a( 'text_color' ),
			'page'    => $a( 'page_background' ),
			'surface' => $a( 'surface_color' ),
			'radius'  => (int) $a( 'radius' ) . 'px',
			'button'  => $after_radius,
			'heading' => $fonts['after'][0],
			'body'    => $fonts['after'][1],
		),
	);
	echo '<div class="tib-sketches" aria-hidden="true">';
	foreach ( $looks as $look ) {
		$dark  = tainacan_interface_is_dark_color( sanitize_hex_color( (string) $look['header'] ) ? (string) $look['header'] : '#ffffff' );
		$style = sprintf(
			'--s-header:%1$s;--s-on-header:%2$s;--s-brand:%3$s;--s-accent:%4$s;--s-text:%5$s;--s-page:%6$s;--s-surface:%7$s;--s-radius:%8$s;--s-button:%9$s;--s-heading:%10$s;--s-body:%11$s',
			sanitize_hex_color( (string) $look['header'] ) ? $look['header'] : '#ffffff',
			$dark ? '#ffffff' : '#222222',
			sanitize_hex_color( (string) $look['brand'] ) ? $look['brand'] : '#222222',
			sanitize_hex_color( (string) $look['accent'] ) ? $look['accent'] : '#2872fa',
			sanitize_hex_color( (string) $look['text'] ) ? $look['text'] : '#333333',
			sanitize_hex_color( (string) $look['page'] ) ? $look['page'] : '#ffffff',
			sanitize_hex_color( (string) $look['surface'] ) ? $look['surface'] : '#ffffff',
			preg_replace( '/[^0-9a-z.%]/', '', (string) $look['radius'] ),
			preg_replace( '/[^0-9a-z.%]/', '', (string) $look['button'] ),
			preg_replace( '/[^a-zA-Z0-9 ,"\'\-]/', '', (string) $look['heading'] ),
			preg_replace( '/[^a-zA-Z0-9 ,"\'\-]/', '', (string) $look['body'] )
		);
		echo '<figure class="tib-sketch" style="' . esc_attr( $style ) . '">';
		echo '<figcaption>' . esc_html( $look['title'] ) . '</figcaption>';
		echo '<div class="tib-s-page"><div class="tib-s-header"><span class="tib-s-logo">' . esc_html( get_bloginfo( 'name' ) ) . '</span><span class="tib-s-menu"><i></i><i></i><i></i></span></div>';
		echo '<div class="tib-s-body"><div class="tib-s-h">' . esc_html__( 'Title of a page', 'tainacan-interface' ) . '</div><p class="tib-s-p">' . esc_html__( 'Text of the page, with a', 'tainacan-interface' ) . ' <u class="tib-s-a">' . esc_html__( 'link', 'tainacan-interface' ) . '</u>.</p>';
		echo '<div class="tib-s-cards"><span></span><span></span><span></span></div><span class="tib-s-button">' . esc_html__( 'Button', 'tainacan-interface' ) . '</span></div></div>';
		echo '</figure>';
	}
	echo '</div>';
}

/**
 * Step 2: the plan of a source.
 *
 * @param string $source Source in the request.
 * @param array  $data   Source.
 */
function tainacan_interface_blocksy_render_plan( $source, $data ) {
	$plan   = tainacan_interface_blocksy_plan( $data );
	$levels = tainacan_interface_blocksy_levels();
	$counts = $plan->counts();

	echo '<div class="tib-card tib-summary">';
	echo '<div class="tib-summary-text">';
	if ( 'file' === $data['kind'] ) {
		/* translators: 1: file name; 2: address of a site. */
		echo '<p>' . esc_html( sprintf( __( 'File %1$s, exported from %2$s.', 'tainacan-interface' ), $data['label'], '' !== $data['site_url'] ? $data['site_url'] : __( 'a site that is not named in it', 'tainacan-interface' ) ) ) . '</p>';
		if ( empty( $data['same_site'] ) ) {
			echo '<p>' . esc_html__( 'It comes from another site: images are downloaded; menus and collections are matched only when they exist here.', 'tainacan-interface' ) . '</p>';
		}
		if ( ! empty( $data['repaired'] ) ) {
			echo '<p>' . esc_html__( 'The file had been changed after the export (lengths of text that no longer matched); it was read anyway. Check the texts below.', 'tainacan-interface' ) . '</p>';
		}
	} else {
		/* translators: %s: name of a theme. */
		echo '<p>' . esc_html( sprintf( __( 'Settings of %s on this site.', 'tainacan-interface' ), $data['label'] ) ) . '</p>';
	}
	if ( ! current_user_can( 'edit_css' ) ) {
		echo '<p><strong>' . esc_html__( 'Your account cannot edit the Additional CSS: the CSS of Blocksy and the exact colors of the header and footer stay out.', 'tainacan-interface' ) . '</strong></p>';
	}
	echo '<ul class="tib-counts">';
	foreach ( $levels as $level => $label ) {
		printf( '<li class="tib-level-%1$s" title="%4$s"><strong>%2$d</strong> %3$s</li>', esc_attr( $level ), (int) $counts[ $level ], esc_html( $label[0] ), esc_attr( $label[1] ) );
	}
	echo '</ul></div>';
	tainacan_interface_blocksy_sketch( $plan );
	echo '</div>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="tib-plan">';
	echo '<input type="hidden" name="action" value="tainacan_interface_blocksy_import">';
	echo '<input type="hidden" name="source" value="' . esc_attr( $source ) . '">';
	wp_nonce_field( 'tainacan_interface_blocksy_import' );

	echo '<p class="tib-legend">';
	foreach ( $levels as $level => $label ) {
		echo '<span class="tib-badge tib-level-' . esc_attr( $level ) . '">' . esc_html( $label[0] ) . '</span> ' . esc_html( $label[1] ) . ' ';
	}
	echo '</p>';
	echo '<p class="tib-toggle-all"><button type="button" class="button-link" data-tib-all="1">' . esc_html__( 'Check all', 'tainacan-interface' ) . '</button> · <button type="button" class="button-link" data-tib-all="0">' . esc_html__( 'Uncheck all', 'tainacan-interface' ) . '</button> · <button type="button" class="button-link" data-tib-open="1">' . esc_html__( 'Open every topic', 'tainacan-interface' ) . '</button></p>';

	foreach ( $plan->groups as $id => $group ) {
		$c = $plan->counts( array( $id ) );
		echo '<details class="tib-card tib-group" id="tib-' . esc_attr( $id ) . '"' . ( in_array( $id, array( 'colors', 'header', 'images' ), true ) ? ' open' : '' ) . '>';
		echo '<summary><label class="tib-check"><input type="checkbox" name="groups[]" value="' . esc_attr( $id ) . '" checked> <span class="screen-reader-text">' . esc_html__( 'Bring this topic', 'tainacan-interface' ) . '</span></label>';
		echo '<span class="tib-group-title">' . esc_html( $group['title'] ) . '</span><span class="tib-group-counts">';
		foreach ( $c as $level => $n ) {
			if ( $n ) {
				echo '<span class="tib-badge tib-level-' . esc_attr( $level ) . '">' . (int) $n . '</span>';
			}
		}
		echo '</span></summary>';
		echo '<p class="tib-group-text">' . esc_html( $group['text'] ) . '</p>';
		echo '<table class="widefat striped tib-table"><thead><tr><th>' . esc_html__( 'Setting', 'tainacan-interface' ) . '</th><th>' . esc_html__( 'In Blocksy', 'tainacan-interface' ) . '</th><th>' . esc_html__( 'In Tainacan Interface', 'tainacan-interface' ) . '</th><th></th></tr></thead><tbody>';
		foreach ( $group['rows'] as $row ) {
			echo '<tr><th scope="row">' . esc_html( $row['label'] ) . '</th><td>';
			tainacan_interface_blocksy_swatch( isset( $row['from_color'] ) ? $row['from_color'] : '' );
			echo '<span class="tib-value">' . esc_html( $row['from'] ) . '</span>';
			if ( ! empty( $row['image'] ) ) {
				echo '<img class="tib-thumb" src="' . esc_url( $row['image'] ) . '" alt="" loading="lazy">';
			}
			echo '</td><td>';
			tainacan_interface_blocksy_swatch( isset( $row['to_color'] ) ? $row['to_color'] : '' );
			if ( ! empty( $row['palette'] ) ) {
				foreach ( $row['palette'] as $color ) {
					tainacan_interface_blocksy_swatch( $color );
				}
			}
			echo '<span class="tib-value">' . esc_html( $row['to'] ) . '</span>';
			if ( '' !== $row['note'] ) {
				echo '<span class="tib-note">' . esc_html( $row['note'] ) . '</span>';
			}
			echo '</td><td><span class="tib-badge tib-level-' . esc_attr( $row['level'] ) . '" title="' . esc_attr( $levels[ $row['level'] ][1] ) . '">' . esc_html( $levels[ $row['level'] ][0] ) . '</span></td></tr>';
		}
		echo '</tbody></table></details>';
	}

	if ( $plan->left_out ) {
		$total = 0;
		foreach ( $plan->left_out as $part ) {
			$total += count( $part['keys'] );
		}
		echo '<details class="tib-card tib-left"><summary>';
		/* translators: %d: number of settings. */
		echo esc_html( sprintf( _n( '%d other setting of Blocksy has no place in this theme', '%d other settings of Blocksy have no place in this theme', $total, 'tainacan-interface' ), $total ) );
		echo '</summary><p>' . esc_html__( 'Mostly details of the header and footer builders, values per device, shop options and fine settings of each kind of page. They stay saved in Blocksy.', 'tainacan-interface' ) . '</p>';
		foreach ( $plan->left_out as $part ) {
			echo '<h3>' . esc_html( $part['label'] ) . ' (' . count( $part['keys'] ) . ')</h3><p class="tib-keys">' . esc_html( implode( ', ', $part['keys'] ) ) . '</p>';
		}
		echo '</details>';
	}

	echo '<div class="tib-actions">';
	echo '<a class="button button-large" href="' . esc_url( tainacan_interface_blocksy_page_url() ) . '">' . esc_html__( 'Back', 'tainacan-interface' ) . '</a>';
	echo '<button type="submit" name="mode" value="preview" class="button button-large">' . esc_html__( 'Try it in Customize, without saving', 'tainacan-interface' ) . '</button>';
	echo '<button type="submit" name="mode" value="apply" class="button button-primary button-hero">' . esc_html__( 'Import the topics checked', 'tainacan-interface' ) . '</button>';
	echo '</div>';
	echo '<p class="description">' . esc_html__( 'In Customize you see the site with the colors, fonts, header, footer and Additional CSS of the import and can publish from there; collections, widgets and downloaded images come only with the import.', 'tainacan-interface' ) . '</p>';
	echo '</form>';
}

/**
 * Step 3: what was done.
 */
function tainacan_interface_blocksy_render_done() {
	$report  = get_option( 'tainacan_interface_blocksy_report' );
	$pending = tainacan_interface_state( 'pending_import' );
	if ( ! is_array( $report ) ) {
		echo '<div class="tib-card"><p>' . esc_html__( 'There is no import to show; it may have been undone.', 'tainacan-interface' ) . '</p><p><a class="button button-primary" href="' . esc_url( tainacan_interface_blocksy_page_url() ) . '">' . esc_html__( 'Start an import', 'tainacan-interface' ) . '</a></p></div>';
		return;
	}
	$levels = tainacan_interface_blocksy_levels();
	echo '<div class="tib-card tib-done">';
	echo '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>';
	/* translators: %s: name of the source. */
	echo '<h2>' . esc_html( sprintf( __( 'The look of %s was brought in.', 'tainacan-interface' ), (string) $report['label'] ) ) . '</h2>';
	echo '<ul class="tib-counts">';
	foreach ( $levels as $level => $label ) {
		printf( '<li class="tib-level-%1$s"><strong>%2$d</strong> %3$s</li>', esc_attr( $level ), isset( $report['counts'][ $level ] ) ? (int) $report['counts'][ $level ] : 0, esc_html( $label[0] ) );
	}
	echo '</ul>';
	if ( ! empty( $report['created'] ) ) {
		/* translators: %d: number of images. */
		echo '<p>' . esc_html( sprintf( _n( '%d image was downloaded to the media library.', '%d images were downloaded to the media library.', count( $report['created'] ), 'tainacan-interface' ), count( $report['created'] ) ) ) . '</p>';
	}
	if ( ! empty( $report['failed'] ) ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Some images did not come:', 'tainacan-interface' ) . '</p><ul>';
		foreach ( $report['failed'] as $failed ) {
			echo '<li>' . esc_html( $failed['what'] . ': ' . $failed['why'] ) . '</li>';
		}
		echo '</ul><p>' . esc_html__( 'Send them in Customize (Site identity for the logo, Top banner for the banner).', 'tainacan-interface' ) . '</p></div>';
	}
	if ( ! empty( $report['skipped'] ) ) {
		echo '<p>' . esc_html__( 'Topics left out on purpose:', 'tainacan-interface' ) . ' ' . esc_html( implode( ', ', array_map( 'strval', $report['skipped'] ) ) ) . '</p>';
	}
	echo '<h3>' . esc_html__( 'Next steps', 'tainacan-interface' ) . '</h3><ol class="tib-next">';
	echo '<li>' . esc_html__( 'Look at the home page, a collection, an item and a post.', 'tainacan-interface' ) . ' <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open the site', 'tainacan-interface' ) . '</a></li>';
	echo '<li>' . esc_html__( 'Adjust what you want in Customize: the fonts chosen as the nearest ones, the decorative band, the item preview.', 'tainacan-interface' ) . ' <a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Open Customize', 'tainacan-interface' ) . '</a></li>';
	echo '<li>' . esc_html__( 'Review the Additional CSS that came from Blocksy: rules for its classes can be removed.', 'tainacan-interface' ) . '</li>';
	echo '<li>' . esc_html__( 'Blocksy, Blocksy Companion and Tainacan Support for Blocksy can be deactivated; keep them installed while you compare, to be able to go back.', 'tainacan-interface' ) . '</li>';
	echo '</ol>';
	if ( is_array( $pending ) ) {
		$undo    = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_undo_import' ), 'tainacan_interface_undo_import' );
		$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_dismiss_import_done' ), 'tainacan_interface_dismiss_import_done' );
		echo '<p class="tib-actions"><a class="button button-primary" href="' . esc_url( $dismiss ) . '">' . esc_html__( 'Keep the import', 'tainacan-interface' ) . '</a> <a class="button" href="' . esc_url( $undo ) . '">' . esc_html__( 'Undo the import', 'tainacan-interface' ) . '</a></p>';
		echo '<p class="description">' . esc_html__( 'Undo puts back the settings, the Additional CSS, the widgets and the collections as they were before the import. Downloaded images stay in the media library.', 'tainacan-interface' ) . '</p>';
	}
	echo '</div>';
}

/**
 * Receives the file.
 */
function tainacan_interface_blocksy_upload() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_blocksy_upload' );
	// The file is only read: it is never moved into the site.
	$file = isset( $_FILES['blocksy_file'] ) && is_array( $_FILES['blocksy_file'] ) ? $_FILES['blocksy_file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field is checked below; tmp_name is checked with is_uploaded_file().
	$tmp  = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
	if ( '' === $tmp || ! empty( $file['error'] ) || ! is_uploaded_file( $tmp ) ) {
		wp_safe_redirect( tainacan_interface_blocksy_page_url( array( 'error' => 'upload' ) ) );
		exit;
	}
	$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
	if ( $size > TAINACAN_INTERFACE_BLOCKSY_MAX_FILE ) {
		set_transient( 'tainacan_interface_blocksy_error_' . get_current_user_id(), __( 'The file is larger than 8 MB; an export of Blocksy is much smaller.', 'tainacan-interface' ), 300 );
		wp_safe_redirect( tainacan_interface_blocksy_page_url( array( 'error' => 'file' ) ) );
		exit;
	}
	$text = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local uploaded file, read once; WP_Filesystem would ask for credentials on some hosts for a file PHP already owns.
	$name = isset( $file['name'] ) ? sanitize_file_name( wp_unslash( (string) $file['name'] ) ) : 'blocksy.dat';
	$data = tainacan_interface_blocksy_from_text( false === $text ? '' : $text, $name );
	if ( is_wp_error( $data ) ) {
		set_transient( 'tainacan_interface_blocksy_error_' . get_current_user_id(), $data->get_error_message(), 300 );
		wp_safe_redirect( tainacan_interface_blocksy_page_url( array( 'error' => 'file' ) ) );
		exit;
	}
	set_transient( tainacan_interface_blocksy_upload_key(), $data, 2 * HOUR_IN_SECONDS );
	wp_safe_redirect( tainacan_interface_blocksy_page_url( array( 'source' => 'file' ) ) );
	exit;
}
add_action( 'admin_post_tainacan_interface_blocksy_upload', 'tainacan_interface_blocksy_upload' );

/**
 * Imports, or opens Customize with the import as a draft.
 */
function tainacan_interface_blocksy_import() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_blocksy_import' );
	$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
	$mode   = isset( $_POST['mode'] ) && 'preview' === $_POST['mode'] ? 'preview' : 'apply';
	$groups = isset( $_POST['groups'] ) && is_array( $_POST['groups'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['groups'] ) ) : array();
	$data   = tainacan_interface_blocksy_source( $source );
	if ( is_wp_error( $data ) ) {
		wp_safe_redirect( tainacan_interface_blocksy_page_url( array( 'error' => 'expired' ) ) );
		exit;
	}
	if ( 'preview' === $mode ) {
		$url = tainacan_interface_blocksy_preview( $data, $groups, tainacan_interface_blocksy_page_url( array( 'source' => $source ) ) );
		wp_safe_redirect( is_wp_error( $url ) ? tainacan_interface_blocksy_page_url( array( 'source' => $source ) ) : $url );
		exit;
	}
	tainacan_interface_blocksy_apply( $data, $groups );
	if ( 'file' === $data['kind'] ) {
		delete_transient( tainacan_interface_blocksy_upload_key() );
	}
	wp_safe_redirect( tainacan_interface_blocksy_page_url( array( 'done' => 1 ) ) );
	exit;
}
add_action( 'admin_post_tainacan_interface_blocksy_import', 'tainacan_interface_blocksy_import' );

/**
 * A draft of Customize (a changeset) with the settings of the import, so the
 * administrator sees the site with them and publishes from there, or leaves.
 * Only what Customize knows goes in: theme mods it registers, the logo and the
 * banner already in the media library, the menus and the Additional CSS.
 *
 * @param array    $data   Source.
 * @param string[] $groups Groups.
 * @param string   $back   Where Customize returns to.
 * @return string|WP_Error Address of Customize.
 */
function tainacan_interface_blocksy_preview( $data, $groups, $back ) {
	$plan   = tainacan_interface_blocksy_plan( $data );
	$images = array();
	foreach ( $plan->images as $ref => $image ) {
		$local = tainacan_interface_blocksy_local_image( $image, ! empty( $data['same_site'] ) );
		if ( $local ) {
			$images[ $ref ] = $local;
		}
	}
	$mods = tainacan_interface_blocksy_mods( $plan, array_merge( $groups, array( 'images' ) ), in_array( 'images', $groups, true ) ? $images : array() );

	require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
	$manager = new WP_Customize_Manager();
	do_action( 'customize_register', $manager ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- the core hook, fired to know which settings Customize has.

	$stylesheet = get_stylesheet();
	$now        = gmdate( 'Y-m-d H:i:s' );
	$changes    = array();
	$entry      = function ( $value, $type ) use ( $now ) {
		return array(
			'value'             => $value,
			'type'              => $type,
			'user_id'           => get_current_user_id(),
			'date_modified_gmt' => $now,
		);
	};
	foreach ( $mods as $mod => $value ) {
		if ( 'header_image_data' === $mod ) {
			$value = (array) $value;
		}
		if ( $manager->get_setting( $mod ) ) {
			$changes[ $stylesheet . '::' . $mod ] = $entry( $value, 'theme_mod' );
		}
	}
	if ( in_array( 'menus', $groups, true ) ) {
		foreach ( $plan->locations as $location => $menu_id ) {
			if ( $manager->get_setting( 'nav_menu_locations[' . $location . ']' ) ) {
				$changes[ $stylesheet . '::nav_menu_locations[' . $location . ']' ] = $entry( $menu_id, 'theme_mod' );
			}
		}
	}
	$css = tainacan_interface_blocksy_css( $plan, $images, $groups );
	if ( '' !== $css ) {
		$changes[ 'custom_css[' . $stylesheet . ']' ] = $entry( $css, 'custom_css' );
	}

	$has_kses = false !== has_filter( 'content_save_pre', 'wp_filter_post_kses' );
	if ( $has_kses ) {
		kses_remove_filters(); // The draft is JSON, as Customize saves it; kses would break it.
	}
	$uuid = wp_generate_uuid4();
	$id   = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'customize_changeset',
				'post_status'  => 'auto-draft',
				'post_name'    => $uuid,
				'post_title'   => __( 'Import from Blocksy (preview)', 'tainacan-interface' ),
				'post_author'  => get_current_user_id(),
				'post_content' => wp_json_encode( $changes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
			)
		),
		true
	);
	if ( $has_kses ) {
		kses_init_filters();
	}
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	return add_query_arg(
		array(
			'changeset_uuid' => $uuid,
			'return'         => rawurlencode( $back ),
		),
		admin_url( 'customize.php' )
	);
}

/**
 * The entry in Customize: a section at the end that explains and opens the page.
 *
 * @param WP_Customize_Manager $wp_customize Customize.
 */
function tainacan_interface_blocksy_customize( $wp_customize ) {
	$wp_customize->add_section(
		'tainacan_interface_import_blocksy',
		array(
			'title'       => __( 'Import from Blocksy', 'tainacan-interface' ),
			'description' => __( 'Brings the colors, fonts, header, footer, posts, Tainacan pages, collections, menus, widgets, images and Additional CSS of a site made with Blocksy. You check everything before it is written, and it can be undone.', 'tainacan-interface' ),
			'priority'    => 300,
			'capability'  => 'edit_theme_options',
		)
	);
	// Colors of the palette of Blocksy (content and editor); a setting, so a draft of the import shows them.
	$wp_customize->add_setting(
		'tainacan_interface_blocksy_palette',
		array(
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'default'           => array(),
			'sanitize_callback' => 'tainacan_interface_blocksy_sanitize_palette',
		)
	);
	$wp_customize->add_setting(
		'tainacan_interface_blocksy_link',
		array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'default'           => '',
			'sanitize_callback' => '__return_empty_string',
		)
	);
	require_once __DIR__ . '/class-tainacan-interface-blocksy-link-control.php';
	$wp_customize->add_control(
		new Tainacan_Interface_Blocksy_Link_Control(
			$wp_customize,
			'tainacan_interface_blocksy_link',
			array(
				'section'  => 'tainacan_interface_import_blocksy',
				'settings' => 'tainacan_interface_blocksy_link',
			)
		)
	);
}
add_action( 'customize_register', 'tainacan_interface_blocksy_customize', 30 );

/**
 * Keeps only hex colors, numbered from 1.
 *
 * @param mixed $value Value.
 * @return array<int, string>
 */
function tainacan_interface_blocksy_sanitize_palette( $value ) {
	$clean = array();
	foreach ( is_array( $value ) ? $value : array() as $n => $color ) {
		$color = sanitize_hex_color( (string) $color );
		if ( $color && (int) $n > 0 ) {
			$clean[ (int) $n ] = $color;
		}
	}
	return $clean;
}

/**
 * After an activation that came from Blocksy: the offer.
 */
function tainacan_interface_blocksy_notice() {
	$from = (string) tainacan_interface_state( 'import_blocksy', '' );
	if ( '' === $from || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'appearance_page_' . TAINACAN_INTERFACE_BLOCKSY_PAGE === $screen->id ) {
		return;
	}
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=tainacan_interface_blocksy_dismiss' ), 'tainacan_interface_blocksy_dismiss' );
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'This site was using Blocksy.', 'tainacan-interface' ) . '</strong> ';
	echo esc_html__( 'Tainacan Interface can bring its look: colors, fonts, header, footer, the Tainacan pages, menus, widgets and images. You see what comes before anything changes.', 'tainacan-interface' ) . '</p>';
	echo '<p><a class="button button-primary" href="' . esc_url( tainacan_interface_blocksy_page_url( array( 'source' => 'site:' . $from ) ) ) . '">' . esc_html__( 'Check what comes from Blocksy', 'tainacan-interface' ) . '</a> <a class="button" href="' . esc_url( $dismiss ) . '">' . esc_html__( 'Not now', 'tainacan-interface' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'tainacan_interface_blocksy_notice' );

/**
 * Dismisses the offer (the page stays in Appearance).
 */
function tainacan_interface_blocksy_dismiss() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'tainacan-interface' ), 403 );
	}
	check_admin_referer( 'tainacan_interface_blocksy_dismiss' );
	tainacan_interface_state_set( 'import_blocksy', null );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'themes.php' ) );
	exit;
}
add_action( 'admin_post_tainacan_interface_blocksy_dismiss', 'tainacan_interface_blocksy_dismiss' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Import from Blocksy on the command line.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tainacan-interface blocksy plan --source=site:blocksy
	 *     wp tainacan-interface blocksy plan --file=site-export.dat --format=json
	 *     wp tainacan-interface blocksy apply --source=site:blocksy --groups=colors,typography --user=admin
	 *     wp tainacan-interface blocksy undo --user=admin
	 *
	 * @param array $args       Subcommand: plan, apply or undo.
	 * @param array $assoc_args source, file, groups, format.
	 */
	function tainacan_interface_blocksy_cli( $args, $assoc_args ) {
		$command = isset( $args[0] ) ? $args[0] : 'plan';
		if ( 'undo' === $command ) {
			$pending = tainacan_interface_state( 'pending_import' );
			if ( ! is_array( $pending ) ) {
				WP_CLI::error( 'There is no import to undo.' );
			}
			tainacan_interface_blocksy_undo_parts( $pending );
			if ( isset( $pending['css'] ) && is_string( $pending['css'] ) ) {
				wp_update_custom_css_post( $pending['css'], array( 'stylesheet' => get_stylesheet() ) );
			}
			update_option( 'theme_mods_' . get_stylesheet(), isset( $pending['mods'] ) && is_array( $pending['mods'] ) ? $pending['mods'] : array() );
			tainacan_interface_state_set( 'pending_import', null );
			WP_CLI::success( 'Import undone.' );
			return;
		}
		if ( ! empty( $assoc_args['file'] ) ) {
			$path = (string) $assoc_args['file'];
			if ( ! is_readable( $path ) ) {
				WP_CLI::error( 'Cannot read the file.' );
			}
			$data = tainacan_interface_blocksy_from_text( (string) file_get_contents( $path ), wp_basename( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local file named on the command line.
		} else {
			$data = tainacan_interface_blocksy_source( isset( $assoc_args['source'] ) ? (string) $assoc_args['source'] : 'site:blocksy' );
		}
		if ( is_wp_error( $data ) ) {
			WP_CLI::error( $data->get_error_message() );
		}
		$groups = isset( $assoc_args['groups'] ) ? array_filter( array_map( 'sanitize_key', explode( ',', (string) $assoc_args['groups'] ) ) ) : tainacan_interface_blocksy_group_order();
		if ( 'apply' === $command ) {
			$report = tainacan_interface_blocksy_apply( $data, $groups );
			WP_CLI::print_value( $report, array( 'format' => 'json' ) );
			return;
		}
		$plan = tainacan_interface_blocksy_plan( $data );
		if ( isset( $assoc_args['format'] ) && 'json' === $assoc_args['format'] ) {
			WP_CLI::print_value(
				array(
					'counts'   => $plan->counts(),
					'groups'   => $plan->groups,
					'set'      => $plan->set,
					'meta'     => $plan->meta,
					'fine'     => $plan->fine,
					'images'   => $plan->images,
					'left_out' => $plan->left_out,
				),
				array( 'format' => 'json' )
			);
			return;
		}
		foreach ( $plan->groups as $id => $group ) {
			WP_CLI::log( '== ' . $group['title'] . ' [' . $id . ']' );
			foreach ( $group['rows'] as $row ) {
				WP_CLI::log( sprintf( '  %-7s %s: %s -> %s%s', $row['level'], $row['label'], $row['from'], $row['to'], '' !== $row['note'] ? ' (' . $row['note'] . ')' : '' ) );
			}
		}
		WP_CLI::log( wp_json_encode( $plan->counts() ) );
	}
	WP_CLI::add_command( 'tainacan-interface blocksy', 'tainacan_interface_blocksy_cli' );
}
