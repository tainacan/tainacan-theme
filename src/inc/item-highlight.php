<?php
/**
 * Highlighted item record, from the Museu Histórico Nacional work.
 *
 * On the lists, the cards, records, masonry and table of Tainacan get a
 * surface, a title with an accent bar, quiet labels and terms as chips. On the
 * item page, the title gets an identification line (collection and the
 * registration number) and the record gets the same reading order: labels
 * quiet, values first, terms as chips, sections marked.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the highlight is on. Theme mod in the Customizer, plus a filter; the
 * design of version 4 is built on it.
 *
 * @return bool
 */
function tainacan_interface_highlight_enabled() {
	/**
	 * Turns the highlighted item record (lists and item page) on or off.
	 *
	 * @param bool $enabled Value of the Customizer option.
	 */
	return (bool) apply_filters( 'tainacan_interface_highlight_enabled', tainacan_interface_is_modern() || (bool) get_theme_mod( 'tainacan_interface_item_highlight', true ) );
}

/**
 * Body class that scopes every rule of the highlight stylesheet.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tainacan_interface_highlight_body_class( $classes ) {
	if ( tainacan_interface_highlight_enabled() && ( tainacan_interface_is_list_page() || tainacan_interface_is_item_page() ) ) {
		$classes[] = 'tt2-skin';
	}
	return $classes;
}
add_filter( 'body_class', 'tainacan_interface_highlight_body_class' );

/**
 * Stylesheet in the head: records and masonry measure the cards already drawn
 * to place their columns, so the rules must be there before the list renders.
 */
function tainacan_interface_highlight_enqueue() {
	if ( ! tainacan_interface_highlight_enabled() || ! ( tainacan_interface_is_list_page() || tainacan_interface_is_item_page() ) ) {
		return;
	}
	wp_enqueue_style( 'tt2-item-highlight', get_template_directory_uri() . '/assets/css/item-highlight.css', array(), TAINACAN_INTERFACE_VERSION );
}
add_action( 'wp_enqueue_scripts', 'tainacan_interface_highlight_enqueue' );

/**
 * The metadatum that holds the registration number of the items of a
 * collection: the filter's answer, or the first metadatum whose name says so
 * (número de registro, tombo, inventário, identificador, accession...).
 *
 * @param \Tainacan\Entities\Collection $collection Collection.
 * @return \Tainacan\Entities\Metadatum|null
 */
function tainacan_interface_identifier_metadatum( $collection ) {
	static $memo = array();

	if ( ! $collection instanceof \Tainacan\Entities\Collection || ! class_exists( '\Tainacan\Repositories\Metadata' ) ) {
		return null;
	}

	$collection_id = (int) $collection->get_id();
	if ( array_key_exists( $collection_id, $memo ) ) {
		return $memo[ $collection_id ];
	}

	$repository = \Tainacan\Repositories\Metadata::get_instance();

	/**
	 * Id of the metadatum that holds the registration number in a collection.
	 * Return 0 to guess from the metadata names, -1 to show no number.
	 *
	 * @param int                           $metadatum_id Metadatum ID.
	 * @param \Tainacan\Entities\Collection $collection   Collection.
	 */
	$chosen = (int) apply_filters( 'tainacan_interface_identifier_metadatum', 0, $collection );
	$found  = null;

	if ( $chosen > 0 ) {
		$metadatum = $repository->fetch( $chosen );
		$found     = $metadatum instanceof \Tainacan\Entities\Metadatum ? $metadatum : null;
	} elseif ( 0 === $chosen ) {
		// Names that say registration number, accession or inventory; a date of registration or a photo record do not count.
		$pattern = '/^\s*((n[úu]mero|n\.?\s*[º°o]\.?)\s*(de\s+)?(registro|tombo|invent[aá]rio)|tombo|n[úu]mero\s+de\s+invent[aá]rio|invent[aá]rio|identificador|identifier|accession(\s+number)?|inventory(\s+number)?)\s*$/iu';
		foreach ( (array) $repository->fetch_by_collection( $collection, array(), 'OBJECT' ) as $metadatum ) {
			if ( $metadatum instanceof \Tainacan\Entities\Metadatum && (int) $metadatum->get_parent() <= 0 && false === stripos( (string) $metadatum->get_metadata_type(), 'Date' ) && preg_match( $pattern, (string) $metadatum->get_name() ) ) {
				$found = $metadatum;
				break;
			}
		}
	}

	$memo[ $collection_id ] = $found;
	return $found;
}

/**
 * The registration number of an item, as plain text, or ''.
 *
 * @param \Tainacan\Entities\Item $item Item.
 * @return string
 */
function tainacan_interface_item_identifier( $item ) {
	if ( ! $item instanceof \Tainacan\Entities\Item || ! class_exists( '\Tainacan\Entities\Item_Metadata_Entity' ) ) {
		return '';
	}

	$metadatum = tainacan_interface_identifier_metadatum( $item->get_collection() );
	if ( null === $metadatum || ( 'publish' !== $metadatum->get_status() && ! current_user_can( 'edit_post', $item->get_id() ) ) ) {
		return '';
	}

	$value = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
	$text  = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $value->get_value_as_html() ) ) );

	return wp_html_excerpt( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), 80, '…' );
}

/**
 * Prints the identification line under the item title: the collection and,
 * when the collection has one, the registration number.
 */
function tainacan_interface_the_item_identity() {
	if ( ! tainacan_interface_highlight_enabled() || ! tainacan_interface_is_item_page() || ! function_exists( 'tainacan_get_item' ) ) {
		return;
	}

	$item = tainacan_get_item();
	if ( ! $item instanceof \Tainacan\Entities\Item ) {
		return;
	}

	$collection = $item->get_collection();
	$number     = tainacan_interface_item_identifier( $item );

	if ( ! $collection && '' === $number ) {
		return;
	}
	?>
	<p class="tt2-item-identity">
		<?php if ( $collection ) : ?>
			<a class="tt2-item-identity__collection" href="<?php echo esc_url( $collection->get_url() ); ?>"><?php echo esc_html( tainacan_interface_plain( $collection->get_name() ) ); ?></a>
		<?php endif; ?>
		<?php if ( '' !== $number ) : ?>
			<span class="tt2-item-identity__number">
				<?php
				printf(
					/* translators: %s: registration (accession) number of the item. */
					esc_html__( 'Reg. no. %s', 'tainacan-interface' ),
					'<strong>' . esc_html( $number ) . '</strong>'
				);
				?>
			</span>
		<?php endif; ?>
	</p>
	<?php
}
