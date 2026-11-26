<?php
/**
 * What the settings of Blocksy become in this theme.
 *
 * Reads the theme mods of Blocksy (from this site or from the file the Blocksy
 * Companion exports) and writes a plan: for every topic, what Blocksy had, what
 * Tainacan Interface gets and how close it is (exact, approximate, an option of
 * this theme turned off to keep the look, or no equivalent). Nothing is written
 * here; the import applies the plan.
 *
 * A setting Blocksy never saved still has its default, and the default is what
 * the visitor saw: the plan reads Blocksy's defaults for the settings it maps
 * (Blocksy 2.1 and Tainacan Support for Blocksy 0.4), so a site that never
 * touched, say, the back-to-top button keeps it off here too.
 *
 * Everything the plan does not read is listed at the end (left_out), grouped,
 * so the administrator sees what had no place in this theme.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The plan of an import.
 */
class Tainacan_Interface_Blocksy_Map {

	/**
	 * Theme mods of Blocksy.
	 *
	 * @var array
	 */
	private $mods;

	/**
	 * Keys of $mods the plan read.
	 *
	 * @var array<string, bool>
	 */
	private $used = array();

	/**
	 * Blocksy palette, 1-based, resolved to hex.
	 *
	 * @var array<int, string>
	 */
	public $palette = array();

	/**
	 * Groups of the plan: id => title, text, rows.
	 *
	 * @var array
	 */
	public $groups = array();

	/**
	 * Theme mods to write, by group.
	 *
	 * @var array<string, array>
	 */
	public $set = array();

	/**
	 * Post meta of collections to write, by group: collection id => meta => value.
	 *
	 * @var array<string, array>
	 */
	public $meta = array();

	/**
	 * Images the import needs: ref => array( id, url, use ).
	 *
	 * @var array
	 */
	public $images = array();

	/**
	 * Menu locations: location here => menu term id.
	 *
	 * @var array<string, int>
	 */
	public $locations = array();

	/**
	 * Widgets: sidebar here => list of widget ids of this site, or of instances from the file.
	 *
	 * @var array
	 */
	public $widgets = array();

	/**
	 * Additional CSS of Blocksy, or null.
	 *
	 * @var string|null
	 */
	public $css = null;

	/**
	 * Source: kind (site or file), label, site_url.
	 *
	 * @var array
	 */
	public $source;

	/**
	 * Fine adjustments by group: design tokens with the exact value of Blocksy
	 * where an option of this theme only has steps (a color of the header, the
	 * corner of the buttons). They go to the Additional CSS of the import.
	 *
	 * @var array<string, string[]>
	 */
	public $fine = array();

	/**
	 * What the sketch of the preview draws: before (Blocksy) and after.
	 *
	 * @var array
	 */
	public $before = array();

	/**
	 * Keys left out, grouped.
	 *
	 * @var array<string, array>
	 */
	public $left_out = array();

	/**
	 * Whether the source is this same site (attachments, menus and widgets exist here).
	 *
	 * @var bool
	 */
	private $same_site;

	/**
	 * Builds the plan.
	 *
	 * @param array $data Normalized source (see tainacan_interface_blocksy_normalize()).
	 */
	public function __construct( $data ) {
		$this->mods      = isset( $data['mods'] ) && is_array( $data['mods'] ) ? $data['mods'] : array();
		$this->source    = $data;
		$this->same_site = ! empty( $data['same_site'] );
		unset( $this->source['mods'], $this->source['css'], $this->source['widgets'] );

		$this->read_palette();
		$this->colors();
		$this->typography();
		$this->layout();
		$this->buttons();
		$this->header();
		$this->footer();
		$this->social();
		$this->navigation();
		$this->posts();
		$this->tainacan();
		$this->collections();
		$this->menus();
		$this->widgets( isset( $data['widgets'] ) ? $data['widgets'] : null );
		$this->custom_css( isset( $data['css'] ) ? (string) $data['css'] : '' );
		$this->content_palette();
		$this->find_left_out();
	}

	// Reading Blocksy values.

	/**
	 * A Blocksy setting, marked as read.
	 *
	 * @param string $key      Key.
	 * @param mixed  $fallback Blocksy's default.
	 * @return mixed
	 */
	private function get( $key, $fallback = null ) {
		$this->used[ $key ] = true;
		return array_key_exists( $key, $this->mods ) ? $this->mods[ $key ] : $fallback;
	}

	/**
	 * Whether Blocksy saved a setting.
	 *
	 * @param string $key Key.
	 * @return bool
	 */
	private function saved( $key ) {
		return array_key_exists( $key, $this->mods );
	}

	/**
	 * The desktop value of a value that may have one per device.
	 *
	 * @param mixed  $value  Value.
	 * @param string $device desktop, tablet or mobile.
	 * @return mixed
	 */
	private static function device( $value, $device = 'desktop' ) {
		if ( is_array( $value ) && array_key_exists( $device, $value ) ) {
			return $value[ $device ];
		}
		if ( is_array( $value ) && array_key_exists( 'desktop', $value ) ) {
			return $value['desktop'];
		}
		return $value;
	}

	/**
	 * A length in pixels: "16px", 16, "1.2em" (16 px per em), per device.
	 *
	 * @param mixed  $value    Value.
	 * @param string $device   Device.
	 * @param int    $viewport Width used for vw.
	 * @return float|null
	 */
	private static function px( $value, $device = 'desktop', $viewport = 1440 ) {
		$value = self::device( $value, $device );
		if ( is_int( $value ) || is_float( $value ) ) {
			return (float) $value;
		}
		if ( ! is_string( $value ) || ! preg_match( '/^\s*(-?[\d.]+)\s*(px|em|rem|vw|%)?\s*$/', $value, $m ) ) {
			return null;
		}
		$number = (float) $m[1];
		$unit   = isset( $m[2] ) ? $m[2] : 'px';
		if ( 'em' === $unit || 'rem' === $unit ) {
			return $number * 16;
		}
		if ( 'vw' === $unit ) {
			return $number * $viewport / 100;
		}
		return '%' === $unit ? null : $number;
	}

	/**
	 * A Blocksy color (a picker array, a palette variable, hex or rgb) as hex.
	 *
	 * @param mixed $value Value.
	 * @param int   $depth Nesting.
	 * @return string Hex, or '' when there is none.
	 */
	public function color( $value, $depth = 0 ) {
		if ( $depth > 6 ) {
			return '';
		}
		if ( is_array( $value ) ) {
			foreach ( array( 'default', 'initial', 'color', 'backgroundColor' ) as $key ) {
				if ( isset( $value[ $key ] ) ) {
					return $this->color( $value[ $key ], $depth + 1 );
				}
			}
			return '';
		}
		if ( ! is_string( $value ) ) {
			return '';
		}
		$value = trim( $value );
		if ( preg_match( '/^var\(\s*--(?:theme-)?palette-color-(\d+)/', $value, $m ) ) {
			return isset( $this->palette[ (int) $m[1] ] ) ? $this->palette[ (int) $m[1] ] : '';
		}
		$globals = array(
			'--theme-text-color'                => 3,
			'--theme-headings-color'            => 4,
			'--theme-link-initial-color'        => 1,
			'--theme-link-hover-color'          => 2,
			'--theme-border-color'              => 5,
			'--theme-button-text-initial-color' => 8,
		);
		if ( preg_match( '/^var\(\s*(--theme-[a-z-]+)/', $value, $m ) ) {
			return isset( $globals[ $m[1] ], $this->palette[ $globals[ $m[1] ] ] ) ? $this->palette[ $globals[ $m[1] ] ] : '';
		}
		return self::hex( $value );
	}

	/**
	 * Whether a Blocksy color covers what is behind it (no transparency).
	 *
	 * @param mixed $value Picker array or CSS color.
	 * @return bool
	 */
	public function opaque( $value ) {
		while ( is_array( $value ) ) {
			$next = null;
			foreach ( array( 'default', 'initial', 'color', 'backgroundColor' ) as $key ) {
				if ( isset( $value[ $key ] ) ) {
					$next = $value[ $key ];
					break;
				}
			}
			$value = $next;
		}
		$value = strtolower( trim( (string) $value ) );
		if ( '' === $value || 'transparent' === $value || false !== strpos( $value, 'ct_css_skip_rule' ) ) {
			return false;
		}
		if ( preg_match( '/^rgba\([^)]*,\s*([\d.]+)\s*\)$/', $value, $m ) ) {
			return (float) $m[1] >= 0.9;
		}
		if ( preg_match( '/^#[0-9a-f]{6}([0-9a-f]{2})$/', $value, $m ) ) {
			return hexdec( $m[1] ) >= 230;
		}
		return '' !== $this->color( $value );
	}

	/**
	 * A CSS color as #rrggbb (alpha is dropped).
	 *
	 * @param string $value Color.
	 * @return string
	 */
	public static function hex( $value ) {
		$value = strtolower( trim( (string) $value ) );
		if ( preg_match( '/^#([0-9a-f]{3})$/', $value, $m ) ) {
			return '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
		}
		if ( preg_match( '/^#([0-9a-f]{6})([0-9a-f]{2})?$/', $value, $m ) ) {
			return '#' . $m[1];
		}
		if ( preg_match( '/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/', $value, $m ) ) {
			return sprintf( '#%02x%02x%02x', min( 255, (int) $m[1] ), min( 255, (int) $m[2] ), min( 255, (int) $m[3] ) );
		}
		return '';
	}

