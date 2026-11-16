<?php
/**
 * The settings of the design of version 4.
 *
 * One schema feeds the Customizer panel, the "Public pages" fields of the
 * collection form and the values the templates read. A value comes from, in
 * order: the collection (only the options marked for it), the Customizer, the
 * default. Theme mods are named tainacan_interface_<key>; the collection keeps
 * its choice in the post meta tainacan_interface_opt_<key>.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sections of the panel: id => array( title, description ).
 *
 * @return array
 */
function tainacan_interface_settings_sections() {
	return array(
		'look'        => array( __( 'Look and feel', 'tainacan-interface' ), __( 'Colors, fonts and shapes shared by every Tainacan page. Start from a ready-made combination and adjust.', 'tainacan-interface' ) ),
		'site_header' => array( __( 'Site header and footer', 'tainacan-interface' ), '' ),
		'breadcrumb'  => array( __( 'Breadcrumb', 'tainacan-interface' ), '' ),
		'collection'  => array( __( 'Collection header', 'tainacan-interface' ), __( 'The top of the items page of a collection or a term. A collection can set its own header on its form in Tainacan.', 'tainacan-interface' ) ),
		'collections' => array( __( 'List of collections', 'tainacan-interface' ), __( 'The page that lists every collection of the site.', 'tainacan-interface' ) ),
		'cards'       => array( __( 'Item cards and lists', 'tainacan-interface' ), '' ),
		'drawer'      => array( __( 'Item preview', 'tainacan-interface' ), __( 'The side panel opened by a click on an item of a list. The metadata it shows are chosen with "Show in the side drawer" on the form of each metadatum.', 'tainacan-interface' ) ),
		'item'        => array( __( 'Item page', 'tainacan-interface' ), '' ),
	);
}

/**
 * The schema. Each option: section, type (choice, bool, color, range, text,
 * textarea), default, label, help; choices for a choice; min, max and step for a
 * range; collection => true when a collection may set its own value (choice and
 * color options only).
 *
 * @return array
 */
