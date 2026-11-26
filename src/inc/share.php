<?php
/**
 * Share links.
 *
 * The original templates glued the permalink and the title into the share URL
 * as they came. A term named x" onmouseover="... broke out of the href (term
 * names keep their quotes in the database), and an & or # in a title added
 * parameters to the share intent. Here every value is URL-encoded and the
 * whole URL is escaped by the caller with esc_url().
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Share URL for a network.
 *
 * @param string $network facebook|twitter|whatsapp|telegram.
 * @param string $url     Address being shared.
 * @param string $text    Title being shared (plain text or with entities).
 * @return string Unescaped URL; escape with esc_url() on output.
 */
function tainacan_interface_share_url( $network, $url, $text = '' ) {
	$url  = (string) $url;
	$text = tainacan_interface_plain( $text );

	switch ( $network ) {
		case 'facebook':
			return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url );
		case 'twitter':
			$via   = sanitize_text_field( (string) get_theme_mod( 'tainacan_twitter_user', '' ) );
			$query = 'url=' . rawurlencode( $url ) . ( '' !== $text ? '&text=' . rawurlencode( $text ) : '' ) . ( '' !== $via ? '&via=' . rawurlencode( ltrim( $via, '@' ) ) : '' );
			return 'https://twitter.com/intent/tweet?' . $query;
		case 'whatsapp':
			return 'https://api.whatsapp.com/send?text=' . rawurlencode( '' !== $text ? $text . ' ' . $url : $url );
		case 'telegram':
			return 'https://t.me/share/url?url=' . rawurlencode( $url ) . ( '' !== $text ? '&text=' . rawurlencode( $text ) : '' );
	}

	return '';
}

/**
 * Prints the share buttons, in one of the two looks of the original theme.
 *
 * @param string $url   Address being shared.
 * @param string $text  Title being shared.
 * @param string $style 'icons' (round icon buttons that unfold) or 'images' (row of PNG icons).
 * @param string $link_class Class of each link in the 'images' style.
 */
function tainacan_interface_the_share_links( $url, $text = '', $style = 'icons', $link_class = 'share-link' ) {
	$networks = array(
		'facebook' => array( 'tainacan_facebook_share', true, __( 'Share this on facebook', 'tainacan-interface' ) ),
		'twitter'  => array( 'tainacan_twitter_share', true, __( 'Share this on twitter', 'tainacan-interface' ) ),
		'whatsapp' => array( 'tainacan_whatsapp_share', false, __( 'Share this on WhatsApp', 'tainacan-interface' ) ),
		'telegram' => array( 'tainacan_telegram_share', false, __( 'Share this on Telegram', 'tainacan-interface' ) ),
	);
	$images   = get_template_directory_uri() . '/assets/images/';

	foreach ( $networks as $network => $setting ) {
		if ( ! get_theme_mod( $setting[0], $setting[1] ) ) {
			continue;
		}
		// The round buttons of the original showed Twitter only when a Twitter user was set.
		if ( 'twitter' === $network && 'icons' === $style && '' === (string) get_theme_mod( 'tainacan_twitter_user', '' ) ) {
			continue;
		}
		$href = tainacan_interface_share_url( $network, $url, $text );
		if ( 'icons' === $style ) {
			?>
			<div class="rotater">
				<a href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $setting[2] ); ?>">
					<div class="btn btn-icon"><i class="tainacan-icon tainacan-icon-<?php echo esc_attr( $network ); ?>"></i></div>
				</a>
			</div>
			<?php
		} else {
			?>
			<a href="<?php echo esc_url( $href ); ?>" class="<?php echo esc_attr( $link_class ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $setting[2] ); ?>">
				<img src="<?php echo esc_url( $images . $network . '-circle.png' ); ?>" alt="<?php echo esc_attr( $setting[2] ); ?>" class="share-images">
			</a>
			<?php
		}
	}

	if ( 'icons' === $style ) {
		?>
		<div class="rotater">
			<button type="button" data-link="<?php echo esc_url( $url ); ?>" title="<?php esc_attr_e( 'Copy link', 'tainacan-interface' ); ?>" class="tainacan-copy-link-button">
				<div class="btn btn-icon"><i class="tainacan-icon tainacan-icon-url"></i></div>
			</button>
		</div>
		<?php
	} else {
		?>
		<button type="button" data-link="<?php echo esc_url( $url ); ?>" title="<?php esc_attr_e( 'Copy link', 'tainacan-interface' ); ?>" class="<?php echo esc_attr( $link_class ); ?> tainacan-copy-link-button">
			<img src="<?php echo esc_url( $images . 'url-circle.png' ); ?>" alt="<?php esc_attr_e( 'Copy link', 'tainacan-interface' ); ?>" class="share-images">
		</button>
		<?php
	}
}

/**
 * Address of the page being viewed, for the share buttons of archive pages.
 *
 * @return string
 */
function tainacan_interface_current_url() {
	global $wp;
	return home_url( isset( $wp->request ) ? user_trailingslashit( $wp->request ) : '/' );
}
