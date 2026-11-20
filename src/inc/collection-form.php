<?php
/**
 * The theme's panel on the collection form of Tainacan: how the public pages
 * of this collection look, next to the defaults of the site.
 *
 * Replaces two forms that did not talk to each other: the "extra settings" of
 * 2.x (layout of the metadata sections, as pictures) and a list of selects of
 * the new design. Now every choice is a picture of the result, the first one
 * is always "As the site" (showing what the site uses today), the pictures
 * take the colors chosen for the collection, and the two settings of the
 * metadata sections became one.
 *
 * Tainacan prints the returned HTML in its Vue form (v-html) and reads every
 * named field of it: radios are checked from the saved meta by Tainacan
 * itself, so the panel works without its script; the script only adds the
 * color picker, the colors of the pictures and the counters.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta of the combined setting of the metadata sections.
 */
const TAINACAN_INTERFACE_SECTIONS_META = 'tainacan_interface_sections';

/**
 * Layout of the metadata sections of a collection, from the closed list.
 *
 * @param int $collection_id Collection ID.
 * @return string default|tabs|collapses|accordion
 */
function tainacan_interface_section_layout( $collection_id ) {
	$layout = get_post_meta( (int) $collection_id, 'tainacan_interface_section_layout', true );
	return in_array( $layout, array( 'tabs', 'collapses', 'accordion' ), true ) ? $layout : 'default';
}

/**
 * Choices of the combined setting of the metadata sections: the layout of 2.x
 * and which panels start open, as one list.
 *
 * @return array<string, array{0: string, 1: string, 2: string|null}> value => (label, layout, open panels).
 */
function tainacan_interface_sections_choices() {
	return array(
		'open-first' => array( __( 'Panels, the first one open', 'tainacan-interface' ), 'default', 'first' ),
		'open-all'   => array( __( 'Panels, all open', 'tainacan-interface' ), 'default', 'all' ),
		'closed'     => array( __( 'Panels, all closed', 'tainacan-interface' ), 'default', 'none' ),
		'tabs'       => array( __( 'Tabs', 'tainacan-interface' ), 'tabs', null ),
		'accordion'  => array( __( 'Accordion, one at a time', 'tainacan-interface' ), 'accordion', null ),
	);
}

/**
 * Combined value of the metadata sections from the two settings it replaces.
 *
 * @param string $layout Layout of 2.x (default, tabs, collapses, accordion; empty for none).
 * @param string $open   Open panels (first, all, none; empty for the site's).
 * @return string Empty for "as the site".
 */
function tainacan_interface_sections_value( $layout, $open ) {
	if ( in_array( $layout, array( 'tabs', 'accordion' ), true ) ) {
		return $layout;
	}
	if ( 'collapses' === $layout ) {
		return 'open-first';
	}
	$by_open = array(
		'first' => 'open-first',
		'all'   => 'open-all',
		'none'  => 'closed',
	);
	return isset( $by_open[ $open ] ) ? $by_open[ $open ] : '';
}

/**
 * Groups of the panel and their options. Short names for the pictures; the
 * long ones of the Customizer stay as the tooltip.
 *
 * @return array
 */
