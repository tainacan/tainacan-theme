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

if (
		! tainacan_interface_is_modern() && (
		( has_post_thumbnail() && get_theme_mod( 'tainacan_single_item_display_thumbnail', true ) ) ||
		( ! get_theme_mod( 'tainacan_single_item_collection_header', false ) && get_theme_mod( 'tainacan_single_item_display_share_buttons', true ) ) )
	) {
	// Gets collection so we can obtain firtst metadata.
	$collection = tainacan_get_collection();

	if ( ! is_null( $collection ) ) {

		// Gets array of metadata order.
		$metadata_order = $collection->get_metadata_order();

		if ( is_array( $metadata_order ) ) {

			$first_metadatum_id = -1;

			foreach ( $metadata_order as $metadatum ) {

				// Checks if the metadata is enabled.
				if ( isset( $metadatum['enabled'] ) && $metadatum['enabled'] && isset( $metadatum['id'] ) ) {
					$first_metadatum_id = $metadatum['id'];

					// IF we are not displaying the title here, we must look for the second metadata.
					if ( get_theme_mod( 'tainacan_single_item_hide_core_title_metadata', false ) ) {

						$tainacan_metadata    = \Tainacan\Repositories\Metadata::get_instance();
						$metadatum_object     = $tainacan_metadata->fetch( $first_metadatum_id ); // tnc-check-ignore TNC-P02: the loop stops at the first enabled metadatum that is not the title, so at most two fetches.
						$metadata_type_object = $metadatum_object->get_metadata_type_object();

						if ( 'title' === $metadata_type_object->get_related_mapped_prop() ) {
							continue;
						}
					}

					break;
				}
			}

			if ( is_numeric( $first_metadatum_id ) && $first_metadatum_id >= 0 ) {

				add_filter(
					'tainacan-get-item-metadatum-as-html-before--id-' . $first_metadatum_id,
					function ( $before ) {

						ob_start();

						if ( has_post_thumbnail() && get_theme_mod( 'tainacan_single_item_display_thumbnail', true ) ) : ?>
							<div class="tainacan-item-thumbnail-container card border-0 mb-3">
								<div class="card-body border-0 pl-0 pt-0 pb-1">
									<h3><?php esc_html_e( 'Thumbnail', 'tainacan-interface' ); ?></h3>
											<?php the_post_thumbnail( 'tainacan-medium-full', array( 'class' => 'item-card--thumbnail mt-2' ) ); ?>
								</div>
							</div>
								<?php endif; ?>
							<?php if ( ! get_theme_mod( 'tainacan_single_item_collection_header', false ) && get_theme_mod( 'tainacan_single_item_display_share_buttons', true ) ) : ?>
							<div class="card border-0 mb-3">
								<div class="tainacan-item-share-container card-body border-0 pl-0 pt-0 pb-1">
									<h3><?php esc_html_e( 'Share', 'tainacan-interface' ); ?></h3>
									<div class="btn-group" role="group">
										<?php tainacan_interface_the_share_links( get_permalink(), get_the_title(), 'images', 'item-card-link--sharing' ); ?>
									</div>
								</div>
							</div>
								<?php
						endif;

							$extra_content = ob_get_contents();
							ob_end_clean();

							return $extra_content . $before;
					},
					10,
					2
				);
			}
		}
	}
}

	$tainacan_interface_metadata_label = (string) get_theme_mod( 'tainacan_single_item_metadata_section_label', '' );
if ( '' !== $tainacan_interface_metadata_label ) :
	?>
		<h2 class="title-content-items" id="single-item-metadata-label"><?php echo esc_html( $tainacan_interface_metadata_label ); ?></h2>
		<?php
	endif;

	$metadata_args = array(
		'display_slug_as_class' => true,
		'exclude_title'         => get_theme_mod( 'tainacan_single_item_hide_core_title_metadata', tainacan_interface_is_modern() ),
		'before_value'          => '<div class="tainacan-metadata-value tainacan-interface-rich-text">',
		'after_value'           => '</div>',
	);

	$section_layout = tainacan_interface_section_layout( tainacan_get_collection_id() );

	if ( tainacan_interface_is_modern() && 'default' === $section_layout ) {
		$sections_open = tainacan_interface_opt( 'item_sections' );

		if ( 'first' === $sections_open ) {
			add_filter(
				'tainacan-get-metadata-section-as-html-before-name--index-0',
				function ( $before ) {
					return str_replace( '<details', '<details open', $before );
				},
				10,
				2
			);
		}

		$args = array(
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

		do_action( 'tainacan-interface-single-item-metadata-begin' );
		echo '<div class="tt2-sections tainacan-single-post">';
		tainacan_the_metadata_sections( $args );
		echo '</div>';
		do_action( 'tainacan-interface-single-item-metadata-end' );

	} elseif ( 'tabs' === $section_layout ) {

		add_filter(
			'tainacan-get-metadata-section-as-html-before-name--index-0',
			function ( $before ) {
				return str_replace( '<input', '<input checked="checked"', $before );
			},
			10,
			2
		);

		$args = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<input name="tabs" type="radio" id="tab-section-$id" />
                        <label for="tab-section-$id">
                            <h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>
                        </label>',
			'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ) . '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);

		echo '<div class="single-item-collection--section-metadata--layout-tabs mt-3 tainacan-single-post">';
		tainacan_the_metadata_sections( $args );
		echo '</div><div class="my-5 border-bottom border-silver"></div>';

	} elseif ( 'collapses' === $section_layout ) {

		add_filter(
			'tainacan-get-metadata-section-as-html-before-name--index-0',
			function ( $before ) {
				return str_replace( '<input', '<input checked="checked"', $before );
			},
			10,
			2
		);

		$args = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<input name="collapses" type="checkbox" id="collapse-section-$id"/>
                        <label for="collapse-section-$id">
                            <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>
                            <h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>
                        </label>',
			'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ) . '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);

		echo '<div class="single-item-collection--section-metadata--layout-collapses mt-3 tainacan-single-post">';
		tainacan_the_metadata_sections( $args );
		echo '</div><div class="my-5 border-bottom border-silver"></div>';

	} elseif ( 'accordion' === $section_layout ) {

		add_filter(
			'tainacan-get-metadata-section-as-html-before-name--index-0',
			function ( $before ) {
				return str_replace( '<input', '<input checked="checked"', $before );
			},
			10,
			2
		);

		$args = array(
			'before'               => '',
			'after'                => '',
			'before_name'          => '<input name="accordion" type="radio" id="accordion-section-$id"/>
                        <label for="accordion-section-$id">
                            <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>
                            <h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>
                        </label>',
			'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ) . '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);

		echo '<div class="single-item-collection--section-metadata--layout-accordion mt-3 tainacan-single-post">';
		tainacan_the_metadata_sections( $args );
		echo '</div><div class="my-5 border-bottom border-silver"></div>';

	} else {
		$args = array(
			'before'               => '<div class="mt-3 tainacan-single-post">',
			'after'                => '</div><div class="my-5 border-bottom border-silver"></div>',
			'before_name'          => '<h2 class="title-content-items" id="metadata-section-$slug">',
			'after_name'           => '</h2>',
			'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ) . '
                <section class="single-item-collection margin-two-column tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
			'after_metadata_list'  => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
			'metadata_list_args'   => $metadata_args,
			'hide_empty'           => true,
		);
		tainacan_the_metadata_sections( $args );
	}


	?>