function tainacan_interface_settings_schema() {
	static $schema = null;
	if ( null !== $schema ) {
		return $schema;
	}

	$yes_no_band = array(
		'bands'    => __( 'Four colors, side by side', 'tainacan-interface' ),
		'gradient' => __( 'Gradient of the four colors', 'tainacan-interface' ),
		'accent'   => __( 'Accent color', 'tainacan-interface' ),
		'none'     => __( 'None', 'tainacan-interface' ),
	);
	$fonts       = array(
		'poppins' => 'Poppins',
		'roboto'  => 'Roboto',
		'lora'    => 'Lora',
		'system'  => __( 'Font of the visitor\'s system', 'tainacan-interface' ),
		'georgia' => 'Georgia',
	);

	$schema = array(
		// Look and feel.
		'design'                => array(
			'section' => 'look',
			'type'    => 'choice',
			'default' => 'modern',
			'label'   => __( 'Page design', 'tainacan-interface' ),
			'help'    => __( 'Modern: the header, lists, preview and item page of version 4. Classic: the pages as they were up to version 3. The item preview, the file viewer and the side column have their own switches, in "Lists and item page".', 'tainacan-interface' ),
			'choices' => array(
				'modern'  => __( 'Modern', 'tainacan-interface' ),
				'classic' => __( 'Classic', 'tainacan-interface' ),
			),
		),
		'brand_color'           => array(
			'section'    => 'look',
			'type'       => 'color',
			'default'    => '#1c355e',
			'label'      => __( 'Main color', 'tainacan-interface' ),
			'help'       => __( 'Titles, current breadcrumb, site header in color.', 'tainacan-interface' ),
			'collection' => true,
		),
		'accent_color'          => array(
			'section'    => 'look',
			'type'       => 'color',
			'default'    => '#5b4fcf',
			'label'      => __( 'Accent color', 'tainacan-interface' ),
			'help'       => __( 'Links, card titles, filters, buttons.', 'tainacan-interface' ),
			'collection' => true,
		),
		'band_style'            => array(
			'section' => 'look',
			'type'    => 'choice',
			'default' => 'bands',
			'label'   => __( 'Decorative band', 'tainacan-interface' ),
			'help'    => __( 'The thin line under the site header and the banner, and beside the description.', 'tainacan-interface' ),
			'choices' => $yes_no_band,
		),
		'band_1'                => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#f5333f',
			'label'   => __( 'Band: first color', 'tainacan-interface' ),
		),
		'band_2'                => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#f8961e',
			'label'   => __( 'Band: second color', 'tainacan-interface' ),
		),
		'band_3'                => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#43aa8b',
			'label'   => __( 'Band: third color', 'tainacan-interface' ),
		),
		'band_4'                => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#5bc0be',
			'label'   => __( 'Band: fourth color', 'tainacan-interface' ),
		),
		'page_background'       => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#f8f9fb',
			'label'   => __( 'Page background', 'tainacan-interface' ),
		),
		'surface_color'         => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#ffffff',
			'label'   => __( 'Background of cards and panels', 'tainacan-interface' ),
		),
		'text_color'            => array(
			'section' => 'look',
			'type'    => 'color',
			'default' => '#2b2f36',
			'label'   => __( 'Text color', 'tainacan-interface' ),
		),
		'font_ui'               => array(
			'section' => 'look',
			'type'    => 'choice',
			'default' => 'poppins',
			'label'   => __( 'Font of the interface', 'tainacan-interface' ),
			'help'    => __( 'All the fonts offered here come with the theme or with the visitor\'s system; nothing is loaded from another server.', 'tainacan-interface' ),
			'choices' => $fonts,
		),
		'font_heading'          => array(
			'section' => 'look',
			'type'    => 'choice',
			'default' => 'poppins',
			'label'   => __( 'Font of the titles', 'tainacan-interface' ),
			'choices' => $fonts,
		),
		'font_reading'          => array(
			'section' => 'look',
			'type'    => 'choice',
			'default' => 'lora',
			'label'   => __( 'Font of long texts', 'tainacan-interface' ),
			'help'    => __( 'Descriptions of collections and items.', 'tainacan-interface' ),
			'choices' => $fonts,
		),
		'font_size'             => array(
			'section' => 'look',
			'type'    => 'range',
			'default' => 16,
			'min'     => 14,
			'max'     => 20,
			'step'    => 1,
			'label'   => __( 'Base text size (px)', 'tainacan-interface' ),
		),
		'radius'                => array(
			'section' => 'look',
			'type'    => 'range',
			'default' => 14,
			'min'     => 0,
			'max'     => 32,
			'step'    => 1,
			'label'   => __( 'Roundness of corners (px)', 'tainacan-interface' ),
		),
		'shadow'                => array(
			'section' => 'look',
			'type'    => 'choice',
			'default' => 'soft',
			'label'   => __( 'Shadows', 'tainacan-interface' ),
			'choices' => array(
				'none' => __( 'None', 'tainacan-interface' ),
				'soft' => __( 'Soft', 'tainacan-interface' ),
				'deep' => __( 'Deep', 'tainacan-interface' ),
			),
		),
		'page_width'            => array(
			'section' => 'look',
			'type'    => 'range',
			'default' => 1500,
			'min'     => 1000,
			'max'     => 2200,
			'step'    => 20,
			'label'   => __( 'Largest width of the content (px)', 'tainacan-interface' ),
		),

		// Site header and footer.
		'header_style'          => array(
			'section' => 'site_header',
			'type'    => 'choice',
			'default' => 'brand',
			'label'   => __( 'Site header', 'tainacan-interface' ),
			'choices' => array(
				'brand' => __( 'In the main color', 'tainacan-interface' ),
				'light' => __( 'Light', 'tainacan-interface' ),
			),
		),
		'header_stack'          => array(
			'section' => 'site_header',
			'type'    => 'choice',
			'default' => 'stacked',
			'label'   => __( 'Logo and menu', 'tainacan-interface' ),
			'choices' => array(
				'stacked' => __( 'Menu below the logo', 'tainacan-interface' ),
				'inline'  => __( 'On the same line', 'tainacan-interface' ),
			),
		),
		'header_band'           => array(
			'section' => 'site_header',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Decorative band under the header', 'tainacan-interface' ),
		),
		'header_sticky'         => array(
			'section' => 'site_header',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'Keep the header in view while scrolling', 'tainacan-interface' ),
		),
		'footer_style'          => array(
			'section' => 'site_header',
			'type'    => 'choice',
			'default' => 'compact',
			'label'   => __( 'Footer', 'tainacan-interface' ),
			'help'    => __( 'Compact leaves out the footer widgets. The text is the one set in "Footer".', 'tainacan-interface' ),
			'choices' => array(
				'compact' => __( 'Compact, in the main color', 'tainacan-interface' ),
				'classic' => __( 'With widgets', 'tainacan-interface' ),
			),
		),

		// Breadcrumb.
		'crumb_style'           => array(
			'section' => 'breadcrumb',
			'type'    => 'choice',
			'default' => 'pills',
			'label'   => __( 'Style', 'tainacan-interface' ),
			'choices' => array(
				'pills' => __( 'Capsule, with the current page highlighted', 'tainacan-interface' ),
				'plain' => __( 'Plain text with arrows', 'tainacan-interface' ),
				'none'  => __( 'Hidden', 'tainacan-interface' ),
			),
		),
		'crumb_home_icon'       => array(
			'section' => 'breadcrumb',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Icon on the first step', 'tainacan-interface' ),
		),
		'crumb_item_collection' => array(
			'section' => 'breadcrumb',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'On the item page, start at the home page', 'tainacan-interface' ),
			'help'    => __( 'Off: the item page shows only the collection and the item.', 'tainacan-interface' ),
		),

		// Collection header.
		'head_layout'           => array(
			'section'    => 'collection',
			'type'       => 'choice',
			'default'    => 'center',
			'label'      => __( 'Layout', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'center'  => __( 'Centered, the icon on the edge of the banner', 'tainacan-interface' ),
				'left'    => __( 'Along the left edge', 'tainacan-interface' ),
				'overlay' => __( 'Title over the banner', 'tainacan-interface' ),
				'compact' => __( 'Compact: icon beside the title, no banner', 'tainacan-interface' ),
			),
		),
		'head_banner'           => array(
			'section'    => 'collection',
			'type'       => 'choice',
			'default'    => 'auto',
			'label'      => __( 'Banner', 'tainacan-interface' ),
			'help'       => __( 'The mosaic is put together from the images of the items, so a collection without a header image still has a face.', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'auto'   => __( 'Header image; a mosaic of items when there is none', 'tainacan-interface' ),
				'image'  => __( 'Only the header image', 'tainacan-interface' ),
				'mosaic' => __( 'Always the mosaic of items', 'tainacan-interface' ),
				'none'   => __( 'No banner', 'tainacan-interface' ),
			),
		),
		'head_banner_width'     => array(
			'section' => 'collection',
			'type'    => 'choice',
			'default' => 'contained',
			'label'   => __( 'Width of the banner', 'tainacan-interface' ),
			'choices' => array(
				'contained' => __( 'Inside the content, with round corners', 'tainacan-interface' ),
				'full'      => __( 'The whole window', 'tainacan-interface' ),
			),
		),
		'head_banner_height'    => array(
			'section' => 'collection',
			'type'    => 'range',
			'default' => 240,
			'min'     => 100,
			'max'     => 560,
			'step'    => 10,
			'label'   => __( 'Height of the banner (px)', 'tainacan-interface' ),
		),
		'head_banner_veil'      => array(
			'section' => 'collection',
			'type'    => 'range',
			'default' => 35,
			'min'     => 0,
			'max'     => 85,
			'step'    => 5,
			'label'   => __( 'Veil over the banner (%)', 'tainacan-interface' ),
			'help'    => __( 'A layer in the main color, so the banner does not compete with the title.', 'tainacan-interface' ),
		),
		'head_icon'             => array(
			'section'    => 'collection',
			'type'       => 'choice',
			'default'    => 'tilted',
			'label'      => __( 'Collection icon', 'tainacan-interface' ),
			'help'       => __( 'The thumbnail of the collection; without one, a mosaic of items.', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'tilted' => __( 'Tilted card', 'tainacan-interface' ),
				'square' => __( 'Rounded square', 'tainacan-interface' ),
				'circle' => __( 'Circle', 'tainacan-interface' ),
				'none'   => __( 'No icon', 'tainacan-interface' ),
			),
		),
		'head_icon_size'        => array(
			'section' => 'collection',
			'type'    => 'range',
			'default' => 120,
			'min'     => 56,
			'max'     => 220,
			'step'    => 4,
			'label'   => __( 'Size of the icon (px)', 'tainacan-interface' ),
		),
		'head_eyebrow'          => array(
			'section' => 'collection',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Label "Collection" above the title', 'tainacan-interface' ),
		),
		'head_rule'             => array(
			'section' => 'collection',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Short band under the title', 'tainacan-interface' ),
		),
		'head_description'      => array(
			'section'    => 'collection',
			'type'       => 'choice',
			'default'    => 'panel',
			'label'      => __( 'Description', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'panel'  => __( 'In a panel', 'tainacan-interface' ),
				'plain'  => __( 'Plain text', 'tainacan-interface' ),
				'hidden' => __( 'Hidden', 'tainacan-interface' ),
			),
		),
		'head_count'            => array(
			'section' => 'collection',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Number of items', 'tainacan-interface' ),
		),
		'head_surprise'         => array(
			'section' => 'collection',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( '"Surprise me" button', 'tainacan-interface' ),
			'help'    => __( 'Opens the preview of a random item of the page. Needs the item preview on.', 'tainacan-interface' ),
		),
		'head_share'            => array(
			'section' => 'collection',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Share links', 'tainacan-interface' ),
		),

		// List of collections.
		'list_heading'          => array(
			'section' => 'collections',
			'type'    => 'text',
			'default' => '',
			'label'   => __( 'Title of the page', 'tainacan-interface' ),
			'help'    => __( 'Empty: "Collections".', 'tainacan-interface' ),
		),
		'list_intro'            => array(
			'section' => 'collections',
			'type'    => 'textarea',
			'default' => '',
			'label'   => __( 'Text under the title', 'tainacan-interface' ),
		),
		'list_style'            => array(
			'section' => 'collections',
			'type'    => 'choice',
			'default' => 'card',
			'label'   => __( 'Card', 'tainacan-interface' ),
			'choices' => array(
				'card'    => __( 'Image above, text below', 'tainacan-interface' ),
				'overlay' => __( 'Text over the image', 'tainacan-interface' ),
				'row'     => __( 'Image beside the text', 'tainacan-interface' ),
			),
		),
		'list_columns'          => array(
			'section' => 'collections',
			'type'    => 'choice',
			'default' => '4',
			'label'   => __( 'Columns on a large screen', 'tainacan-interface' ),
			'choices' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
			),
		),
		'list_ratio'            => array(
			'section' => 'collections',
			'type'    => 'choice',
			'default' => '4/3',
			'label'   => __( 'Proportion of the image', 'tainacan-interface' ),
			'choices' => array(
				'4/3'  => '4:3',
				'3/2'  => '3:2',
				'16/9' => '16:9',
				'1/1'  => '1:1',
			),
		),
		'list_image'            => array(
			'section' => 'collections',
			'type'    => 'choice',
			'default' => 'auto',
			'label'   => __( 'Image of the card', 'tainacan-interface' ),
			'choices' => array(
				'auto'      => __( 'Thumbnail; a mosaic of items when there is none', 'tainacan-interface' ),
				'thumbnail' => __( 'Only the thumbnail', 'tainacan-interface' ),
				'mosaic'    => __( 'Always the mosaic of items', 'tainacan-interface' ),
			),
		),
		'list_order'            => array(
			'section' => 'collections',
			'type'    => 'choice',
			'default' => 'menu_order',
			'label'   => __( 'Order', 'tainacan-interface' ),
			'choices' => array(
				'menu_order' => __( 'Order set for the collections, then newest', 'tainacan-interface' ),
				'title'      => __( 'Title', 'tainacan-interface' ),
				'date'       => __( 'Newest first', 'tainacan-interface' ),
				'items'      => __( 'Most items first', 'tainacan-interface' ),
			),
		),
		'list_description'      => array(
			'section' => 'collections',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Show the description', 'tainacan-interface' ),
		),
		'list_words'            => array(
			'section' => 'collections',
			'type'    => 'range',
			'default' => 28,
			'min'     => 8,
			'max'     => 80,
			'step'    => 2,
			'label'   => __( 'Words of the description', 'tainacan-interface' ),
		),
		'list_count'            => array(
			'section' => 'collections',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Show the number of items', 'tainacan-interface' ),
		),
		'list_search'           => array(
			'section' => 'collections',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Field to find a collection', 'tainacan-interface' ),
			'help'    => __( 'Filters the cards while typing. Shown from 7 collections on.', 'tainacan-interface' ),
		),

		// Item cards and lists.
		'card_accent'           => array(
			'section' => 'cards',
			'type'    => 'choice',
			'default' => 'side',
			'label'   => __( 'Accent of the card title', 'tainacan-interface' ),
			'choices' => array(
				'side' => __( 'Bar on the left', 'tainacan-interface' ),
				'top'  => __( 'Line on top of the card', 'tainacan-interface' ),
				'none' => __( 'None', 'tainacan-interface' ),
			),
		),
		'card_title_lines'      => array(
			'section' => 'cards',
			'type'    => 'choice',
			'default' => '1',
			'label'   => __( 'Lines of the card title', 'tainacan-interface' ),
			'choices' => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
			),
		),
		'card_hover'            => array(
			'section' => 'cards',
			'type'    => 'choice',
			'default' => 'lift',
			'label'   => __( 'When the pointer is over a card', 'tainacan-interface' ),
			'choices' => array(
				'lift' => __( 'Rise a little', 'tainacan-interface' ),
				'ring' => __( 'Ring in the accent color', 'tainacan-interface' ),
			),
		),
		'list_full_width'       => array(
			'section' => 'cards',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Lists in the largest width of the content', 'tainacan-interface' ),
		),

		// Item preview.
		'drawer_mode'           => array(
			'section'    => 'drawer',
			'type'       => 'choice',
			'default'    => 'overlay',
			'label'      => __( 'How it opens', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'overlay' => __( 'Over the list, which keeps its size', 'tainacan-interface' ),
				'push'    => __( 'Beside the list, which makes room', 'tainacan-interface' ),
				'off'     => __( 'Do not open: a click goes to the item page', 'tainacan-interface' ),
			),
		),
		'drawer_side'           => array(
			'section' => 'drawer',
			'type'    => 'choice',
			'default' => 'right',
			'label'   => __( 'Side', 'tainacan-interface' ),
			'choices' => array(
				'right' => __( 'Right', 'tainacan-interface' ),
				'left'  => __( 'Left', 'tainacan-interface' ),
			),
		),
		'drawer_width'          => array(
			'section' => 'drawer',
			'type'    => 'range',
			'default' => 480,
			'min'     => 340,
			'max'     => 820,
			'step'    => 10,
			'label'   => __( 'Width (px)', 'tainacan-interface' ),
		),
		'drawer_backdrop'       => array(
			'section' => 'drawer',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'Darken the page behind', 'tainacan-interface' ),
		),
		'drawer_media'          => array(
			'section' => 'drawer',
			'type'    => 'choice',
			'default' => 'contain',
			'label'   => __( 'Image', 'tainacan-interface' ),
			'choices' => array(
				'contain' => __( 'Whole image', 'tainacan-interface' ),
				'cover'   => __( 'Filling the box', 'tainacan-interface' ),
				'none'    => __( 'No image', 'tainacan-interface' ),
			),
		),
		'drawer_meta_line'      => array(
			'section' => 'drawer',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Line with the collection, the kind of document and the number of files', 'tainacan-interface' ),
		),
		'drawer_description'    => array(
			'section' => 'drawer',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Description', 'tainacan-interface' ),
		),
		'drawer_max_fields'     => array(
			'section' => 'drawer',
			'type'    => 'range',
			'default' => 12,
			'min'     => 0,
			'max'     => 30,
			'step'    => 1,
			'label'   => __( 'Metadata at most', 'tainacan-interface' ),
		),
		'drawer_open_label'     => array(
			'section' => 'drawer',
			'type'    => 'text',
			'default' => '',
			'label'   => __( 'Text of the button that opens the item', 'tainacan-interface' ),
			'help'    => __( 'Empty: "Open item".', 'tainacan-interface' ),
		),
		'drawer_open_new'       => array(
			'section' => 'drawer',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'Open the item in a new tab', 'tainacan-interface' ),
		),

		// Item page.
		'item_layout'           => array(
			'section'    => 'item',
			'type'       => 'choice',
			'default'    => 'stacked',
			'label'      => __( 'Layout', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'stacked'     => __( 'Document above, record below', 'tainacan-interface' ),
				'media-left'  => __( 'Document on the left, record on the right', 'tainacan-interface' ),
				'media-right' => __( 'Record on the left, document on the right', 'tainacan-interface' ),
			),
		),
		'item_subtitle'         => array(
			'section' => 'item',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Line under the title with the date and the first terms', 'tainacan-interface' ),
		),
		'item_chips'            => array(
			'section' => 'item',
			'type'    => 'choice',
			'default' => 'colored',
			'label'   => __( 'Short values of the record', 'tainacan-interface' ),
			'choices' => array(
				'colored' => __( 'Capsules in four tints', 'tainacan-interface' ),
				'mono'    => __( 'Capsules in the accent color', 'tainacan-interface' ),
				'plain'   => __( 'Plain text', 'tainacan-interface' ),
			),
		),
		'item_labels'           => array(
			'section' => 'item',
			'type'    => 'choice',
			'default' => 'side',
			'label'   => __( 'Names of the metadata', 'tainacan-interface' ),
			'choices' => array(
				'side'  => __( 'Beside the value', 'tainacan-interface' ),
				'above' => __( 'Above the value', 'tainacan-interface' ),
			),
		),
		'item_columns'          => array(
			'section' => 'item',
			'type'    => 'choice',
			'default' => '1',
			'label'   => __( 'Columns of metadata', 'tainacan-interface' ),
			'choices' => array(
				'1' => '1',
				'2' => '2',
			),
		),
		'item_sections'         => array(
			'section'    => 'item',
			'type'       => 'choice',
			'default'    => 'first',
			'label'      => __( 'Metadata sections, in panels that open and close', 'tainacan-interface' ),
			'help'       => __( 'Used when the collection keeps the "Default" sections layout.', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'first' => __( 'The first one open', 'tainacan-interface' ),
				'all'   => __( 'All open', 'tainacan-interface' ),
				'none'  => __( 'All closed', 'tainacan-interface' ),
			),
		),
		'item_citation'         => array(
			'section' => 'item',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( '"How to cite" panel', 'tainacan-interface' ),
			'help'    => __( 'ABNT, APA, Vancouver, BibTeX and RIS, with a copy button.', 'tainacan-interface' ),
		),
		'item_frame'            => array(
			'section' => 'item',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Document in a frame', 'tainacan-interface' ),
		),
	);

	/**
	 * The schema of the design settings. Options added here appear in the
	 * Customizer panel and answer to tainacan_interface_opt().
	 *
	 * @param array $schema Options.
	 */
	$schema = (array) apply_filters( 'tainacan_interface_settings_schema', $schema );

	return $schema;
}

