<?php
/**
 * Drawings of the Customizer: a picture of each choice of the site options
 * (the collection options have theirs in inc/collection-form.php) and, at the
 * top of each section, a sketch of the page with the part it changes marked.
 * Same shapes and classes as the pictures of the collection form.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options of the site drawn as pictures in the Customizer.
 *
 * @return string[]
 */
function tainacan_interface_site_picture_keys() {
	return array(
		'band_style',
		'shadow',
		'content_spacing',
		'heading_color',
		'heading_transform',
		'menu_transform',
		'button_shape',
		'button_style',
		'button_transform',
		'field_style',
		'link_style',
		'selection',
		'header_style',
		'menu_indicator',
		'mobile_menu',
		'header_stack',
		'footer_style',
		'footer_tone',
		'social_place',
		'crumb_style',
		'crumb_separator',
		'head_banner_width',
		'list_style',
		'list_columns',
		'list_ratio',
		'list_image',
		'card_accent',
		'card_hover',
		'placeholder_style',
		'drawer_side',
		'drawer_media',
		'item_chips',
		'item_labels',
		'item_columns',
		'hero_style',
		'hero_align',
		'page_title_style',
		'blog_layout',
		'blog_columns',
		'sidebar_position',
		'post_width',
		'back_top_shape',
		'back_top_side',
	);
}

/**
 * Text of a picture.
 *
 * @param float  $x    X.
 * @param float  $y    Y of the baseline.
 * @param string $text Text, constant of this file.
 * @param string $cls  Class.
 * @param int    $size Font size.
 * @return string
 */
function tainacan_interface_drawing_text( $x, $y, $text, $cls = 't', $size = 9 ) {
	return sprintf( '<text x="%s" y="%s" class="%s" font-size="%s" text-anchor="middle">%s</text>', $x, $y, $cls, $size, $text );
}

/**
 * A header bar with a logo and three menu items.
 *
 * @param string $cls  Class of the bar.
 * @param string $ink  Class of the logo and the menu.
 * @param float  $h    Height.
 * @return string
 */
function tainacan_interface_drawing_header( $cls = 'k', $ink = 'w', $h = 14 ) {
	$r   = 'tainacan_interface_cform_rect';
	$out = $r( 6, 5, 84, $h, $cls, 3 ) . $r( 11, 5 + $h / 2 - 3, 14, 6, $ink, 1.5 );
	foreach ( array( 50, 62, 74 ) as $x ) {
		$out .= $r( $x, 5 + $h / 2 - 1.2, 9, 2.4, $ink . ' o', 1.2 );
	}
	return $out;
}

/**
 * Picture of a choice of a site option.
 *
 * @param string $key    Option.
 * @param string $choice Choice.
 * @return string Inner shapes, constant markup built in this file; '' when there is none.
 */
