<?php
/**
 * "How to cite" panel of the item page: ABNT, APA, Vancouver, BibTeX and RIS,
 * from the title, the first author and date metadata, the collection and the site.
 * Only values the visitor may read enter the reference.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * What the reference is made of.
 *
 * @param int $item_id Item.
 * @return array { title, authors[], year, collection, site, url }
 */
function tainacan_interface_citation_data( $item_id ) {
	$item = tainacan_interface_readable_item( (int) $item_id );
	$data = array(
		'title'      => tainacan_interface_plain( get_the_title( $item_id ) ),
		'authors'    => array(),
		'year'       => '',
		'collection' => '',
		'site'       => tainacan_interface_plain( get_bloginfo( 'name' ) ),
		'url'        => (string) get_permalink( $item_id ),
	);

	if ( null === $item || ! class_exists( '\Tainacan\Repositories\Item_Metadata' ) ) {
		return $data;
	}

	$collection = $item->get_collection();
	if ( $collection ) {
		$data['collection'] = tainacan_interface_plain( $collection->get_name() );
	}

	/**
	 * Name patterns of the metadata used in the reference.
	 *
	 * @param array $patterns author and date regular expressions.
	 */
	$patterns = (array) apply_filters(
		'tainacan_interface_citation_patterns',
		array(
			'author' => '/^\s*(autor(es|ia)?|author(s)?|creator|cria(dor|ç[aã]o)|artista|artist)\b/iu',
			'date'   => '/^\s*(data|date|ano|year)\b/iu',
		)
	);

	foreach ( (array) \Tainacan\Repositories\Item_Metadata::get_instance()->fetch( $item, 'OBJECT' ) as $item_metadatum ) {
		$metadatum = $item_metadatum instanceof \Tainacan\Entities\Item_Metadata_Entity ? $item_metadatum->get_metadatum() : null;
		if ( ! $metadatum instanceof \Tainacan\Entities\Metadatum || 'publish' !== $metadatum->get_status() ) {
			continue;
		}

		$name = (string) $metadatum->get_name();

		if ( empty( $data['authors'] ) && preg_match( $patterns['author'], $name ) ) {
			$text = trim( tainacan_interface_plain( $item_metadatum->get_value_as_string() ) );
			if ( '' !== $text ) {
				$data['authors'] = array_values( array_filter( array_map( 'trim', preg_split( '/\s*(?:;|\|)\s*/u', $text ) ) ) );
			}
		}

		if ( '' === $data['year'] && preg_match( $patterns['date'], $name ) ) {
			$text = tainacan_interface_plain( $item_metadatum->get_value_as_string() );
			if ( preg_match( '/\b(\d{4})\b/', $text, $match ) ) {
				$data['year'] = $match[1];
			}
		}
	}

	return $data;
}

/**
 * Whether a name reads as an institution rather than a person: it is not turned
 * into "SURNAME, Name".
 *
 * @param string $name Name.
 * @return bool
 */
function tainacan_interface_citation_is_entity( $name ) {
	return count( preg_split( '/\s+/u', trim( $name ) ) ) > 5 || 1 === preg_match( '/\b(museu|instituto|universidade|arquivo|biblioteca|funda[cç][aã]o|minist[eé]rio|museum|institute|university|archive|library|foundation|equipe|team)\b/iu', $name );
}

/**
 * Splits a person's name into surname and given names.
 *
 * @param string $name Name.
 * @return array array( surname, given )
 */
function tainacan_interface_citation_split( $name ) {
	if ( false !== strpos( $name, ',' ) ) {
		$parts = array_map( 'trim', explode( ',', $name, 2 ) );
		return array( $parts[0], $parts[1] );
	}
	$words   = preg_split( '/\s+/u', trim( $name ) );
	$surname = array_pop( $words );
	return array( (string) $surname, implode( ' ', $words ) );
}

/**
 * Initials of given names: "Peter Paul" gives array( 'P', 'P' ).
 *
 * @param string $given Given names.
 * @return string[]
 */
function tainacan_interface_citation_initials( $given ) {
	$letters = array();
	foreach ( preg_split( '/\s+/u', trim( $given ) ) as $word ) {
		if ( '' !== $word && ! in_array( mb_strtolower( $word ), array( 'de', 'da', 'do', 'das', 'dos', 'e' ), true ) ) {
			$letters[] = mb_strtoupper( mb_substr( $word, 0, 1 ) );
		}
	}
	return $letters;
}

/**
 * The references: format id => array( label, text, html? ). The text is plain;
 * the bold or italic parts are added by whoever prints the formatted version.
 *
 * @param int $item_id Item.
 * @return array
 */