/**
 * Normalizes a value to what its option accepts.
 *
 * @param array $option Option of the schema.
 * @param mixed $value  Raw value.
 * @return mixed
 */
function tainacan_interface_opt_normalize( $option, $value ) {
	switch ( $option['type'] ) {
		case 'bool':
			return (bool) $value;
		case 'color':
			$color = is_string( $value ) ? sanitize_hex_color( $value ) : '';
			return $color ? $color : $option['default'];
		case 'range':
			return min( (int) $option['max'], max( (int) $option['min'], (int) $value ) );
		case 'choice':
			return is_scalar( $value ) && array_key_exists( (string) $value, $option['choices'] ) ? (string) $value : $option['default'];
		case 'textarea':
			return is_string( $value ) ? sanitize_textarea_field( $value ) : '';
		default:
			return is_string( $value ) ? sanitize_text_field( $value ) : '';
	}
}

/**
 * The collection of the page being shown: a list of items or an item.
 *
 * @return int Collection id, 0 elsewhere.
 */
function tainacan_interface_opt_context() {
	if ( is_admin() || ! function_exists( 'tainacan_get_collection_id' ) || ! did_action( 'wp' ) ) {
		return 0;
	}
	return (int) tainacan_get_collection_id();
}

/**
 * Value of a design setting.
 *
 * @param string   $key           Key in the schema.
 * @param int|null $collection_id Collection; null for the one of the current page.
 * @return mixed
 */
