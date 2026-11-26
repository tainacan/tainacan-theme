<?php
/**
 * New design: the header of the items pages and the breadcrumb.
 *
 * The hooks of the classic header (tainacan-interface-collection-header,
 * -collection-description, -collection-header-bottom and the taxonomy ones) fire
 * here too, so what plugins and child themes print through them keeps appearing.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints the header of an items page.
 *
 * @param array $args {
 *     What the header shows.
 *
 *     @type int    $collection_id Collection, or 0.
 *     @type string $eyebrow       Text above the title.
 *     @type string $title         Title, plain text.
 *     @type string $description   Description, HTML.
 *     @type int    $banner_id     Attachment of the header image, or 0.
 *     @type string $icon          Icon HTML, already escaped, or ''.
 *     @type int    $count         Number of items, or -1 for none.
 *     @type string $share_url     Address to share, or ''.
 *     @type string $hook          Prefix of the actions: collection, taxonomy or '' for none.
 *     @type string $heading       Tag of the title.
 * }
 */
function tainacan_interface_the_page_header( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'collection_id' => 0,
			'eyebrow'       => '',
			'title'         => '',
			'description'   => '',
			'banner_id'     => 0,
			'icon'          => '',
			'count'         => -1,
			'share_url'     => '',
			'hook'          => 'collection',
			'heading'       => 'h1',
		)
	);

	$collection_id = (int) $args['collection_id'];
	$heading       = 'h2' === $args['heading'] ? 'h2' : 'h1';
	$layout        = tainacan_interface_opt( 'head_layout', $collection_id );
	$banner_mode   = 'compact' === $layout ? 'none' : tainacan_interface_opt( 'head_banner', $collection_id );
	$icon_shape    = tainacan_interface_opt( 'head_icon', $collection_id );
	$description   = tainacan_interface_opt( 'head_description', $collection_id );
	$desc_width    = tainacan_interface_opt( 'head_desc_width', $collection_id );

	$banner = '';
	if ( 'none' !== $banner_mode ) {
		if ( 'mosaic' !== $banner_mode && $args['banner_id'] ) {
			$banner = wp_get_attachment_image(
				(int) $args['banner_id'],
				'full',
				false,
				array(
					'class'         => 'tt2-head__banner-image',
					'alt'           => '',
					'fetchpriority' => 'high',
				)
			);
		}
		if ( ! $banner && 'image' !== $banner_mode ) {
			$banner = tainacan_interface_mosaic_html( tainacan_interface_collection_images( $collection_id, 8 ), 'tt2-head__banner-mosaic', false );
			$banner = $banner ? $banner : tainacan_interface_art_html( '' === $args['title'] ? 'tt' : ' ' . $args['title'] );
		}
	}

	$classes = array(
		'tt2-head',
		'tt2-head--' . $layout,
		'tt2-head--' . tainacan_interface_opt( 'head_banner_width' ),
		'tt2-head--icon-' . $icon_shape,
	);
	if ( $banner ) {
		$classes[] = 'has-banner';
	}
	$head_style = '';
	if ( 'auto' !== $desc_width ) {
		$classes[] = 'tt2-head--desc-' . $desc_width;
		if ( 'custom' === $desc_width ) {
			$head_style = '--tt2-desc-width: ' . absint( tainacan_interface_opt( 'head_desc_px' ) ) . 'px';
		}
	}
	?>
	<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $head_style ? ' style="' . esc_attr( $head_style ) . '"' : ''; ?>>
		<?php
		if ( '' !== $args['hook'] ) {
			do_action( 'tainacan-interface-' . $args['hook'] . '-header' );
		}

		if ( $banner ) :
			?>
			<div class="tt2-head__banner">
				<?php echo wp_kses( $banner, tainacan_interface_media_tags() ); ?>
				<span class="tt2-band" aria-hidden="true"></span>
			</div>
			<?php
		endif;
		?>
		<div class="tt2-head__body">
			<?php if ( 'none' !== $icon_shape ) : ?>
				<div class="tt2-head__icon" aria-hidden="true">
					<?php if ( $args['icon'] ) : ?>
						<?php echo wp_kses( $args['icon'], tainacan_interface_media_tags() ); ?>
					<?php else : ?>
						<?php echo wp_kses( tainacan_interface_art_html( $args['title'] ), tainacan_interface_media_tags() ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="tt2-head__text">
				<?php if ( $args['eyebrow'] && tainacan_interface_opt( 'head_eyebrow' ) ) : ?>
					<p class="tt2-head__eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
				<?php endif; ?>

				<<?php echo tag_escape( $heading ); ?> class="tt2-head__title"><?php echo esc_html( $args['title'] ); ?></<?php echo tag_escape( $heading ); ?>>

				<?php if ( tainacan_interface_opt( 'head_rule' ) ) : ?>
					<span class="tt2-band tt2-head__rule" aria-hidden="true"></span>
				<?php endif; ?>

				<?php if ( 'hidden' !== $description && '' !== trim( wp_strip_all_tags( $args['description'] ) ) ) : ?>
					<div class="tt2-head__description tt2-head__description--<?php echo esc_attr( $description ); ?> tainacan-interface-rich-text">
						<?php echo wp_kses_post( wpautop( $args['description'] ) ); ?>
					</div>
				<?php endif; ?>

				<?php
				ob_start();
				if ( '' !== $args['hook'] ) {
					do_action( 'tainacan-interface-' . $args['hook'] . '-description' );
				}
				// The classic header of a term fires this one too.
				if ( 'taxonomy' === $args['hook'] ) {
					do_action( 'tainacan-interface-term-description' );
				}
				$extra = trim( (string) ob_get_clean() );

				$item_count    = (int) $args['count'];
				$show_count    = $item_count >= 0 && tainacan_interface_opt( 'head_count' );
				$show_surprise = tainacan_interface_opt( 'head_surprise' ) && tainacan_interface_drawer_enabled() && 'off' !== tainacan_interface_opt( 'drawer_mode', $collection_id );
				$show_share    = '' !== $args['share_url'] && tainacan_interface_opt( 'head_share' );

				if ( $show_count || $show_surprise || $show_share || '' !== $extra ) :
					?>
					<div class="tt2-head__actions">
						<?php if ( $show_count ) : ?>
							<span class="tt2-head__count">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: number of items. */
										_n( '%s item', '%s items', $item_count, 'tainacan-interface' ),
										number_format_i18n( $item_count )
									)
								);
								?>
							</span>
						<?php endif; ?>
						<?php if ( $show_surprise ) : ?>
							<button type="button" class="tt2-pill-button" data-tt2-surprise hidden><?php esc_html_e( 'Surprise me', 'tainacan-interface' ); ?></button>
						<?php endif; ?>
						<?php if ( $show_share ) : ?>
							<details class="tt2-share">
								<summary class="tt2-pill-button"><?php esc_html_e( 'Share', 'tainacan-interface' ); ?></summary>
								<div class="tt2-share__links"><?php tainacan_interface_the_share_links( $args['share_url'], $args['title'], 'images', 'tt2-share__link' ); ?></div>
							</details>
						<?php endif; ?>
						<?php
						if ( '' !== $extra ) {
							// tnc-check-ignore TNC-S05: output of a public action of the theme, printed as the classic header prints it.
							echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- printed by callbacks of a public action of the theme, whose output is their responsibility, as in the classic header.
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		if ( '' !== $args['hook'] ) {
			do_action( 'tainacan-interface-' . $args['hook'] . '-header-bottom' );
		}
		?>
	</header>
	<?php
}

