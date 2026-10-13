<?php
/**
 * Connect the existing Tainacan media gallery to the DearFlip shortcode.
 *
 * @package TainacanDearFlip
 */

namespace Tainacan\DearFlip;

defined( 'ABSPATH' ) || exit;

class Integration {
	const OPTION = 'tainacan_dearflip_enabled';

	/** Register hooks without depending on the order in which plugins load. */
	public static function register() {
		add_filter( 'tainacan-item-get-attachment-as-html', array( __CLASS__, 'attachment_html' ), 20, 4 );
		add_filter( 'tainacan-get-attachment-html-url', array( __CLASS__, 'attachment_url' ), 20, 2 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'wp_kses_allowed_html', array( __CLASS__, 'allowed_html' ), 20, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'viewer' ), 0 );
		add_action( 'admin_menu', array( __CLASS__, 'settings_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'settings_register' ) );
		add_action( 'customize_register', array( __CLASS__, 'customize_register' ), 100 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'gallery_style' ), 20 );
	}

	/** DearFlip only registers its shortcode on frontend requests, not admin screens. */
	public static function dependencies_available() {
		return class_exists( '\Tainacan\Entities\Item' ) && ( class_exists( '\DFlip' ) || shortcode_exists( 'dflip' ) );
	}

	/** Whether both runtime dependencies are available. */
	public static function ready() {
		return self::dependencies_available() && shortcode_exists( 'dflip' );
	}

	/** Size only integration iframes; native media retain their existing styles. */
	public static function gallery_style() {
		if ( self::enabled() ) {
			wp_enqueue_style( 'tainacan-dearflip-gallery', plugins_url( 'assets/gallery.css', dirname( __DIR__ ) . '/tainacan-dearflip.php' ), array(), '0.1.0' );
		}
	}

	/** Integration is opt-in and frontend-only. */
	private static function enabled() {
		return (bool) get_option( self::OPTION, false ) && self::ready()
			&& ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/** Confirm the PDF belongs to the requested item before exposing its viewer. */
	public static function valid_attachment( $attachment_id, $item ) {
		$attachment = get_post( $attachment_id );
		if ( ! $item instanceof \Tainacan\Entities\Item || ! $attachment instanceof \WP_Post
			|| 'attachment' !== $attachment->post_type || 'application/pdf' !== $attachment->post_mime_type ) {
			return false;
		}
		return (int) $attachment->post_parent === (int) $item->get_id()
			|| ( 'attachment' === $item->get_document_type() && (int) $item->get_document() === (int) $attachment_id );
	}

	/** Respect Tainacan permissions and WordPress password protection. */
	private static function can_read( $item ) {
		return $item->can_read() && ! post_password_required( get_post( $item->get_id() ) );
	}

	/** Construct a same-origin viewer URL; no arbitrary PDF URL is accepted. */
	public static function viewer_url( $attachment_id, $item_id ) {
		return add_query_arg( array(
			'tainacan_dearflip_attachment' => (int) $attachment_id,
			'tainacan_dearflip_item'       => (int) $item_id,
		), home_url( '/' ) );
	}

	/** Replace only eligible PDF HTML. Older Tainacan filters omit the fourth argument. */
	public static function attachment_html( $html, $size, $item, $attachment_id = 0 ) {
		if ( ! self::enabled() || ! self::valid_attachment( $attachment_id, $item ) || ! self::can_read( $item ) ) {
			return $html;
		}
		return sprintf(
			'<div class="tainacan-embed-container"><iframe src="%s" title="%s" width="100%%" height="640" loading="lazy" allowfullscreen></iframe></div>',
			esc_url( self::viewer_url( $attachment_id, $item->get_id() ) ),
			esc_attr( get_the_title( $attachment_id ) )
		);
	}

	/** Cover attachment lightboxes, including galleries with only thumbnails. */
	public static function attachment_url( $url, $attachment_id ) {
		if ( ! self::enabled() ) {
			return $url;
		}
		$attachment = get_post( $attachment_id );
		if ( ! $attachment instanceof \WP_Post ) {
			return $url;
		}
		$item = tainacan_get_item( $attachment->post_parent );
		if ( ! self::valid_attachment( $attachment_id, $item ) || ! self::can_read( $item ) ) {
			return $url;
		}
		return self::viewer_url( $attachment_id, $item->get_id() );
	}

	/** Keep iframe accessibility and lazy-loading attributes through gallery sanitization. */
	public static function allowed_html( $allowed, $context ) {
		if ( in_array( $context, array( 'tainacan_content', 'tainacan_media_slide' ), true ) && isset( $allowed['iframe'] ) ) {
			$allowed['iframe']['title'] = true;
			$allowed['iframe']['loading'] = true;
		}
		return $allowed;
	}

	public static function query_vars( $vars ) {
		$vars[] = 'tainacan_dearflip_attachment';
		$vars[] = 'tainacan_dearflip_item';
		return $vars;
	}

	/** Lite needs an explicit source; an attachment ID alone is treated as a flipbook post. */
	public static function shortcode_content( $attachment_id ) {
		return do_shortcode( sprintf(
			'[dflip source="%s" id="tainacan-pdf-%d"][/dflip]',
			esc_url( wp_get_attachment_url( $attachment_id ) ), (int) $attachment_id
		) );
	}

	/** Render a complete document so each lightbox instance initializes independently. */
	public static function viewer() {
		$attachment_id = get_query_var( 'tainacan_dearflip_attachment', '' );
		if ( '' === $attachment_id ) {
			return;
		}
		$item_id = get_query_var( 'tainacan_dearflip_item', '' );
		if ( ! is_scalar( $attachment_id ) || ! is_scalar( $item_id )
			|| ! preg_match( '/^[1-9][0-9]*$/', (string) $attachment_id )
			|| ! preg_match( '/^[1-9][0-9]*$/', (string) $item_id ) || ! function_exists( 'tainacan_get_item' ) ) {
			wp_die( esc_html__( 'PDF not found.', 'tainacan-dearflip' ), '', array( 'response' => 404 ) );
		}
		$item = tainacan_get_item( (int) $item_id );
		if ( ! self::valid_attachment( (int) $attachment_id, $item ) ) {
			wp_die( esc_html__( 'PDF not found.', 'tainacan-dearflip' ), '', array( 'response' => 404 ) );
		}
		if ( ! self::can_read( $item ) ) {
			wp_die( esc_html__( 'You cannot read this item.', 'tainacan-dearflip' ), '', array( 'response' => 403 ) );
		}
		nocache_headers();
		status_header( 200 );
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'X-Frame-Options: SAMEORIGIN', true );
		header( "Content-Security-Policy: frame-ancestors 'self'", true );
		show_admin_bar( false );

		// Run before wp_head so selective DearFlip styles are queued before printing.
		$content = self::enabled() ? self::shortcode_content( (int) $attachment_id ) : '';
		if ( '' === trim( $content ) ) {
			$content = sprintf( '<iframe class="browser-pdf" src="%s" title="%s" allowfullscreen></iframe>',
				esc_url( wp_get_attachment_url( $attachment_id ) ), esc_attr( get_the_title( $attachment_id ) ) );
		}
		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( get_the_title( $attachment_id ) ); ?></title>
			<?php wp_head(); ?>
			<style>
				html, body.tainacan-dearflip-viewer { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; }
				body.tainacan-dearflip-viewer ._df_book, .browser-pdf { width: 100%; height: 100vh !important; margin: 0; border: 0; }
			</style>
		</head>
		<body class="tainacan-dearflip-viewer">
			<?php
			// Trusted plugin shortcode output includes the DearFlip initialization scripts.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $content;
			wp_footer();
			?>
		</body>
		</html>
		<?php
		exit;
	}