function tainacan_interface_cform_groups() {
	return array(
		'colors' => array(
			'title'   => __( 'Colors', 'tainacan-interface' ),
			'hint'    => __( 'Titles, links and buttons of the pages of this collection.', 'tainacan-interface' ),
			'section' => 'look',
			'options' => array(
				'brand_color'  => array(),
				'accent_color' => array(),
			),
		),
		'header' => array(
			'title'   => __( 'Header', 'tainacan-interface' ),
			'hint'    => __( 'The top of the items page: banner, icon, description and menu.', 'tainacan-interface' ),
			'section' => 'collection',
			'options' => array(
				'head_layout'      => array(
					'center'  => __( 'Centered', 'tainacan-interface' ),
					'left'    => __( 'On the left', 'tainacan-interface' ),
					'overlay' => __( 'Title over the banner', 'tainacan-interface' ),
					'compact' => __( 'Compact', 'tainacan-interface' ),
				),
				'head_banner'      => array(
					'auto'   => __( 'Image or mosaic', 'tainacan-interface' ),
					'image'  => __( 'Header image', 'tainacan-interface' ),
					'mosaic' => __( 'Mosaic of items', 'tainacan-interface' ),
					'none'   => __( 'No banner', 'tainacan-interface' ),
				),
				'head_icon'        => array(
					'tilted' => __( 'Tilted card', 'tainacan-interface' ),
					'square' => __( 'Square', 'tainacan-interface' ),
					'circle' => __( 'Circle', 'tainacan-interface' ),
					'none'   => __( 'No icon', 'tainacan-interface' ),
				),
				'head_description' => array(
					'panel'  => __( 'In a panel', 'tainacan-interface' ),
					'plain'  => __( 'Plain text', 'tainacan-interface' ),
					'hidden' => __( 'Hidden', 'tainacan-interface' ),
				),
				'head_menu'        => array(
					'show' => __( 'Show the menu', 'tainacan-interface' ),
					'hide' => __( 'No menu', 'tainacan-interface' ),
				),
			),
		),
		'list'   => array(
			'title'   => __( 'List of items', 'tainacan-interface' ),
			'hint'    => __( 'What a click on an item of the list does.', 'tainacan-interface' ),
			'section' => 'drawer',
			'options' => array(
				'drawer_mode' => array(
					'overlay' => __( 'Preview over the list', 'tainacan-interface' ),
					'push'    => __( 'Preview beside the list', 'tainacan-interface' ),
					'off'     => __( 'Go to the item page', 'tainacan-interface' ),
				),
			),
		),
		'item'   => array(
			'title'   => __( 'Item page', 'tainacan-interface' ),
			'hint'    => __( 'Where the document and the record go, and how the metadata sections are shown.', 'tainacan-interface' ),
			'section' => 'item',
			'options' => array(
				'item_layout' => array(
					'stacked'        => __( 'Document on top', 'tainacan-interface' ),
					'stacked-split'  => __( 'Document, record, attachments', 'tainacan-interface' ),
					'stacked-record' => __( 'Record on top', 'tainacan-interface' ),
					'media-left'     => __( 'Document on the left', 'tainacan-interface' ),
					'media-right'    => __( 'Document on the right', 'tainacan-interface' ),
				),
				'sections'    => array(),
			),
		),
	);
}

/**
 * One shape of a picture.
 *
 * @param float  $x   X.
 * @param float  $y   Y.
 * @param float  $w   Width.
 * @param float  $h   Height.
 * @param string $cls Class (k brand, c accent, s soft, m muted, t text, w white, d dashed).
 * @param float  $rx  Corner radius.
 * @param string $extra Extra attributes, constant markup of this file.
 * @return string
 */
function tainacan_interface_cform_rect( $x, $y, $w, $h, $cls, $rx = 1.5, $extra = '' ) {
	return sprintf( '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" class="%s"%s/>', $x, $y, $w, $h, $rx, $cls, $extra );
}

/**
 * Lines of text of a picture.
 *
 * @param float $x     X.
 * @param float $y     Y of the first line.
 * @param float $w     Width.
 * @param int   $count Lines.
 * @param float $gap   Distance between lines.
 * @return string
 */
function tainacan_interface_cform_lines( $x, $y, $w, $count, $gap = 5 ) {
	$out = '';
	for ( $i = 0; $i < $count; $i++ ) {
		$out .= tainacan_interface_cform_rect( $x, $y + $i * $gap, $i === $count - 1 ? $w * 0.65 : $w, 2.4, 'm', 1.2 );
	}
	return $out;
}

/**
 * A grid of item cards for a picture.
 *
 * @param float $x    X.
 * @param float $y    Y.
 * @param int   $cols Columns.
 * @param int   $rows Rows.
 * @param float $size Size of a card.
 * @return string
 */
function tainacan_interface_cform_cards( $x, $y, $cols, $rows, $size = 12 ) {
	$out = '';
	for ( $r = 0; $r < $rows; $r++ ) {
		for ( $c = 0; $c < $cols; $c++ ) {
			$out .= tainacan_interface_cform_rect( $x + $c * ( $size + 3 ), $y + $r * ( $size + 3 ), $size, $size, 's', 1.5 );
		}
	}
	return $out;
}