/**
 * Header of a collection's items page.
 *
 * @param bool $on_item Whether it is above an item: the title of the page is the
 *                      item's, and the actions of the list do not belong there.
 */
function tainacan_interface_the_collection_header( $on_item = false ) {
	$collection_id = (int) tainacan_get_collection_id();
	$collection    = tainacan_get_collection();
	$title         = tainacan_interface_plain( tainacan_get_the_collection_name() );
	$icon          = '';

	if ( 'none' === tainacan_interface_opt( 'head_icon', $collection_id ) ) {
		$icon = '';
	} elseif ( has_post_thumbnail( $collection_id ) ) {
		$icon = get_the_post_thumbnail(
			$collection_id,
			'tainacan-medium',
			array(
				'class' => 'tt2-head__icon-image',
				'alt'   => '',
			)
		);
	} else {
		$icon = tainacan_interface_mosaic_html( tainacan_interface_collection_images( $collection_id, 4 ), 'tt2-head__icon-mosaic', false );
	}

	tainacan_interface_the_page_header(
		array(
			'collection_id' => $collection_id,
			'eyebrow'       => __( 'Collection', 'tainacan-interface' ),
			'title'         => $title,
			'description'   => (string) tainacan_get_the_collection_description(),
			'banner_id'     => $collection ? (int) $collection->get_header_image_id() : 0,
			'icon'          => $icon,
			'count'         => tainacan_interface_collection_count( $collection_id ),
			'share_url'     => tainacan_interface_current_url(),
			'hook'          => $on_item ? '' : 'collection',
			'heading'       => $on_item ? 'h2' : 'h1',
		)
	);
	if ( 'show' === tainacan_interface_opt( 'head_menu', $collection_id ) ) {
		tainacan_interface_the_collection_menu( $collection_id );
	}
}