function tainacan_interface_citations( $item_id ) {
	$d       = tainacan_interface_citation_data( $item_id );
	$year    = '' !== $d['year'] ? $d['year'] : __( 'n.d.', 'tainacan-interface' );
	$access  = wp_date( 'j M. Y' );
	$abnt    = array();
	$apa     = array();
	$vanc    = array();
	$bib     = array();
	$context = trim( $d['collection'] . ( '' !== $d['collection'] && '' !== $d['site'] ? ', ' : '' ) . $d['site'] );

	foreach ( $d['authors'] as $author ) {
		if ( tainacan_interface_citation_is_entity( $author ) ) {
			$abnt[] = mb_strtoupper( $author );
			$apa[]  = $author;
			$vanc[] = $author;
			$bib[]  = '{' . $author . '}';
			continue;
		}
		list( $surname, $given ) = tainacan_interface_citation_split( $author );
		$letters                 = tainacan_interface_citation_initials( $given );
		$abnt[]                  = mb_strtoupper( $surname ) . ( '' !== $given ? ', ' . $given : '' );
		$apa[]                   = $surname . ( $letters ? ', ' . implode( '. ', $letters ) . '.' : '' );
		$vanc[]                  = $surname . ( $letters ? ' ' . implode( '', $letters ) : '' );
		$bib[]                   = $surname . ( '' !== $given ? ', ' . $given : '' );
	}

	$key = sanitize_title( ( isset( $d['authors'][0] ) ? tainacan_interface_citation_split( $d['authors'][0] )[0] : 'item' ) . '-' . $d['year'] );
	$ris = array( 'TY  - GEN' );
	foreach ( $d['authors'] as $author ) {
		$ris[] = 'AU  - ' . $author;
	}
	$ris[] = 'TI  - ' . $d['title'];
	if ( '' !== $d['year'] ) {
		$ris[] = 'PY  - ' . $d['year'];
	}
	if ( '' !== $d['collection'] ) {
		$ris[] = 'T2  - ' . $d['collection'];
	}
	$ris[] = 'PB  - ' . $d['site'];
	$ris[] = 'UR  - ' . $d['url'];
	$ris[] = 'ER  - ';

	$bibtex = array( '@misc{' . $key . ',' );
	if ( $bib ) {
		$bibtex[] = '  author = {' . implode( ' and ', $bib ) . '},';
	}
	$bibtex[] = '  title = {' . $d['title'] . '},';
	if ( '' !== $d['year'] ) {
		$bibtex[] = '  year = {' . $d['year'] . '},';
	}
	$bibtex[] = '  howpublished = {' . $context . '},';
	$bibtex[] = '  url = {' . $d['url'] . '}';
	$bibtex[] = '}';

	$citations = array(
		'abnt'      => array(
			__( 'ABNT (NBR 6023)', 'tainacan-interface' ),
			trim( ( $abnt ? implode( '; ', $abnt ) . '. ' : '' ) . $d['title'] . '. ' . ( '' !== $d['collection'] ? 'In: ' . $d['collection'] . '. ' : '' ) . $d['site'] . ( '' !== $d['year'] ? ', ' . $d['year'] : '' ) . '. ' . sprintf( 'Disponível em: %1$s. Acesso em: %2$s.', $d['url'], $access ) ),
		),
		'apa'       => array(
			__( 'APA (7th edition)', 'tainacan-interface' ),
			trim( ( $apa ? implode( ', ', $apa ) . ' ' : '' ) . '(' . $year . '). ' . $d['title'] . '. ' . ( '' !== $context ? $context . '. ' : '' ) . $d['url'] ),
		),
		'vancouver' => array(
			__( 'Vancouver (ICMJE)', 'tainacan-interface' ),
			trim( ( $vanc ? implode( ', ', $vanc ) . '. ' : '' ) . $d['title'] . ' [Internet]. ' . ( '' !== $context ? $context . '; ' : '' ) . $year . '. Available from: ' . $d['url'] ),
		),
		'bibtex'    => array( __( 'BibTeX', 'tainacan-interface' ), implode( "\n", $bibtex ) ),
		'ris'       => array( __( 'RIS (EndNote, Mendeley, Zotero)', 'tainacan-interface' ), implode( "\n", $ris ) ),
	);

	/**
	 * The references of the "How to cite" panel.
	 *
	 * @param array $citations Format id => array( label, text ).
	 * @param int   $item_id   Item.
	 */
	return (array) apply_filters( 'tainacan_interface_citations', $citations, $item_id );
}

/**
 * Prints the panel.
 */
function tainacan_interface_the_item_citation() {
	if ( ! tainacan_interface_opt( 'item_citation' ) || ! tainacan_interface_is_item_page() ) {
		return;
	}

	$citations = tainacan_interface_citations( get_the_ID() );
	if ( empty( $citations ) ) {
		return;
	}
	?>
	<section class="tt2-cite" data-tt2-cite aria-labelledby="tt2-cite-title">
		<h2 id="tt2-cite-title" class="tt2-cite__title label"><?php esc_html_e( 'How to cite', 'tainacan-interface' ); ?></h2>
		<div class="tt2-cite__tabs" role="tablist">
			<?php $tainacan_interface_first = true; ?>
			<?php foreach ( $citations as $id => $citation ) : ?>
				<button type="button" role="tab" class="tt2-cite__tab" id="tt2-cite-tab-<?php echo esc_attr( $id ); ?>" aria-controls="tt2-cite-<?php echo esc_attr( $id ); ?>" aria-selected="<?php echo $tainacan_interface_first ? 'true' : 'false'; ?>"><?php echo esc_html( $citation[0] ); ?></button>
				<?php $tainacan_interface_first = false; ?>
			<?php endforeach; ?>
		</div>
		<?php $tainacan_interface_first = true; ?>
		<?php foreach ( $citations as $id => $citation ) : ?>
			<div class="tt2-cite__text" role="tabpanel" id="tt2-cite-<?php echo esc_attr( $id ); ?>" aria-labelledby="tt2-cite-tab-<?php echo esc_attr( $id ); ?>"<?php echo $tainacan_interface_first ? '' : ' hidden'; ?> tabindex="0"><?php echo esc_html( $citation[1] ); ?></div>
			<?php $tainacan_interface_first = false; ?>
		<?php endforeach; ?>
		<button type="button" class="tt2-pill-button" data-tt2-cite-copy><?php esc_html_e( 'Copy reference', 'tainacan-interface' ); ?></button>
	</section>
	<?php
}