/**
 * Picture of a choice: a small drawing of the page with that choice.
 *
 * @param string $key    Option.
 * @param string $choice Choice.
 * @return string SVG, constant markup built in this file.
 */
function tainacan_interface_cform_picture( $key, $choice ) {
	$r     = 'tainacan_interface_cform_rect';
	$lines = 'tainacan_interface_cform_lines';
	$cards = 'tainacan_interface_cform_cards';
	$body  = '';

	switch ( $key . ':' . $choice ) {
		case 'head_layout:center':
			$body = $r( 6, 5, 84, 22, 'k', 3 ) . $r( 40, 17, 16, 16, 'c', 3, ' stroke="#fff" stroke-width="1.5"' ) . $r( 30, 38, 36, 4, 't', 2 ) . $r( 36, 46, 24, 2.4, 'm', 1.2 );
			break;
		case 'head_layout:left':
			$body = $r( 6, 5, 84, 22, 'k', 3 ) . $r( 11, 17, 16, 16, 'c', 3, ' stroke="#fff" stroke-width="1.5"' ) . $r( 11, 38, 44, 4, 't', 2 ) . $r( 11, 46, 30, 2.4, 'm', 1.2 );
			break;
		case 'head_layout:overlay':
			$body = $r( 6, 5, 84, 44, 'k', 3 ) . $r( 13, 30, 44, 4.5, 'w', 2 ) . $r( 13, 38, 28, 2.4, 'w o', 1.2 );
			break;
		case 'head_layout:compact':
			$body = $r( 8, 10, 15, 15, 'c', 3 ) . $r( 28, 12, 44, 4.5, 't', 2 ) . $r( 28, 20, 28, 2.4, 'm', 1.2 ) . $cards( 8, 34, 5, 1, 14 );
			break;

		case 'head_banner:image':
			$body = $r( 6, 5, 84, 30, 's', 3 ) . '<path class="k" d="M10 33 L30 17 L42 27 L54 15 L86 33 Z"/><circle class="c" cx="74" cy="13" r="4"/>' . $r( 6, 42, 40, 4, 't', 2 );
			break;
		case 'head_banner:mosaic':
			$body = $r( 6, 5, 84, 30, 's', 3 );
			foreach ( array( array( 6, 5, 'k' ), array( 27, 5, 'c' ), array( 48, 5, 'm' ), array( 69, 5, 'k' ), array( 6, 20, 'c' ), array( 27, 20, 'm' ), array( 48, 20, 'k' ), array( 69, 20, 'c' ) ) as $tile ) {
				$body .= $r( $tile[0] + 1, $tile[1] + 1, 19, 13, $tile[2] . ' o', 1.5 );
			}
			$body .= $r( 6, 42, 40, 4, 't', 2 );
			break;
		case 'head_banner:auto':
			$body = $r( 6, 5, 84, 30, 's', 3 ) . '<path class="k" d="M9 33 L22 21 L30 28 L38 19 L46 33 Z"/>' . $r( 50, 6, 18, 13, 'c o', 1.5 ) . $r( 70, 6, 18, 13, 'k o', 1.5 ) . $r( 50, 21, 18, 13, 'k o', 1.5 ) . $r( 70, 21, 18, 13, 'c o', 1.5 ) . $r( 6, 42, 40, 4, 't', 2 );
			break;
		case 'head_banner:none':
			$body = $r( 6, 5, 84, 30, 'd', 3 ) . $r( 6, 42, 40, 4, 't', 2 ) . $r( 6, 50, 26, 2.4, 'm', 1.2 );
			break;

		case 'head_icon:tilted':
			$body = $r( 6, 5, 84, 20, 's', 3 ) . $r( 36, 13, 24, 24, 'c', 4, ' transform="rotate(-8 48 25)" stroke="#fff" stroke-width="1.5"' ) . $r( 30, 44, 36, 4, 't', 2 );
			break;
		case 'head_icon:square':
			$body = $r( 6, 5, 84, 20, 's', 3 ) . $r( 36, 13, 24, 24, 'c', 5, ' stroke="#fff" stroke-width="1.5"' ) . $r( 30, 44, 36, 4, 't', 2 );
			break;
		case 'head_icon:circle':
			$body = $r( 6, 5, 84, 20, 's', 3 ) . '<circle class="c" cx="48" cy="25" r="12" stroke="#fff" stroke-width="1.5"/>' . $r( 30, 44, 36, 4, 't', 2 );
			break;
		case 'head_icon:none':
			$body = $r( 6, 5, 84, 20, 's', 3 ) . $r( 36, 13, 24, 24, 'd', 5 ) . $r( 30, 44, 36, 4, 't', 2 );
			break;

		case 'head_description:panel':
			$body = $r( 8, 6, 46, 4.5, 't', 2 ) . $r( 8, 16, 80, 22, 's', 3 ) . $lines( 13, 21, 70, 3 ) . $cards( 8, 43, 5, 1, 12 );
			break;
		case 'head_description:plain':
			$body = $r( 8, 6, 46, 4.5, 't', 2 ) . $lines( 8, 19, 80, 3 ) . $cards( 8, 43, 5, 1, 12 );
			break;
		case 'head_description:hidden':
			$body = $r( 8, 6, 46, 4.5, 't', 2 ) . $cards( 8, 18, 5, 2, 12 );
			break;

		case 'head_menu:show':
			$body = $r( 6, 5, 84, 18, 'k', 3 ) . $r( 8, 28, 40, 4, 't', 2 ) . $r( 8, 37, 16, 6, 'c', 3 ) . $r( 27, 37, 16, 6, 's', 3 ) . $r( 46, 37, 16, 6, 's', 3 ) . $r( 65, 37, 16, 6, 's', 3 ) . $lines( 8, 49, 60, 1 );
			break;
		case 'head_menu:hide':
			$body = $r( 6, 5, 84, 18, 'k', 3 ) . $r( 8, 28, 40, 4, 't', 2 ) . $cards( 8, 38, 5, 1, 13 );
			break;

		case 'drawer_mode:overlay':
			$body = $cards( 7, 7, 5, 3, 13 ) . $r( 54, 4.5, 37, 54, 'sh', 3 ) . $r( 56, 3, 37, 54, 'w', 3 ) . $r( 60, 8, 29, 18, 'k', 2 ) . $lines( 60, 31, 28, 4 );
			break;
		case 'drawer_mode:push':
			$body = $cards( 6, 7, 3, 3, 13 ) . $r( 54, 3, 38, 54, 'w', 3, ' stroke="#cfd5de" stroke-width="1"' ) . $r( 58, 8, 30, 18, 'k', 2 ) . $lines( 58, 31, 29, 4 );
			break;
		case 'drawer_mode:off':
			$body = $cards( 7, 7, 5, 3, 13 ) . '<circle class="c" cx="27" cy="28" r="7"/><path d="M24 28 h6 m-2.5 -2.5 l2.5 2.5 l-2.5 2.5" stroke="#fff" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
			break;

		case 'item_layout:stacked':
			$body = $r( 8, 5, 80, 24, 'k', 3 ) . $r( 8, 32, 8, 6, 'c o', 1 ) . $r( 18, 32, 8, 6, 'c o', 1 ) . $r( 28, 32, 8, 6, 'c o', 1 ) . $lines( 8, 43, 80, 3 );
			break;
		case 'item_layout:stacked-split':
			$body = $r( 8, 5, 80, 22, 'k', 3 ) . $lines( 8, 31, 80, 3 ) . $r( 8, 48, 8, 6, 'c o', 1 ) . $r( 18, 48, 8, 6, 'c o', 1 ) . $r( 28, 48, 8, 6, 'c o', 1 );
			break;
		case 'item_layout:stacked-record':
			$body = $lines( 8, 6, 80, 3 ) . $r( 8, 21, 80, 24, 'k', 3 ) . $r( 8, 48, 8, 6, 'c o', 1 ) . $r( 18, 48, 8, 6, 'c o', 1 ) . $r( 28, 48, 8, 6, 'c o', 1 );
			break;
		case 'item_layout:media-left':
			$body = $r( 6, 6, 42, 34, 'k', 3 ) . $r( 6, 43, 8, 6, 'c o', 1 ) . $r( 16, 43, 8, 6, 'c o', 1 ) . $r( 26, 43, 8, 6, 'c o', 1 ) . $lines( 54, 8, 36, 7 );
			break;
		case 'item_layout:media-right':
			$body = $lines( 6, 8, 36, 7 ) . $r( 48, 6, 42, 34, 'k', 3 ) . $r( 48, 43, 8, 6, 'c o', 1 ) . $r( 58, 43, 8, 6, 'c o', 1 ) . $r( 68, 43, 8, 6, 'c o', 1 );
			break;

		case 'sections:open-first':
			$body = $r( 6, 4, 84, 8, 'k', 2 ) . $lines( 10, 16, 76, 3 ) . $r( 6, 34, 84, 8, 's', 2 ) . $r( 6, 46, 84, 8, 's', 2 );
			break;
		case 'sections:open-all':
			$body = $r( 6, 3, 84, 7, 'k', 2 ) . $lines( 10, 13, 76, 2 ) . $r( 6, 23, 84, 7, 'k', 2 ) . $lines( 10, 33, 76, 2 ) . $r( 6, 43, 84, 7, 'k', 2 ) . $lines( 10, 53, 60, 1 );
			break;
		case 'sections:closed':
			$body = $r( 6, 6, 84, 8, 's', 2 ) . $r( 6, 18, 84, 8, 's', 2 ) . $r( 6, 30, 84, 8, 's', 2 ) . $r( 6, 42, 84, 8, 's', 2 );
			break;
		case 'sections:tabs':
			$body = $r( 6, 6, 24, 8, 'c', 2 ) . $r( 33, 6, 24, 8, 's', 2 ) . $r( 60, 6, 24, 8, 's', 2 ) . $r( 6, 15, 84, 1.2, 'c', 0 ) . $lines( 8, 22, 80, 5 );
			break;
		case 'sections:accordion':
			$body = $r( 6, 4, 84, 8, 's', 2 ) . $r( 6, 15, 84, 8, 'k', 2 ) . $lines( 10, 27, 76, 3 ) . $r( 6, 44, 84, 8, 's', 2 );
			break;
	}

	if ( '' === $body ) {
		return '';
	}
	return '<svg viewBox="0 0 96 60" aria-hidden="true" focusable="false">' . tainacan_interface_cform_rect( 0, 0, 96, 60, 'pg', 4 ) . $body . '</svg>';
}