/**
 * Header of a term's items page.
 */
function tainacan_interface_the_term_header() {
	$wp_term = tainacan_get_term();
	if ( ! $wp_term instanceof WP_Term ) {
		return;
	}

	$taxonomy = get_taxonomy( $wp_term->taxonomy );
	$term     = \Tainacan\Repositories\Terms::get_instance()->fetch( $wp_term->term_id, $wp_term->taxonomy );
	$link     = get_term_link( $wp_term );

	tainacan_interface_the_page_header(
		array(
			'eyebrow'     => $taxonomy ? $taxonomy->labels->name : '',
			'title'       => tainacan_interface_plain( $wp_term->name ),
			'description' => (string) $wp_term->description,
			'banner_id'   => $term ? (int) $term->get_header_image_id() : 0,
			'icon'        => $term && $term->get_header_image_id() ? wp_get_attachment_image( (int) $term->get_header_image_id(), 'medium', false, array( 'alt' => '' ) ) : '',
			'count'       => (int) $wp_term->count,
			'share_url'   => is_wp_error( $link ) ? '' : $link,
			'hook'        => 'taxonomy',
		)
	);
}

/**
 * The first terms of an item, by the metadata the visitor may read.
 *
 * @param \Tainacan\Entities\Item $item  Item.
 * @param int                     $count How many at most.
 * @return string[]
 */
function tainacan_interface_item_first_terms( $item, $count = 3 ) {
	$terms = array();
	if ( ! class_exists( '\Tainacan\Repositories\Item_Metadata' ) ) {
		return $terms;
	}
	foreach ( (array) \Tainacan\Repositories\Item_Metadata::get_instance()->fetch( $item, 'OBJECT' ) as $item_metadatum ) {
		$metadatum = $item_metadatum instanceof \Tainacan\Entities\Item_Metadata_Entity ? $item_metadatum->get_metadatum() : null;
		if ( ! $metadatum instanceof \Tainacan\Entities\Metadatum || 'publish' !== $metadatum->get_status() || 'never' === $metadatum->get_display() ) {
			continue;
		}
		if ( 'Tainacan\Metadata_Types\Taxonomy' !== (string) $metadatum->get_metadata_type() ) {
			continue;
		}
		// A metadatum of a single value gives the Term itself, not a list.
		$value = $item_metadatum->get_value();
		foreach ( is_array( $value ) ? $value : array( $value ) as $term ) {
			$name = $term instanceof \Tainacan\Entities\Term ? $term->get_name() : '';
			if ( '' !== $name ) {
				$terms[] = tainacan_interface_plain( $name );
			}
		}
		if ( count( $terms ) >= $count ) {
			break;
		}
	}
	return array_slice( $terms, 0, $count );
}

/**
 * Heading of the item page in the new design.
 */