function tainacan_interface_site_drawing( $key, $choice ) {
	$r      = 'tainacan_interface_cform_rect';
	$lines  = 'tainacan_interface_cform_lines';
	$cards  = 'tainacan_interface_cform_cards';
	$text   = 'tainacan_interface_drawing_text';
	$header = 'tainacan_interface_drawing_header';

	switch ( $key . ':' . $choice ) {
		case 'band_style:bands':
			return $header() . $r( 6, 20, 21, 3, 'b1', 0 ) . $r( 27, 20, 21, 3, 'b2', 0 ) . $r( 48, 20, 21, 3, 'b3', 0 ) . $r( 69, 20, 21, 3, 'b4', 0 ) . $lines( 10, 32, 76, 4 );
		case 'band_style:gradient':
			return $header() . $r( 6, 20, 84, 3, 'b1', 0 ) . $r( 20, 20, 50, 3, 'b2 o', 0 ) . $r( 45, 20, 35, 3, 'b3 o', 0 ) . $r( 70, 20, 20, 3, 'b4', 0 ) . $lines( 10, 32, 76, 4 );
		case 'band_style:accent':
			return $header() . $r( 6, 20, 84, 3, 'c', 0 ) . $lines( 10, 32, 76, 4 );
		case 'band_style:none':
			return $header() . $lines( 10, 30, 76, 4 );

		case 'shadow:none':
			return $r( 10, 12, 34, 36, 'w', 4, ' stroke="#dfe3ea"' ) . $r( 52, 12, 34, 36, 'w', 4, ' stroke="#dfe3ea"' ) . $lines( 14, 38, 26, 2 ) . $lines( 56, 38, 26, 2 );
		case 'shadow:soft':
			return $r( 11, 14, 34, 36, 'sh', 4 ) . $r( 53, 14, 34, 36, 'sh', 4 ) . $r( 10, 12, 34, 36, 'w', 4 ) . $r( 52, 12, 34, 36, 'w', 4 ) . $lines( 14, 38, 26, 2 ) . $lines( 56, 38, 26, 2 );
		case 'shadow:deep':
			return $r( 12, 16, 35, 38, 'sh', 5 ) . $r( 54, 16, 35, 38, 'sh', 5 ) . $r( 12, 15, 34, 37, 'sh', 4 ) . $r( 54, 15, 34, 37, 'sh', 4 ) . $r( 10, 12, 34, 36, 'w', 4 ) . $r( 52, 12, 34, 36, 'w', 4 ) . $lines( 14, 38, 26, 2 ) . $lines( 56, 38, 26, 2 );

		case 'content_spacing:compact':
			return $r( 10, 8, 76, 12, 's', 2 ) . $r( 10, 22, 76, 12, 's', 2 ) . $r( 10, 36, 76, 12, 's', 2 );
		case 'content_spacing:normal':
			return $r( 10, 6, 76, 11, 's', 2 ) . $r( 10, 24, 76, 11, 's', 2 ) . $r( 10, 42, 76, 11, 's', 2 );
		case 'content_spacing:spacious':
			return $r( 10, 4, 76, 10, 's', 2 ) . $r( 10, 25, 76, 10, 's', 2 ) . $r( 10, 46, 76, 10, 's', 2 );

		case 'heading_color:brand':
		case 'heading_color:accent':
		case 'heading_color:text':
			$cls = array(
				'brand'  => 'k',
				'accent' => 'c',
				'text'   => 't',
			);
			return $r( 10, 12, 60, 7, $cls[ $choice ], 2 ) . $lines( 10, 28, 76, 4 );

		case 'heading_transform:none':
		case 'menu_transform:none':
		case 'button_transform:none':
			return ( 'button_transform' === $key ? $r( 22, 18, 52, 22, 'c', 5 ) . $text( 48, 33, 'Abc', 'w', 11 ) : $text( 48, 36, 'Abc', 'menu_transform' === $key ? 'k' : 't', 18 ) );
		case 'heading_transform:uppercase':
		case 'menu_transform:uppercase':
		case 'button_transform:uppercase':
			return ( 'button_transform' === $key ? $r( 22, 18, 52, 22, 'c', 5 ) . $text( 48, 33, 'ABC', 'w', 11 ) : $text( 48, 36, 'ABC', 'menu_transform' === $key ? 'k' : 't', 18 ) );

		case 'button_shape:rounded':
			return $r( 18, 20, 60, 20, 'c', 5 ) . $r( 34, 28.8, 28, 2.4, 'w', 1.2 );
		case 'button_shape:pill':
			return $r( 18, 20, 60, 20, 'c', 10 ) . $r( 34, 28.8, 28, 2.4, 'w', 1.2 );
		case 'button_shape:square':
			return $r( 18, 20, 60, 20, 'c', 0 ) . $r( 34, 28.8, 28, 2.4, 'w', 1.2 );

		case 'button_style:filled':
			return $r( 18, 20, 60, 20, 'c', 5 ) . $r( 34, 28.8, 28, 2.4, 'w', 1.2 );
		case 'button_style:outline':
			return $r( 19, 21, 58, 18, 'co', 5 ) . $r( 34, 28.8, 28, 2.4, 'c', 1.2 );

		case 'field_style:outlined':
			return $r( 12, 22, 72, 16, 'w', 3, ' stroke="#c4cbd6" stroke-width="1.2"' ) . $r( 17, 28.8, 30, 2.4, 'm', 1.2 );
		case 'field_style:filled':
			return $r( 12, 22, 72, 16, 's', 3 ) . $r( 17, 28.8, 30, 2.4, 'm', 1.2 );
		case 'field_style:underline':
			return $r( 12, 37, 72, 1.5, 'm', 0 ) . $r( 14, 28.8, 30, 2.4, 'm', 1.2 );

		case 'link_style:underline':
			return $lines( 10, 18, 76, 1 ) . $r( 10, 26, 22, 2.4, 'm', 1.2 ) . $r( 35, 26, 26, 2.4, 'c', 1.2 ) . $r( 35, 30, 26, 0.9, 'c', 0 ) . $r( 64, 26, 22, 2.4, 'm', 1.2 ) . $lines( 10, 36, 76, 2 );
		case 'link_style:hover':
			return $lines( 10, 18, 76, 1 ) . $r( 10, 26, 22, 2.4, 'm', 1.2 ) . $r( 35, 26, 26, 2.4, 'c', 1.2 ) . $r( 35, 30, 26, 0.9, 'c o', 0 ) . $r( 64, 26, 22, 2.4, 'm', 1.2 ) . $lines( 10, 36, 76, 2 ) . '<path class="t" d="M52 29 l0 10 l2.6 -2.4 l1.8 4 l1.6 -0.8 l-1.8 -3.8 l3.4 -0.2 z"/>';
		case 'link_style:none':
			return $lines( 10, 18, 76, 1 ) . $r( 10, 26, 22, 2.4, 'm', 1.2 ) . $r( 35, 26, 26, 2.4, 'c', 1.2 ) . $r( 64, 26, 22, 2.4, 'm', 1.2 ) . $lines( 10, 36, 76, 2 );

		case 'selection:accent':
		case 'selection:brand':
		case 'selection:browser':
			$cls = array(
				'accent'  => 'c',
				'brand'   => 'k',
				'browser' => 'bz',
			);
			return $lines( 10, 16, 76, 1 ) . $r( 10, 24, 18, 2.4, 'm', 1.2 ) . $r( 30, 21.5, 38, 7.5, $cls[ $choice ], 1 ) . $r( 32, 24, 34, 2.4, 'w', 1.2 ) . $r( 70, 24, 16, 2.4, 'm', 1.2 ) . $lines( 10, 34, 76, 3 );

		case 'header_style:brand':
			return $header( 'k', 'w', 18 ) . $lines( 10, 34, 76, 4 );
		case 'header_style:accent':
			return $header( 'c', 'w', 18 ) . $lines( 10, 34, 76, 4 );
		case 'header_style:dark':
			return $header( 't', 'w', 18 ) . $lines( 10, 34, 76, 4 );
		case 'header_style:light':
			return $r( 6, 5, 84, 18, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 11, 11, 14, 6, 'k', 1.5 ) . $r( 50, 12.8, 9, 2.4, 'm', 1.2 ) . $r( 62, 12.8, 9, 2.4, 'm', 1.2 ) . $r( 74, 12.8, 9, 2.4, 'm', 1.2 ) . $lines( 10, 34, 76, 4 );

		case 'menu_indicator:underline':
			return $header( 'k', 'w', 20 ) . $r( 49, 20, 11, 1.5, 'w', 0 ) . $lines( 10, 36, 76, 3 );
		case 'menu_indicator:pill':
			return $header( 'k', 'w', 20 ) . $r( 47, 11.5, 15, 7, 'w o', 3.5 ) . $lines( 10, 36, 76, 3 );
		case 'menu_indicator:none':
			return $header( 'k', 'w', 20 ) . $lines( 10, 36, 76, 3 );

		case 'mobile_menu:dropdown':
			return $r( 34, 3, 28, 54, 'w', 4, ' stroke="#c4cbd6"' ) . $r( 34, 3, 28, 9, 'k', 4 ) . $r( 34, 12, 28, 22, 's', 0 ) . $r( 38, 16, 18, 2.4, 'm', 1.2 ) . $r( 38, 22, 14, 2.4, 'm', 1.2 ) . $r( 38, 28, 16, 2.4, 'm', 1.2 ) . $lines( 37, 40, 22, 3 );
		case 'mobile_menu:side':
			return $r( 34, 3, 28, 54, 'w', 4, ' stroke="#c4cbd6"' ) . $r( 34, 3, 28, 9, 'k', 4 ) . $lines( 37, 16, 22, 6 ) . $r( 34, 3, 28, 54, 'sh', 4 ) . $r( 44, 3, 18, 54, 'w', 0 ) . $r( 47, 9, 11, 2.4, 'm', 1.2 ) . $r( 47, 15, 9, 2.4, 'm', 1.2 ) . $r( 47, 21, 11, 2.4, 'm', 1.2 );

		case 'header_stack:stacked':
			return $r( 6, 5, 84, 24, 'k', 3 ) . $r( 11, 9, 16, 7, 'w', 1.5 ) . $r( 11, 21, 9, 2.4, 'w o', 1.2 ) . $r( 23, 21, 9, 2.4, 'w o', 1.2 ) . $r( 35, 21, 9, 2.4, 'w o', 1.2 ) . $lines( 10, 38, 76, 3 );
		case 'header_stack:inline':
			return $header( 'k', 'w', 16 ) . $lines( 10, 32, 76, 4 );

		case 'footer_style:compact':
			return $lines( 10, 8, 76, 4 ) . $r( 6, 40, 84, 15, 'k', 3 ) . $r( 30, 44, 36, 2.4, 'w o', 1.2 ) . $r( 38, 49, 20, 2.4, 'w o', 1.2 );
		case 'footer_style:classic':
			return $lines( 10, 6, 76, 2 ) . $r( 6, 22, 84, 33, 't', 3 ) . $lines( 11, 28, 20, 3 ) . $lines( 38, 28, 20, 3 ) . $lines( 65, 28, 20, 3 ) . $r( 6, 46, 84, 0.8, 'w o', 0 ) . $r( 11, 49.5, 30, 2.4, 'w o', 1.2 );

		case 'footer_tone:brand':
			return $lines( 10, 8, 76, 4 ) . $r( 6, 38, 84, 17, 'k', 3 ) . $r( 30, 44, 36, 2.4, 'w o', 1.2 );
		case 'footer_tone:dark':
			return $lines( 10, 8, 76, 4 ) . $r( 6, 38, 84, 17, 't', 3 ) . $r( 30, 44, 36, 2.4, 'w o', 1.2 );
		case 'footer_tone:light':
			return $lines( 10, 8, 76, 4 ) . $r( 6, 38, 84, 17, 's', 3 ) . $r( 30, 44, 36, 2.4, 'm', 1.2 );

		case 'social_place:footer':
		case 'social_place:header':
		case 'social_place:both':
		case 'social_place:none':
			$out = $header( 'k', 'w', 12 ) . $lines( 10, 24, 76, 3 ) . $r( 6, 42, 84, 13, 'k', 3 );
			if ( in_array( $choice, array( 'header', 'both' ), true ) ) {
				$out .= '<circle class="c" cx="70" cy="11" r="2.4"/><circle class="c" cx="77" cy="11" r="2.4"/><circle class="c" cx="84" cy="11" r="2.4"/>';
			}
			if ( in_array( $choice, array( 'footer', 'both' ), true ) ) {
				$out .= '<circle class="c" cx="41" cy="48.5" r="2.4"/><circle class="c" cx="48" cy="48.5" r="2.4"/><circle class="c" cx="55" cy="48.5" r="2.4"/>';
			}
			return $out;

		case 'crumb_style:pills':
			return $r( 8, 22, 22, 9, 's', 4.5 ) . $r( 33, 22, 26, 9, 's', 4.5 ) . $r( 62, 22, 26, 9, 'c', 4.5 ) . $r( 12, 25.3, 14, 2.4, 'm', 1.2 ) . $r( 37, 25.3, 18, 2.4, 'm', 1.2 ) . $r( 66, 25.3, 18, 2.4, 'w', 1.2 );
		case 'crumb_style:plain':
			return $r( 8, 25.3, 18, 2.4, 'm', 1.2 ) . $text( 31, 28.5, '›', 't', 9 ) . $r( 36, 25.3, 22, 2.4, 'm', 1.2 ) . $text( 63, 28.5, '›', 't', 9 ) . $r( 68, 25.3, 20, 2.4, 't', 1.2 );
		case 'crumb_style:none':
			return $r( 8, 21, 80, 10, 'd', 4 ) . $r( 8, 38, 50, 5, 't', 2 );

		case 'crumb_separator:chevron':
		case 'crumb_separator:slash':
		case 'crumb_separator:dot':
			$sign = array(
				'chevron' => '›',
				'slash'   => '/',
				'dot'     => '·',
			);
			return $r( 8, 25.3, 18, 2.4, 'm', 1.2 ) . $text( 31, 29, $sign[ $choice ], 't', 10 ) . $r( 36, 25.3, 22, 2.4, 'm', 1.2 ) . $text( 63, 29, $sign[ $choice ], 't', 10 ) . $r( 68, 25.3, 20, 2.4, 't', 1.2 );

		case 'head_banner_width:contained':
			return $r( 0, 0, 96, 8, 'k', 0 ) . $r( 10, 12, 76, 24, 'c o', 4 ) . $r( 10, 42, 40, 4, 't', 2 ) . $lines( 10, 50, 60, 1 );
		case 'head_banner_width:full':
			return $r( 0, 0, 96, 8, 'k', 0 ) . $r( 0, 10, 96, 26, 'c o', 0 ) . $r( 10, 42, 40, 4, 't', 2 ) . $lines( 10, 50, 60, 1 );

		case 'list_style:card':
			return $r( 8, 10, 24, 18, 'k o', 2 ) . $r( 36, 10, 24, 18, 'c o', 2 ) . $r( 64, 10, 24, 18, 'k o', 2 ) . $lines( 8, 32, 20, 2 ) . $lines( 36, 32, 20, 2 ) . $lines( 64, 32, 20, 2 );
		case 'list_style:overlay':
			return $r( 8, 12, 24, 30, 'k', 2 ) . $r( 36, 12, 24, 30, 'c', 2 ) . $r( 64, 12, 24, 30, 'k', 2 ) . $r( 11, 34, 16, 2.4, 'w', 1.2 ) . $r( 39, 34, 16, 2.4, 'w', 1.2 ) . $r( 67, 34, 16, 2.4, 'w', 1.2 );
		case 'list_style:row':
			return $r( 8, 6, 22, 14, 'k o', 2 ) . $lines( 34, 9, 52, 2 ) . $r( 8, 23, 22, 14, 'c o', 2 ) . $lines( 34, 26, 52, 2 ) . $r( 8, 40, 22, 14, 'k o', 2 ) . $lines( 34, 43, 52, 2 );

		case 'list_columns:2':
		case 'list_columns:3':
		case 'list_columns:4':
		case 'list_columns:5':
		case 'list_columns:6':
		case 'blog_columns:2':
		case 'blog_columns:3':
		case 'blog_columns:4':
			$cols = (int) $choice;
			$size = min( 18, ( 80 - 3 * ( $cols - 1 ) ) / $cols );
			return $cards( ( 96 - $cols * $size - 3 * ( $cols - 1 ) ) / 2, 10, $cols, $cols > 3 ? 3 : 2, $size );

		case 'list_ratio:4/3':
			return $r( 24, 9, 48, 36, 'k o', 3 );
		case 'list_ratio:3/2':
			return $r( 21, 11, 54, 36, 'k o', 3 );
		case 'list_ratio:16/9':
			return $r( 16, 13, 64, 36, 'k o', 3 );
		case 'list_ratio:1/1':
			return $r( 30, 9, 36, 36, 'k o', 3 );

		case 'list_image:auto':
			return $r( 10, 10, 34, 30, 'k o', 2 ) . '<path class="w" d="M13 38 L22 28 L28 33 L34 26 L41 38 Z"/>' . $r( 52, 10, 16, 14, 'c o', 1.5 ) . $r( 70, 10, 16, 14, 'k o', 1.5 ) . $r( 52, 26, 16, 14, 'k o', 1.5 ) . $r( 70, 26, 16, 14, 'c o', 1.5 ) . $lines( 10, 46, 34, 1 ) . $lines( 52, 46, 34, 1 );
		case 'list_image:thumbnail':
			return $r( 28, 8, 40, 34, 'k o', 2 ) . '<path class="w" d="M32 40 L44 27 L52 34 L58 26 L66 40 Z"/>' . $lines( 28, 48, 40, 1 );
		case 'list_image:mosaic':
			return $r( 28, 8, 19, 16, 'c o', 1.5 ) . $r( 49, 8, 19, 16, 'k o', 1.5 ) . $r( 28, 26, 19, 16, 'k o', 1.5 ) . $r( 49, 26, 19, 16, 'c o', 1.5 ) . $lines( 28, 48, 40, 1 );

		case 'card_accent:side':
			return $r( 18, 10, 60, 40, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 18, 10, 3, 40, 'c', 1.5 ) . $r( 26, 15, 46, 18, 's', 2 ) . $lines( 26, 38, 40, 2 );
		case 'card_accent:top':
			return $r( 18, 10, 60, 40, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 18, 10, 60, 3, 'c', 1.5 ) . $r( 23, 17, 50, 16, 's', 2 ) . $lines( 23, 38, 40, 2 );
		case 'card_accent:none':
			return $r( 18, 10, 60, 40, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 23, 15, 50, 18, 's', 2 ) . $lines( 23, 38, 40, 2 );

		case 'card_hover:lift':
			return $r( 21, 16, 54, 38, 'sh', 3 ) . $r( 20, 8, 56, 40, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 25, 13, 46, 18, 's', 2 ) . $lines( 25, 36, 36, 2 ) . '<path class="c" d="M84 22 l4 5 h-2.6 v7 h-2.8 v-7 h-2.6 z"/>';
		case 'card_hover:ring':
			return $r( 20, 10, 56, 40, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 18.5, 8.5, 59, 43, 'co', 4 ) . $r( 25, 15, 46, 18, 's', 2 ) . $lines( 25, 38, 36, 2 );

		case 'placeholder_style:type':
		case 'placeholder_style:site':
		case 'placeholder_style:original':
			$tiles = array(
				'type'     => array( ' style="fill:#ccfbf1"', ' style="fill:#ffe4e6"', ' style="fill:#ede9fe"' ),
				'site'     => array( ' style="fill:var(--tt2f-accent);opacity:.18"', ' style="fill:var(--tt2f-brand);opacity:.18"', ' style="fill:var(--tt2f-accent);opacity:.3"' ),
				'original' => array( '', '', '' ),
			);
			$inks  = array(
				'type'     => array( ' style="fill:#0f766e"', ' style="fill:#be123c"', ' style="fill:#7c3aed"' ),
				'site'     => array( ' style="fill:var(--tt2f-accent)"', ' style="fill:var(--tt2f-brand)"', ' style="fill:var(--tt2f-accent)"' ),
				'original' => array( ' style="fill:#b8bfca"', ' style="fill:#b8bfca"', ' style="fill:#b8bfca"' ),
			);
			$out   = '';
			foreach ( array( 10, 37, 64 ) as $i => $x ) {
				$out .= $r( $x, 12, 22, 30, 's', 3, $tiles[ $choice ][ $i ] );
				if ( 'original' === $choice ) {
					$out .= $r( $x + 6, 21, 10, 10, 's', 1.5, $inks[ $choice ][ $i ] );
				} else {
					$out .= $r( $x + 5, 17, 12, 12, 'w', 3.5 ) . $r( $x + 8, 21, 6, 4, 's', 1, $inks[ $choice ][ $i ] ) . $r( $x + 6, 34, 10, 2, 's', 1, $inks[ $choice ][ $i ] );
				}
			}
			return $out;

		case 'drawer_side:right':
			return $cards( 7, 7, 4, 3, 13 ) . $r( 58, 4.5, 34, 54, 'sh', 3 ) . $r( 60, 3, 34, 54, 'w', 3 ) . $r( 64, 8, 26, 16, 'k', 2 ) . $lines( 64, 29, 25, 4 );
		case 'drawer_side:left':
			return $cards( 41, 7, 4, 3, 13 ) . $r( 4, 4.5, 34, 54, 'sh', 3 ) . $r( 2, 3, 34, 54, 'w', 3 ) . $r( 6, 8, 26, 16, 'k', 2 ) . $lines( 6, 29, 25, 4 );

		case 'drawer_media:contain':
			return $r( 26, 3, 44, 54, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 30, 7, 36, 24, 's', 2 ) . $r( 39, 9, 18, 20, 'k', 1 ) . $lines( 30, 36, 34, 4 );
		case 'drawer_media:cover':
			return $r( 26, 3, 44, 54, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 30, 7, 36, 24, 'k', 2 ) . $lines( 30, 36, 34, 4 );
		case 'drawer_media:none':
			return $r( 26, 3, 44, 54, 'w', 3, ' stroke="#dfe3ea"' ) . $r( 30, 8, 30, 4, 't', 2 ) . $lines( 30, 18, 34, 7 );

		case 'item_chips:colored':
			return $r( 8, 12, 20, 2.4, 'm', 1.2 ) . $r( 34, 9, 18, 8, 'b1 o', 4 ) . $r( 55, 9, 22, 8, 'b2 o', 4 ) . $r( 8, 27, 20, 2.4, 'm', 1.2 ) . $r( 34, 24, 24, 8, 'b3 o', 4 ) . $r( 61, 24, 16, 8, 'b4 o', 4 ) . $r( 8, 42, 20, 2.4, 'm', 1.2 ) . $r( 34, 42, 46, 2.4, 't', 1.2 );
		case 'item_chips:mono':
			return $r( 8, 12, 20, 2.4, 'm', 1.2 ) . $r( 34, 9, 18, 8, 'c o', 4 ) . $r( 55, 9, 22, 8, 'c o', 4 ) . $r( 8, 27, 20, 2.4, 'm', 1.2 ) . $r( 34, 24, 24, 8, 'c o', 4 ) . $r( 61, 24, 16, 8, 'c o', 4 ) . $r( 8, 42, 20, 2.4, 'm', 1.2 ) . $r( 34, 42, 46, 2.4, 't', 1.2 );
		case 'item_chips:plain':
			return $r( 8, 12, 20, 2.4, 'm', 1.2 ) . $r( 34, 12, 40, 2.4, 't', 1.2 ) . $r( 8, 27, 20, 2.4, 'm', 1.2 ) . $r( 34, 27, 36, 2.4, 't', 1.2 ) . $r( 8, 42, 20, 2.4, 'm', 1.2 ) . $r( 34, 42, 46, 2.4, 't', 1.2 );

		case 'item_labels:side':
			return $r( 8, 10, 22, 2.4, 'm', 1.2 ) . $r( 36, 10, 50, 2.4, 't', 1.2 ) . $r( 8, 24, 22, 2.4, 'm', 1.2 ) . $r( 36, 24, 40, 2.4, 't', 1.2 ) . $r( 8, 38, 22, 2.4, 'm', 1.2 ) . $r( 36, 38, 46, 2.4, 't', 1.2 );
		case 'item_labels:above':
			return $r( 8, 7, 22, 2.4, 'm', 1.2 ) . $r( 8, 12, 60, 2.4, 't', 1.2 ) . $r( 8, 23, 22, 2.4, 'm', 1.2 ) . $r( 8, 28, 50, 2.4, 't', 1.2 ) . $r( 8, 39, 22, 2.4, 'm', 1.2 ) . $r( 8, 44, 66, 2.4, 't', 1.2 );

		case 'item_columns:1':
			return $r( 8, 8, 20, 2.4, 'm', 1.2 ) . $r( 32, 8, 50, 2.4, 't', 1.2 ) . $r( 8, 19, 20, 2.4, 'm', 1.2 ) . $r( 32, 19, 44, 2.4, 't', 1.2 ) . $r( 8, 30, 20, 2.4, 'm', 1.2 ) . $r( 32, 30, 50, 2.4, 't', 1.2 ) . $r( 8, 41, 20, 2.4, 'm', 1.2 ) . $r( 32, 41, 40, 2.4, 't', 1.2 );
		case 'item_columns:2':
			return $r( 8, 12, 14, 2.4, 'm', 1.2 ) . $r( 25, 12, 20, 2.4, 't', 1.2 ) . $r( 51, 12, 14, 2.4, 'm', 1.2 ) . $r( 68, 12, 20, 2.4, 't', 1.2 ) . $r( 8, 26, 14, 2.4, 'm', 1.2 ) . $r( 25, 26, 18, 2.4, 't', 1.2 ) . $r( 51, 26, 14, 2.4, 'm', 1.2 ) . $r( 68, 26, 16, 2.4, 't', 1.2 );

		case 'hero_style:banner':
			return $r( 0, 0, 96, 34, 'k', 0 ) . '<path class="w o" d="M0 34 L22 18 L36 27 L54 13 L96 34 Z"/>' . $r( 10, 14, 40, 5, 'w', 2 ) . $lines( 10, 42, 76, 3 );
		case 'hero_style:plain':
			return $r( 0, 0, 96, 30, 's', 0 ) . $r( 10, 12, 40, 5, 't', 2 ) . $lines( 10, 38, 76, 3 );
		case 'hero_style:none':
			return $r( 10, 10, 40, 5, 't', 2 ) . $lines( 10, 24, 76, 5 );

		case 'hero_align:start':
			return $r( 0, 0, 96, 34, 'k', 0 ) . $r( 10, 13, 40, 5, 'w', 2 ) . $r( 10, 22, 26, 2.4, 'w o', 1.2 ) . $lines( 10, 42, 76, 3 );
		case 'hero_align:center':
			return $r( 0, 0, 96, 34, 'k', 0 ) . $r( 28, 13, 40, 5, 'w', 2 ) . $r( 35, 22, 26, 2.4, 'w o', 1.2 ) . $lines( 10, 42, 76, 3 );

		case 'page_title_style:editorial':
			return $r( 10, 8, 14, 2, 'c', 1 ) . $r( 10, 14, 56, 7, 'k', 2 ) . $r( 10, 25, 20, 5, 'c o', 2.5 ) . $r( 10, 34, 76, 0.8, 'm', 0 ) . $r( 10, 34, 12, 1.5, 'c', 0 ) . $cards( 10, 40, 5, 1, 13 );
		case 'page_title_style:centered':
			return $r( 20, 12, 56, 7, 'k', 2 ) . $r( 38, 23, 20, 5, 'c o', 2.5 ) . $cards( 10, 36, 5, 1, 13 );
		case 'page_title_style:compact':
			return $r( 10, 10, 36, 4.5, 'k', 2 ) . $r( 50, 10, 14, 4.5, 'c o', 2 ) . $cards( 10, 22, 5, 2, 13 );

		case 'blog_layout:list':
			return $r( 8, 6, 24, 14, 'k o', 2 ) . $lines( 36, 8, 50, 3 ) . $r( 8, 24, 24, 14, 'c o', 2 ) . $lines( 36, 26, 50, 3 ) . $r( 8, 42, 24, 14, 'k o', 2 ) . $lines( 36, 44, 50, 3 );
		case 'blog_layout:grid':
			return $r( 8, 8, 24, 16, 'k o', 2 ) . $r( 36, 8, 24, 16, 'c o', 2 ) . $r( 64, 8, 24, 16, 'k o', 2 ) . $lines( 8, 28, 22, 3 ) . $lines( 36, 28, 22, 3 ) . $lines( 64, 28, 22, 3 );

		case 'sidebar_position:right':
			return $lines( 8, 10, 54, 8 ) . $r( 68, 8, 20, 40, 's', 3 );
		case 'sidebar_position:left':
			return $r( 8, 8, 20, 40, 's', 3 ) . $lines( 34, 10, 54, 8 );
		case 'sidebar_position:none':
			return $lines( 8, 10, 80, 8 );

		case 'post_width:normal':
			return $r( 8, 8, 50, 6, 'k', 2 ) . $lines( 8, 22, 80, 6 );
		case 'post_width:narrow':
			return $r( 24, 8, 40, 6, 'k', 2 ) . $lines( 24, 22, 48, 6 );

		case 'back_top_shape:circle':
		case 'back_top_shape:rounded':
		case 'back_top_shape:square':
		case 'back_top_side:right':
		case 'back_top_side:left':
			$radius = array(
				'circle'  => 7,
				'rounded' => 3.5,
				'square'  => 0,
			);
			$rx     = 'back_top_shape' === $key ? $radius[ $choice ] : 7;
			$x      = 'back_top_side:left' === $key . ':' . $choice ? 8 : 74;
			return $lines( 8, 8, 80, 7 ) . $r( $x, 40, 14, 14, 'c', $rx ) . '<path d="M' . ( $x + 3.5 ) . ' 49 l3.5 -3.5 l3.5 3.5" stroke="#fff" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
	}
	return '';
}

