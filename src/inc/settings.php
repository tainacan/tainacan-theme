<?php
/**
 * The settings of the new design.
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
 * Panels of the Customizer, one per topic: id => array( title, line under the
 * title in the menu, dashicon ).
 *
 * @return array
 */
function tainacan_interface_settings_panels() {
	return array(
		'style'  => array( __( 'Style of the site', 'tainacan-interface' ), __( 'Colors, fonts, shapes and buttons of every page', 'tainacan-interface' ), 'art' ),
		'header' => array( __( 'Header, menu and footer', 'tainacan-interface' ), __( 'Top and bottom of every page, search, breadcrumb and social networks', 'tainacan-interface' ), 'menu-alt3' ),
		'lists'  => array( __( 'Collections and item lists', 'tainacan-interface' ), __( 'The collections page, the collection header, cards, filters and the item preview', 'tainacan-interface' ), 'grid-view' ),
		'item'   => array( __( 'Item page', 'tainacan-interface' ), __( 'Layout, metadata, document, side column and related items', 'tainacan-interface' ), 'media-document' ),
		'blog'   => array( __( 'Posts and pages', 'tainacan-interface' ), __( 'Top banner, page titles, list of posts, a post and the sidebar', 'tainacan-interface' ), 'admin-post' ),
	);
}

/**
 * Sections of the Customizer. Each: panel, title, line under the title in the
 * menu (sub), text at the top of the open section (help) and the kind of page
 * the preview goes to when it opens (preview: collections, collection, item,
 * blog or '').
 *
 * @return array Id => array( panel, title, sub, help, preview ).
 */
function tainacan_interface_settings_sections() {
	$s = function ( $panel, $title, $sub, $help = '', $preview = '' ) {
		return compact( 'panel', 'title', 'sub', 'help', 'preview' );
	};
	return array(
		'look'          => $s( 'style', __( 'Colors', 'tainacan-interface' ), __( 'Main and accent colors, decorative band, backgrounds', 'tainacan-interface' ), __( 'Start from a ready-made combination and adjust. A collection can choose its own colors on its form in Tainacan.', 'tainacan-interface' ) ),
		'typography'    => $s( 'style', __( 'Typography', 'tainacan-interface' ), __( 'Fonts, sizes and weights of text, titles and menu', 'tainacan-interface' ) ),
		'layout'        => $s( 'style', __( 'Layout, shapes and spacing', 'tainacan-interface' ), __( 'Width of the content, margins, corners and shadows', 'tainacan-interface' ) ),
		'buttons'       => $s( 'style', __( 'Buttons, fields and links', 'tainacan-interface' ), __( 'Shape and style of buttons, text fields and links', 'tainacan-interface' ), __( 'The buttons, search fields and links of the theme and of the blocks. The controls of the Tainacan lists keep their own look.', 'tainacan-interface' ) ),
		'extras'        => $s( 'style', __( 'Back to top and performance', 'tainacan-interface' ), __( 'Button to go back to the top, lighter pages', 'tainacan-interface' ) ),
		'site_header'   => $s( 'header', __( 'Site header', 'tainacan-interface' ), __( 'Color, logo, menu, button and behavior on scroll', 'tainacan-interface' ), __( 'The logo itself is in Site Identity; the items of the menu, in Menus.', 'tainacan-interface' ) ),
		'header_search' => $s( 'header', __( 'Search in the header', 'tainacan-interface' ), __( 'Search field of the header and what it looks for', 'tainacan-interface' ) ),
		'breadcrumb'    => $s( 'header', __( 'Breadcrumb', 'tainacan-interface' ), __( 'The path "Home > Collection > Item" above the title', 'tainacan-interface' ) ),
		'footer'        => $s( 'header', __( 'Footer', 'tainacan-interface' ), __( 'Style, contact, logo and the line at the end', 'tainacan-interface' ), __( 'The links of the footer are in Menus (location "Footer menu").', 'tainacan-interface' ) ),
		'social'        => $s( 'header', __( 'Profiles of the institution', 'tainacan-interface' ), __( 'Links to Instagram, YouTube and other profiles', 'tainacan-interface' ), __( 'Icons that link to the profiles of the institution (follow us), in the header or the footer. They appear once at least one address below is filled in.', 'tainacan-interface' ) ),
		'share'         => $s( 'header', __( 'Share buttons', 'tainacan-interface' ), __( 'Networks offered to share a page', 'tainacan-interface' ), __( 'Which networks the share links of items, collections, posts and pages offer. Where the links appear is chosen in each kind of page.', 'tainacan-interface' ) ),
		'collections'   => $s( 'lists', __( 'List of collections', 'tainacan-interface' ), __( 'The page that lists every collection', 'tainacan-interface' ), '', 'collections' ),
		'collection'    => $s( 'lists', __( 'Collection header', 'tainacan-interface' ), __( 'Banner, icon, title and description of a collection', 'tainacan-interface' ), __( 'The top of the items page of a collection or a term. A collection can set its own header on its form in Tainacan.', 'tainacan-interface' ), 'collection' ),
		'cards'         => $s( 'lists', __( 'Item cards', 'tainacan-interface' ), __( 'Look of the items in the lists', 'tainacan-interface' ), '', 'collection' ),
		'items_search'  => $s( 'lists', __( 'Search and view modes', 'tainacan-interface' ), __( 'Search bar of the list, sorting and view modes', 'tainacan-interface' ), __( 'The bar above the items of a collection, a term or the repository.', 'tainacan-interface' ), 'collection' ),
		'filters'       => $s( 'lists', __( 'Filters', 'tainacan-interface' ), __( 'Where the filters panel stays and how it opens', 'tainacan-interface' ), '', 'collection' ),
		'pagination'    => $s( 'lists', __( 'Pagination', 'tainacan-interface' ), __( 'Items per page and page buttons', 'tainacan-interface' ), '', 'collection' ),
		'drawer'        => $s( 'lists', __( 'Item preview', 'tainacan-interface' ), __( 'Side panel opened by a click on an item', 'tainacan-interface' ), __( 'The metadata it shows are chosen with "Show in the side drawer" on the form of each metadatum.', 'tainacan-interface' ), 'collection' ),
		'item'          => $s( 'item', __( 'Layout and metadata', 'tainacan-interface' ), __( 'Position of the document, look of the metadata, sections', 'tainacan-interface' ), '', 'item' ),
		'document'      => $s( 'item', __( 'Document and attachments', 'tainacan-interface' ), __( 'Viewer, gallery and file buttons', 'tainacan-interface' ), '', 'item' ),
		'aside'         => $s( 'item', __( 'Side column', 'tainacan-interface' ), __( 'Ways to keep exploring beside the metadata', 'tainacan-interface' ), __( 'Beside the metadata of the item page: the items related by a Tainacan relationship metadatum and the items with the same term. On small screens the column comes right after the metadata.', 'tainacan-interface' ), 'item' ),
		'related'       => $s( 'item', __( 'Related items', 'tainacan-interface' ), __( 'Section at the end of the page', 'tainacan-interface' ), '', 'item' ),
		'item_nav'      => $s( 'item', __( 'Previous and next item', 'tainacan-interface' ), __( 'Links to the neighbor items of the list', 'tainacan-interface' ), '', 'item' ),
		'hero'          => $s( 'blog', __( 'Top banner', 'tainacan-interface' ), __( 'Image and title at the top of posts and pages', 'tainacan-interface' ), '', 'blog' ),
		'page_title'    => $s( 'blog', __( 'Page titles', 'tainacan-interface' ), __( 'Title of lists, searches and archives', 'tainacan-interface' ), '', 'blog' ),
		'blog'          => $s( 'blog', __( 'List of posts', 'tainacan-interface' ), __( 'Cards or list, columns and summary', 'tainacan-interface' ), '', 'blog' ),
		'post'          => $s( 'blog', __( 'A post or page', 'tainacan-interface' ), __( 'Width of the text and what goes around it', 'tainacan-interface' ) ),
		'sidebar'       => $s( 'blog', __( 'Sidebar', 'tainacan-interface' ), __( 'Side column of the blog', 'tainacan-interface' ), __( 'Its contents are in Widgets.', 'tainacan-interface' ), 'blog' ),
	);
}