function tainacan_interface_opt( $key, $collection_id = null ) {
	$schema = tainacan_interface_settings_schema();
	if ( ! isset( $schema[ $key ] ) ) {
		return null;
	}
	$option = $schema[ $key ];

	$value = get_theme_mod( 'tainacan_interface_' . $key, $option['default'] );

	if ( ! empty( $option['collection'] ) ) {
		$collection_id = null === $collection_id ? tainacan_interface_opt_context() : (int) $collection_id;
		if ( $collection_id > 0 ) {
			$own = get_post_meta( $collection_id, 'tainacan_interface_opt_' . $key, true );
			if ( '' !== $own && false !== $own ) {
				$value = $own;
			} elseif ( 'brand_color' === $key ) {
				// The header color a collection chose for the classic design, when it can carry the titles too.
				$legacy = sanitize_hex_color( (string) get_post_meta( $collection_id, 'tainacan_theme_collection_background_color', true ) );
				if ( $legacy && tainacan_interface_is_dark_color( $legacy ) ) {
					$value = $legacy;
				}
			}
		}
	}

	$value = tainacan_interface_opt_normalize( $option, $value );

	/**
	 * Value of a design setting, after the collection and the Customizer.
	 *
	 * @param mixed  $value Value.
	 * @param string $key   Key in the schema.
	 */
	return apply_filters( 'tainacan_interface_opt', $value, $key );
}