/**
 * Sketch of the page of a section, with the part it changes marked: kind of
 * page and the parts to mark.
 *
 * @return array Section id => array( page, parts ).
 */
function tainacan_interface_section_maps() {
	return array(
		'site_header'   => array( 'list', array( 'header' ) ),
		'header_search' => array( 'list', array( 'search_icon' ) ),
		'breadcrumb'    => array( 'item', array( 'crumb' ) ),
		'footer'        => array( 'list', array( 'footer' ) ),
		'social'        => array( 'list', array( 'social_head', 'social_foot' ) ),
		'share'         => array( 'item', array( 'share' ) ),
		'extras'        => array( 'list', array( 'back_top' ) ),
		'collections'   => array( 'collections', array( 'grid' ) ),
		'collection'    => array( 'list', array( 'head' ) ),
		'cards'         => array( 'list', array( 'cards' ) ),
		'items_search'  => array( 'list', array( 'search' ) ),
		'filters'       => array( 'list', array( 'filters' ) ),
		'pagination'    => array( 'list', array( 'pagination' ) ),
		'drawer'        => array( 'list', array( 'drawer' ) ),
		'item'          => array( 'item', array( 'title', 'metadata' ) ),
		'document'      => array( 'item', array( 'document' ) ),
		'aside'         => array( 'item', array( 'aside' ) ),
		'related'       => array( 'item', array( 'related' ) ),
		'item_nav'      => array( 'item', array( 'nav' ) ),
		'hero'          => array( 'post', array( 'hero' ) ),
		'page_title'    => array( 'blog', array( 'title' ) ),
		'blog'          => array( 'blog', array( 'posts' ) ),
		'post'          => array( 'post', array( 'text' ) ),
		'sidebar'       => array( 'post', array( 'sidebar' ) ),
	);
}

