<?php
/**
 * Edit shortcuts (the pencil) of the Customizer preview for the areas of the
 * theme: a click on the pencil opens the option that shapes that area.
 *
 * Each shortcut is a partial bound to a setting that never changes, so it
 * never re-renders anything; the pencil opens the control named as its
 * primary setting. The options keep their own refresh or live preview.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Areas of the page => option opened by the pencil.
 *
 * @return array<string, array{0: string, 1: string}> id => (CSS selector, option key).
 */
function tainacan_interface_customizer_shortcuts() {
	return (array) apply_filters(
		'tainacan_interface_customizer_shortcuts',
		array(
			'header'      => array( '#topNavbar', 'header_style' ),
			'band'        => array( '.tt2-site-band', 'band_style' ),
			'crumbs'      => array( '.tt2-crumbs', 'crumb_style' ),
			'page_title'  => array( '.tt2-page-head', 'page_title_style' ),
			'coll_head'   => array( '.tt2-head', 'head_layout' ),
			'coll_list'   => array( '.tt2-collections-page__head', 'list_heading' ),
			'coll_cards'  => array( '.tt2-collections', 'list_style' ),
			'coll_tax'    => array( '.tt2-taxonomies', 'list_taxonomies' ),
			'item_doc'    => array( '.single-item-collection--document', 'item_layout' ),
			'item_meta'   => array( '.s-item-collection--metadata', 'item_label_size' ),
			'item_cite'   => array( '.tt2-cite', 'item_citation' ),
			'item_share'  => array( '.tt2-share__links', 'head_share' ),
			'social'      => array( '.tt2-social', 'social_place' ),
			'footer'      => array( '.tainacan-footer', 'footer_style' ),
			'footer_note' => array( '.tt2-footer-note', 'footer_text' ),
		)
	);
}

/**
 * Registers the shortcuts.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function tainacan_interface_register_customizer_shortcuts( $wp_customize ) {
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		return;
	}

	// Anchor of every shortcut: never shown, never saved, never changes.
	$wp_customize->add_setting(
		'tainacan_interface_shortcut_anchor',
		array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'default'           => '',
			'transport'         => 'postMessage',
			'sanitize_callback' => '__return_empty_string',
		)
	);

	foreach ( tainacan_interface_customizer_shortcuts() as $id => $shortcut ) {
		$target = 'tainacan_interface_' . $shortcut[1];
		if ( ! $wp_customize->get_control( $target ) ) {
			continue;
		}
		$wp_customize->selective_refresh->add_partial(
			'tainacan_interface_shortcut_' . $id,
			array(
				'selector'            => $shortcut[0],
				'settings'            => array( 'tainacan_interface_shortcut_anchor' ),
				'primary_setting'     => $target,
				'container_inclusive' => true,
				'fallback_refresh'    => false,
				'render_callback'     => '__return_false',
			)
		);
	}
}
add_action( 'customize_register', 'tainacan_interface_register_customizer_shortcuts', 30 );