/**
 * Whether the pages use the design of version 4. A site updated from an earlier
 * version stays on classic until an administrator chooses (inc/upgrade.php).
 *
 * @return bool
 */
function tainacan_interface_is_modern() {
	/**
	 * Turns the design of version 4 on or off.
	 *
	 * @param bool $modern Value of the Customizer option.
	 */
	return (bool) apply_filters( 'tainacan_interface_modern_enabled', 'modern' === tainacan_interface_opt( 'design' ) );
}

/**
 * Ready-made combinations of the "Look and feel" section.
 *
 * @return array Slug => label and values.
 */
function tainacan_interface_presets() {
	$presets = array(
		'observatory' => array(
			'label'  => __( 'Observatory (navy and violet)', 'tainacan-interface' ),
			'values' => array(
				'brand_color'     => '#1c355e',
				'accent_color'    => '#5b4fcf',
				'band_1'          => '#f5333f',
				'band_2'          => '#f8961e',
				'band_3'          => '#43aa8b',
				'band_4'          => '#5bc0be',
				'page_background' => '#f8f9fb',
				'surface_color'   => '#ffffff',
				'text_color'      => '#2b2f36',
			),
		),
		'museum'      => array(
			'label'  => __( 'Museum (wine and gold)', 'tainacan-interface' ),
			'values' => array(
				'brand_color'     => '#4a1525',
				'accent_color'    => '#8c442c',
				'band_1'          => '#8c1c34',
				'band_2'          => '#b5812a',
				'band_3'          => '#d9b46a',
				'band_4'          => '#6b3f2a',
				'page_background' => '#faf7f2',
				'surface_color'   => '#ffffff',
				'text_color'      => '#2f2723',
			),
		),
		'archive'     => array(
			'label'  => __( 'Archive (sepia and graphite)', 'tainacan-interface' ),
			'values' => array(
				'brand_color'     => '#33383d',
				'accent_color'    => '#754e24',
				'band_1'          => '#754e24',
				'band_2'          => '#a07c4f',
				'band_3'          => '#c9b08a',
				'band_4'          => '#5e666d',
				'page_background' => '#f6f3ec',
				'surface_color'   => '#fffdf9',
				'text_color'      => '#2e2b26',
			),
		),
		'nature'      => array(
			'label'  => __( 'Nature (forest and turquoise)', 'tainacan-interface' ),
			'values' => array(
				'brand_color'     => '#14433a',
				'accent_color'    => '#187181',
				'band_1'          => '#255f56',
				'band_2'          => '#4f9d69',
				'band_3'          => '#a3c15f',
				'band_4'          => '#2cb4c1',
				'page_background' => '#f4f8f5',
				'surface_color'   => '#ffffff',
				'text_color'      => '#22302c',
			),
		),
		'contrast'    => array(
			'label'  => __( 'High contrast', 'tainacan-interface' ),
			'values' => array(
				'brand_color'     => '#000000',
				'accent_color'    => '#0039a6',
				'band_1'          => '#000000',
				'band_2'          => '#0039a6',
				'band_3'          => '#000000',
				'band_4'          => '#0039a6',
				'page_background' => '#ffffff',
				'surface_color'   => '#ffffff',
				'text_color'      => '#000000',
			),
		),
	);

	/**
	 * Ready-made combinations of the "Look and feel" section.
	 *
	 * @param array $presets Slug => array( label, values ).
	 */
	return (array) apply_filters( 'tainacan_interface_presets', $presets );
}

