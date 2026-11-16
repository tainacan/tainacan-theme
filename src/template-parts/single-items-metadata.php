<?php
/**
 * Metadata section of a single Tainacan item.
 *
 * The new metadata sections function makes it a bit more complicated to add
 * the thumbnail and share buttons in the middle of the metadata.
 * So we have some logic that is only needed if they are set.
 * The following uses a filter to add them right above the first metadatum in the first section.
 *
 * @package tainacan-interface
 */

// A child theme made for 2.10.0 may have customized the record in the file that held it then.
if ( tainacan_interface_child_legacy_part( 'template-parts/single-items-metadata_new' ) ) {
	return;
}

	$tainacan_interface_metadata_label = (string) get_theme_mod( 'tainacan_single_item_metadata_section_label', '' );
if ( '' !== $tainacan_interface_metadata_label ) :
	?>
		<h2 class="title-content-items" id="single-item-metadata-label"><?php echo esc_html( $tainacan_interface_metadata_label ); ?></h2>
		<?php
	endif;

	$metadata_args = array(
		'display_slug_as_class' => true,
		'exclude_title'         => get_theme_mod( 'tainacan_single_item_hide_core_title_metadata', true ),
		'before_value'          => '<div class="tainacan-metadata-value tainacan-interface-rich-text">',
		'after_value'           => '</div>',
	);

	$section_layout = tainacan_interface_section_layout( tainacan_get_collection_id() );

	// What marks the first section that reaches the page, set per layout below and
	// applied to the rendered HTML, after every filter. Tainacan numbers the empty
	// sections hide_empty skips, and a plugin may remove a section it has already
	// rendered (TainacanBiblio replaces the first one with its own sheet), so neither
	// the "--index-0" filter nor the first call of the generic one is the first section seen.
	$tainacan_interface_first = array();

	if ( 'default' === $section_layout ) {
		$sections_open = tainacan_interface_opt( 'item_sections' );

		if ( 'first' === $sections_open ) {
			$tainacan_interface_first = array( '<details', '<details open' );
		}

		$args          = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<details class="tt2-section"' . ( 'all' === $sections_open ? ' open' : '' ) . '><summary class="tt2-section__summary"><h2 class="tt2-section__title label" id="metadata-section-$slug">',
			'after_name'           => '</h2><span class="tt2-section__count"></span></summary>',
			'before_metadata_list' => '<section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section></details>',
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);
		$wrapper_open  = '<div class="tt2-sections tainacan-single-post">';
		$wrapper_close = '</div>';

	} elseif ( 'tabs' === $section_layout ) {

		$tainacan_interface_first = array( '<input', '<input checked="checked"' );

		$args          = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<input name="tabs" type="radio" id="tab-section-$id" />
                        <label for="tab-section-$id">
                            <h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>
                        </label>',
			'before_metadata_list' => '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>',
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);
		$wrapper_open  = '<div class="single-item-collection--section-metadata--layout-tabs mt-3 tainacan-single-post">';
		$wrapper_close = '</div><div class="my-5 border-bottom border-silver"></div>';

	} elseif ( 'collapses' === $section_layout ) {

		$tainacan_interface_first = array( '<input', '<input checked="checked"' );

		$args          = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<input name="collapses" type="checkbox" id="collapse-section-$id"/>
                        <label for="collapse-section-$id">
                            <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>
                            <h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>
                        </label>',
			'before_metadata_list' => '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>',
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);
		$wrapper_open  = '<div class="single-item-collection--section-metadata--layout-collapses mt-3 tainacan-single-post">';
		$wrapper_close = '</div><div class="my-5 border-bottom border-silver"></div>';

	} elseif ( 'accordion' === $section_layout ) {

		$tainacan_interface_first = array( '<input', '<input checked="checked"' );

		$args          = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<input name="accordion" type="radio" id="accordion-section-$id"/>
                        <label for="accordion-section-$id">
                            <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>
                            <h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>
                        </label>',
			'before_metadata_list' => '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>',
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);
		$wrapper_open  = '<div class="single-item-collection--section-metadata--layout-accordion mt-3 tainacan-single-post">';
		$wrapper_close = '</div><div class="my-5 border-bottom border-silver"></div>';

	} else {
		$args          = array(
			'before'               => '<div class="mt-3 tainacan-single-post">',
			'after'                => '</div><div class="my-5 border-bottom border-silver"></div>',
			'before_name'          => '<h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>',
			'before_metadata_list' => '
                <section class="single-item-collection margin-two-column tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>',
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);
		$wrapper_open  = '';
		$wrapper_close = '';
	}

	$sections_html = (string) tainacan_get_the_metadata_sections( $args );
	if ( ! empty( $tainacan_interface_first ) ) {
		$sections_html = (string) preg_replace( '/' . preg_quote( $tainacan_interface_first[0], '/' ) . '/', $tainacan_interface_first[1], $sections_html, 1 );
	}

	// The two hooks of 2.x fire around the sections, once, in every layout. In 2.x
	// they were concatenated into the arguments, where do_action() returns null: they
	// already fired here, before the sections, and printed outside them.
	do_action( 'tainacan-interface-single-item-metadata-begin' );
	echo wp_kses_post( $wrapper_open );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The HTML that tainacan_the_metadata_sections() itself echoes: Tainacan escapes each value as it renders it; wp_kses_post() would strip the embeds (iframe, audio, video) of media metadata.
	echo $sections_html; // tnc-check-ignore TNC-S05: HTML rendered and escaped by Tainacan's own template tag; wp_kses_post() would strip the embeds of media metadata.
	echo wp_kses_post( $wrapper_close );
	do_action( 'tainacan-interface-single-item-metadata-end' );