	/**
	 * Relative luminance of a hex color, 0 to 1.
	 *
	 * @param string $hex Color.
	 * @return float
	 */
	private static function luminance( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 6 !== strlen( $hex ) ) {
			return 1.0;
		}
		$channels = array();
		foreach ( str_split( $hex, 2 ) as $pair ) {
			$c          = hexdec( $pair ) / 255;
			$channels[] = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		}
		return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
	}

	/**
	 * Whether two colors look the same (each channel within 12 of 255).
	 *
	 * @param string $a Color.
	 * @param string $b Color.
	 * @return bool
	 */
	private static function same_color( $a, $b ) {
		$a = ltrim( (string) $a, '#' );
		$b = ltrim( (string) $b, '#' );
		if ( 6 !== strlen( $a ) || 6 !== strlen( $b ) ) {
			return false;
		}
		for ( $i = 0; $i < 6; $i += 2 ) {
			if ( abs( hexdec( substr( $a, $i, 2 ) ) - hexdec( substr( $b, $i, 2 ) ) ) > 12 ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * A Blocksy switch ('yes'/'no', true/false) as a boolean.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function yes( $value ) {
		return true === $value || 'yes' === $value || 1 === $value || '1' === $value;
	}

	/**
	 * Weight of a Blocksy font variation ("n7" is 700).
	 *
	 * @param mixed $variation Variation.
	 * @param int   $fallback  Weight when there is none.
	 * @return int
	 */
	private static function weight( $variation, $fallback ) {
		return is_string( $variation ) && preg_match( '/^[ni]([1-9])$/', $variation, $m ) ? (int) $m[1] * 100 : $fallback;
	}

	/**
	 * Font of this theme for a font family of Blocksy: the same one when the
	 * theme has it, otherwise the nearest in style among the ones it serves.
	 *
	 * @param string $family Family name in Blocksy.
	 * @return array{0: string|null, 1: bool} Slug (null for "inherit") and whether it is the same font.
	 */
	public static function font( $family ) {
		$name = strtolower( trim( (string) $family ) );
		if ( '' === $name || 'default' === $name ) {
			return array( null, true );
		}
		$same = array(
			'poppins'          => 'poppins',
			'roboto'           => 'roboto',
			'inter'            => 'inter',
			'open sans'        => 'open-sans',
			'montserrat'       => 'montserrat',
			'lora'             => 'lora',
			'source serif 4'   => 'source-serif',
			'source serif pro' => 'source-serif',
			'merriweather'     => 'merriweather',
			'playfair display' => 'playfair',
			'georgia'          => 'georgia',
			'system default'   => 'system',
		);
		if ( isset( $same[ $name ] ) ) {
			return array( $same[ $name ], true );
		}
		$near = array(
			'poppins'      => array( 'sen', 'jost', 'outfit', 'urbanist', 'league spartan', 'lexend', 'sora', 'kumbh sans', 'quicksand', 'comfortaa', 'varela round', 'josefin sans', 'dm sans', 'plus jakarta sans', 'manrope', 'figtree', 'red hat display', 'space grotesk', 'epilogue', 'nunito', 'questrial', 'mulish' ),
			'montserrat'   => array( 'raleway', 'work sans', 'barlow', 'barlow condensed', 'barlow semi condensed', 'oswald', 'bebas neue', 'archivo', 'archivo narrow', 'agdasima', 'anton', 'fjalla one', 'teko', 'saira', 'exo 2', 'titillium web', 'rubik', 'karla', 'syne', 'chivo' ),
			'open-sans'    => array( 'lato', 'source sans pro', 'source sans 3', 'noto sans', 'pt sans', 'fira sans', 'ubuntu', 'cabin', 'hind', 'mukta', 'nunito sans', 'asap', 'assistant', 'overpass', 'ibm plex sans', 'public sans', 'libre franklin', 'arimo', 'arial', 'helvetica', 'verdana', 'tahoma', 'segoe ui', 'noto sans display' ),
			'roboto'       => array( 'roboto condensed', 'roboto flex', 'heebo', 'roboto mono', 'arimo' ),
			'merriweather' => array( 'roboto slab', 'zilla slab', 'arvo', 'bitter', 'domine', 'noticia text', 'slabo 27px', 'josefin slab', 'aleo' ),
			'playfair'     => array( 'abril fatface', 'dm serif display', 'prata', 'cormorant', 'cormorant garamond', 'yeseva one', 'bodoni moda', 'cinzel', 'marcellus', 'fraunces' ),
			'source-serif' => array( 'pt serif', 'noto serif', 'literata', 'newsreader', 'gelasio', 'petrona', 'ibm plex serif', 'spectral' ),
			'lora'         => array( 'libre baskerville', 'eb garamond', 'crimson text', 'crimson pro', 'alegreya', 'vollkorn', 'cardo', 'old standard tt', 'libre caslon text', 'frank ruhl libre', 'times new roman', 'dm serif text' ),
		);
		foreach ( $near as $slug => $names ) {
			if ( in_array( $name, $names, true ) ) {
				return array( $slug, false );
			}
		}
		if ( false !== strpos( $name, 'slab' ) ) {
			return array( 'merriweather', false );
		}
		if ( false !== strpos( $name, 'serif' ) && false === strpos( $name, 'sans' ) ) {
			return array( 'lora', false );
		}
		return array( 'inter', false );
	}

	// Writing the plan.

	/**
	 * Starts a group.
	 *
	 * @param string $id    Group.
	 * @param string $title Title.
	 * @param string $text  What the group brings.
	 */
	private function group( $id, $title, $text ) {
		$this->groups[ $id ] = array(
			'title' => $title,
			'text'  => $text,
			'rows'  => array(),
		);
	}

	/**
	 * Adds a line to a group.
	 *
	 * @param string $group Group.
	 * @param string $label What it is.
	 * @param string $from  What Blocksy had.
	 * @param string $to    What this theme gets.
	 * @param string $level exact, approx, adjust or none.
	 * @param string $note  Explanation.
	 * @param array  $extra Colors for swatches (from, to), image URL (image).
	 */
	private function row( $group, $label, $from, $to, $level, $note = '', $extra = array() ) {
		$this->groups[ $group ]['rows'][] = array_merge(
			array(
				'label' => $label,
				'from'  => $from,
				'to'    => $to,
				'level' => $level,
				'note'  => $note,
			),
			$extra
		);
	}

	/**
	 * Sets an option of the new design (key of the schema), normalized.
	 *
	 * @param string $group Group.
	 * @param string $key   Key in the schema.
	 * @param mixed  $value Value.
	 * @return mixed The value as it will be stored.
	 */
	private function opt( $group, $key, $value ) {
		$schema = tainacan_interface_settings_schema();
		if ( ! isset( $schema[ $key ] ) ) {
			return $value;
		}
		$option = $schema[ $key ];
		if ( 'range' === $option['type'] ) {
			$step  = isset( $option['step'] ) ? max( 1, (int) $option['step'] ) : 1;
			$value = (int) ( round( (float) $value / $step ) * $step );
		}
		$value = tainacan_interface_opt_normalize( $option, $value );

		$this->set[ $group ][ 'tainacan_interface_' . $key ] = $value;
		return $value;
	}

	/**
	 * Sets a theme mod that is not in the schema (options of 2.x, core).
	 *
	 * @param string $group Group.
	 * @param string $mod   Theme mod.
	 * @param mixed  $value Value.
	 */
	private function mod( $group, $mod, $value ) {
		$this->set[ $group ][ $mod ] = $value;
	}

	/**
	 * How a value of the new design is called on screen.
	 *
	 * @param string $key   Key in the schema.
	 * @param mixed  $value Value.
	 * @return string
	 */
	private function says( $key, $value ) {
		$schema = tainacan_interface_settings_schema();
		if ( ! isset( $schema[ $key ] ) ) {
			return (string) $value;
		}
		$option = $schema[ $key ];
		switch ( $option['type'] ) {
			case 'bool':
				return $value ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' );
			case 'choice':
				return isset( $option['choices'][ (string) $value ] ) ? wp_strip_all_tags( (string) $option['choices'][ (string) $value ] ) : (string) $value;
			case 'range':
				$px = array( 'font_size', 'font_size_mobile', 'h1_size', 'h2_size', 'h3_size', 'h4_size', 'h5_size', 'h6_size', 'menu_size', 'page_width', 'radius', 'edge_spacing', 'frame_size', 'narrow_width', 'sidebar_width', 'hero_height', 'header_padding' );
				return in_array( $key, $px, true ) ? sprintf( '%d px', (int) $value ) : (string) $value;
			default:
				return (string) $value;
		}
	}

	/**
	 * A row for an option of the new design, with the label of the option.
	 *
	 * @param string $group Group.
	 * @param string $key   Key in the schema.
	 * @param mixed  $value Value.
	 * @param string $from  What Blocksy had.
	 * @param string $level Level.
	 * @param string $note  Explanation.
	 * @param array  $extra Extra.
	 */
	private function opt_row( $group, $key, $value, $from, $level = 'exact', $note = '', $extra = array() ) {
		$schema = tainacan_interface_settings_schema();
		$stored = $this->opt( $group, $key, $value );
		if ( isset( $schema[ $key ] ) && 'color' === $schema[ $key ]['type'] ) {
			$extra['to_color'] = $stored;
		}
		$this->row( $group, isset( $schema[ $key ] ) ? wp_strip_all_tags( $schema[ $key ]['label'] ) : $key, $from, $this->says( $key, $stored ), $level, $note, $extra );
	}

	/**
	 * "(Blocksy default)" after a value Blocksy never saved.
	 *
	 * @param string $key  Blocksy key.
	 * @param string $text Value as text.
	 * @return string
	 */
	private function shown( $key, $text ) {
		/* translators: %s: a value of a setting. */
		return $this->saved( $key ) ? $text : sprintf( __( '%s (Blocksy default)', 'tainacan-interface' ), $text );
	}

	/**
	 * Registers an image the import needs.
	 *
	 * @param string $ref Reference.
	 * @param int    $id  Attachment id in the source site.
	 * @param string $url Address of the file, when known.
	 * @param string $purpose What it is for.
	 * @return string The reference.
	 */
	private function image( $ref, $id, $url, $purpose ) {
		$this->images[ $ref ] = array(
			'id'  => (int) $id,
			'url' => (string) $url,
			'use' => $purpose,
		);
		return $ref;
	}

	// The groups.

	/**
	 * The palette (color 1 to 8 and more), resolved.
	 */
	private function read_palette() {
		$defaults = array( '#2872fa', '#1559ed', '#3a4f66', '#192a3d', '#e1e8ed', '#f2f5f7', '#fafbfc', '#ffffff' );
		$palette  = $this->get( 'colorPalette', array() );
		$palette  = is_array( $palette ) ? $palette : array();
		$count    = 8;
		foreach ( array_keys( $palette ) as $key ) {
			if ( is_string( $key ) && preg_match( '/^color(\d+)$/', $key, $m ) ) {
				$count = max( $count, min( 64, (int) $m[1] ) );
			}
		}
		// Two passes: a color may point to another one of the palette.
		for ( $pass = 0; $pass < 2; $pass++ ) {
			for ( $i = 1; $i <= $count; $i++ ) {
				$raw                 = isset( $palette[ 'color' . $i ]['color'] ) ? $palette[ 'color' . $i ]['color'] : ( isset( $defaults[ $i - 1 ] ) ? $defaults[ $i - 1 ] : '' );
				$hex                 = $this->color( $raw );
				$this->palette[ $i ] = '' !== $hex ? $hex : ( isset( $this->palette[ $i ] ) ? $this->palette[ $i ] : ( isset( $defaults[ $i - 1 ] ) ? $defaults[ $i - 1 ] : '#000000' ) );
			}
		}
	}

	/**
	 * Colors.
	 */
	private function colors() {
		$g = 'colors';
		$this->group( $g, __( 'Colors', 'tainacan-interface' ), __( 'The global colors of Blocksy become the colors of the site. A collection can still choose its own on its form in Tainacan.', 'tainacan-interface' ) );

		$link    = $this->color( $this->get( 'linkColor', array( 'default' => array( 'color' => 'var(--theme-palette-color-1)' ) ) ) );
		$heading = $this->color( $this->get( 'headingColor', array( 'default' => array( 'color' => 'var(--theme-palette-color-4)' ) ) ) );
		$text    = $this->color( $this->get( 'fontColor', array( 'default' => array( 'color' => 'var(--theme-palette-color-3)' ) ) ) );
		$bg      = $this->get( 'site_background', array() );
		$bg_hex  = $this->color( is_array( $bg ) && isset( $bg['backgroundColor'] ) ? $bg['backgroundColor'] : 'var(--theme-palette-color-7)' );
		$card    = $this->color( $this->get( 'blog_cardBackground', array( 'default' => array( 'color' => 'var(--theme-palette-color-8)' ) ) ) );

		$this->before = array(
			'accent'  => $link,
			'brand'   => $heading,
			'text'    => $text,
			'page'    => $bg_hex,
			'surface' => $card,
		);
		$this->opt_row( $g, 'accent_color', $link, $this->shown( 'linkColor', __( 'Links', 'tainacan-interface' ) . ' ' . $link ), 'exact', __( 'Links, buttons, filters and card titles.', 'tainacan-interface' ), array( 'from_color' => $link ) );
		$this->opt_row( $g, 'brand_color', $heading, $this->shown( 'headingColor', __( 'Headings', 'tainacan-interface' ) . ' ' . $heading ), 'exact', __( 'Titles and the current step of the breadcrumb.', 'tainacan-interface' ), array( 'from_color' => $heading ) );
		$this->opt_row( $g, 'text_color', $text, $this->shown( 'fontColor', __( 'Text', 'tainacan-interface' ) . ' ' . $text ), 'exact', '', array( 'from_color' => $text ) );
		$bg_level = is_array( $bg ) && isset( $bg['background_type'] ) && 'color' !== $bg['background_type'] ? 'approx' : 'exact';
		$this->opt_row( $g, 'page_background', $bg_hex, $this->shown( 'site_background', __( 'Site background', 'tainacan-interface' ) . ' ' . $bg_hex ), $bg_level, 'approx' === $bg_level ? __( 'The background of Blocksy was an image or a gradient; this theme takes its color.', 'tainacan-interface' ) : '', array( 'from_color' => $bg_hex ) );
		$this->opt_row( $g, 'surface_color', $card, $this->shown( 'blog_cardBackground', __( 'Cards and boxes', 'tainacan-interface' ) . ' ' . $card ), 'exact', '', array( 'from_color' => $card ) );
		$this->opt_row( $g, 'heading_color', 'brand', __( 'Headings in their own color', 'tainacan-interface' ) );

		$selection = $this->get( 'selectionColor', array() );
		$sel_bg    = $this->color( is_array( $selection ) && isset( $selection['hover'] ) ? $selection['hover'] : 'var(--theme-palette-color-1)' );
		$sel       = self::same_color( $sel_bg, $heading ) && ! self::same_color( $sel_bg, $link ) ? 'brand' : 'accent';
		$this->opt_row( $g, 'selection', $sel, $this->shown( 'selectionColor', $sel_bg ), self::same_color( $sel_bg, 'brand' === $sel ? $heading : $link ) ? 'exact' : 'approx' );

		// The decorative band is this theme's own: off, as Blocksy had none.
		$this->opt_row( $g, 'band_style', 'none', __( 'Blocksy has no decorative band', 'tainacan-interface' ), 'adjust', __( 'Turned off so the pages keep the look they had. It can be turned on in Customize.', 'tainacan-interface' ) );
		$this->opt( $g, 'header_band', false );
	}

	/**
	 * Typography.
	 */
	private function typography() {
		$g = 'typography';
		$this->group( $g, __( 'Typography', 'tainacan-interface' ), __( 'Fonts, sizes and weights of the text and of the titles. This theme serves its fonts from the site itself (no Google Fonts): a font it does not have becomes the nearest one in style.', 'tainacan-interface' ) );

		$root_defaults = array(
			'family'         => 'System Default',
			'variation'      => 'n4',
			'size'           => '16px',
			'line-height'    => '1.65',
			'letter-spacing' => '0em',
		);
		$root          = $this->get( 'rootTypography', array() );
		$root          = array_merge( $root_defaults, is_array( $root ) ? $root : array() );

		$this->before['font']          = (string) $root['family'];
		list( $body_font, $body_same ) = self::font( $root['family'] );
		$body_font                     = null === $body_font ? 'system' : $body_font;
		$this->opt_row( $g, 'font_ui', $body_font, $this->shown( 'rootTypography', (string) $root['family'] ), $body_same ? 'exact' : 'approx', $body_same ? '' : __( 'This font does not come with the theme; the nearest one in style was chosen.', 'tainacan-interface' ) );
		$this->opt( $g, 'font_reading', $body_font );

		$weight = self::weight( $root['variation'], 400 );
		$this->opt_row( $g, 'body_weight', (string) max( 300, min( 600, $weight ) ), (string) $weight, $weight >= 300 && $weight <= 600 ? 'exact' : 'approx' );

		$size = self::px( $root['size'] );
		if ( null !== $size ) {
			$this->opt_row( $g, 'font_size', $size, $this->shown( 'rootTypography', sprintf( '%s px', round( $size, 1 ) ) ), $size >= 14 && $size <= 20 ? 'exact' : 'approx' );
			$mobile = self::px( $root['size'], 'mobile' );
			$this->opt( $g, 'font_size_mobile', null !== $mobile ? $mobile : $size );
		}
		$line = is_numeric( $root['line-height'] ) ? (float) $root['line-height'] : 1.65;
		$this->opt_row( $g, 'line_height', $line * 100, (string) $line, 'exact' );

		// Titles: the family of h1 (or of the first heading that sets one), sizes one by one.
		$sizes     = array(
			1 => 40,
			2 => 35,
			3 => 30,
			4 => 25,
			5 => 20,
			6 => 16,
		);
		$family    = '';
		$hweight   = 700;
		$upper     = 0;
		$spacing   = null;
		$size_text = array();
		foreach ( $sizes as $n => $fallback ) {
			$h = $this->get( 'h' . $n . 'Typography', array() );
			$h = is_array( $h ) ? $h : array();
			if ( '' === $family && isset( $h['family'] ) && 'Default' !== $h['family'] ) {
				$family = (string) $h['family'];
			}
			if ( 1 === $n ) {
				$hweight = self::weight( isset( $h['variation'] ) ? $h['variation'] : 'n7', 700 );
				$spacing = isset( $h['letter-spacing'] ) ? self::px( $h['letter-spacing'] ) : null;
			}
			if ( $n <= 4 && isset( $h['text-transform'] ) && 'uppercase' === $h['text-transform'] ) {
				++$upper;
			}
			$px = isset( $h['size'] ) ? self::px( $h['size'] ) : null;
			$px = null === $px ? $fallback : $px;
			$this->opt( $g, 'h' . $n . '_size', $px );
			$size_text[] = sprintf( 'H%d %s', $n, round( $px ) );
		}
		$this->opt_row( $g, 'heading_scale', 100, __( 'Sizes one by one', 'tainacan-interface' ), 'exact', implode( ' · ', $size_text ) . ' px' );
		$this->before['font_heading']  = '' === $family ? (string) $root['family'] : $family;
		list( $head_font, $head_same ) = self::font( '' === $family ? $root['family'] : $family );
		$head_font                     = null === $head_font ? $body_font : $head_font;
		$this->opt_row( $g, 'font_heading', $head_font, '' === $family ? __( 'Same font as the text', 'tainacan-interface' ) : $family, $head_same ? 'exact' : 'approx', $head_same ? '' : __( 'This font does not come with the theme; the nearest one in style was chosen.', 'tainacan-interface' ) );
		$this->opt_row( $g, 'heading_weight', (string) max( 400, min( 900, $hweight ) ), (string) $hweight );
		// Letter spacing of the titles is kept in hundredths of an em here.
		$this->opt( $g, 'heading_spacing', null === $spacing ? 0 : $spacing / 16 * 100 );
		$this->opt_row( $g, 'heading_transform', $upper >= 2 ? 'uppercase' : 'none', $upper >= 2 ? __( 'Titles in capitals', 'tainacan-interface' ) : __( 'Titles as written', 'tainacan-interface' ) );
	}

	/**
	 * Layout, shapes and spacing.
	 */
	private function layout() {
		$g = 'layout';
		$this->group( $g, __( 'Layout and spacing', 'tainacan-interface' ), __( 'Width of the content, space between the parts, frame around the page and the shape of cards.', 'tainacan-interface' ) );

		$width = self::px( $this->get( 'maxSiteWidth', 1290 ) );
		$width = null === $width ? 1290 : $width;
		$this->opt_row( $g, 'page_width', $width, $this->shown( 'maxSiteWidth', sprintf( '%d px', $width ) ), $width >= 1000 && $width <= 2200 ? 'exact' : 'approx' );

		$narrow = self::px( $this->get( 'narrowContainerWidth', 750 ) );
		$narrow = null === $narrow ? 750 : $narrow;
		$this->opt_row( $g, 'narrow_width', $narrow, $this->shown( 'narrowContainerWidth', sprintf( '%d px', $narrow ) ), 'exact' );

		$space = self::px( $this->get( 'contentAreaSpacing', '60px' ) );
		$space = null === $space ? 60 : $space;
		$this->opt_row( $g, 'content_spacing', $space < 45 ? 'compact' : ( $space > 90 ? 'spacious' : 'normal' ), $this->shown( 'contentAreaSpacing', sprintf( '%d px', $space ) ), 'approx', __( 'This theme has three steps of spacing; the nearest one was chosen.', 'tainacan-interface' ) );

		$edge = self::px( $this->get( 'contentEdgeSpacing', '5vw' ) );
		if ( null !== $edge ) {
			$this->opt_row( $g, 'edge_spacing', min( 80, max( 8, $edge ) ), $this->shown( 'contentEdgeSpacing', sprintf( '%d px', $edge ) ), 'approx', __( 'Blocksy measures this margin in a share of the screen; this is its size on a 1440 px screen.', 'tainacan-interface' ) );
		}

		$frame = self::yes( $this->get( 'has_passepartout', 'no' ) ) ? self::px( $this->get( 'passepartoutSize', 10 ) ) : 0;
		$this->opt_row( $g, 'frame_size', (int) $frame, $frame ? sprintf( '%d px', $frame ) : $this->shown( 'has_passepartout', __( 'No frame', 'tainacan-interface' ) ) );

		$radius                 = $this->get( 'blog_cardRadius', null );
		$r                      = is_array( $radius ) && isset( $radius['top'] ) ? self::px( $radius['top'] ) : null;
		$r                      = null === $r ? 3 : $r;
		$this->before['radius'] = $r;
		$this->opt_row( $g, 'radius', $r, $this->shown( 'blog_cardRadius', sprintf( '%d px', $r ) ), 'approx', __( 'Blocksy rounds the corners of the post cards; here the value goes to every card and box of the theme.', 'tainacan-interface' ) );

		$shadow = $this->get(
			'blog_cardShadow',
			array(
				'enable' => true,
				'blur'   => 18,
			)
		);
		$shadow = is_array( $shadow ) ? $shadow : array();
		$kind   = empty( $shadow['enable'] ) ? 'none' : ( isset( $shadow['blur'] ) && (int) $shadow['blur'] > 24 ? 'deep' : 'soft' );
		$this->opt_row( $g, 'shadow', $kind, $this->shown( 'blog_cardShadow', empty( $shadow['enable'] ) ? __( 'No shadow', 'tainacan-interface' ) : __( 'Shadow', 'tainacan-interface' ) ), 'approx' );
	}

	/**
	 * Buttons and fields.
	 */
	private function buttons() {
		$g = 'buttons';
		$this->group( $g, __( 'Buttons and fields', 'tainacan-interface' ), __( 'Shape of the buttons and style of the text fields.', 'tainacan-interface' ) );

		$radius                        = $this->get( 'buttonRadius', '3px' );
		$r                             = is_array( $radius ) ? self::device( $radius ) : $radius;
		$r                             = is_array( $r ) && isset( $r['top'] ) ? self::px( $r['top'] ) : self::px( $r );
		$r                             = null === $r ? 3 : $r;
		$shape                         = $r <= 0 ? 'square' : ( $r >= 20 ? 'pill' : 'rounded' );
		$this->before['button_radius'] = $r;
		$exact                         = $r <= 0 || $r >= 20 || 8 === (int) $r;
		if ( ! $exact ) {
			$this->fine[ $g ][] = '--tt2-button-radius:' . (int) $r . 'px';
		}
		$this->opt_row( $g, 'button_shape', $shape, $this->shown( 'buttonRadius', sprintf( '%d px', $r ) ), 'exact', $exact ? '' : __( 'The exact corner goes in a fine adjustment of the Additional CSS of the import.', 'tainacan-interface' ) );
		$this->opt_row( $g, 'button_style', 'filled', __( 'Filled', 'tainacan-interface' ) );

		$font = $this->get( 'buttons', array() );
		$this->opt_row( $g, 'button_transform', is_array( $font ) && isset( $font['text-transform'] ) && 'uppercase' === $font['text-transform'] ? 'uppercase' : 'none', is_array( $font ) && isset( $font['text-transform'] ) ? (string) $font['text-transform'] : __( 'As written', 'tainacan-interface' ) );

		$forms = $this->get( 'forms_type', 'classic-forms' );
		$this->opt_row( $g, 'field_style', 'modern-forms' === $forms ? 'underline' : 'outlined', $this->shown( 'forms_type', 'modern-forms' === $forms ? __( 'Modern (line below)', 'tainacan-interface' ) : __( 'Classic (with border)', 'tainacan-interface' ) ) );
	}

	/**
	 * The active header of the header builder.
	 *
	 * @param string $key header_placements or footer_placements.
	 * @return array{0: array, 1: array<string, array>} The section and the values of its items by id.
	 */
	private function builder( $key ) {
		$placements = $this->get( $key, array() );
		$sections   = is_array( $placements ) && isset( $placements['sections'] ) && is_array( $placements['sections'] ) ? $placements['sections'] : array();
		$current    = is_array( $placements ) && isset( $placements['current_section'] ) ? $placements['current_section'] : 'type-1';
		$section    = array();
		foreach ( $sections as $candidate ) {
			if ( is_array( $candidate ) && isset( $candidate['id'] ) && $candidate['id'] === $current ) {
				$section = $candidate;
			}
		}
		$items = array();
		if ( isset( $section['items'] ) && is_array( $section['items'] ) ) {
			foreach ( $section['items'] as $item ) {
				if ( is_array( $item ) && isset( $item['id'] ) ) {
					$items[ (string) $item['id'] ] = isset( $item['values'] ) && is_array( $item['values'] ) ? $item['values'] : array();
				}
			}
		}
		return array( $section, $items );
	}

	/**
	 * Header and menu.
	 */
	private function header() {
		$g = 'header';
		$this->group( $g, __( 'Header and menu', 'tainacan-interface' ), __( 'The header builder of Blocksy places items freely; this theme has fixed arrangements. The import reads what was in the main row of the active header and picks the arrangement that shows the same things.', 'tainacan-interface' ) );
		list( $section, $items ) = $this->builder( 'header_placements' );

		// Where each item is: row of the desktop header.
		$rows = array();
		if ( empty( $section ) ) {
			// Blocksy's default header: logo, menu on one line; search and offcanvas trigger.
			$rows['middle-row'] = array( 'logo', 'menu', 'search' );
		} elseif ( isset( $section['desktop'] ) && is_array( $section['desktop'] ) ) {
			foreach ( $section['desktop'] as $row ) {
				if ( ! is_array( $row ) || ! isset( $row['id'], $row['placements'] ) || 'offcanvas' === $row['id'] ) {
					continue;
				}
				foreach ( (array) $row['placements'] as $place ) {
					foreach ( isset( $place['items'] ) ? (array) $place['items'] : array() as $id ) {
						$rows[ $row['id'] ][] = (string) $id;
					}
				}
			}
		}
		$where = array();
		foreach ( $rows as $row_id => $ids ) {
			foreach ( $ids as $id ) {
				$where[ $id ] = $row_id;
			}
		}
		$found = array_keys( $where );
		$this->row( $g, __( 'What the header shows', 'tainacan-interface' ), $found ? implode( ', ', $found ) : '-', __( 'Logo, menu, search, button and profiles where they exist', 'tainacan-interface' ), 'approx' );

		$inline = isset( $where['logo'], $where['menu'] ) && $where['logo'] === $where['menu'];
		$this->opt_row( $g, 'header_stack', $inline ? 'inline' : 'stacked', $inline ? __( 'Logo and menu on the same line', 'tainacan-interface' ) : __( 'Menu on a line below the logo', 'tainacan-interface' ) );

		// Color of the row that holds the logo.
		$logo_row = isset( $where['logo'] ) ? $where['logo'] : 'middle-row';
		$row_bg   = isset( $items[ $logo_row ]['headerRowBackground'] ) ? $items[ $logo_row ]['headerRowBackground'] : array();
		$row_hex  = $this->color( is_array( $row_bg ) && isset( $row_bg['backgroundColor'] ) ? $row_bg['backgroundColor'] : 'var(--theme-palette-color-8)' );
		$settings = isset( $section['settings'] ) && is_array( $section['settings'] ) ? $section['settings'] : array();

		// The "transparent" header of Blocksy can have a solid color of its own: when it is on
		// everywhere, that is the color visitors see on most pages, and the one that comes.
		$transparent = isset( $settings['has_transparent_header'] ) && self::yes( $settings['has_transparent_header'] );
		$everywhere  = false;
		foreach ( isset( $settings['transparent_conditions'] ) ? (array) $settings['transparent_conditions'] : array() as $rule ) {
			if ( is_array( $rule ) && isset( $rule['type'], $rule['rule'] ) && 'include' === $rule['type'] && 'everywhere' === $rule['rule'] ) {
				$everywhere = true;
			}
		}
		$solid_bg   = isset( $items[ $logo_row ]['transparentHeaderRowBackground'] ) ? $items[ $logo_row ]['transparentHeaderRowBackground'] : null;
		$solid_raw  = is_array( $solid_bg ) && isset( $solid_bg['backgroundColor'] ) ? $solid_bg['backgroundColor'] : $solid_bg;
		$solid_hex  = null !== $solid_raw && $this->opaque( $solid_raw ) ? $this->color( $solid_raw ) : '';
		$other_note = '';
		if ( $transparent && $everywhere && '' !== $solid_hex && ! self::same_color( $solid_hex, $row_hex ) ) {
			/* translators: %s: a color. */
			$other_note = sprintf( __( 'Blocksy showed this color on most pages (its "transparent" header, which here was solid); the pages it left out used %s.', 'tainacan-interface' ), $row_hex );
			$row_hex    = $solid_hex;
		}
		$brand  = isset( $this->set['colors']['tainacan_interface_brand_color'] ) ? $this->set['colors']['tainacan_interface_brand_color'] : '';
		$accent = isset( $this->set['colors']['tainacan_interface_accent_color'] ) ? $this->set['colors']['tainacan_interface_accent_color'] : '';
		if ( self::same_color( $row_hex, $brand ) ) {
			$style = 'brand';
		} elseif ( self::same_color( $row_hex, $accent ) ) {
			$style = 'accent';
		} else {
			$style = self::luminance( $row_hex ) < 0.25 ? 'dark' : 'light';
		}
		$exact                  = in_array( $style, array( 'brand', 'accent' ), true ) || self::same_color( $row_hex, '#ffffff' );
		$this->before['header'] = $row_hex;
		if ( ! $exact && '' !== $row_hex ) {
			$this->fine[ $g ][] = '--tt2-header-bg:' . $row_hex . ';--tt2-header-fg:' . ( self::luminance( $row_hex ) < 0.4 ? '#ffffff' : '#111111' );
		}
		$notes = array_filter( array( $exact ? '' : __( 'This theme has no option for this exact color: it goes in a fine adjustment of the Additional CSS of the import.', 'tainacan-interface' ), $other_note ) );
		$this->opt_row( $g, 'header_style', $style, $row_hex, '' === $other_note ? 'exact' : 'approx', implode( ' ', $notes ), array( 'from_color' => $row_hex ) );

		$sticky = isset( $settings['has_sticky_header'] ) && self::yes( $settings['has_sticky_header'] );
		$this->opt_row( $g, 'header_sticky', $sticky, $sticky ? __( 'Sticky header', 'tainacan-interface' ) : __( 'Header scrolls with the page', 'tainacan-interface' ) );
		if ( $transparent && '' === $solid_hex ) {
			$this->row( $g, __( 'Transparent header over the banner', 'tainacan-interface' ), __( 'On', 'tainacan-interface' ), '-', 'none', __( 'This theme has no transparent header: the header keeps its color on every page.', 'tainacan-interface' ) );
		}

		// Menu.
		$menu  = isset( $items['menu'] ) ? $items['menu'] : array();
		$type  = isset( $menu['header_menu_type'] ) ? (string) $menu['header_menu_type'] : 'type-1';
		$marks = array(
			'type-1' => 'none',
			'type-2' => 'underline',
			'type-3' => 'pill',
			'type-4' => 'underline',
		);
		$this->opt_row( $g, 'menu_indicator', isset( $marks[ $type ] ) ? $marks[ $type ] : 'none', $type, 'type-4' === $type ? 'approx' : 'exact' );
		$font  = isset( $menu['headerMenuFont'] ) && is_array( $menu['headerMenuFont'] ) ? $menu['headerMenuFont'] : array();
		$font  = array_merge(
			array(
				'size'           => '12px',
				'text-transform' => 'uppercase',
			),
			$font
		);
		$msize = self::px( $font['size'] );
		$this->opt_row( $g, 'menu_size', null === $msize ? 12 : $msize, sprintf( '%s px', null === $msize ? 12 : round( $msize ) ), 'exact' );
		$this->opt_row( $g, 'menu_transform', 'uppercase' === $font['text-transform'] ? 'uppercase' : 'none', (string) $font['text-transform'] );
		$this->opt_row( $g, 'mobile_menu', 'side', __( 'Side panel (off-canvas)', 'tainacan-interface' ) );

		// Search, button and profiles in the header.
		$this->mod( $g, 'tainacan_hide_search_input', ! isset( $where['search'] ) );
		$this->row( $g, __( 'Search field in the header', 'tainacan-interface' ), isset( $where['search'] ) ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), isset( $where['search'] ) ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), 'exact' );
		if ( isset( $where['button'] ) ) {
			$button = isset( $items['button'] ) ? $items['button'] : array();
			$label  = isset( $button['header_button_text'] ) ? (string) $button['header_button_text'] : __( 'Download', 'tainacan-interface' );
			$url    = isset( $button['header_button_link'] ) ? (string) $button['header_button_link'] : '';
			$this->opt_row( $g, 'header_button_label', $label, $label );
			$this->opt( $g, 'header_button_url', $url );
		}

		// Logo: the image and its height; the height of the row gives the padding.
		$logo    = isset( $items['logo'] ) ? $items['logo'] : array();
		$logo_id = isset( $logo['custom_logo'] ) ? self::device( $logo['custom_logo'] ) : $this->get( 'custom_logo', 0 );
		$logo_id = is_numeric( $logo_id ) ? (int) $logo_id : 0;
		if ( $logo_id > 0 ) {
			$this->set['images']['custom_logo'] = array( '__image' => $this->image( 'logo', $logo_id, '', __( 'Logo of the header', 'tainacan-interface' ) ) );
		}
		$height = isset( $logo['logoMaxHeight'] ) ? self::px( $logo['logoMaxHeight'] ) : 50;
		$height = null === $height ? 50 : $height;
		$this->mod( $g, 'tainacan_header_logo_max_height', (int) max( 42, min( 220, $height ) ) );
		$row_h = isset( $items[ $logo_row ]['headerRowHeight'] ) ? self::px( $items[ $logo_row ]['headerRowHeight'] ) : 120;
		$row_h = null === $row_h ? 120 : $row_h;
		/* translators: 1: height of the header row in pixels; 2: height of the logo in pixels. */
		$sizes = sprintf( __( 'Row of %1$d px, logo of %2$d px', 'tainacan-interface' ), $row_h, $height );
		$this->opt_row( $g, 'header_padding', max( 0, ( $row_h - $height ) / 2 ), $sizes, 'approx' );
		if ( isset( $logo['has_site_title'] ) && self::yes( $logo['has_site_title'] ) && $logo_id > 0 ) {
			$this->row( $g, __( 'Site title beside the logo', 'tainacan-interface' ), __( 'On', 'tainacan-interface' ), __( 'Only the logo', 'tainacan-interface' ), 'none', __( 'This theme shows the logo, or the title when there is no logo.', 'tainacan-interface' ) );
		}
		foreach ( array( 'top-row', 'bottom-row' ) as $other ) {
			if ( isset( $rows[ $other ] ) && $other !== $logo_row && array_diff( $rows[ $other ], array( 'menu' ) ) ) {
				$this->row( $g, __( 'Second row of the header', 'tainacan-interface' ), implode( ', ', $rows[ $other ] ), '-', 'none', __( 'This theme has one row for the header (two when the menu goes below the logo). Texts, HTML and widgets of other rows do not come.', 'tainacan-interface' ) );
			}
		}
	}

	/**
	 * Footer.
	 */
	private function footer() {
		$g = 'footer';
		$this->group( $g, __( 'Footer', 'tainacan-interface' ), __( 'The footer builder of Blocksy has rows and columns; this theme has a compact footer and one with widgets.', 'tainacan-interface' ) );
		list( $section, $items ) = $this->builder( 'footer_placements' );

		$found   = array();
		$row_of  = array();
		$columns = 0;
		if ( isset( $section['rows'] ) && is_array( $section['rows'] ) ) {
			foreach ( $section['rows'] as $row ) {
				foreach ( isset( $row['columns'] ) ? (array) $row['columns'] : array() as $column ) {
					foreach ( (array) $column as $id ) {
						$found[]       = (string) $id;
						$row_of[ $id ] = isset( $row['id'] ) ? $row['id'] : '';
						if ( 0 === strpos( (string) $id, 'widget-area' ) ) {
							++$columns;
						}
					}
				}
			}
		} else {
			// Blocksy's default footer: a copyright line.
			$found  = array( 'copyright' );
			$row_of = array( 'copyright' => 'bottom-row' );
		}
		$this->row( $g, __( 'What the footer shows', 'tainacan-interface' ), $found ? implode( ', ', $found ) : '-', $columns ? __( 'Footer with widgets', 'tainacan-interface' ) : __( 'Compact footer', 'tainacan-interface' ), 'approx' );
		$this->opt( $g, 'footer_style', $columns ? 'classic' : 'compact' );
		if ( $columns > 1 ) {
			$this->row( $g, __( 'Widget areas', 'tainacan-interface' ), (string) $columns, '1', 'approx', __( 'The widgets of every area go to the one footer area of this theme, in order.', 'tainacan-interface' ) );
		}

		$tone_row               = isset( $row_of['copyright'] ) ? $row_of['copyright'] : 'bottom-row';
		$bg                     = isset( $items[ $tone_row ]['footerRowBackground'] ) ? $items[ $tone_row ]['footerRowBackground'] : array();
		$hex                    = $this->color( is_array( $bg ) && isset( $bg['backgroundColor'] ) ? $bg['backgroundColor'] : 'var(--theme-palette-color-6)' );
		$brand                  = isset( $this->set['colors']['tainacan_interface_brand_color'] ) ? $this->set['colors']['tainacan_interface_brand_color'] : '';
		$tone                   = self::same_color( $hex, $brand ) ? 'brand' : ( self::luminance( $hex ) < 0.25 ? 'dark' : 'light' );
		$exact                  = 'brand' === $tone || self::same_color( $hex, '#ffffff' ) || self::same_color( $hex, '#16191d' );
		$this->before['footer'] = $hex;
		if ( ! $exact && '' !== $hex ) {
			$this->fine[ $g ][] = '--tt2-footer-bg:' . $hex . ';--tt2-footer-fg:' . ( self::luminance( $hex ) < 0.4 ? '#ffffff' : '#111111' );
		}
		$this->opt_row( $g, 'footer_tone', $tone, $hex, 'exact', $exact ? '' : __( 'This theme has no option for this exact color: it goes in a fine adjustment of the Additional CSS of the import.', 'tainacan-interface' ), array( 'from_color' => $hex ) );

		if ( in_array( 'copyright', $found, true ) ) {
			$copy = isset( $items['copyright']['copyright_text'] ) ? (string) $items['copyright']['copyright_text'] : 'Copyright &copy; {current_year} - WordPress Theme by {theme_author}';
			$copy = preg_replace( '/\s*[-–]\s*WordPress Theme by \{theme_author\}/i', '', $copy );
			$copy = str_replace( array( '{current_year}', '{site_title}', '{theme_author}' ), array( '{year}', '{site}', '' ), $copy );
			$copy = trim( html_entity_decode( wp_strip_all_tags( $copy ), ENT_QUOTES, 'UTF-8' ) );
			$this->opt_row( $g, 'footer_text', $copy, $copy, 'exact', __( 'Links and formatting of the line become plain text.', 'tainacan-interface' ) );
		}
		$this->mod( $g, 'tainacan_display_footer_logo', in_array( 'logo', $found, true ) );
		$this->row( $g, __( 'Logo in the footer', 'tainacan-interface' ), in_array( 'logo', $found, true ) ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), in_array( 'logo', $found, true ) ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), 'exact' );
		$this->footer_socials = in_array( 'socials', $found, true );
	}

	/**
	 * Whether the footer shows the profiles of the institution.
	 *
	 * @var bool
	 */
	private $footer_socials = false;

	/**
	 * Profiles of the institution.
	 */
	private function social() {
		$g = 'social';
		$this->group( $g, __( 'Profiles of the institution', 'tainacan-interface' ), __( 'The addresses of the social network accounts and where their icons appear.', 'tainacan-interface' ) );
		$ours  = array(
			'facebook'  => 'social_facebook',
			'instagram' => 'social_instagram',
			'twitter'   => 'social_x',
			'youtube'   => 'social_youtube',
			'linkedin'  => 'social_linkedin',
			'tiktok'    => 'social_tiktok',
			'bluesky'   => 'social_bluesky',
			'threads'   => 'social_threads',
		);
		$any   = false;
		$other = array();
		foreach ( array( 'facebook', 'facebook_group', 'instagram', 'threads', 'twitter', 'bluesky', 'youtube', 'linkedin', 'tiktok', 'pinterest', 'whatsapp', 'telegram', 'mastodon', 'flickr', 'vimeo', 'spotify', 'soundcloud', 'github', 'email', 'phone', 'rss' ) as $network ) {
			$url = $this->get( $network, '' );
			if ( ! is_string( $url ) || '' === trim( $url ) || '#' === trim( $url ) ) {
				continue;
			}
			if ( isset( $ours[ $network ] ) ) {
				$this->opt_row( $g, $ours[ $network ], $url, $url );
				$any = true;
			} else {
				$other[] = $network;
			}
		}
		if ( $other ) {
			$this->row( $g, __( 'Other networks', 'tainacan-interface' ), implode( ', ', $other ), '-', 'none', __( 'This theme has icons for Facebook, Instagram, X, YouTube, LinkedIn, TikTok, Bluesky and Threads.', 'tainacan-interface' ) );
		}
		if ( $any ) {
			list( , $header_items ) = $this->builder( 'header_placements' );
			$in_header              = isset( $header_items['socials'] ) && $this->header_has( 'socials' );
			$place                  = $in_header && $this->footer_socials ? 'both' : ( $in_header ? 'header' : ( $this->footer_socials ? 'footer' : 'none' ) );
			$this->opt_row( $g, 'social_place', $place, $place );
		}
	}

	/**
	 * Whether the desktop header of the active section shows an item.
	 *
	 * @param string $id Item.
	 * @return bool
	 */
	private function header_has( $id ) {
		list( $section ) = $this->builder( 'header_placements' );
		foreach ( isset( $section['desktop'] ) ? (array) $section['desktop'] : array() as $row ) {
			if ( ! is_array( $row ) || ( isset( $row['id'] ) && 'offcanvas' === $row['id'] ) ) {
				continue;
			}
			foreach ( isset( $row['placements'] ) ? (array) $row['placements'] : array() as $place ) {
				if ( isset( $place['items'] ) && in_array( $id, (array) $place['items'], true ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Breadcrumb, back to top, emoji.
	 */
	private function navigation() {
		$g = 'navigation';
		$this->group( $g, __( 'Breadcrumb and back to top', 'tainacan-interface' ), __( 'The path above the titles and the button that goes back to the top.', 'tainacan-interface' ) );
		$sep = $this->get( 'breadcrumb_separator', 'type-1' );
		$this->opt_row( $g, 'crumb_separator', 'type-3' === $sep ? 'slash' : 'chevron', $this->shown( 'breadcrumb_separator', 'type-3' === $sep ? '/' : '›' ), 'type-2' === $sep ? 'approx' : 'exact' );
		$this->opt_row( $g, 'crumb_home_icon', 'icon' === $this->get( 'breadcrumb_home_item', 'text' ), $this->shown( 'breadcrumb_home_item', 'icon' === $this->get( 'breadcrumb_home_item', 'text' ) ? __( 'Icon', 'tainacan-interface' ) : __( 'Text', 'tainacan-interface' ) ) );
		$this->opt_row( $g, 'crumb_style', 'plain', __( 'Plain text', 'tainacan-interface' ), 'adjust', __( 'The breadcrumb of Blocksy is plain text; the pills of this theme were turned off.', 'tainacan-interface' ) );

		$top = self::yes( $this->get( 'has_back_top', 'no' ) );
		$this->opt_row( $g, 'back_top', $top, $this->shown( 'has_back_top', $top ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ) ) );
		if ( $top ) {
			$shape = $this->get( 'top_button_shape', 'square' );
			$this->opt_row( $g, 'back_top_shape', 'circle' === $shape ? 'circle' : 'square', $this->shown( 'top_button_shape', (string) $shape ) );
			$side = $this->get( 'top_button_alignment', 'right' );
			$this->opt_row( $g, 'back_top_side', 'left' === $side ? 'left' : 'right', $this->shown( 'top_button_alignment', (string) $side ) );
			$vis = $this->get( 'back_top_visibility', array( 'mobile' => true ) );
			$this->opt( $g, 'back_top_mobile', ! is_array( $vis ) || ! isset( $vis['mobile'] ) || (bool) $vis['mobile'] );
		}
		$emoji = $this->get( 'emoji_scripts', 'yes' );
		$this->opt_row( $g, 'perf_emoji', ! self::yes( $emoji ), $this->shown( 'emoji_scripts', self::yes( $emoji ) ? __( 'Emoji script loaded', 'tainacan-interface' ) : __( 'Emoji script left out', 'tainacan-interface' ) ) );
	}

	/**
	 * Posts, pages, the banner and the sidebar.
	 */
	private function posts() {
		$g = 'blog';
		$this->group( $g, __( 'Posts, pages and sidebar', 'tainacan-interface' ), __( 'The list of posts, a post, the banner at the top and the sidebar of the blog.', 'tainacan-interface' ) );

		$structure = $this->get( 'blog_structure', 'grid' );
		$this->opt_row( $g, 'blog_layout', 'simple' === $structure ? 'list' : 'grid', $this->shown( 'blog_structure', (string) $structure ), 'gutenberg' === $structure ? 'approx' : 'exact' );
		$columns = self::device( $this->get( 'blog_columns', 3 ) );
		$this->opt_row( $g, 'blog_columns', (string) max( 2, min( 4, (int) $columns ) ), $this->shown( 'blog_columns', (string) $columns ) );

		$ratio   = '4/3';
		$excerpt = 40;
		$order   = $this->get( 'blog_archive_order', array() );
		foreach ( is_array( $order ) ? $order : array() as $part ) {
			if ( ! is_array( $part ) || ! isset( $part['id'] ) ) {
				continue;
			}
			if ( 'featured_image' === $part['id'] && isset( $part['thumb_ratio'] ) ) {
				$ratio = (string) $part['thumb_ratio'];
			}
			if ( 'excerpt' === $part['id'] ) {
				$excerpt = isset( $part['enabled'] ) && ! $part['enabled'] ? 0 : ( isset( $part['excerpt_length'] ) ? (int) $part['excerpt_length'] : 40 );
			}
		}
		$ratios = array(
			'original' => 'auto',
			'4/3'      => '4/3',
			'3/2'      => '3/2',
			'16/9'     => '16/9',
			'1/1'      => '1/1',
			'2/1'      => '16/9',
			'3/4'      => '1/1',
			'2/3'      => '1/1',
			'9/16'     => '1/1',
		);
		$this->opt_row( $g, 'blog_ratio', isset( $ratios[ $ratio ] ) ? $ratios[ $ratio ] : '4/3', $ratio, ( isset( $ratios[ $ratio ] ) && $ratios[ $ratio ] === $ratio ) || 'original' === $ratio ? 'exact' : 'approx' );
		$this->opt_row( $g, 'blog_excerpt', $excerpt, (string) $excerpt, $excerpt <= 80 ? 'exact' : 'approx' );

		$side = self::yes( $this->get( 'blog_has_sidebar', 'no' ) ) ? $this->get( 'blog_sidebar_position', 'right' ) : 'none';
		$this->opt_row( $g, 'sidebar_position', in_array( $side, array( 'left', 'right' ), true ) ? $side : 'none', $this->shown( 'blog_has_sidebar', (string) $side ) );
		$pct   = $this->get( 'sidebarWidth', 27 );
		$width = isset( $this->set['layout']['tainacan_interface_page_width'] ) ? (int) $this->set['layout']['tainacan_interface_page_width'] : 1290;
		$this->opt_row( $g, 'sidebar_width', (float) $pct * $width / 100, sprintf( '%s%%', (string) $pct ), 'approx', __( 'Blocksy measures the sidebar in a share of the page; here it is in pixels.', 'tainacan-interface' ) );
		$this->opt( $g, 'sidebar_sticky', self::yes( $this->get( 'has_sticky_sidebar', 'no' ) ) );

		$p      = 'single_blog_post_';
		$struct = $this->get( $p . 'structure', 'type-3' );
		$this->opt_row( $g, 'post_width', 'type-3' === $struct ? 'narrow' : 'normal', $this->shown( $p . 'structure', (string) $struct ), in_array( $struct, array( 'type-3', 'type-4' ), true ) ? 'exact' : 'approx', in_array( $struct, array( 'type-1', 'type-2' ), true ) ? __( 'A post with its own sidebar: here the sidebar of the blog decides.', 'tainacan-interface' ) : '' );
		foreach ( array(
			'post_featured' => array( 'has_featured_image', __( 'Featured image', 'tainacan-interface' ) ),
			'post_share'    => array( 'has_share_box', __( 'Share box', 'tainacan-interface' ) ),
			'post_terms'    => array( 'has_post_tags', __( 'Tags', 'tainacan-interface' ) ),
			'post_nav'      => array( 'has_post_nav', __( 'Previous and next post', 'tainacan-interface' ) ),
		) as $key => $from ) {
			$on = self::yes( $this->get( $p . $from[0], 'yes' ) );
			$this->opt_row( $g, $key, $on, $this->shown( $p . $from[0], $from[1] . ': ' . ( $on ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ) ) ) );
		}
		foreach ( array(
			'has_author_box'    => array( 'no', __( 'Author box', 'tainacan-interface' ) ),
			'has_related_posts' => array( 'yes', __( 'Related posts', 'tainacan-interface' ) ),
		) as $key => $from ) {
			if ( self::yes( $this->get( $p . $key, $from[0] ) ) ) {
				$this->row( $g, $from[1], __( 'On', 'tainacan-interface' ), '-', 'none', __( 'This theme does not have it for posts.', 'tainacan-interface' ) );
			}
		}

		// The banner at the top of posts and pages.
		$hero    = $this->get( $p . 'hero_section', 'type-1' );
		$enabled = self::yes( $this->get( $p . 'hero_enabled', 'yes' ) );
		$style   = ! $enabled ? 'none' : ( 'type-2' === $hero ? 'banner' : 'plain' );
		$this->opt_row( $g, 'hero_style', $style, $this->shown( $p . 'hero_section', 'type-2' === $hero ? __( 'Title over an image', 'tainacan-interface' ) : __( 'Title in the content', 'tainacan-interface' ) ) );
		$align = $this->get( $p . ( 'type-2' === $hero ? 'hero_alignment2' : 'hero_alignment1' ), 'type-2' === $hero ? 'center' : 'left' );
		$this->opt_row( $g, 'hero_align', 'center' === $align ? 'center' : 'start', (string) $align );
		if ( 'banner' === $style ) {
			$h = self::px( $this->get( $p . 'hero_height', '250px' ) );
			$this->opt_row( $g, 'hero_height', null === $h ? 240 : $h, sprintf( '%d px', null === $h ? 250 : $h ), 'approx' );
			$bg = $this->get( $p . 'page_title_bg_type', 'featured_image' );
			$this->mod( $g, 'tainacan_featured_image_on_header_banner', 'featured_image' === $bg );
			$this->row( $g, __( 'Image of the banner of a post', 'tainacan-interface' ), (string) $bg, 'featured_image' === $bg ? __( 'Featured image of the post', 'tainacan-interface' ) : __( 'Image of the banner of the site', 'tainacan-interface' ), 'exact' );
		}
		// An image chosen for the banner of pages or of the blog becomes the banner image of the site.
		foreach ( array( 'single_page_', 'blog_', $p, 'categories_' ) as $prefix ) {
			$custom = $this->get( $prefix . 'custom_hero_background', null );
			if ( 'custom_image' === $this->get( $prefix . 'page_title_bg_type', '' ) && is_array( $custom ) && ! empty( $custom['attachment_id'] ) ) {
				$this->set['images']['header_image'] = array( '__image' => $this->image( 'banner', (int) $custom['attachment_id'], isset( $custom['url'] ) ? (string) $custom['url'] : '', __( 'Image of the banner', 'tainacan-interface' ) ) );
				break;
			}
		}
		$blog_align = $this->get( 'blog_hero_alignment2', $this->get( 'blog_hero_alignment1', 'left' ) );
		$this->opt_row( $g, 'page_title_style', 'center' === $blog_align ? 'centered' : 'editorial', (string) $blog_align, 'approx' );
	}

	/**
	 * Values of a Tainacan Support for Blocksy setting for the repository and
	 * every collection: the repository wins; else the value most collections share.
	 *
	 * @param string $suffix   Setting after the prefix (show_search...).
	 * @param string $kind     archive or single.
	 * @param mixed  $fallback Default of the plugin.
	 * @return array{0: mixed, 1: array} Value and the collections that differ.
	 */
	private function tnc_value( $suffix, $kind, $fallback ) {
		if ( 'archive' === $kind && $this->saved( 'tainacan-repository-items_archive_' . $suffix ) ) {
			$value  = $this->get( 'tainacan-repository-items_archive_' . $suffix );
			$others = array();
		} else {
			$value  = null;
			$others = array();
		}
		$counts = array();
		$by     = array();
		foreach ( $this->mods as $key => $v ) {
			if ( is_string( $key ) && preg_match( '/^tnc_col_(\d+)_item_' . $kind . '_' . preg_quote( $suffix, '/' ) . '$/', $key, $m ) ) {
				$this->used[ $key ] = true;
				$s                  = wp_json_encode( $v );
				$counts[ $s ]       = isset( $counts[ $s ] ) ? $counts[ $s ] + 1 : 1;
				$by[ (int) $m[1] ]  = $s;
			}
		}
		if ( null === $value ) {
			if ( $counts ) {
				arsort( $counts );
				$value = json_decode( (string) key( $counts ), true );
			} else {
				$value = $fallback;
			}
		}
		$chosen = wp_json_encode( $value );
		foreach ( $by as $id => $s ) {
			if ( $s !== $chosen ) {
				$others[] = $id;
			}
		}
		return array( $value, $others );
	}

	/**
	 * Lists and item pages of Tainacan, for the whole site.
	 */
	private function tainacan() {
		$g = 'tainacan';
		$this->group( $g, __( 'Tainacan pages', 'tainacan-interface' ), __( 'Search bar, filters, pagination and item page, from Tainacan Support for Blocksy. Blocksy keeps these per collection; this theme keeps one value for the site, from the repository list or from what most collections use.', 'tainacan-interface' ) );
		if ( ! $this->has_tainacan_settings() ) {
			$this->row( $g, __( 'Tainacan Support for Blocksy', 'tainacan-interface' ), __( 'No settings saved', 'tainacan-interface' ), __( 'The defaults of the plugin', 'tainacan-interface' ), 'exact', __( 'The values below are the defaults of Tainacan Support for Blocksy, so the lists look as they did.', 'tainacan-interface' ) );
		}

		// Search bar and filters: switch of Blocksy (yes shows) => theme mod of 2.x and whether it hides.
		$lists = array(
			'show_search'                               => array( 'yes', 'tainacan_items_page_hide_search', true, __( 'Search field', 'tainacan-interface' ) ),
			'show_advanced_search'                      => array( 'yes', 'tainacan_items_page_hide_advanced_search', true, __( 'Advanced search', 'tainacan-interface' ) ),
			'show_sort_by_button'                       => array( 'yes', 'tainacan_items_page_hide_sort_by_button', true, __( 'Sort button', 'tainacan-interface' ) ),
			'show_exposers_button'                      => array( 'yes', 'tainacan_items_page_hide_exposers_button', true, __( 'View as (exposers)', 'tainacan-interface' ) ),
			'show_inline_view_mode_options'             => array( 'no', 'tainacan_items_page_show_inline_view_mode_options', false, __( 'View modes side by side', 'tainacan-interface' ) ),
			'show_fullscreen_with_view_modes'           => array( 'no', 'tainacan_items_page_show_fullscreen_with_view_modes', false, __( 'Full screen among the view modes', 'tainacan-interface' ) ),
			'show_hide_filters_button'                  => array( 'yes', 'tainacan_items_page_hide_hide_filters_button', true, __( 'Button to hide the filters', 'tainacan-interface' ) ),
			'show_filters_button_inside_search_control' => array( 'yes', 'tainacan_items_page_show_filters_button_inside_search_control', false, __( 'Filters button in the search bar', 'tainacan-interface' ) ),
			'start_with_filters_hidden'                 => array( 'no', 'tainacan_items_page_start_with_filters_hidden', false, __( 'Filters start hidden', 'tainacan-interface' ) ),
			'filters_as_modal'                          => array( 'no', 'tainacan_items_page_filters_as_modal', false, __( 'Filters in a modal', 'tainacan-interface' ) ),
			'filters_fixed_on_scroll'                   => array( 'no', 'tainacan_items_page_filters_fixed_on_scroll', false, __( 'Filters fixed on scroll', 'tainacan-interface' ) ),
			'display_filters_horizontally'              => array( 'no', 'tainacan_items_page_display_filters_horizontally', false, __( 'Filters side by side', 'tainacan-interface' ) ),
			'should_not_hide_filters_on_mobile'         => array( 'no', 'tainacan_items_page_should_not_hide_filters_on_mobile', false, __( 'Filters visible on phones', 'tainacan-interface' ) ),
			'show_items_per_page_button'                => array( 'yes', 'tainacan_items_page_hide_items_per_page_button', true, __( 'Items per page', 'tainacan-interface' ) ),
			'show_go_to_page_button'                    => array( 'yes', 'tainacan_items_page_hide_go_to_page_button', true, __( 'Go to page', 'tainacan-interface' ) ),
		);
		foreach ( $lists as $suffix => $how ) {
			list( $value, $others ) = $this->tnc_value( $suffix, 'archive', $how[0] );
			$on                     = self::yes( $value );
			$this->mod( $g, $how[1], $how[2] ? ! $on : $on );
			$this->row( $g, $how[3], $on ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), $on ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), $others ? 'approx' : 'exact', $others ? $this->differs( $others ) : '' );
		}
		list( $view ) = $this->tnc_value( 'default_view_mode', 'archive', '' );
		if ( $this->saved( 'tainacan-repository-items_archive_default_view_mode' ) && is_string( $view ) && '' !== $view ) {
			$this->mod( $g, 'tainacan_items_repository_list_default_view_mode', $view );
			$this->row( $g, __( 'View mode of the repository list', 'tainacan-interface' ), $view, $view, 'exact' );
		}

		// Item page.
		$items = array(
			'show_title_metadata'             => array( 'no', 'tainacan_single_item_hide_core_title_metadata', true, __( 'Title among the metadata', 'tainacan-interface' ) ),
			'hide_download_button'            => array( 'no', 'tainacan_single_item_hide_download_document', false, __( 'Hide the download button', 'tainacan-interface' ) ),
			'disable_gallery_lightbox'        => array( 'no', 'tainacan_single_item_disable_gallery_lightbox', false, __( 'Gallery without lightbox', 'tainacan-interface' ) ),
			'hide_files_name'                 => array( 'no', 'tainacan_single_item_hide_files_name', false, __( 'Hide file names', 'tainacan-interface' ) ),
			'hide_files_name_main'            => array( 'yes', 'tainacan_single_item_hide_files_name_main', false, __( 'Hide the name under the document', 'tainacan-interface' ) ),
			'hide_files_caption_main'         => array( 'yes', 'tainacan_single_item_hide_files_caption_main', false, __( 'Hide the caption under the document', 'tainacan-interface' ) ),
			'hide_files_description_main'     => array( 'yes', 'tainacan_single_item_hide_files_description_main', false, __( 'Hide the description under the document', 'tainacan-interface' ) ),
			'hide_files_name_lightbox'        => array( 'no', 'tainacan_single_item_hide_files_name_lightbox', false, __( 'Hide the name in the lightbox', 'tainacan-interface' ) ),
			'hide_files_caption_lightbox'     => array( 'no', 'tainacan_single_item_hide_files_caption_lightbox', false, __( 'Hide the caption in the lightbox', 'tainacan-interface' ) ),
			'hide_files_description_lightbox' => array( 'no', 'tainacan_single_item_hide_files_description_lightbox', false, __( 'Hide the description in the lightbox', 'tainacan-interface' ) ),
			'display_items_related_to_this'   => array( 'yes', 'tainacan_single_item_enable_related_items_section', false, __( 'Items related to this', 'tainacan-interface' ) ),
			'has_post_nav'                    => array( 'yes', 'tainacan_single_item_show_navigation_options', false, __( 'Previous and next item', 'tainacan-interface' ) ),
		);
		foreach ( $items as $suffix => $how ) {
			list( $value, $others ) = $this->tnc_value( $suffix, 'single', $how[0] );
			$on                     = self::yes( $value );
			$this->mod( $g, $how[1], $how[2] ? ! $on : $on );
			$this->row( $g, $how[3], $on ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), $on ? __( 'On', 'tainacan-interface' ) : __( 'Off', 'tainacan-interface' ), $others ? 'approx' : 'exact', $others ? $this->differs( $others ) : '' );
		}
		list( $scheme ) = $this->tnc_value( 'gallery_color_scheme', 'single', 'dark' );
		$this->mod( $g, 'tainacan_single_item_gallery_color_scheme', 'light' === $scheme ? 'light' : 'dark' );
		list( $merged ) = $this->tnc_value( 'document_attachments_structure', 'single', 'gallery-type-1' );
		$this->mod( $g, 'tainacan_single_item_gallery_mode', 'gallery-type-2' === $merged );
		$this->row( $g, __( 'Document and attachments', 'tainacan-interface' ), 'gallery-type-2' === $merged ? __( 'One gallery', 'tainacan-interface' ) : __( 'Separate', 'tainacan-interface' ), 'gallery-type-2' === $merged ? __( 'One gallery', 'tainacan-interface' ) : __( 'Separate', 'tainacan-interface' ), 'exact' );
		list( $height ) = $this->tnc_value( 'document_height', 'single', 60 );
		$this->mod( $g, 'tainacan_single_item_document_max_height', max( 10, min( 100, (int) $height ) ) );
		list( $labels ) = $this->tnc_value( 'metadata_list_structure_type', 'single', 'metadata-type-1' );
		$this->opt_row( $g, 'item_labels', 'metadata-type-2' === $labels ? 'side' : 'above', 'metadata-type-2' === $labels ? __( 'Label aside values', 'tainacan-interface' ) : __( 'Label above values', 'tainacan-interface' ) );
		foreach ( array(
			'items_related_to_this_layout'            => array( 'carousel', 'tainacan_single_item_related_items_layout' ),
			'items_related_to_this_max_columns_count' => array( 4, 'tainacan_single_item_related_items_max_columns_count' ),
			'items_related_to_this_order'             => array( 'title_asc', 'tainacan_single_item_related_items_order' ),
			'section_document_label'                  => array( null, 'tainacan_single_item_document_section_label' ),
			'section_attachments_label'               => array( null, 'tainacan_single_item_attachments_section_label' ),
			'section_metadata_label'                  => array( null, 'tainacan_single_item_metadata_section_label' ),
			'section_items_related_to_this_label'     => array( null, 'tainacan_single_item_related_items_section_label' ),
		) as $suffix => $how ) {
			list( $value ) = $this->tnc_value( $suffix, 'single', $how[0] );
			if ( null !== $value && is_scalar( $value ) ) {
				$this->mod( $g, $how[1], is_int( $how[0] ) ? (int) $value : sanitize_text_field( (string) $value ) );
			}
		}
	}

	/**
	 * Whether any Tainacan Support for Blocksy setting was saved.
	 *
	 * @return bool
	 */
	private function has_tainacan_settings() {
		foreach ( array_keys( $this->mods ) as $key ) {
			if ( is_string( $key ) && preg_match( '/^(tnc_col_\d+_item_|tainacan-repository-items_|tainacan-terms-items_)/', $key ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Note about collections with another value.
	 *
	 * @param int[] $ids Collections.
	 * @return string
	 */
	private function differs( $ids ) {
		$names = array();
		foreach ( array_slice( $ids, 0, 5 ) as $id ) {
			$names[] = $this->collection_name( $id );
		}
		/* translators: %s: names of collections. */
		return sprintf( __( 'Different in: %s. Here the value is the same for the whole site.', 'tainacan-interface' ), implode( ', ', $names ) . ( count( $ids ) > 5 ? '…' : '' ) );
	}

	/**
	 * Name of a collection of this site, or its number.
	 *
	 * @param int $id Collection.
	 * @return string
	 */
	private function collection_name( $id ) {
		$post = $this->same_site || $this->collection_exists( $id ) ? get_post( $id ) : null;
		/* translators: %d: number of a collection. */
		return $post ? get_the_title( $post ) : sprintf( __( 'collection %d', 'tainacan-interface' ), $id );
	}

	/**
	 * Whether a collection with this id exists here.
	 *
	 * @param int $id Collection.
	 * @return bool
	 */
	private function collection_exists( $id ) {
		$post = get_post( (int) $id );
		return $post && 'tainacan-collection' === $post->post_type;
	}

	/**
	 * Settings of one collection: layout of the item page and of the metadata sections.
	 */
	private function collections() {
		$g = 'collections';
		$this->group( $g, __( 'Each collection', 'tainacan-interface' ), __( 'What Tainacan Support for Blocksy kept for each collection and that this theme also keeps per collection: the layout of the item page and of the metadata sections. They go to the form of each collection ("Look of this collection on the site").', 'tainacan-interface' ) );
		$ids = array();
		foreach ( array_keys( $this->mods ) as $key ) {
			if ( is_string( $key ) && preg_match( '/^tnc_col_(\d+)_item_(single|archive)_/', $key, $m ) ) {
				$ids[ (int) $m[1] ] = true;
			}
		}
		$layouts  = array(
			'type-dam' => array( 'stacked', 'exact' ),
			'type-dma' => array( 'stacked-split', 'exact' ),
			'type-mda' => array( 'stacked-record', 'exact' ),
			'type-gm'  => array( 'media-left', 'exact' ),
			'type-mg'  => array( 'media-right', 'exact' ),
			'type-gtm' => array( 'stacked', 'approx' ),
		);
		$sections = array(
			'metadata-section-type-2' => 'tabs',
			'metadata-section-type-3' => 'open-first',
			'metadata-section-type-4' => 'accordion',
		);
		$schema   = tainacan_interface_settings_schema();
		foreach ( array_keys( $ids ) as $id ) {
			$name   = $this->collection_name( $id );
			$exists = $this->collection_exists( $id );
			$layout = $this->get( 'tnc_col_' . $id . '_item_single_page_structure_type', null );
			// From another site a number can name another collection here: say which one gets it.
			$guess = $exists && ! $this->same_site;
			/* translators: %s: name of a collection of this site. */
			$guess_note = $guess ? sprintf( __( 'Matched by number with "%s" of this site; uncheck this topic if it is not the same collection.', 'tainacan-interface' ), $name ) : '';
			if ( null !== $layout && isset( $layouts[ $layout ] ) ) {
				if ( $exists ) {
					$this->meta[ $g ][ $id ]['tainacan_interface_opt_item_layout'] = $layouts[ $layout ][0];
				}
				$this->row( $g, $name . ' — ' . __( 'item page', 'tainacan-interface' ), $layout, $exists ? wp_strip_all_tags( $schema['item_layout']['choices'][ $layouts[ $layout ][0] ] ) : '-', $exists ? ( $guess ? 'approx' : $layouts[ $layout ][1] ) : 'none', $exists ? $guess_note : __( 'There is no collection with this number on this site.', 'tainacan-interface' ) );
			}
			$sec = $this->get( 'tnc_col_' . $id . '_item_single_metadata_sections_layout_type', null );
			if ( null !== $sec && isset( $sections[ $sec ] ) ) {
				if ( $exists ) {
					$this->meta[ $g ][ $id ]['__sections'] = $sections[ $sec ];
				}
				$choices = tainacan_interface_sections_choices();
				$this->row( $g, $name . ' — ' . __( 'metadata sections', 'tainacan-interface' ), $sec, $exists ? $choices[ $sections[ $sec ] ][0] : '-', $exists ? ( 'open-first' === $sections[ $sec ] || $guess ? 'approx' : 'exact' ) : 'none', $guess_note );
			}
		}
		if ( ! $ids ) {
			$this->row( $g, __( 'Collections', 'tainacan-interface' ), __( 'No settings of their own', 'tainacan-interface' ), __( 'They follow the site', 'tainacan-interface' ), 'exact' );
		}
	}

	/**
	 * Menus.
	 */
	private function menus() {
		$g = 'menus';
		$this->group( $g, __( 'Menus', 'tainacan-interface' ), __( 'The menus themselves are content of the site and stay as they are; only the place where each one appears is chosen again.', 'tainacan-interface' ) );
		$locations = $this->get( 'nav_menu_locations', array() );
		$locations = is_array( $locations ) ? $locations : array();
		$main      = ! empty( $locations['menu_1'] ) ? (int) $locations['menu_1'] : ( ! empty( $locations['menu_mobile'] ) ? (int) $locations['menu_mobile'] : 0 );
		$pairs     = array(
			'navMenubelowHeader' => array( $main, __( 'Main menu', 'tainacan-interface' ) ),
			'tt2_footer'         => array( ! empty( $locations['footer'] ) ? (int) $locations['footer'] : 0, __( 'Footer menu', 'tainacan-interface' ) ),
		);
		foreach ( $pairs as $location => $pair ) {
			if ( ! $pair[0] ) {
				continue;
			}
			$menu = $this->same_site ? wp_get_nav_menu_object( $pair[0] ) : false;
			if ( $menu ) {
				$this->locations[ $location ] = (int) $menu->term_id;
				$this->row( $g, $pair[1], $menu->name, $menu->name, 'exact' );
			} else {
				$this->row( $g, $pair[1], '#' . $pair[0], '-', 'none', __( 'The menu is in the other site. Export it with Tools > Export (Navigation Menu Items) and choose it in Appearance > Menus.', 'tainacan-interface' ) );
			}
		}
		if ( ! empty( $locations['menu_2'] ) ) {
			$this->row( $g, __( 'Secondary menu of the header', 'tainacan-interface' ), '#' . (int) $locations['menu_2'], '-', 'none', __( 'This theme has one menu in the header.', 'tainacan-interface' ) );
		}
		if ( ! $this->locations && ! $this->groups[ $g ]['rows'] ) {
			$this->row( $g, __( 'Menus', 'tainacan-interface' ), __( 'No menu in a location', 'tainacan-interface' ), '-', 'exact' );
		}
	}

	/**
	 * Widgets: the sidebar and the footer areas.
	 *
	 * @param array|null $exported Widgets of the file (sidebar => id => settings), or null.
	 */
	private function widgets( $exported ) {
		$g = 'widgets';
		$this->group( $g, __( 'Widgets', 'tainacan-interface' ), __( 'The blog sidebar of Blocksy goes to the sidebar of this theme, and its footer areas to the footer area, in order.', 'tainacan-interface' ) );
		$map      = array( 'sidebar-1' => 'tainacan-sidebar-right' );
		$data     = array();
		$snapshot = $this->get( 'sidebars_widgets', array() );
		if ( is_array( $exported ) ) {
			$data = $exported;
		} elseif ( $this->same_site ) {
			$data = is_array( $snapshot ) && isset( $snapshot['data'] ) && is_array( $snapshot['data'] ) ? $snapshot['data'] : array();
		} else {
			// The ids of the snapshot name widgets of the other site: here they would be other widgets.
			$data = array();
		}
		foreach ( $data as $sidebar => $widgets ) {
			if ( ! is_array( $widgets ) || ! $widgets || 'wp_inactive_widgets' === $sidebar ) {
				continue;
			}
			$target = isset( $map[ $sidebar ] ) ? $map[ $sidebar ] : ( 0 === strpos( (string) $sidebar, 'ct-footer-sidebar-' ) ? 'tainacan-sidebar-footer' : '' );
			if ( '' === $target ) {
				/* translators: %d: number of widgets. */
				$count = sprintf( _n( '%d widget', '%d widgets', count( $widgets ), 'tainacan-interface' ), count( $widgets ) );
				$this->row( $g, (string) $sidebar, $count, '-', 'none', __( 'An area this theme does not have; the widgets stay among the inactive ones.', 'tainacan-interface' ) );
				continue;
			}
			foreach ( $widgets as $key => $widget ) {
				// From this site: a list of ids. From the file: id => settings.
				$this->widgets[ $target ][ is_array( $widget ) ? (string) $key : (string) $widget ] = is_array( $widget ) ? $widget : null;
			}
			$labels = array(
				'tainacan-sidebar-right'  => __( 'Sidebar', 'tainacan-interface' ),
				'tainacan-sidebar-footer' => __( 'Footer', 'tainacan-interface' ),
			);
			/* translators: %d: number of widgets. */
			$count = sprintf( _n( '%d widget', '%d widgets', count( $widgets ), 'tainacan-interface' ), count( $widgets ) );
			$this->row( $g, (string) $sidebar, $count, $labels[ $target ], 'exact' );
		}
		if ( ! $this->groups[ $g ]['rows'] ) {
			$this->row( $g, __( 'Widgets', 'tainacan-interface' ), is_array( $exported ) ? __( 'None in the file', 'tainacan-interface' ) : __( 'None', 'tainacan-interface' ), '-', 'exact', is_array( $exported ) ? '' : __( 'To bring widgets from another site, export from Blocksy with "Widgets settings" checked.', 'tainacan-interface' ) );
		}
	}

	/**
	 * Additional CSS of Blocksy.
	 *
	 * @param string $css CSS.
	 */
	private function custom_css( $css ) {
		$g = 'css';
		$this->group( $g, __( 'Additional CSS', 'tainacan-interface' ), __( 'The Additional CSS of Blocksy goes after the one of this theme, marked with a comment. Rules written for the classes of Blocksy (.ct-…, .entry-card…) have no effect here and are counted below; images it points to in the other site come with the import.', 'tainacan-interface' ) );
		if ( '' === trim( $css ) ) {
			$this->row( $g, __( 'Additional CSS', 'tainacan-interface' ), __( 'Empty', 'tainacan-interface' ), '-', 'exact' );
			return;
		}
		$this->css = $css;
		$rules     = preg_match_all( '/\{/', $css );
		$blocksy   = preg_match_all( '/(\.ct-|\.entry-card|\.hero-section|\[data-header|\[data-footer|\[data-row|\.page-title\b)[^{]*\{/', $css );
		$this->row(
			$g,
			__( 'Additional CSS', 'tainacan-interface' ),
			/* translators: 1: size in kB; 2: number of rules. */
			sprintf( __( '%1$s kB, %2$d rules', 'tainacan-interface' ), number_format_i18n( strlen( $css ) / 1024, 1 ), $rules ),
			__( 'Copied, after the CSS of this theme', 'tainacan-interface' ),
			$blocksy ? 'approx' : 'exact',
			/* translators: %d: number of CSS rules. */
			$blocksy ? sprintf( _n( '%d rule uses classes of Blocksy and does nothing here; review it in Customize > Additional CSS.', '%d rules use classes of Blocksy and do nothing here; review them in Customize > Additional CSS.', $blocksy, 'tainacan-interface' ), $blocksy ) : ''
		);
		// Images of the other site in url(…).
		$host = wp_parse_url( isset( $this->source['site_url'] ) ? (string) $this->source['site_url'] : '', PHP_URL_HOST );
		if ( ! $this->same_site && $host && preg_match_all( '/url\(\s*[\'"]?(https?:\/\/[^\'")\s]+)/i', $css, $m ) ) {
			$n = 0;
			foreach ( array_unique( $m[1] ) as $url ) {
				if ( wp_parse_url( $url, PHP_URL_HOST ) === $host && preg_match( '/\.(jpe?g|png|gif|webp|avif|svg)(\?|$)/i', $url ) ) {
					$this->image( 'css-' . ( ++$n ), 0, $url, __( 'Image in the Additional CSS', 'tainacan-interface' ) );
				}
			}
		}
	}

	/**
	 * The palette of Blocksy for the content of pages and posts.
	 */
	private function content_palette() {
		$g = 'content';
		$this->group( $g, __( 'Palette colors in the content', 'tainacan-interface' ), __( 'Blocks of pages and posts painted with the palette of Blocksy (classes has-palette-color-N and the variables --theme-palette-color-N) lose their color in another theme. The import keeps those colors in this theme, with the same names, and offers them in the block editor.', 'tainacan-interface' ) );
		$this->set[ $g ]['tainacan_interface_blocksy_palette'] = $this->palette;
		/* translators: %d: number of colors. */
		$count = sprintf( _n( '%d color', '%d colors', count( $this->palette ), 'tainacan-interface' ), count( $this->palette ) );
		$this->row( $g, __( 'Palette', 'tainacan-interface' ), $count, __( 'Same colors, same names', 'tainacan-interface' ), 'exact', '', array( 'palette' => $this->palette ) );
	}

	/**
	 * Everything the plan did not read, grouped.
	 */
	private function find_left_out() {
		$groups = array(
			'shop'     => array( '/^(woo|product|shop|cart|checkout|has_ajax_add_to_cart|quantity_type|blocksy_woo)/', __( 'Shop (WooCommerce)', 'tainacan-interface' ) ),
			'tainacan' => array( '/^(tnc_col_|tainacan-)/', __( 'Tainacan, details without an equivalent', 'tainacan-interface' ) ),
			'posts'    => array( '/^(blog_|single_|categories_|search_|author_|page_|archive_)/', __( 'Posts and pages, details', 'tainacan-interface' ) ),
			'other'    => array( '/./', __( 'Other settings', 'tainacan-interface' ) ),
		);
		foreach ( $this->mods as $key => $value ) {
			$key = (string) $key;
			if ( isset( $this->used[ $key ] ) || null === $value || false === $value || '' === $value || array() === $value || in_array( $key, array( '0', 'custom_css_post_id' ), true ) ) {
				continue;
			}
			foreach ( $groups as $id => $group ) {
				if ( preg_match( $group[0], $key ) ) {
					$this->left_out[ $id ]['label']  = $group[1];
					$this->left_out[ $id ]['keys'][] = $key;
					break;
				}
			}
		}
	}

	/**
	 * Counts of the rows by level, over the groups given.
	 *
	 * @param string[]|null $only Groups; null for all.
	 * @return array<string, int>
	 */
	public function counts( $only = null ) {
		$counts = array(
			'exact'  => 0,
			'approx' => 0,
			'adjust' => 0,
			'none'   => 0,
		);
		foreach ( $this->groups as $id => $group ) {
			if ( null !== $only && ! in_array( $id, $only, true ) ) {
				continue;
			}
			foreach ( $group['rows'] as $row ) {
				++$counts[ $row['level'] ];
			}
		}
		return $counts;
	}
}