/**
 * Customizer panel, built from the schema.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function tainacan_interface_settings_customize( $wp_customize ) {
	$wp_customize->add_panel(
		'tainacan_interface',
		array(
			'title'       => __( 'Tainacan Interface', 'tainacan-interface' ),
			'description' => __( 'Everything about the look of the Tainacan pages in one place. The options of the item page and of the search area stay in their own panels.', 'tainacan-interface' ),
			'priority'    => 20,
			'capability'  => 'edit_theme_options',
		)
	);

	$priority = 10;
	foreach ( tainacan_interface_settings_sections() as $id => $section ) {
		$wp_customize->add_section(
			'tainacan_interface_opt_' . $id,
			array(
				'title'       => $section[0],
				'description' => $section[1],
				'panel'       => 'tainacan_interface',
				'priority'    => $priority,
				'capability'  => 'edit_theme_options',
			)
		);
		$priority += 10;
	}

	// Sections of version 3, now in the same panel.
	foreach ( array( 'tainacan_interface_features', 'tainacan_interface_aside' ) as $id ) {
		$section = $wp_customize->get_section( $id );
		if ( $section ) {
			$section->panel    = 'tainacan_interface';
			$section->priority = $priority;
			$priority         += 10;
		}
	}

	// Not stored: its script writes the colors of the combination into the controls.
	$wp_customize->add_setting(
		'tainacan_interface_preset_picker',
		array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'default'           => '',
			'transport'         => 'postMessage',
			'sanitize_callback' => '__return_empty_string',
		)
	);
	$preset_choices = array( '' => __( 'Choose a combination…', 'tainacan-interface' ) );
	foreach ( tainacan_interface_presets() as $slug => $preset ) {
		$preset_choices[ $slug ] = $preset['label'];
	}
	$wp_customize->add_control(
		'tainacan_interface_preset_picker',
		array(
			'type'        => 'select',
			'section'     => 'tainacan_interface_opt_look',
			'label'       => __( 'Ready-made colors', 'tainacan-interface' ),
			'description' => __( 'Fills in the colors below; publish to keep them.', 'tainacan-interface' ),
			'choices'     => $preset_choices,
			'priority'    => 1,
		)
	);

	$order = 2;
	$live  = tainacan_interface_live_options();
	foreach ( tainacan_interface_settings_schema() as $key => $option ) {
		$id = 'tainacan_interface_' . $key;
		$wp_customize->add_setting(
			$id,
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => $option['default'],
				'transport'         => isset( $live[ $key ] ) ? 'postMessage' : 'refresh',
				'sanitize_callback' => 'tainacan_interface_opt_sanitize_setting',
			)
		);

		$control = array(
			'settings'    => $id,
			'section'     => 'tainacan_interface_opt_' . $option['section'],
			'label'       => $option['label'],
			'description' => isset( $option['help'] ) ? $option['help'] : '',
			'priority'    => $order++,
		);

		if ( 'color' === $option['type'] ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $control ) );
			continue;
		}

		switch ( $option['type'] ) {
			case 'bool':
				$control['type'] = 'checkbox';
				break;
			case 'choice':
				$control['type']    = 'select';
				$control['choices'] = $option['choices'];
				break;
			case 'range':
				$control['type']        = 'number';
				$control['input_attrs'] = array(
					'min'  => $option['min'],
					'max'  => $option['max'],
					'step' => $option['step'],
				);
				break;
			default:
				$control['type'] = $option['type'];
		}
		$wp_customize->add_control( $id, $control );
	}
}
add_action( 'customize_register', 'tainacan_interface_settings_customize', 20 );

/**
 * Sanitizes a Customizer value by the option it belongs to.
 *
 * @param mixed                $value   Submitted value.
 * @param WP_Customize_Setting $setting Setting.
 * @return mixed
 */
