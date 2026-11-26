<?php
/**
 * Reads PHP serialized data without unserialize().
 *
 * The export of the Blocksy Companion (.dat) is serialize() of an array, and
 * the widgets in it are stdClass objects. unserialize() would build objects
 * (even with allowed_classes false it builds __PHP_Incomplete_Class), so this
 * reader walks the format and returns only scalars and arrays: an object
 * becomes the array of its properties, a reference becomes null and a
 * custom-serialized class (C:) makes the reading fail.
 *
 * Like the Companion, it tolerates a string whose declared length no longer
 * matches its bytes (a file edited by hand or saved with another encoding):
 * it looks for the end of the string where the next value starts.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reader of the serialize() format.
 */
class Tainacan_Interface_Serialized_Reader {

	/**
	 * Deepest nesting accepted.
	 */
	const MAX_DEPTH = 64;

	/**
	 * The text being read.
	 *
	 * @var string
	 */
	private $text;

	/**
	 * Position of the next byte.
	 *
	 * @var int
	 */
	private $pos = 0;

	/**
	 * Length of the text.
	 *
	 * @var int
	 */
	private $len;

	/**
	 * Whether a string length had to be guessed.
	 *
	 * @var bool
	 */
	public $repaired = false;

	/**
	 * Reads a whole text.
	 *
	 * @param string $text Serialized text.
	 * @return array{ok: bool, value: mixed, repaired: bool}
	 */
	public static function read( $text ) {
		$reader = new self( (string) $text );
		try {
			$value = $reader->value( 0 );
			$ok    = trim( substr( $reader->text, $reader->pos ) ) === '';
		} catch ( UnexpectedValueException $e ) {
			$value = null;
			$ok    = false;
		}
		return array(
			'ok'       => $ok,
			'value'    => $value,
			'repaired' => $reader->repaired,
		);
	}

	/**
	 * Constructor.
	 *
	 * @param string $text Serialized text.
	 */
	private function __construct( $text ) {
		$this->text = $text;
		$this->len  = strlen( $text );
	}

	/**
	 * Fails the reading.
	 *
	 * @throws UnexpectedValueException Always.
	 */
	private function fail() {
		throw new UnexpectedValueException( 'serialized' );
	}

	/**
	 * Consumes an expected piece of text.
	 *
	 * @param string $expected Text.
	 */
	private function expect( $expected ) {
		if ( substr( $this->text, $this->pos, strlen( $expected ) ) !== $expected ) {
			$this->fail();
		}
		$this->pos += strlen( $expected );
	}

	/**
	 * Reads up to a delimiter and consumes it.
	 *
	 * @param string $delimiter One character.
	 * @return string
	 */
	private function until( $delimiter ) {
		$end = strpos( $this->text, $delimiter, $this->pos );
		if ( false === $end ) {
			$this->fail();
		}
		$piece     = substr( $this->text, $this->pos, $end - $this->pos );
		$this->pos = $end + 1;
		return $piece;
	}

	/**
	 * Reads an integer written in the text.
	 *
	 * @param string $delimiter What ends it.
	 * @return int
	 */
	private function integer( $delimiter ) {
		$piece = $this->until( $delimiter );
		if ( ! preg_match( '/^-?\d{1,18}$/', $piece ) ) {
			$this->fail();
		}
		return (int) $piece;
	}

	/**
	 * Reads one value.
	 *
	 * @param int $depth Nesting.
	 * @return mixed
	 */
	private function value( $depth ) {
		if ( $depth > self::MAX_DEPTH || $this->pos >= $this->len ) {
			$this->fail();
		}
		$type = $this->text[ $this->pos ];
		switch ( $type ) {
			case 'N':
				$this->expect( 'N;' );
				return null;
			case 'b':
				$this->expect( 'b:' );
				return '1' === $this->until( ';' );
			case 'i':
				$this->expect( 'i:' );
				return $this->integer( ';' );
			case 'd':
				$this->expect( 'd:' );
				$piece = $this->until( ';' );
				if ( ! is_numeric( $piece ) && ! in_array( $piece, array( 'INF', '-INF', 'NAN' ), true ) ) {
					$this->fail();
				}
				return (float) $piece;
			case 's':
				$this->expect( 's:' );
				return $this->string( $this->integer( ':' ) );
			case 'a':
				$this->expect( 'a:' );
				return $this->entries( $this->integer( ':' ), $depth );
			case 'O':
				// An object: its class is read and dropped, its properties become an array.
				$this->expect( 'O:' );
				$this->string( $this->integer( ':' ), true );
				$this->expect( ':' );
				return $this->entries( $this->integer( ':' ), $depth, true );
			case 'r':
			case 'R':
				$this->pos += 2;
				$this->integer( ';' );
				return null;
			default:
				// C: (a class with its own format) and anything else.
				$this->fail();
		}
		return null;
	}

	/**
	 * Reads a quoted string of a declared length, with the tolerance above.
	 *
	 * @param int  $length     Declared length in bytes.
	 * @param bool $class_name Whether it is the class name of an object (followed by ':').
	 * @return string
	 */
	private function string( $length, $class_name = false ) {
		$this->expect( '"' );
		$close = $class_name ? '":' : '";';
		if ( $length >= 0 && substr( $this->text, $this->pos + $length, 2 ) === $close ) {
			$piece = substr( $this->text, $this->pos, $length );
			// The quote; the ':' after a class name is read by the caller.
			$this->pos += $length + ( $class_name ? 1 : 2 );
			return $piece;
		}
		if ( $class_name ) {
			$this->fail();
		}
		// The length is wrong: the string ends at the first '";' followed by the start of a value or the end of an array.
		$from = $this->pos;
		while ( true ) {
			$end = strpos( $this->text, '";', $from );
			if ( false === $end ) {
				$this->fail();
			}
			$next = substr( $this->text, $end + 2, 2 );
			if ( '' === $next || '}' === $next[0] || preg_match( '/^[sibdNaOrR][:;]/', $next ) ) {
				break;
			}
			$from = $end + 1;
		}
		$this->repaired = true;
		$piece          = substr( $this->text, $this->pos, $end - $this->pos );
		$this->pos      = $end + 2;
		return $piece;
	}

	/**
	 * Reads the entries of an array or of the properties of an object.
	 *
	 * @param int  $count  Declared number of entries.
	 * @param int  $depth  Nesting.
	 * @param bool $is_object Whether the keys are property names.
	 * @return array
	 */
	private function entries( $count, $depth, $is_object = false ) {
		if ( $count < 0 || $count > 200000 ) {
			$this->fail();
		}
		$this->expect( '{' );
		$entries = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$key = $this->value( $depth + 1 );
			if ( ! is_int( $key ) && ! is_string( $key ) ) {
				$this->fail();
			}
			if ( $is_object && is_string( $key ) && false !== strpos( $key, "\0" ) ) {
				// Private and protected properties carry "\0Class\0" or "\0*\0" before the name.
				$key = substr( $key, strrpos( $key, "\0" ) + 1 );
			}
			$entries[ $key ] = $this->value( $depth + 1 );
		}
		$this->expect( '}' );
		return $entries;
	}
}