	public static function sanitize_enabled( $value ) {
		return empty( $value ) ? 0 : 1;
	}

	public static function settings_register() {
		register_setting( 'tainacan_dearflip', self::OPTION, array(
			'type' => 'boolean', 'default' => false,
			'sanitize_callback' => array( __CLASS__, 'sanitize_enabled' ),
		) );
	}

	public static function settings_menu() {
		add_options_page( 'Tainacan DearFlip', 'Tainacan DearFlip', 'manage_options', 'tainacan-dearflip', array( __CLASS__, 'settings_page' ) );
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tainacan DearFlip', 'tainacan-dearflip' ); ?></h1>
			<?php if ( ! self::dependencies_available() ) : ?>
				<p><?php esc_html_e( 'Activate Tainacan and DearFlip to use this integration. The installed Tainacan must support the attachment ID and viewer URL filters described in the README.', 'tainacan-dearflip' ); ?></p>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php settings_fields( 'tainacan_dearflip' ); ?>
				<input type="hidden" name="<?php echo esc_attr( self::OPTION ); ?>" value="0">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>" value="1" <?php checked( get_option( self::OPTION, false ) ); ?>>
					<?php esc_html_e( 'Use DearFlip for Tainacan PDF documents and attachments', 'tainacan-dearflip' ); ?>
				</label>
				<p><?php esc_html_e( 'Keep configuring the flipbook appearance in DearFlip. Images, audio, video and external URL documents keep their current viewers.', 'tainacan-dearflip' ); ?></p>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** Add the same option to the theme Customizer without editing the theme. */
	public static function customize_register( $customizer ) {
		if ( ! self::dependencies_available() || ! $customizer->get_section( 'tainacan_single_item_page_document' ) ) {
			return;
		}
		$customizer->add_setting( self::OPTION, array(
			'type' => 'option', 'default' => false, 'capability' => 'manage_options',
			'transport' => 'refresh', 'sanitize_callback' => array( __CLASS__, 'sanitize_enabled' ),
		) );
		$customizer->add_control( self::OPTION, array(
			'type' => 'checkbox', 'section' => 'tainacan_single_item_page_document', 'priority' => 5,
			'label' => __( 'Use DearFlip for PDF documents and attachments', 'tainacan-dearflip' ),
		) );
	}
}