function tainacan_interface_the_item_heading() {
	$item = tainacan_get_item();

	if ( get_theme_mod( 'tainacan_single_item_collection_header', false ) ) {
		tainacan_interface_the_collection_header( true );
	}
	?>
	<div class="tt2-item-head tainacan-single-item-heading">
		<h1 class="tt2-item-head__title"><?php the_title(); ?></h1>
		<?php
		tainacan_interface_the_item_identity();

		if ( tainacan_interface_opt( 'item_subtitle' ) ) {
			$line = array( get_the_date() );
			if ( $item instanceof \Tainacan\Entities\Item ) {
				$line = array_merge( $line, tainacan_interface_item_first_terms( $item ) );
			}
			echo '<p class="tt2-item-head__subtitle">' . esc_html( implode( ' · ', $line ) ) . '</p>';
		}

		$share = (bool) get_theme_mod( 'tainacan_single_item_display_share_buttons', true );
		$edit  = '';
		if ( function_exists( 'tainacan_the_item_edit_link' ) ) {
			ob_start();
			tainacan_the_item_edit_link( null, '', '' );
			$edit = trim( (string) ob_get_clean() );
		}

		if ( $share || '' !== $edit ) :
			?>
			<div class="tt2-item-head__actions">
				<?php if ( $share ) : ?>
					<details class="tt2-share">
						<summary class="tt2-pill-button"><?php esc_html_e( 'Share', 'tainacan-interface' ); ?></summary>
						<div class="tt2-share__links"><?php tainacan_interface_the_share_links( get_permalink(), get_the_title(), 'images', 'tt2-share__link' ); ?></div>
					</details>
				<?php endif; ?>
				<?php if ( '' !== $edit ) : ?>
					<span class="tainacan-edit-item-collection"><?php echo wp_kses_post( $edit ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * The steps of the breadcrumb of the design, for the Tainacan pages. Each step
 * is array( label, url ); the last one is the current page.
 *
 * @return array|null Steps, or null when the page is not one of them.
 */
function tainacan_interface_crumb_steps() {
	$collections = array( __( 'Collections', 'tainacan-interface' ), get_post_type_archive_link( 'tainacan-collection' ) );
	$home        = array( __( 'Home', 'tainacan-interface' ), home_url( '/' ) );

	if ( tainacan_interface_is_item_page() ) {
		$collection = tainacan_get_collection();
		$steps      = array();
		if ( tainacan_interface_opt( 'crumb_item_collection' ) ) {
			$steps = array( $home, $collections );
		}
		if ( $collection ) {
			$steps[] = array( $collection->get_name(), $collection->get_url() );
		}
		$steps[] = array( get_the_title(), '' );
		return $steps;
	}

	if ( is_post_type_archive( 'tainacan-collection' ) ) {
		return array( $home, array( $collections[0], '' ) );
	}

	if ( function_exists( 'tainacan_get_collection_id' ) && is_post_type_archive() && (int) tainacan_get_collection_id() > 0 ) {
		return array( $home, $collections, array( tainacan_get_the_collection_name(), '' ) );
	}

	if ( is_tax() && function_exists( 'tainacan_get_term' ) && tainacan_get_term() instanceof WP_Term ) {
		$term     = tainacan_get_term();
		$taxonomy = get_taxonomy( $term->taxonomy );
		$steps    = array( $home );
		if ( $taxonomy ) {
			$steps[] = array( $taxonomy->labels->name, '' );
		}
		$steps[] = array( $term->name, '' );
		return $steps;
	}

	return null;
}

/**
 * The breadcrumb of the design. Pages outside Tainacan reuse the classic crumbs,
 * so a category, a search or a page gets the same capsule.
 *
 * @param string $classic    HTML of the crumbs of the classic breadcrumb.
 * @param string $pagination HTML of the previous and next links of an item, or ''.
 * @return string HTML (escaped).
 */
function tainacan_interface_modern_breadcrumb( $classic, $pagination = '' ) {
	$style = tainacan_interface_opt( 'crumb_style' );
	if ( 'none' === $style ) {
		return '' !== $pagination ? '<nav class="tt2-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'tainacan-interface' ) . '">' . $pagination . '</nav>' : '';
	}

	$steps = tainacan_interface_crumb_steps();
	$home  = tainacan_interface_opt( 'crumb_home_icon' ) ? ' tt2-crumbs--home-icon' : '';

	if ( null === $steps ) {
		return '<nav class="tt2-crumbs tt2-crumbs--flat' . esc_attr( $home ) . '" aria-label="' . esc_attr__( 'Breadcrumb', 'tainacan-interface' ) . '"><div class="tainacan-interface-breadcrumbs">' . $classic . '</div>' . $pagination . '</nav>';
	}

	$html = '<nav class="tt2-crumbs' . esc_attr( $home ) . '" aria-label="' . esc_attr__( 'Breadcrumb', 'tainacan-interface' ) . '"><ol>';
	$last = count( $steps ) - 1;
	foreach ( array_values( $steps ) as $index => $step ) {
		$label = esc_html( tainacan_interface_plain( $step[0] ) );
		if ( $index === $last ) {
			$html .= '<li class="tt2-crumbs__current"><span aria-current="page">' . $label . '</span></li>';
		} elseif ( $step[1] ) {
			$html .= '<li><a href="' . esc_url( $step[1] ) . '">' . $label . '</a></li>';
		} else {
			$html .= '<li><span>' . $label . '</span></li>';
		}
	}
	return $html . '</ol>' . $pagination . '</nav>';
}