/**
 * Parts of each kind of page in the sketch: name => array( x, y, w, h, class ).
 * Parts with no class are drawn only when marked.
 *
 * @param string $page Kind of page.
 * @return array
 */
function tainacan_interface_section_map_parts( $page ) {
	$frame = array(
		'header'      => array( 0, 0, 160, 10, 'k' ),
		'search_icon' => array( 148, 3, 6, 4, 'w o' ),
		'social_head' => array( 124, 3.5, 18, 3, '' ),
		'footer'      => array( 0, 82, 160, 8, 'k' ),
		'social_foot' => array( 68, 84.5, 24, 3, '' ),
		'back_top'    => array( 146, 68, 8, 8, '' ),
	);
	$pages = array(
		'list'        => array(
			'head'       => array( 8, 14, 144, 16, 'm' ),
			'search'     => array( 48, 34, 104, 6, 's' ),
			'filters'    => array( 8, 34, 36, 40, 's' ),
			'cards'      => array( 48, 43, 104, 26, 's' ),
			'pagination' => array( 48, 72, 104, 4, 's' ),
			'drawer'     => array( 108, 10, 52, 72, '' ),
		),
		'item'        => array(
			'crumb'    => array( 8, 13, 56, 3, 'm' ),
			'nav'      => array( 128, 13, 24, 3, 'm' ),
			'title'    => array( 8, 19, 80, 5, 't' ),
			'share'    => array( 136, 19, 16, 5, '' ),
			'document' => array( 8, 27, 98, 26, 's' ),
			'metadata' => array( 8, 56, 98, 15, 's' ),
			'aside'    => array( 110, 27, 42, 44, 's' ),
			'related'  => array( 8, 74, 144, 5, 's' ),
		),
		'collections' => array(
			'title' => array( 8, 14, 60, 6, 't' ),
			'grid'  => array( 8, 24, 144, 42, 's' ),
			'terms' => array( 8, 70, 144, 8, 's' ),
		),
		'post'        => array(
			'hero'    => array( 0, 10, 160, 22, 'm' ),
			'text'    => array( 8, 36, 104, 42, 's' ),
			'sidebar' => array( 118, 36, 34, 42, 's' ),
		),
		'blog'        => array(
			'title'   => array( 8, 14, 104, 9, 't' ),
			'posts'   => array( 8, 27, 104, 51, 's' ),
			'sidebar' => array( 118, 14, 34, 64, 's' ),
		),
	);
	return array_merge( $frame, isset( $pages[ $page ] ) ? $pages[ $page ] : array() );
}