function tainacan_interface_opt_sanitize_setting( $value, $setting ) {
	$schema = tainacan_interface_settings_schema();
	$key    = substr( $setting->id, strlen( 'tainacan_interface_' ) );
	return isset( $schema[ $key ] ) ? tainacan_interface_opt_normalize( $schema[ $key ], $value ) : '';
}

/**
 * Options a collection may set for itself.
 *
 * @return array
 */
function tainacan_interface_collection_options() {
	return array_filter(
		tainacan_interface_settings_schema(),
		function ( $option ) {
			return ! empty( $option['collection'] ) && in_array( $option['type'], array( 'choice', 'color' ), true );
		}
	);
}

/**
 * Registers the fields of the collection form.
 */
function tainacan_interface_collection_options_register() {
	if ( function_exists( 'tainacan_register_admin_hook' ) ) {
		tainacan_register_admin_hook( 'collection', 'tainacan_interface_collection_options_form', 'end-right' );
	}
}
add_action( 'tainacan-register-admin-hooks', 'tainacan_interface_collection_options_register' );

/**
 * Declares the meta, so the form is filled with the saved values.
 *
 * @param array $extra_meta Meta keys declared by others.
 * @return array
 */
function tainacan_interface_collection_options_expose( $extra_meta ) {
	$extra_meta = is_array( $extra_meta ) ? $extra_meta : array();
	foreach ( array_keys( tainacan_interface_collection_options() ) as $key ) {
		$extra_meta[] = 'tainacan_interface_opt_' . $key;
	}
	return $extra_meta;
}
add_filter( 'tainacan-api-response-collection-meta', 'tainacan_interface_collection_options_expose' );