/**
 * The schema. Each option: section, type (choice, bool, color, range, text,
 * textarea, url), default, label, help; choices for a choice; min, max and step for a
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
	// Built before init, the labels would stay in English for the whole
	// request: the theme loads its translations on init.
	$cache = did_action( 'init' ) > 0;

	$yes_no_band = array(
		'bands'    => __( 'Four colors, side by side', 'tainacan-interface' ),
		'gradient' => __( 'Gradient of the four colors', 'tainacan-interface' ),
		'accent'   => __( 'Accent color', 'tainacan-interface' ),
		'none'     => __( 'None', 'tainacan-interface' ),
	);
	// Sans serif first, then serif; the names of the fonts are not translated.
	$fonts       = array(
		'poppins'      => 'Poppins',
		'roboto'       => 'Roboto',
		'inter'        => 'Inter',
		'open-sans'    => 'Open Sans',
		'montserrat'   => 'Montserrat',
		'system'       => __( 'Font of the visitor\'s system', 'tainacan-interface' ),
		'lora'         => 'Lora',
		'source-serif' => 'Source Serif',
		'merriweather' => 'Merriweather',
		'playfair'     => 'Playfair Display',
		'georgia'      => 'Georgia',
	);
	$weight_help = __( 'A weight the chosen font does not have falls to the nearest one it has.', 'tainacan-interface' );

	$schema = array(
		// Look and feel.
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
		'font_size'             => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 16,
			'min'     => 14,
			'max'     => 20,
			'step'    => 1,
			'label'   => __( 'Base text size (px)', 'tainacan-interface' ),
		),
		'radius'                => array(
			'section' => 'layout',
			'type'    => 'range',
			'default' => 14,
			'min'     => 0,
			'max'     => 32,
			'step'    => 1,
			'label'   => __( 'Roundness of corners (px)', 'tainacan-interface' ),
		),
		'shadow'                => array(
			'section' => 'layout',
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
			'section' => 'layout',
			'type'    => 'range',
			'default' => 1500,
			'min'     => 1000,
			'max'     => 2200,
			'step'    => 20,
			'label'   => __( 'Largest width of the content (px)', 'tainacan-interface' ),
		),

		// Typography.
		'font_ui'               => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => 'poppins',
			'label'   => __( 'Font of the body text', 'tainacan-interface' ),
			'help'    => __( 'All the fonts offered here come with the theme or with the visitor\'s system; nothing is loaded from another server.', 'tainacan-interface' ),
			'choices' => $fonts,
		),
		'body_weight'           => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => '400',
			'label'   => __( 'Weight of the body text', 'tainacan-interface' ),
			'help'    => $weight_help,
			'choices' => array(
				'300' => __( 'Light', 'tainacan-interface' ),
				'400' => __( 'Regular', 'tainacan-interface' ),
				'500' => __( 'Medium', 'tainacan-interface' ),
				'600' => __( 'Semibold', 'tainacan-interface' ),
			),
		),
		'font_reading'          => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => 'lora',
			'label'   => __( 'Font of long texts', 'tainacan-interface' ),
			'help'    => __( 'Descriptions of collections and items.', 'tainacan-interface' ),
			'choices' => $fonts,
		),
		'font_heading'          => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => 'poppins',
			'label'   => __( 'Font of the titles', 'tainacan-interface' ),
			'choices' => $fonts,
		),
		'heading_weight'        => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => '700',
			'label'   => __( 'Weight of the titles', 'tainacan-interface' ),
			'help'    => $weight_help,
			'choices' => array(
				'400' => __( 'Regular', 'tainacan-interface' ),
				'500' => __( 'Medium', 'tainacan-interface' ),
				'600' => __( 'Semibold', 'tainacan-interface' ),
				'700' => __( 'Bold', 'tainacan-interface' ),
				'800' => __( 'Extra bold', 'tainacan-interface' ),
				'900' => __( 'Black', 'tainacan-interface' ),
			),
		),
		'heading_scale'         => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 100,
			'min'     => 80,
			'max'     => 130,
			'step'    => 5,
			'label'   => __( 'Size of the titles (%)', 'tainacan-interface' ),
		),
		'h1_size'               => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 44,
			'min'     => 24,
			'max'     => 72,
			'step'    => 1,
			/* translators: %d: level of the title, 1 to 6. */
			'label'   => sprintf( __( 'Size of the H%d titles (px)', 'tainacan-interface' ), 1 ),
			'help'    => __( 'Each level of title, in the text of pages and posts; H1 is also the big title of every page, which shrinks on phones. "Size of the titles (%)" above still scales them all.', 'tainacan-interface' ),
		),
		'h2_size'               => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 28,
			'min'     => 18,
			'max'     => 48,
			'step'    => 1,
			/* translators: %d: level of the title, 1 to 6. */
			'label'   => sprintf( __( 'Size of the H%d titles (px)', 'tainacan-interface' ), 2 ),
		),
		'h3_size'               => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 22,
			'min'     => 14,
			'max'     => 36,
			'step'    => 1,
			/* translators: %d: level of the title, 1 to 6. */
			'label'   => sprintf( __( 'Size of the H%d titles (px)', 'tainacan-interface' ), 3 ),
		),
		'h4_size'               => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 19,
			'min'     => 13,
			'max'     => 30,
			'step'    => 1,
			/* translators: %d: level of the title, 1 to 6. */
			'label'   => sprintf( __( 'Size of the H%d titles (px)', 'tainacan-interface' ), 4 ),
		),
		'h5_size'               => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 17,
			'min'     => 12,
			'max'     => 26,
			'step'    => 1,
			/* translators: %d: level of the title, 1 to 6. */
			'label'   => sprintf( __( 'Size of the H%d titles (px)', 'tainacan-interface' ), 5 ),
		),
		'h6_size'               => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 15,
			'min'     => 11,
			'max'     => 22,
			'step'    => 1,
			/* translators: %d: level of the title, 1 to 6. */
			'label'   => sprintf( __( 'Size of the H%d titles (px)', 'tainacan-interface' ), 6 ),
		),
		'heading_spacing'       => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => -2,
			'min'     => -5,
			'max'     => 15,
			'step'    => 1,
			'label'   => __( 'Letter spacing of the titles (hundredths of the size)', 'tainacan-interface' ),
		),
		'heading_transform'     => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => 'none',
			'label'   => __( 'Letters of the titles', 'tainacan-interface' ),
			'choices' => array(
				'none'      => __( 'As written', 'tainacan-interface' ),
				'uppercase' => __( 'Capitals', 'tainacan-interface' ),
			),
		),
		'heading_color'         => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => 'brand',
			'label'   => __( 'Color of the titles', 'tainacan-interface' ),
			'choices' => array(
				'brand'  => __( 'Main color', 'tainacan-interface' ),
				'accent' => __( 'Accent color', 'tainacan-interface' ),
				'text'   => __( 'Text color', 'tainacan-interface' ),
			),
		),
		'line_height'           => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 150,
			'min'     => 130,
			'max'     => 200,
			'step'    => 5,
			'label'   => __( 'Line height of the text (%)', 'tainacan-interface' ),
		),
		'font_size_mobile'      => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 16,
			'min'     => 13,
			'max'     => 20,
			'step'    => 1,
			'label'   => __( 'Base text size on phones (px)', 'tainacan-interface' ),
		),
		'menu_size'             => array(
			'section' => 'typography',
			'type'    => 'range',
			'default' => 15,
			'min'     => 12,
			'max'     => 20,
			'step'    => 1,
			'label'   => __( 'Size of the menu (px)', 'tainacan-interface' ),
		),
		'menu_transform'        => array(
			'section' => 'typography',
			'type'    => 'choice',
			'default' => 'none',
			'label'   => __( 'Letters of the menu', 'tainacan-interface' ),
			'choices' => array(
				'none'      => __( 'As written', 'tainacan-interface' ),
				'uppercase' => __( 'Capitals', 'tainacan-interface' ),
			),
		),

		// Layout and spacing.
		'edge_spacing'          => array(
			'section' => 'layout',
			'type'    => 'range',
			'default' => 40,
			'min'     => 8,
			'max'     => 80,
			'step'    => 4,
			'label'   => __( 'Space at the sides of the page (px)', 'tainacan-interface' ),
			'help'    => __( 'On large screens; phones keep a smaller one.', 'tainacan-interface' ),
		),
		'content_spacing'       => array(
			'section' => 'layout',
			'type'    => 'choice',
			'default' => 'normal',
			'label'   => __( 'Space between the parts of a page', 'tainacan-interface' ),
			'choices' => array(
				'compact'  => __( 'Compact', 'tainacan-interface' ),
				'normal'   => __( 'Normal', 'tainacan-interface' ),
				'spacious' => __( 'Spacious', 'tainacan-interface' ),
			),
		),
		'frame_size'            => array(
			'section' => 'layout',
			'type'    => 'range',
			'default' => 0,
			'min'     => 0,
			'max'     => 32,
			'step'    => 2,
			'label'   => __( 'Frame around the site (px)', 'tainacan-interface' ),
			'help'    => __( 'A border in the main color around the window. 0: none.', 'tainacan-interface' ),
		),

		// Buttons, fields and links.
		'button_shape'          => array(
			'section' => 'buttons',
			'type'    => 'choice',
			'default' => 'rounded',
			'label'   => __( 'Shape of the buttons', 'tainacan-interface' ),
			'choices' => array(
				'rounded' => __( 'Rounded corners', 'tainacan-interface' ),
				'pill'    => __( 'Capsule', 'tainacan-interface' ),
				'square'  => __( 'Square corners', 'tainacan-interface' ),
			),
		),
		'button_style'          => array(
			'section' => 'buttons',
			'type'    => 'choice',
			'default' => 'filled',
			'label'   => __( 'Style of the buttons', 'tainacan-interface' ),
			'choices' => array(
				'filled'  => __( 'Filled with the accent color', 'tainacan-interface' ),
				'outline' => __( 'Outline in the accent color', 'tainacan-interface' ),
			),
		),
		'button_transform'      => array(
			'section' => 'buttons',
			'type'    => 'choice',
			'default' => 'none',
			'label'   => __( 'Letters of the buttons', 'tainacan-interface' ),
			'choices' => array(
				'none'      => __( 'As written', 'tainacan-interface' ),
				'uppercase' => __( 'Capitals', 'tainacan-interface' ),
			),
		),
		'field_style'           => array(
			'section' => 'buttons',
			'type'    => 'choice',
			'default' => 'outlined',
			'label'   => __( 'Text fields', 'tainacan-interface' ),
			'choices' => array(
				'outlined'  => __( 'With a border', 'tainacan-interface' ),
				'filled'    => __( 'Filled, no border', 'tainacan-interface' ),
				'underline' => __( 'Only a line below', 'tainacan-interface' ),
			),
		),
		'link_style'            => array(
			'section' => 'buttons',
			'type'    => 'choice',
			'default' => 'underline',
			'label'   => __( 'Links in the text of posts and pages', 'tainacan-interface' ),
			'choices' => array(
				'underline' => __( 'Always underlined', 'tainacan-interface' ),
				'hover'     => __( 'Underlined under the pointer', 'tainacan-interface' ),
				'none'      => __( 'Never underlined', 'tainacan-interface' ),
			),
		),
		'selection'             => array(
			'section' => 'buttons',
			'type'    => 'choice',
			'default' => 'accent',
			'label'   => __( 'Selected text', 'tainacan-interface' ),
			'choices' => array(
				'accent'  => __( 'In the accent color', 'tainacan-interface' ),
				'brand'   => __( 'In the main color', 'tainacan-interface' ),
				'browser' => __( 'As the browser shows it', 'tainacan-interface' ),
			),
		),

		// Site header and footer.
		'header_style'          => array(
			'section' => 'site_header',
			'type'    => 'choice',
			'default' => 'brand',
			'label'   => __( 'Site header', 'tainacan-interface' ),
			'choices' => array(
				'brand'  => __( 'In the main color', 'tainacan-interface' ),
				'accent' => __( 'In the accent color', 'tainacan-interface' ),
				'dark'   => __( 'Dark', 'tainacan-interface' ),
				'light'  => __( 'Light', 'tainacan-interface' ),
			),
		),
		'header_padding'        => array(
			'section' => 'site_header',
			'type'    => 'range',
			'default' => 14,
			'min'     => 0,
			'max'     => 40,
			'step'    => 2,
			'label'   => __( 'Height of the header: space above and below (px)', 'tainacan-interface' ),
		),
		'menu_indicator'        => array(
			'section' => 'site_header',
			'type'    => 'choice',
			'default' => 'underline',
			'label'   => __( 'Current menu item and pointer over the menu', 'tainacan-interface' ),
			'choices' => array(
				'underline' => __( 'Line below', 'tainacan-interface' ),
				'pill'      => __( 'Capsule', 'tainacan-interface' ),
				'none'      => __( 'Nothing', 'tainacan-interface' ),
			),
		),
		'header_button_label'   => array(
			'section' => 'site_header',
			'type'    => 'text',
			'default' => '',
			'label'   => __( 'Button in the header: text', 'tainacan-interface' ),
			'help'    => __( 'A highlighted link beside the menu, such as "Visit" or "Contact". Empty: no button.', 'tainacan-interface' ),
		),
		'header_button_url'     => array(
			'section' => 'site_header',
			'type'    => 'url',
			'default' => '',
			'label'   => __( 'Button in the header: address', 'tainacan-interface' ),
		),
		'mobile_menu'           => array(
			'section' => 'site_header',
			'type'    => 'choice',
			'default' => 'dropdown',
			'label'   => __( 'Menu on phones', 'tainacan-interface' ),
			'choices' => array(
				'dropdown' => __( 'Opens below the header', 'tainacan-interface' ),
				'side'     => __( 'Panel from the side', 'tainacan-interface' ),
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
			'section' => 'footer',
			'type'    => 'choice',
			'default' => 'compact',
			'label'   => __( 'Footer', 'tainacan-interface' ),
			'help'    => __( 'Compact leaves out the footer widgets. The text is the one set in "Footer".', 'tainacan-interface' ),
			'choices' => array(
				'compact' => __( 'Compact, in the main color', 'tainacan-interface' ),
				'classic' => __( 'With widgets', 'tainacan-interface' ),
			),
		),
		'footer_tone'           => array(
			'section' => 'footer',
			'type'    => 'choice',
			'default' => 'brand',
			'label'   => __( 'Color of the compact footer', 'tainacan-interface' ),
			'choices' => array(
				'brand' => __( 'Main color', 'tainacan-interface' ),
				'dark'  => __( 'Dark', 'tainacan-interface' ),
				'light' => __( 'Light', 'tainacan-interface' ),
			),
		),
		'footer_text'           => array(
			'section' => 'footer',
			'type'    => 'textarea',
			'default' => '',
			'label'   => __( 'Line at the end of the footer', 'tainacan-interface' ),
			'help'    => __( 'For example "© {year} {site}. Some rights reserved." {year} and {site} become the year and the name of the site. The footer menu is chosen in Appearance > Menus.', 'tainacan-interface' ),
		),
		'social_place'          => array(
			'section' => 'social',
			'type'    => 'choice',
			'default' => 'footer',
			'label'   => __( 'Where the links appear', 'tainacan-interface' ),
			'choices' => array(
				'footer' => __( 'Footer', 'tainacan-interface' ),
				'header' => __( 'Header', 'tainacan-interface' ),
				'both'   => __( 'Header and footer', 'tainacan-interface' ),
				'none'   => __( 'Nowhere', 'tainacan-interface' ),
			),
		),
		'social_new_tab'        => array(
			'section' => 'social',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Open in a new tab', 'tainacan-interface' ),
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
		'crumb_separator'       => array(
			'section' => 'breadcrumb',
			'type'    => 'choice',
			'default' => 'chevron',
			'label'   => __( 'Separator', 'tainacan-interface' ),
			'choices' => array(
				'chevron' => __( 'Arrow', 'tainacan-interface' ),
				'slash'   => __( 'Slash', 'tainacan-interface' ),
				'dot'     => __( 'Dot', 'tainacan-interface' ),
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
		'head_menu'             => array(
			'section'    => 'collection',
			'type'       => 'choice',
			'default'    => 'show',
			'label'      => __( 'Menu of the collection below the header', 'tainacan-interface' ),
			'help'       => __( 'The menu saved in the location of the collection, in Appearance > Menus. A collection can turn it off on its form.', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'show' => __( 'Show', 'tainacan-interface' ),
				'hide' => __( 'Hide', 'tainacan-interface' ),
			),
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
			'help'    => __( 'Only changes collections without a thumbnail of their own (and "Always the mosaic", which changes all).', 'tainacan-interface' ),
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
		'list_menu'             => array(
			'section' => 'collections',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Menu of the page below the title', 'tainacan-interface' ),
			'help'    => __( 'The menu saved in the location "List of collections", in Appearance > Menus.', 'tainacan-interface' ),
		),
		'list_search'           => array(
			'section' => 'collections',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Field to find a collection', 'tainacan-interface' ),
			'help'    => __( 'A field above the cards that filters them while typing. Shown when there are two or more collections.', 'tainacan-interface' ),
		),

		'list_taxonomies'       => array(
			'section' => 'collections',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Taxonomies below the collections', 'tainacan-interface' ),
			'help'    => __( 'The public taxonomies of the site, each with a link to its page of terms.', 'tainacan-interface' ),
		),
		'list_taxonomies_heading' => array(
			'section' => 'collections',
			'type'    => 'text',
			'default' => '',
			'label'   => __( 'Title of the taxonomies', 'tainacan-interface' ),
			'help'    => __( 'Empty: "Taxonomies".', 'tainacan-interface' ),
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
			'help'       => __( 'Where the document (image, file, video) and the record (metadata) go. In the side-by-side layouts, phones show first what is on the left.', 'tainacan-interface' ),
			'collection' => true,
			'choices'    => array(
				'stacked'        => __( 'Document and attachments on top, record below', 'tainacan-interface' ),
				'stacked-split'  => __( 'Document on top, then the record, attachments at the end', 'tainacan-interface' ),
				'stacked-record' => __( 'Record on top, document and attachments below', 'tainacan-interface' ),
				'media-left'     => __( 'Document on the left, record on the right', 'tainacan-interface' ),
				'media-right'    => __( 'Record on the left, document on the right', 'tainacan-interface' ),
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
		'item_label_size'       => array(
			'section' => 'item',
			'type'    => 'range',
			'default' => 14,
			'min'     => 10,
			'max'     => 22,
			'step'    => 1,
			'label'   => __( 'Size of the names of the metadata (px)', 'tainacan-interface' ),
			'help'    => __( 'The names in the record (Description, Author...). 14 px is 0.875rem.', 'tainacan-interface' ),
		),
		'item_value_size'       => array(
			'section' => 'item',
			'type'    => 'range',
			'default' => 16,
			'min'     => 12,
			'max'     => 26,
			'step'    => 1,
			'label'   => __( 'Size of the values of the metadata (px)', 'tainacan-interface' ),
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
			'section' => 'document',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Document in a frame', 'tainacan-interface' ),
		),

		// Posts and pages.
		'hero_style'            => array(
			'section' => 'hero',
			'type'    => 'choice',
			'default' => 'banner',
			'label'   => __( 'Banner at the top', 'tainacan-interface' ),
			'help'    => __( 'On the blog, posts, pages and searches. The image is chosen below; collections without a banner of their own use it too.', 'tainacan-interface' ),
			'choices' => array(
				'banner' => __( 'Image with a veil in the main color', 'tainacan-interface' ),
				'plain'  => __( 'Plain, no image', 'tainacan-interface' ),
				'none'   => __( 'No banner', 'tainacan-interface' ),
			),
		),
		'hero_align'            => array(
			'section' => 'hero',
			'type'    => 'choice',
			'default' => 'start',
			'label'   => __( 'Alignment of the banner text', 'tainacan-interface' ),
			'choices' => array(
				'start'  => __( 'Left', 'tainacan-interface' ),
				'center' => __( 'Center', 'tainacan-interface' ),
			),
		),
		'hero_height'           => array(
			'section' => 'hero',
			'type'    => 'range',
			'default' => 0,
			'min'     => 0,
			'max'     => 480,
			'step'    => 20,
			'label'   => __( 'Minimum height of the banner (px)', 'tainacan-interface' ),
			'help'    => __( '0: as tall as its text.', 'tainacan-interface' ),
		),
		'page_title_style'      => array(
			'section' => 'page_title',
			'type'    => 'choice',
			'default' => 'editorial',
			'label'   => __( 'Title of the page', 'tainacan-interface' ),
			'help'    => __( 'Below the banner, on the blog, posts, pages, searches and archives. A short line above the title says what the page is: the category of a post, the search, the archive.', 'tainacan-interface' ),
			'choices' => array(
				'editorial' => __( 'Large, with a fine line below', 'tainacan-interface' ),
				'centered'  => __( 'Large and centered', 'tainacan-interface' ),
				'compact'   => __( 'Compact', 'tainacan-interface' ),
			),
		),
		'page_title_meta'       => array(
			'section' => 'page_title',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Count and description below the title', 'tainacan-interface' ),
			'help'    => __( 'How many posts or results a listing has, and the description of a category, tag or author.', 'tainacan-interface' ),
		),
		'page_title_back'       => array(
			'section' => 'page_title',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Back button beside the title', 'tainacan-interface' ),
			'help'    => __( 'Returns to the previous page of the site; a visitor who arrived from outside goes to the blog or to the start page.', 'tainacan-interface' ),
		),
		'blog_layout'           => array(
			'section' => 'blog',
			'type'    => 'choice',
			'default' => 'list',
			'label'   => __( 'List of posts', 'tainacan-interface' ),
			'choices' => array(
				'list' => __( 'One below the other, image beside the text', 'tainacan-interface' ),
				'grid' => __( 'Cards in columns', 'tainacan-interface' ),
			),
		),
		'blog_columns'          => array(
			'section' => 'blog',
			'type'    => 'choice',
			'default' => '3',
			'label'   => __( 'Columns of cards on a large screen', 'tainacan-interface' ),
			'choices' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
		),
		'blog_ratio'            => array(
			'section' => 'blog',
			'type'    => 'choice',
			'default' => '3/2',
			'label'   => __( 'Proportion of the images of the list', 'tainacan-interface' ),
			'choices' => array(
				'auto' => __( 'As the image is', 'tainacan-interface' ),
				'4/3'  => '4:3',
				'3/2'  => '3:2',
				'16/9' => '16:9',
				'1/1'  => '1:1',
			),
		),
		'blog_excerpt'          => array(
			'section' => 'blog',
			'type'    => 'range',
			'default' => 28,
			'min'     => 0,
			'max'     => 80,
			'step'    => 2,
			'label'   => __( 'Words of the summary in the list', 'tainacan-interface' ),
			'help'    => __( '0: no summary.', 'tainacan-interface' ),
		),
		'sidebar_position'      => array(
			'section' => 'sidebar',
			'type'    => 'choice',
			'default' => 'right',
			'label'   => __( 'Sidebar of the blog', 'tainacan-interface' ),
			'help'    => __( 'Shown when it has widgets (Appearance > Widgets).', 'tainacan-interface' ),
			'choices' => array(
				'right' => __( 'On the right', 'tainacan-interface' ),
				'left'  => __( 'On the left', 'tainacan-interface' ),
				'none'  => __( 'None', 'tainacan-interface' ),
			),
		),
		'sidebar_width'         => array(
			'section' => 'sidebar',
			'type'    => 'range',
			'default' => 320,
			'min'     => 220,
			'max'     => 440,
			'step'    => 10,
			'label'   => __( 'Width of the sidebar (px)', 'tainacan-interface' ),
		),
		'sidebar_sticky'        => array(
			'section' => 'sidebar',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'Keep the sidebar in view while scrolling', 'tainacan-interface' ),
		),
		'post_width'            => array(
			'section' => 'post',
			'type'    => 'choice',
			'default' => 'normal',
			'label'   => __( 'Width of the text of a post or page', 'tainacan-interface' ),
			'choices' => array(
				'normal' => __( 'The width of the page', 'tainacan-interface' ),
				'narrow' => __( 'Narrow, easier to read', 'tainacan-interface' ),
			),
		),
		'narrow_width'          => array(
			'section' => 'post',
			'type'    => 'range',
			'default' => 760,
			'min'     => 560,
			'max'     => 1100,
			'step'    => 20,
			'label'   => __( 'Narrow width (px)', 'tainacan-interface' ),
		),
		'post_featured'         => array(
			'section' => 'post',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Featured image at the top of a post', 'tainacan-interface' ),
		),
		'post_meta'             => array(
			'section' => 'post',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Date and author of a post', 'tainacan-interface' ),
		),
		'post_share'            => array(
			'section' => 'post',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Share links on posts and pages', 'tainacan-interface' ),
		),
		'post_terms'            => array(
			'section' => 'post',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Categories, tags and comments at the end of a post', 'tainacan-interface' ),
		),
		'post_nav'              => array(
			'section' => 'post',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Previous and next post', 'tainacan-interface' ),
		),
		'post_progress'         => array(
			'section' => 'post',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'Reading progress bar on posts', 'tainacan-interface' ),
			'help'    => __( 'A thin line at the top of the window that fills as the visitor reads. Drawn by the browser, without a script; browsers that cannot draw it show nothing.', 'tainacan-interface' ),
		),

		// Back to top, frame and performance.
		'back_top'              => array(
			'section' => 'extras',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Back to top button', 'tainacan-interface' ),
		),
		'back_top_shape'        => array(
			'section' => 'extras',
			'type'    => 'choice',
			'default' => 'circle',
			'label'   => __( 'Shape of the button', 'tainacan-interface' ),
			'choices' => array(
				'circle'  => __( 'Circle', 'tainacan-interface' ),
				'rounded' => __( 'Rounded square', 'tainacan-interface' ),
				'square'  => __( 'Square', 'tainacan-interface' ),
			),
		),
		'back_top_side'         => array(
			'section' => 'extras',
			'type'    => 'choice',
			'default' => 'right',
			'label'   => __( 'Side of the button', 'tainacan-interface' ),
			'choices' => array(
				'right' => __( 'Right', 'tainacan-interface' ),
				'left'  => __( 'Left', 'tainacan-interface' ),
			),
		),
		'back_top_mobile'       => array(
			'section' => 'extras',
			'type'    => 'bool',
			'default' => true,
			'label'   => __( 'Show the button on phones', 'tainacan-interface' ),
		),
		'perf_emoji'            => array(
			'section' => 'extras',
			'type'    => 'bool',
			'default' => false,
			'label'   => __( 'Leave out the emoji script of WordPress', 'tainacan-interface' ),
			'help'    => __( 'Browsers draw emoji by themselves; the script only replaces them with images on very old systems.', 'tainacan-interface' ),
		),
	);

	foreach ( tainacan_interface_social_networks() as $key => $network ) {
		$schema[ 'social_' . $key ] = array(
			'section' => 'social',
			'type'    => 'url',
			'default' => '',
			'label'   => $network[0],
		);
	}

	/**
	 * The schema of the design settings. Options added here appear in the
	 * Customizer panel and answer to tainacan_interface_opt().
	 *
	 * @param array $schema Options.
	 */
	$built  = (array) apply_filters( 'tainacan_interface_settings_schema', $schema );
	$schema = $cache ? $built : null;
	return $built;
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
		case 'url':
			return is_string( $value ) ? esc_url_raw( trim( $value ), array( 'http', 'https', 'mailto' ) ) : '';
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
	$rule     = __( 'These are the defaults of the whole site. Colors, collection header, item preview and item page can also be chosen for one collection, on its form in Tainacan (Settings > Look of this collection on the site): what a collection chooses there prevails for it, and what it leaves "As the site" follows these options.', 'tainacan-interface' );
	$priority = 21;
	foreach ( tainacan_interface_settings_panels() as $id => $panel ) {
		$wp_customize->add_panel(
			'tainacan_interface_' . $id,
			array(
				'title'       => $panel[0],
				'description' => in_array( $id, array( 'style', 'lists', 'item' ), true ) ? $rule : '',
				'priority'    => $priority++,
				'capability'  => 'edit_theme_options',
			)
		);
	}

	$priority = 10;
	foreach ( tainacan_interface_settings_sections() as $id => $section ) {
		$wp_customize->add_section(
			'tainacan_interface_opt_' . $id,
			array(
				'title'       => $section['title'],
				'description' => tainacan_interface_section_intro( $id, $section['help'] ),
				'panel'       => 'tainacan_interface_' . $section['panel'],
				'priority'    => $priority,
				'capability'  => 'edit_theme_options',
			)
		);
		$priority += 10;
	}

	// The banner image of the site (core) goes with the banner options; the
	// title and tagline switches and the core colors do nothing in this theme.
	foreach ( array( 'header_image', 'header_video', 'external_header_video' ) as $id ) {
		$control = $wp_customize->get_control( $id );
		if ( $control ) {
			$control->section = 'tainacan_interface_opt_hero';
		}
	}
	foreach ( array( 'header_image', 'colors' ) as $id ) {
		$wp_customize->remove_section( $id );
	}
	foreach ( array( 'header_text', 'display_header_text', 'header_textcolor' ) as $id ) {
		$wp_customize->remove_control( $id );
	}
	if ( ! current_theme_supports( 'custom-background' ) ) {
		$wp_customize->remove_section( 'background_image' );
		$wp_customize->remove_control( 'background_color' );
	}

	// The options that came from the original theme go after the new ones of the
	// same section; the switch of a feature stays on top of its options.
	$switches = array( 'tainacan_interface_item_drawer', 'tainacan_interface_item_viewer', 'tainacan_interface_item_aside' );
	foreach ( $wp_customize->controls() as $control ) {
		if ( 0 === strpos( $control->section, 'tainacan_interface_opt_' ) && ! in_array( $control->id, $switches, true ) ) {
			$control->priority += 200;
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

	$order    = 2;
	$live     = tainacan_interface_live_options();
	$pictures = tainacan_interface_picture_options();
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
			if ( ! empty( $option['collection'] ) ) {
				$control['description'] = trim( $control['description'] . ' ' . __( 'Default of the site; a collection can choose another color on its form in Tainacan.', 'tainacan-interface' ) );
			}
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $control ) );
			continue;
		}

		if ( 'choice' === $option['type'] && isset( $pictures[ $key ] ) && class_exists( 'Tainacan_Interface_Picture_Control' ) ) {
			$names = array();
			foreach ( array_keys( $option['choices'] ) as $value ) {
				$names[ $value ] = isset( $pictures[ $key ]['names'][ $value ] ) ? $pictures[ $key ]['names'][ $value ] : $option['choices'][ $value ];
			}
			$control['choices']        = $names;
			$control['picture']        = $pictures[ $key ]['picture'];
			$control['picture_values'] = $pictures[ $key ]['values'];
			$control['per_collection'] = ! empty( $option['collection'] );
			$wp_customize->add_control( new Tainacan_Interface_Picture_Control( $wp_customize, $id, $control ) );
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
			case 'url':
				$control['type']        = 'url';
				$control['input_attrs'] = array( 'placeholder' => 'https://' );
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
