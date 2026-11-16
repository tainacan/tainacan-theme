<?php
/**
 * Links to the social network profiles of the institution, in the header or the
 * footer of the design of version 4. The icons are inline SVG from Tabler Icons
 * (MIT, see readme.txt): no file and no request for them.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The networks: key => array( name, paths of the icon ).
 *
 * @return array
 */
function tainacan_interface_social_networks() {
	return array(
		'facebook'  => array( 'Facebook', array( 'M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3' ) ),
		'instagram' => array( 'Instagram', array( 'M4 8a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z', 'M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0', 'M16.5 7.5v.01' ) ),
		'x'         => array( 'X', array( 'M4 4l11.733 16h4.267l-11.733 -16z', 'M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772' ) ),
		'youtube'   => array( 'YouTube', array( 'M2 8a4 4 0 0 1 4 -4h12a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-12a4 4 0 0 1 -4 -4v-8z', 'M10 9l5 3l-5 3z' ) ),
		'linkedin'  => array( 'LinkedIn', array( 'M8 11v5', 'M8 8v.01', 'M12 16v-5', 'M16 16v-3a2 2 0 1 0 -4 0', 'M3 7a4 4 0 0 1 4 -4h10a4 4 0 0 1 4 4v10a4 4 0 0 1 -4 4h-10a4 4 0 0 1 -4 -4z' ) ),
		'tiktok'    => array( 'TikTok', array( 'M21 7.917v4.034a9.948 9.948 0 0 1 -5 -1.951v4.5a6.5 6.5 0 1 1 -8 -6.326v4.326a2.5 2.5 0 1 0 4 2v-11.5h4.083a6.005 6.005 0 0 0 4.917 4.917z' ) ),
		'bluesky'   => array( 'Bluesky', array( 'M6.335 5.144c-1.654 -1.199 -4.335 -2.127 -4.335 .826c0 .59 .35 4.953 .556 5.661c.713 2.463 3.13 2.75 5.444 2.369c-4.045 .665 -4.889 3.208 -2.667 5.41c1.03 1.018 1.913 1.59 2.667 1.59c2 0 3.134 -2.769 3.5 -3.5c.333 -.667 .5 -1.167 .5 -1.5c0 .333 .167 .833 .5 1.5c.366 .731 1.5 3.5 3.5 3.5c.754 0 1.637 -.571 2.667 -1.59c2.222 -2.203 1.378 -4.746 -2.667 -5.41c2.314 .38 4.73 .094 5.444 -2.369c.206 -.708 .556 -5.072 .556 -5.661c0 -2.953 -2.68 -2.025 -4.335 -.826c-2.293 1.662 -4.76 5.048 -5.665 6.856c-.905 -1.808 -3.372 -5.194 -5.665 -6.856z' ) ),
		'threads'   => array( 'Threads', array( 'M19 7.5c-1.333 -3 -3.667 -4.5 -7 -4.5c-5 0 -8 2.5 -8 9s3.5 9 8 9s7 -3 7 -5s-1 -5 -7 -5c-2.5 0 -3 1.25 -3 2.5c0 1.5 1 2.5 2.5 2.5c2.5 0 3.5 -1.5 3.5 -5s-2 -4 -3 -4s-1.833 .333 -2.5 1' ) ),
	);
}

/**
 * Prints the links of the profiles filled in, when this place shows them.
 *
 * @param string $place header or footer.
 */
function tainacan_interface_the_social_links( $place ) {
	if ( ! tainacan_interface_is_modern() || ! in_array( tainacan_interface_opt( 'social_place' ), array( $place, 'both' ), true ) ) {
		return;
	}

	$new_tab = tainacan_interface_opt( 'social_new_tab' );
	$links   = '';
	foreach ( tainacan_interface_social_networks() as $key => $network ) {
		$url = (string) tainacan_interface_opt( 'social_' . $key );
		if ( '' === $url ) {
			continue;
		}
		$icon = '';
		foreach ( $network[1] as $path ) {
			$icon .= '<path d="' . esc_attr( $path ) . '"/>';
		}
		$links .= sprintf(
			'<li><a href="%1$s" aria-label="%2$s"%3$s><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg></a></li>',
			esc_url( $url ),
			esc_attr( $network[0] ),
			$new_tab ? ' target="_blank" rel="noopener me"' : ' rel="me"',
			$icon
		);
	}

	if ( '' === $links ) {
		return;
	}
	echo wp_kses(
		'<ul class="tt2-social tt2-social--' . esc_attr( $place ) . '">' . $links . '</ul>',
		array(
			'ul'   => array( 'class' => true ),
			'li'   => array(),
			'a'    => array(
				'href'       => true,
				'aria-label' => true,
				'target'     => true,
				'rel'        => true,
			),
			'svg'  => array(
				'viewbox'         => true,
				'width'           => true,
				'height'          => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'aria-hidden'     => true,
				'focusable'       => true,
			),
			'path' => array( 'd' => true ),
		)
	);
}