/**
 * Tags of the pictures, for wp_kses at the output.
 *
 * @return array
 */
function tainacan_interface_cform_svg_tags() {
	$shape = array(
		'class'           => true,
		'x'               => true,
		'y'               => true,
		'width'           => true,
		'height'          => true,
		'rx'              => true,
		'cx'              => true,
		'cy'              => true,
		'r'               => true,
		'd'               => true,
		'transform'       => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
		'fill'            => true,
	);
	return array(
		'svg'          => array(
			'viewbox'     => true,
			'aria-hidden' => true,
			'focusable'   => true,
		),
		'rect'         => $shape,
		'circle'       => $shape,
		'path'         => $shape,
	);
}

/**
 * Prints a picture.
 *
 * @param string $key    Option.
 * @param string $choice Choice.
 */
function tainacan_interface_cform_print_picture( $key, $choice ) {
	echo wp_kses( tainacan_interface_cform_picture( $key, $choice ), tainacan_interface_cform_svg_tags() );
}

/**
 * A color as #rrggbb, or empty: the panel, the picker and the counters work
 * with six digits.
 *
 * @param string $color Color.
 * @return string
 */
function tainacan_interface_cform_hex6( $color ) {
	$color = (string) sanitize_hex_color( (string) $color );
	if ( 4 === strlen( $color ) ) {
		$color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
	}
	return strtolower( $color );
}