/**
 * Sketch of the page for a section, or ''.
 *
 * @param string $section Section id, without the prefix.
 * @return string SVG, constant markup built in this file.
 */
function tainacan_interface_section_map( $section ) {
	$maps = tainacan_interface_section_maps();
	if ( ! isset( $maps[ $section ] ) ) {
		return '';
	}
	list( $page, $marked ) = $maps[ $section ];

	$parts = tainacan_interface_section_map_parts( $page );
	$out   = tainacan_interface_cform_rect( 0, 0, 160, 90, 'pg', 4 );
	foreach ( $parts as $name => $part ) {
		if ( '' !== $part[4] ) {
			$out .= tainacan_interface_cform_rect( $part[0], $part[1], $part[2], $part[3], $part[4], 1.5 );
		}
	}
	foreach ( $marked as $name ) {
		// One unit around the part, inside the frame so the edge is not cut.
		$part = $parts[ $name ];
		$x    = max( 1, $part[0] - 1 );
		$y    = max( 1, $part[1] - 1 );
		$out .= tainacan_interface_cform_rect( $x, $y, min( 159, $part[0] + $part[2] + 1 ) - $x, min( 89, $part[1] + $part[3] + 1 ) - $y, 'hl', 2.5 );
	}
	return '<svg viewBox="0 0 160 90" aria-hidden="true" focusable="false">' . $out . '</svg>';
}

/**
 * Text at the top of an open section: the sketch of where it is on the page
 * and the help.
 *
 * @param string $section Section id, without the prefix.
 * @param string $help    Help text.
 * @return string HTML.
 */
function tainacan_interface_section_intro( $section, $help ) {
	$map = tainacan_interface_section_map( $section );
	$out = '';
	if ( '' !== $map ) {
		$out .= '<span class="tt2-cform tt2-cform--customizer tt2-map"><span class="tt2-map__label">' . esc_html__( 'Where it is on the page', 'tainacan-interface' ) . '</span>' . wp_kses( $map, tainacan_interface_cform_svg_tags() ) . '</span>';
	}
	if ( '' !== $help ) {
		$out .= '<span class="tt2-map__help">' . esc_html( $help ) . '</span>';
	}
	return $out;
}