/**
 * The fields. Tainacan prints the returned HTML in its Vue form (v-html), so the
 * callback returns it and carries no script.
 *
 * @return string
 */
function tainacan_interface_collection_options_form() {
	ob_start();
	?>
	<div class="field tainacan-collection--section-header">
		<h4><?php esc_html_e( 'Public pages of this collection', 'tainacan-interface' ); ?></h4>
		<p class="help"><?php esc_html_e( 'Tainacan Interface. Leave a field on "As the site" to follow Appearance > Customize.', 'tainacan-interface' ); ?></p>
		<hr>
	</div>
	<?php
	foreach ( tainacan_interface_collection_options() as $key => $option ) :
		$name = 'tainacan_interface_opt_' . $key;
		?>
		<div class="field">
			<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $option['label'] ); ?></label>
			<div class="control is-clearfix">
				<?php if ( 'color' === $option['type'] ) : ?>
					<input class="input" type="text" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'As the site (or a color such as #1c355e)', 'tainacan-interface' ); ?>" pattern="#[0-9a-fA-F]{3,6}">
				<?php else : ?>
					<span class="select">
						<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
							<option value=""><?php esc_html_e( 'As the site', 'tainacan-interface' ); ?></option>
							<?php foreach ( $option['choices'] as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	endforeach;
	return (string) ob_get_clean();
}

/**
 * Saves the fields with the collection.
 *
 * @param \Tainacan\Entities\Collection $collection Collection being saved.
 */
function tainacan_interface_collection_options_save( $collection ) {
	if ( ! function_exists( 'tainacan_get_api_postdata' ) || ! $collection instanceof \Tainacan\Entities\Collection || ! $collection->can_edit() ) {
		return;
	}

	$posted = tainacan_get_api_postdata();
	if ( ! is_object( $posted ) ) {
		return;
	}

	foreach ( tainacan_interface_collection_options() as $key => $option ) {
		$name = 'tainacan_interface_opt_' . $key;
		if ( ! isset( $posted->{$name} ) || ! is_scalar( $posted->{$name} ) ) {
			continue;
		}
		$raw = trim( (string) $posted->{$name} );
		if ( '' === $raw ) {
			delete_post_meta( (int) $collection->get_id(), $name );
			continue;
		}
		$valid = 'color' === $option['type'] ? (bool) sanitize_hex_color( $raw ) : array_key_exists( $raw, $option['choices'] );
		if ( $valid ) {
			update_post_meta( (int) $collection->get_id(), $name, $raw );
		}
	}
}
add_action( 'tainacan-insert-tainacan-collection', 'tainacan_interface_collection_options_save' );