/**
 * What the site uses today for an option of the panel.
 *
 * @param string $key Option ("sections" for the combined one).
 * @return string
 */
function tainacan_interface_cform_site_value( $key ) {
	if ( 'sections' === $key ) {
		return tainacan_interface_sections_value( 'default', (string) get_theme_mod( 'tainacan_interface_item_sections', 'first' ) );
	}
	$schema = tainacan_interface_settings_schema();
	$value  = get_theme_mod( 'tainacan_interface_' . $key, $schema[ $key ]['default'] );
	return (string) tainacan_interface_opt_normalize( $schema[ $key ], $value );
}

/**
 * Prints one option of choices as pictures, "As the site" first.
 *
 * @param string $key     Option ("sections" for the combined one).
 * @param array  $choices Value => short name.
 */
function tainacan_interface_cform_choice( $key, $choices ) {
	$schema = tainacan_interface_settings_schema();
	if ( 'sections' === $key ) {
		$name    = TAINACAN_INTERFACE_SECTIONS_META;
		$label   = __( 'Metadata sections', 'tainacan-interface' );
		$help    = __( 'How the groups of metadata of the record are shown. Sections are created in the metadata of the collection.', 'tainacan-interface' );
		$choices = wp_list_pluck( tainacan_interface_sections_choices(), 0 );
		$long    = $choices;
	} else {
		$name  = 'tainacan_interface_opt_' . $key;
		$label = $schema[ $key ]['label'];
		$help  = isset( $schema[ $key ]['help'] ) ? $schema[ $key ]['help'] : '';
		$long  = $schema[ $key ]['choices'];
	}
	$site      = tainacan_interface_cform_site_value( $key );
	$site_name = isset( $choices[ $site ] ) ? $choices[ $site ] : '';
	?>
	<fieldset class="tt2-cform__opt" data-option="<?php echo esc_attr( $key ); ?>">
		<legend class="label"><?php echo esc_html( $label ); ?></legend>
		<?php if ( $help ) : ?>
			<p class="tt2-cform__help"><?php echo esc_html( $help ); ?></p>
		<?php endif; ?>
		<div class="tt2-cform__tiles">
			<label class="tt2-tile tt2-tile--site">
				<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="" checked>
				<span class="tt2-tile__pic"><?php tainacan_interface_cform_print_picture( $key, $site ); ?></span>
				<span class="tt2-tile__name"><?php esc_html_e( 'As the site', 'tainacan-interface' ); ?></span>
				<?php if ( $site_name ) : ?>
					<?php /* translators: %s: name of the choice the site uses today. */ ?>
					<span class="tt2-tile__note"><?php echo esc_html( sprintf( __( 'today: %s', 'tainacan-interface' ), $site_name ) ); ?></span>
				<?php endif; ?>
			</label>
			<?php foreach ( $choices as $value => $short ) : ?>
				<label class="tt2-tile" title="<?php echo esc_attr( isset( $long[ $value ] ) ? $long[ $value ] : $short ); ?>">
					<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
					<span class="tt2-tile__pic"><?php tainacan_interface_cform_print_picture( $key, $value ); ?></span>
					<span class="tt2-tile__name"><?php echo esc_html( $short ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	</fieldset>
	<?php
}

/**
 * Prints one color of the panel: picker, code and "use the site's".
 *
 * @param string $key Option.
 */
function tainacan_interface_cform_color( $key ) {
	$schema = tainacan_interface_settings_schema();
	$name   = 'tainacan_interface_opt_' . $key;
	$site   = tainacan_interface_cform_hex6( tainacan_interface_cform_site_value( $key ) );
	$site   = '' === $site ? tainacan_interface_cform_hex6( $schema[ $key ]['default'] ) : $site;
	?>
	<div class="tt2-cform__opt tt2-color" data-option="<?php echo esc_attr( $key ); ?>" data-site="<?php echo esc_attr( $site ); ?>">
		<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $schema[ $key ]['label'] ); ?></label>
		<?php if ( ! empty( $schema[ $key ]['help'] ) ) : ?>
			<p class="tt2-cform__help"><?php echo esc_html( $schema[ $key ]['help'] ); ?></p>
		<?php endif; ?>
		<div class="tt2-color__row">
			<input type="color" class="tt2-color__pick" form="tt2-cform-none" value="<?php echo esc_attr( $site ); ?>" aria-label="<?php echo esc_attr( $schema[ $key ]['label'] ); ?>">
			<?php /* translators: %s: color of the site, such as #1c355e. */ ?>
			<input type="text" class="input tt2-color__hex" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( sprintf( __( 'As the site (%s)', 'tainacan-interface' ), $site ) ); ?>" pattern="#([0-9a-fA-F]{3}){1,2}" maxlength="7" spellcheck="false" autocomplete="off">
			<button type="button" class="button is-small is-outlined tt2-color__reset" form="tt2-cform-none"><?php esc_html_e( 'Use the site\'s', 'tainacan-interface' ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * The panel. Tainacan prints the returned HTML in its Vue form (v-html), so
 * the callback returns it and carries no script.
 *
 * @return string
 */
function tainacan_interface_cform() {
	$brand  = tainacan_interface_cform_site_value( 'brand_color' );
	$accent = tainacan_interface_cform_site_value( 'accent_color' );
	/* translators: %d: number of settings changed in this collection. */
	$many = __( '%d changes in this collection', 'tainacan-interface' );
	ob_start();
	?>
	<div class="tt2-cform" style="--tt2f-brand: <?php echo esc_attr( tainacan_interface_cform_hex6( $brand ) ); ?>; --tt2f-accent: <?php echo esc_attr( tainacan_interface_cform_hex6( $accent ) ); ?>;">
		<div class="tt2-cform__head">
			<h4><?php esc_html_e( 'Look of this collection on the site', 'tainacan-interface' ); ?></h4>
			<p><?php esc_html_e( 'Each part starts as the whole site is set in Appearance > Customize. Pick another picture only where this collection should be different.', 'tainacan-interface' ); ?></p>
		</div>
		<?php foreach ( tainacan_interface_cform_groups() as $group_id => $group ) : ?>
			<details class="tt2-cform__group" data-group="<?php echo esc_attr( $group_id ); ?>">
				<summary>
					<span class="tt2-cform__title"><?php echo esc_html( $group['title'] ); ?></span>
					<span class="tt2-cform__state" data-site="<?php esc_attr_e( 'As the site', 'tainacan-interface' ); ?>" data-one="<?php esc_attr_e( '1 change in this collection', 'tainacan-interface' ); ?>" data-many="<?php echo esc_attr( $many ); ?>"><?php esc_html_e( 'As the site', 'tainacan-interface' ); ?></span>
					<span class="tt2-cform__hint"><?php echo esc_html( $group['hint'] ); ?></span>
				</summary>
				<div class="tt2-cform__body">
					<?php
					foreach ( $group['options'] as $key => $choices ) {
						if ( 'colors' === $group_id ) {
							tainacan_interface_cform_color( $key );
						} else {
							tainacan_interface_cform_choice( $key, $choices );
						}
					}
					?>
					<a class="tt2-cform__site" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=tainacan_interface_opt_' . $group['section'] ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Change it for the whole site', 'tainacan-interface' ); ?> &#8599;</a>
				</div>
			</details>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Registers the panel on the collection form.
 */
function tainacan_interface_cform_register() {
	if ( function_exists( 'tainacan_register_admin_hook' ) ) {
		tainacan_register_admin_hook( 'collection', 'tainacan_interface_cform', 'end-right' );
	}
}
add_action( 'tainacan-register-admin-hooks', 'tainacan_interface_cform_register' );

/**
 * Style and script of the panel, on the Tainacan admin only.
 */
function tainacan_interface_cform_enqueue() {
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'tainacan-interface-collection-form', $uri . '/assets/css/collection-form.css', array(), TAINACAN_INTERFACE_VERSION );
	wp_enqueue_script( 'tainacan-interface-collection-form', $uri . '/assets/js/collection-form.js', array(), TAINACAN_INTERFACE_VERSION, true );
}
add_action( 'tainacan-enqueue-admin-scripts', 'tainacan_interface_cform_enqueue' );

/**
 * Declares the meta, so Tainacan fills the panel with the saved values.
 *
 * @param array $extra_meta Meta keys declared by others.
 * @return array
 */
function tainacan_interface_cform_expose( $extra_meta ) {
	$extra_meta = is_array( $extra_meta ) ? $extra_meta : array();
	foreach ( array_keys( tainacan_interface_collection_options() ) as $key ) {
		$extra_meta[] = 'tainacan_interface_opt_' . $key;
	}
	$extra_meta[] = TAINACAN_INTERFACE_SECTIONS_META;
	return array_values( array_unique( $extra_meta ) );
}
add_filter( 'tainacan-api-response-collection-meta', 'tainacan_interface_cform_expose' );

/**
 * Writes the combined setting of the metadata sections and the two it stands for.
 *
 * @param int    $collection_id Collection ID.
 * @param string $value         Combined value; empty for "as the site".
 */
function tainacan_interface_sections_save( $collection_id, $value ) {
	$choices = tainacan_interface_sections_choices();
	if ( ! isset( $choices[ $value ] ) ) {
		delete_post_meta( $collection_id, TAINACAN_INTERFACE_SECTIONS_META );
		delete_post_meta( $collection_id, 'tainacan_interface_section_layout' );
		delete_post_meta( $collection_id, 'tainacan_interface_opt_item_sections' );
		return;
	}
	update_post_meta( $collection_id, TAINACAN_INTERFACE_SECTIONS_META, $value );
	update_post_meta( $collection_id, 'tainacan_interface_section_layout', $choices[ $value ][1] );
	if ( null === $choices[ $value ][2] ) {
		delete_post_meta( $collection_id, 'tainacan_interface_opt_item_sections' );
	} else {
		update_post_meta( $collection_id, 'tainacan_interface_opt_item_sections', $choices[ $value ][2] );
	}
}

/**
 * Saves the panel with the collection.
 *
 * @param \Tainacan\Entities\Collection $collection Collection being saved.
 */
function tainacan_interface_cform_save( $collection ) {
	if ( ! function_exists( 'tainacan_get_api_postdata' ) || ! $collection instanceof \Tainacan\Entities\Collection || ! $collection->can_edit() ) {
		return;
	}

	$posted = tainacan_get_api_postdata();
	if ( ! is_object( $posted ) ) {
		return;
	}
	$collection_id = (int) $collection->get_id();

	foreach ( tainacan_interface_collection_options() as $key => $option ) {
		$name = 'tainacan_interface_opt_' . $key;
		if ( 'item_sections' === $key || ! isset( $posted->{$name} ) || ! is_scalar( $posted->{$name} ) ) {
			continue;
		}
		$raw = trim( (string) $posted->{$name} );
		if ( '' === $raw ) {
			delete_post_meta( $collection_id, $name );
			continue;
		}
		if ( 'color' === $option['type'] ) {
			$raw = tainacan_interface_cform_hex6( $raw );
		}
		$valid = 'color' === $option['type'] ? '' !== $raw : array_key_exists( $raw, $option['choices'] );
		if ( $valid ) {
			update_post_meta( $collection_id, $name, $raw );
		}
	}

	$sections = TAINACAN_INTERFACE_SECTIONS_META;
	if ( isset( $posted->{$sections} ) && is_scalar( $posted->{$sections} ) ) {
		tainacan_interface_sections_save( $collection_id, trim( (string) $posted->{$sections} ) );
	}
}
add_action( 'tainacan-insert-tainacan-collection', 'tainacan_interface_cform_save' );

/**
 * Migration step 3: collections that set their metadata sections in either of
 * the two former settings get the combined one. The 2.x "collapses" layout
 * becomes panels with the first one open, which is what it showed.
 *
 * @return bool False when Tainacan is not active: the step waits for it.
 */
function tainacan_interface_migrate_sections() {
	if ( ! post_type_exists( 'tainacan-collection' ) ) {
		return false;
	}
	$ids = get_posts(
		array(
			'post_type'        => 'tainacan-collection',
			'post_status'      => 'any',
			'numberposts'      => -1, // tnc-check-ignore TNC-P01: only the IDs of the collections that set the sections, once per site on update.
			'fields'           => 'ids',
			'suppress_filters' => false,
			// One pass on update, over the collections only.
			'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- runs once per site, on update, over the collections (a few hundred posts at most).
				'relation' => 'OR',
				array( 'key' => 'tainacan_interface_section_layout' ),
				array( 'key' => 'tainacan_interface_opt_item_sections' ),
			),
		)
	);
	foreach ( $ids as $id ) {
		$value = tainacan_interface_sections_value(
			(string) get_post_meta( $id, 'tainacan_interface_section_layout', true ),
			(string) get_post_meta( $id, 'tainacan_interface_opt_item_sections', true )
		);
		tainacan_interface_sections_save( (int) $id, $value );
	}
	return true;
}
