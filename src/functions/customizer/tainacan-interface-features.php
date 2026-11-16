<?php
/**
 * Customizer section of the three features this theme adds.
 *
 * @package tainacan-interface
 */

/**
 * Registers the section and its three switches.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function tainacan_interface_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'tainacan_interface_features',
		array(
			'title'       => __( 'Lists and item page', 'tainacan-interface' ),
			'description' => __( 'Features of version 3 for the Tainacan pages. Each one can be turned on or off without affecting the others.', 'tainacan-interface' ),
			'priority'    => 30,
			'capability'  => 'edit_theme_options',
		)
	);

	$switches = array(
		'tainacan_interface_item_drawer' => array(
			__( 'Item preview drawer on the lists', 'tainacan-interface' ),
			__( 'A click on an item of a list opens a side panel with its image, description and metadata, with previous and next. Ctrl+click still opens the item page.', 'tainacan-interface' ),
		),
		'tainacan_interface_item_viewer' => array(
			__( 'File viewer on the item page', 'tainacan-interface' ),
			__( 'Clicking a document or attachment opens a full screen viewer: images with zoom, video and audio, and PDFs in a reader. Replaces the Tainacan lightbox.', 'tainacan-interface' ),
		),
		'tainacan_interface_item_aside'  => array(
			__( 'Show the side column beside the metadata', 'tainacan-interface' ),
			__( 'Related items beside the metadata of the item page. Its options are in "Side column of the item".', 'tainacan-interface' ),
		),
	);

	foreach ( $switches as $id => $texts ) {
		$wp_customize->add_setting(
			$id,
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => true,
				'transport'         => 'refresh',
				'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'type'        => 'checkbox',
				'settings'    => $id,
				'section'     => 'tainacan_interface_features',
				'label'       => $texts[0],
				'description' => $texts[1],
			)
		);
	}

	tainacan_interface_customize_aside( $wp_customize );
}
add_action( 'customize_register', 'tainacan_interface_customize_register', 12 );

/**
 * Sanitizes a choice against the control's own list.
 *
 * @param string               $value   Submitted value.
 * @param WP_Customize_Setting $setting Setting.
 * @return string
 */
function tainacan_interface_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control && is_array( $control->choices ) ? $control->choices : array();
	return is_scalar( $value ) && array_key_exists( (string) $value, $choices ) ? (string) $value : (string) $setting->default;
}

/**
 * Section of the side column of the item page.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function tainacan_interface_customize_aside( $wp_customize ) {
	$wp_customize->add_section(
		'tainacan_interface_aside',
		array(
			'title'       => __( 'Side column of the item', 'tainacan-interface' ),
			'description' => __( 'Beside the metadata of the item page, ways to keep exploring: the items related by a Tainacan relationship metadatum and the items with the same term. On small screens the column comes right after the metadata.', 'tainacan-interface' ),
			'priority'    => 31,
			'capability'  => 'edit_theme_options',
		)
	);

	$fields = array(
		'tainacan_interface_aside_side'          => array(
			'select',
			'right',
			__( 'Side', 'tainacan-interface' ),
			'',
			array(
				'right' => __( 'Right', 'tainacan-interface' ),
				'left'  => __( 'Left', 'tainacan-interface' ),
			),
		),
		'tainacan_interface_aside_title'         => array( 'text', __( 'Keep exploring', 'tainacan-interface' ), __( 'Title of the column', 'tainacan-interface' ), __( 'Leave empty for no title.', 'tainacan-interface' ) ),
		'tainacan_interface_aside_relationships' => array( 'checkbox', true, __( 'Related by relationship metadata', 'tainacan-interface' ), __( 'Items that point to this one (as set in each relationship metadatum, "Display in related items") and items this one points to.', 'tainacan-interface' ) ),
		'tainacan_interface_aside_terms'         => array( 'checkbox', true, __( 'Items with the same term', 'tainacan-interface' ), __( 'The most specific terms of the item first. Choose the taxonomies with "Use to suggest related items" on the metadatum form; with none chosen, all are used.', 'tainacan-interface' ) ),
		'tainacan_interface_aside_collection'    => array( 'checkbox', true, __( 'More from the collection, when there is nothing else', 'tainacan-interface' ), '' ),
		'tainacan_interface_aside_order'         => array(
			'select',
			'relationships',
			__( 'Order', 'tainacan-interface' ),
			'',
			array(
				'relationships' => __( 'Relationships first', 'tainacan-interface' ),
				'terms'         => __( 'Same term first', 'tainacan-interface' ),
			),
		),
		'tainacan_interface_aside_per_thread'    => array(
			'select',
			'6',
			__( 'Items per group', 'tainacan-interface' ),
			'',
			array(
				'3' => '3',
				'6' => '6',
				'9' => '9',
			),
		),
		'tainacan_interface_aside_term_threads'  => array(
			'select',
			'2',
			__( 'Groups of the same term, at most', 'tainacan-interface' ),
			'',
			array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
			),
		),
		'tainacan_interface_aside_sticky'        => array( 'checkbox', true, __( 'Keep the column in view while scrolling', 'tainacan-interface' ), '' ),
		'tainacan_interface_aside_hide_bottom'   => array( 'checkbox', true, __( 'Leave out the "Items related to this" section at the bottom of the page', 'tainacan-interface' ), __( 'Applies when the column shows the relationships, so they do not appear twice.', 'tainacan-interface' ) ),
	);

	foreach ( $fields as $id => $f ) {
		$sanitize = 'checkbox' === $f[0] ? 'tainacan_callback_sanitize_checkbox' : ( 'text' === $f[0] ? 'sanitize_text_field' : 'tainacan_interface_sanitize_choice' );
		$wp_customize->add_setting(
			$id,
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => $f[1],
				'transport'         => 'refresh',
				'sanitize_callback' => $sanitize,
			)
		);
		$control = array(
			'type'        => $f[0],
			'settings'    => $id,
			'section'     => 'tainacan_interface_aside',
			'label'       => $f[2],
			'description' => $f[3],
		);
		if ( isset( $f[4] ) ) {
			$control['choices'] = $f[4];
		}
		$wp_customize->add_control( $id, $control );
	}
}
